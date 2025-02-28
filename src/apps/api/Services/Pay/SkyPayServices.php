<?php

namespace Api\Services\Pay;

use Admin\Services\Repayment\ManualRepaymentServer;
use Admin\Services\TradeManage\RemitServer;
use Api\Models\Order\Order;
use Api\Models\Trade\TradeLog;
use Api\Services\BaseService;
use Common\Libraries\PayChannel\SkyPay\SkyPay;
use Common\Services\Repay\RepayServer;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Payment\Dragonpay\Api;

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-25
 * Time: 下午8:44
 */
class SkyPayServices extends BaseService
{
    /**
     * 完结支出 放款成功 payoutQueuePayout(线上出款成功) GCash, bank
     */
    public function payoutQueuePayout()
    {
        $success = [
            'responseTime'        => date('Y-m-d H:i:s'),
            'responseCode'        => '1000',
            'responseDescription' => 'Success'
        ];

        $pay    = new SkyPay();
        $params = $pay->encryptData('payoutQueuePayout');
        try {
            MerchantHelper::clearMerchantId();
            $tradeLog = TradeLog::model()->where('withdraw_no', $params['controlNumber'])->orderBy('id')->first();


            //验签
            if (!$params) {
                return $pay->responseData([
                    'responseTime'        => date('Y-m-d H:i:s'),
                    'responseCode'        => '-1010',
                    'responseDescription' => '验签错误'
                ], $params, 'payoutPayout');
            }

            if (!$tradeLog || !$tradeLog->order) {
                EmailHelper::send(var_export($params, true), '代付出款，交易记录未找到');
                return $pay->responseData([
                    'responseTime'        => date('Y-m-d H:i:s'),
                    'responseCode'        => '-1010',
                    'responseDescription' => '订单不存在'
                ], $params, 'payoutQueuePayout');
            }
            MerchantHelper::setMerchantId($tradeLog->merchant_id);

            if (isset(Api::PROD_ENV[$tradeLog->merchant_id])) {
                MerchantHelper::helper()->setMerchantId($tradeLog->merchant_id);
                (new Api())->lifeTime($tradeLog->user);
            }
            $tradeTime = $params['dealTime'] ?? date('Y-m-d H:i:s');

            //明确成功
            if ($params['payType'] == '2') {
                if ($tradeLog->isSuccess()) {
                    // 回调已处理
                    return $pay->responseData($success, $params, 'payoutPayout');
                }
                RemitServer::server()->flowRemitSuccess($tradeLog, $tradeLog->trade_amount, $tradeTime);

                //明确失败
            } else if ($params['payType'] == '4') {
                if ($tradeLog->isFailed()) {
                    // 回调已处理
                    return $pay->responseData($success, $params, 'payoutPayout');
                }
                RemitServer::server()->flowRemitFailed($tradeLog, $tradeTime, true, [
                    'msg'     => $params['failDescription'],
                    'tradeNo' => $params['referenceNumber']
                ]);
            }

            return $pay->responseData($success, $params, 'payoutQueuePayout');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            EmailHelper::send(var_export($params, true) . "\n e:{$msg}", '代付出款报错');
            return $pay->responseData([
                'responseTime'        => date('Y-m-d H:i:s'),
                'responseCode'        => '-1010',
                'responseDescription' => $e->getMessage()
            ], $params, 'payoutQueuePayout');
        }
    }

    /**
     * 还款验证 - 读取还款金额的获取
     */
    public function collectionInquiry()
    {
        $pay  = new SkyPay();
        $data = $pay->encryptData('collectionInquiry');
        MerchantHelper::clearMerchantId();
        $order = Order::where('reference_no', $data['contractNumber'])->first();
        if(!$order){
            DingHelper::notice("还款验证是合同号【{$data['contractNumber']}】未找到", '合同未找到,请检查!');
        }
        MerchantHelper::setMerchantId($order->merchant_id);
        $user          = $order->user;
        $repaymentPlan = $order->lastRepaymentPlan;

        $subject = CalcRepaymentSubjectServer::server($repaymentPlan)->getSubject();

        return $pay->responseData([
            "responseTime"        => date('Y-m-d H:i:s'),
            "responseCode"        => "1000",
            "responseDescription" => "Success",
            "amount"              => $subject->repaymentPaidAmount >= 0 ? $subject->repaymentPaidAmount : $order->principal,
            "payerName"           => $user->fullname,
            "payerAddress"        => "",
            "payerPhone"          => $user->telephone
        ], $data, 'collectionInquiry');

    }

    /**
     * 线下取款模式
     * 你们生成放款信息， 把放款码给用户，  用户拿着码去线下店取款，店员调用4.1接口，你们返回成功，店员点击确认，调用4.2接口，用户取到钱，放款完成
     * 放款验证 payoutInquiry
     * @return string
     */
    public function payoutInquiry()
    {
        $pay  = new SkyPay();
        $data = $pay->encryptData('payoutInquiry');

        //验签
        if (!$data) {
            return $pay->responseData([
                'responseTime'        => date('Y-m-d H:i:s'),
                'responseCode'        => '-1010',
                'responseDescription' => '验签错误'
            ], $data, 'payoutInquiry');
        }
        MerchantHelper::clearMerchantId();
        $trade = TradeLog::model()
            ->where('withdraw_no', $data['controlNumber'])
            ->first();

        if ($trade && $trade->order->status == Order::STATUS_PAYING) {
            MerchantHelper::setMerchantId($trade->merchant_id);
            $payoutParams = (new SkyPay($trade))->payoutParams();

            return $pay->responseData([
                "responseTime"        => date('Y-m-d H:i:s'),
                "responseCode"        => "1000",
                "responseDescription" => "Success",
                'sender'              => $payoutParams['sender'],
                'senderAddress'       => $payoutParams['location'],
                "controlNumber"       => $payoutParams['controlNumber'],
                "name"                => $payoutParams['name'],
                "birthday"            => $payoutParams['birthday'],
                "identificationId"    => $payoutParams['identificationId'],
                "idType"              => $payoutParams['idType'],
                "phone"               => $payoutParams['phone'],
                "amount"              => $payoutParams['amount'],
                "idcardPicType"       => $payoutParams['idcardPicType'],
                "idcardPicUrl"        => $payoutParams['idcardPicUrl'],
                "contractNumber"      => $payoutParams['contractNumber'],
                "location"            => $payoutParams['location']
            ], $data, 'payoutInquiry');
        } else {
            return $pay->responseData([
                "responseTime"        => date('Y-m-d H:i:s'),
                "responseCode"        => "-1000",
                "responseDescription" => "order error",
            ], $data, 'payoutInquiry');
        }
    }

