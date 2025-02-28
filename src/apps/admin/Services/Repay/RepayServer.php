<?php

namespace Admin\Services\Repay;

use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Api\Models\User\User;
use Admin\Models\Repay\RepayAccountAdjustment;
use Admin\Models\Repay\RepayDetail;
use Admin\Models\User\UserInfo;
use Admin\Services\Repayment\ManualRepaymentServer;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\LoginHelper;
use Common\Models\Trade\TradeLog;
use DB;

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-15
 * Time: 下午2:25
 */
class RepayServer extends \Common\Services\Repay\RepayServer
{
    /**
     * 还款明细
     * @var array $params
     * @var  $sortDefault
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function repayDetailList($params, $sortDefault = null)
    {
        if (!is_null($sortDefault)) {
            $params['sort'] = $sortDefault;
        }
        $query = RepayDetail::model()->search($params);

        $size  = array_get($params, 'size');
        $datas = $query->paginate($size);
        return $datas;
    }

    /**
     * 撤销还款
     * @param $params
     * @return $this
     */
    public function revoke($params)
    {
        $repayDetail = RepayDetail::model()->getOne($params['id']);
        if ($repayDetail->status != RepayDetail::STATUS_IS_VALID) {
            return $this->outputError('该笔还款已调帐');
        }

        DB::beginTransaction();
        try {
            $data = [
                'remark'                   => $params['remark'],
                'trade_id'                 => $repayDetail->trade_id,
                'repay_detail_id'          => $repayDetail->id,
                'before_repayment_plan_id' => $repayDetail->repayment_plan_id,
                'type'                     => RepayAccountAdjustment::TYPE_IS_REVOKE,
                'admin_id'                 => LoginHelper::getAdminId(),
            ];
            $repayDetail->update(['status' => RepayDetail::STATUS_IS_CANCEL]);
            $adjustment = RepayAccountAdjustment::model()->add($data);

            //还原还款计划 - 根据repayDetail 最后一条记录来处理
            $this->_restoreRepayment($repayDetail->repaymentPlan);

            DB::commit();

            return $adjustment;
        } catch (\Exception $exception) {
            DB::rollBack();

            return $this->outputError($exception->getMessage());
        }
    }

    /**
     * 调账并结清
     * @param $params
     * @return $this|ManualRepaymentServer
     */
    public function completeRepaymentPlan($params)
    {
        $repayDetail = RepayDetail::model()->getOne($params['id']);
        if ($repayDetail->status != RepayDetail::STATUS_IS_VALID) {
            return $this->outputError('该笔还款已调帐');
        }

        $order         = Order::where('reference_no', $params['repayment_plan_no'])->first();
        if(!$order){
            $userInfo = UserInfo::where('dg_pay_lifetime_id', $params['repayment_plan_no'])->first();
            if (!$userInfo) {
                echo "result=no_found_userinfo";
                exit;
            }
            $user = User::model()->getOne($userInfo->user_id);
            $order = $user->order;
        }
        $repaymentPlan = $order->lastRepaymentPlan;
//        $repaymentPlan = RepaymentPlan::getByNo($params['repayment_plan_no']);

        DB::beginTransaction();
        try {
            $data = [
                'remark'                   => $params['remark'],
                'trade_id'                 => $repayDetail->trade_id,
                'repay_detail_id'          => $repayDetail->id,
                'repayment_plan_id'        => $repaymentPlan->id,
                'order_id'                 => $order->id,
                'uid'                      => $order->user_id,
                'before_repayment_plan_id' => $repayDetail->repayment_plan_id,
                'type'                     => RepayAccountAdjustment::TYPE_IS_COMPLETE,
                'admin_id'                 => LoginHelper::getAdminId(),
            ];
            $repayDetail->update(['status' => RepayDetail::STATUS_IS_OFFSET]);
            $adjustment = RepayAccountAdjustment::model()->add($data);
            //还原还款计划 - 根据repayDetail 最后一条记录来处理
            $this->_restoreRepayment($repayDetail->repaymentPlan);

            $subject     = CalcRepaymentSubjectServer::server($repaymentPlan, $repayDetail->trade)->getSubject();
            $repayDetailData = [
                'uid' => $repaymentPlan->user_id,
                'trade_id' => $repayDetail->trade->id,
                'order_id' => $repaymentPlan->order_id,
                'repayment_plan_id' => $repaymentPlan->id,
                'certificate' => $repayDetail->trade->transaction_no,
                'origin_data' => json_encode([
                    'repayment_plan' => $repaymentPlan,
                ]),
                'appointment_paid_time' => $repaymentPlan->appointment_paid_time,
                'actual_paid_time' => $repayDetail->trade->trade_result_time,
                'overdue_days' => $subject->overdueDays,
                'status' => RepayDetail::STATUS_IS_VALID,
                'paid_amount' => $repayDetail->trade->trade_amount,
                'repay_type' => RepayDetail::REPAY_TYPE_REPAY,
                'principal' =>  $repayDetail->trade->trade_amount

            ];

            $repay       = RepayDetail::model(RepayDetail::SCENARIO_CREATE)->add($repayDetailData);

            $repaymentPlanParams = [
                'id'              => $repaymentPlan->order_id, // 此处是订单id !!!
                'remark'          => '调账并结清',
                'repay_name'      => $repaymentPlan->user->fullname,
                'repay_telephone' => $repaymentPlan->user->telephone,
                'repay_account'   => $repaymentPlan->no,
                'repay_time'      => $repayDetail->actual_paid_time,
                'repay_amount'    => $repayDetail->paid_amount,
                'is_part'         => '0',
            ];

            //调用手动还款逻辑
            $result = ManualRepaymentServer::server()->repaySubmit($repaymentPlanParams);
            if ($result->isError()) {
                DB::rollBack();
                return $this->outputError($result->getMsg());
            }
            DB::commit();

            return $result;

        } catch (\Exception $exception) {
            DB::rollBack();

            return $this->outputError($exception->getMessage());
        }
    }

