<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-03-07
 * Time: 15:40
 */

namespace Api\Services\Auth\Card;

use Api\Models\User\User;
use Api\Services\BaseService;
use Api\Services\User\UserAuthBlackServer;
use Api\Services\User\UserBlackServer;
use Common\Helper\Auth\CardHelper;
use Common\Models\Third\ThirdPartyLog;
use Common\Models\User\UserThirdData;
use Common\Redis\CommonRedis;
use Common\Utils\Data\StringHelper;
use Common\Utils\Services\AuthRequestHelper;
use Exception;
use JMD\Libs\Services\DataFormat;

class CardCheckServer extends BaseService
{
    /**
     * 1个自然日最大验证次数
     *
     * @var int
     */
    const MAX_VERIFY_NUM = 3;

    /**
     * 1个自然日最大验证失败次数
     *
     * @var int
     */
    const MAX_VERIFY_FAILURE_NUM = 3;

    /**
     * 1个自然日最大aadhaarCard和panCard交叉验证次数
     *
     * @var int
     */
    const MAX_AADHAAR_CARD_VERIFY_PAN_CARD_NUM = 3;

    /**
     * pan card 验证统计
     */
    const PAN_CARD_KEY = 'pan_card_verify_count:';
    /**
     * pan card 验证失败次数
     */
    const PAN_CARD_VERIFY_FAILURE_KEY = 'pan_card_verify_failure_count:';
    /**
     * aadhaar card 验证失败次数
     */
    const AADHAAR_CARD_VERIFY_FAILURE_KEY = 'aadhaar_verify_failure_count:';

    /** 用户被拉入黑名单 */
    const OUTPUT_USER_BLACK = 13001;

    /**
     * @param $panCard
     * @param $userId
     * @return array|bool
     * @throws Exception
     */
    public function validatePanCard($panCard, \Common\Models\User\User $user)
    {
        /** @var User $user */
        $userId = $user->id;
        if ($count = $this->verifyCount($panCard, static::PAN_CARD_KEY) >= 1) {
            return $this->outputException('Upto the limit times,please try tomorrow');
        }
        
        if ($count = $this->verifyCount($userId, static::PAN_CARD_KEY) >= static::MAX_VERIFY_NUM) {
            return $this->outputException('Upto the limit times,please try tomorrow');
        }
        $authRequestHelper = new AuthRequestHelper();
        $requestRes = $authRequestHelper->panCard($panCard, $user);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_PAN_CARD_VERFIY, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $userId);

        # pancard三次不过，用户拉黑
        if ($requestRes && $requestRes->getMsg() != AuthRequestHelper::AUTH_SUCCESS) {
            if ($this->verifyCount($userId, static::MAX_VERIFY_FAILURE_NUM) >= static::MAX_VERIFY_NUM) {
                $blackRemark = 'pancard verify';
                UserBlackServer::server()->addCannotAuth($user->telephone, $blackRemark);
                UserAuthBlackServer::server()->addAadharrBlack($user->userInfo->aadhaar_card_no, $user->id, $blackRemark);
                return $this->outputException("Invalid PanCard No.,please check again.", self::OUTPUT_USER_BLACK);
            }
        }

        # 对返回结果进行验证
        $authFailMsg = "Invalid PanCard No.,please check again.";
        $this->requestDataCheck($requestRes, $thirdPartLogModel, $authFailMsg);
        // 认证成功，存入历史认证表
        $data = $requestRes->getData();
        # 名字交叉校验
        $apiUserName = array_get($data, 'auth_data.first_name') . ' ' . array_get($data, 'auth_data.middle_name') . ' ' . array_get($data, 'auth_data.last_name');
        $apiUserName = StringHelper::spacesToSpace($apiUserName);

        $ocrName = (new UserThirdData)->getPanName($user->id);
        CardHelper::helper()->saveCheckName($user, UserThirdData::TYPE_PANCARD_NAME_CHECK, $apiUserName);
        CardHelper::helper()->saveCheckPanNames($user, $apiUserName, $ocrName);

