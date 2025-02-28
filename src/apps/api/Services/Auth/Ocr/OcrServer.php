<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14
 * Time: 15:50
 */

namespace Api\Services\Auth\Ocr;

use Api\Models\Upload\Upload;
use Api\Services\Auth\Card\AuthCardServer;
use Api\Services\Auth\Card\CardCheckServer;
use Api\Services\BaseService;
use Common\Helper\Auth\CardHelper;
use Common\Models\Third\ThirdPartyLog;
use Common\Models\User\UserThirdData;
use Common\Utils\Services\AuthRequestHelper;
use Common\Utils\Upload\OssHelper;
use JMD\Libs\Services\DataFormat;
use Common\Utils\Riskcloud\RiskcloudHelper;
use Api\Services\User\UserAuthServer;
use Api\Models\User\UserAuth;
use Api\Services\Third\WhatsappServer;
use Api\Models\User\UserInfo;
use Api\Models\User\User;

class OcrServer extends BaseService {

    public $user;
    public $type;

    const MAX_VERIFY_NUM = 3;
    const OCR_COUNT_PREFIX = 'ocr:';

    /**
     * @param $user
     * @param $type
     * @return OcrServer|void
     * @throws \Common\Exceptions\ApiException
     */
    public function auth($user, $type) {
        $this->user = $user;
        $this->type = $type;
        if (CardCheckServer::server()->verifyCount($this->user->id, self::OCR_COUNT_PREFIX . $this->type) >= static::MAX_VERIFY_NUM) {
            return $this->outputException('Upto the limit times,please try tomorrow');
        }
        # 添加What'sAPP的验证
        if ("2" == $res = WhatsappServer::server()->check($user->telephone)) {
            return $this->outputException("Sorry, you're not eligible to apply the loan.");
        }
        $fun = camel_case($this->type);
        if (!method_exists($this, $fun)) {
            return $this->outputException('type error');
        }
        call_user_func([$this, $fun]);
        return $this->outputSuccess('success', $this->data);
    }

    /**
     * @throws \Common\Exceptions\ApiException
     */
    public function panCard() {
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_PAN_CARD);
        if (!$front) {
            return $this->outputException('Pancard have not upload');
        }
        $url = OssHelper::helper()->picTokenUrl($front->path, 900);
        /* if ("1" == $this->user->app_id) {
          $authRequestHelper = new AuthRequestHelper();
          $requestRes = $authRequestHelper->panCardOcr($url, $this->user);
          $thirdPartLogModel = new ThirdPartyLog();
          $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_PAN_CARD_OCR, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $this->user->id);
          $this->requestDataCheck($requestRes, $thirdPartLogModel);
          $this->data = array_get($requestRes->getData(), 'auth_data');
          UserThirdData::model()->create($this->user->id, UserThirdData::TYPE_PAN_CARD_FRONT, $this->data);
          } else { */
        $riskcloud = new RiskcloudHelper();
        $data = [
            "cardType" => "PAN_FRONT",
            "image" => base64_encode(file_get_contents($url))
        ];
        $res = $riskcloud->cardOcr($data);
        $res = $res->getAll();
        if ("13000" == $res['code']) {
            return $this->outputException($res['msg']);
        }
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_PAN_CARD_OCR, $data, "cardOcr", '', '', $this->user->id);
        $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, json_encode($res), $res['data']['reportId']);
        if ("1" == $this->user->app_id) {
            $this->data['card_no'] = $res['data']['panCode'] ?? "";
            $this->data['father_name'] = $res['data']['fatherName'] ?? "";
            $this->data['name'] = $res['data']['panName'] ?? "";
            $this->data['date_info'] = $res['data']['dateOfBirth'] ?? "";
//                $this->data = $dataTmp;
        } else {
            $this->data = $res['data'];
        }
        UserThirdData::model()->create($this->user->id, UserThirdData::TYPE_PAN_CARD_FRONT, $this->data);

        if ("1" != $this->user->app_id) {
            $userInfoUpdateData = [
                'father_name' => $res['data']['fatherName'] ?? "",
                'pan_card_no' => $res['data']['panCode'] ?? "",
                'birthday' => $res['data']['dateOfBirth'] ?? "",
            ];
            UserInfo::updateOrCreateModel(UserInfo::SCENARIO_UPDATE_CARD, ['user_id' => $this->user->id], $userInfoUpdateData);
            $this->user->setScenario(User::SCENARIO_ID_CARD)->saveModel([
                'id_card_no' => $res['data']['panCode']
            ]);
        }
