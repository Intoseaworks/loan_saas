<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Config;

use Admin\Rules\Config\ConfigRule;
use Admin\Services\BaseService;
use Common\Models\Approve\Approve;
use Common\Models\Config\Config;
use Common\Models\Config\LoanMultipleConfig;
use Common\Models\Trade\TradeLog;
use Common\Models\User\UserAuth;
use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;

class ConfigServer extends BaseService
{
    public function getConfig($name = null)
    {
        $keys = Config::getKeys($name);
        $data = Config::getValueByKeys($keys);
        if ($dataKeyLoginIpFilters = array_get($data, Config::KEY_LOGIN_IP_FILTER)) {
            foreach ($dataKeyLoginIpFilters as $key => $dataKeyLoginIpFilter) {
                try {
                    $city = HostHelper::getAddressByIp($dataKeyLoginIpFilter);
                } catch (\Exception $e) {
                    $city = '';
                }
                $dataKeyLoginIpFilters[$key] = [
                    'ip' => $dataKeyLoginIpFilter,
                    'city' => $city,
                ];
            }
            $data[Config::KEY_LOGIN_IP_FILTER] = $dataKeyLoginIpFilters;
        }
        $data['app_name'] = config('domain.project_name');
        $data['pusher_config'] = [
            'key' => config('broadcasting.connections.pusher.key'),
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'forceTLS' => config('broadcasting.connections.pusher.options.useTLS'),
            'authEndpoint' => config('broadcasting.connections.pusher.authEndpoint'),
        ];
        $loanConfig = LoanMultipleConfig::getConfigByMerchant(MerchantHelper::getMerchantId());
        if ($loanConfig) {
            $data['repay_part_repay_max'] = min(array_get($loanConfig, '0.loan_amount_range'), 10000);
        }
        return $data;
    }

    public function initAllConfig($merchantId, $config = null)
    {
        $server = ConfigServer::server();
        $configs = ConfigRule::SCENARIO_LIST;
        if (!is_null($config)) {
            return $server->initConfig($config, $merchantId);
        }
        foreach ($configs as $config) {
            $server->initConfig($config, $merchantId);
            if ($server->isSuccess()) {
                echo $config . '配置成功' . PHP_EOL;
            } else {
                echo $server->getMsg();
            }
        }
        return true;
    }

    public function initConfig($config, $merchantId = null)
    {
        if (!in_array($config, ConfigRule::SCENARIO_LIST)) {
            return $this->outputError('配置项非法');
        }
        $data = self::server()->getDataByScenario($config);
        return self::server()->firstOrCreateConfig($data, $merchantId);
    }

    /**
     * 对应key不存在则新建
     * @param $params
     * @param $merchantId
     * @return ConfigServer
     */
    public function firstOrCreateConfig($params, $merchantId = null)
    {
        foreach ($params as $key => $val) {
            if (!Config::findOrCreate($merchantId, $key, $val)) {
                return $this->outputError('更新' . $key . '失败');
            }
        }
    }

    /**
     * 保存数据库
     * @param $params
     * @return ConfigServer
     */
    public function storeConfig($params)
    {
        foreach ($params as $key => $val) {
            if (!Config::createOrUpdate($key, $val)) {
                return $this->outputError('更新' . $key . '失败');
            }
        }
        return $this->outputSuccess();
    }

    public function getInfo()
    {
        $keys = [
            Config::KEY_DING_LOGIN_ON,
        ];
        $data = Config::getValueByKeys($keys);
        return $data;
    }

    public function getKeysByScenario($scenario)
    {
        return array_keys(self::getDataByScenario($scenario));
    }

