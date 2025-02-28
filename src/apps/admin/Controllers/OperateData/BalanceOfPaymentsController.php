<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-26
 * Time: 15:30
 */

namespace Admin\Controllers\OperateData;


use Admin\Controllers\BaseController;
use Admin\Rules\OperateData\BalanceOfPaymentsRule;
use Admin\Services\OperateData\BalanceOfPaymentsServer;

class BalanceOfPaymentsController extends BaseController
{
    /**
     * @var null|BalanceOfPaymentsRule
     */
    protected $rule;

    /**
     * BalanceOfPaymentsController constructor.
     * @param BalanceOfPaymentsRule $rule
     */
    public function __construct(BalanceOfPaymentsRule $rule)
    {
        parent::__construct();
        $this->rule = $rule;
    }

    /**
     * @return array
     */
    public function index()
    {
        $params = $this->getParams();
        if (!$this->rule->validate($this->rule::SCENARIO_LIST, $params)) {
            return $this->resultFail($this->rule->getError());
        }
        return $this->resultSuccess(BalanceOfPaymentsServer::server()->list($params));
    }

    /**
     * @return array
     */
    public function incomeList()
    {
        $params = $this->getParams();
        if (!$this->rule->validate($this->rule::SCENARIO_INCOME_LIST, $params)) {
            return $this->resultFail($this->rule->getError());
        }
        return $this->resultSuccess(BalanceOfPaymentsServer::server()->incomeList($params));
    }

    /**
     * @return array
     */
    public function disburseList()
    {
        $params = $this->getParams();
        if (!$this->rule->validate($this->rule::SCENARIO_DISBURSE_LIST, $params)) {
            return $this->resultFail($this->rule->getError());
        }
        return $this->resultSuccess(BalanceOfPaymentsServer::server()->disburseList($params));
    }
}
