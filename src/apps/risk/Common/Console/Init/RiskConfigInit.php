<?php

namespace Risk\Common\Console\Init;

use Common\Models\Merchant\Merchant;
use Illuminate\Console\Command;
use Risk\Common\Services\Config\ConfigServer;
use Risk\Common\Services\SystemApprove\SystemApproveRuleInitServer;

class RiskConfigInit extends command
{
    const CONFIG_ALL = 'all';
    const CONFIG_CONFIG = 'config';
    const CONFIG_SYSTEM_APPROVE = 'system-approve';

    /**
     * The name and signature of the console command.
     * 所有配置初始化 risk:init:config all
     * 机审配置 risk:init:config system-approve
     * @var string
     */
    protected $signature = 'risk:init:config {config} {--merchantId=}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = '配置初始化 配置表:config 机审配置:system-approve';

    public function handle()
    {
        $merchantId = $this->option('merchantId');
        $config = $this->argument('config');

        // 公共配置
        $this->initCommonHandle($config);

        // 商户特有配置
        if ($merchantId) {
            $merchant = Merchant::model()->getById($merchantId);

            $this->initHandle($merchant->id, $config);
        } else {
            $merchants = Merchant::model()->getNormalAll();

            if ($merchants->isEmpty()) {
                $this->error('商户不存在数据，请先建立初始商户');
                die();
            }

            foreach ($merchants as $merchant) {
                $this->initHandle($merchant->id, $config);
            }
        }
    }

    /**
     * 初始化公共配置
     * @param $config
     */
    public function initCommonHandle($config)
    {
        switch ($config) {
        }
    }

    /*********************************************************************************************************
     * 单个配置初始化
     ********************************************************************************************************/

    /**
     * 商户特有配置
     * @param $appId
     * @param $config
     */
    public function initHandle($appId, $config)
    {
        switch ($config) {
            /** 所有配置初始化 */
            case self::CONFIG_ALL:
                $this->initSystemApproveRule($appId);
                $this->initConfig($appId);
                break;
            /** 机审配置初始化 */
            case self::CONFIG_CONFIG:
                $this->initConfig($appId);
                break;
            case self::CONFIG_SYSTEM_APPROVE:
                $this->initSystemApproveRule($appId);
                break;
            default:
                break;
        }
    }

    /**
     * 机审配置默认值初始化
     * @param $appId
     * @return bool
     */
    public function initSystemApproveRule($appId)
    {
        return SystemApproveRuleInitServer::server()->initRule($appId);
    }

    public function initConfig($appId)
    {
        return ConfigServer::server()->initConfig($appId);
    }
}
