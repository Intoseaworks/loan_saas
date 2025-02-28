<?php

namespace Risk\Common\Services\SystemApprove;

use Admin\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Services\BaseService;
use Common\Utils\Data\StringHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Riskcloud\RiskcloudHelper;
use Common\Utils\Upload\ImageHelper;
use Risk\Common\Models\CreditReport\ExperianBaseInfo;
use Risk\Common\Models\SystemApprove\SystemApproveRule;
use Risk\Common\Models\Third\ThirdDataRiskcloud;

class SystemApproveThirdServer extends BaseService
{

    const VERIFY_PAN_CARD = "panVerify";
    const VERIFY_AADHAAR_CARD = "aadhaarVerify";
    const VERIFY_FACE_MATCH = "faceMatch";
    const VERIFY_BANK_CARD_VERIFY = "bankCardVerify";
    const VERIFY_LIST = [
        self::VERIFY_FACE_MATCH,
        self::VERIFY_AADHAAR_CARD,
        self::VERIFY_PAN_CARD,
        self::VERIFY_BANK_CARD_VERIFY
    ];
    //人脸匹配阈值不得低于70
    const FACE_MIN_SCORE = 70;

    private $_requestData;

    public function isValidate($appID, $funName) {
        $config = config('thirdparty-invoking.app_list');
        if (isset($config[$funName]) && in_array($appID, $config[$funName])) {
            return true;
        }
        return false;
    }

    public function isPass($user) {
        //验证用户注册app是否需要此验证
        MerchantHelper::setMerchantId($user->app_id);
        $user = User::model()->getOne($user->id);
        foreach (self::VERIFY_LIST as $item) {

            if (!$this->isValidate($user->app_id, $item)) {
                continue;
            }
            if ($item == self::VERIFY_AADHAAR_CARD) {

                /* if ($user->getAadhaarCardKYCStatus() == 1) {//ekyc认证的跳过a卡认证
                  continue;
                  } */
            }

            $validated = ThirdDataRiskcloud::model()->findByUserType($user->id, $item);

            if ($validated) {
                continue;
            }
            $res = $this->{$item}($user);
            $resData = $res->getData();

            if (!isset($resData['errorCode'])) {
                continue;
            }
            $log = [
                "user_id" => $user->id,
                "type" => $item,
                "request" => json_encode($this->_requestData),
                "response" => json_encode($res->getData()),
                "status" => $res->getCode() == '18000' ? 1 : 2
            ];
            ThirdDataRiskcloud::model()->createModel($log);
            if ($res->getCode() != '18000') {
                $rejectRecord = [
                    SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_VERIFY => [
                        'rule' => SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_VERIFY,
                        'value' => [],
                        'hit_value' => json_encode($resData)
                    ]
                ];
                return $rejectRecord;
            } else {
                switch ($item) {
                    case self::VERIFY_FACE_MATCH://人脸比对需要范围分数
                        if ($resData['score'] < self::FACE_MIN_SCORE) {
                            $rejectRecord = [
                                SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_VERIFY => [
                                    'rule' => SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_VERIFY,
                                    'value' => [],
                                    'hit_value' => json_encode($resData)
                                ]
                            ];
                            return $rejectRecord;
                        }
                        break;
                }
            }
        }
        return true;
    }

    public function faceMatch(User $user) {
//        print_r($user);exit;
        $upload = (new Upload())->getPathsByUserIdAndType($user->id, [
            Upload::TYPE_AADHAAR_CARD_FRONT,
            Upload::TYPE_FACES,
            Upload::TYPE_AADHAAR_CARD_KYC
        ]);
//        print_r($upload);exit;
        foreach ($upload as $k => $v) {
            $fileUrls[$k] = ImageHelper::getPicUrl($v, 400);
        }
//        print_r($fileUrls);exit;
        $aadhaarCardFile = array_get($fileUrls, Upload::TYPE_AADHAAR_CARD_FRONT) ?? array_get($fileUrls, Upload::TYPE_AADHAAR_CARD_KYC);
//        echo $aadhaarCardFile;exit;
        $faceFile = array_get($fileUrls, Upload::TYPE_FACES);
        $this->_requestData = [
            "image1" => base64_encode(file_get_contents($aadhaarCardFile)),
            "image1Type" => "BASE64",
            "image2" => base64_encode(file_get_contents($faceFile)),
            "image2Type" => "BASE64",
        ];

        $res = RiskcloudHelper::helper()->faceMatch($this->_requestData);

        unset($this->_requestData['image1']);
        unset($this->_requestData['image2']);
//        print_r($res);exit;
        return $res;
    }

    public function panVerify(User $user) {
        $this->_requestData = [
            "uid" => $user->id,
            "panCode" => $user->userInfo->pan_card_no,
            "fullName" => $user->fullname,
            "dateOfBirth" => $user->userInfo->birthday, //dd/mm/yyyy
        ];
        $res = RiskcloudHelper::helper()->panVerify($this->_requestData);
        return $res;
    }

    public function aadhaarVerify(User $user) {
        $this->_requestData = [
            "uid" => $user->id,
            "aadhaarNo" => $user->userInfo->aadhaar_card_no
        ];

        $res = RiskcloudHelper::helper()->aadhaarVerify($this->_requestData);
        return $res;
    }

    public function bankCardVerify(User $user) {
        $this->_requestData = [
            "uid" => $user->id,
            "name" => $user->fullname,
            "bankAccount" => $user->bankCard->account_no,
            "ifsc" => $user->bankCard->ifsc
        ];
        $res = RiskcloudHelper::helper()->bankCardVerify($this->_requestData);
        return $res;
    }

    public function experianOfRiskcloud(User $user) {
        list($firstName, $middleName, $lastName) = StringHelper::nameExplode($user->fullname);
        $this->_requestData = [
            "uid" => $user->id,
            "dataFormat" => "json",
            "pan" => $user->userInfo->pan_card_no,
            "firstName" => $firstName,
            "lastName" => $lastName,
            "mobile" => $user->telephone,
            "gender" => $user->userInfo->gender == 'Male' ? 1 : 2,
            "dateOfBirth" => $user->userInfo->birthday,
        ];
        $res = RiskcloudHelper::helper()->searchExperainCredit($this->_requestData);
        $reportData = array_get($res->getData(), "reportData");
        $data = [
            "pan_card_no" => $user->userInfo->pan_card_no,
            "user_id" => $user->id,
            "score_num" => $reportData['INProfileResponse']['SCORE']['BureauScore'] ?? "",
            "report_number" => $reportData['INProfileResponse']['CreditProfileHeader']['ReportNumber'] ?? "",
            "report_date" => $reportData['INProfileResponse']['CreditProfileHeader']['ReportDate'] ?? "",
            "report_time" => $reportData['INProfileResponse']['CreditProfileHeader']['ReportTime'] ?? "",
            "json_data" => json_encode($res->getAll()),
        ];
        ExperianBaseInfo::model()->createModel($data);
    }

}
