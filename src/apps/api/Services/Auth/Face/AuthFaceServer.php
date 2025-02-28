<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Auth\Face;

use Api\Models\Upload\Upload;
use Api\Models\User\User;
use Api\Services\BaseService;
use Api\Services\Upload\UploadServer;
use Api\Services\User\UserAuthServer;
use Common\Models\Third\ThirdPartyLog;
use Common\Models\User\UserAuth;
use Common\Models\User\UserThirdData;
use Common\Services\Third\ThirdRequestServer;
use Common\Utils\Sense\lib\Sense;
use Common\Utils\Services\AuthRequestHelper;
use Common\Utils\Upload\OssHelper;
use Common\Utils\Riskcloud\RiskcloudHelper;
use JMD\Libs\Services\DataFormat;
use Common\Utils\DingDing\DingHelper;

class AuthFaceServer extends BaseService {

    /**
     * 人脸认证结果上传
     *
     * @param $faceAll
     * @param $requestId
     * @return AuthFaceServer
     * @throws \Common\Exceptions\ApiException
     */
    public function uploadFace($faceAll, $requestId) {
        /** @var User $user */
        $user = \Auth::user();
        if (!$faceAll || !is_array($faceAll)) {
            $this->outputException('face file is null');
        }
        Upload::model()->clear($user->id, Upload::TYPE_FACES);
        foreach ($faceAll as $key => $face) {
            if (!$attributes = UploadServer::saveFile($face, 'face', $user->id . '_' . date("His", time()))) {
                $this->outputException(t('上传文件保存失败', 'auth') . $key);
            }
            $attributes['type'] = Upload::TYPE_FACES;
            if (!$model = UploadServer::create($attributes, $user->id)) {
                $this->outputException(trans('上传文件保存记录失败', 'auth') . $key);
            }
        }
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_FACES);
        ThirdPartyLog::model()->createByRequest(ThirdPartyLog::NAME_FACE, 'face file', 'sense face sdk', $requestId, '', $user->id);
//        (new AuthRequestHelper)->face($requestId, $user);
        return $this->outputSuccess();
    }

    /**
     * 人脸比对
     *
     * @param $user
     * @throws \Common\Exceptions\ApiException
     */
    public function faceComparison($user) {
        $cardImg = null;
        //20200925 Pan卡不清晰取消
        /*if ($user->getPanCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            $cardImg = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_PAN_CARD);
        }*/

        if (!$cardImg) {
            /** @var User $user */
            if ($user->getAadhaarCardKYCStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                $cardImg = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_AADHAAR_CARD_KYC);
            } elseif ($user->getAadhaarCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                $cardImg = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_AADHAAR_CARD_FRONT);
            } else {
                return $this->outputException('aadhaar have not auth');
            }
        }
        $face = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_FACES);
        if (!$cardImg) {
            return $this->outputException('aadhaar or pancard have not upload');
        }
        if (!$face) {
            return $this->outputException('face have not upload');
        }
        $aadhaarUrl = OssHelper::helper()->picTokenUrl($cardImg->path);
        $faceUrl = OssHelper::helper()->picTokenUrl($face->path);
        /*         * 替换印牛人脸比对* */
//        $authRequestHelper = new AuthRequestHelper();
//        $requestRes = $authRequestHelper->faceComparison($aadhaarUrl, $faceUrl, $user);
        $riskcloudData = [
            "image1" => base64_encode(file_get_contents($aadhaarUrl)),
            "image1Type" => "BASE64",
            "image2" => base64_encode(file_get_contents($faceUrl)),
            "image2Type" => "BASE64",
        ];

        $riskRes = RiskcloudHelper::helper()->faceMatch($riskcloudData);
        $requestRes = $riskRes->getData();
        if (isset($requestRes['score']) && is_numeric($requestRes['score'])) {
            $requestRes['score'] = round($requestRes['score'] / 100, 2);
        } else {
            $requestRes = ["error" => json_encode($requestRes)];
            $requestRes['score'] = 0;
        }
        $res = [
            "code" => $riskRes->getCode(),
            "msg" => "success",
            "data" => json_encode($requestRes)];
        $requestRes = new DataFormat($res);
        $thirdPartLogModel = new ThirdPartyLog();
        unset($riskcloudData['image1']);
        unset($riskcloudData['image2']);
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_FACE_COMPARISON, $riskcloudData, "faceMatch", '', '', $user->id);
        ThirdRequestServer::server()->requestDataCheck($requestRes, $thirdPartLogModel);
        $authData = json_decode($requestRes->getData(), true);
        $authData = array_merge($authData, [
            'aadhaar_upload_id' => $cardImg->id,
            'aadhaar_upload_path' => $cardImg->path,
            'face_upload_id' => $face->id,
            'face_upload_path' => $face->path,
        ]);
        UserThirdData::model()->create($user->id, UserThirdData::TYPE_FACE_COMPARISON, $authData, UserThirdData::CHANNEL_SERVICES);
    }

    /**
     * 人脸视频认证
     *
     * @return AuthFaceServer
     * @throws \Common\Exceptions\ApiException
     */
    public function uploadFaceVideo() {
        /** @var User $user */
        $user = \Auth::user();
        $file = $_FILES['video'];
        $filename = $file["tmp_name"];
        $md5 = md5_file($filename);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $dir = OssHelper::uploadLocalPath('face');
        $name = $dir . $md5 . '.' . $ext;
        $bool = move_uploaded_file($filename, $name);
        if (!$bool) {
            return $this->output(self::OUTPUT_ERROR, t('数据保存异常', 'auth'), '');
        }

        $data = (new Sense())->sendImg(Sense::SILENT_DETECTION, $name, '');
        $code = array_get($data, 'code');
        $passed = array_get($data, 'passed');
        if ($code != 1000 || !$passed) {
            $this->outputException(t('验证不通过', 'auth'));
        }

        $img = 'data:image/jpeg;base64,' . $data['base64_image'];
        if (!$attributes = UploadServer::saveFile($img, 'face', $user->id . '_' . date("His", time()))) {
            $this->outputException(t('上传文件保存失败', 'auth'));
        }
        $attributes['type'] = Upload::TYPE_FACES;
        if (!$model = UploadServer::create($attributes, $user->id)) {
            $this->outputException(trans('上传文件保存记录失败', 'auth'));
        }

        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_FACES);
        return $this->outputSuccess();
    }

}
