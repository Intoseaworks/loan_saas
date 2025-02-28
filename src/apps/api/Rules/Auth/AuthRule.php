<?php

namespace Api\Rules\Auth;

use Api\Models\Upload\Upload;
use Common\Models\User\UserAuth;
use Common\Rule\Rule;

class AuthRule extends Rule
{
    /**
     * detail 验证场景
     */
    const SCENARIO_DETAIL = 'detail';

    /**
     * ocr 验证场景
     */
    const SCENARIO_OCR = 'ocr';

    /**
     * 卡信息 修改场景
     */
    const SCENARIO_CHECK = 'check';


    const SCENARIO_FACE = 'face';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_DETAIL => [
                'id' => 'required|exists:user,id',
                'type' => 'required|in:' . implode(',', array_keys(Upload::TYPE)),
            ],
            self::SCENARIO_OCR => [
                'type' => 'required|in:' . implode(',', UserAuth::OCR_TYPE),
            ],
            self::SCENARIO_CHECK => [
                'type' => 'required|in:' . implode(',', UserAuth::CARD_TYPE),
                'no' => 'required',
//                'father_name' => 'required_if:type,' . UserAuth::TYPE_PAN_CARD,
//                'address' => 'required_if:type,' . UserAuth::TYPE_AADHAAR_CARD,
//                'pincode' => 'required_if:type,' . UserAuth::TYPE_AADHAAR_CARD . '|size:6|string',
            ],
            self::SCENARIO_FACE => [
                //'video' => 'required',
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
