<?php

namespace Api\Services\Action;

use Api\Models\Action\ActionLog;
use Api\Services\BaseService;
use Common\Jobs\ActionLogJob;
use Common\Utils\Data\DateHelper;
use Common\Utils\Host\HostHelper;

class ActionLogService extends BaseService
{

    public function job($data)
    {
        $data['ip'] = HostHelper::getIp();
        $data['created_at'] = DateHelper::dateTime();
        $actionNameArr = explode(',', array_get($data, 'name'));
        foreach ($actionNameArr as $actionNameData) {
            $data['name'] = $actionNameData;
            dispatch(new ActionLogJob($data));
        }
        return $this->outputSuccess();
    }

    public function create($data)
    {
        return ActionLog::model()->create($data);
    }

}
