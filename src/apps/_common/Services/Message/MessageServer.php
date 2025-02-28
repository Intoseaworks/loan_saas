<?php

namespace Common\Services\Message;

use Common\Events\Broadcast\AdminMessageBroadcast;
use Common\Models\Message\Message;
use Common\Models\Message\MessageUser;
use Common\Models\Order\Order;
use Common\Services\BaseService;
use Common\Utils\DingDing\DingHelper;

class MessageServer extends BaseService
{
    /**
     * 发送当日剩余放款金额预警消息
     * @param $merchantId
     * @param float $remainAmount
     * @return bool
     * @throws \Exception
     */
    public function sendDailyRemainAmountWarningMessage($merchantId, float $remainAmount)
    {
        if ($remainAmount < 0) {
            $remainAmount = 0;
        }

        try {
            return $this->sendMessageToMerchant($merchantId, Message::TEMPLATE_DAILY_REMAIN_AMOUNT_WARNING, ['amount' => $remainAmount]);
        } catch (\Exception $e) {
            $errorInfo = [];
            DingHelper::notice("余额预警报错:{$merchantId}", $errorInfo, DingHelper::AT_SOLIANG);
            throw $e;
        }
    }

    /**
     * 发送当日剩余订单创建量预警消息
     * @param $merchantId
     * @param float $remainCount
     * @param $quality
     * @return bool
     * @throws \Exception
     */
    public function sendDailyRemainCreateWarningMessage($merchantId, float $remainCount, $quality)
    {
        if ($remainCount < 0) {
            $remainCount = 0;
        }

        $userTypeText = $quality == Order::QUALITY_NEW ? 'new users' : 'old users';

        try {
            return $this->sendMessageToMerchant($merchantId, Message::TEMPLATE_DAILY_REMAIN_CREATE_WARNING,
                ['count' => $remainCount, 'userTypeText' => $userTypeText]);
        } catch (\Exception $e) {
            $errorInfo = [];
            DingHelper::notice("剩余订单创建数预警报错:{$merchantId}", $errorInfo, DingHelper::AT_SOLIANG);
            throw $e;
        }
    }

    /**
     * 发送消息模板至商户所有用户
     * @param $merchantId
     * @param $template
     * @param array $templateParams
     * @return bool
     * @throws \Exception
     */
    protected function sendMessageToMerchant($merchantId, $template, $templateParams = [])
    {
        $message = MessageUser::sendByTemplateToMerchant($template, $merchantId, $templateParams);

        $relevance = $message->messageUser->pluck('id', 'user_id')->toArray();

        // 发送通知
        event(new AdminMessageBroadcast($merchantId, $relevance, $message->title, $message->content, $message->type));

        MessageUser::updateSendByMessageId($message->id);
        $message->statusToSend();

        return true;
    }

    public function getMessageByUser($userId)
    {
        $messages = MessageUser::getMessageByUser($userId);

        $data = [];
        foreach ($messages as &$message) {
            $data[] = [
                'id' => $message->id,
                'type' => $message->message->type,
                'title' => $message->message->title,
                'description' => $message->message->content,
                'datetime' => $message->send_time,
                'clickClose' => false, // 点击item项关闭通知菜单
                'read' => false,
                //'avatar' => '', // 头像
            ];
        }

        return $data;
    }
}
