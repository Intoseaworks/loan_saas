<?php

namespace Common\Console\Commands\Collection;

use Illuminate\Console\Command;
use Common\Services\Third\FeixiangServer;
use Common\Models\Collection\Collection;
use Common\Models\Order\Order;

class CollectionFxStop extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:fx:stop {adminId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '一休云催收手动推送 collection:yxy:stop {adminId} {--orderIds=}';

    public function handle() {
        $adminId = $this->argument('adminId');
        $query = Collection::model()->newQuery();
        $query->where('admin_id', $adminId);
        $query->whereIn('status', Collection::STATUS_COMPLETE);
        $query->where('finish_time', ">", date("Y-m-d"));
        $collections = $query->get();
        print_r($collections->count());
        foreach ($collections as $collection) {
            $res = FeixiangServer::server()->checkAdminId($adminId)->collectionOrderRepayment($collection->order);
            print_r($res);
        }
    }

}
