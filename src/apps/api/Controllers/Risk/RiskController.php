<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Risk;

use Api\Rules\Risk\RiskRule;
use Api\Services\Risk\RiskServer;
use Common\Response\ApiBaseController;
use Common\Utils\Email\EmailHelper;

class RiskController extends ApiBaseController
{
    public function authTime(RiskRule $rule)
    {
        $params = $this->request->all();
        if (!$rule->validate(RiskRule::METHOD_AUTH_STATUS, $params)) {
            return $this->resultFail($rule->getError());
        }
        if ($this->getParam('token') != 'b7c23032fcd840c2f4ff4a68f0bf72df') {
            EmailHelper::send($params, '风控第三方认证数据回调无权限');
            return $this->resultFail('无权限 ');
        }
        RiskServer::server()->authTime($params);
        return $this->resultSuccess([], '提交成功');
    }
}
