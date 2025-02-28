<?php

namespace Common\Listeners\Order;

use Admin\Services\Message\MessageServer;
use Common\Events\Order\OrderCreateEvent;
use Common\Models\Config\Config;
use Common\Services\Order\OrderCheckServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderRemainWarningListener implements ShouldQueue
{
    /** @var OrderCreateEvent */
    protected $event;
    protected $merchantId;
    protected $quality;

    /**
     * Create the event listener.
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param OrderCreateEvent $event
     * @return bool
     */
    public function handle(OrderCreateEvent $event)
    {
        $this->event = $event;
        $this->merchantId = $event->getMerchantId();
        $this->quality = $event->getQuality();

        if (!$this->merchantId) {
            return false;
        }

        MerchantHelper::setMerchantId($this->merchantId);

        $this->handleWarning();
    }

    public function handleWarning()
    {
        $dailyCreateOrderQuery = OrderCheckServer::server()->getDailyCreateOrderCount($this->quality, null, true);
        $dailyCreateOrderCount = $dailyCreateOrderQuery->count();

        // 最大申请笔数
        $dailyCreateOrderMax = Config::model()->getDailyCreateOrderMax($this->quality);
        // 剩余申请预警值
        $dailyRemainWarningValue = Config::model()->getWarningDailyRemainCreateOrderValue($this->quality);

        // 预警值未设置 || 未设置最大申请数 || 设置不合理(预警值>最大)
        if (!$dailyRemainWarningValue || $dailyRemainWarningValue > $dailyCreateOrderMax) {
            return false;
        }

        $remainCount = bcsub($dailyCreateOrderMax, $dailyCreateOrderCount, 2);
        if ($remainCount > $dailyRemainWarningValue) {
            return false;
        }

        DingHelper::notice([
            'merchantId' => MerchantHelper::getMerchantId(),
            'orderId' => $this->event->getOrderId(),
            'dailyCreateOrderCount' => $dailyCreateOrderCount,
            'dailyCreateOrderMax' => $dailyCreateOrderMax,
            'dailyCreateOrderWaringValue' => $dailyRemainWarningValue,
            'sql' => $dailyCreateOrderQuery->toSql(),
        ], '订单创建余额预警(未排除重复)', DingHelper::AT_SOLIANG);

        // 判断当前[申请数-预警值]是否已经发送过通知，已发送则不进行发送
        if (!LockRedisHelper::helper()->adminSendWarning(
            $this->merchantId,
            Config::KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE,
            $dailyCreateOrderMax . '-' . $dailyRemainWarningValue
        )) {
            return false;
        }
        return MessageServer::server()->sendDailyRemainCreateWarningMessage($this->merchantId, (float)$remainCount,
            $this->quality);
    }
}
