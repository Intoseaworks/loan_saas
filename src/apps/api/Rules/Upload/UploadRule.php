<?php

namespace Api\Rules\Upload;

use Api\Models\Upload\Upload;
use Common\Rule\Rule;
use Common\Validators\Validation;
use Common\Utils\ValidatorHelper;
use Validator;

/**
 * Class UploadRule
 * @package App\Http\Api\Rules\Login
 * @author ChangHai Zhan
 */
class UploadRule extends Rule {

    /**
     * 验证场景 上传文件
     */
    const SCENARIO_CREATE = 'create';
    const SCENARIO_ID_CARD = 'id_card';

    /**
     * 验证场景 根据来源ID与type打包下载文件
     */
    const SCENARIO_DOWNLOAD_BY_TYPE = 'downloadByType';

    /**
     * @return array|mixed
     */
    public function rules() {
        $this->extend();
        return [
            self::SCENARIO_CREATE => [
                'file' => 'required|mimes:' . implode(',', Upload::EXTENSION),
                'type' => 'required|in:' . implode(',', array_keys(Upload::TYPE)),
            ],
            self::SCENARIO_ID_CARD => [
                'card_type_code' => 'required|in:' . implode(',', array_keys(Upload::TYPE_BUK)),
                'card_num' => 'required|size:14',
                'file_card' => 'required|file|mimes:' . implode(',', Upload::EXTENSION),
                'file_face' => 'required|file|mimes:' . implode(',', Upload::EXTENSION),
            ],
            self::SCENARIO_DOWNLOAD_BY_TYPE => [
                'source_id' => 'required',
                'type' => 'required|array',
                'type.*' => 'in:' . implode(',', array_keys(Upload::TYPE_BUK)),
            ]
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages() {
        return [
        ];
    }

    /**
     * @return array
     */
    public function attributes() {
        return [
        ];
    }

    protected function extend() {
        Validator::extendImplicit('validate_card_num', function ($attribute, $value, $parameters, Validation $validator) {
            $data = $validator->getData();
            switch ($data['card_type_code']) {
                case Upload::TYPE_BUK_NIC:
                    if (!ValidatorHelper::validNICNum($value)) {
                        $validator->setCustomMessages(['validate_card_num' => "The format of ID number is 14 digits,pls enter the correct card number"]);
                        return false;
                    }
                    break;
                case Upload::TYPE_BUK_PASSPORT:
                    if (!ValidatorHelper::validPassportCard($value)) {
                        $validator->setCustomMessages(['validate_card_num' => "The format of SSS card is 10 digits,pls enter the correct card number"]);
                        return false;
                    }
                    break;
            }
            return true;
        });
    }

}
