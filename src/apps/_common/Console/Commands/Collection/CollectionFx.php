<?php

namespace Common\Console\Commands\Collection;

use Common\Console\Services\Collection\CollectionAssignServer;
use Illuminate\Console\Command;
use Common\Services\Third\FeixiangServer;
use Common\Models\Order\Order;

class CollectionFx extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:fx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '飞象催收手动推送';

    public function handle() {
        CollectionAssignServer::server()->feixiangPush();
    }

}
