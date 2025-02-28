<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Collection;

use Admin\Controllers\BaseController;
use Admin\Rules\Collection\CollectionDeductionRule;
use Admin\Services\Collection\CollectionDeductionServer;

class CollectionDeductionController extends BaseController
{

    public function info(CollectionDeductionRule $rule)
    {
        if (!$rule->validate(CollectionDeductionRule::SCENARIO_INFO, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $params = $this->request->all();
        return $this->resultSuccess(CollectionDeductionServer::server()->getInfo($params));
    }

    public function create(CollectionDeductionRule $rule)
    {
        $param = $this->request->all();
        if (!$rule->validate(CollectionDeductionRule::SCENARIO_CREATE, $param)) {
            return $this->resultFail($rule->getError());
        }
        CollectionDeductionServer::server()->create($param);
        return $this->resultSuccess();
    }

}
