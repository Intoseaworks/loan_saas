<?php

namespace Admin\Services\Config;

use Admin\Services\BaseService;
use Admin\Services\Risk\RiskServer;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\MerchantHelper;

class RiskConfigServer extends BaseService
{
    const CONFIG_SYSTEM_APPROVE_KEYS = [
        'risk_score' => [
            'default' => "30",
            'hint' => '',
        ],
        'new_user_age' => [
            'default' => "[\"20\",\"55\"]",
            'hint' => '',
            'type' => 'json',
        ],
        'new_rejected_days' => [
            'default' => '50',
            'hint' => '有拒绝记录',
        ],
        'new_ass_rejected_days' => [
            'default' => '15',
            'hint' => '包括：银行卡、uuid、imei、紧急联系人、Pan card',
        ],
        'new_user_ass_account' => [
            'default' => '2',
            'hint' => '包括：银行卡、uuid、imei、紧急联系人、Pan card',
        ],
        'new_user_sms_cnt' => [
            'default' => '30',
            'hint' => '',
        ],
        'new_user_none_h5_contacts_cnt' => [
            'default' => '40',
            'hint' => 'h5客户端未验证',
        ],
        'old_user_age' => [
            'default' => "[\"20\",\"55\"]",
            'hint' => '',
            'type' => 'json',
        ],
        'old_user_max_overdue_days' => [
            'default' => '7',
            'hint' => '',
        ],
        'old_rejected_days' => [
            'default' => '90',
            'hint' => '有拒绝记录',
        ],
        'old_ass_rejected_days' => [
            'default' => '15',
            'hint' => '包括：银行卡、uuid、imei、紧急联系人、Pan card',
        ],
        'old_user_ass_account' => [
            'default' => '2',
            'hint' => '包括：银行卡、uuid、imei、紧急联系人、Pan card',
        ],
    ];

    public function getRiskConfig($keys = null)
    {
        $result = RiskServer::server()->updateRiskSetting(null, MerchantHelper::getMerchantId());
        $config = [];
        foreach (self::CONFIG_SYSTEM_APPROVE_KEYS as $key => $setting) {
            $riskRes = array_get($result, $key);
            $value = $riskRes ?? $setting['default'];

            if (array_get($setting, 'type') == 'json' && is_string($value)) {
                $value = ArrayHelper::jsonToArray($value);
            }

            $config[$key] = [
                'value' => $value,
                'hint' => t(array_get($setting, 'hint')),
            ];
        }

        return $config;
    }
}
