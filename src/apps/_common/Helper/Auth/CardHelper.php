<?php


namespace Common\Helper\Auth;


use Api\Models\Upload\Upload;
use Api\Models\User\User;
use Api\Services\Auth\Skip\SkipServer;
use Api\Services\Upload\UploadServer;
use Common\Helper\BaseHelper;
use Common\Models\User\UserThirdData;
use Common\Redis\CommonRedis;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\Helper;

class CardHelper extends BaseHelper
{
    use Helper;

    const ERROR_CODE_LICENSE_EXPIRE = -6;//许可证已经过期
    const ERROR_CODE_LICENSE_INVALID_BUNDLE = -10;//当前包名称与许可证中的包名称不匹配
    const ERROR_CODE_INVALID_CAPTCHA = -100;//验证码错误
    const ERROR_CODE_INVALID_MOBILE = -500;//未绑定手机
    const ERROR_CODE_INVALID_OTP = -600;//验证码错误

    const REDIS_KEY_EKYC_OTP_ERROR_COUNT = 'ekyc_otp_error_count:';

    const BIRTHDAY_AUTH_TYPE_PAN_CARD = 'pan_card';
    const BIRTHDAY_AUTH_TYPE_AADHAAR_CARD = 'aadhaar_card';
    /**
     * ekyc 错误处理
     *
     * @param User $user
     * @param $data
     */
    public function ekycError(User $user, $data)
    {
        $errorCode = array_get($data, 'errorCode');
        # aadhaar无绑定手机号，过期，许可证不正确跳过ekyc验证
        if(in_array($errorCode, [self::ERROR_CODE_LICENSE_EXPIRE,self::ERROR_CODE_LICENSE_INVALID_BUNDLE,self::ERROR_CODE_INVALID_MOBILE])){
            SkipServer::aadhaarKycSkip($user);
        }
        # 验证码输错两次，跳过ekyc验证
        if(in_array($errorCode, [self::ERROR_CODE_INVALID_CAPTCHA, self::ERROR_CODE_INVALID_OTP])){
            if(CommonRedis::redis()->verifyCount(self::REDIS_KEY_EKYC_OTP_ERROR_COUNT.$user->id) >= 2){
                SkipServer::aadhaarKycSkip($user);
            }
        }
    }

    /**
     * ekyc 图片上传
     *
     * @param User $user
     * @param $aadhaarImg
     * @throws \Common\Exceptions\ApiException
     */
    public function ekycImageUpload(User $user, $aadhaarImg)
    {
        Upload::model()->clear($user->id, Upload::TYPE_AADHAAR_CARD_KYC);
        if (!$attributes = UploadServer::saveFile($aadhaarImg, 'aadhaar', $user->id . '_' . date("His", time()) . '.jpeg')) {
            $this->outputException(t('aadhaar上传文件保存失败', 'auth'));
        }
        $attributes['type'] = Upload::TYPE_AADHAAR_CARD_KYC;
        if (!$model = UploadServer::create($attributes, $user->id)) {
            $this->outputException(trans('aadhaar上传文件保存记录失败', 'auth'));
        }
    }

