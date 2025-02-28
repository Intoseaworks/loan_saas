<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 10:45
 */

namespace Common\Utils\Services;


use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Helper;
use Common\Utils\MerchantHelper;
use JMD\JMD;
use JMD\Libs\Services\SaasService;
use Common\Utils\Riskcloud\RiskcloudHelper;
use JMD\Libs\Services\DataFormat;
use Common\Utils\Data\StringHelper;

class AuthRequestHelper
{
    use Helper;

    const ROUTE_AUTH_CARD = '/auth/third/card';
    const ROUTE_AUTH_FACE = '/auth/third/face';
    const ROUTE_AUTH_OCR = '/auth/third/ocr';
    const ROUTE_AUTH_SDK = '/auth/third/sdk';
    const ROUTE_AUTH_FACE_COMPARISON = '/auth/third/face-comparison';
    const AUTH_PAN_CARD = 'pan_card';
    const AUTH_AADHAAR_CARD = 'aadhaar_card';
    const AUTH_VOTER_ID = 'voter_id';
    const AUTH_PASSPORT = 'passport';
    const AUTH_BANK_CARD = 'bank_card';
    const AUTH_FACE = 'face';
    const AUTH_SIGN = 'sign';
    const AUTH_AADHAAR_KYC = 'aadhaar_kyc';
    const AUTH_AADHAAR_CARD_FRONT = 'aadhaar_card_front';
    const AUTH_AADHAAR_CARD_BACK = 'aadhaar_card_back';
    const AUTH_SUCCESS = 'success';
    const AUTH_FAIL = 'fail';
    public $params = [];
    public $route;

    /**
     * @param $route
     * @param $params
     * @return bool|\JMD\Libs\Services\DataFormat
     */
    public function auth()
    {
        try {
            JMD::init(['projectType' => 'lumen']);
            $this->setParams(['merchant_id' => MerchantHelper::getMerchantId()]);
            $res = SaasService::request($this->getRoute(), $this->getParams());
            return $res;
        } catch (\Exception $e) {
            EmailHelper::send([
                'route' => $this->getRoute(),
                'postData' => $this->getParams(),
                'e' => $e->getMessage(),
            ], '【异常】Saas认证印牛服务');
            return false;
        }
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($data)
    {
        $this->params = array_merge($data, $this->getParams());
    }

    /**
     * @param $no
     * @param User $user
     * @return bool|\JMD\Libs\Services\DataFormat
     */
    public function panCard($no, User $user)
    {
        $requestData = [
            "uid" => $user->id,
            "panCode" => $no,
            "fullName" => strtoupper($user->fullname),
            "dateOfBirth" => strpos($user->userInfo->birthday, "-")!==false ? date("d/m/Y", strtotime($user->userInfo->birthday)) : $user->userInfo->birthday, //dd/mm/yyyy
        ];
        $res = RiskcloudHelper::helper()->panVerify($requestData);

        $resData = $res->getData();
        if ($res->getCode() == DataFormat::OUTPUT_SUCCESS || (isset($resData['nameSimilarity']) && $resData['nameSimilarity']>=60 && $resData['errorCode']=="4005")) {
            list($firstName, $middleName, $lastName) = StringHelper::nameExplode($user->fullname);
            $data = [
                "code" => DataFormat::OUTPUT_SUCCESS,
                "msg" => 'success',
                "data" => [
                    "report_id" => $resData['reportId'],
                    "auth_data" => [
                        "pan_number" => $resData["panCode"],
                        "pan_status" => "VALID",
                        "nameSimilarity" => $resData['nameSimilarity'],
                        "pan_holder_title" => "",
                        "pan_last_updated" => "",
                        "first_name" => $firstName,
                        "middle_name" => $middleName,
                        "last_name" => $lastName,
                        "date_of_birth" => $resData['dateOfBirth']
                    ]
                ]
            ];
            return new DataFormat(json_encode($data));
        } else {
            return $res;
        }

        /*$this->setRoute(self::ROUTE_AUTH_CARD);
        $this->setParams([
            'no' => $no,
            'user_id' => $user->id,
            'type' => self::AUTH_PAN_CARD
        ]);
        return $this->auth();*/
    }

    public function aadhaarCard($no, User $user)
    {
        $requestData = [
            "uid" => $user->id,
            "aadhaarNo" => $no
        ];
        $res = RiskcloudHelper::helper()->aadhaarVerify($requestData);

        $resData = $res->getData();
        if ($res->getCode() == DataFormat::OUTPUT_SUCCESS) {
            list($firstName, $middleName, $lastName) = StringHelper::nameExplode($user->fullname);
            $data = [
                "code" => DataFormat::OUTPUT_SUCCESS,
                "msg" => 'success',
                "data" => [
                    "report_id" => $resData['reportId'],
                    "auth_data" => [
                        "age_band" => $resData["ageRange"],
                        "gender" => $resData["gender"],
                        "mobile_number" => $resData["phoneNumber"],
                        "state" => $resData["state"]
                    ]
                ]
            ];
            return new DataFormat(json_encode($data));
        } else {
            return $res;
        }
        
        /*$this->setRoute(self::ROUTE_AUTH_CARD);
        $this->setParams([
            'no' => $no,
            'user_id' => $user->id,
            'type' => self::AUTH_AADHAAR_CARD
        ]);
        return $this->auth();*/
    }

    /**
     * @param $no
     * @param User $user
     * @return bool|\JMD\Libs\Services\DataFormat
     */
    public function voterId($no, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_CARD);
        $this->setParams([
            'no' => $no,
            'user_id' => $user->id,
            'type' => self::AUTH_VOTER_ID
        ]);
        return $this->auth();
    }

