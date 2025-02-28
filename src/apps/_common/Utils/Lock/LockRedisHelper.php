<?php

namespace Common\Utils\Lock;

use Common\Redis\Lock\LockRedis;
use Common\Utils\Helper;

class LockRedisHelper
{
    use Helper;

    const LOCK_ORDER_CREATE = 'order:create:';
    const LOCK_REMIT_SUBMIT = 'remit:submit:';
    const LOCK_RISK_APPROVE_MAX_NOTICE = 'risk-approve-max-notice';
    // 确认续期锁
    const LOCK_ORDER_RENEWAL_CONFIRM = 'order:renewal-confirm';
    // cashnow订单签约
    const LOCK_ORDER_SIGN_INTERVAL = 'order:sign-interval';
    // cashnow用户银行卡绑定lock
    const LOCK_USER_BANK_CARD_BIND = 'user:bank-card-bind';
    // 机审锁
    const LOCK_SYSTEM_SYSTEM_APPROVE = 'system:system-approve';
    // 机审锁
    const LOCK_SYSTEM_SYSTEM_APPROVE_SEND_DATA = 'system:system-approve:send_data';
    // 后台预警锁
    const LOCK_ADMIN_SEND_WARNING = 'admin:send-warning';

    /**
     * 创建订单重复提交锁
     *
     * @param $userId
     * @return bool
     */
    public function orderCreate($userId)
    {
        $key = self::LOCK_ORDER_CREATE . $userId;
        return $this->addLock($key);
    }

    /**
     * 人工确认放款重复提交锁
     * @param $orderId
     * @return bool
     */
    public function remitSubmit($orderId)
    {
        $key = self::LOCK_REMIT_SUBMIT . $orderId;
        return $this->addLock($key);
    }

    /**
     * 机审滞留告警间隔锁
     *
     * @param $second
     * @param $type
     * @return bool
     */
    public function riskApproveMaxNotice($second, $type = '')
    {
        $key = self::LOCK_RISK_APPROVE_MAX_NOTICE . $type;
        /** 间隔1小时 */
        return $this->addLock($key, $second);
    }

    /**
     * 订单续期锁
     * @param $orderId
     * @param int $second
     * @return bool
     */
    public function orderRenewalConfirm($orderId, $second = 10)
    {
        $key = str_finish(self::LOCK_ORDER_RENEWAL_CONFIRM, ':') . $orderId;
        return $this->addLock($key, $second);
    }

    /**
     * 用户绑定银行卡lock
     * @param $userId
     * @param int $second
     * @return bool
     */
    public function userBankCardBind($userId, $second = 3)
    {
        $key = str_finish(self::LOCK_USER_BANK_CARD_BIND, ':') . $userId;
        return $this->addLock($key, $second);
    }

    public function orderSign($orderId, $second = 30)
    {
        $key = str_finish(self::LOCK_ORDER_SIGN_INTERVAL, ':') . $orderId;
        return $this->addLock($key, $second);
    }

    /**
     * 机审锁
     * @param $orderId
     * @param int $second
     * @return bool
     */
    public function systemApprove($orderId, $second = 120)
    {
        $key = str_finish(self::LOCK_SYSTEM_SYSTEM_APPROVE, ':') . $orderId;
        return $this->addLock($key, $second);
    }

    /**
     * 机审上传数据锁
     * 默认半小时
     * @param $taskNo
     * @param $second
     * @return bool
     */
    public function systemApproveSendData($taskNo, $second = 1800)
    {
        $key = str_finish(self::LOCK_SYSTEM_SYSTEM_APPROVE_SEND_DATA, ':') . $taskNo;
        return $this->addLock($key, $second);
    }

    /**
     * 后台通知预警锁
     * @param $merchantId
     * @param $type
     * @param $key
     * @param float|int $second
     * @return bool
     */
    public function adminSendWarning($merchantId, $type, $key, $second = 3600 * 24)
    {
        $date = date('Y-m-d');
        $keys = [$date, $merchantId, $type, $key];
        $keys = implode(':', $keys);

        $key = str_finish(self::LOCK_ADMIN_SEND_WARNING, ':') . $keys;

        return $this->addLock($key, $second);
    }

    /**
     * 公共添加锁
     *
     * @param $key
     * @param int $second 秒
     * @return bool
     */
    public function addLock($key, $second = 10)
    {
        if (LockRedis::redis()->get($key)) {
            return false;
        }
        LockRedis::redis()->set($key, $second);
        return true;
    }

    public function hasLock($key)
    {
        if (!LockRedis::redis()->get($key)) {
            return false;
        }
        return true;
    }
}
