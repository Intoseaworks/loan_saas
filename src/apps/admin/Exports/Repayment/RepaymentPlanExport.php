<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Repayment;

use Admin\Models\Collection\Collection;
use Admin\Models\Order\Order;
use Admin\Models\User\User;
use Common\Utils\Data\StringHelper;
use Common\Utils\Export\AbstractExport;

class RepaymentPlanExport extends AbstractExport
{
    /**
     * 还款计划
     */
    const SCENE_REPAYMENT_LIST = 'SCENE_REPAYMENT_LIST';

    /**
     * 已还款订单
     */
    const SCENE_REPAYMENT_PAID_LIST = 'SCENE_REPAYMENT_PAID_LIST';

    /**
     * 已逾期订单
     */
    const SCENE_REPAYMENT_OVERDUE_LIST = 'SCENE_REPAYMENT_OVERDUE_LIST';

    /**
     * 已坏账订单
     */
    const SCENE_REPAYMENT_BAD_DEBT_LIST = 'SCENE_REPAYMENT_BAD_DEBT_LIST';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_REPAYMENT_LIST => [
                'user.telephone' => '手机号码',
                'user.fullname' => '姓名',
                'order.order_no' => '订单号',
                'installment_num' => '还款期数',
                'order.paid_time' => '放款日期',
                'repay_time' => '实际还款日期',
                'appointment_paid_time' => '应还款日期',
                'status_text' => '状态',
                'order.principal' => '借款金额',
                'overdue_days' => '逾期天数',
                'overdue_fee' => '应还逾期罚息(包含GST)',
                'reduction_fee' => '减免金额',
                'principal' => '当期本金',
                'interest_fee' => '当期利息',
                'receivable_amount' => '应还本息',
                'repay_amount' => '实际还款金额',
            ],
            static::SCENE_REPAYMENT_PAID_LIST => [
                'user.telephone' => '手机号码',
                'user.fullname' => '收款人姓名',
                'order_no' => '订单号',
                'principal' => '借款金额',
                'loan_days' => '借款期限',
                'created_at' => '订单创建时间',
                'processing_fee_incl_gst' => '手续费(包含GST)',
                'paid_amount' => '出款金额',
                'overdue_fee_incl_gst' => '逾期罚息(包含GST)',
                'receivable_amount' => '实际还款金额',
                'status_text' => '状态',
            ],
            static::SCENE_REPAYMENT_OVERDUE_LIST => [
                'user.telephone' => '手机号码',
                'user.fullname' => '收款人姓名',
                'order_no' => '订单号',
                'principal' => '借款金额',
                'created_at' => '订单创建时间',
                'processing_fee_incl_gst' => '手续费(包含GST)',
                'paid_amount' => '出款金额',
                'overdue_fee_incl_gst' => '逾期罚息(包含GST)',
                'receivable_amount' => '应还金额',
                'overdue_days' => '逾期天数',
                'collection.level' => '催收等级',
                'collection.staff.username' => '催收员',
            ],
            static::SCENE_REPAYMENT_BAD_DEBT_LIST => [
                'user.telephone' => '手机号码',
                'user.fullname' => '收款人姓名',
                'order_no' => '订单号',
                'principal' => '借款金额',
                'loan_days' => '借款期限',
                'created_at' => '订单创建时间',
                'processing_fee_incl_gst' => '手续费(包含GST)',
                'paid_amount' => '出款金额',
                'receivable_amount' => '应还金额',
                'collection.bad_time' => '订单坏账时间',
                'collection.bad_time_text' => '坏账规则',
            ],
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $data
     * @return mixed|void
     */
    protected function beforePutCsv($data)
    {
        /** @var $data Order */
        $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
        $data->user && $data->user->setScenario(User::SCENARIO_INFO)->getText();
        $data->collection && $data->collection->setScenario(Collection::SCENARIO_SIMPLE)->getText();
        $data->setScenario(Order::SCENARIO_LIST)->getText();
        unset($data->lastRepaymentPlan);
        $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
    }
}
