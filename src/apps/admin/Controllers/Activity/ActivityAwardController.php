<?php

/**
 * @author Roc
 */

namespace Admin\Controllers\Activity;

use Admin\Services\Upload\UploadServer;
use Common\Response\ApiBaseController;
use Admin\Services\Activity\ActivityAwardServer;
use Common\Utils\LoginHelper;

class ActivityAwardController extends ApiBaseController {

    /**
     * 奖品列表
     * @return type
     */
    public function index() {
        $params = $this->getParams();
        $res = ActivityAwardServer::server()->list($params);
        foreach ($res as  &$item){
            if ( $item->upload_id ){
                $item->file = ActivityAwardServer::server()->getUploadFile($item->upload_id);
            }
        }
        return $this->resultSuccess($res);
    }

    /**
     * 新建活动奖品下拉列表
     * @return type
     */
    public function all() {
        $params = $this->getParams();
        $res = ActivityAwardServer::server()->listAll($params);
        return $this->resultSuccess($res);
    }

    /**
     * 奖品信息查看
     */
    public function view() {
        $res = ActivityAwardServer::server()->view($this->getParam('id'));
        if ($res) {
            return $this->resultSuccess($res);
        }
        return $this->resultFail();
    }

    /**
     * 添加奖品
     */
    public function addActivityAward() {
        $params = $this->getParams();
        $params['upload_id'] = $this->getUploadId();
        $res = ActivityAwardServer::server()->addActivityAward($params);
        if ($res->isSuccess()) {
            return $this->resultSuccess();
        }
        return $this->resultFail($res->getMsg());
    }

    public function setActivityAward(){
        $params = $this->getParams();
        $params['upload_id'] = $this->getUploadId();
        $res = ActivityAwardServer::server()->setActivityAward($params);
        if ($res->isSuccess()) {
            return $this->resultSuccess();
        }
        return $this->resultFail($res->getMsg());
    }

    public function getUploadId(){
        if ($this->request->file('file')){
            if (!$attributes = UploadServer::moveFile($this->request->file('file'))) {
                return $this->resultFail('上传文件保存失败');
            }
            $attributes['type'] = 'activity_award';
            if (!$model = UploadServer::create($attributes, LoginHelper::getAdminId())) {
                return $this->resultFail('上传文件保存记录失败');
            }
            return $model->getText()->id;
        }
        return 0;
    }

}
