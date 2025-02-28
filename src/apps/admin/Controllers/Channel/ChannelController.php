<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */
namespace Admin\Controllers\Channel;

use Admin\Controllers\BaseController;
use Admin\Rules\Channel\ChannelRule;
use Admin\Services\Channel\ChannelServer;

class ChannelController extends BaseController
{
    public function index(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_LIST, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $param = $this->request->all();
        $data = ChannelServer::server()->getList($param);
        return $this->resultSuccess($data);
    }

    public function monitor(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_MONITOR, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $param = $this->request->all();
        $data = ChannelServer::server()->getMonitor($param);
        return $this->resultSuccess($data);
    }

    public function monitorItem(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_MONITOR_ITEM, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $param = $this->request->all();
        $data = ChannelServer::server()->getMonitorItem($param);
        return $this->resultSuccess($data);
    }

    public function create(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $channelServer = ChannelServer::server();
        $channelServer->create($this->request->all());
        if ($channelServer->isError()) {
            return $this->resultFail($channelServer->getMsg());
        }
        return $this->resultSuccess($channelServer->getData(), $channelServer->getMsg());
    }

    public function update(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_UPDATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $channelServer = ChannelServer::server();
        $channelServer->update($this->request->input('id'), $this->request->all());
        if($channelServer->isError()){
            return $this->resultFail($channelServer->getMsg());
        }
        return $this->resultSuccess([], $channelServer->getMsg());
    }

    public function del(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_DELETE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $channelServer = ChannelServer::server();
        $channelServer->del($this->request->input('id'));
        if($channelServer->isError()){
            return $this->resultFail($channelServer->getMsg());
        }
        return $this->resultSuccess([], $channelServer->getMsg());
    }

    public function view(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_DETAIL, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(ChannelServer::server()->getOne($this->request->input('id')));
    }

    public function checkCode(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_CHECK, $this->request->all())) {
            return $this->result(2222, $rule->getError());
        }
        return $this->resultSuccess();
    }

    public function updateStatus(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_STATUS, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $channelServer = ChannelServer::server();
        $channelServer->updateStatus($this->request->input('id'), $this->request->input('status'));
        if($channelServer->isError()){
            return $this->resultFail($channelServer->getMsg());
        }
        return $this->resultSuccess([], $channelServer->getMsg());
    }

    public function updateTop(ChannelRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_TOP, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $channelServer = ChannelServer::server();
        $channelServer->updateTop($this->request->input('id'));
        return $this->resultSuccess([], $channelServer->getMsg());
    }

}
