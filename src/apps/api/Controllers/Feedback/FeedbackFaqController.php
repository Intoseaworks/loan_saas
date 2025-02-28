<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Api\Controllers\Feedback;

use Api\Rules\Feedback\FeedbackFaqRule;
use Api\Services\Feedback\FeedbackFaqServer;
use Common\Response\ApiBaseController;

class FeedbackFaqController extends ApiBaseController
{
    public function index()
    {
        $param = $this->request->all();
        $data = FeedbackFaqServer::server()->getList($param);
        return $this->resultSuccess($data);
    }

    public function detail(FeedbackFaqRule $rule)
    {
        if (!$rule->validate(FeedbackFaqRule::SCENARIO_DETAIL, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(FeedbackFaqServer::server()->getOne($this->request->input('id')));
    }

}