    /**
     * @param $no
     * @param User $user
     * @return bool|\JMD\Libs\Services\DataFormat
     */
    public function passport($no, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_CARD);
        $this->setParams([
            'no' => $no,
            'user_id' => $user->id,
            'fullname' => $user->fullname,
            'birthday' => $user->userInfo->birthday,
            'type' => self::AUTH_PASSPORT
        ]);
        return $this->auth();
    }

    public function bankCard($no, $ifsc, User $user)
    {
        $requestData = [
            "uid" => $user->id,
            "name" => $user->fullname,
            "bankAccount" => $no,
            "ifsc" => $ifsc
        ];
        $res = RiskcloudHelper::helper()->bankCardVerify($requestData);

        $resData = $res->getData();
        if ($res->getCode() == DataFormat::OUTPUT_SUCCESS) {
            list($firstName, $middleName, $lastName) = StringHelper::nameExplode($user->fullname);
            $data = [
                "code" => DataFormat::OUTPUT_SUCCESS,
                "msg" => 'success',
                "data" => [
                    "report_id" => $resData['reportId'],
                    "auth_data" => [
                        "Remark" => "Transaction Successful",
                        "BeneName" => $resData["nameAtBank"],
                        "BankRef" => "",
                        "NameSimilarity" => $resData['nameSimilarity'] ?? "",
                        "Status" => "VERIFIED",
                        "amount" => 1
                    ]
                ]
            ];
            return new DataFormat(json_encode($data));
        } else {
            return $res;
        }
        /*$this->setRoute(self::ROUTE_AUTH_CARD);
        $this->setParams([
            'no' => $no,
            'ifsc' => $ifsc,
            'user_id' => $user->id,
            'type' => self::AUTH_BANK_CARD
        ]);
        return $this->auth();*/
    }

    public function face($requestId, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_SDK);
        $this->setParams([
            'user_id' => $user->id,
            'request_id' => $requestId,
            'type' => self::AUTH_FACE
        ]);
        return $this->auth();
    }

    public function panCardOcr($file, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_OCR);
        $this->setParams([
            'user_id' => $user->id,
            'file' => $file,
            'type' => self::AUTH_PAN_CARD
        ]);
        return $this->auth();
    }

    public function aadhaarCardFrontOcr($file, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_OCR);
        $this->setParams([
            'user_id' => $user->id,
            'file' => $file,
            'type' => self::AUTH_AADHAAR_CARD_FRONT,
        ]);
        return $this->auth();
    }

    public function aadhaarCardBackOcr($file, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_OCR);
        $this->setParams([
            'user_id' => $user->id,
            'file' => $file,
            'type' => self::AUTH_AADHAAR_CARD_BACK,
        ]);
        return $this->auth();
    }

    public function voterIdOcr($file, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_OCR);
        $this->setParams([
            'user_id' => $user->id,
            'file' => $file,
            'type' => self::AUTH_VOTER_ID
        ]);
        return $this->auth();
    }

    public function passportOcr($file, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_OCR);
        $this->setParams([
            'user_id' => $user->id,
            'file' => $file,
            'type' => self::AUTH_PASSPORT
        ]);
        return $this->auth();
    }

    public function faceComparison($face1, $face2, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_FACE_COMPARISON);
        $this->setParams([
            'face1' => $face1,
            'face2' => $face2,
            'user_id' => $user->id,
        ]);
        return $this->auth();
    }

    public function aadhaarKyc($resData, $requestId, User $user)
    {
        $this->setRoute(self::ROUTE_AUTH_SDK);
        $this->setParams([
            'user_id' => $user->id,
            'request_id' => $requestId,
            'type' => self::AUTH_AADHAAR_KYC,
            'resData' => $resData,
        ]);
        return $this->auth();
    }

    public function sign(Order $order, User $user, $status)
    {
        $resData = [
            'order_id' => $order->id,
            'status' => $status
        ];
        $this->setRoute(self::ROUTE_AUTH_SDK);
        $this->setParams([
            'user_id' => $user->id,
            'type' => self::AUTH_SIGN,
            'resData' => $resData,
        ]);
        return $this->auth();
    }
}
