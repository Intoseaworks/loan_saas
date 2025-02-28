<?php

namespace Common\Console\Commands\Statistics;

use Common\Console\Services\Statistics\History\StatisticsLogHistoryServer;
use Common\Models\Merchant\Merchant;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class StatisticsLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:log {startTime?} {endTime?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计记录';

    public function handle()
    {
        $startTime = $this->argument('startTime');
        $endTime = $this->argument('endTime');

        $merchants = Merchant::model()->getNormalAll();

        foreach ($merchants as $merchant) {
            MerchantHelper::setMerchantId($merchant->id);

            StatisticsLogHistoryServer::server()->history($startTime, $endTime);
        }
    }

}
