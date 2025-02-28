<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-07
 * Time: 15:28
 */

namespace Common\Utils\Code;


use Common\Traits\GetInstance;

class OrderStatus
{
    use GetInstance;

    /**
     * 待初审
     *
     * @var int
     */
    const FIRST_APPROVAL = 1;

    /**
     * 待补充资料
     *
     * @var int
     */
    const FIRST_APPROVAL_SUPPLEMENT = 2;

    /**
     * 待电审
     *
     * @var int
     */
    const CALL_APPROVAL = 3;

    /**
     * 电二审
     *
     * @var int
     */
    const CALL_APPROVAL_SECOND = 4;

    /**
     * 机审拒绝
     *
     * @var int
     */
    const SYSTEM_REJECT = 5;

    /**
     * @param $orderStatus
     * @return mixed|string
     */
    public function getOrderStatusText($orderStatus)
    {
        return $this->orderStatusList()[$orderStatus] ?? '';
    }

    /**
     * @return array
     */
    public function orderStatusList()
    {
        return [
            static::FIRST_APPROVAL => t('待初审', 'approve'),
            static::FIRST_APPROVAL_SUPPLEMENT => t('待初审-已补充资料', 'approve'),
            static::CALL_APPROVAL => t('待电审', 'approve'),
            static::CALL_APPROVAL_SECOND => t('待电审二审', 'approve'),
            static::SYSTEM_REJECT => t('机审拒绝', 'approve'),
        ];
    }
}
