<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/17
 * Time: 15:05
 */

namespace Api\Services\IDCard;

use Api\Models\Upload\Upload;
use Api\Models\User\User;
use Api\Models\User\UserInfo;
use Api\Services\User\UserAuthServer;
use Common\Models\User\UserAuth;
use Common\Services\BaseService;
use Common\Utils\Curl;
use Common\Utils\Data\DistrictCodeHelper;
use Common\Utils\Data\IDCardHelper;
use Common\Utils\Data\StringHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Upload\ImageHelper;
use Common\Validators\Validation;

class IDCardServer extends BaseService
{

    /** @var \Illuminate\Contracts\Auth\Authenticatable|User */
    private $user;

    public function __construct()
    {
        $this->user = \Auth::user();
    }

    public function authFront($imgPath)
    {
        try {
            $dataFace = $this->curlFace($imgPath);
            $dataFace = $this->authFrontInfo($dataFace);
            $user = $this->saveUser($dataFace);
            $this->saveUserInfoFront($dataFace);
            UserAuthServer::server()->setAuth($this->user->id, UserAuth::TYPE_ID_FRONT);
            $result = [
                'fullname' => $user['fullname'],
                'id_card_no' => $user['id_card_no'],
            ];
            return $this->outputSuccess('', $result);

        } catch (\Exception $e) {
            EmailHelper::sendException($e, '身份证认证异常');
            return $this->outputError($e->getMessage());
        }
    }

    public function authBack($imgPath)
    {
        try {
            $idCardInfo = $this->curlFace($imgPath);
            $this->authBackFace($idCardInfo);
            $this->saveUserInfo($idCardInfo);
            UserAuthServer::server()->setAuth($this->user->id, UserAuth::TYPE_ID_BACK);
            return $this->outputSuccess();

        } catch (\Exception $e) {
            EmailHelper::sendException($e, '身份证认证异常');
            return $this->outputError($e->getMessage());
        }
    }

    public function authHandheld()
    {
        if (!UserAuthServer::server()->setAuth($this->user->id, UserAuth::TYPE_ID_HANDHELD)) {
            return $this->outputException('手持身份证认证失败');
        }
        return $this->outputSuccess();
    }

    public function IDConfirm($params)
    {
        $user = User::model()->getOne($this->user->id);
        $params = [
            'fullname' => $params['fullname'],
            'id_card_no' => $params['id_card_no'],
        ];
        $user->setScenario(User::SCENARIO_ID_CARD)->saveModel($params);
    }

    public function getIdentity()
    {
        return [
            'fullname' => $this->user->fullname ?? '',
            'id_card_no' => $this->user->id_card_no ?? '',
            'id_card_front_status' => $this->getAuthStatus(UserAuth::TYPE_ID_FRONT),
            'id_card_front_url' => $this->getUploadedUrl(Upload::TYPE_ID_FRONT),
            'id_card_back_status' => $this->getAuthStatus(UserAuth::TYPE_ID_BACK),
            'id_card_back_url' => $this->getUploadedUrl(Upload::TYPE_ID_BACK),
            'id_card_handheld_status' => $this->getAuthStatus(UserAuth::TYPE_ID_HANDHELD),
            'id_card_handheld_url' => $this->getUploadedUrl(Upload::TYPE_ID_HANDHELD),
            'faces_status' => $this->getAuthStatus(UserAuth::TYPE_FACES),
            'bankcard_status' => $this->getAuthStatus(UserAuth::TYPE_BANKCARD),
        ];
    }

    public function authFaces()
    {
        $result = UserAuthServer::server()->setAuth($this->user->id, UserAuth::TYPE_FACES);

        if (!$result) {
            return $this->outputException('人脸识别认证失败');
        }
        return $this->outputSuccess();
    }

    private function getUploadedUrl($type)
    {
        $upload = Upload::model()->getPathByUserId($this->user->id);

        if (!isset($upload[$type])) {
            return '';
        }

        return ImageHelper::getPicUrl($upload[$type]);
    }

    private function getAuthStatus($type)
    {
        $status = UserAuthServer::server()->getAuth($this->user->id, $type);

        if (!isset($status)) {
            return '';
        }
        return $status;
    }

