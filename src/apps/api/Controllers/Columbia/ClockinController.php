<?php

namespace Api\Controllers\Columbia;

use Api\Rules\Columbia\ClockinRule;
use Api\Services\Columbia\ClockinServer;
use Common\Response\ServicesApiBaseController;
use Common\Services\Upload\UploadServer;
use Common\Utils\Baidu\BaiduApi;

class ClockinController extends ServicesApiBaseController {

    public function index() {
        $user = $this->identity();
        return $this->resultSuccess(ClockinServer::server()->index($user, $this->getParam("order_id")));
    }

    public function punch(ClockinRule $rule) {
        if (!$rule->validate(ClockinRule::PUNCH, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $insert = [];
        //保存自拍
        if (!$attributes = UploadServer::moveFile($this->request->file('selfie_pic'))) {
            return $this->resultFail(trans('messages.上传文件保存失败'));
        }
        $insert['pic_selfie'] = $attributes['path'];
        if (!$attributes = UploadServer::moveFile($this->request->file('surroundings_pic'))) {
            return $this->resultFail(trans('messages.上传文件保存失败'));
        }
        $insert['pic_surroundings'] = $attributes['path'];
//        $insert['pic_surroundings'] = 'test';
//        $insert['pic_selfie'] = 'test';
        $insert['longitude'] = $this->getParam('longitude');
        $insert['latitude'] = $this->getParam('latitude');
        $mapsData = BaiduApi::getAddress($insert['latitude'], $insert['longitude']);
        $insert['address'] = json_encode($mapsData);
        $user = $this->identity();
        $res = ClockinServer::server()->do($user, $insert);
        if ($res->isError()) {
            return $this->resultFail($res->getMsg());
        } else {
            return $this->resultSuccess();
        }
    }

    public function couponInfo() {
        $user = $this->identity();
        return $this->resultSuccess(["coupon_amount" => ClockinServer::server()->couponInfo($user)]);
    }

}
