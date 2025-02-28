<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Payout;

use Admin\Services\TradeManage\RemitServer;
use Common\Models\Trade\TradeLog;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class Manual extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout:manual {controlNumber} {date}';
    protected $description = '手动放款成功 {controlNumber} {date}';

    public function handle() {
        $controlNumber = $this->argument('controlNumber');
        $datetime = $this->argument('date');
        $this->success($controlNumber, $datetime . " 23:59:01");
    }

    public function success($controlNumber, $tradeTime) {
//        dd($controlNumber, $tradeTime);
        echo "start $controlNumber $tradeTime";
        $tradeLog = TradeLog::model()->where('master_business_no', $controlNumber)->orderByDesc('id')->first();
        if ($tradeLog) {
            $tradeTime = $tradeTime ?? date('Y-m-d H:i:s');
            MerchantHelper::setMerchantId($tradeLog->merchant_id);
            if ($tradeLog->order->status == \Common\Models\Order\Order::STATUS_PAYING) {
                RemitServer::server()->flowRemitSuccess($tradeLog, $tradeLog->trade_amount, $tradeTime);
            }
            echo "====SUCCESS";
        } else {
            echo "TradeLog 未找到";
        }
    }

}