        /*if (!ArrayHelper::stringIntersect($apiUserName, $user->fullname)) {
            // 新第三方请求需更新ThirdPartyLog
            isset($thirdPartLogModel) && $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_VERIFY_FAIL);
            if ($this->verifyCount($userId, static::PAN_CARD_VERIFY_FAILURE_KEY) >= static::MAX_AADHAAR_CARD_VERIFY_PAN_CARD_NUM) {
                $blackRemark = 'aadhaar verify pancard';
                UserBlackServer::server()->addCannotAuth($user->telephone, $blackRemark);
                UserAuthBlackServer::server()->addAadharrBlack($user->userInfo->aadhaar_card_no, $user->id, $blackRemark);
                return $this->outputException("The PAN No. doesn't belong you. Please re-type PAN No. or upload again", self::OUTPUT_USER_BLACK);
            }
            return $this->outputException("The PAN No. doesn't belong you. Please re-type PAN No. or upload again");
        }*/
        # 存储认证成功数据
        UserThirdData::model()->create($user->id, UserThirdData::TYPE_PAN_CARD, $data, UserThirdData::CHANNEL_SERVICES);
        $user->setScenario(User::SCENARIO_ID_CARD)->saveModel([
            'fullname' => $apiUserName
        ]);
        return $this->outputSuccess();
    }

    public function validateAadhaarCard($no, \Common\Models\User\User $user)
    {
        /** @var User $user */
        $userId = $user->id;
        if ($count = $this->verifyCount($no, static::AADHAAR_CARD_VERIFY_FAILURE_KEY) >= 1) {
            return $this->outputException('Upto the limit times,please try tomorrow');
        }
        # 失败上限判断
        if (CommonRedis::redis()->get(self::AADHAAR_CARD_VERIFY_FAILURE_KEY .$user->id) >= static::MAX_VERIFY_NUM) {
            return $this->outputException('Upto the limit times,please try tomorrow');
        }

        $authRequestHelper = new AuthRequestHelper();
        $requestRes = $authRequestHelper->aadhaarCard($no, $user);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_AADHAAR_CARD_VERFIY, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $userId);

        # 失败计数
        if(!$requestRes || !$requestRes->isSuccess() || $requestRes->getMsg() != AuthRequestHelper::AUTH_SUCCESS){
            CommonRedis::redis()->verifyCount(self::AADHAAR_CARD_VERIFY_FAILURE_KEY.$user->id);
        }
        # 对返回结果进行验证
        $authFailMsg = "Invalid Aadhaar Card No.,please check again.";
        $this->requestDataCheck($requestRes, $thirdPartLogModel, $authFailMsg);
        // 认证成功，存入历史认证表
        $data = $requestRes->getData();
        $aadhaarMobileNumber = array_get($data, 'auth_data.mobile_number');
        $aadhaarAge = array_get($data, 'auth_data.age_band');
        # 存储认证成功数据
        UserThirdData::model()->create($user->id, UserThirdData::TYPE_AADHAAR_CARD, $data, UserThirdData::CHANNEL_SERVICES);
        CardHelper::helper()->saveCheckAadhaarTelephone($user, $aadhaarMobileNumber);
        CardHelper::helper()->saveCheckAadhaarAge($user, $aadhaarAge);
        return $this->outputSuccess();
    }

    /**
     * 选民证
     *
     * @param $voterNo
     * @param $userId
     * @return CardCheckServer
     */
    public function validateVoterID($no, \Common\Models\User\User $user)
    {
        $userId = $user->id;
        $this->checkMaxAuth($userId);
        $no = trim($no);
        $authRequestHelper = new AuthRequestHelper();
        $requestRes = $authRequestHelper->voterId($no, $user);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_VOTER_ID_VERFIY, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $userId);
        if (!$requestRes) {
            return $this->outputError('Error, please try again');
        }
        # 对返回结果进行验证
        $authFailMsg = 'Invalid Voter ID No., please check again.';
        $this->requestDataCheck($requestRes, $thirdPartLogModel, $authFailMsg);
        $data = $requestRes->getData();
        // 用户名和Aadhaar/pan OCR名字一致(只需任意一个单词一致即可)
        CardHelper::helper()->saveCheckName($user, UserThirdData::TYPE_VOTER_NAME_CHECK, array_get($data, 'auth_data.name'));
        /*if (!ArrayHelper::stringIntersect(array_get($data, 'auth_data.name'), $user->fullname)) {
            // 新第三方请求需更新ThirdPartyLog
            isset($AuthRequestHelper) && ThirdPartyLog::updateRequestStatus($data['third_part_log_id'], ThirdPartyLog::REQUEST_STATUS_VERIFY_FAIL);
            // 名字不一致
            return $this->outputError("The voter Id doesn't belong to you.Please upload the correct one");
        }*/
        $response = $data;
        UserThirdData::model()->create($user->id, UserThirdData::TYPE_VOTER_ID, $response, UserThirdData::CHANNEL_SERVICES);
        return $this->outputSuccess();
    }

    /**
     * 护照
     *
     * @param $no
     * @param $userId
     * @return CardCheckServer
     */
    public function validatePassport($no, \Common\Models\User\User $user)
    {
        $userId = $user->id;
        $this->checkMaxAuth($userId);
        $no = trim($no);
        $authRequestHelper = new AuthRequestHelper();
        $requestRes = $authRequestHelper->passport($no, $user);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_PASSPORT_VERFIY, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $userId);
        # 对返回结果进行验证
        $authFailMsg = $requestRes ? $requestRes->getMsg() : '';
        $this->requestDataCheck($requestRes, $thirdPartLogModel, $authFailMsg);
        $data = $requestRes->getData();
        $response = $data;
        UserThirdData::model()->create($user->id, UserThirdData::TYPE_PASSPORT, $response, UserThirdData::CHANNEL_SERVICES);
        return $this->outputSuccess();
    }

    /**
     * @param $userId
     * @param $key
     * @param bool $incr
     * @return int
     */
    public function verifyCount($userId, $key, $incr = true)
    {

        $redisKey = "{$key}{$userId}";

        $count = CommonRedis::redis()->get($redisKey);

        if ($incr) {
            CommonRedis::redis()->incr($redisKey);
        }

        if (is_null($count)) {
            CommonRedis::redis()->expire($redisKey, strtotime(date('Y-m-d', strtotime('+1 day'))) - time());
        }

        return $count ?: 0;
    }

    protected function checkMaxAuth($userId)
    {
        $cacheAuthCountKeyName = 'auth:address_no:count:' . date('Ymd') . ':' . $userId;
        $cacheAuthCount = CommonRedis::redis()->get($cacheAuthCountKeyName);
        if ($cacheAuthCount >= self::MAX_VERIFY_NUM) {
            return $this->outputException('Upto the limit times, please try tomorrow');
        }
        CommonRedis::redis()->set($cacheAuthCountKeyName, $cacheAuthCount + 1, 60 * 60 * 24);
    }

    public function requestDataCheck($request, ThirdPartyLog $thirdPartLogModel, $authFailMsg = '')
    {
        if (!$request) {
            $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_FAIL);
            return $this->outputException('Error, please try again');
        }
        /** @var DataFormat $request */
        $response = $request->getAll();
        $data = $request->getData();
        $msg = $request->getMsg();
        $reportId = array_get($data, 'report_id', '');
        // 响应结果保存到日志
        $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, $response, $reportId);
        //认证不成功
        if ($msg != AuthRequestHelper::AUTH_SUCCESS) {
            $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_VERIFY_FAIL);
            return $this->outputException($authFailMsg ?: 'The status is not active,please check again.');
        }
    }

    /**
     * @param $userId
     * @param $key
     * @return mixed
     */
    public function clearCount($userId, $key)
    {
        $redisKey = "{$key}{$userId}";
        return CommonRedis::redis()->set($redisKey, 0);
    }

    /**
     * 银行卡认证
     *
     * @param $cardNo
     * @param $ifsc
     * @param \Common\Models\User\User $user
     * @return CardCheckServer
     */
    public function validateBankCard($cardNo, $ifsc, \Common\Models\User\User $user)
    {
        $userId = $user->id;
        $cardNoText = "****" . substr($cardNo, -4, 4);
        # 银行卡验证
        $AuthRequestHelper = new AuthRequestHelper();
        $requestRes = $AuthRequestHelper->bankCard($cardNo, $ifsc, $user);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_BANK_CARD_CHACK, $AuthRequestHelper->getParams(), $AuthRequestHelper->getRoute(), '', '', $userId);
        if (!$requestRes) {
            $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_FAIL);
            return $this->outputError('Error, please try again. There are THREE times to add a bank card per day.Please try again.');
        }
        # 对返回结果进行验证
        $this->requestDataCheck($requestRes, $thirdPartLogModel);
        $resData = $requestRes->getData();
        $dataBeneName = array_get($resData, 'auth_data.BeneName');
        $nameSimilarity = array_get($resData, 'auth_data.NameSimilarity');
        if($nameSimilarity && $nameSimilarity < 60){
            return $this->outputError('The bank account name does not match the entered name.');
        }
        # 当返回名字为空时
        if(!$dataBeneName){
            //return $this->outputError("{$cardNoText} don't has return name, please change other card");
            return $this->outputError("Validation failed. Your bank card was not found, please change another one. There are THREE times to add a bank card per day.Please try again.");
        }
        # 银行卡名比对结果存入用户数据表
        CardHelper::helper()->saveCheckBankname($user, $dataBeneName);
        # 银行卡用户名检验
        /*if (!ArrayHelper::stringIntersectByBank2($user->fullname, $dataBeneName)) {
            isset($thirdPartLogModel) && $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_VERIFY_FAIL);
            return $this->outputError("{$cardNoText} doesn't belong to {$user->fullname}，please confirm the Holder Name and the Account no. There are THREE times to add a bank card per day.Please try again.");
        }*/
        return $this->outputSuccess();
    }

    /**
     * @return Connection $redis
     */
    protected function getRedis()
    {
        return CommonRedis::redis();
    }

}
