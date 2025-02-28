<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Inbox;

use Api\Rules\Inbox\InboxRule;
use Api\Services\Inbox\InboxServer;
use Api\Services\Notice\NoticeServer;
use Common\Response\ApiBaseController;
use Common\Utils\Push\Jpush;

class InboxController extends ApiBaseController
{
    public function index(InboxRule $rule)
    {
        $user = $this->identity();
        if (!$rule->validate(InboxRule::SCENARIO_INDEX, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $page = $this->getParam('page', 1);
        $size = $this->getParam('size', 10);
        $type = $this->getParam('type', 10);
        return $this->resultSuccess(InboxServer::server()->getList($user->id, $page, $size, $type), '消息列表获取成功');
    }

    public function get(InboxRule $rule)
    {
        $user = $this->identity();
        if (!$rule->validate(InboxRule::SCENARIO_GET, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $param = [
            'user_id' => $user->id,
            'id' => $this->getParam('id'),
            'type' => $this->getParam('type'),
        ];
        $data = [];
        if ($param['type'] == Jpush::TYPE_INBOX) {
            $data = InboxServer::server()->getOne($param);
        } elseif ($param['type'] == Jpush::TYPE_NOTICE) {
            $data = NoticeServer::server()->getOne($param);
        }
        return $this->resultSuccess($data, '');
    }

    public function setRead(InboxRule $rule)
    {
        $user = $this->identity();
        if (!$rule->validate(InboxRule::SCENARIO_SET_READ, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $param = [
            'user_id' => $user->id,
            'id' => $this->getParam('id'),
            'type' => $this->getParam('type'),
        ];
        if (isset($param['type']) && $param['type'] == Jpush::TYPE_INBOX) {
            $data = InboxServer::server()->setRead($param);
        } else {
            $data = NoticeServer::server()->setRead($param);
        }
        return $this->resultSuccess($data, '');
    }

}
