<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Collection;

use Admin\Controllers\BaseController;
use Admin\Rules\Collection\CollectionRecordRule;
use Admin\Services\Collection\CollectionRecordServer;

class CollectionRecordController extends BaseController
{
    public function index()
    {
        $param = $this->request->all();
        $data = CollectionRecordServer::server()->getPageList($param);
        return $this->resultSuccess($data);
    }

    public function view(CollectionRecordRule $rule)
    {
        if (!$rule->validate(CollectionRecordRule::SCENARIO_DETAIL, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(CollectionRecordServer::server()->getOne($this->request->input('id')));
    }

    public function create(CollectionRecordRule $rule)
    {
        $param = $this->request->all();
        if (!$rule->validate(CollectionRecordRule::SCENARIO_CREATE, $param)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(CollectionRecordServer::server()->create($param));
    }
    
    public function backOut(){
        return $this->resultSuccess(\Admin\Models\Collection\Collection::model()->getBackOut());
    }

}
