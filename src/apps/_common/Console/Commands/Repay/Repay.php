<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Repay;

use Admin\Models\Order\Order;
use Admin\Services\Repayment\ManualRepaymentServer;
use Common\Libraries\PayChannel\Fawry\RepayHelper;
use Common\Models\Repay\RepayDetail;
use Common\Models\Trade\TradeLog;
use Common\Services\Repay\RepayServer;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Common\Utils\Data\DateHelper;

class Repay extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repay:repay {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复trade --date=YYYY-HH-DD还款日期';

    public function handle()
    {
        if (!($date = $this->option('date'))) {
            $date = date("Y-m-d");
        }
        echo 'PULL DATE=' . $date . PHP_EOL;
        $trades = TradeLog::model()
            ->where(DB::raw('date(trade_request_time)'), $date)
            ->where("trade_platform", TradeLog::TRADE_PLATFORM_FAWRY)
            ->where("business_type", TradeLog::BUSINESS_TYPE_REPAY)
            ->where("trade_evolve_status", TradeLog::TRADE_EVOLVE_STATUS_TRADING)->get();
        foreach ($trades as $tradeLog) {
            $this->repair($tradeLog);
        }

    }

    protected function repair($trade)
    {
        echo "start trade_id:{$trade->id}";
        $res = \Api\Services\Repay\RepayServer::server()->checkTrade($trade);
        if ($res) {
            echo " PAID" . PHP_EOL;
        } else {
            echo " UNPAID" . PHP_EOL;
        }
    }

}
