<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Test;

use Admin\Models\User\User;
use Admin\Models\User\UserAuth;
use Admin\Services\BaseService;
use Admin\Services\User\UserAuthServer;
use Api\Services\Auth\Card\CardCheckServer;
use Api\Services\Auth\Ocr\OcrServer;
use Common\Helper\Auth\CardHelper;
use Common\Models\Third\ThirdPartyLog;
use Common\Redis\CommonRedis;
use Common\Utils\Services\AuthRequestHelper;

class TestAuthServer extends BaseService
{
    const TYPE_PAN_REJECTED = 'pan_rejected';
    const TYPE_PAN_OCR = 'pan_ocr';
    const TYPE_VOTER_OCR = 'voter_ocr';
    const TYPE_PASSPORT_OCR = 'passport_ocr';
    const TYPE = [
        self::TYPE_PAN_REJECTED => 'panCard拒绝',
        UserAuth::TYPE_BASE_INFO => '基本信息',
        UserAuth::TYPE_USER_EXTRA_INFO => '扩展信息',
        UserAuth::TYPE_PAN_CARD => 'panCard',
        UserAuth::TYPE_FACES => '人脸',
        UserAuth::TYPE_CONTACTS => '紧急联系人',
        UserAuth::TYPE_ADDRESS_AADHAAR => 'aadhaar',
        UserAuth::TYPE_ADDRESS_PASSPORT => 'passport',
        UserAuth::TYPE_ADDRESS_VOTER => 'voter',
        UserAuth::TYPE_FACEBOOK => 'facebook',
        UserAuth::TYPE_BANKCARD => '银行卡',
    ];
    public $userId;

    /**
     * @param $datas
     * @return TestAuthServer|void
     * @throws \Common\Exceptions\ApiException
     */
    public function clearOrComplete($datas)
    {
        $telephone = array_get($datas, 'telephone');
        if (!$telephone) {
            return $this->outputException('请输入手机号');
        }

        if (!($user = User::model()->where('telephone', $telephone)->orWhere('id', $telephone)->first())) {
            return $this->outputException('用户不存在');
        }

        $this->userId = $user->id;
        $type = array_get($datas, 'type');
        switch ($type) {
            case self::TYPE_PAN_REJECTED:
                CardCheckServer::server()->clearCount($this->userId, CardCheckServer::PAN_CARD_KEY);
                break;
            case self::TYPE_PAN_OCR:
                CardCheckServer::server()->clearCount($this->userId, OcrServer::OCR_COUNT_PREFIX.UserAuth::TYPE_PAN_CARD);
                break;
            case self::TYPE_VOTER_OCR:
                CardCheckServer::server()->clearCount($this->userId, OcrServer::OCR_COUNT_PREFIX.UserAuth::TYPE_ADDRESS_VOTER);
                break;
            case self::TYPE_PASSPORT_OCR:
                CardCheckServer::server()->clearCount($this->userId, OcrServer::OCR_COUNT_PREFIX.UserAuth::TYPE_ADDRESS_PASSPORT);
                break;
            case ThirdPartyLog::NAME_AADHAAR_CARD_FRONT_OCR:
                CardCheckServer::server()->clearCount($this->userId, OcrServer::OCR_COUNT_PREFIX. AuthRequestHelper::AUTH_AADHAAR_CARD_FRONT);
                break;
            case ThirdPartyLog::NAME_AADHAAR_CARD_BACK_OCR:
                CardCheckServer::server()->clearCount($this->userId, OcrServer::OCR_COUNT_PREFIX. AuthRequestHelper::AUTH_AADHAAR_CARD_BACK);
                break;
            case 'aadhaar_kyc_otp_error_clear':
                CommonRedis::redis()->del(CardHelper::REDIS_KEY_EKYC_OTP_ERROR_COUNT.$this->userId);
                break;
            case UserAuth::TYPE_BASE_INFO:
            case UserAuth::TYPE_USER_EXTRA_INFO:
            case UserAuth::TYPE_PAN_CARD:
            case UserAuth::TYPE_FACES:
            case UserAuth::TYPE_CONTACTS:
            case UserAuth::TYPE_ADDRESS_AADHAAR:
            case UserAuth::TYPE_ADDRESS_PASSPORT:
            case UserAuth::TYPE_ADDRESS_VOTER:
            case UserAuth::TYPE_FACEBOOK:
            case UserAuth::TYPE_AADHAAR_CARD:
            case UserAuth::TYPE_AADHAAR_CARD_KYC:
                $this->clearOrSetAuth($type);
                break;
            default :
                return $this->outputException('类型不存在');
                break;
        }
        return $this->outputSuccess();
    }

    /**
     * @param $authType
     */
    public function clearOrSetAuth($authType)
    {
        if (UserAuthServer::server()->checkIsAuth($this->userId, $authType)) {
            UserAuthServer::server()->clearAuth($this->userId, $authType);
        } else {
            UserAuthServer::server()->setAuth($this->userId, $authType);
        }
    }

}
