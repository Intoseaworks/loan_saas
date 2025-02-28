<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Collection;

use Admin\Controllers\BaseController;
use Admin\Services\Collection\CollectionCallServer;
use Common\Models\Merchant\Merchant;

class CollectionCallController extends BaseController {

    public function index() {
        $params = $this->getParams();
        $data = CollectionCallServer::server()->list($params);
        return $this->resultSuccess($data);
    }

    public function status() {
        $res = CollectionCallServer::server()->setStatus($this->request->input('admin_id'));
        if ($res->isSuccess()) {
            return $this->resultSuccess($res->getMsg());
        } else {
            return $this->resultFail($res->getMsg());
        }
    }

    public function create() {
        if (!$this->required(['username', 'extension_num', 'type'])) {
            return $this->resultFail("Parameter is not complete");
        }
        $res = CollectionCallServer::server()->create($this->getParam('type', null), $this->getParam('username', null), $this->getParam('extension_num', null));
        if ($res->isSuccess()) {
            return $this->resultSuccess();
        }
        return $this->resultFail($res->getMsg());
    }

    public function call() {
        $params = $this->getParams();
        if ($params) {
            $res = CollectionCallServer::server()->call($params);
            if ($res->isSuccess()) {
                return $this->resultSuccess();
            }
            return $this->resultFail($res->getMsg());
        } else {
            return $this->resultFail('参数错误');
        }
    }
    
    public function callFileIndex() {
        $data = CollectionCallServer::server()->fileIndex($this->getParams());
        return $this->resultSuccess($data);
    }

    public function autoCallIndex() {
        return $this->resultSuccess(CollectionCallServer::server()->autoCallList($this->getParams()));
    }

    public function autoCallSave() {
        if(!$this->required(['level_name','assign_admin_ids'])){
            return $this->resultFail("Parameter is not complete");
        }
        $res = CollectionCallServer::server()->autoCallSave($this->getParams());
        if($res->isSuccess()){
            return $this->resultSuccess();
        }
        return $this->resultFail($res->getMsg());
    }

    public function autoCallRemove() {
        if(!$this->required(['level_name'])){
            return $this->resultFail("Parameter is not complete");
        }
        $res = CollectionCallServer::server()->autoCallRemove($this->getParam("level_name"));
        if($res->isSuccess()){
            return $this->resultSuccess();
        }
        return $this->resultFail($res->getMsg());
    }

    public function callFile() {
        $params = $this->getParams();
        $data = CollectionCallServer::server()->file($params);
        return $this->resultSuccess($data);
    }
    
    public function merchants() {
        return $this->resultSuccess(Merchant::model()->whereStatus(1)->pluck("product_name", "id"));
    }

}
