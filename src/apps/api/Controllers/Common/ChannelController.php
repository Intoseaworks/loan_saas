<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/15
 * Time: 10:07
 */

namespace Api\Controllers\Common;

use Api\Rules\Common\ChannelRule;
use Api\Services\Channel\ChannelServer;
use Common\Response\ApiBaseController;

class ChannelController extends ApiBaseController
{
    public function record(ChannelRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate(ChannelRule::SCENARIO_COUNT, $params)) {
            return $this->resultFail($rule->getError());
        }
        $server = ChannelServer::server()->recordUser($params);

        return $this->resultSuccess($server->getData());
    }
}