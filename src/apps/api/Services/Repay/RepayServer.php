<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Repay;

use Admin\Services\Repayment\RepaymentPlanServer;
use Api\Models\Order\Order;
use Api\Models\Order\OrderDetail;
use Api\Models\Trade\TradeLog;
use Api\Models\User\User;
use Api\Services\BaseService;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Libraries\PayChannel\Fawry\RepayHelper;
use Common\Models\Config\Config;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Repay\RepayDetail;
use Common\Services\Order\OrderPayServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Common\Models\BankCard\BankCardRepay;

class RepayServer extends BaseService
{
    //变更到还款中失败
    const REPAYMENT_PLAN_TO_REPAYING_FAIL = 13000;
    //还款中
    const REPAYMENT_PLAN_IS_REPAYING = 13000;
    //没有银生宝协议编号???
    const REPAYMENT_PLAN_NO_YSB = 13000;

    public function mode($param)
    {
        /** @var $user User */
        $channelData = [];

        foreach (Config::model()->sysRepayPlatform() as $key => $item) {
            $channelData[] = [
                'id' => $key,
                'text' => array_get(TradeLog::TRADE_PLATFORM, $item, $item),
            ];
        }
        return $channelData;
    }

    public function canRepay($orderId, $userId)
    {
        return Order::query()
            ->where([
                'id' => $orderId,
                'user_id' => $userId,
            ])
            ->whereIn('status', Order::WAIT_REPAYMENT_STATUS)
            ->first();
    }

    /**
     * 执行代扣
     * @param $order
     * @return bool|\Common\Models\Trade\TradeLog|null
     */
    public function daikou($order)
    {
        return OrderPayServer::server()->daikou($order, false);
    }

    /**
     * 根据 trade_log_id 查询交易结果
     * @param $tradeLogId
     * @param $where
     * @return array|\Common\Models\Trade\TradeLog|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function queryTrade($tradeLogId, $where = [])
    {
        $tradeLog = TradeLog::query()
            ->where('id', $tradeLogId)
            ->where($where)
            ->first();

        if (!$tradeLog) {
            return [];
        }

        return $tradeLog->setScenario(TradeLog::SCENARIO_LIST)->getText();
    }

    /**
     * @param $method
     * @param $orderId
     * @param $repayAmount
     * @param $payerAccount
     * @param bool $isApp
     * @return RepayServer|void
     * @throws \Common\Exceptions\ApiException
     */
    public function repay($method, $orderId, $repayAmount = null, $payerAccount = null, $repayMethod = 'referenceNumber')
    {
        /** @var User $user */
        $user = Auth::user();
        $order = $user->orders->where('id', $orderId)->first();
        if (!in_array($method, TradeLog::TRADE_PLATFORM_HAS_REPAY)) {
            $this->outputException(t('对应支付方式不存在'));
        }

        if (empty($order)) {
            return $this->output(self::REPAYMENT_PLAN_TO_REPAYING_FAIL, '(' . $user->id . ')' . t('当前计划有误-请稍后') . '(' . $orderId . ')');
        }

        //判断状态
        if ($order->status == Order::STATUS_REPAYING) {
            return $this->output(self::REPAYMENT_PLAN_IS_REPAYING, t('当前应还金额有部分处于还款中,请稍后'));
        }

        if (!in_array($order->status, Order::WAIT_REPAYMENT_STATUS)) {
            return $this->output(self::REPAYMENT_PLAN_NO_YSB, t('当前不支持还款,请联系客服'));
        }

        $needRepaymentPlan = RepaymentPlan::getNeedRepayRepaymentPlans($order);
        if ($needRepaymentPlan->isEmpty()) {
            return $this->outputException("当前订单不存在应还还款计划");
        }

        $result = OrderPayServer::server()->payment($order, $method, $repayAmount, $payerAccount, null, $repayMethod);

        if (!$result) {
            return $this->outputError(t('操作失败，稍后再试'));
        }

        return $this->outputSuccess('success', $result);
    }

    public function tradeList($orderId)
    {
        $tradeLogs = TradeLog::model()->where("master_related_id", $orderId)
            ->where("business_type", TradeLog::BUSINESS_TYPE_REPAY)
            ->orderByDesc("id")
            ->get();
        $res = [];
        foreach ($tradeLogs as $tradeLog) {
            $res[] = [
                "transaction_no" => $tradeLog->transaction_no,
                "repay_no" => $tradeLog->request_no,
                "created_at" => $tradeLog->created_at->toDateTimeString(),
                "status" => $tradeLog->trade_result == 1 ? "PAID" : "UNPAID",
                "business_amount" => $tradeLog->business_amount,
                "amount" => $tradeLog->trade_amount ?: $tradeLog->business_amount,
            ];
        }
        return $res;
    }

    /**
     * @param $trade
     * @return bool
     * @throws \Exception
     */
    public function checkTrade($trade)
    {
        if ($trade) {
            $platformResult = RepayHelper::helper()->pull($trade->transaction_no);
            if (isset($platformResult['orderStatus']) && $platformResult['orderStatus'] == "PAID") {
                if (!RepayDetail::model()->where("trade_id", $trade->id)->exists()) {
                    $trade->evolveStatusOverResultSuccess($platformResult['fawryRefNumber'], DateHelper::dateTime(), DateHelper::dateTime(), $platformResult['paymentAmount']);
                    MerchantHelper::helper()->setMerchantId($trade->merchant_id);
                    DB::beginTransaction();
                    $repaymentPlan = $trade->order->lastRepaymentPlan;
                    \Common\Services\Repay\RepayServer::server($repaymentPlan, $trade)->completeRepay();
                    DB::commit();
                    return true;
                } else {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function addRepayBank($attributes){
        return BankCardRepay::model()->createModel($attributes);
    }
    
    public function getRepayBank($userId){
        return BankCardRepay::model()->where("user_id", $userId)
                ->where("status", BankCardRepay::STATUS_ACTIVE)
                ->get();
    }
    
    public function removeRepayBank($userId, $id){
        return BankCardRepay::model()->where("user_id", $userId)
                ->where("id", $id)->update(["status" => BankCardRepay::STATUS_DELETE]);
    }
}
