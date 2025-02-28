<?php

namespace Api\Rules\Data;

use Common\Rule\Rule;

/**
 * Class IDConfirmRule
 * @package Api\Rules\Data
 */
class IDConfirmRule extends Rule
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
                'fullname' => 'required',
                'id_card_no' => 'required',
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
