<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/12
 * Time: 11:05
 */

namespace Common\Redis\Notice;

use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class NoticeRedis
{
    use BaseRedis;


    public function read($noticeId, $userId)
    {
        $count = $this->redis::sadd($this->getKey($noticeId), $userId);
        return $count;
    }

    public function getKey($noticeId = '')
    {
        return RedisKey::NOTICE_PREFIX . $noticeId;
    }

    public function isRead($noticeId, $userId)
    {
        return $this->redis::sismember($this->getKey($noticeId), $userId);
    }

}