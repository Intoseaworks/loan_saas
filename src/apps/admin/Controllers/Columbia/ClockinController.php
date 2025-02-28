<?php

namespace Admin\Controllers\Columbia;

use Admin\Services\Columbia\ClockinServer;
use Admin\Controllers\BaseController;

class ClockinController extends BaseController {

    public function index() {
        return $this->resultSuccess(ClockinServer::server()->index($this->getParams()));
    }

    public function detail() {
        return $this->resultSuccess(ClockinServer::server()->detail($this->getParams()));
    }

    public function approve() {
        $res = ClockinServer::server()->apprive($this->getParams());
        if ($res->isSuccess()) {
            return $this->resultSuccess();
        } else {
            return $this->resultFail($res->getMsg());
        }
    }
    
    public function option(){
        return $this->resultSuccess(\Common\Models\Columbia\ColClockin::APPROVE_REJECT_RESION);
    }

}
