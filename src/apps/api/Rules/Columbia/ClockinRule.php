<?php

namespace Api\Rules\Columbia;

use Api\Models\Upload\Upload;
use Common\Rule\Rule;

class ClockinRule extends Rule {

    /**
     * 验证场景 create
     */
    const PUNCH = 'punch';

    /**
     * @return array
     */
    public function rules() {
        return [
            self::PUNCH => [
                'selfie_pic' => 'required|mimes:' . implode(',', Upload::EXTENSION),
                'surroundings_pic' => 'required|mimes:' . implode(',', Upload::EXTENSION),
                'longitude' => 'required|numeric',
                'latitude' => 'required|numeric',
            ]
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages() {
        return [
            self::PUNCH => [
                'selfie_pic.required' => t('请上传自拍照', 'columbia'),
                'surroundings_pic.required' => t('请上传环境照', 'columbia'),
                'longitude.required' => "Failed to get your location, please check if GPS is turn on, then try again",
                'latitude.required' => "Failed to get your location, please check if GPS is turn on, then try again",
            ]
        ];
    }

    public function attributes() {
        return [];
    }

}