    /**
     * 完结还款 collectionCollect 还款成功
     */
    public function collectionCollect()
    {
        $pay  = new SkyPay();
        $data = $pay->encryptData('collectionCollect');

        //验签
        if (!$data) {
            return $pay->responseData([
                'responseTime'        => date('Y-m-d H:i:s'),
                'responseCode'        => '-1010',
                'responseDescription' => '验签错误'
            ], $data, 'payoutPayout');
        }

        try {
            $contractNumber = $data['contractNumber'];
            $receiptNumber  = $data['receiptNumber']; //收款编号
            MerchantHelper::clearMerchantId();
            $trade = TradeLog::model()
                ->where('reference_no', $contractNumber)
                ->where('trade_platform_no', $receiptNumber)
                ->first();


            //没有trade -- 需要手动补充
            if (!$trade) {
                $order         = \Admin\Models\Order\Order::where('reference_no', $data['contractNumber'])->first();
                if(!$order){
                    DB::rollBack();
                    return $pay->responseData([
                        'responseTime'        => date('Y-m-d H:i:s'),
                        'responseCode'        => '1000',
                        'responseDescription' => "Missing order data"
                    ], $data, 'collectionCollect');
                }
                MerchantHelper::setMerchantId($order->merchant_id);
                $user          = $order->user;
                $repaymentPlan = $order->lastRepaymentPlan;

                $tradeParams = [
                    'remark'          => 'SKY - 还款回调- 自动补单',
                    'repay_name'      => $user->fullname,
                    'repay_telephone' => $data['phone'],
                    'repay_account'   => $contractNumber,
                    'repay_time'      => $data['"collectedTime'] ?? date('Y-m-d H:i:s'),
                    'repay_channel'   => TradeLog::TRADE_PLATFORM_SKYPAY,
                    'repay_amount'    => $data['amount']
                ];

                DB::beginTransaction();
                $trade                    = ManualRepaymentServer::server()->addRepayTradeLog($order, TradeLog::TRADE_PLATFORM_SKYPAY, $data["amount"], $tradeParams);
                $trade->trade_platform_no = $receiptNumber;
                $trade->reference_no      = $contractNumber;

                
                $trade->save();

                RepayServer::server($repaymentPlan, $trade)->completeRepay();
            }

            DB::commit();

            return $pay->responseData([
                'responseTime'        => date('Y-m-d H:i:s'),
                'responseCode'        => '1000',
                'responseDescription' => 'Success'
            ], $data, 'collectionCollect');

        } catch (\Exception $exception) {
            DB::rollBack();
            return $pay->responseData([
                'responseTime'        => date('Y-m-d H:i:s'),
                'responseCode'        => '1000',
                'responseDescription' => $exception->getMessage()
            ], $data, 'collectionCollect');
        }
    }

    /**
     * 放款确认信息 -- payoutPayout 线下放款成功通知: 只有成功,没有失败
     */
    public function payoutPayout()
    {
        $success = [
            'responseTime'        => date('Y-m-d H:i:s'),
            'responseCode'        => '1000',
            'responseDescription' => 'Success'
        ];

        $pay    = new SkyPay();
        $params = $pay->encryptData('payoutPayout');

        try {
            MerchantHelper::clearMerchantId();
            $tradeLog = TradeLog::model()->where('withdraw_no', $params['controlNumber'])->orderBy('id')->first();

            MerchantHelper::setMerchantId($tradeLog->merchant_id);

            //验签
            if (!$params) {
                return $pay->responseData([
                    'responseTime'        => date('Y-m-d H:i:s'),
                    'responseCode'        => '-1010',
                    'responseDescription' => '验签错误'
                ], $params, 'payoutPayout');
            }

            if (!$tradeLog || !$tradeLog->order) {
                EmailHelper::send(var_export($params, true), '代付出款，交易记录未找到');
                return $pay->responseData([
                    'responseTime'        => date('Y-m-d H:i:s'),
                    'responseCode'        => '-1010',
                    'responseDescription' => '订单不存在'
                ], $params, 'payoutPayout');
            }

            $tradeTime = $params['payTime'] ?? date('Y-m-d H:i:s');

            if ($tradeLog->isSuccess()) {
                // 回调已处理
                return $pay->responseData($success, $params, 'payoutPayout');
            }
            RemitServer::server()->flowRemitSuccess($tradeLog, $tradeLog->trade_amount, $tradeTime);

            return $pay->responseData($success, $params, 'payoutPayout');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            EmailHelper::send(var_export($params, true) . "\n e:{$msg}", '代付出款报错');
            return $pay->responseData([
                'responseTime'        => date('Y-m-d H:i:s'),
                'responseCode'        => '-1010',
                'responseDescription' => $e->getMessage()
            ], $params, 'payoutPayout');
        }
    }
}