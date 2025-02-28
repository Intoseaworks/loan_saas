<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Risk;

use Carbon\Carbon;
use Common\Services\User\UserDataServer;

class RiskServer extends \Common\Services\Risk\RiskServer
{
    const OPERATOR_REPORT = 'userOperatorReport';
    const USER_POSITION = 'userPosition';
    const CONTACTS = 'contacts';
    const USER_SMS = 'userSms';
    const USER_APP = 'userApp';
    const USER_LAST_POSITION = 'lastPosition';
    const DUO_TOU = 'duoTou';
    const MANUAL_RISK = 'manualRisk';
    const SYS_CHECK_DETAIL = 'systemDetail'; //机审详情
    const ALIPAY_REPORT = 'alipayReport'; //支付宝报告
    const RISK_DATA = [
        self::OPERATOR_REPORT,
        self::USER_POSITION,
        self::CONTACTS,
//        self::USER_SMS,
        self::USER_APP,
        self::DUO_TOU,
        self::MANUAL_RISK,
        self::SYS_CHECK_DETAIL,
        self::ALIPAY_REPORT,
    ];

    public $data = [];

    /**
     * 获取用户上传风控信息
     * @param $userId
     * @param $methods
     * @return mixed
     */
    public function getUserData($userId, $methods)
    {
        $methods = (array)$methods;
        foreach ($methods as $method) {
            if (!method_exists($this, $method)) {
                continue;
            }
            call_user_func([$this, $method], $userId);
        }
        $this->formatData();
        return (array)$this->data;
    }

    private function formatData()
    {
        $this->arrayValueToDate($this->data);
    }

    /**
     * 通讯录列表
     * @param $userId
     * @return array
     */
    public function contacts($userId)
    {
        return $this->data[self::CONTACTS] = self::server()->getRiskUserData($userId,
                UserDataServer::DATA_CONTACTS_VIEW) ?? [];
    }

    /**
     * APP列表
     * @param $userId
     * @return array
     */
    public function userApp($userId)
    {
        return $this->data[self::USER_APP] = self::server()->getRiskUserData($userId,
                UserDataServer::DATA_APPLICATION_VIEW) ?? [];
    }

    /**
     * 定位列表
     * @param $userId
     * @return array
     */
    public function userPosition($userId)
    {
        return $this->data[self::USER_POSITION] = self::server()->getRiskUserData($userId,
                UserDataServer::DATA_POSITION_VIEW) ?? [];
    }

    /**
     * 获取最新的定位信息
     * @param $userId
     * @return mixed
     */
    public function lastPosition($userId)
    {
        $positions = (array)$this->userPosition($userId);
        return current($positions);
    }

    /**
     * 遍历处理数组毫秒时间戳
     * @param $arr
     */
    public function arrayValueToDate(&$arr)
    {
        foreach ($arr as &$val) {
            if (is_array($val)) {
                $this->arrayValueToDate($val);
            } elseif (is_numeric($val) && strlen($val) == 13) {
                $val = Carbon::createFromTimestamp($val / 1000)->toDateTimeString();
            }
        }
    }
}
