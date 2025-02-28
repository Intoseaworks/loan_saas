<?php

namespace Common\Console\Commands\Statistics;

use Common\Console\Services\Statistics\History\StatisticsDataHistoryServer;
use Common\Models\Merchant\Merchant;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class StatisticsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:data {startDate?} {endDate?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计记录';

    public function handle()
    {
        $startDate = $this->argument('startDate');
        $endDate = $this->argument('endDate');

        $merchants = Merchant::model()->getNormalAll();

        foreach ($merchants as $merchant) {
            MerchantHelper::setMerchantId($merchant->id);

            StatisticsDataHistoryServer::server()->history($startDate, $endDate);
        }
    }

}
