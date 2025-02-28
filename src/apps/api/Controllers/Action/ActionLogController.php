<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Action;

use Api\Rules\Action\ActionLogRule;
use Api\Services\Action\ActionLogService;
use Common\Response\ApiBaseController;
use Common\Utils\MerchantHelper;
use Yunhan\Utils\Env;

class ActionLogController extends ApiBaseController
{
    public function create(ActionLogRule $rule)
    {
        if($this->getParam('token') && $user = $this->identity()){
            $userId = $user->id;
            $quality = $user->getRealQuality();
        }else{
            $userId = 0;
            $quality = 0;
        }
        if (!$rule->validate(ActionLogRule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $params = $this->request->all();
        if(!Env::isProd()){
            //DingHelper::notice(['params' => $params], '终端埋点数据');
        }
        $actionName = array_get($params, "name");
        $data = [
            'app_id' => MerchantHelper::getAppId(),
            'user_id' => $userId,
            'quality' => $quality,
            'name' => $actionName,
            'device_uuid' => array_get($params, "device_uuid") ?: array_get($params, "device-uuid", '00000000-0000-0000-0000-000000000000'),
            'app_version' => array_get($params, "app_version"),
            'client_id' => array_get($params, "client_id"),
            'brand' => array_get($params, "brand"),
            'unit_type' => array_get($params, "unit_type"),
            'content' => array_get($params, "content"),
        ];
        ActionLogService::server()->job($data);
        return $this->resultSuccess([], '提交成功');
    }

}
