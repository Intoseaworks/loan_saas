<?php

namespace Risk\Common\Console\UserAssociated;

use Illuminate\Console\Command;
use Risk\Common\Services\UserAssociated\UserAssociatedRecordServer;

class UserAssociatedRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:user-associated:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '关联账户信息写入';

    public function handle()
    {
        $startTime = date('Y-m-d H:00:00', strtotime('-1 hour'));

        UserAssociatedRecordServer::server(null, $startTime)->handle();
    }
}
