<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/31
 * Time: 15:42
 */

namespace Common\Console\Commands\HelperRun;


use Common\Events\Order\OrderFlowPushEvent;
use Common\Models\Order\Order;
use Common\Services\Order\OrderServer;
use Common\Utils\Email\EmailHelper;
use Illuminate\Console\Command;

class OrderPassPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:pass_push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '通过订单推送';

    public function handle()
    {
        $this->passPush();
    }

    protected function passPush()
    {
        $orders = Order::query()->whereIn('status', [Order::STATUS_MANUAL_PASS]);
        foreach ($orders->get() as $order) {
            event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_APPROVE_PASS));
            echo "push:{$order->id}".PHP_EOL;
        }
        exit();
    }
}
