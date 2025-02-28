<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/21
 * Time: 19:22
 */

namespace Common\Services\Order;

use Admin\Models\Order\Order;
use Carbon\Carbon;
use Common\Models\Config\Config;
use Common\Models\Trade\TradeLog;
use Common\Services\BaseService;

/**
 * Class OrderCheckServer
 * @package Common\Services\Order
 */
class OrderCheckServer extends BaseService
{
    /**
     * 日申请笔数策略拦截
     * @param $quality
     * @return bool
     * @throws \Exception
     */
    public function reachMaxCreate($quality)
    {
        $maxCreateNum = Config::model()->getDailyCreateOrderMax($quality);
        $dailyCreateCount = $this->getDailyCreateOrderCount($quality);
        return $dailyCreateCount >= $maxCreateNum;
    }

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

        $query = Order::query();
        $query->whereBetween('order.created_at', [$createdTimeStart, $createdTimeEnd]);
        if (!is_null($quality)) {
            $query->whereQuality($quality);
        }

        if ($returnQuery) {
            return $query;
        }

        return $query->count();
    }

    /**
     * 获取相应状态订单总数数
     * @param null $quality
     * @param null $conditions
     * @param bool $returnQuery
     * @return Order|\Illuminate\Database\Eloquent\Builder|int
     */
    public function getOrderCount($userIn=null,$quality = null, $orderStatusIn=null, $conditions = null, $returnQuery = false)
    {
        $query = Order::query();
        if (!is_null($orderStatusIn)) {
            $query->whereIn('status',$orderStatusIn);
        }
        if (!is_null($userIn)) {
            $query->whereIn('user_id',$userIn);
        }
        if (!is_null($conditions)) {
            $query->where($conditions);
        }
        if (!is_null($quality)) {
            $query->whereQuality($quality);
        }
        if ($returnQuery) {
            return $query;
        }
        return $query->count();
    }

    /**
     * 日最大放款金额策略拦截
     * 配置值为 0，则是任意金额，不拦截
     * (当前单放款金额(申请时判断逻辑则取最大允许额度) + 放款中金额 + 已放款金额) > 后台配置值，则进行拦截
     * @param $paidAmount
     * @return bool
     * @throws \Exception
     */
    public function reachMaxLoanAmount(float $paidAmount)
    {
        $maxLoanAmount = Config::model()->getDailyLoanAmountMax();

        $dayPaidAmount = TradeLog::getDailyRemitAmount();

        return $dayPaidAmount + $paidAmount > $maxLoanAmount;
    }

    /**
     * 判断订单能否续期
     * @param \Common\Models\Order\Order $order
     * @return bool
     */
    public function canRenewalOrder($order)
    {
        if (in_array($order->status, Order::WAIT_REPAYMENT_STATUS) && Config::model()->getLoanRenewalOn()) {
            return true;
        }
        return false;
    }
}
