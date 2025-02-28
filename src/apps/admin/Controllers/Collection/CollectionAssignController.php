<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Collection;

use Admin\Controllers\BaseController;
use Admin\Rules\Collection\CollectionAssignRule;
use Admin\Rules\Collection\CollectionDeductionRule;
use Admin\Services\Collection\CollectionAssignServer;

class CollectionAssignController extends BaseController
{
    public function assignToCollector(CollectionAssignRule $rule)
    {
        if (!$rule->validate(CollectionAssignRule::SCENARIO_ASSIGN, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $params = $this->request->all();
        CollectionAssignServer::server()->assignToCollector($params);
        return $this->resultSuccess('success');
    }

}
