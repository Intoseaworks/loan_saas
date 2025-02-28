<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/12
 * Time: 16:23
 */

namespace Common\Services\User;

use Common\Models\Config\Config;
use Common\Models\User\User;
use Common\Models\User\UserAuth;
use Common\Services\BaseService;
use Common\Utils\Data\DateHelper;

class UserAuthServer extends BaseService
{

    /** 后台认证有效期配置 */
    public static $configDaysValid = null;

    /**
     * @param $userId
     * @param $type
     * @return int
     */
    public function getAuthStatus($userId, $typeName)
    {
        $types = (array)$typeName;
        $auths = UserAuth::model()->getAuths($userId, $types)->toArray();
        if (!$auths) {
            return UserAuth::AUTH_STATUS_NOT;
        }
        $authsTypeTime = array_column($auths, 'time', 'type');
        $authsTypeAuthStatus = array_column($auths, 'auth_status', 'type');
        foreach ($types as $type) {
            if (!array_key_exists($type, $authsTypeAuthStatus)) {
                return UserAuth::AUTH_STATUS_NOT;
            }
            if (!array_key_exists($type, $authsTypeTime)) {
                return UserAuth::AUTH_STATUS_NOT;
            }
            if ($authsTypeAuthStatus[$type] == UserAuth::AUTH_STATUS_FAIL) {
                return UserAuth::AUTH_STATUS_NOT;
            }
            if ($authsTypeAuthStatus[$type] == UserAuth::AUTH_STATUS_EXPIRE) {
                return UserAuth::AUTH_STATUS_EXPIRE;
            }
            if ($authsTypeAuthStatus[$type] != UserAuth::AUTH_STATUS_SUCCESS) {
                return $authsTypeAuthStatus[$type];
            }
        }
        return UserAuth::AUTH_STATUS_SUCCESS;
    }

    /**
     * @param $userId
     * @param $type
     * @return int
     */
    public function getWorkAuthStatus($userId, $type)
    {
        $types = (array)$type;
        $auths = UserAuth::model()->getAuths($userId, $types)->toArray();
        $authsTypeTime = array_column($auths, 'auth_status', 'type');
        foreach ($types as $type) {
            return $authsTypeTime[$type];
        }
    }

    /**
     * @param $userId
     * @param $type
     * @return date
     */
    public function getExpireDate($userId, $type)
    {
        $types = (array)$type;
        $auths = UserAuth::model()->getAuths($userId, $types)->toArray();
        $authsTypeTime = array_column($auths, 'time', 'type');
        foreach ($types as $type) {
                return DateHelper::addDays($authsTypeTime[$type],$this->configDaysValid($type));
        }
    }

    /**
     * @param $user
     * @param $type
     * @param string $time
     * @param int $authStatus
     * @return UserAuth|bool
     */
    public function setAuth($user, $type, $time = '', $authStatus = UserAuth::AUTH_STATUS_SUCCESS)
    {
        if (is_numeric($user)) {
            if (!($user = User::model()->getOne($user))) {
                return false;
            }
        }
        $userId = $user->id;
        if ($time === '') {
            $time = $this->getDateTime();
        }
        UserAuth::model()->clearAuth($userId, $type);
        if (in_array($type, UserAuth::ADDRESS_CARD_TYPE)) {
            UserAuth::model()->setAuth($user, UserAuth::TYPE_ADDRESS, $time, $authStatus);
        }
        return UserAuth::model()->setAuth($user, $type, $time, $authStatus);
    }

    /**
     * @param $userId
     * @param $type
     * @return bool
     */
    public function clearAuth($userId, $type)
    {
        if (in_array($type, UserAuth::ADDRESS_CARD_TYPE)) {
            UserAuth::model()->clearAuth($userId, UserAuth::TYPE_ADDRESS);
        }
        return UserAuth::model()->clearAuth($userId, $type);
    }

