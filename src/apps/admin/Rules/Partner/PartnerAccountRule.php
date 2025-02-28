<?php

namespace Admin\Rules\Partner;

use Common\Models\Config\Config;
use Common\Rule\Rule;
use Common\Validators\Validation;
use Validator;

class PartnerAccountRule extends Rule
{
    /** 消费记录统计列表 */
    const SCENARIO_CONSUME_LIST = 'consume_list';
    /** 消费记录明细列表 */
    const SCENARIO_CONSUME_LOG_LIST = 'consume_log_list';
    /** 每日统计列表 */
    const SCENARIO_ACCOUNT_STATISTICS_LIST = 'account_statistics_list';
    /** 更新商户信息 */
    const SCENARIO_ACCOUNT_UPDATE = 'account_update';

    /**
     * @return array
     */
    public function rules()
    {

        $this->extend();

        return [
            self::SCENARIO_CONSUME_LIST => [
                'date' => 'array',
                'date.*' => 'date_format:Y-m-d',
            ],
            self::SCENARIO_CONSUME_LOG_LIST => [
                'date' => 'required|date_format:Y-m-d',
            ],
            self::SCENARIO_ACCOUNT_STATISTICS_LIST => [
                'date' => 'array',
                'date.*' => 'date_format:Y-m-d',
            ],
            self::SCENARIO_ACCOUNT_UPDATE => [
                'partner_account_company_name' => 'company_name_verfy',
                'partner_account_app_name' => 'app_name_verfy',
                'partner_account_contract' => 'required',
                'partner_account_contract_tel' => 'required|mobile',
                'partner_account_lender_name' => 'required|string',
                'partner_account_lender_id_card' => 'required|id_card_verify',
            ],
        ];
    }

    public function extend()
    {
        Validator::extendImplicit('company_name_verfy', function ($attribute, $value, $parameters, Validation $validator) {
            $info = Config::model()->getPartnerAccountInfo();
            if (!empty($info['partner_account_company_name'])) {
                if ($value) {
                    $validator->setCustomMessages(['company_name_verfy' => '公司主体名称不可编辑']);
                    return false;
                } else {
                    return true;
                }
            }
            return $value ? true : false;
        });

        Validator::extendImplicit('app_name_verfy', function ($attribute, $value, $parameters, Validation $validator) {
            $info = Config::model()->getPartnerAccountInfo();
            if (!empty($info['partner_account_app_name'])) {
                if ($value) {
                    $validator->setCustomMessages(['app_name_verfy' => 'App产品名称不可编辑']);
                    return false;
                } else {
                    return true;
                }
            }
            return $value ? true : false;
        });

        Validator::extendImplicit('id_card_verify', function ($attribute, $value, $parameters, Validation $validator) {
            $pattern = "/^(^\d{15}$)|(^\d{17}([0-9]|X)$)$/i";
            return preg_match($pattern, $value);
        });
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_ACCOUNT_UPDATE => [
                'company_name_verfy' => '公司主体名称不能为空',
                'app_name_verfy' => 'App产品名称不能为空',
                'id_card_verify' => '身份证号码格式不正确',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'partner_account_contract' => '公司联系人',
            'partner_account_contract_tel' => '公司联系人号码',
            'partner_account_lender_name' => '出借人姓名',
            'partner_account_lender_id_card' => '出借人身份证号',
        ];
    }
}