    public function getDataByScenario($scenario)
    {
        switch ($scenario) {
            case ConfigRule::SCENARIO_ADMIN_SAFE:
                $keys = [
                    Config::KEY_LOGIN_IP_FILTER => [],
                    Config::KEY_LOGIN_IP_FILTER_ON => 0,
                    Config::KEY_LOGIN_ONLY_LOGIN_ON => 0,
                    //Config::KEY_DING_LOGIN_ON => 0,
                    Config::KEY_DEFAULT_PASSWORD => 111111,
                    Config::KEY_LOGIN_EXPIRE_HOURS => 8,
                ];
                break;
            case ConfigRule::SCENARIO_LOAN:
                $keys = [
//                    // 新用户
//                    Config::KEY_LOAN_AMOUNT_RANGE => [1000, 2000, 3000],
//                    Config::KEY_LOAN_DAYS_RANGE => [14],
//                    Config::KEY_LOAN_AGAIN_DAYS_RANGE => 30,
//                    Config::KEY_LOAN_DAILY_LOAN_RATE => 0.1,
//                    Config::KEY_PROCESSING_RATE => 10,
//                    Config::KEY_GST_PROCESSING_RATE => 18,
//                    Config::KEY_PENALTY_RATE => 0.5,
//                    Config::KEY_GST_PENALTY_RATE => 18,
//
//                    // 老用户
//                    Config::KEY_LOAN_AMOUNT_RANGE_OLD => [1000, 2000, 3000],
//                    Config::KEY_LOAN_DAYS_RANGE_OLD => [14],
//                    Config::KEY_LOAN_AGAIN_DAYS_RANGE_OLD => 60,
//                    Config::KEY_LOAN_DAILY_LOAN_RATE_OLD => 0.1,
//                    Config::KEY_PROCESSING_RATE_OLD => 10,
//                    Config::KEY_GST_PROCESSING_RATE_OLD => 18,
//                    Config::KEY_PENALTY_RATE_OLD => 0.5,
//                    Config::KEY_GST_PENALTY_RATE_OLD => 18,
                    // 公共
                    Config::KEY_FIRST_LOAN_REPAYMENT_RATE => 99.99,
                    Config::KEY_LAST_LOAN_REPAYMENT_DEDUCTION_ON => 1,
                    Config::KEY_LOAN_GST_RATE => 18,
                    Config::KEY_LOAN_FORFEIT_PENALTY_RATE => 5,
                    Config::KEY_WITHDRAWAL_SERVICE_CHARGE => 130,
                    Config::KEY_LOAN_RENEWAL_RATE => 10,
                ];
                break;
            case ConfigRule::SCENARIO_APPROVE:
                $keys = [
                    //Config::KEY_APPROVE_ALL_RISK => 0,
                    //Config::KEY_APPROVE_RISK_SCORE => 30,
                    Config::KEY_APPROVE_MANUAL_ALLOT_COUNT => 5,
                    Config::KEY_APPROVE_MANUAL_OVERTIME => 10,
                    Config::KEY_APPROVE_MANUAL_MAX_COUNT => 10,
                    Config::KEY_APPROVE_NEW_USER_PROCESS => [
                        Approve::PROCESS_FIRST_APPROVAL,
                        Approve::PROCESS_CALL_APPROVAL
                    ],
                    Config::KEY_APPROVE_OLD_USER_PROCESS => [
                        Approve::PROCESS_FIRST_APPROVAL,
                        Approve::PROCESS_CALL_APPROVAL
                    ],
                    Config::KEY_APPROVE_EKYC_FAIL_PROCESS => [
                        Approve::PROCESS_FIRST_APPROVAL,
                        Approve::PROCESS_CALL_APPROVAL
                    ]
                ];
                break;
            case ConfigRule::SCENARIO_OPERATE:
                $keys = [
                    Config::KEY_DAILY_REGISTER_USER_MAX => 1000,
                    Config::KEY_DAILY_CREATE_ORDER_MAX => 1000,
                    Config::KEY_DAILY_CREATE_ORDER_MAX_OLD => 1000,
                    Config::KEY_DAILY_LOAN_AMOUNT_MAX => 100000,
                    Config::KEY_CUSTOMER_SERVICE_TELEPHONE_NUMBERS => [],
                    Config::KEY_CUSTOMER_WORK_TIME => ['周一', '周日', '9:00-18:30'],
                    Config::KEY_CUSTOMER_SERVICE_EMAIL => 'email',
                    Config::KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE => 50,
                    Config::KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE_OLD => 50,
                    Config::KEY_WARNING_DAILY_REMAIN_LOAN_AMOUNT_VALUE => 5000,
                    Config::KEY_SYS_AUTO_REMIT => 0,
                    Config::KEY_COMPANY_NAME => '',
                    Config::KEY_COMPANY_ADDRESS => '',
                    Config::KEY_COMPANY_OFFLINE_RAPAY_BAMK => '',
                ];
                break;
            case ConfigRule::SCENARIO_COLLECTION:
                $keys = [
                    Config::KEY_COLLECTION_BAD_DAYS => 999,
                ];
                break;
//            case ConfigRule::SCENARIO_PARTENR_ACCOUNT:
//                $partnerAccountKeys = Config::CONFIG_PARTNER_ACCOUNT_INFO;
//                $keys = array_combine($partnerAccountKeys, array_fill(0, count($partnerAccountKeys), ''));
//                break;
            case ConfigRule::SCENARIO_INIT_ROLE_SYMOBL:
                $keys = [
                    Config::KEY_INIT_ROLE_SYMBOL => 1,
                    Config::KEY_HISTORY_MENU => '{}',
                ];
                break;
            case ConfigRule::SCENARIO_AUTH:
                $keys = [
                    Config::KEY_AUTH_EKYC_ON => 1,
                    Config::KEY_AADHAAR_VERIFY_ON => 1,
                    Config::KEY_AUTH_SETTING => [],
                ];
                break;
            case ConfigRule::SCENARIO_REPAY:
                $keys = [
                    Config::KEY_REPAY_PART_REPAY_ON => 0,
                    Config::KEY_REPAY_PART_CONFIG => [],
                    Config::KEY_REPAY_PART_MIN_OVERDUE_DAYS => 0,
                    Config::KEY_REPAY_PART_ALL_OVERDUE_ON => 0,
                ];
                break;
            case ConfigRule::SCENARIO_SYSTEM:
                $keys = [
                    Config::KEY_SYS_AUTO_REMIT => 0,
                    Config::KEY_SYS_AUTO_REMIT_PLATFORM => '',
                    Config::KEY_SYS_REPAY_PLATFORM => [TradeLog::TRADE_PLATFORM_PAYMOB],
                ];
                break;
            case ConfigRule::SCENARIO_APP:
                $keys = [
                    Config::KEY_APP_VERSION_NO => '1.0.0',
                    Config::KEY_APP_VERSION_FORCIBLE => 0,
                    Config::KEY_APP_VERSION_TITLE => 'New Version Update',
                    Config::KEY_APP_VERSION_CONTENT => 'Optimize product experience',
                    Config::KEY_APP_VERSION_URL => '',
                ];
                break;
            default:
                $keys = [];
        }
        return $keys;
    }

    public function getUserApproveConfig($order)
    {
        /** e-KYC认证不通过走特殊流程 */
        $userApproveConfig = Config::model()->getEKYCFailApproveProcess();
        /** e-KYC认证通过/e-KYC后台审批未配置 走正常流程 */
        if ($order->user->getAadhaarCardKYCStatus() == UserAuth::AUTH_STATUS_SUCCESS ||
            empty($userApproveConfig)) {
            $userApproveConfig = Config::model()->getUserApproveProcess($order->quality);
        }
        return $userApproveConfig;
    }

}
