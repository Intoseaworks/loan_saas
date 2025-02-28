<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\TradeManage;

use Admin\Models\Trade\TradeLog;
use Common\Services\Order\OrderServer;
use Common\Utils\Export\AbstractExport;

class TradeLogExport extends AbstractExport
{
    /**
     * 支付记录
     */
    const SCENE_TRADE_LOG = 'SCENE_TRADE_LOG';
    /**
     * 系统放款记录
     */
    const SCENE_SYSTEM_PAY_LIST = 'system_pay_list';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_TRADE_LOG => [
                'trade_account_telephone' => '手机号码',
                'trade_account_name' => '收款人姓名',
                'master_business_no' => '订单号',
                'request_no' => '商户交易编号',
                'trade_platform_no' => '支付平台交易编号',
                'trade_amount' => '金额',
                'trade_account_no' => '银行卡号',
                'bankCard.bank_name' => '银行名称',
                'bankCard.bank_branch_name' => '支行名称',
                'bankCard.ifsc' => 'IFSC Code',
                [
                    'name' => 'State/ City',
                    'value' => function ($data) {
                        if (!$data->bankCard || !$data->bankCard->province) {
                            return '';
                        }
                        return $data->bankCard->province . '/ ' . $data->bankCard->city;
                    },
                ],
                'out_trade_result_time' => '打款时间',
                'in_trade_result_time' => '还款时间',
                'business_type_text' => '类型',
                'trade_platform_text' => '支付方式',
                'trade_result_text' => '状态',
            ],
            static::SCENE_SYSTEM_PAY_LIST => [
                'trade_account_telephone' => '手机号码',
                'trade_account_name' => '收款人姓名',
                'master_business_no' => '订单号',
                'transaction_no' => '交易编号',
                'order.principal' => '借款金额',
                'order.processing_fee' => '手续费',
                'order.gst_processing_fee' => 'GST',
                'trade_amount' => '出款金额',
                'trade_account_no' => '收款银行卡',
                'trade_result_time' => '打款时间',
                'trade_platform_text' => '支付方式',
                'trade_result_text' => '状态',
                'trade_desc' => '描述',
            ],
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $model
     * @return mixed|void
     */
    protected function beforePutCsv($model)
    {
        if ($this->sence == static::SCENE_TRADE_LOG) {
            /** @var TradeLog $data */
            $model->out_trade_result_time = '';
            $model->in_trade_result_time = '';
            $model->renewal_result_time = '';
            if ($model->trade_result != TradeLog::TRADE_RESULT_NULL) {
                if ($model->business_type == TradeLog::BUSINESS_TYPE_MANUAL_REMIT) {
                    $model->out_trade_result_time = $model->trade_result_time;
                } elseif ($model->business_type == TradeLog::BUSINESS_TYPE_REPAY) {
                    $model->in_trade_result_time = $model->trade_result_time;
                    $model->out_trade_result_time = optional($model->order)->paid_time;
                } elseif ($model->business_type == TradeLog::BUSINESS_TYPE_RENEWAL) {
                    $model->renewal_result_time = $model->trade_result_time;
                }
            }
            $model->setScenario(TradeLog::SCENARIO_LIST)->getText();
        } elseif ($this->sence == static::SCENE_SYSTEM_PAY_LIST) {
            $model->setScenario(TradeLog::SCENARIO_LIST)->getText();

            if ($model->order) {
                $model->order->processing_fee = strval($model->order->getProcessingFee());
                $model->order->gst_processing_fee = strval(OrderServer::server()->getGstProcessingFee($model->order));
            }
        }
    }
}
