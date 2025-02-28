<?php

namespace Common\Console\Commands\Collection;

use Common\Models\Collection\Collection;
use Common\Models\Order\Order;
use Admin\Services\Collection\CollectionServer;
use Illuminate\Console\Command;

class CollectionFinish extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:finish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将催收结束的订单结束催收';

    public function handle() {
        $collections = Collection::model()->whereIn('status', Collection::STATUS_NOT_COMPLETE)->get();
        foreach($collections as $collection){
            if(in_array($collection->order->status,Order::FINISH_STATUS)){
                echo "结束订单:{$collection->order_id}";
                CollectionServer::server()->finish($collection->order_id);
            }
        }
    }

}
