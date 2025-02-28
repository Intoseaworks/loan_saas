<?php

namespace Api\Controllers\Callback;

use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\Order\Order;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Response\ServicesApiBaseController;
use Common\Services\Order\OrderServer;
use Common\Services\Risk\RiskServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use JMD\App\lumen\Utils;
use JMD\Libs\Services\BaseRequest;
use JMD\Utils\SignHelper;

class RiskController extends ServicesApiBaseController
{
    public function taskNotice()
    {
        $params = $this->request->post();
        try {
            DB::beginTransaction();

            $task = SystemApproveTask::getByTaskNo($params['task_no']);

            MerchantHelper::setMerchantId($task->merchant_id);

            $this->validateSign();

            if (!$task || !$task->order) {
                DingHelper::notice(var_export($params, true), '风控机审回调，任务记录未找到');
                exit('任务记录未找到');
            }

            if (
                in_array($task->status, [SystemApproveTask::STATUS_FINISH, SystemApproveTask::STATUS_EXCEPTION]) &&
                $task->order->status != Order::STATUS_SYSTEM_APPROVING
            ) {
                exit('SUCCESS');
            }

            if (
                !isset($params['result']) || !isset($params['status']) ||
                ($params['status'] != RiskServer::STATUS_EXCEPTION && !in_array($params['result'], [RiskServer::RESULT_PASS, RiskServer::RESULT_REJECT]))
            ) {
                DingHelper::notice(var_export($params, true), '风控机审回调，回调状态不正确');
                exit('回调状态不正确');
            }

            $hitRuleCode = (isset($params['hit_rule_code']) && $params['hit_rule_code']) ? explode(',', $params['hit_rule_code']) : [];
            if (!$task->finishTask($params['status'], $params['result'], $hitRuleCode, $params['task_desc'] ?? '')) {
                throw new \Exception('状态更新失败');
            }

            $task->refresh();
            /** @var Order $order */
            $order = $task->order;
            $approvePass = false;
            if ($task->status == SystemApproveTask::STATUS_FINISH && $task->result == SystemApproveTask::RESULT_PASS) {
                $approvePass = OrderServer::server()->systemToManualOrPass($order->id);
            } elseif ($task->status == SystemApproveTask::STATUS_FINISH && $task->result == SystemApproveTask::RESULT_REJECT) {
                OrderServer::server()->systemReject($order);
            } elseif ($task->status == SystemApproveTask::STATUS_EXCEPTION) {
                OrderServer::server()->systemRevertToWait($order->id);
            } else {
                DingHelper::notice(var_export($params, true), '风控机审回调，未知响应');
                exit('未知响应');
            }

            DB::commit();

//            if ($approvePass) {
//                event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_APPROVE_PASS));
//            }
            // 风控数据上传
            event(new RiskDataSendEvent($order->user_id, RiskDataSendEvent::NODE_APPROVE_FINISH));

            exit('SUCCESS');
        } catch (\Exception $e) {
            DB::rollBack();
            DingHelper::notice(var_export($params, true) . "\n" . $e->getMessage(), '风控机审回调报错');
            exit($e->getMessage());
        }
    }

    /**
     * 验签
     */
    protected function validateSign()
    {
        $params = $this->request->post();
        $config = Utils::getParam(BaseRequest::CONFIG_NAME);
        $checkSign = SignHelper::validateSign($params, $config['app_secret_key']);

        if (!$checkSign) {
            EmailHelper::send("route:" . request()->getRequestUri() . "\n" . json_encode($params), '风控机审回调验签失败');
            exit('验签错误');
        }
    }
}
