<?php

namespace Common\Providers;

use Common\Events\Login\LoginEvent;
use Common\Events\Order\OrderAgreementEvent;
use Common\Events\Order\OrderCreateEvent;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Events\Order\OrderRemitSuccessEvent;
use Common\Events\Order\OrderStatusChangeEvent;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Events\User\UserSetClientIdEvent;
use Common\Listeners\Login\LoginListener;
use Common\Listeners\Order\OrderAgreementListener;
use Common\Listeners\Order\OrderCouponUseListener;
use Common\Listeners\Order\OrderFlowPushListener;
use Common\Listeners\Order\OrderRemainWarningListener;
use Common\Listeners\Order\OrderRemitWarningListener;
use Common\Listeners\Order\OrderStatusChangeListener;
use Common\Listeners\Risk\RiskDataSendListener;
use Common\Listeners\User\UserSetClientIdListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Risk\Common\Events\SystemApprove\SystemApproveFinishEvent;
use Risk\Common\Listeners\SystemApprove\ContactLoanAppComparisonListener;
use Risk\Common\Listeners\SystemApprove\RiskAssociatedRecordListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        /**
         * 终端登录事件
         */
        LoginEvent::class => [
            LoginListener::class
        ],
        /**
         * 订单推送事件
         */
        OrderFlowPushEvent::class => [
            OrderFlowPushListener::class,
        ],
        /**
         * 订单状态流转记录日志
         */
        OrderStatusChangeEvent::class => [
            OrderStatusChangeListener::class,
        ],
        /**
         * 订单协议事件
         */
        OrderAgreementEvent::class => [
            OrderAgreementListener::class,
        ],
        /**
         * 登录修改用户client_id事件
         */
        UserSetClientIdEvent::class => [
            UserSetClientIdListener::class,
        ],

        /** 订单创建事件 */
        OrderCreateEvent::class => [
            // 剩余订单申请数预警
            OrderRemainWarningListener::class,
        ],

        /** 出款成功事件 */
        OrderRemitSuccessEvent::class => [
            // 剩余出款金额预警
            OrderRemitWarningListener::class,
            OrderCouponUseListener::class,
            //发放邀请活动成功放款奖励放在上面监听器一起
        ],

        /** NBFC 上报事件 */
//        NbfcReportEvent::class => [
//            NbfcReportListener::class,
//        ],
        /** 风控数据上传事件 */
        RiskDataSendEvent::class => [
            RiskDataSendListener::class,
        ],

        /** 机审完成事件 */
        SystemApproveFinishEvent::class => [
            ContactLoanAppComparisonListener::class,
            RiskAssociatedRecordListener::class,
        ],
    ];
}
