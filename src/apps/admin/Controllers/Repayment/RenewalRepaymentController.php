<?php

namespace Admin\Controllers\Repayment;

use Admin\Rules\Order\RepaymentPlanRule;
use Admin\Services\Repay\MockRestoreServer;
use Admin\Services\Repayment\RenewalRepaymentServer;
use Common\Response\AdminBaseController;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Models\Order\Order;

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-6
 * Time: 下午3:37
 */
class RenewalRepaymentController extends AdminBaseController {

    /**
     * 续期管理
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function RenewalPreList(RepaymentPlanRule $rule) {
        if (!$rule->validate($rule::SCENARIO_RENEWAL_PRE_LIST, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(RenewalRepaymentServer::server($this->getParams())->preList());
    }

    /**
     * 申请续期
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function applyRenewal(RepaymentPlanRule $rule) {
        if (!$rule->validate($rule::SCENARIO_APPLY_RENEWAL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }
        $return = RenewalRepaymentServer::server($this->getParams())->applyRenewal();
        if ( ($return instanceof RenewalRepaymentServer) && $return->isError() ) {
            return $this->resultFail($return->getMsg());
        }
        return $this->resultSuccess($return);
    }

    /**
     * 续期列表
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function renewalList(RepaymentPlanRule $rule) {
        if (!$rule->validate($rule::SCENARIO_RENEWAL_LIST, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(RenewalRepaymentServer::server($this->getParams())->renewalList());
    }

    public function renewalTest(RepaymentPlanRule $rule) {
        // return $this->resultSuccess(RenewalRepaymentServer::server($this->getParams())->test());
        return $this->resultSuccess(MockRestoreServer::server($this->getParams())->test());
    }

    public function renewalTrial() {
        $date = $this->getParam('date', '');
        $oid = intval($this->getParam('order_id'));
        if ($date) {
            list($y, $m, $d) = explode('-', $date);
            if (checkdate($m, $d, $y) && $oid) {

                $order = Order::model()->getOne($oid);
                $repaymentPlans = $order->repaymentPlans()->get();
                $subject = CalcRepaymentSubjectServer::server($repaymentPlans[0], $date)->getSubject();
                $data = [
                    'min_repay_amount' => $subject->renewalPaidAmount,
                    'renewal_charge' => $subject->renewalFee,
                    'overdue_fee' => $subject->overdueFee,
                    'overdue_days' => $subject->overdueDays
                ];
                return $this->resultSuccess($data);
            }
        }
        return $this->resultFail('参数错误');
    }

}
