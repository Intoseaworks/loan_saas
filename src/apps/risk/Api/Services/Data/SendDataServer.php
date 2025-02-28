<?php

namespace Risk\Api\Services\Data;

use Common\Services\BaseService;
use Risk\Common\Models\Business\BankCard\BankCardPeso;
use Risk\Common\Models\Business\Collection\CollectionRecord;
use Risk\Common\Models\Business\Common\Channel;
use Risk\Common\Models\Business\Order\ManualApproveLog;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\Order\OrderDetail;
use Risk\Common\Models\Business\Order\RepaymentPlan;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Business\User\UserAuth;
use Risk\Common\Models\Business\User\UserContact;
use Risk\Common\Models\Business\User\UserInfo;
use Risk\Common\Models\Business\User\UserThirdData;
use Risk\Common\Models\Business\User\UserWork;
use Risk\Common\Models\Business\UserData\UserApplication;
use Risk\Common\Models\Business\UserData\UserContactsTelephone;
use Risk\Common\Models\Business\UserData\UserPhoneHardware;
use Risk\Common\Models\Business\UserData\UserPhonePhoto;
use Risk\Common\Models\Business\UserData\UserPosition;
use Risk\Common\Models\Business\UserData\UserSms;
use Risk\Common\Models\Task\TaskData;

class SendDataServer extends BaseService
{
    const COMMON_DATA_CHANNEL = 'CHANNEL';

    const COMMON_DATA_TYPE = [
        self::COMMON_DATA_CHANNEL
    ];

    // 允许上传为空的数据类型
    const ALLOW_IS_EMPTY = [
        TaskData::TYPE_USER_POSITION,
        TaskData::TYPE_USER_SMS,
        TaskData::TYPE_USER_PHONE_HARDWARE,
        TaskData::TYPE_USER_CONTACTS_TELEPHONE,
        TaskData::TYPE_USER_APPLICATION,
        TaskData::TYPE_USER_PHONE_PHOTO,
        TaskData::TYPE_USER_THIRD_DATA,
        TaskData::TYPE_USER_AUTH,
        TaskData::TYPE_REPAYMENT_PLAN,
        TaskData::TYPE_COLLECTION_RECORD,
        TaskData::TYPE_MANUAL_APPROVE_LOG,
    ];

    public function saveBankCard($userId, $data)
    {
        $res = (new BankCardPeso())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveCollectionRecord($userId, $data)
    {
        $res = (new CollectionRecord())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveOrder($userId, $data)
    {
        $res = (new Order())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveOrderDetail($userId, $data)
    {
        $res = (new OrderDetail())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveRepaymentPlan($userId, $data)
    {
        $res = (new RepaymentPlan())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUser($userId, $data)
    {
        $res = (new User())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserAuth($userId, $data)
    {
        $res = (new UserAuth())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserContact($userId, $data)
    {
        $res = (new UserContact())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserInfo($userId, $data)
    {
        $res = (new UserInfo())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserThirdData($userId, $data)
    {
        $res = (new UserThirdData())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserWork($userId, $data)
    {
        $res = (new UserWork())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserApplication($userId, $data)
    {
        $res = (new UserApplication())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserContactsTelephone($userId, $data)
    {
        $res = (new UserContactsTelephone())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserPhoneHardware($userId, $data)
    {
        $res = (new UserPhoneHardware())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserPhonePhoto($userId, $data)
    {
        $res = (new UserPhonePhoto())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserPosition($userId, $data)
    {
        $res = (new UserPosition())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveUserSms($userId, $data)
    {
        $res = (new UserSms())->batchAddRiskData($userId, $data);
        return $res;
    }

    public function saveManualApproveLog($userId, $data)
    {
        $res = (new ManualApproveLog())->batchAddRiskData($userId, $data);
        return $res;
    }

    /************************************* common ***************************************************/

    public function saveChannel($data)
    {
        $res = (new Channel())->batchAddRiskCommonData($data);
        return $res;
    }
}
