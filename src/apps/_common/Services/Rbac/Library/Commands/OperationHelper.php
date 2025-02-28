<?php

namespace Common\Services\Rbac\Library\Commands;

use Common\Models\Merchant\Merchant;
use Common\Services\Rbac\Library\Contracts\Config;
use Common\Services\Rbac\Models\Operation;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class OperationHelper extends Command
{
    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $operationFile = 'operation-{module}.php';

    /**
     * 控制台命令名称
     * @var string
     */
    protected $signature = 'rbac:operation:push 
                                {module : 同步的模块.对应中间件加的gurade:例如`"middleware" => "setGuard:admin"`模块为`admin`}';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '功能推送';


    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->module = $this->argument('module');
        $operation = $this->getOperation();
        if (!$operation) {
            $this->error('路由不存在');
            return;
        }

        $merchants = Merchant::model()->getNormalAll();
        $operationModel = new Operation();
        foreach ($merchants as $merchant) {
            // 在command模式下,MerchantHelper::$merchantId 为null,手动赋值.避免model merchantScope不起作用
            MerchantHelper::setMerchantId($merchant->id);
            $operationModel->syncOperation($operation);
        }

        $this->info('SUCCESS');
    }

    /**
     * 获取operation
     *
     * @return array
     */
    protected function getOperation()
    {
        /** @var Config $config */
        $config = app(Config::class);
        $path = rtrim($config->getOperationFilePath()) . '/';
        $file = $path . str_replace('{module}', $this->module, $this->operationFile);
        if (!file_exists($file)) {
            throw new \Exception('功能文件不存在');
        }
        return require $file;
    }
}
