<?php

namespace Risk\Common\Services\Config;

use Common\Services\BaseService;
use Risk\Common\Models\Config\Config;

class ConfigServer extends BaseService
{
    public function initConfig($appId)
    {
        $config = [];

        return $this->firstOrCreateConfig($config, $appId);
    }

    public function firstOrCreateConfig($params, $appId = null)
    {
        foreach ($params as $key => $val) {
            if (!Config::findOrCreate($appId, $key, $val)) {
                return $this->outputError('更新' . $key . '失败');
            }
        }
    }
}
