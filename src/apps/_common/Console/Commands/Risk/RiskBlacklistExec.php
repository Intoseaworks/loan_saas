<?php

namespace Common\Console\Commands\Risk;

use Common\Services\Risk\RiskBlacklistServer;
use Illuminate\Console\Command;

class RiskBlacklistExec extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'risk:blacklist';

    /**
     * The console command description.
     * @var string
     */
    protected $description = '风控：黑名单';

    public function handle()
    {
        $this->overdueAddBlacklist();
    }

    /**
     * 风控逾期入黑
     */
    protected function overdueAddBlacklist()
    {
        return RiskBlacklistServer::server()->overdueAddBlacklist();
    }
}
