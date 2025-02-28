<?php

namespace Api\Rules\Feedback;

use Common\Rule\Rule;

class FeedbackFaqRule extends Rule
{
    /**
     * detail 验证场景
     */
    const SCENARIO_DETAIL = 'detail';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_DETAIL => [
                'id' => 'required|exists:feedback_faq,id',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_DETAIL => [
                'id.required' => 'id 不能为空',
                'id.exists' => '记录不存在'
            ],
        ];
    }

    public function attributes()
    {
        return [

        ];
    }
}
