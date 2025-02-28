<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/31
 * Time: 15:42
 */

namespace Common\Console\Commands\Order;


use Common\Models\Order\Order;
use Common\Services\Order\OrderServer;
use Common\Utils\Email\EmailHelper;
use Illuminate\Console\Command;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApproveUserPool;

class OrderCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cancel {orderNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时取消订单';

    public function handle()
    {
        if ($orderNo = $this->argument('orderNo')) {
            echo "Simple GO ";
            $order = Order::model()->getByOrderNo($orderNo);
            if($order){
                echo $orderNo;
                if($order->status != Order::STATUS_MANUAL_CANCEL){
                    OrderServer::server()->manualCancel($order, $order->status);
                    ApprovePool::model()->where("order_id", $order->id)->update(['status'=>ApprovePool::STATUS_CANCEL]);
                    ApproveUserPool::model()->where("order_id", $order->id)->update(['status'=> ApproveUserPool::STATUS_CANCEL]);
                }else{
                    echo '-';
                }
                echo "OK";
            }
        } else {
            $this->cancelByNoSign();
            $this->cancelByNoComplete();
            $this->cancelByNoWithdrawMoney();
        }
    }

    /**
     * 未签约>3天系统取消订单 系统自动取消
     */
    protected function cancelByNoSign()
    {
        /** 未签约>3天订单 */
        $orders = OrderServer::server()->noSignAfterDayOrders();
        $count = $orders->count();
        echo $count.PHP_EOL;
        $success = 0;
        $take = 2000;
        while ( $canelOrders = $orders->take($take)->get() ){
            if ($canelOrders && count($canelOrders)>0){
                foreach ($canelOrders as $order) {
                    echo '处理取消七天订单--'.$order->id.PHP_EOL;
                    //没有证件号码和类型的用户不处理
//                    $user = $order->user;
                    if (OrderServer::server()->systemCancelNoCardNewUser($order, $order->status)) {
                        echo '取消成功订单--'.$order->id.PHP_EOL;
                        $success++;
                    }
//                    if (OrderServer::server()->systemCancel($order, $order->status)) {
//                        echo '取消成功订单--'.$order->id.PHP_EOL;
//                        $success++;
//                    }
                }
            }else{
                break;
            }
        }
        if ($count > 0) {
            EmailHelper::send("成功{$success}/{$count}", '未签约>7天系统取消订单 系统自动取消');
        }
    }

    /**
     * 重新提交材料>5天 系统自动取消
     */
    protected function cancelByNoComplete()
    {
        /** 重新提交材料>5天 */
        $orders = OrderServer::server()->noReplenishAfter5Days();
        $count = $orders->count();
        $success = 0;
        foreach ($orders->get() as $order) {
            if (OrderServer::server()->systemCancel($order, Order::STATUS_REPLENISH)) {
                $success++;
            }
        }
        if ($count > 0) {
            EmailHelper::send("成功{$success}/{$count}", '重新提交材料>5天 系统自动取消');
        }
    }

    /**
     * 线下取款超时7天取消
     */
    protected function cancelByNoWithdrawMoney()
    {
        $orders = OrderServer::server()->noWithdrawMoney();
        $count = $orders->count();
        $success = 0;
        foreach ($orders->get() as $order) {
            if (OrderServer::server()->systemCancel($order, Order::STATUS_PAYING)) {
                $success++;
            }
        }
        if ($count > 0) {
            EmailHelper::send("成功{$success}/{$count}", '线下取款超时7天 系统自动取消');
        }
    }
}
