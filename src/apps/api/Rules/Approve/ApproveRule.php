<?php


namespace Api\Rules\Approve;


use Common\Rule\Rule;

class ApproveRule extends Rule
{
    const SCENARIO_MANUAL_APPROVE = 'manual_approve';
    const SCENARIO_CALL_APPROVE = 'call_approve';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_MANUAL_APPROVE => [
                'data' => 'required|array',
                'data.riskData' => 'required',
                'data.orderStatus' => 'required',
                'data.orderId' => 'required|integer',
                'data.userId' => 'required|integer',
            ],
            self::SCENARIO_CALL_APPROVE => [
                'data' => 'required|array',
                'data.riskData' => 'required',
                'data.orderStatus' => 'required',
                'data.orderId' => 'required|integer',
                'data.userId' => 'required|integer',
            ],
        ];
    }
}
