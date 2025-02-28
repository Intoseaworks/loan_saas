<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-26
 * Time: 15:30
 */

namespace Admin\Controllers\OperateData;


use Admin\Controllers\BaseController;
use Admin\Rules\OperateData\PostLoanRule;
use Admin\Services\OperateData\PostLoanServer;

class PostLoanController extends BaseController
{
    protected $rule;

    public function __construct(PostLoanRule $rule)
    {
        parent::__construct();
        $this->rule = $rule;
    }

    public function index()
    {
        $params = $this->getParams();
        if (!$this->rule->validate($this->rule::SCENARIO_LIST, $params)) {
            return $this->resultFail($this->rule->getError());
        }

        return $this->resultSuccess(PostLoanServer::server()->getList($params));
    }
    
    public function reloanRate(){
        $params = $this->getParams();
        return $this->resultSuccess(PostLoanServer::server()->reloanRate($params));
    }
}
