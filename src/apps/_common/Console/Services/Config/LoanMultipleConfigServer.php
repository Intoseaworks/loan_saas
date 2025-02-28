<?php

namespace Common\Console\Services\Config;

use Common\Models\Config\LoanMultipleConfig;
use Common\Utils\MerchantHelper;

class LoanMultipleConfigServer extends \Common\Services\Config\LoanMultipleConfigServer
{
    public function getDefaultLoanConfig()
    {
        $startNumber = LoanMultipleConfig::HEADER_NUMBER_START;

        $config = [
            [
                LoanMultipleConfig::FIELD_NUMBER_END => $startNumber,
                LoanMultipleConfig::FIELD_LOAN_AMOUNT_RANGE => [1000, 2000, 3000],
                LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE => [7],
                LoanMultipleConfig::FIELD_LOAN_DAILY_LOAN_RATE => 0.1,
                LoanMultipleConfig::FIELD_PENALTY_RATE => 0.5,
                LoanMultipleConfig::FIELD_PROCESSING_RATE => [10],
                LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE => [10],
                LoanMultipleConfig::FIELD_LOAN_FORFEIT_PENALTY_RATE => [5, 5],
                LoanMultipleConfig::FIELD_LOAN_AGAIN_DAYS => 30,
            ],
            [
                LoanMultipleConfig::FIELD_NUMBER_END => 999,
                LoanMultipleConfig::FIELD_LOAN_AMOUNT_RANGE => [1000, 2000, 3000],
                LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE => [7],
                LoanMultipleConfig::FIELD_LOAN_DAILY_LOAN_RATE => 0.1,
                LoanMultipleConfig::FIELD_PENALTY_RATE => 0.5,
                LoanMultipleConfig::FIELD_PROCESSING_RATE => [10],
                LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE => [10],
                LoanMultipleConfig::FIELD_LOAN_FORFEIT_PENALTY_RATE => [5, 5],
                LoanMultipleConfig::FIELD_LOAN_AGAIN_DAYS => 30,
            ],
        ];

        return $config;
    }

    /**
     * 初始化商户贷款设置
     * @param $merchantId
     * @return mixed
     */
    public function initConfig($merchantId)
    {
        return MerchantHelper::callbackOnce(function () use ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);

            $config = LoanMultipleConfig::getConfigByMerchant($merchantId);
            if ($config) {
                return true;
            }
            $initConfig = $this->getDefaultLoanConfig();
            $this->saveConfig($initConfig);
            return true;
        });
    }
}
