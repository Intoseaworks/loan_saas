<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Auth\Card;

use Api\Models\Upload\Upload;
use Api\Models\User\User;
use Api\Models\User\UserInfo;
use Api\Services\BaseService;
use Api\Services\User\UserAuthServer;
use Api\Services\User\UserInfoServer;
use Common\Helper\Auth\CardHelper;
use Common\Jobs\FaceComparisonJob;
use Common\Models\Common\Pincode;
use Common\Models\Config\Config;
use Common\Models\Third\ThirdPartyLog;
use Common\Models\User\UserAuth;
use Common\Models\User\UserThirdData;
use Common\Utils\Services\AuthRequestHelper;
use Common\Utils\ValidatorHelper;
use Illuminate\Support\Facades\DB;

class AuthCardServer extends BaseService
{

    public $no;
    public $type;
    public $params;
    public $user;

    public function __construct($params)
    {
        /** @var $user User */
        $user = \Auth::user();
        $this->no = array_get($params, 'no');
        $this->type = array_get($params, 'type');
        $this->params = $params;
        $this->user = $user;
    }

    public function updateCheckCard()
    {
        $this->no = strtoupper($this->no);
        $fun = camel_case($this->type);
        if (!method_exists($this, $fun)) {
            return $this->outputException('type error');
        }
        call_user_func([$this, $fun]);
        return $this->outputSuccess();
    }

    public function panCard()
    {
        if ($this->user->getPanCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            return;
        }
        # 正反面验证
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_PAN_CARD);
        if (!$front) {
            return $this->outputException('Pancard have not upload');
        }

        if (!ValidatorHelper::validPanNo($this->no)) {
            return $this->outputException("Pan No. Error");
        }

        $fatherName = array_get($this->params, 'father_name', '');
        $cardCheckServer = new CardCheckServer();
        $cardCheckServer->validatePanCard($this->no, $this->user);
        if ($cardCheckServer->isError()) {
            return $this->outputException($cardCheckServer->getMsg());
        }

