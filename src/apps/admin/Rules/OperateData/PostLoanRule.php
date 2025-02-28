<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 22:34
 */

namespace Admin\Rules\OperateData;

use Admin\Models\User\User;
use Common\Rule\Rule;

class PostLoanRule extends Rule
{
    const SCENARIO_LIST = 'list';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_LIST => [
                'date' => 'sometimes|array',
                'quality' => 'in:' . implode(',', array_keys(User::QUALITY)),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'date' => '放款日期',
            'quality' => '用户类型',
            'channel_code.*' => '订单渠道',
        ];
    }
}
