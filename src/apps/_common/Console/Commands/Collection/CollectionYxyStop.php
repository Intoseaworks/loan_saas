<?php

namespace Common\Console\Commands\Collection;

use Common\Models\Collection\Collection;
use Common\Services\Third\YixiuyunServer;
use Illuminate\Console\Command;
use Common\Models\Order\Order;

class CollectionYxyStop extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:yxy:stop {adminId?} {--orderIds=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '一休云催收手动推送 collection:yxy:stop {adminId} {--orderIds=}';

    public function handle() {
        $adminId = $this->argument('adminId');
        if (!$orderIds = $this->option('orderIds')) {
            if (!$adminId) {
                $adminId = YixiuyunServer::server()->adminIds;
            } else {
                $adminId = [$adminId];
            }
            while (1) {
                $date = date("Y-m-d");

                $query = Collection::model()->newQuery();
                $query->whereIn('admin_id', $adminId);
                $query->whereIn('status', Collection::STATUS_COMPLETE);
                $query->where('finish_time', ">", $date);
                $collections = $query->get();
                print_r("{$date}处理数" . $collections->count() . PHP_EOL);
                foreach ($collections as $collection) {
                    $res = YixiuyunServer::server()->checkAdminId($collection->admin_id)->stop_agent_coll($collection);
                    print_r($res);
                }
                echo 'loop:' . date("H:i:s") . PHP_EOL;
                sleep(60 * 5);
            }
        } else {
            if (!$orderIdsArr = explode(',', $orderIds)) {
                $this->line('orderIds格式不正确，举例--orderIds=1,2,3');
                exit;
            } else {
                $this->collectionByOrderId($adminId, $orderIdsArr);
            }
        }
    }

    public function collectionByOrderId($adminId, $orderIds) {
        foreach ($orderIds as $orderId) {
            $this->line('推送[' . $adminId . ']订单：' . $orderId);
            $order = Order::model()->getOne($orderId);
            $res = YixiuyunServer::server()->checkAdminId($adminId)->stop_agent_coll($order->collection);
            var_dump($res);
        }
    }

}
