<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-14
 * Time: 10:36
 */

namespace Approve\Admin\Rules;


use Common\Models\Approve\ApproveQuality;
use Common\Rule\Rule;

class ApproveQualityRule extends Rule
{
    /**
     * index
     */
    const SENARIO_INDEX = 'SENARIO_INDEX';

    /**
     * 详情
     */
    const SENARIO_DETAIL = 'SENARIO_DETAIL';

    /**
     * 质检提交
     */
    const SENARIO_QUALITY_SUBMIT = 'SENARIO_QUALITY_SUBMIT';

    /**
     * @return array
     */
    public function rules()
    {
        return [

            static::SENARIO_INDEX => [
                'approve_time' => 'sometimes|array',
                'approve_time.0' => 'sometimes|integer',
                'approve_time.1' => 'sometimes|integer',
                'order_no' => 'sometimes|string',
                'user' => 'sometimes|string',
                'per_page' => 'sometimes|integer',
                'approve_result' => 'sometimes|integer',
                'quality_status' => 'sometimes|integer',
            ],

            static::SENARIO_DETAIL => [
                'id' => 'required|integer',
            ],

            static::SENARIO_QUALITY_SUBMIT => [
                'id' => 'required|integer',
                'quality_result' => 'required|integer' . $this->qualityResult(),
            ],

        ];
    }

    /**
     * @return string
     */
    protected function qualityResult()
    {
        $list = (new ApproveQuality())->getQualityResultList();
        return '|in:' . implode(',', array_keys($list));
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
