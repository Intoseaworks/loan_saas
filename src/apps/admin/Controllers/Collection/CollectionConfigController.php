<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Collection;

use Admin\Controllers\BaseController;
use Admin\Services\Collection\CollectionConfigServer;

class CollectionConfigController extends BaseController
{
    public function optionDialProgress()
    {
        $param = $this->request->all();
        $data = CollectionConfigServer::server()->getOptionDialProgress(array_get($param, 'type'));
        return $this->resultSuccess($data);
    }

    public function optionDial()
    {
        $data = CollectionConfigServer::server()->getOptionDial();
        return $this->resultSuccess($data);
    }

    public function optionProgress()
    {
        $param = $this->request->all();
        $data = CollectionConfigServer::server()->getOptionProgress(array_get($param, 'dial'));
        return $this->resultSuccess($data);
    }

    public function optionTimeSlot() {
        return $this->resultSuccess(\Common\Models\Collection\CollectionRecord::TIME_SLOT);
    }
}