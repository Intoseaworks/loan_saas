<?php

namespace Api\Rules\Data;

use Api\Models\Upload\Upload;
use Common\Rule\Rule;

/**
 * Class UploadFacesRule
 * @package Api\Rules\Data
 */
class UploadFacesRule extends Rule
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
                'image_best' => 'required|mimes:' . implode(',', Upload::EXTENSION),
                'image_env' => 'mimes:' . implode(',', Upload::EXTENSION),
                'image_action1' => 'mimes:' . implode(',', Upload::EXTENSION),
                'image_action2' => 'mimes:' . implode(',', Upload::EXTENSION),
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
            'file' => '上传图片',
        ];
    }
}
