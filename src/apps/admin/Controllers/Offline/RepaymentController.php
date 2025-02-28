<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Offline;


use Admin\Rules\Order\OrderRule;
use Admin\Services\Order\OrderServer;
use Illuminate\Http\Request;
use Admin\Models\Upload\Upload;
use Admin\Controllers\BaseController;


class RepaymentController extends BaseController
{

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
        if (!$rule->validate(OrderRule::SCENARIO_UPLOAD_REPAYMENT, $param)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(OrderServer::server()->uploadCsv($request, Upload::TYPE_IMPORT_MANUAL_REPAYMENT));
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
        if (!$rule->validate(OrderRule::SCENARIO_CONFIRM_REPAYMENT_IMPORT, $param)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(OrderServer::server()->repaymentConfirm($request));
    }
}
