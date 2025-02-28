<?php

namespace Risk\Common\Helper;

use Common\Utils\Helper;
use Common\Utils\MerchantHelper;
use Risk\Common\Redis\Lock\LockRedis;

class LockRedisHelper
{
    use Helper;

    // 机审锁
    const LOCK_SYSTEM_SYSTEM_APPROVE = 'system:system-approve';
    // 用户关联信息更新锁
    const LOCK_UPDATE_USER_ASSOCIATED_RECORD = 'sys:update-user-associated-record';
    // 通讯录名称比对贷款app入高危电话库锁
    const LOCK_RISK_CONTACT_LOAN_APP_COMPARISON = 'risk:contact-loan-app-comparison';

    // 机审滞留告警间隔锁
    const LOCK_RISK_APPROVE_MAX_NOTICE = 'risk-approve-max-notice';

    /**
     * 机审滞留告警间隔锁
     * @param $second
     * @return bool
     */
    public function riskApproveMaxNotice($second)
    {
        $key = self::LOCK_RISK_APPROVE_MAX_NOTICE;
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

    /**
     * 机审锁
     * @param $appId
     * @param $orderNo
     * @param int $second
     * @return bool
     */
    public function systemApprove($appId, $orderNo, $second = 120)
    {
        $key = str_finish(self::LOCK_SYSTEM_SYSTEM_APPROVE, ':') . $appId . ':' . $orderNo;
        return $this->addLock($key, $second);
    }

    /**
     * 更新用户关联数据锁
     * @param $userId
     * @param float|int $second
     * @return bool
     */
    public function updateUserAssociatedRecord($userId, $second = 60 * 30)
    {
        $merchantStr = MerchantHelper::getMerchantId() ? MerchantHelper::getMerchantId() . ':' : '';
        $key = str_finish(self::LOCK_UPDATE_USER_ASSOCIATED_RECORD, ':') . $merchantStr . $userId;
        return $this->addLock($key, $second);
    }

    /**
     * 通讯录名称比对贷款app入高危电话库锁
     * @param $orderId
     * @param float|int $second
     * @return bool
     */
    public function riskContactLoanAppComparison($orderId, $second = 60 * 30)
    {
        $key = str_finish(self::LOCK_RISK_CONTACT_LOAN_APP_COMPARISON, ':') . $orderId;
        return $this->addLock($key, $second);
    }

    /**
     * 判断是否有锁
     * @param $orderId
     * @return bool
     */
    public function hasRiskContactLoanAppComparison($orderId)
    {
        $key = str_finish(self::LOCK_RISK_CONTACT_LOAN_APP_COMPARISON, ':') . $orderId;
        return $this->hasLock($key);
    }

    public function hasLock($key)
    {
        if (!LockRedis::redis()->get($key)) {
            return false;
        }
        return true;
    }
}
