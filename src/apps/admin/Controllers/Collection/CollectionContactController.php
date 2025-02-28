<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Collection;

use Admin\Controllers\BaseController;
use Admin\Rules\Collection\CollectionContactRule;
use Admin\Rules\Collection\CollectionContactSmsRule;
use Admin\Services\Collection\CollectionContactServer;
use Admin\Services\Collection\CollectionContactSmsServer;

class CollectionContactController extends BaseController
{
    public function create(CollectionContactRule $rule)
    {
        $param = $this->request->all();
        if (!$rule->validate(CollectionContactRule::SCENARIO_CREATE, $param)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(CollectionContactServer::server()->create($param));
    }

    public function createSms(CollectionContactSmsRule $rule)
    {
        $param = $this->request->all();
        if (!$rule->validate(CollectionContactSmsRule::SCENARIO_CREATE, $param)) {
            return $this->resultFail($rule->getError());
        }
        $res = CollectionContactSmsServer::server()->create($param);
        if ($res && $res!="The order is sending message now ,please wait!"){
            return $this->resultSuccess($res);
        }else{
            return $this->resultFail('your sms send limits up to max or The order is sending message now ,please wait!');
        }
    }

}
