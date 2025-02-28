<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Columbia;

use Common\Models\Columbia\ColClockin;
use Common\Models\Order\Order;
use Common\Utils\MerchantHelper;
use Common\Utils\Sms\SmsEgpHelper;
use Illuminate\Console\Command;
use Common\Models\Merchant\App;

class SendAlertSms extends Command {

    protected $signature = 'col:alert {frame}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时提醒打卡';

    public function handle() {
        $timeFrame = $this->argument('frame');
        $frameTime = ColClockin::TIME_FRAME_TITLE[$timeFrame];
        MerchantHelper::setMerchantId(5);
        $dateTime = date("Y-m-d H:i:s", strtotime("-5 day"));
        echo $frameTime;
        echo $dateTime;
        $orderList = Order::model()->whereIn("status", [Order::STATUS_SYSTEM_PAID, Order::STATUS_MANUAL_PAID])->where("paid_time", ">=", $dateTime)->get();
        foreach ($orderList as $order) {
            $appId = App::model()->where("merchant_id", $order->merchant_id)->get()->first()->id;
            $sendId = App::getDataById($appId, 'send_id');
            $content = "Please clock in between $frameTime time slots";
            SmsEgpHelper::helper()->sendMarketing($order->user->telephone, $content, [], $sendId);
            echo $order->user->telephone . " SENDED" . PHP_EOL;
        }
    }

}
