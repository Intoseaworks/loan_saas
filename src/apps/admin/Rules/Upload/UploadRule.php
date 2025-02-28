<?php

namespace Admin\Rules\Upload;

use Admin\Models\Upload\Upload;
use Common\Rule\Rule;

/**
 * Class UploadRule
 * @package App\Http\Api\Rules\Login
 * @author ChangHai Zhan
 */
class UploadRule extends Rule
{
    /**
     * 验证场景 上传文件
     */
    const SCENARIO_CREATE = 'create';
    /**
     * 验证场景 根据来源ID与type打包下载文件
     */
    const SCENARIO_DOWNLOAD_BY_TYPE = 'downloadByType';

    /**
     * @return array|mixed
     */
    public function rules()
    {
        return [
            self::SCENARIO_CREATE => [
                'file' => 'required|mimes:' . implode(',', Upload::EXTENSION),
                'type' => 'required',
            ],
            self::SCENARIO_DOWNLOAD_BY_TYPE => [
                'source_id' => 'required',
                'type' => 'required|array',
                'type.*' => 'in:' . implode(',', array_keys(Upload::TYPE)),
            ]
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
            'type' => '图片类型',
            'ocr' => '图片识别'
        ];
    }
}
