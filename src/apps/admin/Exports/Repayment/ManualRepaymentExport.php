<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Repayment;

use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Common\Utils\Data\StringHelper;
use Common\Utils\Export\AbstractExport;

class ManualRepaymentExport extends AbstractExport
{
    /**
     * 还款计划
     */
    const SCENE_MANUAL_REPAYMENT_LIST = 'SCENE_MANUAL_REPAYMENT_LIST';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_MANUAL_REPAYMENT_LIST => [
                'order_no' => '订单号',
                'user.fullname' => '收款人姓名',
                'user.telephone' => '手机号码',
                'last_repayment_plan.appointment_paid_time_text' => '应还款日期',
                'status_text' => '还款状态',
                'principal' => '借款金额',
                'overdue_days' => '逾期天数',
                'overdue_fee_incl_gst' => '应还逾期罚息(包含GST)',
                'reduction_fee' => '减免金额（元)',
                'receivable_amount' => '应还本息（元)',
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
        if ($this->sence == static::SCENE_MANUAL_REPAYMENT_LIST) {
            /** @var $data Order */
            $data->setScenario(Order::SCENARIO_LIST)->getText();
            $data->user && $data->user->getText(['telephone', 'fullname']);
            $data->lastRepaymentPlan && $data->lastRepaymentPlan->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
            unset($data->repaymentPlans);
        }
        $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
    }
}
