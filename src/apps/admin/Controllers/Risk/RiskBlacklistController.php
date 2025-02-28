<?php

namespace Admin\Controllers\Risk;

use Admin\Rules\Risk\RiskBlacklistRule;
use Admin\Services\Risk\RiskBlacklistServer;
use Common\Response\AdminBaseController;
use Illuminate\Http\Request;

/**
 * 风控黑名单
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-6
 * Time: 下午3:37
 */
class RiskBlacklistController extends AdminBaseController
{
    /**
     * 风控黑名单列表
     * @param RiskBlacklistRule $rule
     * @return array
     */
    public function index(RiskBlacklistRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_LIST, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(RiskBlacklistServer::server()->list($this->getParams()));
    }

    /**
     * 风控黑名单详情
     * @param RiskBlacklistRule $rule
     * @return array
     */
    public function detail()
    {
        return $this->resultSuccess(RiskBlacklistServer::server()->detail($this->getParam('id')));
    }

    /**
     * 线下导入导入外部黑名单
     * @param RiskBlacklistRule $rule
     * @param Request $request
     * @return array
     */
    public function import(RiskBlacklistRule $rule, Request $request)
    {
        if (!$rule->validate($rule::SCENARIO_IMPORT, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        $server = RiskBlacklistServer::server()->import($request);
        return $server->isSuccess() ? $this->resultSuccess(null, $server->getMsg()) : $this->resultFail($server->getMsg());
    }
}
