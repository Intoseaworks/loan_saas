<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-03-07
 * Time: 15:05
 */

namespace Common\Utils\AadhaarApi\Api;


use Api\Services\BaseService;
use Common\Models\Third\ThirdPartyLog;
use Common\Models\User\User;
use Common\Utils\Curl;

class AadhaarRequest extends BaseService
{
    /**
     * pan_card 认证成功
     *
     * @var int
     */
    const PAN_CARD_SUCCESS = 1;

    /**
     * pan_card 认证失败
     *
     * @var int
     */
    const PAN_CARD_AUTH_FAILURE = 3;

    /**
     * 驾驶证验证成功
     *
     * @var int
     */
    const DL_SUCCESS = 101;

    /**
     * 驾驶证验证失败
     *
     * @var int
     */
    const DL_FAILURE = 102;


    const BANK_CARD_CODE_SUCCESS = 'TXN';
    const BANK_CARD_CODE_ERROR = 'ERR';

    const BANK_CARD_TRANSACTION_STATUS_SUCCESS = 1;
    const BANK_CARD_TRANSACTION_STATUS_ERROR = 2;

    const BANK_CARD_STATUS_SUCCESS = 'VERIFIED';
    const BANK_CARD_STATUS_ERROR = 'NOT VERIFIED';

    /**
     * @param $panCard
     * @param $userId
     * @return AadhaarRequest|mixed
     */
    public function panCard($panCard, $userId)
    {
        $params = ['pan' => $panCard];
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_PAN_CARD_VERFIY, $params, Config::getPanCardUrl(), '', '', $userId);
        return $this->request($params, Config::getPanCardUrl(), $thirdPartLogModel);
    }

    /**
     * @param $params
     * @param $url
     * @param ThirdPartyLog $thirdPartLogModel
     * @param null $respoHandle
     * @return AadhaarRequest|mixed
     */
    protected function request($params, $url, ThirdPartyLog $thirdPartLogModel, $respoHandle = null)
    {
        $appKey = Config::getApiKey();
        $anencyId = Config::getAgencyId();
        $header = [
            "qt_api_key: {$appKey}",
            "qt_agency_id: {$anencyId}",
        ];
        $result = json_decode(Curl::post($params, $url, $header), true);
        $response = $result;

        if (!$result) {
            $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_FAIL);
            return $this->outputError('Error, please try again');
        }

        // 处理数据,响应数据如果存在base64的数据把他过滤掉
        if ($respoHandle && ($respoHandle instanceof \Closure)) {
            $response = $respoHandle($result);
        }
        // 响应结果保存到日志
        $thirdPartLogModel->updatedByResponse(
            isset($result['statusCode']) ? ThirdPartyLog::REQUEST_STATUS_FAIL : ThirdPartyLog::REQUEST_STATUS_SUCCESS,
            $response,
            array_get($result, 'id', '')
        );
        if ($result) {
            $result['third_part_log_id'] = $thirdPartLogModel->id;
            return $this->outputSuccess('Success', $result);
        }

        return $this->outputError('Error, please try again');
    }

    /**
     * @param $dlNo
     * @param $birthday
     * @param $userId
     * @return AadhaarRequest|mixed
     */
    public function drivingLicence($dlNo, $birthday, $userId)
    {
        $params = [
            'dl_no' => $dlNo,
            'dob' => $birthday,
            'consent' => 'Y',
            'consent_text' => 'I herby declare my consent to provide by information to cashnowapp for purpose of verification',
        ];
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_DL_VERFIY, $params, Config::getDrivingLicenceUrl(), '', '', $userId);
        $responseHandle = function ($response) {
            if (array_get($response, 'response_code') == static::DL_SUCCESS) {
                $response['result']['img'] = '***';
            }
            return $response;
        };
        return $this->request($params, Config::getDrivingLicenceUrl(), $thirdPartLogModel, $responseHandle);
    }

    /**
     * @param $Account
     * @param $ifsc
     * @param $userId
     * @return AadhaarRequest|mixed
     */
    public function bankCard($account, $ifsc, $userId)
    {
        $params = [
            'Account' => $account,
            'IFSC' => $ifsc,
        ];
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_BANK_CARD_CHACK, $params, Config::getBankCardUrl(), '', '', $userId);
        return $this->request($params, Config::getBankCardUrl(), $thirdPartLogModel);
    }

    /**
     * @param $voterId
     * @param $userId
     * @return AadhaarRequest|mixed
     */
    public function voterId($voterId, $userId)
    {
        $params = [
            'epic_no' => $voterId,
            'consent' => 'Y',
            'consent_text' => 'I herby declare my consent to provide by information to cashnowapp for purpose of verification',
        ];
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_VOTER_ID_VERFIY, $params, Config::getVoterIdUrl(), '', '', $userId);
        return $this->request($params, Config::getVoterIdUrl(), $thirdPartLogModel);
    }

    /**
     * @param $voterId
     * @param $userId
     * @return AadhaarRequest|mixed
     */
    public function passport($passportNo, User $user)
    {
        $params = [
            'name' => $user->fullname,
            'last_name' => $user->fullname,
            'dob' => $user->userInfo->birthday,
            'doe' => $user->userInfo->birthday,
            'gender' => 'T',
            'passport_no' => $passportNo,
            'type' => 'P',
            'country' => 'IND',
            'consent' => 'Y',
            'consent_text' => 'I herby declare my consent to provide by information to cashnowapp for purpose of verification',
        ];
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_PASSPORT_VERFIY, $params, Config::getPassportUrl(), '', '', $user->user_id);
        return $this->request($params, Config::getPassportUrl(), $thirdPartLogModel);
    }
}
