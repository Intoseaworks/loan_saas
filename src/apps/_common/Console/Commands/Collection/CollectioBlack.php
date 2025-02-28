<?php

namespace Common\Console\Commands\Collection;

use Admin\Services\Collection\CollectionRecordServer;
use Api\Services\User\UserBlackServer;
use Common\Models\Collection\Collection;
use Illuminate\Console\Command;

class CollectioBlack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:black';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '催收黑名单';

    public function handle()
    {
        $collections = Collection::query()->get();
        foreach ($collections as $collection) {
            echo $collection->id.PHP_EOL;
            if(CollectionRecordServer::server()->getUnableContactCount($collection->id) >= 3){
                echo $collection->id.PHP_EOL;
                UserBlackServer::server()->addCannotRegister($collection->user->telephone, 'collection unable contact');
            }
        }
    }

}
