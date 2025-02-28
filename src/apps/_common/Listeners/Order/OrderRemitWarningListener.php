<?php

namespace Common\Listeners\Order;

use Common\Events\Order\OrderRemitSuccessEvent;
use Common\Models\Config\Config;
use Common\Models\Trade\TradeLog;
use Common\Services\Message\MessageServer;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderRemitWarningListener implements ShouldQueue
{
    protected $merchantId;

    /**
     * Create the event listener.
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param OrderRemitSuccessEvent $event
     * @return bool
     */
    public function handle(OrderRemitSuccessEvent $event)
    {
        $this->merchantId = $event->getMerchantId();

        if (!$this->merchantId) {
            return false;
        }

        MerchantHelper::setMerchantId($this->merchantId);

        $this->handleWarning();
    }

    public function handleWarning()
    {
        $dailyRemitAmount = TradeLog::getDailyRemitAmount();

        // 最大放款金额
        $dailyLoanAmountMax = Config::model()->getDailyLoanAmountMax();
        // 剩余预警值
        $dailyRemainWarningValue = Config::model()->getWarningDailyRemainLoanAmountValue();

        // 预警值未设置 || 未设置最大放款额 || 设置不合理(预警值>最大)
        if (!$dailyRemainWarningValue || $dailyLoanAmountMax == INF || $dailyRemainWarningValue > $dailyLoanAmountMax) {
            return false;
        }

        $remainAmount = bcsub($dailyLoanAmountMax, $dailyRemitAmount, 2);
        if ($remainAmount > $dailyRemainWarningValue) {
            return false;
        }

        // 判断当前预警值是否已经发送过通知，已发送则不进行发送
        if (!LockRedisHelper::helper()->adminSendWarning(
            $this->merchantId,
            Config::KEY_WARNING_DAILY_REMAIN_LOAN_AMOUNT_VALUE,
            $dailyRemainWarningValue
        )) {
            return false;
        }

        return MessageServer::server()->sendDailyRemainAmountWarningMessage($this->merchantId, $remainAmount);
    }
}
