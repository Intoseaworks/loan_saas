<?php

namespace Risk\Common\Services\SystemApprove;

use Common\Services\BaseService;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use Risk\Common\Helper\LockRedisHelper;
use Risk\Common\Jobs\ComputeUserapplicationDifCreateInstall;
use Risk\Common\Jobs\ComputeUserapplicationDifCreateInstallJob;
use Common\Models\Order\Order;
use Risk\Common\Models\SystemApprove\SystemApproveRecord;
use Risk\Common\Models\Task\Task;
use Risk\Common\Services\CreditReport\CreditReportServer;
use Risk\Common\Services\Task\TaskNoticeServer;

class ComputeRiskDataServer extends BaseService {

    const CREDIT_EFFECT_START_VERSION = '1.0.5';
    const KREDITONE_UNIT = [
        "APPLY_ADDRESS_UNQUALIFIED",
        //"CONTACTS_COUNT",
        "OTHER_RECENT_INSTALL_LOAN_APP_COUNT",
        "APP_ANTIFRAUD_RULE0005",
        "APP_ANTIFRAUD_RULE0006",
        "APP_ANTIFRAUD_RULE0007",
        "APP_ANTIFRAUD_RULE0011",
        "APP_ANTIFRAUD_RULE0012",
        //"THIRD_DATA_WHATSAPP",
        "APP_ANTIFRAUD_RULE0015",
        "APP_ANTIFRAUD_RULE0016",
    ];

    protected $taskId;
    protected $needScoring = false;

    /**
     * 机审总开关
     * @return mixed
     */
    public function envSystemApprove() {
        return config('risk.system_approve');
    }

    /**
     * 机审征信流程总开关
     * @return mixed
     */
    public function envSystemApproveCredit() {
        return config('risk.system_approve_credit');
    }

    public function setTaskId($taskId) {
        $this->taskId = $taskId;
    }

    /**
     * @param Task $task
     * @return bool
     * @throws \Exception
     */
    public function approve(Task $task) {
        $this->taskId = $task->id;

        $order = Order::getByIdAndUserId($task->user_id, $task->order_no);

        if (!$order) {
            $task->toException('对应订单不存在');
            TaskNoticeServer::server()->addTaskNoticeQueue($task);
            return false;
        }

        // 判断内部数据是否准备完善
        if (!$task->isFinishDataInner()) {
            return false;
        }

        MerchantHelper::setMerchantId($order->app_id);

        // 机审锁，避免与队列重复执行
        if (!LockRedisHelper::helper()->systemApprove($order->app_id, $order->order_no)) {
            return false;
        }

        DB::connection($task->getConnectionName())->beginTransaction();
        try {
            // 流转订单状态为机审中
            $task->toProcessing();

            /**  非正式环境机审默认通过 */
            if (app()->environment() != 'prod') {
                $ruleRes = true;
            } else {
                $ruleRes = $this->rulePasses($order);
            }

            if ($ruleRes === true) {
                $task->toFinish(Task::RESULT_PASS);
            } else {
                $task->toFinish(Task::RESULT_REJECT);
            }

            /** @var SystemApproveRecord $lastSystemApproveRecord */
            $lastSystemApproveRecord = $task->load('lastSystemApproveRecord')->lastSystemApproveRecord;
            if ($lastSystemApproveRecord) {
                $hitRuleCode = implode(',', $lastSystemApproveRecord->getHitRuleCode());
                $taskDesc = $lastSystemApproveRecord->description;

                $lastRejectSystemApproveRecord = $task->load('lastRejectSystemApproveRecord')->lastRejectSystemApproveRecord;
                if ($lastRejectSystemApproveRecord) {
                    $hitRuleCode = implode(',', $lastRejectSystemApproveRecord->getHitRuleCode());
                    $taskDesc = $lastRejectSystemApproveRecord->description;
                }

                $task->update([
                    'hit_rule_code' => $hitRuleCode,
                    'task_desc' => $taskDesc,
                ]);
            }

            DB::connection($task->getConnectionName())->commit();

            TaskNoticeServer::server()->addTaskNoticeQueue($task);
            return true;
        } catch (\Exception $e) {
            DB::connection($task->getConnectionName())->rollBack();
            DingHelper::notice(
                    json_encode([
                'file' => $e->getFile() . ":" . $e->getLine(),
                'order_id' => $order->id,
                'rule_result' => $ruleRes ?? ''
                    ]),
                    '机审异常 - ' . $e->getMessage() . '-' . app()->environment()
            );
            return false;
        }
    }

    public function rulePasses(Order $order) {
        $result = $this->basicRulePasses($order);
        // 基础规则通过 && 打开征信审批开关
        if (
                $result == SystemApproveRecord::RESULT_PASS &&
                $this->envSystemApproveCredit()
        ) {
            # 闪云征信报告
            if (SystemApproveThirdServer::server()->isValidate($order->business_app_id, "experianOfRiskcloud")) {
                SystemApproveThirdServer::server()->experianOfRiskcloud($order->user);
            } else {
                /** 挡板规则通过订单获取征信报告 */
                CreditReportServer::server()->hmCreditReport($order);
            }
        }
        $resultList = [SystemApproveRecord::RESULT_PASS, SystemApproveRecord::RESULT_REJECT];
        if (!in_array($result, $resultList)) {
            throw new \Exception('机审module得到预期外返回值');
        }

        return $result == SystemApproveRecord::RESULT_PASS;
    }

    /**
     * basic 变量计算
     * @param Order $order
     * @param bool $isSandboxMode
     * @return bool
     * @throws \Exception
     */
    public function basicVariableCompute(Order $order, $isSandboxMode = false) {
        dispatch(new ComputeUserapplicationDifCreateInstallJob($order));
//        (new ComputeUserapplicationDifCreateInstall($order))->handle();
    }

}
