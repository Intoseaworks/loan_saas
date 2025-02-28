<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Collection;

use Admin\Models\Collection\Collection;
use Common\Utils\Export\AbstractExport;

class CollectionOrderExport extends AbstractExport
{
    /**
     * 催收订单
     */
    const SCENE_COLLECTION_ORDER_LIST = 'SCENE_COLLECTION_ORDER_LIST';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_COLLECTION_ORDER_LIST => [
                'order.order_no' => '订单号',
                'order.fullname' => '收款人姓名',
                'order.telephone' => '手机号码',
                'order.principal' => '借款金额 (元)',
                'order.loan_days' => '借款期限 (天)',
                'order.appointment_paid_time' => '应还款日期',
                'assign_time' => '分案时间',
                'order.overdue_days' => '逾期天数',
                'order.overdue_fee_incl_gst' => '应还逾期罚息(包含GST)',
                'order.reduction_fee' => '减免金额 (元)',
                'order.receivable_amount' => '应还本息 (元)',
                'level' => '催收等级',
                'staff.username' => '催收员',
                'status_text' => '状态',
                'collection_detail.record_time' => '催记时间',
                'collection_detail.contact' => '联系人',
                'collection_detail.progress_text' => '联系结果',
                'collection_detail.remark' => '催记记录',
                'collection_detail.promise_paid_time' => '承诺还款时间',
                'finish_time' => '催收成功时间',
                'bad_time' => '坏账时间',
                'user_info.address' => '现居住地址',
                'user_info.permanent_address' => '永久居住地址',
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
        if ($this->sence == static::SCENE_COLLECTION_ORDER_LIST) {
            $data->setScenario(Collection::SCENARIO_LIST)->getText();
            $data->user->telephone = \Common\Utils\Data\StringHelper::desensitization($data->user->telephone);
            $data->order->telephone = \Common\Utils\Data\StringHelper::desensitization($data->order->telephone);
        }
    }
}