//        }
    }

    public function aadhaarCardFront() {
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_AADHAAR_CARD_FRONT);
        if (!$front) {
            return $this->outputException('AadhaarCard front have not upload');
        }
        $url = OssHelper::helper()->picTokenUrl($front->path, 900);
        /* 印牛服务替换为闪云 */
        /* if ("1" == $this->user->app_id) {
          $authRequestHelper = new AuthRequestHelper();
          $requestRes = $authRequestHelper->aadhaarCardFrontOcr($url, $this->user);
          $thirdPartLogModel = new ThirdPartyLog();
          $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_AADHAAR_CARD_FRONT_OCR, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $this->user->id);
          $this->requestDataCheck($requestRes, $thirdPartLogModel);
          $this->data = array_get($requestRes->getData(), 'auth_data');
          UserThirdData::model()->create($this->user->id, UserThirdData::TYPE_AADHAAR_CARD_FRONT, $this->data);
          } else { */
        $riskcloud = new RiskcloudHelper("rupeecash");
        $data = [
            "cardType" => "AADHAAR_FRONT",
            "image" => base64_encode(file_get_contents($url))
        ];
        $res = $riskcloud->cardOcr($data);
        $res = $res->getAll();
        if ("13000" == $res['code']) {
            return $this->outputException($res['msg']);
        }
        UserAuthServer::server()->setAuth($this->user->id, UserAuth::TYPE_AADHAAR_CARD);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_AADHAAR_CARD_FRONT_OCR, $data, "cardOcr", '', '', $this->user->id);
        $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, json_encode($res), $res['data']['reportId']);
        if ("1" == $this->user->app_id) {
            $this->data['card_no'] = $res['data']['idNumber'] ?? "";
            $this->data['name'] = $res['data']['name'] ?? "";
            $this->data['date_info'] = "";
            $this->data['gender'] = $res['data']['gender'] ?? "";
        } else {
            $this->data = $res['data'];
        }
        UserThirdData::model()->create($this->user->id, UserThirdData::TYPE_AADHAAR_CARD_FRONT, $this->data);
//        }
    }

    public function aadhaarCardBack() {
        $back = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_AADHAAR_CARD_BACK);
        if (!$back) {
            return $this->outputException('AadhaarCard back have not upload');
        }
        $url = OssHelper::helper()->picTokenUrl($back->path, 900);
        //切换到闪云
//        if ("1" == $this->user->app_id) {
//            $authRequestHelper = new AuthRequestHelper();
//            $requestRes = $authRequestHelper->aadhaarCardBackOcr($url, $this->user);
//            $thirdPartLogModel = new ThirdPartyLog();
//            $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_AADHAAR_CARD_BACK_OCR, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $this->user->id);
//            $this->requestDataCheck($requestRes, $thirdPartLogModel);
//            $this->data = array_get($requestRes->getData(), 'auth_data');
//            UserThirdData::model()->create($this->user->id, UserThirdData::TYPE_AADHAAR_CARD_BACK, $this->data);
//        } else {
            $riskcloud = new RiskcloudHelper("rupeecash");
            $data = [
                "cardType" => "AADHAAR_BACK",
                "image" => base64_encode(file_get_contents($url))
            ];
            $res = $riskcloud->cardOcr($data);
            $res = $res->getAll();
            if ("13000" == $res['code']) {
                return $this->outputException($res['msg']);
            }
            $thirdPartLogModel = new ThirdPartyLog();
            $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_AADHAAR_CARD_BACK_OCR, $data, "cardOcr", '', '', $this->user->id);
            $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, json_encode($res), $res['data']['reportId']);
            if ("1" == $this->user->app_id) {
                $this->data['pincode'] = $res['data']['pin'] ?? "";
                $this->data['address'] = $res['data']['address'] ?? "";
            } else {
                $this->data = $res['data'];
            }
            UserThirdData::model()->create($this->user->id, UserThirdData::TYPE_AADHAAR_CARD_BACK, $this->data);
//        }
    }

    /**
     * @throws \Common\Exceptions\ApiException
     */
    public function addressVoter() {
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_VOTER_ID_CARD_FRONT);
        //$back = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_VOTER_ID_CARD_BACK);
        if (!$front/* && $back */) {
            return $this->outputException('voterId have not upload');
        }
        $url = OssHelper::helper()->picTokenUrl($front->path);
        $authRequestHelper = new AuthRequestHelper();
        $requestRes = $authRequestHelper->voterIdOcr($url, $this->user);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_VOTER_ID_OCR, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $this->user->id);
        $this->requestDataCheck($requestRes, $thirdPartLogModel);
        $this->data = array_get($requestRes->getData(), 'auth_data');
        UserThirdData::model()->create($this->user->id, UserThirdData::TYPE_VOTER_ID_FRONT, $this->data);
        CardHelper::helper()->saveCheckName($this->user, UserThirdData::TYPE_VOTER_NAME_CHECK, array_get($this->data, 'name'));
    }

    /**
     * @throws \Common\Exceptions\ApiException
     */
    public function addressPassport() {
        $front = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_PASSPORT_IDENTITY);
        //$back = Upload::model()->getOneFileByUser($this->user->id, Upload::TYPE_PASSPORT_DEMOGRAPHICS);
        if (!$front/* && $back */) {
            return $this->outputException('Passport have not upload');
        }
        $url = OssHelper::helper()->picTokenUrl($front->path);
        $authRequestHelper = new AuthRequestHelper();
        $requestRes = $authRequestHelper->passportOcr($url, $this->user);
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_PASSPORT_OCR, $authRequestHelper->getParams(), $authRequestHelper->getRoute(), '', '', $this->user->id);
        $this->requestDataCheck($requestRes, $thirdPartLogModel);
        $this->data = array_get($requestRes->getData(), 'auth_data');
        UserThirdData::model()->create($this->user->id, UserThirdData::TYPE_PASSPORT_IDENTITY, $this->data);
        CardHelper::helper()->saveCheckName($this->user, UserThirdData::TYPE_PASSPORT_NAME_CHECK, array_get($this->data, 'name'));
    }

    /**
     * @param $request
     * @param ThirdPartyLog $thirdPartLogModel
     * @param string $authFailMsg
     * @throws \Common\Exceptions\ApiException
     */
    public function requestDataCheck($request, ThirdPartyLog $thirdPartLogModel, $authFailMsg = '') {
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

}
