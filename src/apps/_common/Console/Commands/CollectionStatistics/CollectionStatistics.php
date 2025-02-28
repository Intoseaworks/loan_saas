<?php

namespace Common\Console\Commands\CollectionStatistics;

use Carbon\Carbon;
use Common\Services\CollectionStatistics\CollectionStatisticsServer;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class CollectionStatistics extends Command
{
    /** 类型：催收订单统计 */
    const TYPE_STATISTICS = 'statistics';
    /** 类型：催回率统计 */
    const TYPE_STATISTICS_RATE = 'statistics_rate';
    /** 类型：催收员每日统计 */
    const TYPE_STATISTICS_STAFF = 'statistics_staff';
    
    const TYPE_STATISTICS_ONLINE = 'statistics_online';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection-statistics:statistics {--is_today} {--type=} {--merchantId=} {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '催收统计：催收订单统计';

    /**
     * @var Carbon
     */
    protected $date;

    public function handle()
    {
        $date = Carbon::yesterday();
        if ($this->option('is_today')) {
            $date = Carbon::today();
        }
        if ($this->option('date')) {
            $date = Carbon::parse($this->option('date'));
        }
        $this->date = $date;

        $merchantId = $this->option('merchantId');

        MerchantHelper::callback(function ($merchant) {
            $this->handleItem();
        }, $merchantId);

//        $merchants = Merchant::model()->getNormalAll();
//
//        foreach ($merchants as $merchant) {
//            MerchantHelper::$merchantId = $merchant->id;
//
//            $this->handleItem();
//        }
    }

    protected function handleItem()
    {
        $type = $this->option('type');

        if (!is_null($type)) {
            switch ($type) {
                case self::TYPE_STATISTICS:
                    $this->statistics();
                    break;
                case self::TYPE_STATISTICS_RATE:
                    $this->statisticsRate();
                    break;
                case self::TYPE_STATISTICS_STAFF:
                    $this->statisticsStaff();
                    break;
                case self::TYPE_STATISTICS_ONLINE:
                    $this->statisticsOnline();
                    break;
                default:
                    throw new \Exception('类型错误');
            }
        } else {
            //催收订单统计
            $this->statistics();

            //催回率统计
            $this->statisticsRate();

            //催收员每日统计
            $this->statisticsStaff();
        }
    }

    /**
     * 催收订单统计
     */
    protected function statistics()
    {
        CollectionStatisticsServer::server()->statistics($this->date);

        $this->info('催收订单统计完成');
    }

    /**
     * 催回率统计
     */
    protected function statisticsRate()
    {
        CollectionStatisticsServer::server()->statisticsRate($this->date);

        $this->info('催回率统计完成');
    }

    /**
     * 催收员每日统计
     */
    protected function statisticsStaff()
    {
        CollectionStatisticsServer::server()->statisticsStaff($this->date);

        $this->info('催收员每日统计完成');
    }
    
    protected function statisticsOnline(){
        CollectionStatisticsServer::server()->statisticsOnline();

        $this->info('催收员Online统计完成');
    }
}
