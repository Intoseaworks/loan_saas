<?php
/**
 * 调账服务层
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-15
 * Time: 下午4:00
 */

namespace Admin\Services\Repay;

use Admin\Models\Order\RepaymentPlan;
use Common\Services\BaseService;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;

class AccountAdjustmentServer extends BaseService
{
    public $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    /**
     * 调账-预算
     */
    public function accountAdjustmentPre()
    {
        $reapyIdArr = $this->data['repay_ids'];
        $type       = $this->data['type'];

        $this->_justAdjustment();
    }

    /**
     * 单纯的调账
     */
    private function _justAdjustment()
    {
        $repaymentPlan = RepaymentPlan::getByNo($this->params['repayment_plan_no']);

        $subject = CalcRepaymentSubjectServer::server();

        return [
            'repayment_plan_no' => $repaymentPlan->no,
            'order_no' => $repaymentPlan->order->order_no,
            'fullname' => $repaymentPlan->user->fullname,
            'appointment_paid_time' => $repaymentPlan->appoinment_paid_time,
            'overdue_days' => '',
        ];
    }

}