<?php

namespace Admin\Services\TradeManage;

use Admin\Exports\TradeManage\TradeLogExport;
use Admin\Models\Trade\TradeLog;
use Admin\Services\BaseService;
use Common\Services\Order\OrderServer;
use Common\Services\Pay\BasePayServer;

class TradeLogServer extends BaseService
{
    /**
     * 支付列表
     * @param $params
     * @param $exportScene
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list($params, $exportScene = TradeLogExport::SCENE_TRADE_LOG)
    {
        $with = [
            'bankCard',
            'order',
        ];
        $size = array_get($params, 'size');
        $query = TradeLog::model()->search($params, $with);
        $query->orderByCustom('-created_at');

        if ($this->getExport()) {
            TradeLogExport::getInstance()->export($query, $exportScene);
        }
        $tradeLog = $query->paginate($size);
        /** @var TradeLog $model */ //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($tradeLog as $model) {
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

            if ($model->order) {
                $model->order->processing_fee = strval($model->order->getProcessingFee());
                $model->order->gst_processing_fee = strval(OrderServer::server()->getGstProcessingFee($model->order));
            }
        }
        return $tradeLog;
    }

    /**
     * 系统放款记录
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function systemPayList($params)
    {
        $params['admin_id'] = TradeLog::HANDLER_SYSTEM;
        $params['trade_type'] = TradeLog::TRADE_TYPE_REMIT;
        return $this->list($params, TradeLogExport::SCENE_SYSTEM_PAY_LIST);
    }

    /**
     * 代扣还款记录
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function systemRepayList($params)
    {
        /** 商户可用代付代扣渠道 */
        $autoPayChannel = BasePayServer::server()->getAutoPayChannel();
        $params['trade_platform'] = $autoPayChannel;
        $params['trade_type'] = TradeLog::TRADE_TYPE_RECEIPTS;
        return $this->list($params);
    }
}
