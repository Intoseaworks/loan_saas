<?php

namespace Risk\Common\Services\SystemApproveData;

use Carbon\Carbon;
use Common\Services\BaseService;
use Risk\Common\Models\Business\Order\Order;

class OrderCheckServer extends BaseService
{
    /**
     * 获取指定日期的订单创建数(默认当天)
     * @param null $quality
     * @param null $date
     * @param bool $returnQuery
     * @return Order|\Illuminate\Database\Eloquent\Builder|int
     */
    public function getDailyCreateOrderCount($quality = null, $date = null, $returnQuery = false)
    {
        $dateCarbon = Carbon::today();
        if (!is_null($date)) {
            $dateCarbon = Carbon::parse($date);
        }
        $createdTimeStart = $dateCarbon->toDateString();
        $createdTimeEnd = $dateCarbon->addDay()->toDateString();

        $tableName = (new Order())->getTable();
        $query = Order::query();
        $query->whereBetween($tableName . '.created_at', [$createdTimeStart, $createdTimeEnd]);
        if (!is_null($quality)) {
            $query->whereQuality($quality);
        }

        if ($returnQuery) {
            return $query;
        }

        return $query->count();
    }
}
