<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Test;

use Admin\Models\Order\Order;
use Admin\Models\User\User;
use Admin\Services\BaseService;
use Admin\Services\Collection\CollectionServer;
use Admin\Services\Repayment\ManualRepaymentServer;
use Api\Services\Order\OrderServer;
use Carbon\Carbon;
use Common\Models\Order\OrderDetail;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Trade\TradeLog;
use Common\Utils\Curl;
use Common\Utils\Data\DateHelper;
use DB;

class TestOrderServer extends BaseService
{

    const TYPE_SYSTEM_PASS = 'systemPass';
    const TYPE_SYSTEM_TO_MANUAL = 'systemToManual';
    const TYPE_MANUAL_PASS = 'manualPass';
    const TYPE_MANUAL_TO_CALL = 'manualToCall';
    const TYPE_CANCEL = 'cancel';
    const TYPE_SIGN = 'sign';
    const TYPE_REJECT = 'reject';
    const TYPE_CLEAR_REJECT = 'clearReject';

    const TYPE = [
        self::TYPE_SYSTEM_PASS => '机审通过',
        self::TYPE_SYSTEM_TO_MANUAL => '机审转人审',
        self::TYPE_MANUAL_PASS => '人审通过',
        self::TYPE_MANUAL_TO_CALL => '人审转电审',
        self::TYPE_CANCEL => '订单取消',
        self::TYPE_SIGN => '订单签约',
        self::TYPE_REJECT => '订单拒绝',
        self::TYPE_CLEAR_REJECT => '清除订单拒绝',
    ];

    public function statusUpdate($data)
    {
        $type = array_get($data, 'type');
        if (!$type) {
            return $this->outputException('please input type');
        }
        call_user_func([$this, $type], $data);
    }

    public function statusCreate($data)
    {
        $this->createOrder($data);
        throw new \Exception('机审流程变更，需重新改写');
        $this->systemPass($data);
        $this->manualPass($data);
        $this->sign($data);
    }

    public function createOrder($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        $params = [
            'token' => $user->id,
            'position' => '{}',
            'client_id' => 'test',
            'loan_reason' => 'test',
        ];
        $result = Curl::post($params, 'http://saas.dev.indiaox.in/app/order/create');
        return $this->outputSuccess();
    }

    /**
     * @param User $user
     * @return TestOrderServer
     */
    public function systemPass($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order || $user->order->status != Order::STATUS_WAIT_SYSTEM_APPROVE) {
            return $this->outputException('无待机审订单');
        }

//        if (!SystemApproveServer::server()->approve($user->order)) {
//            return $this->outputException('订单通过失败');
//        }
        return $this->outputSuccess('订单通过成功');
    }

    public function systemToManual($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order || $user->order->status != Order::STATUS_WAIT_SYSTEM_APPROVE) {
            return $this->outputException('无待机审订单');
        }