    /**
     * 判断是否已完善，并回写完善时间
     *
     * @param User $user
     * @return bool
     */
    public function getIsCompleted($user)
    {
        /** @var User $user */
        $userId = $user->id;
        $types = UserAuth::getTypeIsCompleted();
        $types[] = UserAuth::TYPE_COMPLETED;
        $auths = UserAuth::model()->getAuths($userId, $types)->toArray();
        if (!$auths) {
            return false;
        }
        $authsTypeTime = array_column($auths, 'time', 'type');
        $authsTypeAuthStatus = array_column($auths, 'auth_status', 'type');
        foreach ($types as $type) {
            if ($type == UserAuth::TYPE_COMPLETED) {
                continue;
            }
            if (!array_key_exists($type, $authsTypeAuthStatus)) {
                return false;
            }
            if (!array_key_exists($type, $authsTypeTime)) {
                return false;
            }
            if ($authsTypeAuthStatus[$type] != UserAuth::AUTH_STATUS_SUCCESS) {
                return false;
            }
            if (!$this->checkValid($authsTypeTime[$type], $type)) {
                return false;
            }
        }
        $authsTypeTimeMax = max($authsTypeTime);
        if (!($completedTime = array_get($authsTypeTime, UserAuth::TYPE_COMPLETED))) {
            UserAuthServer::server()->setAuth($userId, UserAuth::TYPE_COMPLETED, $authsTypeTimeMax);
        } elseif ($completedTime < $authsTypeTimeMax) {
            UserAuthServer::server()->setAuth($userId, UserAuth::TYPE_COMPLETED, $authsTypeTimeMax);
        }
        return true;
    }
    
    public function getNoCompletedList($user) {
        $noAuthList = [];
        $types = UserAuth::getTypeIsCompleted();
        $auths = UserAuth::model()->getAuths($user->id, $types)->toArray();
        if (!$auths) {
            return $types;
        }
        $types[] = UserAuth::TYPE_COMPLETED;
        $authsTypeTime = array_column($auths, 'time', 'type');
        $authsTypeAuthStatus = array_column($auths, 'auth_status', 'type');
        foreach ($types as $type) {
            if ($type == UserAuth::TYPE_COMPLETED) {
                continue;
            }
            if (!array_key_exists($type, $authsTypeAuthStatus)) {
                $noAuthList[] = $type;
                continue;
            }
            if (!array_key_exists($type, $authsTypeTime)) {
                $noAuthList[] = $type;
                continue;
            }
            if ($authsTypeAuthStatus[$type] != UserAuth::AUTH_STATUS_SUCCESS) {
                $noAuthList[] = $type;
                continue;
            }
            if (!$this->checkValid($authsTypeTime[$type], $type)) {
                $noAuthList[] = $type;
                continue;
            }
        }
        return $noAuthList;
    }

    /**
     * 判断选填项是否已认证
     *
     * @param $userId
     * @return bool
     */
    public function getOptionalIsCompleted($userId)
    {
        $types = UserAuth::getTypeOptionalIsCompleted();
        if (!$types) {
            return true;
        }
        return $this->getAuthStatusOnly($userId, $types);
    }

    /**
     * 一项通过即为已认证
     *
     * @param $userId
     * @param $types
     * @return bool
     */
    public function getAuthStatusOnly($userId, $types)
    {
        $auths = UserAuth::model()->getAuths($userId, $types)->toArray();
        if (!$auths) {
            return false;
        }
        $authsTypeTime = array_column($auths, 'time', 'type');
        $authsTypeAuthStatus = array_column($auths, 'auth_status', 'type');
        foreach ($types as $type) {
            if (!array_key_exists($type, $authsTypeAuthStatus)) {
                continue;
            }
            if (!array_key_exists($type, $authsTypeTime)) {
                continue;
            }
            if ($authsTypeAuthStatus[$type] != UserAuth::AUTH_STATUS_SUCCESS) {
                continue;
            }
            if (!$this->checkValid($authsTypeTime[$type], $type)) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * 有效期验证
     *
     * @param $time
     * @param $type
     */
    public function checkValid($time, $type)
    {
        if ($authCheck = array_get(UserAuth::AUTH_CHECK_LIST, $type)) {
            $daysValid = $this->configDaysValid($type) ?: $authCheck['daysValid'];
            if (DateHelper::diffInDays($time) > $daysValid) {
                return false;
            }
        }
        return true;
    }

    public function configDaysValid($type)
    {
        if (self::$configDaysValid === null) {
            $authSetting = (new Config())->getAuthSetting();
            self::$configDaysValid = $authSetting ?: [];
        }
        $settingType = array_get(UserAuth::TYPE_BY_SETTING_TYPE, $type, '');
        return array_get(self::$configDaysValid, "{$settingType}.daysValid", false);
    }

    /**
     * 判断某项是否认证
     *
     * @param $userId
     * @param $type
     * @return bool
     */
    public function checkIsAuth($userId, $type)
    {
        $status = $this->getAuthStatus($userId, $type);
        if ($status == UserAuth::STATUS_VALID) {
            return true;
        }
        return false;
    }

    /**
     * 获取认证完成时间
     * @param $userId
     * @return int|mixed
     */
    public function getAuthCompleteTime($userId)
    {
        return UserAuth::model()->whereUserId($userId)->whereStatus(UserAuth::STATUS_VALID)
            ->whereType(UserAuth::TYPE_COMPLETED)->value('time');
    }
}
