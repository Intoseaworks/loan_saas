<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 11:24
 */

namespace Common\Console\Commands\Init;


use Admin\Services\Config\ConfigServer;
use Common\Console\Services\Config\LoanMultipleConfigServer;
use Common\Models\Merchant\Merchant;
use Common\Services\Init\FaqServer;
use Common\Services\NewClm\ClmConfigServer;
use Illuminate\Console\Command;

class ConfigInit extends command
{
    const CONFIG_ALL = 'all';
    const CONFIG_MENU = 'menu';
    const CONFIG_ROLE = 'role';
    const CONFIG_CONFIG_ALL = 'config_all';
    const CONFIG_LOAN_CONFIG = 'loan-config';
    /**
     * The name and signature of the console command.
     * 所有配置初始化 init:config all
     * //安全设置初始化 init:config safe
     * //贷款设置初始化 init:config loan
     * //审批设置初始化 init:config approve
     * //运营设置初始化 init:config operate
     * //催收设置初始化 init:config collection
     * //初始化角色 init:config role
     * //版本更新配置 init:config app
     * //贷款设置 init:config loan-config
     *
     * @var string
     */
    protected $signature = 'init:config {config} {--merchantId=} {--adminUsername=} {--adminPassword=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '配置初始化 所有:all config表所有:config_all 安全设置:safe 贷款设置(公共部分):loan 审批设置:approve 运营设置:operate 催收设置:collection 同步菜单:menu 初始化角色:role 版本更新配置:app 贷款设置(设置项部分):loan-config';

    public function handle()
    {
        $merchantId = $this->option('merchantId');
        $config = $this->argument('config');

        // 公共配置
        $this->initCommonHandle($config);

        // 商户特有配置
        if ($merchantId) {
            $merchant = Merchant::model()->getNormalById($merchantId);
            if (!$merchant) {
                $this->error('商户不存在');
                die();
            }

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
     * 商户特有配置
     * @param $merchantId
     * @param $config
     */
    public function initHandle($merchantId, $config)
    {
        switch ($config) {
            /** 所有配置初始化 */
            case self::CONFIG_ALL:
                $this->backendInit(null, $merchantId);
                $this->initRole($merchantId);
                $this->initLoanConfig($merchantId);
                break;
            /** 初始化config表所有配置 */
            case self::CONFIG_CONFIG_ALL:
                $this->backendInit(null, $merchantId);
                break;
            /** 默认角色权限 */
            case self::CONFIG_ROLE:
                $this->initRole($merchantId);
                break;
            /** 贷款设置初始化 */
            case self::CONFIG_LOAN_CONFIG:
                $this->initLoanConfig($merchantId);
                break;
            /** 安全设置/审批设置/运营设置/催收设置 */
            default:
                if (empty($config)) {
                    exit('config不能为空 例子:init:config faq');
                }
                $this->backendInit($config, $merchantId);
                break;
        }
    }

    /**
     * 初始化公共配置
     * @param $config
     */
    public function initCommonHandle($config)
    {
        switch ($config) {
            /** 所有配置初始化 */
            case self::CONFIG_ALL:
                $this->syncMenu();
                break;
            /** 初始化菜单 */
            case self::CONFIG_MENU:
                $this->syncMenu();
                break;
        }
    }


    /*********************************************************************************************************
     * 单个配置初始化
     ********************************************************************************************************/

    /**
     * 初始化常见问题
     */
    public function faqInit()
    {
        echo (FaqServer::server()->init() ? 'Faq配置成功' : 'Faq配置失败') . PHP_EOL;
    }

    /**
     * config 表初始化
     * @param null $config
     * @param null $merchantId
     * @return bool
     */
    public function backendInit($config = null, $merchantId = null)
    {
        $server = ConfigServer::server();
        return $server->initAllConfig($merchantId, $config);
    }

    /**
     * 初始化 RBAC 菜单配置
     */
    protected function syncMenu()
    {
        $this->call('sync-menu:sync-from-file', [
            '--isInit' => 1,
        ]);
    }

    /**
     * 初始化权限
     * @param $merchantId
     */
    protected function initRole($merchantId)
    {
        $adminUsername = $this->option('adminUsername');
        $adminPassword = $this->option('adminPassword');

        $this->call('permission:init-role', [
            '--merchantId' => $merchantId,
            '--initUsername' => $adminUsername,
            '--initPassword' => $adminPassword,
        ]);
    }

    /**
     * 贷款设置初始化
     * @param $merchantId
     * @return mixed
     */
    protected function initLoanConfig($merchantId)
    {
        // 初始化等级
        ClmConfigServer::server()->initLevel($merchantId);
        return LoanMultipleConfigServer::server()->initConfig($merchantId);
    }
}
