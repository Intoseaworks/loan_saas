<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/13
 * Time: 12:11
 */

namespace Common\Console\Commands\Order;


use Common\Console\Services\Order\OrderBadServer;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class FlowCollectionBad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:flow-collection-bad';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '流转坏帐状态';

    public function handle()
    {
        MerchantHelper::callback(function () {
            OrderBadServer::server()->orderToBad();
        });
    }
}
