<?php

namespace Common\Console\Commands\Risk;

use Common\Models\Merchant\Merchant;
use Common\Services\Risk\RiskSendServer;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class RiskCommonDataSend extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'risk:send-common-data';

    /**
     * The console command description.
     * @var string
     */
    protected $description = '风控：公共数据上传';

    public function handle()
    {
        $this->sendCommonData();
    }

    protected function sendCommonData()
    {
        MerchantHelper::callback(function (Merchant $merchant) {
            RiskSendServer::server()->sendCommonQueue($merchant->id);
        });
    }
}
