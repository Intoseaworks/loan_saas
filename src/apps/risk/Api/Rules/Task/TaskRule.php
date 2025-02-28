<?php

namespace Risk\Api\Rules\Task;

use Common\Rule\Rule;

class TaskRule extends Rule
{
    /**
     * 验证场景 - 创建任务&验证必传数据
     */
    const SCENARIO_START_TASK = 'start_task';

    /**
     * 验证场景 - 执行机审
     */
    const SCENARIO_EXEC_TASK = 'exec_task';

    /**
     * @return array|mixed
     */
    public function rules()
    {
        return [
            self::SCENARIO_START_TASK => [
                'user_id' => 'required',
                'order_id' => 'required',
                'notice_url' => 'required|string',
            ],
            self::SCENARIO_EXEC_TASK => [
                'task_no' => 'required|string',
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