//        if (!OrderServer::server()->systemApproving($user->order->id)
//            || !OrderServer::server()->systemToManual($user->order->id)) {
//            return $this->outputException('订单通过失败');
//        }
        return $this->outputException('ffff');
        return $this->outputSuccess('订单通过成功');
    }

    public function manualToCall($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order || !in_array($user->order->status, [Order::STATUS_WAIT_MANUAL_APPROVE])) {
            return $this->outputException('无待人审订单');
        }
        if (!OrderServer::server()->manualToCall($user->order->id)) {
            return $this->outputException('订单通过失败');
        }
        return $this->outputSuccess('订单通过成功');
    }

    /**
     * @param User $user
     * @return TestOrderServer
     */
    public function manualPass($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order || !in_array($user->order->status, array_merge(Order::APPROVAL_PENDING_STATUS, Order::APPROVAL_CALL_STATUS))) {
            return $this->outputException('无待人审订单');
        }
        if (!OrderServer::server()->manualPass($user->order->id)) {
            return $this->outputException('订单通过失败');
        }
        return $this->outputSuccess('订单通过成功');
    }

    /**
     * @param $param
     * @return mixed
     */
    public function cancel($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order) {
            return $this->outputException('无进行中订单');
        }
        if (!OrderServer::server()->manualCancel($user->order, $user->order->status)) {
            return $this->outputException('订单取消失败');
        }
        return $this->outputSuccess('订单取消成功');
    }

    /**
     * @param $param
     * @return mixed
     */
    public function reject($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order || !in_array($user->order->status, Order::APPROVAL_PENDING_STATUS)) {
            return $this->outputException('无可拒绝订单');
        }
        if (!OrderServer::server()->systemReject($user->order->id)) {
            return $this->outputException('订单拒绝失败');
        }
        return $this->outputSuccess('订单拒绝成功');
    }

    /**
     * @param $param
     * @return mixed
     */
    public function clearReject($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order || !in_array($user->order->status, [Order::APPROVAL_REJECT_STATUS])) {
            return $this->outputException('无可清除拒绝订单');
        }
        if (OrderDetail::model()->setValueByKey($user->order, OrderDetail::KEY_REJECTED_DAYS, 0)) {
            return $this->outputException('清除拒绝失败');
        }
        return $this->outputSuccess('清除拒绝成功');
    }

    /**
     * @param User $user
     * @return TestOrderServer
     */
    public function sign($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order || !in_array($user->order->status, Order::WAIT_SIGN)) {
            return $this->outputException('无待签约订单');
        }
        if (!OrderServer::server()->sign($user)) {
            return $this->outputException('订单签约失败');
        }
        return $this->outputSuccess('订单签约成功');
    }

    /**
     * @param $user User
     * @param $day
     * @return TestOrderServer
     */
    public function overdue($user, $day)
    {
        if (!$user->order) {
            return $this->outputException('无进行中订单');
        }
        if (!in_array($user->order->status, Order::WAIT_REPAYMENT_STATUS)) {
            return $this->outputException('订单非待还款状态');
        }
        if (!$user->order->firstProgressingRepaymentPlan) {
            return $this->outputException('待还还款计划不存在');
        }
        if ($user->order->firstProgressingRepaymentPlan->installment_num == 2) {
            return $this->outputException('尾期还款计划不能逾期');
        }
//        if (!$user->order->tradeLogRemitSuccess) {
//            return $this->outputException('放款记录不存在');
//        }
        $paidTime = DateHelper::subDaysTime($user->order->getLoanUnexpiredDays() + $day);
        DB::beginTransaction();
        # 订单逾期 //改用 Artisan 执行
//        $orderUpdate = $user->order->setScenario([
//            'status' => Order::STATUS_OVERDUE,
//            'paid_time' => $paidTime,
//            'overdue_time' => DateHelper::subDaysTime($day - 1),
//        ])->save();
        # 还款计划逾期
        $appointmentPaidTime = DateHelper::subDaysTime($day);
        $repaymentPlanUpdate = $user->order->lastRepaymentPlan->setScenario([
            'overdue_days' => $day > 0 ? $day : 0,
            'appointment_paid_time' => $appointmentPaidTime,
            'updated_at' => DateHelper::dateTime(),
        ])->save();

        $lastInstallmentOverdueDays = ($day + $user->order->loan_days) - 60;
        $lastInstallmentOverdueDays = $lastInstallmentOverdueDays > 0 ? $lastInstallmentOverdueDays : 0;
        $user->order->lastInstallmentRepaymentPlan && $user->order->lastInstallmentRepaymentPlan->setScenario([
            'overdue_days' => $lastInstallmentOverdueDays,
            'appointment_paid_time' => DateHelper::addDays($user->order->lastInstallmentRepaymentPlan->repay_days, 'Y-m-d H:i:s', $appointmentPaidTime),
            'updated_at' => DateHelper::dateTime(),
        ])->save();

        # 放款时间调整
        $tradeLogRemitUpdate = $user->order->setScenario([
            'paid_time' => $paidTime,
        ])->save();

        if ($day > 0) {
            OrderServer::server()->beOverdue($user->order->id);
            $newOrderStatus = $user->order->refresh()->status;
            if ($newOrderStatus == Order::STATUS_OVERDUE && $repaymentPlanUpdate && $tradeLogRemitUpdate) {
                DB::commit();
                return $this->outputSuccess("订单逾期{$day}天成功");
            }
            DB::rollBack();
            return $this->outputException("订单逾期{$day}天失败");
        }
        if ($repaymentPlanUpdate && $tradeLogRemitUpdate) {
            DB::commit();
            return $this->outputSuccess("订单到期前{$day}天成功");
        }
        DB::rollBack();
        return $this->outputException("订单到期前{$day}天失败");
    }

    /*public function useLoanDays($user, $day)
    {
        if (!$user->order) {
            return $this->outputException('无进行中订单');
        }
        if (!in_array($user->order->status, Order::WAIT_REPAYMENT_STATUS)) {
            return $this->outputException('订单非待还款状态');
        }
        if (!$user->order->lastRepaymentPlan) {
            return $this->outputException('还款计划不存在');
        }
        if (!$user->order->tradeLogRemitSuccess) {
            return $this->outputException('放款记录不存在');
        }
        $paidTime = DateHelper::subDaysTime($user->order->getLoanUnexpiredDays() + $day);
        DB::beginTransaction();
        $appointmentPaidTime = DateHelper::subDaysTime($day);
        $repaymentPlanUpdate = $user->order->lastRepaymentPlan->setScenario([
            'overdue_days' => $day,
            'appointment_paid_time' => $appointmentPaidTime,
            'updated_at' => DateHelper::dateTime(),
        ])->save();

        # 放款时间调整
        $tradeLogRemitUpdate = $user->order->setScenario([
            'paid_time' => $paidTime,
        ])->save();

        Artisan::call('order:flow-overdue');
        $newOrderStatus = $user->order->refresh()->status;
        if ($newOrderStatus == Order::STATUS_OVERDUE && $repaymentPlanUpdate && $tradeLogRemitUpdate) {
            DB::commit();
            return $this->outputSuccess("订单借款{$day}天成功");
        }
        DB::rollBack();
        return $this->outputException("订单借款{$day}天失败");
    }*/

    public function orderRepaySuccess($data)
    {
        $user = $this->getUser(array_get($data, 'telephone'));
        if (!$user->order || !in_array($user->order->status, array_merge(Order::WAIT_REPAYMENT_STATUS, [Order::STATUS_REPAYING]))) {
            return $this->outputException('无待还款订单');
        }

        // 生成还款tradeLog
        $order = $user->order;
        $platform = TradeLog::TRADE_PLATFORM_MOBIKWIK;
        $receivableAmount = $order->repayAmount();

        $tradeLogParams = [
            'merchant_id' => $order->merchant_id,
            'trade_platform' => $platform,
            'user_id' => $order->user_id,
            'admin_trade_account_id' => 0,
            'master_related_id' => $order->id,
            'master_business_no' => $order->order_no,
            'trade_type' => TradeLog::TRADE_TYPE_RECEIPTS,
            'request_type' => TradeLog::REQUEST_TYPE_USER,
            'business_type' => TradeLog::BUSINESS_TYPE_REPAY,
            'business_amount' => $receivableAmount,
            'trade_amount' => $receivableAmount,
            'trade_desc' => '测试api还款，非真实交易',
            'trade_request_time' => Carbon::now()->toDateTimeString(),
            'admin_id' => 0,
            'handle_name' => '',
            //'bank_name' => $bankCard->bank_name,
            'trade_account_telephone' => $user->telephone,
            'trade_account_name' => $user->fullname,
            //'trade_account_no' => $bankCard->no,
        ];

        $repaymentPlans = RepaymentPlan::getNeedRepayRepaymentPlans($order);
        $detailData = [];
        foreach ($repaymentPlans as $repaymentPlan) {
            $detailData[] = [
                'business_id' => $repaymentPlan->id,
                'business_no' => $repaymentPlan->no,
                'amount' => $order->repayAmount($repaymentPlan),
            ];
        }

        DB::beginTransaction();
        try {
            $tradeLog = TradeLog::model()->add($tradeLogParams, $detailData);

            // 模拟执行还款成功回调
            $tradeTime = date('Y-m-d H:i:s');
            $tradeNo = uniqid('repay-test') . rand(1000, 9999);
            $result = ManualRepaymentServer::server()->repaySuccess($tradeLog, $tradeNo, $tradeTime, $receivableAmount);
            if (!$result) {
                throw new \Exception('状态流转失败');
            }
            //逾期完结
            CollectionServer::server()->finish($order->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->outputError('订单还款失败！！');
        }

        return $this->outputSuccess('订单还款成功');
    }

    /**
     * @param $key
     * @return \Common\Models\User\User
     * @throws \Common\Exceptions\ApiException
     */
    public function getUser($key)
    {
        if (!($user = User::model()->where('telephone', $key)->orWhere('id', $key)->first())) {
            return $this->outputException('用户不存在');
        }
        return $user;
    }

}
