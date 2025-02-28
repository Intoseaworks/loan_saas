<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/14
 * Time: 15:49
 */

namespace Api\Controllers\Data;

use Api\Models\Upload\Upload;
use Api\Models\User\User;
use Api\Rules\Data\BankcardRule;
use Api\Rules\Data\IDConfirmRule;
use Api\Rules\Data\UploadFacesRule;
use Api\Rules\Upload\UploadRule;
use Api\Services\Bankcard\BankcardServer;
use Api\Services\Data\DataServer;
use Api\Services\IDCard\IDCardServer;
use Api\Services\Upload\UploadServer;
use Api\Services\User\UserInfoServer;
use Common\Exceptions\ApiException;
use Common\Models\UserData\UserApplication;
use Common\Models\UserData\UserBehavior;
use Common\Models\UserData\UserContactsTelephone;
use Common\Models\UserData\UserGyroscope;
use Common\Models\UserData\UserPhoneHardware;
use Common\Models\UserData\UserPhotoExif;
use Common\Models\UserData\UserPosition;
use Common\Models\UserData\UserSms;
use Common\Response\ApiBaseController;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Models\UserData\UserContactRecord;


class DataController extends ApiBaseController
{
    /** @var User user */
    protected $user;

    public function bankcardCreate(BankcardRule $rule)
    {
        if (!$rule->validate(BankcardRule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $params = $this->getParams();

        $params['user_id'] = $this->identity()->id;

        $model = BankcardServer::server()->create($params);

        if (!$model) {
            return $this->resultFail('银行卡信息保存记录失败');
        }
        return $this->resultSuccess($model);
    }

    /**
     * 身份证上传
     * 备注：
     * 包含（身份证正面上传，身份证反面上传，手持身份证上传）
     * @param UploadRule $rule
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function uploadIDCard(UploadRule $rule)
    {
        $userId = $this->identity()->id;

        if (!$rule->validate($rule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }

        if (!$data = UploadServer::moveFile($file = $this->request->file('file'))) {
            return $this->resultFail('上传文件保存失败');
        }

        $data['type'] = $this->getParam('type');
        $data['user_id'] = $userId;
        $data['source_id'] = $userId;
        if (!Upload::model(Upload::SCENARIO_CREATE)->saveModel($data)) {
            return $this->resultFail('上传文件保存记录失败');
        }

        $server = IDCardServer::server();

        switch ($data['type']) {
            case Upload::TYPE_ID_FRONT:
                $server->authFront($file->getRealPath());
                break;
            case Upload::TYPE_ID_BACK:
                $server->authBack($file->getRealPath());
                break;
            case Upload::TYPE_ID_HANDHELD:
                $server->authHandheld();
                break;
            default:
                return $this->resultFail('图片类型上传错误');
        }

        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($server->getData());
    }

    /**
     * 身份信息确认
     * @param IDConfirmRule $rule
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function IDConfirm(IDConfirmRule $rule)
    {
        $this->identity();
        if (!$rule->validate(IDConfirmRule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $params = $this->getParams();
        IDCardServer::server()->IDConfirm($params);
        return $this->resultSuccess();
    }

    /**
     * 身份认证校验
     * @return array|bool|mixed|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function identityList()
    {
        $this->identity();
        return $this->resultSuccess(IDCardServer::server()->getIdentity());
    }

    /**
     * 上传人脸识别图
     * @param UploadFacesRule $rule
     * @return array
     * @throws \Exception
     */
    public function uploadFaces(UploadFacesRule $rule)
    {
        $this->identity();
        if (!$rule->validate(UploadFacesRule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }

        $imgArr = [
            'image_best',
            'image_env',
            'image_action1',
            'image_action2',
        ];

        //@phan-suppress-next-line PhanUndeclaredProperty
        $userId = \Auth::user()->id;

        $attrs = [];
        foreach ($imgArr as $val) {
            /** 支持一张/多张人脸图片 */
            if (!$file = $this->request->file($val)) {
                continue;
            }

            if (!$data = UploadServer::moveFile($file)) {
                return $this->resultFail('上传文件保存失败');
            }

            $data['type'] = Upload::TYPE_FACES;
            $data['user_id'] = $userId;
            $data['source_id'] = $userId;

            $attrs[] = $data;
        }

        if (!Upload::model(Upload::SCENARIO_CREATE)->saveModels($attrs)) {
            return $this->resultFail('上传文件保存记录失败');
        }

        IDCardServer::server()->authFaces();

        return $this->resultSuccess();
    }

    /**
     * 通讯录数据上传风控
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userContact()
    {
        $contactData = $this->getDataParams('通讯录数据');

        $applyId = $this->user->order->id ?? 0;
        $tableName = "user_contacts_telephone_" . date("ym");
        if (DataServer::server()->isCanUploadContact($this->user->id) == 0) {
            $insertData = DataServer::server()->getValidContactData($this->user->id, $contactData, $applyId);
            foreach ($insertData as $insert) {
                $insert['created_at'] = date('Y-m-d H:i:s');
                $userContacts = new UserContactsTelephone($insert);
                $userContacts->setTable($tableName);
                try {
                    $userContacts->save();
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $this->resultSuccess(null, '通讯录数据保存成功');
    }

    /**
     * 用户位置信息上传风控
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userPosition()
    {
        $data = $this->getDataParams('位置信息');

        $applyId = $this->user->order->id ?? 0;

        $insertData = DataServer::server()->getValidPositionData($this->user->id, $data, $applyId);

        (new UserPosition())->add($this->user->id, $insertData);

        //DataServer::server()->saveUserData($this->user, DataServer::METHOD_USER_POSITION, $insertData);

        return $this->resultSuccess(null, '位置信息保存成功');
    }

    /**
     * 短信记录上传风控
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userSms()
    {
        $data = $this->getDataParams('短信记录');
        $applyId = $this->user->order->id ?? 0;
        $tableName = "user_sms_" . date("ym");
        $insertData = DataServer::server()->getValidSmsData($this->user->id, $data, $applyId);
        foreach ($insertData as $insert) {
            $insert['created_at'] = date('Y-m-d H:i:s');
            $userSms = new UserSms($insert);
            $userSms->setTable($tableName);
            try {
                $userSms->save();
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this->resultSuccess(null, '短信记录保存成功');
    }

    /**
     * APP列表上传风控
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userAppList()
    {
        $data = $this->getDataParams('应用数据');

        $applyId = $this->user->order->id ?? 0;
        $tableName = "user_application_" . date("ym");
        if (DataServer::server()->isCanUploadAppList($this->user->id) == 0) {
            $insertData = DataServer::server()->getValidAppData($this->user->id, $data, $applyId);
            foreach ($insertData as $insert) {
                $insert['created_at'] = date('Y-m-d H:i:s');
                $userApp = new UserApplication($insert);
                $userApp->setTable($tableName);

                try {
                    $userApp->save();
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $this->resultSuccess(null, '应用数据保存成功');
    }

    /**
     * 硬件信息上传风控
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userPhoneHardware()
    {
        $data = $this->getDataParams('硬件信息');

        $applyId = $this->user->order->id ?? 0;

        $insertData = DataServer::server()->getValidPhoneHardwareData($this->user->id, $data, $applyId);

        (new UserPhoneHardware)->add($insertData);

        //DataServer::server()->saveUserData($this->user, DataServer::METHOD_USER_PHONE_HARDWARE, $insertData);

        return $this->resultSuccess(null, '硬件信息保存成功');
    }

    /**
     * 获取参数
     * @param string $msg
     * @return mixed
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getDataParams($msg = '数据')
    {
        $this->user = $this->identity();

        $dataStr = $this->getParam('data_json');
        if ($msg == '通讯录数据' && substr($dataStr, 0, 1) != '[') {
            $dataStr = base64_decode($dataStr);
            $dataStr = gzinflate(substr($dataStr, 2, -4));
        }
        if ($msg == '短信记录' && substr($dataStr, 0, 1) != '[') {
            $dataStr = base64_decode($dataStr);
            $dataStr = gzinflate(substr($dataStr, 2, -4));
        }
        if ($msg == '应用数据' && substr($dataStr, 0, 1) != '[') {
            $dataStr = base64_decode($dataStr);
            $dataStr = gzinflate(substr($dataStr, 2, -4));
        }
        $data = ArrayHelper::jsonToArray($dataStr);

        if (!is_array($data)) {
            throw new ApiException($msg . '获取失败');
        }

        return $data;
    }

    /**
     * 获取参数
     * @param string $msg
     * @return mixed
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getDataParamsNotoken($msg = '数据')
    {
//        $this->user = $this->identity();

        $dataStr = $this->getParam('data_json');
        if ($msg == '通讯录数据' && substr($dataStr, 0, 1) != '[') {
            $dataStr = base64_decode($dataStr);
            $dataStr = gzinflate(substr($dataStr, 2, -4));
        }
        if ($msg == '短信记录' && substr($dataStr, 0, 1) != '[') {
            $dataStr = base64_decode($dataStr);
            $dataStr = gzinflate(substr($dataStr, 2, -4));
        }
        if ($msg == '应用数据' && substr($dataStr, 0, 1) != '[') {
            $dataStr = base64_decode($dataStr);
            $dataStr = gzinflate(substr($dataStr, 2, -4));
        }
        $data = ArrayHelper::jsonToArray($dataStr);

        if (!is_array($data)) {
            throw new ApiException($msg . '获取失败');
        }

        return $data;
    }

    /**
     * 更新googleToken
     *
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateGoogleToken()
    {
        $user = $this->identity();
        $googleToken = $this->getParam('google_token');
        if (!$googleToken) {
            return $this->resultFail('缺少google_token参数');
        }
        UserInfoServer::server()->updateGoogleToken($googleToken);
        return $this->resultSuccess();
    }

    public function userPhotoExif()
    {
        $datas = (array)$this->getDataParams('相册信息');

        $applyId = $this->user->order->id ?? 0;

        $insertData = DataServer::server()->getValidPhotoData($this->user->id, $datas, $applyId);
        if (!$insertData) {
            DingHelper::notice(['user_id' => $this->user->id, 'data' => $datas], '相册信息异常');
            return $this->resultFail('相册信息保存失败');
        }
        (new UserPhotoExif())->add($insertData);
        return $this->resultSuccess(null, '相册信息保存成功');
    }

    /**
     * 设置通讯录个数
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userContactCount()
    {
        $user = $this->identity();
        $count = $this->getParam('count', null);

        if (!isset($count) || !is_numeric($count)) {
            return $this->resultFail('count field not pass muster');
        }

        return $this->resultSuccess(UserInfoServer::server()->setUserContactCount($count));
    }

    /**
     * 上传陀螺仪信息
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userGyroscope()
    {
        $data = $this->getDataParams('陀螺仪信息');
        $applyId = $this->user->order->id ?? 0;

        $tableName = "user_gyroscope_" . date("ym");
        $step = $this->getParam('step', UserGyroscope::STEP_LOGIN);
        $insertData = [];
        foreach ($data as $item) {
            $insert = DataServer::server()->getValidGyroscopeData($this->user->id, $item, $applyId, $step);

            $insert['created_at'] = date('Y-m-d H:i:s');
            $userGyr = new UserGyroscope($insert);
            $userGyr->setTable($tableName);
            try {
                $userGyr->save();
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this->resultSuccess();
    }

    /**
     * 上传用户行为数据
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userBehavior()
    {
        $data = $this->getDataParams('用户行为信息');

        $applyId = $this->user->order->id ?? 0;

        $insertData = DataServer::server()->getValidBehaviorData($this->user->id, $data, $applyId);

        (new UserBehavior())->batchAdd($insertData);

        return $this->resultSuccess();
    }

    /**
     * 上传用户行为数据未登录前
     * @return array
     * @throws ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userBehaviorNotoken()
    {
        $data = $this->getDataParamsNotoken('用户行为信息');

        $applyId = 0;

        $insertData = DataServer::server()->getValidBehaviorData(0, $data, $applyId);

        (new UserBehavior())->batchAdd($insertData);

        return $this->resultSuccess();
    }

    /**
     * 上传通讯记录
     */
    public function userContactRecord()
    {
        $data = $this->getDataParamsNotoken('用户通讯记录');
        $user = $this->identity();
        $applyId = $user->order->id ?? 0;
        $tableName = "user_contact_record_" . date("ym");
        foreach ($data as $item) {
            $callTime = array_get($item, 'i', null);
            if ($callTime) {
                if (strlen($callTime) == 13) {
                    $callTime = $callTime / 1000;
                }
                $callTime = date('Y-m-d H:i:s', $callTime);
            }
            $insert = [
                "contact_fullname" => $item['n'],
                "contact_telephone" => $item['t'],
                "order_id" => $applyId,
                "user_id" => $user->id,
                "contact_telephone_11P" => substr($item['t'], -11),
                "call_time" => $callTime,
                "duration" => $item['d'],
                "type" => $item['p'],
                "created_at" => date("Y-m-d H:i:s"),
            ];

            $userRecord = new UserContactRecord($insert);
            $userRecord->setTable($tableName);
            try {
                $userRecord->save();
            } catch (\Exception $e) {
                continue;
            }
        }
        return $this->resultSuccess();
    }

}
