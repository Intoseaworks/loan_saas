<?php

namespace Api\Services\Pay;

use Api\Models\Trade\TradeLog;
use Api\Services\BaseService;
use Common\Utils\Payment\Dragonpay\Api;
use Illuminate\Support\Facades\DB;
use Common\Services\Repay\RepayServer;
use Common\Models\User\UserInfo;
use Admin\Models\User\User;
use Admin\Services\Repayment\ManualRepaymentServer;
use Common\Utils\MerchantHelper;
use Admin\Services\TradeManage\RemitServer;

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-25
 * Time: 下午8:44
 */
class DragonPayService extends BaseService {

    public function callback($data) {
        try {
            $dgApi = new Api();
            $dgApi->writeLog($data, "callback");
            $digest = array_get($data, "digest");
            $txnid = array_get($data, "txnid");
            $status = array_get($data, "status");
            $refno = array_get($data, "refno");
            $message = array_get($data, "message");
            $time = array_get($data, "time");
            $amount = array_get($data, "amount");

            #payout back
            $payOutMerchantTxnId = array_get($data, "merchantTxnId");
            $payOutRefNo = array_get($data, "refNo");
            $payOutStatus = array_get($data, "status");

            if ($payOutMerchantTxnId) {
                MerchantHelper::clearMerchantId();
                $trade = TradeLog::model()
                        ->where('master_business_no', $payOutMerchantTxnId)
                        ->where('trade_platform_no', $payOutRefNo)
                        ->first();
                $result = $this->payout($trade, $data);
                echo "result={$result}";
                exit;
            }

            $result = 'unknown';
            $merchant_statuses = [
                'P' => 'Unpaid+',
                'U' => 'Unpaid',
                'F' => 'Payment_Failed',
                'S' => 'Paid',
                'V' => 'Cancelled', // Merchant cancelled or the user requested for a refund directly through Dragonpay.
                'R' => 'Reversed', // Only for credit cards and other payment methods that can be reversed.
            ];

            if (empty($digest) || empty($txnid) || empty($status) || empty($refno) || empty($message)) {
                $result = 'missing_parameters';
            } else if (!in_array($status, array_keys($merchant_statuses))) {
                $result = 'invalid_status_' . $status;
            } else {
                $digest_data = [
                    'txnid' => $txnid,
                    'refno' => $refno,
                    'status' => $status,
                    'message' => $message,
                    'key' => $dgApi->getPasswordKey(),
                ];

                $digest_string = implode(':', $digest_data);
                $digest_compare = sha1($digest_string);

                if ($digest_compare != $digest) {
                    $result = 'digest_error';
                    echo "result={$result}";
                    exit;
                }
                MerchantHelper::clearMerchantId();
                $trade = TradeLog::model()
                        ->where('transaction_no', $txnid)
                        ->first();
                if ($trade) {
                    MerchantHelper::setMerchantId($trade->merchant_id);
                    if ($trade->trade_evolve_status == TradeLog::TRADE_EVOLVE_STATUS_TRADING) {
                        $repaymentPlan = $trade->order->lastRepaymentPlan;
                        DB::beginTransaction();
                        $trade->evolveStatusOverResultSuccess($refno, $time, $time, $amount);
                        RepayServer::server($repaymentPlan, $trade)->completeRepay();
                        DB::commit();
                    }
                    $result = 'ok';
                } else {
                    $lifetimeId = explode('-', $txnid);
                    $lifetimeId = $lifetimeId[0];
                    $userInfo = UserInfo::where('dg_pay_lifetime_id', $lifetimeId)->first();
                    if (!$userInfo) {
                        $lifetimeId = \Common\Models\User\UserLifetimeId::where("lifetime_id")->first();
                        if (!$lifetimeId) {
                            echo "result=no_found_userinfo";
                            exit;
                        } else {
                            $userId = $lifetimeId->user_id;
                        }
                    } else {
                        $userId = $userInfo->user_id;
                    }
                    $user = User::model()->getOne($userId);
                    $order = $user->order;
                    $repaymentPlan = $order->lastRepaymentPlan;
                    $tradeParams = [
                        'remark' => 'DragonPay - 还款回调- 自动补单',
                        'repay_name' => $user->fullname,
                        'repay_telephone' => $user->telephone,
                        'repay_time' => $time ?? date('Y-m-d H:i:s'),
                        'repay_channel' => TradeLog::TRADE_PLATFORM_DRAGONPAY,
                        'repay_amount' => $amount
                    ];

                    $trade = ManualRepaymentServer::server()->addRepayTradeLog($order, TradeLog::TRADE_PLATFORM_DRAGONPAY, $amount, $tradeParams);
                    $trade->trade_platform_no = $txnid;
                    $trade->request_no = $refno;

                    DB::beginTransaction();
                    $trade->save();

                    RepayServer::server($repaymentPlan, $trade)->completeRepay();
                    DB::commit();
                    $result = 'ok';
                }
            }
            echo "result={$result}";
            exit;
        } catch (\Exception $e) {
            DB::rollBack();
            echo "result={$e->getMessage()}";
        }
    }

    private function payout($tradeLog, $params) {
        try {
            if($tradeLog){
                MerchantHelper::setMerchantId($tradeLog->merchant_id);
            }
            $dgApi  = new Api();
            $digest_data = [
                'merchantTxnId' => $params['merchantTxnId'],
                'refNo' => $params['refNo'],
                'status' => $params['status'],
                'message' => $params['message'],
                'key' => $dgApi->getPasswordKey(),
            ];

            $digest_string = implode(':', $digest_data);
            $digest_compare = sha1($digest_string);

            if ($digest_compare != $params['digest']) {
                $result = 'digest_error';
                echo "result={$result}";
                exit;
            }


            (new Api())->lifeTime($tradeLog->user);

            if (!$tradeLog || !$tradeLog->order) {
                EmailHelper::send(var_export($params, true), '代付出款，交易记录未找到');
                return "The order does not exist";
            }

            MerchantHelper::setMerchantId($tradeLog->merchant_id);
            $tradeTime = $params['dealTime'] ?? date('Y-m-d H:i:s');

            //明确成功
            if ($params['status'] == 'S') {
                if ($tradeLog->isSuccess()) {
                    // 回调已处理
                    return "ok";
                }
                RemitServer::server()->flowRemitSuccess($tradeLog, $tradeLog->trade_amount, $tradeTime);
                //明确失败
            } else if ($params['status'] == 'F') {
                if ($tradeLog->isFailed()) {
                    // 回调已处理
                    return "ok";
                }
                RemitServer::server()->flowRemitFailed($tradeLog, $tradeTime, true, ['msg'=>$params['message']]);
            }

            return "ok";
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            EmailHelper::send(var_export($params, true) . "\n e:{$msg}", '代付出款报错');
            return $msg;
        }
    }

}
