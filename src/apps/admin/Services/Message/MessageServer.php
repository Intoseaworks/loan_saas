<?php

namespace Admin\Services\Message;

use Common\Models\Message\MessageUser;
use Common\Utils\LoginHelper;

class MessageServer extends \Common\Services\Message\MessageServer
{
    public function readMessage($id)
    {
        $userId = LoginHelper::getAdminId();
        return MessageUser::updateStatusToRead($userId, $id);
    }

    public function readMessageByMessageId($messageId)
    {
        $userId = LoginHelper::getAdminId();
        $messageUserIds = MessageUser::getMessageByUser($userId, ['message_id' => $messageId])->pluck('id')->toArray();

        return MessageUser::updateStatusToRead($userId, $messageUserIds);
    }
}