    /**
     * @param $user
     * @param $type
     * @param $authName
     */
    public function saveCheckName($user, $type, $authName)
    {
        /** @var User $user */
        $result = [
            'auth_name' => $authName,
            'fullname' => $user->fullname,
        ];
        $userThirdData = new UserThirdData();
        if (!$authName){
            // 不存在
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_FAIL);
            $result['identical'] = false;
        } elseif (!ArrayHelper::stringIntersect($authName, $user->fullname)) {
            // 名字不一致
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_VERIFY_FAIL);
            $result['identical'] = false;
        } else {
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_SUCCESS);
            $result['identical'] = true;
        }
        $userThirdData->create($user->id, $type, $result);
    }

    public function saveCheckPanNames($user, $verfiyName, $ocrName)
    {
        /** @var User $user */
        $result = [
            'verfiy_name' => $verfiyName,
            'ocr_name' => $ocrName,
        ];
        $userThirdData = new UserThirdData();
        if (!$verfiyName || !$ocrName){
            // 不存在
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_FAIL);
            $result['identical'] = false;
        } elseif (!ArrayHelper::stringIntersect($verfiyName, $ocrName)) {
            // 名字不一致
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_VERIFY_FAIL);
            $result['identical'] = false;
        } else {
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_SUCCESS);
            $result['identical'] = true;
        }
        $userThirdData->create($user->id, UserThirdData::TYPE_PANCARD_OCR_VERFIY_NAME_CHECK, $result);
    }

    public function saveCheckAadhaarTelephone($user, $aadhaarTelephone)
    {
        /** @var User $user */
        $result = [
            'user_telephone' => $user->telephone,
            'aadhaar_telephone' => $aadhaarTelephone,
        ];
        $userThirdData = new UserThirdData();
        if (!$aadhaarTelephone){
            // 不存在
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_FAIL);
            $result['identical'] = false;
        } elseif (substr($user->telephone,-3) != substr($aadhaarTelephone,-3)) {
            // 尾号不一致
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_VERIFY_FAIL);
            $result['identical'] = false;
        } else {
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_SUCCESS);
            $result['identical'] = true;
        }
        $userThirdData->create($user->id, UserThirdData::TYPE_AADHAAR_CARD_TELEPHONE_CHECK, $result);
    }

    public function saveCheckAadhaarAge($user, $aadhaarAge)
    {
        /** @var User $user */
        $birthday = $user->userInfo->birthday;
        $result = [
            'birthday' => $birthday,
            'aadhaar_age' => $aadhaarAge,
        ];
        $userThirdData = new UserThirdData();
        if (!$aadhaarAge || !$birthday) {
            // 不存在
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_FAIL);
            $result['identical'] = false;
        } else {
            $userBirthdayAge = DateHelper::getAge($birthday);
            list($minAge, $maxAge) = explode('-', $aadhaarAge);
            if (!is_numeric($minAge) || !is_numeric($maxAge)) {
                $userThirdData->setResStatus(UserThirdData::RES_STATUS_FAIL);
                $result['identical'] = false;
            } elseif ($userBirthdayAge < $minAge || $userBirthdayAge > $maxAge) {
                // 年龄不一致
                $userThirdData->setResStatus(UserThirdData::RES_STATUS_VERIFY_FAIL);
                $result['identical'] = false;
            } else {
                $userThirdData->setResStatus(UserThirdData::RES_STATUS_SUCCESS);
                $result['identical'] = true;
            }
        }
        $userThirdData->create($user->id, UserThirdData::TYPE_AADHAAR_CARD_AGE_CHECK, $result);
    }

    public function saveCheckInputBirthday($user, $inputBirthday, $authBirthday, $auth)
    {
        $userThirdData = new UserThirdData();
        $result = [
            'birthday' => $inputBirthday,
            'authBirthday' => $authBirthday,
            'auth' => $auth,
        ];

        if (!$authBirthday){
            // 不存在
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_FAIL);
            $result['identical'] = false;
        } else {
            if ($authBirthday == $inputBirthday || $authBirthday == substr($inputBirthday,-4)){
                $userThirdData->setResStatus(UserThirdData::RES_STATUS_SUCCESS);
                $result['identical'] = true;
            } else {
                $userThirdData->setResStatus(UserThirdData::RES_STATUS_VERIFY_FAIL);
                $result['identical'] = false;
            }
        }

        $userThirdData->create($user->id, UserThirdData::TYPE_INPUT_BIRTHDAY_CHECK, $result);
    }

    public function saveCheckBankname($user, $bankname)
    {
        $userThirdData = new UserThirdData();
        $result = [
            'fullname' => $user->fullname,
            'bankname' => $bankname,
        ];
        if (!$bankname){
            // 不存在
            $userThirdData->setResStatus(UserThirdData::RES_STATUS_FAIL);
            $result['identical'] = false;
        } else {
            if (ArrayHelper::stringIntersectByBank2($user->fullname, $bankname)){
                $userThirdData->setResStatus(UserThirdData::RES_STATUS_SUCCESS);
                $result['identical'] = true;
            } else {
                $userThirdData->setResStatus(UserThirdData::RES_STATUS_VERIFY_FAIL);
                $result['identical'] = false;
            }
        }
        $userThirdData->create($user->id, UserThirdData::TYPE_BANKNAME_CHECK, $result);
    }

}
