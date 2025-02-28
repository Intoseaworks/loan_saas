<?php

namespace Admin\Rules\Approve;

use Admin\Models\Approve\Approve;
use Admin\Models\Order\Order;
use Common\Rule\Rule;

class ApproveRule extends Rule
{
    /**
     * 验证场景 审批列表
     */
    const SCENARIO_INDEX = 'index';
    /**
     * 验证场景 提交审批
     */
    const SCENARIO_APPROVE_SUBMIT = 'approve_submit';
    const SCENARIO_DETAIL = 'detail';
    /**
     * 验证场景 被拒列表
     */
    const SCENARIO_REJECT_LIST = 'reject-list';
    /**
     * 验证场景 人审被拒原因
     */
    const SCENARIO_REJECT_REASON = 'reject-reason';
    /**
     * 判断订单能否进入审批
     */
    const SCENARIO_CAN_APPROVE = 'can_approve';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_INDEX => [
                'status.*' => 'in:' . implode(',', Order::APPROVAL_PENDING_STATUS),
            ],
            self::SCENARIO_DETAIL => [
                'id' => 'required'
            ],
            self::SCENARIO_APPROVE_SUBMIT => [
                'first' => 'string',
                'order_id' => 'required_without:first|numeric',
                'approve_result' => 'required_without:first|array',
                'approve_result.*' => 'in:' . implode(',', array_flatten(Approve::SELECT_GROUP)),
                'remark' => 'string',
            ],
            self::SCENARIO_REJECT_LIST => [
                'status.*' => 'in:' . implode(',', Order::APPROVAL_REJECT_STATUS),
            ],
            self::SCENARIO_REJECT_REASON => [
                'id' => 'required',
            ],
            self::SCENARIO_CAN_APPROVE => [
                'order_id' => 'required',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_APPROVE_SUBMIT => [
                'order_id.required_without' => 'order_id 不能为空',
                'approve_result.required_without' => 'approve_result 不能为空',
            ]
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
