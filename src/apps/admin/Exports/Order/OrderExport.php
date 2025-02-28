<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Order;

use Admin\Models\Order\Order;
use Admin\Models\User\User;
use Common\Utils\Data\StringHelper;
use Common\Utils\Export\AbstractExport;

class OrderExport extends AbstractExport
{
    /**
     * 借款订单
     */
    const SCENE_BORROW = 'SCENE_BORROW';
    /**
     * 待签约
     */
    const SCENE_SUPER_NOT_SIGN_ORDER = 'SCENE_SUPER_NOT_SIGN_ORDER';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_BORROW => [
                'user.telephone' => '手机号码',
                'user.fullname' => '真实姓名',
                'quality_text' => '用户类型',
                'order_no' => '订单号',
                'principal' => '借款金额',
                'loan_days' => '借款期限',
                'processing_fee_incl_gst' => '手续费(包含GST)',
                'paid_amount' => '实际到账金额',
                'created_at' => '订单创建时间',
                'app_client_text' => '版本号',
                'updated_at' => '业务处理时间',
                'status_text' => '订单进展状态',
            ],
            static::SCENE_SUPER_NOT_SIGN_ORDER => [
                'user.telephone' => '手机号码',
                'user.fullname' => '真实姓名',
                'created_at' => '订单创建时间',
                'pass_time' => '订单通过时间',
                'status' => '订单进展状态',
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
        if ($this->sence == static::SCENE_BORROW) {
            $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
            $data->user && $data->user->setScenario(User::SCENARIO_INFO)->getText();
            $data->setScenario(Order::SCENARIO_LIST)->getText();
            $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
            unset($data->lastRepaymentPlan);
        }
        if($this->sence != static::SCENE_SUPER_NOT_SIGN_ORDER){
            //$data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
        }
    }
}