    public function authBackFace($idCardInfo)
    {
        /** 判断是否是反面 */
        if (empty($idCardInfo['side']) || $idCardInfo['side'] != 'back' || empty($idCardInfo['valid_date']) || empty($idCardInfo['issued_by'])) {
            return $this->outputException('身份证反面拍照模糊，请重新拍照!');
        }

        /** 校验格式 */
        list($startDate, $endDate) = explode('-', $idCardInfo['valid_date']);
        $exception = IDCardHelper::checkValidity($startDate, $endDate);
        if ($exception !== true) {
            if ($exception->getCode() == IDCardHelper::VALIDITY_CODE) {
                return $this->outputException($exception->getMessage());
            } else {
                return $this->outputException('身份证反面拍照模糊，请重新拍照!');
            }
        }

    }

    public function saveUserInfo($idCardInfo)
    {
        $model = UserInfo::firstOrNewModel(UserInfo::SCENARIO_SAVE_ID_BACK_INFO, ['user_id' => $this->user->id]);
        $params = [
            'id_card_valid_date' => $idCardInfo['valid_date'],
            'id_card_issued_by' => $idCardInfo['issued_by'],
        ];
        $model->saveModel($params);
    }

    public function curlFace($imgPath)
    {
        $post = [
            'api_key' => config('config.faceid_app_key'),
            'api_secret' => config('config.faceid_app_secret'),
            'legality' => '1',
            'image' => new \CURLFile($imgPath),
        ];

        $curlInfo = Curl::post($post, 'https://api.faceid.com/faceid/v1/ocridcard');

        return $faceid_result = json_decode($curlInfo, true);
    }

    protected function authFrontInfo($dataFace)
    {
        if (!isset($dataFace['name']) || !isset($dataFace['id_card_number'])) {
            return $this->outputException('身份证识别失败，请重新上传 -1');
        }

        if (!isset($dataFace['gender']) || !isset($dataFace['address'])) {
            return $this->outputException('身份证识别失败，请重新上传 -2');
        }

        $dataFace['name'] = StringHelper::delSpace($dataFace['name']);
        $dataFace['id_card_number'] = StringHelper::delSpace($dataFace['id_card_number']);
        $dataFace['gender'] = StringHelper::delSpace($dataFace['gender']);
        $dataFace['address'] = StringHelper::delSpace($dataFace['address']);

        /** 过滤身份证非法字符 */
        if (!Validation::identity($dataFace['id_card_number'])) {
            return $this->outputException('身份证识别失败，请重新上传 -3');
        }

        /** 过滤姓名非法字符 */
        if (!Validation::zh(str_replace('·', '', $dataFace['name']))) {
            return $this->outputException('身份证识别失败，请重新上传 -4');
        }

        /** 身份证重复注册拦截 */
        if ($user = User::model()->getByIdCardNo($dataFace['id_card_number'])) {
            if ($user->id <> $this->user->id) {
                return $this->outputException("该身份证已存在，已绑定账号：{$user->telephone}。如您需换绑，可联系客服 或退出至登录页操作“更换手机号”");
            }
        }

        return $dataFace;
    }

    public function saveUser($dataFace)
    {
        $params = [
            'fullname' => $dataFace['name'],
            'id_card_no' => $dataFace['id_card_number'],
        ];
        return $this->user->setScenario(User::SCENARIO_ID_CARD)->saveModel($params);
    }

    public function saveUserInfoFront($dataFace)
    {
        $params = [
            'gender' => $dataFace['gender'],
            'address' => $dataFace['address'],
        ];

        $provinceCode = substr($dataFace['id_card_number'], 0, 2);
        $provinces = array_pluck(DistrictCodeHelper::shengCode(), 'name', 'sheng');
        if ($provinceName = array_get($provinces, $provinceCode)) {
            $params['province'] = $provinceName;
        }

        $model = UserInfo::firstOrNewModel(UserInfo::SCENARIO_SAVE_ID_FRONT_INFO, ['user_id' => $this->user->id]);
        $model->saveModel($params);
    }
}