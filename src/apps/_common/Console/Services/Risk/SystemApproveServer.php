<?php

namespace Common\Console\Services\Risk;

use Carbon\Carbon;
use Common\Models\Order\Order;
use Common\Models\Risk\RiskStrategyTask;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Services\BaseService;
use Common\Services\Order\OrderServer;
use Common\Services\Risk\RiskBlacklistServer;
use Common\Services\Risk\RiskSendServer;
use Common\Services\Risk\RiskServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use JMD\Libs\Risk\RiskSend;
use Yunhan\Utils\Env;
use Common\Jobs\User\UserBehaviorStatisticsJob;

class SystemApproveServer extends BaseService
{
    /**
     * 机审总开关
     * @return mixed
     */
    public function envSystemApprove()
    {
        return config('config.system_approve');
    }

    /**
     * 机审任务执行开关
     * @return mixed
     */
    public function envSystemApproveExec()
    {
        return config('config.system_approve_exec');
    }

    /**
     *
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    public function approve(Order $order)
    {
        MerchantHelper::setMerchantId($order->merchant_id);

        /** 订单创建后3分钟才进入机审，确保资料上传完毕 */
        $carbon = Carbon::parse($order->created_at);
        if (Env::isProd() && (new Carbon)->diffInMinutes($carbon, true) < 3) {
            return false;
        }

        // 机审锁，避免与队列重复执行
        if (!LockRedisHelper::helper()->systemApprove($order->id)) {
            return false;
        }

        // 有进行中的机审任务
        if (SystemApproveTask::inSystemApprove($order->id)) {
            return false;
        }

        DB::beginTransaction();
        try {
            // 流转订单状态为机审中
            dispatch(new UserBehaviorStatisticsJob($order));
            OrderServer::server()->systemApproving($order->id);
            /** 风控黑名单(关联入黑) */
            //RiskBlacklistServer::server()->relateAddBlack($order);
            /** 检查是否命中风控黑名单 */
            //$riskBlacklistServer = RiskBlacklistServer::server()->hitBlacklist($order);
            /** 命中风控黑名单 直接拒绝 */
            //if ($riskBlacklistServer->isSuccess()) {
            //    $hitData = $riskBlacklistServer->getData();
            //    list($refusalCode, $hitKeyword, $hitValues) = $hitData;
            //    OrderServer::server()->systemReject($order, $refusalCode);
            //} else {
                /** 未命中黑名单 发起策略节点1执行请求 */
                RiskStrategyTask::model()->create($order, RiskStrategyTask::RISK_STRATEGY_STEP_1);
            //}

            /** 使用策略平台替换 废弃12/31 */
//            $res = RiskServer::server()->startTask($order->user_id, $order->id);
//
//            if (!$res->isSuccess()) {
//                throw new \Exception($res->getMsg());
//            }
//            $data = $res->getData();
//
//            $requiredDataType = $data['required'];
//
//            $task = SystemApproveTask::createTask([
//                'user_id' => $order->user_id,
//                'order_id' => $order->id,
//                'user_type' => $order->quality,
//                'task_no' => $data['taskNo'],
//            ]);
//            $this->sendQueueByType($task, $requiredDataType);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            EmailHelper::sendException($e, '风控-业务：机审创建异常');
            return false;
        }
    }

    public function sendQueueByType($task, $type)
    {
        if (!LockRedisHelper::helper()->systemApproveSendData($task->task_no)) {
            return true;
        }

        return RiskSendServer::server()->sendByType($task->user_id, $type, $task);
    }

    public function approveExec(SystemApproveTask $taskModel)
    {
        try {
            $res = RiskServer::server()->execTask($taskModel->task_no);
            $data = $res->getData();

            if (!$res->isSuccess() || !isset($data['status'])) {
                throw new \Exception($res->getMsg());
            }

            if ($data['status'] == RiskServer::STATUS_CREATE) {
                $type = $data['required'] ?? RiskSend::ALL_TYPE;
                $this->sendQueueByType($taskModel, $type);
            } elseif ($data['status'] == RiskServer::STATUS_WAITING) {
                $taskModel->toProcessing();
            }
            return true;
        } catch (\Exception $e) {
            DingHelper::notice(
                json_encode([
                    'file' => $e->getFile() . ":" . $e->getLine(),
                    'msg' => $e->getMessage(),
                    'task_id' => $taskModel->id,
                    'data' => $data ?? '',
                ]),
                '机审任务执行异常 - ' . '-' . app()->environment()
            );
            return false;
        }
    }
}
