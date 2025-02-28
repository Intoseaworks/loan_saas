<?php

namespace Common\Console\Commands\Merchant;

use Api\Services\SaasMaster\MerchantServer;
use Common\Models\Merchant\Merchant;
use Common\Models\Staff\Staff;
use Common\Services\Common\BaseServicesServer;
use Illuminate\Console\Command;

class CreateMerchant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merchant:create {adminUsername} {adminPassword}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建新商户 adminUsername adminPassword';

    public function handle()
    {
        $userName = trim($this->argument('adminUsername'));
        $password = trim($this->argument('adminPassword'));

        if ($merchant = Staff::model()->getOneByData(['username' => $userName])) {
            $this->error('超管名字已经存在');
            return;
        }
                /** 申请印牛服务appKey */
        //$result = BaseServicesServer::server()->registerApp($userName);
        //if ($result->isError()) {
            //$this->error('申请印牛服务appKey失败-' . $result->getMsg());
        //    return;
        //}
        $appData = [
            "app_key" => "jOd6I526KXO0nY",
            "app_secret_key" => "ea8a05cbfde62f2509fab0f1be60294d"
        ];
        $params = [
            'adminUsername' => $userName,
            'adminPassword' => $password,
            'merchant_app_key' => array_get($appData, 'app_key'),
            'merchant_app_secret_key' => array_get($appData, 'app_secret_key'),
            'merchantId' => Merchant::model()->getNewMerchantId(),
            'merchant_no' => Merchant::model()->generateMerchantNo(),
            'product_name' => $userName,
        ];
        $server = MerchantServer::server()->createInitMerchant($params);

        if ($server->isError()) {
            $this->error($server->getMsg());
            return;
        }

        $this->line($server->getMsg());
    }
}
