<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Order;

use Admin\Models\Order\Order;
use Admin\Models\User\UserInfo;
use Common\Utils\Data\StringHelper;
use Common\Utils\Export\AbstractExport;

class ContractExport extends AbstractExport
{
    /**
     * 放款订单
     */
    const SCENE_LOAN = 'SCENE_LOAN';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_LOAN => [
                'user.telephone' => '手机号码',
                'user.fullname' => '真实姓名',
                'quality_text' => '用户类型',
                'order_count' => '借款次数',
                'overdue_order_count' => '逾期次数',
                'userInfo.province' => '省份',
                'userInfo.city' => '市',
                'order_no' => '订单号',
                'principal' => '借款金额',
                'loan_days' => '借款期限',
                'paid_time' => '打款时间',
                'processing_fee_incl_gst' => '手续费(包含GST)',
                'paid_amount' => '放款金额',
                'interest_fee' => '应还息费',
                'overdue_fee_incl_gst' => '应还逾期罚息(包含GST)',
                'receivable_amount' => '应还金额',
                'repay_amount' => '实际还款金额',
                'repay_time' => '实际还款时间',
                'app_client_text' => '版本号',
                'status_text' => '状态',
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
        if ($this->sence == static::SCENE_LOAN) {
            $data->user->telephone = \Common\Utils\Data\StringHelper::desensitization($data->user->telephone);
            $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
            $data->user->getText(['telephone', 'fullname', 'quality']);
            $data->setScenario(Order::SCENARIO_LIST)->getText();

            if ($data->userInfo) {
                $data->userInfo->setScenario(UserInfo::SCENARIO_LIST)->getText();
            }

            $data->order_count = $data->orderFinish ? $data->orderFinish->where('created_at', '<', $data->created_at)->count() + 1 : 1;
            $data->overdue_order_count = $data->orderOverdue ? $data->orderOverdue->count() : 0;
            unset($data->orderFinish, $data->orderOverdue);
        }
        $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
    }
}