    /**
     * 历史调账记录
     * @var array $params
     * @var  $sortDefault
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function adjustmentList($params, $sortDefault = null)
    {
        if (!is_null($sortDefault)) {
            $params['sort'] = $sortDefault;
        }
        $query = RepayAccountAdjustment::model()->search($params);

        $size  = array_get($params, 'size');
        $datas = $query->paginate($size);

        foreach ($datas as $data) {
            unset($data->user);
        }

        return $datas;
    }

    /**
     * 还原还款 order & repayment_plan
     * 获取repayDetail 最后一条记录 根据里面的 repayment_plan 进行还原
     * @param RepaymentPlan $repaymentPlan
     */
    private function _restoreRepayment($repaymentPlan)
    {
        $lastRepayDetail = RepayDetail::where('repayment_plan_id', $repaymentPlan->id)->orderByDesc('id')->first();

        /** @var RepaymentPlan $beforeRepaymentPlan */
        $beforeRepaymentPlan = json_decode($lastRepayDetail->origin_data)->repayment_plan;
        $beforeOrder         = $beforeRepaymentPlan->order;


        $order = $repaymentPlan->order;

        //还原订单
        $order->status = $beforeOrder->status;

        //还原还款计划
        $repaymentPlan->repay_time            = $beforeRepaymentPlan->repay_time;
        $repaymentPlan->repay_channel         = $beforeRepaymentPlan->repay_channel;
        $repaymentPlan->interest_fee          = $beforeRepaymentPlan->interest_fee;
        $repaymentPlan->overdue_fee           = $beforeRepaymentPlan->overdue_fee;
        $repaymentPlan->principal             = $beforeRepaymentPlan->principal;
        $repaymentPlan->gst_processing        = $beforeRepaymentPlan->gst_processing;
        $repaymentPlan->gst_penalty           = $beforeRepaymentPlan->gst_penalty;
        $repaymentPlan->overdue_days          = $beforeRepaymentPlan->overdue_days;
        $repaymentPlan->ost_prncp             = $beforeRepaymentPlan->ost_prncp;
        $repaymentPlan->status                = $beforeRepaymentPlan->status;
        $repaymentPlan->appointment_paid_time = $beforeRepaymentPlan->appointment_paid_time;
        $repaymentPlan->interest_start_time   = $beforeRepaymentPlan->interest_start_time;
        $repaymentPlan->repay_amount          = $beforeRepaymentPlan->repay_amount;

        $repaymentPlan->save();
        $order->save();
    }

    /**
     * 只调账 - 不做其他逻辑处理
     * @param $params
     * @return $this|bool
     */
    public function adjustmentOnly($params)
    {
        $repayDetail = RepayDetail::model()->getOne($params['id']);
        if ($repayDetail->status != RepayDetail::STATUS_IS_VALID) {
//            return $this->outputError('该笔还款已调帐');
        }

        // 要调入的还款计划 ()
        $order               = Order::where('reference_no', $params['repayment_plan_no'])->first();
        if(!$order){
            $userInfo = UserInfo::where('dg_pay_lifetime_id', $params['repayment_plan_no'])->first();
            if (!$userInfo) {
                echo "result=no_found_userinfo";
                exit;
            }
            $user = User::model()->getOne($userInfo->user_id);
            $order = $user->order;
        }
        $this->repaymentPlan = $order->lastRepaymentPlan;
//        $this->repaymentPlan = RepaymentPlan::getByNo($params['repayment_plan_no']);
        $this->trade = $repayDetail->trade;


        DB::beginTransaction();
        try {
            $data = [
                'remark'                   => $params['remark'],
                'trade_id'                 => $repayDetail->trade_id,
                'repayment_plan_id'        => $this->repaymentPlan->id,
                'order_id'                 => $order->id,
                'uid'                      => $order->user_id,
                'repay_detail_id'          => $repayDetail->id,
                'before_repayment_plan_id' => $repayDetail->repayment_plan_id,
                'type'                     => RepayAccountAdjustment::TYPE_IS_ADJUSTMENT,
                'admin_id'                 => LoginHelper::getAdminId(),
            ];
            $repayDetail->update(['status' => RepayDetail::STATUS_IS_OFFSET]);
            $adjustment = RepayAccountAdjustment::model()->add($data);

            //调入的还款计划 - 加入repayDetail 数据
            self::completeRepay();

            //还原还款计划 - 根据repayDetail 最后一条记录来处理
            $this->_restoreRepayment($repayDetail->repaymentPlan);

            DB::commit();

            return $adjustment;
        } catch (\Exception $exception) {
            DB::rollBack();

            return $this->outputError($exception->getMessage());
        }
    }
}