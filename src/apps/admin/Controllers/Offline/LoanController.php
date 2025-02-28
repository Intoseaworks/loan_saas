<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Offline;

use Admin\Controllers\BaseController;
use Admin\Rules\Order\OrderRule;
use Admin\Services\Order\OrderServer;
use Admin\Models\Order\Order;
use Illuminate\Http\Request;

class LoanController extends BaseController
{
    public function index(OrderRule $rule)
    {
        $params = $this->getParams();
//        $params['status'] = Order::STATUS_SIGN;
        if (!$rule->validate(OrderRule::SCENARIO_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(OrderServer::server()->waitList($params));
    }
    
    public function view(OrderRule $rule)
    {
        if (!$rule->validate(OrderRule::SCENARIO_DETAIL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(OrderServer::server($this->getParam('id'))->view());
    }
    
    /**
     * 导入黑名单
     * @param UserRule $rule
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function upload(OrderRule $rule, Request $request)
    {
        $param = $this->request->all();
        if (!$rule->validate(OrderRule::SCENARIO_UPLOAD_LOAN, $param)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(OrderServer::server()->uploadCsv($request));
    }
    
    /**
     * 导入确认提交
     * @param UserRule $rule
     * @param Request $request
     * @return array
     * @throws \Common\Exceptions\ApiException
     */
    public function confirm(OrderRule $rule, Request $request)
    {
        $param = $this->getParams();
        if (!$rule->validate(OrderRule::SCENARIO_CONFIRM_LOAN_IMPORT, $param)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(OrderServer::server()->confirm($request));
    }
}