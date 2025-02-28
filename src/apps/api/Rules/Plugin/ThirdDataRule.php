<?php

namespace Api\Rules\Plugin;

use Api\Models\Upload\Upload;
use Common\Rule\Rule;

/**
 * Class UploadRule
 * @package App\Http\Api\Rules\Login
 * @author ChangHai Zhan
 */
class ThirdDataRule extends Rule
{
    /**
     * facebook资料
     */
    const SCENARIO_FACEBOOK_CREATE = 'facebook_create';

    /**
     * @return array|mixed
     */
    public function rules()
    {
        return [
            self::SCENARIO_FACEBOOK_CREATE => [
                'apply_id' => 'required',
                'email' => 'email',
                'picture' => 'mimes:' . implode(',', Upload::EXTENSION),
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
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
