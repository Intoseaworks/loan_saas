<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-18
 * Time: 10:21
 */

namespace Approve\Admin\Rules;


use Common\Rule\Rule;

class ApproveStatisticRule extends Rule
{
    /**
     * 列表
     */
    const SENARIO_INDEX = 'SENARIO_INDEX';

    /**
     * 详情
     */
    const SENARIO_DETAIL = 'SENARIO_DETAIL';

    /**
     * 用户统计详情
     */
    const SENARIO_USER_SUMMARY = 'SENARIO_USER_SUMMARY';

    public function rules()
    {
        return [

            static::SENARIO_INDEX => [
                'approve_type' => 'sometimes|integer',
                'admin_id' => 'sometimes|integer',
            ],

            static::SENARIO_DETAIL => [
                'admin_id' => 'required|integer',
                'approve_type' => 'required|integer',
                'sort_type' => 'required|integer',
            ],

            static::SENARIO_USER_SUMMARY => [
                'admin_id' => 'required|integer',
                'approve_type' => 'required|integer',
            ],
        ];
    }

    public function attributes()
    {
        return [];
    }
}
