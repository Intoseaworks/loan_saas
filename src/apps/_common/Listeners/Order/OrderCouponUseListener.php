<?php

namespace Common\Listeners\Order;

use Admin\Models\Order\Order;
use Admin\Services\Activity\ActivitiesRecordServer;
use Admin\Services\Coupon\CouponServer;
use Carbon\Carbon;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Events\Order\OrderRemitSuccessEvent;
use Common\Jobs\Push\App\AppByCommonJob;
use Common\Jobs\Push\App\AppByPayScheduleJob;
use Common\Jobs\Push\Sms\SmsByCommonJob;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Coupon\CouponReceive;
use Common\Models\Merchant\App;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Push\Push;
use Common\Utils\Sms\SmsHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Common\Models\Trade\TradeLog;

class OrderCouponUseListener implements ShouldQueue
{
    public $queue = 'order-coupon-use';

    /**
     * @var Order
     */
    protected $order;

    protected $userId;

    protected $highlightColor = '#FF8C00';

    protected $appName;
    protected $url;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function handle(OrderRemitSuccessEvent $event)
    {
        try {
//            $conn = \DB::getDefaultConnection();
//            \DB::setDefaultConnection($conn);
            \DB::reconnect();
            $couponReceive = CouponReceive::model();
            $couponReceive->refresh();
            if (!$receive = $couponReceive->query()
                ->where("order_id",$event->getOrderId())->first()) {
                return true;
            }
            MerchantHelper::clearMerchantId();
            if (!$order = Order::query()->where('id', $event->getOrderId())->first()) {
                throw new \Exception('订单不存在');
            }
            //发放邀请活动成功放款奖励
            if ($order->user->invitedCode){
                ActivitiesRecordServer::server()->awardBonus(1,1,3,$order->merchant_id,Carbon::now()->toDateTimeString(),$order->user->invitedCode->user_id);
            }
            MerchantHelper::setAppId($order->app_id, $order->merchant_id);
            $this->order = $order;
            $checkingInfo = CouponServer::server()->checking($this->order->user,$receive,$this->order);
            //remit放款用券不成功退回优惠券
            if ($checkingInfo !== true){
                $receive->order_id = null;
                $receive->use_time = null;
                $receive->save();
            }
            \Log::info('放款成功使用优惠券消息------'.$checkingInfo.'---'.$event->getOrderId());
            return true;
        } catch (\Exception $e) {
            \Log::error('放款成功使用优惠券异常------'.json_encode([
                    'orderId' => $event->getOrderId(),
                    'merchantId' => MerchantHelper::getMerchantId(),
                    'appId' => MerchantHelper::getAppId(),
                    'user_id' => $this->userId ?? '',
                    'order' => $this->order ?? '',
                    'e' => EmailHelper::warpException($e),
                ]));
            DingHelper::notice([
                'orderId' => $event->getOrderId(),
                'merchantId' => MerchantHelper::getMerchantId(),
                'appId' => MerchantHelper::getAppId(),
                'user_id' => $this->userId ?? '',
                'order' => $this->order ?? '',
                'e' => EmailHelper::warpException($e),
            ], '放款成功使用优惠券异常');
            return false;
        }

    }
}
