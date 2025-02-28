<?php

namespace Api\Rules\Common;

use Common\Rule\Rule;

class BankCardRule extends Rule
{
    /**
     * 鉴权绑卡
     */
    const SCENARIO_BIND_SEND_CODE = 'bind_send_code';

    /**
     * 分行查询【越南】
     */
    const SCENARIO_GET_BRANCH = 'branch_bank';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_BIND_SEND_CODE => [
                'bank_card_no' => 'required',
                'province_code' => 'required',
                //'city_code' => 'required',
                'bank_branch_name' => 'required',
                'reserved_telephone' => 'required|mobile',
            ],
            self::SCENARIO_GET_BRANCH => [
                'state' => 'required',
                'bank' => 'required',
            ]
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
        ];
    }

    public function attributes()
    {
        return [
            'bank_card_no' => '银行卡号',
            'province_code' => '开户行地址',
            'bank_branch_name' => '支行名称',
            'reserved_telephone' => '手机号',
        ];
    }
}
