<?php

namespace Common\Services\Order;

use Admin\Services\TradeManage\RemitServer;
use Api\Services\Config\ConfigServer;
use Carbon\Carbon;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Order\Order;
use Common\Models\Order\OrderDetail;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Trade\AdminTradeAccount;
use Common\Models\Trade\TradeLog;
use Common\Models\Trade\TradeLogDetail;
use Common\Services\BaseService;
use Common\Services\NewClm\ClmServer;
use Common\Services\Pay\BasePayServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Illuminate\Support\Facades\DB;
use Common\Services\Config\LoanMultipleConfigServer;

class OrderPayServer extends BaseService {

    const PAY_ENV_TEST = 'test';
    const PAY_ENV_PROD = 'prod';

    /**
     * 代付
     * @param $order
     * @return OrderPayServer
     * @throws \Exception
     */
    public function daifu($order) {
        $order->service_charge = $order->principal * LoanMultipleConfigServer::server()->getServiceChargeRate($order->user, $order->loan_days);
        //折扣服务砍头费
        $order->service_charge = $order->service_charge * (1 - ClmServer::server()->getInterestDiscount($order->user) / 100); //砍头费打折
        $order->save();
        /** @var $order Order */
        $order->refresh();

        if (!OrderServer::server()->canExecRemit($order)) {
            return $this->outputError('订单暂不能放款');
        }

        $amount = $order->getPaidAmount(false);
        if (app()->environment() != 'prod') {
            $amount = '1';
        }

        // 检查是否超过日最大放款金额
        if (OrderCheckServer::server()->reachMaxLoanAmount($amount)) {
            return $this->output(self::OUTPUT_DAIFU_EXCESS, '超过日最大放款金额');
        }

        /** 订单进入放款 默认锁定5分钟 */
//        if (!RemitServer::server()->lockManualRemit($order->id, true)) {
//            return $this->outputError('订单锁暂存在');
//        }

        $tradeAccountNo = OrderDetail::model()->getBankCardNo($order) ?? $order->user->bankCard->account_no;
        $tradeAccountBankName = OrderDetail::model()->getBankName($order) ?? $order->user->bankCard->bank_name;

        $tradePlatform = $order->pay_channel;

        /** 生成交易记录 */
        $telephone = $order->user->telephone;
        $params = [
            'merchant_id' => $order->merchant_id,
            'trade_platform' => $tradePlatform,
            'user_id' => $order->user_id,
            'admin_trade_account_id' => 0, //暂时去除
            'bank_card_id' => $order->user->bankCard->id ?? null,
            'master_related_id' => $order->id,
            'master_business_no' => $order->order_no,
            'business_type' => TradeLog::BUSINESS_TYPE_MANUAL_REMIT,
            'trade_type' => TradeLog::TRADE_TYPE_REMIT,
            'request_type' => TradeLog::REQUEST_TYPE_SYSTEM,
            'business_amount' => $amount,
            'trade_amount' => $amount,
            'bank_name' => $tradeAccountBankName,
            'trade_account_telephone' => $telephone,
            'trade_account_name' => $order->user->fullname,
            'trade_account_no' => $tradeAccountNo,
            'trade_desc' => 'system payout',
            'trade_request_time' => Carbon::now()->toDateTimeString(),
            'admin_id' => TradeLog::HANDLER_SYSTEM,
            'handle_name' => '',
        ];

        $tradeLog = TradeLog::model()->add($params);

        DB::beginTransaction();
        try {
            $orderServer = OrderServer::server();
            $orderServer->paying($order->id);
            $tradeLog->evolveStatusTrading();
            /** 放款业务实现 */
            $result = BasePayServer::server()->executeDaifuPay($tradeLog);
            $data = $result->getData();
            if (!$result->isSuccess() || $data['status'] != BasePayServer::RESULT_SUCCESS) {
                $msg = $result->getMsg() ?? null;
                $tradeLog->evolveStatusOverResultFailed('', '', DateHelper::dateTime(), $msg);
                $orderServer->systemPayFail($order->id);
                DB::commit();

                return $this->outputError(self::OUTPUT_DAIFU_FAIL, '代付支付失败');
            }
            $tradeLog->evolveStatusTrading($data['requestNo']);
            //交易成功 放到回调处理
            //$tradeLog->evolveStatusOverResultSuccess($data['requestNo'], '', DateHelper::dateTime());
            //$orderServer->systemPaid($order->id);
            DB::commit();
            if ($order->refresh()->getPaymentType() == BankCardPeso::PAYMENT_TYPE_CASH) {
                event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_DRAW_MONEY));
            }
            return $this->outputSuccess();
        } catch (\Exception $e) {
            DB::rollBack();
            EmailHelper::sendException($e, '代付执行报错');
            return $this->outputError(self::OUTPUT_DAIFU_FAIL, $e->getMessage());
        }
    }

    /**
     * 代扣（废弃）
     * @param $order
     * @param bool $isSystem
     * @return bool|TradeLog|null
     */
    public function daikou($order, $isSystem = true) {
        /** @var $order Order */
        $order->refresh();
        if (!in_array($order->status, Order::WAIT_REPAYMENT_STATUS)) {
            return null;
        }

        if (!$repaymentPlan = $order->lastRepaymentPlan) {
            return false;
        }

        $receivableAmount = $order->repayAmount();
        if (app()->environment() != 'prod') {
            $receivableAmount = 0.01;
        }

        /** @var BankCardPeso $bankCard */
        $bankCard = $order->user->bankCard;
        if (!$bankCard) {
            EmailHelper::send(
                    "order_id:{$order->id}, user_id:{$order->user}",
                    '代扣报错：找不到用户当前绑定卡号'
            );
            return false;
        }
        /** 获取代扣账号 */
        if (!$daikouAccount = BasePayServer::server()->getDaikouAccount()) {
            EmailHelper::send('未设置代扣账号,请设置', '未设置代扣账号,请设置');
            return false;
        }
        /** 生成交易记录 */
        $params = [
            'merchant_id' => $order->merchant_id,
            'trade_platform' => array_get(AdminTradeAccount::PAYMENT_METHOD_RELATE_PLATFORM,
                    $daikouAccount->payment_method),
            'user_id' => $order->user_id,
            'admin_trade_account_id' => $daikouAccount->id,
            'bank_card_id' => $bankCard->id,
            'master_related_id' => $order->id,
            'master_business_no' => $order->order_no,
            'business_type' => TradeLog::BUSINESS_TYPE_REPAY,
            'trade_type' => TradeLog::TRADE_TYPE_RECEIPTS,
            'request_type' => $isSystem ? TradeLog::REQUEST_TYPE_SYSTEM : TradeLog::REQUEST_TYPE_USER,
            'business_amount' => $receivableAmount,
            'trade_amount' => $receivableAmount,
            'bank_name' => $bankCard->bank_name,
            'trade_account_telephone' => $order->user->telephone,
            'trade_account_name' => $bankCard->account_name,
            'trade_account_no' => $bankCard->account_no,
            'trade_desc' => $isSystem ? '系统代扣回款' : '一键代扣还款',
            'trade_request_time' => Carbon::now()->toDateTimeString(),
            'admin_id' => 0,
            'handle_name' => '',
        ];

        $tradeLog = TradeLog::model()->add($params);

        DB::beginTransaction();
        try {
            $orderServer = OrderServer::server();
            $orderServer->repaying($order->id);
            $tradeLog->evolveStatusTrading();

            $result = BasePayServer::server()->executeDaikouPay($tradeLog, $bankCard);
            $data = $result->getData();

            if (!$result->isSuccess() || $data['status'] != BasePayServer::RESULT_SUCCESS) {
                $tradeLog->evolveStatusOverResultFailed('', '', DateHelper::dateTime());
                $orderServer->revertStatus($order->id);
                DB::commit();
                return false;
            }
            $tradeLog->evolveStatusTrading($data['requestNo']);
            //交易成功 放到回调处理
            //$tradeLog->evolveStatusOverResultSuccess($data['requestNo'], '', DateHelper::dateTime());
            //$orderServer->systemPaid($order->id);
            DB::commit();
            return $tradeLog;
        } catch (\Exception $e) {
            DB::rollBack();
            EmailHelper::send(
                    "order_id:{$order->id}, user_id:{$order->user}, bankcard_no:{$bankCard->account_no} \n e:{$e->getMessage()}",
                    '代扣执行报错'
            );
            return false;
        }
    }

    /**
     * 续期代扣方法（废弃）
     * @param $renewalModel
     * @return bool|TradeLog
     * @throws \Exception
     */
    public function renewalPay($renewalModel) {
        /** @var Order $order */
        $order = $renewalModel->order;
        if (!OrderCheckServer::server()->canRenewalOrder($order)) {
            return false;
        }
        $amount = $renewalModel->renewal_fee;
        if (app()->environment() != 'prod') {
            $amount = 0.01;
        }

        /** @var BankCardPeso $bankCard */
        $bankCard = $order->user->bankCard;
        if (!$bankCard) {
            EmailHelper::send(
                    "order_id:{$order->id}, user_id:{$order->user}, repayment_plan_renewal_id:{$renewalModel->id}",
                    '续期报错：找不到用户当前绑定卡号'
            );
            return false;
        }
        /** 获取代扣账号 */
        if (!$daikouAccount = BasePayServer::server()->getDaikouAccount()) {
            EmailHelper::send('未设置代扣账号,请设置', '未设置代扣账号,请设置');
            return false;
        }

        /** 生成交易记录 */
        $params = [
            'merchant_id' => $order->merchant_id,
            'trade_platform' => array_get(AdminTradeAccount::PAYMENT_METHOD_RELATE_PLATFORM,
                    $daikouAccount->payment_method),
            'user_id' => $order->user_id,
            'admin_trade_account_id' => $daikouAccount->id,
            'bank_card_id' => $bankCard->id,
            'master_related_id' => $order->id,
            'master_business_no' => $order->order_no,
            'business_type' => TradeLog::BUSINESS_TYPE_RENEWAL,
            'trade_type' => TradeLog::TRADE_TYPE_RECEIPTS,
            'request_type' => TradeLog::REQUEST_TYPE_USER,
            'business_amount' => $amount,
            'bank_name' => $bankCard->bank_name,
            'trade_account_telephone' => $order->user->telephone,
            'trade_account_name' => $bankCard->account_name,
            'trade_account_no' => $bankCard->account_no,
            'trade_desc' => '订单续期',
            'trade_request_time' => Carbon::now()->toDateTimeString(),
            'admin_id' => 0,
            'handle_name' => '',
        ];
        $tradeLogDetail = [
            [
                'business_id' => $renewalModel->id,
                'business_no' => '',
                'amount' => $amount,
            ],
        ];

        $tradeLog = TradeLog::model()->add($params, $tradeLogDetail);

        DB::beginTransaction();
        try {
            $tradeLog->evolveStatusTrading();

            $result = BasePayServer::server()->executeDaikouPay($tradeLog, $bankCard);
            $data = $result->getData();

            if (!$result->isSuccess() || $data['status'] != BasePayServer::RESULT_SUCCESS) {
                $tradeLog->evolveStatusOverResultFailed('', '', DateHelper::dateTime(), $result->getMsg());
                $renewalModel->toRenewalFailed();
                DB::commit();
                return false;
            }
            $tradeLog->evolveStatusTrading($data['requestNo']);
            DB::commit();
            return $tradeLog;
        } catch (\Exception $e) {
            DB::rollBack();
            EmailHelper::send(
                    "order_id:{$order->id}, user_id:{$order->user}, bankcard_no:{$bankCard->account_no}, repayment_plan_renewal_id:{$renewalModel->id}
                 \n e:{$e->getMessage()}",
                    '续期代扣报错！'
            );
            return false;
        }
    }

    /**
     * 第三方页面支付
     * @param Order $order
     * @param $platform
     * @param $repayAmount
     * @param $payerAccount
     * @return array|bool
     * @throws \Exception
     */
    public function payment(Order $order, $platform, $repayAmount = null, $payerAccount = null, $from = "system", $method = "referenceNumber") {
        if (!in_array($platform, array_keys(TradeLog::TRADE_PLATFORM))) {
            DingHelper::notice("platform参数错误：{$platform}." . static::class . ':' . __FUNCTION__, 'platform参数错误');
            return false;
        }

        $tradeLog = $this->buildRepayTradeLog($order, $platform, $repayAmount, $from . ' repay', $payerAccount);

        DB::beginTransaction();
        try {
            $orderServer = OrderServer::server();

            // 订单流转交易中状态放到 支付成功重定向中处理。除 Mpurse 外
            $tradeLog->evolveStatusTrading();
            $result = BasePayServer::server()->executePay($tradeLog, $method);
            $data = $result->getData();

            if (!$result->isSuccess()) {
                $tradeLog->evolveStatusOverResultFailed(
                        array_get($data, 'requestNo'),
                        array_get($data, 'tradeNo'),
                        DateHelper::dateTime(),
                        $result->getMsg()
                );
                $order->refresh();
                if ($order->status == Order::STATUS_REPAYING) {
                    $orderServer->revertStatus($order->id);
                }
                DB::commit();
                return false;
            }
            $tradeLog->evolveStatusTrading(array_get($data, 'tradeNo'), array_get($data, 'tradeNo'));
            //交易成功 放到回调处理
            DB::commit();
            return $data;
        } catch (\Exception $e) {
            DB::rollBack();
            DingHelper::notice(
                    "order_id:{$order->id}, user_id:{$order->user->id}, trade_log_id:{$tradeLog->id} \n e:{$e->getMessage()}",
                    'DGPAY还款执行报错'
            );

            throw $e;
            return false;
        }
    }

    protected function getSuccessMsg($platform) {
        switch ($platform) {
            case TradeLog::TRADE_PLATFORM_MPURSE:
                return 'Request successful, please check UPI account!';
            default:
                return 'success';
        }
    }

    public function appPayment(Order $order, $platform, $repayAmount = null) {
        if (!in_array($platform, array_keys(TradeLog::TRADE_PLATFORM))) {
            DingHelper::notice("platform参数错误：{$platform}." . static::class . ':' . __FUNCTION__, 'platform参数错误');
            return false;
        }

        $tradeLog = $this->buildRepayTradeLog($order, $platform, $repayAmount, 'app repay');

        DB::beginTransaction();
        try {
            //$orderServer = OrderServer::server();
            // 订单流转交易中状态放到 支付成功重定向中处理
            //$orderServer->repaying($order->id);
            $tradeLog->evolveStatusTrading();

            $result = BasePayServer::server()->executeAppPay($tradeLog);
            $data = $result->getData();

            if (!$result->isSuccess() || $data['status'] != BasePayServer::RESULT_SUCCESS) {
                $tradeLog->evolveStatusOverResultFailed(
                        array_get($data, 'requestNo'),
                        array_get($data, 'tradeNo'),
                        DateHelper::dateTime(),
                        array_get($data, 'msg')
                );
                //$orderServer->revertStatus($order->id);
                DB::commit();
                return false;
            }
            $tradeLog->evolveStatusTrading($data['requestNo'], array_get($data, 'tradeNo'));
            //交易成功 放到回调处理
            //$tradeLog->evolveStatusOverResultSuccess($data['requestNo'], '', DateHelper::dateTime());
            //$orderServer->systemPaid($order->id);

            DB::commit();

            return array_merge(array_only($data, ['transactionNo', 'open_method', 'tradeAmount', 'post_url', 'resultDetail']), [
                'env' => self::PAY_ENV_PROD,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            DingHelper::notice(
                    "order_id:{$order->id}, user_id:{$order->user->id}, trade_log_id:{$tradeLog->id} \n e:{$e->getMessage()}",
                    'apppay执行报错',
                    DingHelper::AT_SOLIANG
            );
            return false;
        }
    }

    protected function buildRepayTradeLog(Order $order, $platform, $repayAmount = null, $tradeDesc = 'repay', $payerAccount = null) {
        $receivableAmount = $order->repayAmount();
        $isFinalRepay = true;
        // 格式化成两位小数，bc使用两位小数
        $repayAmount = isset($repayAmount) ? MoneyHelper::round2point($repayAmount) : null;
        // 有传递还款金额字段 && 还款金额小于应还金额
        if (
                isset($repayAmount) &&
                MoneyHelper::compare($receivableAmount, 0) === 1 &&
                MoneyHelper::sub($receivableAmount, $repayAmount) > 1 // 部分还款小于应还金额 1卢比以上才支持部分还，不然剩下的小于1卢比的razorpay不支持还
        ) {
            // 判断最小应还金额
            $minRepayAmount = ConfigServer::server()->getMinPartRepay($order);
            if (MoneyHelper::compare($repayAmount, $minRepayAmount) === -1) {
                //$this->outputException('No less than the minimum repayment');//飞象要求跳过此设置
            }
            // 根据条件，至此时 还款金额肯定小于应还金额，不设置完结字段
            $isFinalRepay = false;
            $receivableAmount = $repayAmount;
        }

        if (app()->environment() != 'prod') {
            //$receivableAmount = 1;
        }
        $user = $order->user;
        /** 生成交易记录 */
        $params = [
            'merchant_id' => $order->merchant_id,
            'trade_platform' => $platform,
            'user_id' => $order->user_id,
            'admin_trade_account_id' => 0, //暂设为0
            'master_related_id' => $order->id,
            'master_business_no' => $order->order_no,
            'business_type' => TradeLog::BUSINESS_TYPE_REPAY,
            'trade_type' => TradeLog::TRADE_TYPE_RECEIPTS,
            'request_type' => TradeLog::REQUEST_TYPE_USER,
            'business_amount' => $receivableAmount,
            'trade_amount' => $receivableAmount,
            //'bank_name' => $bankCard->bank_name,
            'trade_account_telephone' => $user->telephone,
            'trade_account_name' => $user->fullname,
            'trade_account_no' => $payerAccount,
            'trade_desc' => $tradeDesc,
            'trade_request_time' => Carbon::now()->toDateTimeString(),
            'admin_id' => 0,
            'handle_name' => '',
        ];

        $repaymentPlans = RepaymentPlan::getNeedRepayRepaymentPlans($order);
        $detailData = [];
        $amountCount = $receivableAmount;
        foreach ($repaymentPlans as $repaymentPlan) {
            $repaymentNeedRepay = $order->repayAmount($repaymentPlan);

            $amount = min($amountCount, $repaymentNeedRepay);
            $detailData[] = [
                'business_id' => $repaymentPlan->id,
                'business_no' => $repaymentPlan->no,
                'amount' => $amount,
                'flag' => $isFinalRepay ? TradeLogDetail::FLAG_IS_FINAL_REPAY : null,
            ];
            $amountCount -= $amount;
        }

        $tradeLog = TradeLog::model()->add($params, $detailData);

        return $tradeLog;
    }

}
