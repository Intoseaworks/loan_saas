<?php

namespace Risk\Api\Rules\Data;

use Common\Rule\Rule;
use Common\Validators\Validation;
use Illuminate\Support\Facades\Validator;
use Risk\Common\Models\Task\TaskData;

class SendDataRule extends Rule
{
    /**
     * 验证场景 - 上传用户信息
     */
    const SCENARIO_SEND_DATA_ALL = 'send_data_all';

    /**
     * 发送公共数据
     */
    const SCENARIO_SEND_COMMON = 'send_common';

    /**
     * @return array|mixed
     */
    public function rules()
    {
        Validator::extendImplicit('data_validate', function ($attribute, $value, $parameters, Validation $validator) {
            $taskNo = array_get($validator->getData(), 'task_no');
            // 不与任务关联的上传  不进行表单必填验证
            if (!$taskNo) {
                return true;
            }

            $data = array_only($validator->getData(), array_keys(TaskData::TYPE_MODEL_CLASS));

            if (empty($data)) {
                $validator->setCustomMessages(['上传数据项不能为空']);
                return false;
            }
            foreach ($data as $type => $item) {
                $class = TaskData::TYPE_MODEL_CLASS[$type];

                if (!isset($class::$validate)) {
                    continue;
                }
                $validate = $class::$validate;

                // 数据量太大不适合表单验证，取部分验证
                if (count($item) > 100) {
                    $item = array_only($item, array_rand($item, 100));
                }

                $ruleValidator = Validator::make(['data' => $item], $validate);

                if ($ruleValidator->fails()) {
                    $error = array_flatten($ruleValidator->errors()->getMessages());
                    if (is_array($error)) {
                        $error = array_map(function ($i) use ($type) {
                            $i = str_replace('data', $type, $i);
                            return $i;
                        }, $error);
                    }
                    $validator->setCustomMessages($error);
                    return false;
                }
            }

            return true;
        });

        return [
            self::SCENARIO_SEND_DATA_ALL => [
                'user_id' => 'required|integer',
                'task_no' => 'string',
                'data' => 'data_validate',
            ],
            self::SCENARIO_SEND_COMMON => [

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

    public function attributes()
    {
        return [
        ];
    }
}
