<?php

namespace Common\Console\Commands\Collection;

use Illuminate\Console\Command;
use Common\Console\Services\Collection\CollectionAssignServer;

class CollectionYxyPush extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:yxy:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '一休云催收手动推送';

    public function handle() {
        CollectionAssignServer::server()->yixiuyunPushList();
    }

}
