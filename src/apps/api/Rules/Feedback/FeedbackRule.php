<?php

namespace Api\Rules\Feedback;

use Common\Models\Upload\Upload;
use Common\Rule\Rule;

class FeedbackRule extends Rule
{

    const SCENARIO_CREATE = 'create';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_CREATE => [
                'pic1' => 'mimes:' . implode(',', Upload::EXTENSION),
                'pic2' => 'mimes:' . implode(',', Upload::EXTENSION),
                'pic3' => 'mimes:' . implode(',', Upload::EXTENSION),
                'contact_info' => 'string|max:200',
                'content' => 'required|string|max:500',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_CREATE => [
                'id.required' => t('id 不能为空', 'rule'),
                'id.exists' => t('id 用户不存在', 'rule'),
            ],
        ];
    }
}
