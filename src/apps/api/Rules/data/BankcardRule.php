<?php

namespace Api\Rules\Data;

use Common\Rule\Rule;

/**
 * Class BankcardRule
 * @package Api\Rules\Data
 */
class BankcardRule extends Rule
{
    /**
     * 验证场景 上传文件
     */
    const SCENARIO_CREATE = 'create';


    /**
     * @return array|mixed
     */
    public function rules()
    {
        return [
            self::SCENARIO_CREATE => [
                'no' => 'required',
                'bank_name' => 'required',
                'reserved_telephone' => 'required|mobile',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            'create' => [
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
        ];
    }
}
