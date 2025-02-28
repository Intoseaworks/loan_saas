<?php

namespace Api\Controllers\Common;

use Api\Services\Common\VersionServer;
use Common\Models\Common\FriendLink;
use Common\Response\ServicesApiBaseController;
use Api\Services\Common\DictServer;

class CommonController extends ServicesApiBaseController
{
    /**
     * 获取最新版本情况
     * @return array
     */
    public function version()
    {
        $platform = strtolower($this->getParam('platform'));
        $version = $this->getParam('app_version');

        if (!in_array($platform, VersionServer::PLATFORM)) {
            return $this->resultFail('platform 参数不正确');
        }

        $lastVersion = VersionServer::server()->getLastVersionInfo($platform);
        VersionServer::server()->compareVersion($lastVersion, $version, $platform);

        return $this->resultSuccess($lastVersion);
    }

    public function dict() {
        $params = $this->getParams();
        if (is_array($params) && count($params)>0) {
            # 为兼容前端特殊处理
            if(isset($params['parent_code']) && stripos($params['parent_code'], 'RELATIONSHIP') !== false){
                $params['parent_code'] = "RELATIONSHIP";
            }
            $res = DictServer::server()->getListByArray($params);
            return $this->resultSuccess($res, '');
        }

        return $this->resultFail('参数不正确');
    }

    public function friendLink() {
        return $this->resultSuccess(FriendLink::model()->all());
    }

}
