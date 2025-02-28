<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Api\Controllers\Repay;

use Admin\Services\Repayment\RepaymentPlanServer;
use Api\Models\Trade\TradeLog;
use Api\Rules\Repay\RepayRule;
use Api\Services\Repay\RepayServer;
use Common\Models\Order\Order;
use Common\Response\ApiBaseController;
use Common\Utils\Email\EmailHelper;

class RepayController extends ApiBaseController {

    /**
     * 还款方式列表
     */
    public function mode() {
        $this->identity();
        $params = $this->request->all();
        $data = RepayServer::server()->mode($params);
        return $this->resultSuccess($data);
    }

    /**
     * 执行代扣
     * @param RepayRule $rule
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function daikou(RepayRule $rule) {
        $user = $this->identity();
        $params = $this->request->post();
        if (!$rule->validate($rule::SCENARIO_DAIKOU, $params)) {
            return $this->resultFail($rule->getError());
        }

        if (!$order = RepayServer::server()->canRepay($params['order_id'], $user->id)) {
            return $this->resultFail('订单状态不正确');
        }

        if (!$tradeLog = RepayServer::server()->daikou($order)) {
            return $this->resultFail('扣款失败，请换绑银行卡重新支付');
        }

        return $this->resultSuccess(['trade_log_id' => $tradeLog->id], '已发起扣款，请耐心等待还款结果');
    }

    /**
     * 查询交易记录
     * @param RepayRule $rule
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function queryTrade(RepayRule $rule) {
        $user = $this->identity();
        $params = $this->request->post();
        if (!$rule->validate($rule::SCENARIO_QUERY_ORDER, $params)) {
            return $this->resultFail($rule->getError());
        }

        if (!isset($params['trade_log_id'])) {
            $params['trade_log_id'] = optional(optional($user->order)->lastTradeLog)->id;
        }

        return $this->resultSuccess(RepayServer::server()->queryTrade($params['trade_log_id'], ['user_id' => $user->id]));
    }

    public function repay(RepayRule $rule) {
        $this->identity();
        $params = $rule->validateE($rule::SCENARIO_REPAY);

        $server = RepayServer::server()->repay($params['channel'], $params['orderId'], array_get($params, 'repayAmount'), array_get($params, 'payerAccount'), array_get($params, 'method', 'referenceNumber'));

        if (!$server->isSuccess()) {
            return $this->result($server->getCode(), $server->getMsg());
        }

        return $this->resultSuccess($server->getData(), 'success');
    }

    public function repayList() {
        $user = $this->identity();
        $order = $user->order;
        return $this->resultSuccess(RepayServer::server()->tradeList($order->id));
    }

    public function checkRepay() {
        $user = $this->identity();
        if ($this->getParam("transaction_no")) {
            $trade = TradeLog::model()->where("transaction_no", $this->getParam("transaction_no"))->first();
            if ($trade) {
                $res = RepayServer::server()->checkTrade($trade);
                return $this->resultSuccess($res ? "PAID" : "UNPAID");
            }
        }
        return $this->resultFail();
    }

    public function bank() {
        $res = [
//            [
//                "bankNameKey" => "Bank Name:",
//                "bankName" => "Kanbawza Bank",
//                "branchNameKey" => "Name:",
//                "branchName" => "Yan Naing Kyaw",
//                "accountNumerKey" => "Account Number:",
//                "accountNumber" => "3173 0199 9107 73601",
//            ],
//            [
//                "bankNameKey" => "Bank Name:",
//                "bankName" => "Test Banc T",
//                "branchNameKey" => "Name:",
//                "branchName" => "tutian",
//                "accountNumerKey" => "Account Number:",
//                "accountNumber" => "6666 1111 6666 9999",
//            ],
        ];
        return $this->resultSuccess($res);
    }

    public function addRepayBank(RepayRule $rule) {
        $user = $this->identity();
        $params = $this->request->post();
        unset($params['token']);
        if (!$rule->validate($rule::SCENARIO_ADD_REPAY_BANK, $params)) {
            return $this->resultFail($rule->getError());
        }
        $params['user_id'] = $user->id;
        $params['merchant_id'] = $user->merchant_id;
        return $this->resultSuccess(RepayServer::server()->addRepayBank($params));
    }

    public function getRepayBankList() {
        $user = $this->identity();
        return $this->resultSuccess(RepayServer::server()->getRepayBank($user->id));
    }

    public function removeRepayBank() {
        $user = $this->identity();
        return $this->resultSuccess(RepayServer::server()->removeRepayBank($user->id, $this->getParam('id')));
    }

    public function useBank() {
        $params = $this->getParams();
        $user = $this->identity();
        
        $server = RepayServer::server()->repay(array_get($params, 'channel', 'fawry'), $user->order->id, array_get($params, 'amount'), array_get($params, 'id'), 'bank');

        if (!$server->isSuccess()) {
            return $this->result($server->getCode(), $server->getMsg());
        }

        return $this->resultSuccess($server->getData(), 'success');
    }

}
