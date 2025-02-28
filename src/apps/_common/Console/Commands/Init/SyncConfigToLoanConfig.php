<?php

namespace Common\Console\Commands\Init;

use Common\Console\Services\Config\LoanMultipleConfigServer;
use Common\Models\Common\Config;
use Common\Models\Config\LoanMultipleConfig;
use Common\Models\Merchant\Merchant;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SyncConfigToLoanConfig extends command
{
    protected $signature = 'init:sync:config-to-loan-config {--merchantId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步config表的贷款设置至最新的贷款配置表';

    const NEED_SYNC_CONFIG_1 = [
        Config::KEY_LOAN_AMOUNT_RANGE => LoanMultipleConfig::FIELD_LOAN_AMOUNT_RANGE,
        Config::KEY_LOAN_DAYS_RANGE => LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE,
        Config::KEY_LOAN_AGAIN_DAYS_RANGE => LoanMultipleConfig::FIELD_LOAN_AGAIN_DAYS,
        Config::KEY_LOAN_DAILY_LOAN_RATE => LoanMultipleConfig::FIELD_LOAN_DAILY_LOAN_RATE,
        Config::KEY_PROCESSING_RATE => LoanMultipleConfig::FIELD_PROCESSING_RATE,
        Config::KEY_PENALTY_RATE => LoanMultipleConfig::FIELD_PENALTY_RATE,
    ];

    const NEED_SYNC_CONFIG_2 = [
        Config::KEY_LOAN_AMOUNT_RANGE_OLD => LoanMultipleConfig::FIELD_LOAN_AMOUNT_RANGE,
        Config::KEY_LOAN_DAYS_RANGE_OLD => LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE,
        Config::KEY_LOAN_AGAIN_DAYS_RANGE_OLD => LoanMultipleConfig::FIELD_LOAN_AGAIN_DAYS,
        Config::KEY_LOAN_DAILY_LOAN_RATE_OLD => LoanMultipleConfig::FIELD_LOAN_DAILY_LOAN_RATE,
        Config::KEY_PROCESSING_RATE_OLD => LoanMultipleConfig::FIELD_PROCESSING_RATE,
        Config::KEY_PENALTY_RATE_OLD => LoanMultipleConfig::FIELD_PENALTY_RATE,
    ];

    public function handle()
    {
        $merchantId = $this->option('merchantId');

        if ($merchantId) {
            $merchant = Merchant::model()->getNormalById($merchantId);
            if (!$merchant) {
                $this->error('商户不存在');
                die();
            }

            $this->initHandle($merchant->id);

        } else {
            $merchants = Merchant::model()->getNormalAll();

            if ($merchants->isEmpty()) {
                $this->error('商户不存在数据，请先建立初始商户');
                die();
            }

            foreach ($merchants as $merchant) {
                if ($this->initHandle($merchant->id)) {
                    $this->line("同步完成:{$merchant->id}");
                }
            }
        }
    }

    protected function initHandle($merchantId)
    {
        return MerchantHelper::callbackOnce(function () use ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);

            $config = LoanMultipleConfig::getConfigByMerchant($merchantId);
            if (count($config) > 2) {
                $this->info("贷款配置已更新，需手动同步:{$merchantId}");
                return false;
            }

            $this->call('init:config', [
                'config' => ConfigInit::CONFIG_LOAN_CONFIG,
                '--merchantId' => $merchantId,
            ]);
            $this->call('init:config', [
                'config' => 'loan',
                '--merchantId' => $merchantId,
            ]);

            $oldConfig = $this->getOldConfig($merchantId);
            // 如果旧的config记录不存在，不进行同步
            if ($oldConfig->isEmpty()) {
                $this->info("旧配置不存在,无需同步:{$merchantId}");
                return false;
            }

            return $this->sync($oldConfig);
        });
    }

    protected function getOldConfig($merchantId)
    {
        $where = [
            'merchant_id' => $merchantId,
            'status' => Config::STATUS_NORMAL,
        ];

        $keys = array_merge(array_keys(self::NEED_SYNC_CONFIG_1), array_keys(self::NEED_SYNC_CONFIG_2));

        return Config::query()->whereIn('key', $keys)->where($where)->pluck('value', 'key');
    }

    protected function sync(Collection $oldConfig)
    {
        $defaultConfig = LoanMultipleConfigServer::server()->getDefaultLoanConfig();

        foreach (self::NEED_SYNC_CONFIG_1 as $k => $v) {
            if (!$value = $oldConfig->get($k)) {
                continue;
            }
            $value = $this->formatField($v, $value);
            $defaultConfig[0][$v] = $value;
        }

        foreach (self::NEED_SYNC_CONFIG_2 as $k => $v) {
            if (!$value = $oldConfig->get($k)) {
                continue;
            }
            $value = $this->formatField($v, $value);
            $defaultConfig[1][$v] = $value;
        }

        return LoanMultipleConfigServer::server()->saveConfig($defaultConfig);
    }

    protected function formatField($k, $v)
    {
        switch (LoanMultipleConfig::FIELD_TYPE[$k]) {
            case 'array':
                return json_decode($v, true);
            default:
                return $v;
        }
    }
}
