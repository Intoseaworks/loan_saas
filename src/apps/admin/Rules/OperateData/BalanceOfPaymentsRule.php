<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 22:34
 */

namespace Admin\Rules\OperateData;


use Common\Rule\Rule;

class BalanceOfPaymentsRule extends Rule
{
    const SCENARIO_LIST = 'list';
    const SCENARIO_INCOME_LIST = 'income_list';
    const SCENARIO_DISBURSE_LIST = 'disburse_list';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_LIST => [
                'date' => 'sometimes|array',
                'date.0' => 'sometimes|string',
                'date.1' => 'sometimes|string',
            ],
            self::SCENARIO_INCOME_LIST => [
                'date' => 'required|string',
            ],
            self::SCENARIO_DISBURSE_LIST => [
                'date' => 'required|string',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            'date.0' => '开始时间不能为空',
            'date.1' => '结束时间不能为空',
        ];
    }
}
