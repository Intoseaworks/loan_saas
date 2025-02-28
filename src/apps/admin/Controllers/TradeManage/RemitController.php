<?php

namespace Admin\Controllers\TradeManage;

use Admin\Controllers\BaseController;
use Admin\Models\Order\Order;
use Admin\Rules\TradeManage\RemitRule;
use Admin\Services\Order\OrderServer;
use Admin\Services\TradeManage\RemitServer;
use Common\Models\BankCard\BankCard;
use Common\Utils\Lock\LockRedisHelper;

/**
 * Class RemitController
 * 人工出款
 * @package Admin\Controllers\TradeManage
 */
class RemitController extends BaseController {

    /**
     * 人工出款列表
     * @param RemitRule $rule
     * @return array
     */
    public function manualRemitList(RemitRule $rule) {
        $params = $this->getParams();
        $params['status'] = Order::WAIT_CONFIRM_PAY_STATUS;
        if (!$rule->validate($rule::SCENARIO_MANUAL_REMIT_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(RemitServer::server()->list($params));
    }

    /**
     * 人工出款详情
     */
    public function manualRemitDetail() {
        $id = $this->getParam('id');

        $order = OrderServer::server($id)->getOrder();

        //判断订单能否出款
        if (!in_array($order->status, Order::WAIT_CONFIRM_PAY_STATUS)) {
            # return $this->resultFail('订单状态有误');
        }

//        if (BasePayServer::server()->hasDaifuOpen()) {
//            return $this->resultFail('人工出款已关闭');
//        }
        //给订单上锁
        if (!RemitServer::server()->lockManualRemit($order->id)) {
            return $this->resultFail('订单已进入人工放款，请刷新后重试');
        }

        /** @var $bankCardInfos \Admin\Models\BankCard\BankCard */
        $order->setScenario(Order::SCENARIO_DETAIL)->getText();
        $bankCardNo = $order->bank_card_no;
        $bankCardInfos = optional($order->user)->bankCards;
        $order->bank_card_info = $bankCardInfos->where('account_no', $bankCardNo)->last();
        $order->bank_card_info && $order->bank_card_info->setScenario(BankCard::SCENARIO_DETAIL)->getText();
        unset($order->user);
        return $this->resultSuccess($order);
    }

    /**
     * 人工确认出款
     * @param RemitRule $rule
     * @return array
     */
    public function manualRemitSubmit(RemitRule $rule) {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_MANUAL_REMIT_SUBMIT, $params)) {
            return $this->resultFail($rule->getError());
        }
        $orderIds = explode(',', $params['order_id']);
        $orderServer = OrderServer::server()->canManualRemit($orderIds);

        /** 防止重复提交 */
        if (!LockRedisHelper::helper()->remitSubmit($params['order_id'])) {
            return $this->resultFail('操作过于频繁, 请稍后再试!');
        }

        if ($orderServer->isError()) {
            return $this->resultFail($orderServer->getMsg());
        }

        $orders = $orderServer->getData();
        foreach ($orders as $order) {
//            if (!RemitServer::server()->lockIsCurrentAdmin($order->id)) {
//                return $this->resultFail('订单审批人错误，请检查是否重复放款');
//            }

            $payPlatform = $this->getParam('pay_platform');
            /** 修改订单状态 & 确定放款支付渠道 */
            $result = RemitServer::server()->manualConfirmLoan($order, $payPlatform);

//        $adminTradeAccountNo = $params['trade_account'];
//        $result = RemitServer::server()->manualRemitSubmit($order, $adminTradeAccountNo, $params['trade_result'], $params['remark'] ?? '');

            if (!$result) {
                return $this->resultFail('记录保存失败');
            }
        }

        $nextOrder = RemitServer::server()->getNextOrder();

        return $this->resultSuccess([
                    'order_id' => optional($nextOrder)->id,
        ]);
    }

    /**
     * 出款失败列表
     * @param RemitRule $rule
     * @return array
     */
    public function failList(RemitRule $rule) {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_FAIL_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        $list = OrderServer::server()->failList($params);

        return $this->resultSuccess($list);
    }

    /**
     * 批量取消
     * @param RemitRule $rule
     * @return array
     */
    public function cancelBatch(RemitRule $rule) {

        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_CANCEL_BATCH, $params)) {
            return $this->resultFail($rule->getError());
        }

        $server = OrderServer::server();
        $list = $server->cancelBatch($params);

        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($list);
    }

    public function toWaitRemitBatch(RemitRule $rule) {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_TO_WAIT_REMIT_BATCH, $params)) {
            return $this->resultFail($rule->getError());
        }

        $server = OrderServer::server();
        $list = $server->toWaitRemitBatch($params);

        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($list);
    }

    /**
     * skypay余额
     */
    public function skypayBalance() {
        return $this->resultSuccess(0);
    }

    public function itServiceSendSMS() {
        $params = $this->getParams();
        if (isset($params['tpl_id']) && isset($params['order_list'])) {
            if (is_array($params['order_list'])) {
                # 获取模板
                $tpl = \Common\Models\Crm\SmsTemplate::model()->getOne($params['tpl_id']);
                if ($tpl) {
                    $success = 0;
                    $total = 0;
                    foreach ($params['order_list'] as $oid) {
                        $order = \Common\Models\Order\Order::model()->getOne($oid);
                        dispatch(new \Common\Jobs\Push\Sms\StdSmsJob($order->user->telephone, $tpl->tpl_content, \Common\Models\Merchant\App::getDataById($order->app_id, 'send_id')));
                        $success++;
                        $total++;
                    }
                    return $this->resultSuccess("Total:{$success}/{$total}");
                }
                return $this->resultFail("Tpl not found");
            }

            return $this->resultFail("order_list not array");
        } else {
            return $this->resultFail("Missing parameter");
        }
    }
}
