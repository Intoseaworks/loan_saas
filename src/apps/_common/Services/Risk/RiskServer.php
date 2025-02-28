<?php

namespace Common\Services\Risk;

use Api\Models\User\User;
use Carbon\Carbon;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\Channel\Channel;
use Common\Models\Collection\CollectionRecord;
use Common\Models\Order\OrderDetail;
use Common\Models\Order\RepaymentPlan;
use Common\Models\User\UserThirdData;
use Common\Models\UserData\UserApplication;
use Common\Models\UserData\UserContactsTelephone;
use Common\Models\UserData\UserPhoneHardware;
use Common\Models\UserData\UserPhotoExif;
use Common\Models\UserData\UserPosition;
use Common\Models\UserData\UserSms;
use Common\Services\BaseService;
use Common\Services\User\UserDataServer;
use Common\Utils\MerchantHelper;
use Common\Utils\Risk\RiskHelper;
use JMD\Libs\Risk\RiskSend;

/**
 * Class RiskServer
 * @package Common\Services\Risk
 */
class RiskServer extends BaseService
{
    /** 状态：创建 等待数据完善 */
    const STATUS_CREATE = 'CREATE';
    /** 状态：等待机审 */
    const STATUS_WAITING = 'WAITING';
    /** 状态：机审中 */
    const STATUS_PROCESSING = 'PROCESSING';
    /** 状态：任务完结 */
    const STATUS_FINISH = 'FINISH';
    /** 状态：异常 */
    const STATUS_EXCEPTION = 'EXCEPTION';

    /** 结果：空 */
    const RESULT_NULL = 'NA';
    /** 结果：通过 */
    const RESULT_PASS = 'PASS';
    /** 结果：拒绝 */
    const RESULT_REJECT = 'REJECT';

    // 发送数据接口返回：成功
    const SEND_DATA_STATUS_SUCCESS = 'SUCCESS';
    // 发送数据接口返回：失败
    const SEND_DATA_STATUS_FAILED = 'FAILED';

    // 创建机审任务
    protected $routeStartTask = 'api/risk/task/start_task';
    // 执行机审任务
    protected $routeExecTask = 'api/risk/task/exec_task';
    // 机审结果通知路由
    protected $routeRiskTaskNotice = 'app/callback/risk/task_notice';

    public function getRiskUserData($userId, $method, array $params = [])
    {
        //直接调用本地
        return UserDataServer::server()->userInfo($userId, $method, $params);
    }

    protected $sendDataLimit = 5000;

    /**
     * 发送用户数据
     * @param $userId
     * @param null $type
     * @param null $taskNo
     * @return \JMD\Libs\Risk\DataFormat
     */
    public function sendDataAll($userId, $type = null, $taskNo = null)
    {
        $user = User::whereId($userId)->first();
        MerchantHelper::setMerchantId($user->merchant_id);
        $riskSend = RiskHelper::getRiskSendObj($user, $taskNo);

        if (is_null($type) || in_array(RiskSend::TYPE_USER, $type)) {
            $riskSend->setUser($user->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_ORDER, $type)) {
            $riskSend->setOrder($user->orders->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_ORDER_DETAIL, $type)) {
            $orderIds = $user->orders->pluck('id')->toArray();
            $orderDetails = OrderDetail::query()->whereIn('order_id', $orderIds)->get()->toArray();
            $riskSend->setOrderDetail($orderDetails);
        }
        if (is_null($type) || in_array(RiskSend::TYPE_BANK_CARD, $type)) {
            $riskSend->setBankCard(optional($user->bankCards)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_INFO, $type)) {
            $riskSend->setUserInfo(optional($user->userInfo)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_CONTACT, $type)) {
            $riskSend->setUserContact(optional($user->userContacts)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_AUTH, $type)) {
            $riskSend->setUserAuth(optional($user->userAuths)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_WORK, $type)) {
            $riskSend->setUserWork(optional($user->userWork)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_REPAYMENT_PLAN, $type)) {
            $riskSend->setRepaymentPlan(RepaymentPlan::whereUserId($userId)->get()->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_COLLECTION_RECORD, $type)) {
            $riskSend->setCollectionRecord(CollectionRecord::whereUserId($userId)->get()->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_THIRD_DATA, $type)) {
            $riskSend->setUserThirdData(UserThirdData::whereUserId($userId)->get()->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_APPLICATION, $type)) {
            $riskSend->setUserApplication(UserApplication::whereUserId($userId)->get()->slice(0, $this->sendDataLimit)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_CONTACTS_TELEPHONE, $type)) {
            $riskSend->setUserContactsTelephone(UserContactsTelephone::whereUserId($userId)->get()->slice(0, $this->sendDataLimit)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_PHONE_HARDWARE, $type)) {
            $riskSend->setUserPhoneHardware(UserPhoneHardware::whereUserId($userId)->get()->slice(0, $this->sendDataLimit)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_PHONE_PHOTO, $type)) {
            $riskSend->setUserPhonePhoto(UserPhotoExif::whereUserId($userId)->get()->slice(0, $this->sendDataLimit)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_POSITION, $type)) {
            $riskSend->setUserPosition(UserPosition::whereUserId($userId)->get()->slice(0, $this->sendDataLimit)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_USER_SMS, $type)) {
            $riskSend->setUserSms(UserSms::whereUserId($userId)->get()->slice(0, $this->sendDataLimit)->toArray());
        }
        if (is_null($type) || in_array(RiskSend::TYPE_MANUAL_APPROVE_LOG, $type)) {
            $manualApproveLog = ManualApproveLog::query()->where('user_id', $user->id)->get()->toArray();
            $riskSend->setManualApproveLog($manualApproveLog);
        }

        return $riskSend->execute();
    }

    /**
     * @param $merchantId
     * @param $type
     * @return mixed|\JMD\Libs\Risk\DataFormat
     */
    public function sendDataCommon($merchantId, $type = null)
    {
        return MerchantHelper::callbackOnce(function () use ($merchantId, $type) {
            MerchantHelper::setMerchantId($merchantId);

            $riskSend = RiskHelper::getRiskSendCommonObj();

            if (is_null($type) || in_array(RiskSend::COMMON_TYPE_CHANNEL, $type)) {
                $channel = Channel::getAllChannel()->toArray();
                $riskSend->setChannel($channel);
            }

            return $riskSend->execute();
        });
    }

    public function startTask($userId, $orderId)
    {
        $noticeUrl = str_finish(config('config.api_client_domain'), '/') . $this->routeRiskTaskNotice;

        $params = [
            'user_id' => $userId,
            'order_id' => $orderId,
            'notice_url' => $noticeUrl,
        ];

        return RiskHelper::sendRiskRequest($this->routeStartTask, $params);
    }

    public function execTask($taskNo)
    {
        $params = [
            'task_no' => $taskNo,
        ];

        return RiskHelper::sendRiskRequest($this->routeExecTask, $params);
    }
}
