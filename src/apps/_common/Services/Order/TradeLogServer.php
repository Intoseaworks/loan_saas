<?php

namespace Common\Services\Order;

use Common\Models\Trade\TradeLog;
use Common\Services\BaseService;

class TradeLogServer extends BaseService
{
    /**
     * 获取交易结果不明确的交易记录
     * @param $whereInPlatform
     * @return TradeLog[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getPayingTrade(array $whereInPlatform = [])
    {
        $where = [
            'trade_evolve_status' => TradeLog::TRADE_EVOLVE_STATUS_TRADING,
            'trade_result' => TradeLog::TRADE_RESULT_NULL,
        ];
        $query = TradeLog::query();

        if ($whereInPlatform) {
            $query->whereIn('trade_platform', $whereInPlatform);
        }

        return $query->where($where)->get();
    }
}
