<?php

namespace Common\Console\Commands\Merchant;

use Common\Models\Merchant\App;
use Common\Models\Merchant\Merchant;
use Common\Redis\Merchant\MerchantRedis;
use Illuminate\Console\Command;

class InitApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merchant:init-app {merchantId} {appName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '商户APP创建';

    public function handle()
    {
        $merchantId = $this->argument('merchantId');
        $appName = trim($this->argument('appName'));

        if (!$merchant = Merchant::find($merchantId)) {
            $this->error('商户不存在');
            return;
        }
        if ($merchant->status == Merchant::STATUS_DISABLE && !$this->confirm('商户状态为 ' . Merchant::STATUS[Merchant::STATUS_DISABLE] . '，继续创建？')) {
            return;
        }

        if (App::model()->getByAppName($appName)) {
            $this->error('AppName 已经存在');
            return;
        }

        $model = App::model()->add($merchant->id, $appName);

        MerchantRedis::redis()->clear($merchant->id);

        if (!$model) {
            $this->error('创建失败');
            return;
        }

        $this->line($model->app_key);
    }
}
