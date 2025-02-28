<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-17
 * Time: 20:25
 */

namespace Approve\Admin\Services\Approval;


use Closure;
use Common\Traits\GetInstance;

class WorkFlowService
{
    use GetInstance;


    /**
     * 基础信息
     */
    const BASE_DETAIL = 'bashDetail';

    /**
     * 银行流水
     */
    const BANK_STATEMENT = 'bankStatement';

    /**
     * 工资单
     */
    const PAY_SLIP = 'paySlip';

    /**
     * 员工证
     */
    const EMPLOYEE_CARD = 'employeeCard';

    /**
     * 就业信息
     */
    const EMPLOYMENT_INFO = 'employmentInfo';


    /**
     * 电审
     */
    const CALL_APPROVE = 'detail';

    /**
     * 初审审批集合
     */
    const FIRST_WORK_FLOW = [
        self::BASE_DETAIL,
    ];

    /**
     * 电审审批集合
     */
    const CALL_WORK_FLOW = [
        self::CALL_APPROVE,
    ];

    /**
     * 初审工作流
     * @param $orderId
     * @return array
     */
    public function firstApproveWorkFlow($orderId)
    {
        $server = FirstApproveService::getInstance($orderId);
        $allFlows = $this->warpFunc($server, static::FIRST_WORK_FLOW);
        $data = $this->setFisrtApproveWorkFlow();
        return $this->getWorkFlow($data['flows'], $allFlows, $data['if_empty_remove']);
    }

    /**
     * 打包成闭包
     * @param $server
     * @param $funcs
     * @return array
     */
    protected function warpFunc($server, $funcs)
    {
        $warp = function () use ($server) {
            $params = func_get_args();
            $func = $params[0];
            unset($params[0]);
            return function () use ($server, $func, $params) {
                return call_user_func_array([$server, $func], $params);
            };
        };
        $data = [];
        foreach ($funcs as $func) {
            $data[$func] = $warp($func);
        }

        return $data;
    }

    /**
     * 设置初审工作流程
     * @return array
     */
    public function setFisrtApproveWorkFlow()
    {
        // 初审工作流程
        $flows = [
            static::BASE_DETAIL,
        ];
        // 数据为空就移除当前步骤
        $ifEmptyRemove = $this->getFirstEmptyRemoveList();

        return ['flows' => $flows, 'if_empty_remove' => $ifEmptyRemove];
    }

    /**
     * @return array
     */
    public function getFirstEmptyRemoveList()
    {
        return [];
    }

    /**
     * 获取工作流
     * @param $flowKeys
     * @param $allFlows
     * @param array $emptyRemove
     * @return array
     */
    public function getWorkFlow($flowKeys, $allFlows, $emptyRemove = [])
    {
        $workFlows = array_intersect_key(
            $allFlows,
            array_combine($flowKeys, array_fill(0, count($flowKeys), 1))
        );

        $data = [];
        foreach ($workFlows as $k => $workFlow) {
            if ($workFlow instanceof Closure) {
                $temp = $workFlow();
                if (isset($emptyRemove[$k]) && !$temp[$emptyRemove[$k]]) {
                    continue;
                }
                $data[$k] = $temp;
            }

        }

        return ['data' => $data, 'flows' => array_keys($data)];
    }

    /**
     * 电审工作流
     * @param $orderId
     * @return array
     */
    public function callApproveWorkFlow($orderId)
    {
        $server = CallApproveService::getInstance($orderId);
        $allFlows = $this->warpFunc($server, static::CALL_WORK_FLOW);
        $data = $this->setCallApproveWorkFlow();
        return $this->getWorkFlow($data['flows'], $allFlows, $data['if_empty_remove']);
    }

    /**
     * 设置电审工作流程
     * @return array
     */
    public function setCallApproveWorkFlow()
    {
        // 初审工作流程
        $flows = [
            static::CALL_APPROVE,
        ];

        // 数据为空就移除当前步骤
        $ifEmptyRemove = [];

        return ['flows' => $flows, 'if_empty_remove' => $ifEmptyRemove];
    }

}