        $userInfoUpdateData = [
            'father_name' => $fatherName,
            'pan_card_no' => $this->no
        ];
        # 如果没有认证ekyc，则填充panOcr的生日
        if ($this->user->getAadhaarCardKYCStatus() != UserAuth::AUTH_STATUS_SUCCESS) {
            if ($ocrBirthday = (new UserThirdData)->getPanBirthday($this->user->id)) {
                if (strlen($ocrBirthday) == 10) {
                    $userInfoUpdateData['birthday'] = $ocrBirthday;
                }
            }
            $inpuBirthday = $this->user->userInfo->birthday;
            # 生日比对，结果保存
            CardHelper::helper()->saveCheckInputBirthday($this->user, $inpuBirthday, $ocrBirthday, CardHelper::BIRTHDAY_AUTH_TYPE_PAN_CARD);
        }
        UserInfo::updateOrCreateModel(UserInfo::SCENARIO_UPDATE_CARD, ['user_id' => $this->user->id], $userInfoUpdateData);
        $this->user->setScenario(User::SCENARIO_ID_CARD)->saveModel([
            'id_card_no' => $this->no
        ]);
        UserAuthServer::server()->setAuth($this->user, UserAuth::TYPE_PAN_CARD);
        dispatch((new FaceComparisonJob($this->user)));
    }

    public function aadhaarCard()
    {
        if ($this->user->getAadhaarCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            return;
        }
        # 正反面验证
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_AADHAAR_CARD_FRONT);
        $back = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_AADHAAR_CARD_BACK);
        if (!$front) {
            return $this->outputException('Aadhaar front have not upload');
        }
        if (!$back) {
            return $this->outputException('Aadhaar back have not upload');
        }
        if (!ValidatorHelper::validAadhaarNo($this->no)) {
            return $this->outputException("Aadhaar No. Error");
        }
        if (Config::model()->getAadhaarVerifyOn()) {
            $cardCheckServer = new CardCheckServer();
            $cardCheckServer->validateAadhaarCard($this->no, $this->user);
            if ($cardCheckServer->isError()) {
                return $this->outputException($cardCheckServer->getMsg());
            }
        }
        if ($fullname = (new UserThirdData)->getAadhaarName($this->user->id)) {
            $fullname && $this->user->setScenario(User::SCENARIO_ID_CARD)->saveModel([
                'fullname' => $fullname
            ]);
        }
        // 更新为卡片上的性别
        if ($ocrGender = (new UserThirdData)->getaadhaarGender($this->user->id)) {
            if ($ocrGender) {
                UserInfo::updateOrCreateModel(UserInfo::SCENARIO_UPDATE_CARD, ['user_id' => $this->user->id], ['gender' => $ocrGender]);
            }
        }
        $userAuthName = UserAuth::TYPE_AADHAAR_CARD;
        $userInfoCardNoName = UserInfo::AADHAAR_CARD_NO;
        $this->saveAddressAuthData($userAuthName, $userInfoCardNoName);
        $user = \Auth::user();
    }

    public function addressPassport()
    {
        if ($this->user->getAddressPassportStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            return;
        }
        # 正反面验证
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_PASSPORT_IDENTITY);
        //$back = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_PASSPORT_DEMOGRAPHICS);
        if (!$front) {
            return $this->outputException('Passport front have not upload');
        }
        /*if (!$back) {
            return $this->outputException('Passport back have not upload');
        }*/

        $cardCheckServer = new CardCheckServer();
        $cardCheckServer->validatePassport($this->no, $this->user);
        if ($cardCheckServer->isError()) {
            return $this->outputException($cardCheckServer->getMsg());
        }
        $userAuthName = UserAuth::TYPE_ADDRESS_PASSPORT;
        $userInfoCardNoName = UserInfo::PASSPORT_NO;
        $this->saveAddressAuthData($userAuthName, $userInfoCardNoName);
    }

    public function addressVoter()
    {
        if ($this->user->getAddressVoterIdCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            return;
        }
        # 正反面验证
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_VOTER_ID_CARD_FRONT);
        //$back = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_VOTER_ID_CARD_BACK);
        if (!$front) {
            return $this->outputException('Voter front have not upload');
        }
        /*if (!$back) {
            return $this->outputException('Voter back have not upload');
        }*/
        # 印度方不稳定，临时停掉
        $cardCheckServer = new CardCheckServer();
        $cardCheckServer->validateVoterID($this->no, $this->user);
        if ($cardCheckServer->isError()) {
            return $this->outputException($cardCheckServer->getMsg());
        }
        $userAuthName = UserAuth::TYPE_ADDRESS_VOTER;
        $userInfoCardNoName = UserInfo::VOTER_ID_CARD_NO;
        $this->saveAddressAuthData($userAuthName, $userInfoCardNoName);
    }

    public function addressDriving()
    {
        if ($this->user->getAddressDrivingLicenseStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            return;
        }
        # 正反面验证
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_DRIVING_LICENSE_FRONT);
        $back = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_DRIVING_LICENSE_BACK);
        if (!$front) {
            return $this->outputException('Driving front have not upload');
        }
        if (!$back) {
            return $this->outputException('Driving back have not upload');
        }

        $cardCheckServer = new CardCheckServer();
        $cardCheckServer->validateDrivingLicence($this->no, $this->user->id);
        if ($cardCheckServer->isError()) {
            return $this->outputException($cardCheckServer->getMsg());
        }
        $userAuthName = UserAuth::TYPE_ADDRESS_DRIVING;
        $userInfoCardNoName = UserInfo::DRIVING_LICENSE_NO;
        $this->saveAddressAuthData($userAuthName, $userInfoCardNoName);
    }

    public function saveAddressAuthData($userAuthName, $userInfoCardNoName)
    {
        $address = array_get($this->params, 'address', '');
        $pincode = array_get($this->params, 'pincode', '');

        UserInfoServer::server()->updateCardData($this->user->id, $userInfoCardNoName, $this->no, $address, $pincode);
        UserAuthServer::server()->setAuth($this->user, $userAuthName);
    }

    public function authAadhaar($aadhaarImg, $requestId)
    {
        /** @var User $user */
        $user = \Auth::user();
        $inpuBirthday = $user->userInfo->birthday;

        $dataJson = array_get($this->params, 'data');
        /** @var array $data */
        $data = json_decode($dataJson, true);
        (new AuthRequestHelper())->aadhaarKyc($data, $requestId, $user);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_AADHAAR_CARD_KYC, 'aadhaarCard eKYC', 'accuauth eKYC sdk', $requestId, '', $user->id);
        # KYC验卡失败处理
        if (!$aadhaarImg || !$data) {
            (new CardHelper)->ekycError($user, $data);
            $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_VERIFY_FAIL, $dataJson);
            return $this->outputSuccess();
        }
        $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, $dataJson);

        # ekyc图片保存
        (new CardHelper)->ekycImageUpload($user, $aadhaarImg);

        # 用户信息，认证状态更新
        DB::beginTransaction();
        if (!$this->saveAadhaarUserInfo($user, $data)) {
            DB::rollBack();
            return $this->outputError(t('个人信息保存失败', 'auth'));
        }
        $authBirthday = str_replace('-', '/', array_get($data, 'dob', ''));
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_AADHAAR_CARD_KYC);
        UserThirdData::model()->create($user->id, UserThirdData::TYPE_AADHAAR_CARD_KYC, $dataJson, UserThirdData::CHANNEL_SERVICES, $thirdPartLogModel->id);
        DB::commit();
        CardHelper::helper()->saveCheckInputBirthday($user, $inpuBirthday, $authBirthday, CardHelper::BIRTHDAY_AUTH_TYPE_AADHAAR_CARD);
        //dispatch((new FaceComparisonJob($user)));
        return $this->outputSuccess();
    }

    public function saveAadhaarUserInfo($user, $aadhaarAuthData)
    {
        $userInfo = $user->userInfo;
        $data = $aadhaarAuthData;
        if ($aadhaarNumber = array_get($data, 'aadhaarNumber')) {
            $userInfo->aadhaar_card_no = $aadhaarNumber;
        }
        if ($name = array_get($data, 'name')) {
            $user->fullname = $name;
        }
        if ($dob = array_get($data, 'dob')) {
            $userInfo->birthday = str_replace('-', '/', $dob);
        }
        if ($gender = array_get($data, 'gender')) {
            $userInfo->gender = $gender == 'M' ? UserInfo::GENDER_MALE : UserInfo::GENDER_FEMALE;
        }
        if ($state = array_get($data, 'state')) {
            $userInfo->permanent_province = $state;
        }
        if ($dist = array_get($data, 'dist')) {
            $userInfo->permanent_city = $dist;
        }
        if ($address = array_get($data, 'address')) {
            $userInfo->permanent_address = $address;
        }
        if ($pincode = array_get($data, 'pc')) {
            $userInfo->permanent_pincode = $pincode;
            if ($pincodeData = Pincode::model()->getPincodeData($pincode)) {
                $userInfo->permanent_province = $pincodeData->statename;
                $userInfo->permanent_city = $pincodeData->districtname;
            }
        }
        return $user->save() && $userInfo->save();
    }

}
