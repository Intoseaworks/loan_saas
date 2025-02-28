<?php

namespace Common\Console\Commands\Risk;

use Common\Console\Services\Risk\SystemApproveServer;
use Common\Models\Order\OrderLog;
use Common\Models\Risk\RiskStrategyTask;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Models\User\UserContact;
use Common\Services\Order\OrderServer;
use Common\Services\Risk\RiskStrategyServer;
use Common\Services\Risk\RiskBlacklistServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Third\AirudderHelper;
use Illuminate\Console\Command;
use Yunhan\Utils\Env;
use Common\Models\Order\Order;
use Illuminate\Support\Facades\DB;
use Common\Utils\Email\EmailHelper;

class SystemApproveExec extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk-business:system-approve:exec {--once} {--tid=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '风控-业务：机审执行';

    public function handle() {
        $once = $this->option('once');

        $tid = $this->option('tid');

        $taskModels = RiskStrategyTask::getWaitTask();

        /** 检查待机审订单滞留 每小时告警一次 */
        $maxWaitOrder = 100;
        $count = $taskModels->count();
//        echo $count;
        if (LockRedisHelper::helper()->riskApproveMaxNotice(3600, 'check') && $count > $maxWaitOrder) {
            DingHelper::notice("机审待执行任务滞留超过{$maxWaitOrder}单, 当前等待check机审{$count}单", 'check机审任务滞留,请检查!');
        }

        if (!SystemApproveServer::server()->envSystemApproveExec() && !$once) {
            return;
        }

        foreach ($taskModels as $taskModel) {
            if ($tid != $taskModel->id && $tid) {
                continue;
            }
//            echo $taskModel->strategy_step.PHP_EOL;
            /** @var RiskStrategyTask $taskModel */
            MerchantHelper::setMerchantId($taskModel->merchant_id);

            if (!SystemApproveServer::server()->envSystemApproveExec() && !$once) {
                return;
            }
            echo "TASK_ID:" . $taskModel->id . PHP_EOL;
            $order = $taskModel->order;
            //代码容错,order不存在时不跑了
            if (!$order) {
                continue;
            }
            echo $order->id . "-" . PHP_EOL;
            try {
                switch ($taskModel->status) {
                    /** 待查询策略结果 status=0 exec=1 */
                    case RiskStrategyTask::STATUS_WAIT:
//                    $server = RiskStrategyServer::server()->getDataByRulesPlatform($order, $taskModel->strategy_step);
                        /** 请求成功 */
//                    if ($server->isSuccess()) {
//                        list($skipRiskControl2, $skipManualApproval, $rejectCode, $strategyResult) = $server->getData();
//                        /** 测试环境默认通过 */
////                        $strategyResult = Env::isProd() ? $strategyResult : RiskStrategyServer::STRATEGY_PASS;
//                        /** 规则平台拒绝 直接机审拒绝 跳过后续task 跳出当前任务 */
//                        if ($strategyResult == RiskStrategyServer::STRATEGY_REJECT) {
//                            OrderServer::server()->systemReject($order, $rejectCode);
//                            RiskStrategyTask::toSkipTask($taskModel->id);
//                            continue 2;
//                        }
//                        /** 需要跳过人审 更新到订单manual_check和call_check字段 */
//                        if ($skipManualApproval) {
//                            OrderServer::server()->skipManualApprove($order);
//                        }
//                        /** Step1时，需要跳过Step2 跳出当前任务 */
//                        if ($skipRiskControl2 && $taskModel->strategy_step == RiskStrategyTask::RISK_STRATEGY_STEP_1) {
//                            /** 不需要Step2，Step策略结果作为最终结果 根据规则选择下一步订单状态 */
//                            if ($strategyResult == RiskStrategyServer::STRATEGY_PASS) {
//                                OrderServer::server()->systemToManualOrPass($order->id);
//                            }
//                            RiskStrategyTask::toSkipTask($taskModel->id);
//                            continue 2;
//                        }
//                        RiskStrategyTask::toFinishStatus($taskModel->id);
//                    }
                        break;
                    /** 执行task最终收尾工作 status=1 exec=0 => exec=1 */
                    case RiskStrategyTask::STATUS_FINISH:

                        echo "Order:" . $order->id . "\t" . date("Y-m-d H:i:s") . PHP_EOL;
                        DB::beginTransaction();
                        switch ($taskModel->strategy_step) {
                            case RiskStrategyTask::RISK_STRATEGY_STEP_1:
                                /** 检查task空号检测是否拿到结果 测试环境跳过空号检测 */
//                            echo 45;
                                $checkNullNumber = Env::isProd() ? UserContact::model()->hasCheckNull($order->user_id) : true;
                                if ($checkNullNumber) {
                                    /** 生成第二步任务 完结当前任务exec=0 => exec=1 */
                                    RiskStrategyTask::model()->create($order, RiskStrategyTask::RISK_STRATEGY_STEP_2);
                                    RiskStrategyTask::toFinishExec($taskModel->id);
                                } else {
                                    /** 调用赛舵空号检测 */
                                    $limit = Env::isProd() ? 3 : 1; //测试环境只取1个紧急联系人
//                                $limit = 1; //测试环境只取1个紧急联系人
                                    $userContacts = UserContact::query()->limit($limit)->whereStatus(UserContact::STATUS_ACTIVE)
                                                    ->whereUserId($order->user_id)->whereIsSupplement(UserContact::IS_NOT_SUPPLEMENT)
                                                    ->pluck('contact_telephone')->toArray();
                                    $userContacts = array_merge($userContacts, [$order->user->telephone]); //本人+紧急联系人
                                    foreach ($userContacts as $userContact) {
                                        AirudderHelper::helper()->query($userContact);
                                    }
                                }
                                echo PHP_EOL . 'Step 1 END';
                                break;
                            case RiskStrategyTask::RISK_STRATEGY_STEP_2:
                                /** 规则平台Step2通过 机审通过，否则机审拒绝 */
                                /** 需要跳过人审 更新到订单manual_check和call_check字段 */
                                $lastResult = $taskModel->lastRiskStrategyResult;
                                if(!$lastResult){
                                    break;
                                }
                                $strategyResult = $lastResult->result;
                                $rejectCode = $lastResult->reject_code;
                                echo $strategyResult . PHP_EOL;
                                #取消的订单不需要改状态
                                if (!in_array($order->status, [Order::STATUS_SYSTEM_CANCEL, Order::STATUS_SYSTEM_REJECT, Order::STATUS_WAIT_MANUAL_APPROVE])) {
                                    if ($order->status == Order::STATUS_SYSTEM_APPROVING) {
                                        # 黑名单检测
                                        if ($strategyResult == 'PASS' && $lastResult->skip_black_list == 0) {
                                            /** 风控黑名单(关联入黑) */
                                            RiskBlacklistServer::server()->relateAddBlack($order);
                                            $riskBlacklistServer = RiskBlacklistServer::server()->hitBlacklist($order);
                                            if ($riskBlacklistServer->isSuccess()) {
                                                $hitData = $riskBlacklistServer->getData();
                                                list($refusalCode, $hitKeyword, $hitValues) = $hitData;
                                                $rejectCode = $refusalCode;
                                                $strategyResult = "REJECT";
                                            }
                                        }
                                        if ($strategyResult == 'PASS') {
                                            # 跳过人工审核
                                            if ($lastResult->skip_manual_approval) {
                                                OrderServer::server()->skipManualApprove($order);
                                                if ($lastResult->sug_loan_amt > 0 && $lastResult->sug_loan_amt < $order->principal) {
//                                                echo 999;
                                                    $order->principal = $lastResult->sug_loan_amt;
                                                    $order->save();
                                                    OrderLog::model(OrderLog::SCENARIO_CREATE)
                                                            ->saveModel([
                                                                'merchant_id' => $order->merchant_id,
                                                                'order_id' => $order->id,
                                                                'user_id' => $order->user_id,
                                                                'admin_id' => LoginHelper::getAdminId(),
                                                                'from_status' => $order->status,
                                                                'to_status' => OrderServer::server()->getOrderSystemPassStatus($order),
                                                                'content' => '机审输出结果为跳过人审降额',
                                                                'name' => 'approve_exec',
                                                    ]);
                                                }
                                                OrderServer::server()->systemToManualOrPass($order->id);
                                            } else {
                                                OrderServer::server()->systemToManualOrPass($order->id);
                                            }
                                        } else {
                                            if ($strategyResult == 'REJECT') {
                                                OrderServer::server()->systemReject($order, $rejectCode);
                                            }
                                        }
                                    }
                                }
                                RiskStrategyTask::toFinishExec($taskModel->id);
                                echo PHP_EOL . 'Step 2 END';
                                break;
                        }
                        DB::commit();
                }
            } catch (\Exception $e) {
                DB::rollBack();
                EmailHelper::sendException($e, '风控-业务：机审执行异常');
            }
        }
        echo "ALL END";
    }

    /**
     * 使用策略平台替换 废弃12/31
     */
    protected function systemApproveExec() {
        $once = $this->option('once');

        $taskModels = SystemApproveTask::getWaitSystemApprove();

        /** 检查待机审订单滞留 每小时告警一次 */
        $maxWaitOrder = 100;
        $count = $taskModels->count();
        if (LockRedisHelper::helper()->riskApproveMaxNotice(3600, 'exec') && $count > $maxWaitOrder) {
            DingHelper::notice("机审待执行任务滞留超过{$maxWaitOrder}单, 当前等待机审{$count}单", '机审任务滞留,请检查!');
        }

        if (!SystemApproveServer::server()->envSystemApproveExec() && !$once) {
            return;
        }

        foreach ($taskModels as $taskModel) {
            if (!SystemApproveServer::server()->envSystemApproveExec() && !$once) {
                return;
            }

            MerchantHelper::setMerchantId($taskModel->merchant_id);

            SystemApproveServer::server()->approveExec($taskModel);

            if ($once) {
                $once = false;
            }
        }
    }

}
