<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-14
 * Time: 10:36
 */

namespace Approve\Admin\Rules;


use Common\Rule\Rule;

class ApproveCheckRule extends Rule
{
    /**
     * index
     */
    const SENARIO_INDEX = 'SENARIO_INDEX';

    /**
     * 设置优先级
     */
    const SENARIO_SET_PRIORITY = 'SENARIO_SET_PRIORITY';

    /**
     * 详情
     */
    const SENARIO_SHOW = 'SENARIO_SHOW';

    /**
     * 根据订单状态展示审批状态
     */
    const SENARIO_APPROVE_STATUS = 'SENARIO_APPROVE_STATUS';

    /**
     * @return array
     */
    public function rules()
    {
        return [

            static::SENARIO_INDEX => [
                'status' => 'sometimes|integer',
                'order_created_time' => 'sometimes|array',
                'order_created_time.0' => 'sometimes|integer',
                'order_created_time.1' => 'sometimes|integer',
                'order_no' => 'sometimes|string',
                'user' => 'sometimes|string',
                'admin_user' => 'sometimes|string',
                'per_page' => 'sometimes|integer',
            ],

            static::SENARIO_SET_PRIORITY => [
                'id' => 'required|integer',
            ],

            static::SENARIO_SHOW => [
                'id' => 'required|integer',
            ],

            static::SENARIO_APPROVE_STATUS => [
                'order_status' => 'sometimes|array',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function attributes()
    {
        return [
        ];
    }
}
