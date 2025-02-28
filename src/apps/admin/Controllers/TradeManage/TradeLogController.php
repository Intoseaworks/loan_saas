<?php

namespace Admin\Controllers\TradeManage;

use Admin\Controllers\BaseController;
use Admin\Rules\TradeManage\AccountRule;
use Admin\Services\TradeManage\AccountServer;
use Admin\Services\TradeManage\TradeLogServer;
use Common\Models\Trade\AdminTradeAccount;

/**
 * Class TradeLogController
 * 支付记录
 * @package Admin\Controllers\TradeManage
 */
class TradeLogController extends BaseController
{
    /**
     * 支付记录列表
     * @param AccountRule $rule
     * @return array
     */
    public function tradeLogList(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(TradeLogServer::server()->list($params));
    }

    /**
     * 系统放款记录
     * @param AccountRule $rule
     * @return array
     */
    public function systemPayList(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_SYSTEM_PAY_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(TradeLogServer::server()->systemPayList($params));
    }

    /**
     * 代扣还款记录
     * @param AccountRule $rule
     * @return array
     */
    public function systemRepayList(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_SYSTEM_PAY_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(TradeLogServer::server()->systemRepayList($params));
    }

    /**
     * 账户列表
     * @param AccountRule $rule
     * @return array
     */
    public function accountList(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(AccountServer::server()->list($params));
    }

    /**
     * 账户添加
     * @param AccountRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     */
    public function accountCreate(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_CREATE, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(AccountServer::server()->create($params), '添加成功');
    }

    /**
     * 同一业务类型、支付方式，账户需去重判断
     * @param AccountRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     */
    public function accountCheck(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_CHECK, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(AccountServer::server()->hasExist($params));
    }

    /**
     * 禁用/启用 账户
     * @param AccountRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     */
    public function accountDisableOrEnable(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_CHANGE_STATUS, $params)) {
            return $this->resultFail($rule->getError());
        }
        AccountServer::server()->changeStatus($this->getParam('id'),
            $this->getParam('status'));
        return $this->resultSuccess([], '更新成功');
    }

    /**
     * 设置默认
     *
     * @param AccountRule $rule
     * @return array
     */
    public function accountDefault(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_CHANGE_DEFAULT, $params)) {
            return $this->resultFail($rule->getError());
        }
        AccountServer::server()->changeDefault($this->getParam('id'));
        return $this->resultSuccess([], '更新成功');
    }

    /**
     * 账户列表下拉配置
     * @param AccountRule $rule
     * @return array
     */
    public function accountOption(AccountRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_OPTION, $params)) {
            return $this->resultFail($rule->getError());
        }
        switch ($this->getParam('type')) {
            case AdminTradeAccount::TYPE_IN:
                $data = array_only(AdminTradeAccount::PLATFORM_ALIAS, AdminTradeAccount::REPAYMENT_PLATFORM);
                break;
            case AdminTradeAccount::TYPE_OUT:
                $data = array_only(AdminTradeAccount::PLATFORM_ALIAS, AdminTradeAccount::PAYMENT_PLATFORM);
                break;
            default :
                $data = [];
        }
        return $this->resultSuccess($data, '获取成功');
    }
}