<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Nbfc;

use Common\Models\Order\Order;
use Illuminate\Console\Command;
use Common\Models\Nbfc\NbfcReportConfig;
use Common\Services\Report\NbfcReportServer;

class Reporting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nbfc:reporting {orderId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '手动上报nbfc';

    public function handle()
    {
        /** 代付放款 */
        $orderId = $this->argument('orderId');
        $order = Order::model()->getOne($orderId);
        $res = NbfcReportServer::server()->handle($order, NbfcReportConfig::REPORT_NODE_SIGN);
        print_r($res);
    }

}
