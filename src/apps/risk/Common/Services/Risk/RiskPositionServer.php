<?php

namespace Risk\Common\Services\Risk;

use Carbon\Carbon;
use Common\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Risk\Common\Models\Business\Order\OrderDetail;
use Risk\Common\Models\Business\UserData\UserPosition;

class RiskPositionServer extends BaseService
{
    /**
     * 小于等于[num]天内，半径[num]内申请订单数
     * @param $order
     * @param $day
     * @param $m
     * @return bool
     */
    public function hasApplyWithinRadiusCount($order, $day, $m)
    {
        $position = (new OrderDetail())->getPosition($order);

        list($longitude, $latitude) = $this->parseDetailValue($position);

        // 可能为NULL或0
        if (!(float)$longitude || !(float)$latitude) {
            // 取不到order_detail取 user_position
            $position = UserPosition::getLastPosition($order->user_id);
            if (!$position || !$position->longitude || !$position->latitude) {
                return false;
            }

            $longitude = $position->longitude;
            $latitude = $position->latitude;
        }

        $orderPositions = $this->getAllPosition($day, $order->created_at, $order->user_id)->pluck('value', 'order_id');

        $count = 0;
        foreach ($orderPositions as $key => $value) {
            list($longitudeItem, $latitudeItem) = $this->parseDetailValue($value);
            if (!(float)$longitudeItem || !(float)$latitudeItem) {
                continue;
            }
            $distance = $this->getDistance($longitude, $latitude, $longitudeItem, $latitudeItem);

            if ($distance <= $m) {
                $count++;
            }
        }

        return $count;
    }

    protected function parseDetailValue($position)
    {
        $positionArr = explode(',', $position);

        if (!$position || count($positionArr) != 2 || !is_numeric($positionArr[0]) || !is_numeric($positionArr[1])) {
            return false;
        }

        return $positionArr;
    }

    /**
     * 获取指定天数内订单位置信息
     * @param $day
     * @param $createDate
     * @param $exceptUserId
     * @return OrderDetail[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllPosition($day, $createDate, $exceptUserId)
    {
        $startDate = Carbon::parse($createDate)->subDays($day)->toDateString();
        $endTime = Carbon::parse($createDate)->addHour()->toDateTimeString();

        $data = OrderDetail::query()
            ->where('key', OrderDetail::KEY_LOAN_POSITION)
            ->whereHas('order', function (Builder $query) use ($exceptUserId) {
                $query->where('user_id', '!=', $exceptUserId);
            })
            ->whereBetween('created_at', [$startDate, $endTime])
            ->get();

        return $data;
    }

    /**
     * 计算两经纬度距离
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return false|float
     */
    public function getDistance($lng1, $lat1, $lng2, $lat2)
    {
        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = M_PI;

        $radLat1 = $lat1 * $PI / 180.0;
        $radLat2 = $lat2 * $PI / 180.0;

        $radLng1 = $lng1 * $PI / 180.0;
        $radLng2 = $lng2 * $PI / 180.0;

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $distance = $distance * $EARTH_RADIUS * 1000;

        return round($distance);
    }

    /**
     * 计算两经纬度距离2
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return float|int
     */
    public function getDistance1($lng1, $lat1, $lng2, $lat2)
    {
        // deg2rad()函数将角度转换为弧度
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1);
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }
}
