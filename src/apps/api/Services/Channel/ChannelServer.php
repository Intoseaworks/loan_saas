<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Channel;

use Api\Services\BaseService;
use Common\Redis\Channel\ChannelRecordRedis;

class ChannelServer extends BaseService
{

    public function recordUser($params)
    {
        $id = array_get($params, 'id');
        $type = array_get($params, 'type');

        $server = new ChannelRecordRedis();

        $data = $server->record($id, $type);
        return $this->outputSuccess('', $data);
    }

}
