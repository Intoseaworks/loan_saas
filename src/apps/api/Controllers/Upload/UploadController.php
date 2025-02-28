<?php

namespace Api\Controllers\Upload;

use Api\Models\Order\Order;
use Api\Models\User\UserAuth;
use Api\Rules\Upload\UploadRule;
use Api\Services\Order\OrderServer;
use Api\Services\Upload\UploadServer;
use Api\Services\User\UserAuthServer;
use Api\Services\User\UserCheckServer;
use Common\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Response\ApiBaseController;
use Common\Utils\Upload\OssHelper;
use Common\Services\Crm\CustomerServer;

/**
 * 上传文件
 * Class UploadController
 * @package App\Http\Api\Controllers\Upload
 * @author ChangHai Zhan
 */
class UploadController extends ApiBaseController {

    /**
     * @param UploadRule $rule
     * @return array
     */
    public function create(UploadRule $rule) {
        /** @var User $user */
        $user = $this->identity();
        //数据验证
        if (!$rule->validate(UploadRule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }

        $type = $this->request->input('type');
        if ($type == Upload::TYPE_PAN_CARD && $user->getPanCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            if ($user->app_id == "1")
                return $this->resultFail(t('is auth'));
        }
        if (in_array($type, [Upload::TYPE_AADHAAR_CARD_FRONT, Upload::TYPE_AADHAAR_CARD_BACK]) && $user->getAadhaarCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            if ($user->app_id == "1")
                return $this->resultFail(t('is auth'));
        }
        if (in_array($type, [Upload::TYPE_VOTER_ID_CARD_FRONT, Upload::TYPE_VOTER_ID_CARD_BACK]) && $user->getAddressVoterIdCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            return $this->resultFail(t('is auth'));
        }
        if (in_array($type, [Upload::TYPE_PASSPORT_IDENTITY, Upload::TYPE_PASSPORT_DEMOGRAPHICS]) && $user->getAddressPassportStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            return $this->resultFail(t('is auth'));
        }
        if (!$attributes = UploadServer::moveFile($this->request->file('file'))) {
            return $this->resultFail(trans('messages.上传文件保存失败'));
        }
        $attributes['type'] = $this->request->input('type');
        if (!$model = UploadServer::create($attributes, $user->id)) {
            return $this->resultFail(trans('messages.上传文件保存记录失败'));
        }
        $model->url = OssHelper::helper()->picTokenUrl($model->path);
        //非Urupee的人脸识别上传照片后授权nio.wang
        if ($type == Upload::TYPE_FACES && $user->app_id != "1") {
            UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_FACES);
        }
        //为Urupee的panCard验证后移nio.wang
        if ($type == Upload::TYPE_PAN_CARD && $user->app_id != "1") {
            UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_PAN_CARD);
        }
        return $this->resultSuccess($model->getText());
    }

    public function idCard(UploadRule $rule) {
        $params = $this->request->all();
        /** @var User $user */
        $user = $this->identity();
        //数据验证
        if (isset($params['card_num']) && $params['card_num']){
            $params['card_num'] = str_replace([" ",'-'],'', $params['card_num']);
            if (strpos($params['card_num'], '-') !== false) {
                return $this->resultFail("Incorrect document format, do not enter -");
            }
        }
        if (!$rule->validate(UploadRule::SCENARIO_ID_CARD, $params)) {
            return $this->resultFail($rule->getError());
        }
        if (isset($params['card_num']) && $params['card_num']){
            UserCheckServer::server()->checkCard($params['card_num'], \Auth::user()->id);
        }
        //保存证件
        if (!$attributes = UploadServer::moveFile($this->request->file('file_card'))) {
            return $this->resultFail(trans('messages.上传文件保存失败'));
        }
        $attributes['type'] = $this->request->input('card_type_code')."_BACK";
        if (!$modelCardBack = UploadServer::create($attributes, $user->id)) {
            return $this->resultFail(trans('messages.上传文件保存记录失败'));
        }
        //保存证件背面
        if (!$attributes = UploadServer::moveFile($this->request->file('file_card_back'))) {
            return $this->resultFail(trans('messages.上传文件保存失败'));
        }
        $attributes['type'] = $this->request->input('card_type_code');
        if (!$modelCard = UploadServer::create($attributes, $user->id)) {
            return $this->resultFail(trans('messages.上传文件保存记录失败'));
        }
        $user->card_type = Upload::TYPE_BUK[$params['card_type_code']];
        $user->id_card_no = $params['card_num'];
        $user->save();
        CustomerServer::server()->getCrmCustomer($user);
//        $modelCard->url = OssHelper::helper()->picTokenUrl($modelCard->path);
        //保存人脸
        if (!$attributes = UploadServer::moveFile($this->request->file('file_face'))) {
            return $this->resultFail(trans('messages.上传文件保存失败'));
        }
        $attributes['type'] = Upload::TYPE_FACES;
        if (!$modelFace = UploadServer::create($attributes, $user->id)) {
            return $this->resultFail(trans('messages.上传文件保存记录失败'));
        }
//        $modelFace->url = OssHelper::helper()->picTokenUrl($modelFace->path);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_ID_FRONT);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_FACES);
        return $this->resultSuccess(["card_file" => $modelCard->getText(), "face_file" => $modelFace->getText(), "card_file_back" => $modelCardBack->getText()]);
    }

    /**
     * 资料补件
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function replenish()
    {
        $user = $this->identity();
        if (!$lastOrder = OrderServer::server()->getLastOrder($user)) {
            return $this->resultFail(trans('订单不存在'));
        }
        if ($lastOrder->status != Order::STATUS_REPLENISH) {
            return $this->resultFail(trans('订单不能补件'));
        }
        // 保存身份证
        if ($fileCard = $this->request->file('file_card')) {
            if (!$attributes = UploadServer::moveFile($fileCard)) {
                return $this->resultFail(trans('messages.上传文件保存失败'));
            }
            $imgRelation = array_flip(Upload::TYPE_BUK);
            $attributes['type'] = array_get($imgRelation, $user->card_type);
            if (!$modelCard = UploadServer::create($attributes, $user->id)) {
                return $this->resultFail(trans('messages.上传文件保存记录失败'));
            }
            UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_ID_FRONT);
        }
        //保存人脸
        if ($fileFace = $this->request->file('file_face')) {
            if (!$attributes = UploadServer::moveFile($fileFace)) {
                return $this->resultFail(trans('messages.上传文件保存失败'));
            }
            $attributes['type'] = Upload::TYPE_FACES;
            if (!$modelCard = UploadServer::create($attributes, $user->id)) {
                return $this->resultFail(trans('messages.上传文件保存记录失败'));
            }
            UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_FACES);
        }
        /** 切换到补件原来状态 */
        OrderServer::server()->rollbackStatus($lastOrder->id);
        return $this->resultSuccess();
    }

    public function downloadByType(UploadRule $rule) {
        $user = $this->identity();
        $request = $this->request;
        // 数据验证
        if (!$rule->validate($rule::SCENARIO_DOWNLOAD_BY_TYPE, $request->all())) {
            return $this->resultFail($rule->getError());
        }

        $sourceId = $request->input('source_id');
        $type = $request->input('type');
        $paths = UploadServer::getPathsBySourceIdAndType($sourceId, $type)->toArray();

        if (empty($paths)) {
            return $this->resultFail('对应文件不存在');
        }

        $tmpFileName = UploadServer::setDownloadTmpFileName($paths);
        UploadServer::resetDownloadTmp();

        // 报单资料文件 重命名
        $fileName = UploadServer::getFileName($sourceId, $type);

        if (!file_exists($tmpFileName)) {
            if (!$downloadFile = UploadServer::downloadFiles($paths, $tmpFileName)) {
                return $this->resultFail('下载失败');
            }
        }
        // 打包预请求
        if ($request->has('auth')) {
            return $this->resultSuccess();
        }

        return UploadServer::download($tmpFileName, false, $fileName);
    }

}
