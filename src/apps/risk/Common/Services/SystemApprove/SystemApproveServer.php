<?php

namespace Risk\Common\Services\SystemApprove;

use Common\Services\BaseService;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use Risk\Common\Helper\LockRedisHelper;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\SystemApprove\SystemApproveRecord;
use Risk\Common\Models\SystemApprove\SystemApproveRule;
use Risk\Common\Models\SystemApprove\SystemApproveSandboxRecord;
use Risk\Common\Models\Task\Task;
use Risk\Common\Services\CreditReport\CreditReportServer;
use Risk\Common\Services\SystemApprove\RuleData\RuleData;
use Risk\Common\Services\SystemApprove\RuleServer\SystemApproveBasicRuleServer;
use Risk\Common\Services\SystemApprove\RuleServer\SystemApproveSandboxRuleServer;
use Risk\Common\Services\SystemApprove\RuleServer\ThirdApproveRuleServer;
use Risk\Common\Services\Task\TaskNoticeServer;

class SystemApproveServer extends BaseService {

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

            /** TODO 非正式环境机审默认通过 */
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
     * basic 规则执行
     * @param Order $order
     * @param bool $isSandboxMode
     * @return bool
     * @throws \Exception
     */
    public function basicRulePasses(Order $order, $isSandboxMode = false) {
        $ruleData = new RuleData($order);
        /** 沙盒模式SystemApproveRuleServer重新getRule方法 */
        if ($isSandboxMode) {
            $server = SystemApproveSandboxRuleServer::server($order, $ruleData)->passes();
        } else {
            $server = SystemApproveBasicRuleServer::server($order, $ruleData)->passes();
        }

        $res = $server->isSuccess();
        $resData = $server->getData();
        $resMsg = $server->getMsg();
        list('rejectRecord' => $rejectRecord, 'record' => $record) = $resData;
        $result = $res ? SystemApproveRecord::RESULT_PASS : SystemApproveRecord::RESULT_REJECT;

        if (!$res) {
            /* 联合kreditone 判断 */
            $kscore = $record[SystemApproveRule::RULE_KREDITONE_SCORE]['exe_value'] ?? false;

            if ($kscore && $kscore > 630) {
                foreach ($rejectRecord as $key => $item) {
                    if (in_array($key, self::KREDITONE_UNIT)) {
                        unset($rejectRecord[$key]);
                    }
                }
                if (count($rejectRecord) == 0) {
                    $resMsg = 'K规则通过';
                    $result = SystemApproveRecord::RESULT_PASS;
                    $res = true;
                }
            }
        }

        # nio.wang 规则1跑完 并通过 调用外部黑名单 20200921 jerry 复贷订单不走黑名单
        if ($res && $order->quality=='0') {
            $blackRes = (new ThirdApproveRuleServer())->check($order);
            if ($blackRes != false) {
                $record[SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_BLACK] = $blackRes;
                $result = SystemApproveRecord::RESULT_REJECT;
                $resMsg = "规则未通过";
                $res = false;
                $rejectRecord = [
                    SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_BLACK => [
                        'rule' => SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_BLACK,
                        'value' => $blackRes,
                        'hit_value' => $blackRes
                    ]
                ];
            }
        }
        # 人脸对比，A卡，pan卡，银行卡验证
        if ($res) {
            $thirdRes = SystemApproveThirdServer::server()->isPass($order->user);
            if ($thirdRes !== true) {
                $record['THIRD_API'] = $thirdRes;
                $result = SystemApproveRecord::RESULT_REJECT;
                $resMsg = "规则未通过";
                $res = false;
                $rejectRecord = $thirdRes;
            }
        }
        $record = [
            'app_id' => $order->app_id,
            'task_id' => $this->taskId ?? 0,
            'module' => $server->getModule(),
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'user_type' => $order->quality,
            'result' => $result,
            'hit_rule_cnt' => $res ? 0 : count($rejectRecord),
            'hit_rule' => $res ? null : json_encode($rejectRecord),
            'exe_rule' => json_encode($record),
            'description' => $resMsg,
        ];
        /** 沙盒模式记录另存system_approve_sandbox_record */
        if ($isSandboxMode) {
            SystemApproveSandboxRecord::addRecord($record);
        } else {
            SystemApproveRecord::addRecord($record);
        }

        // 触发 RuleData 析构函数
        unset($ruleData, $server);

        return $result;
    }

}
