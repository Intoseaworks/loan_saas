<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Tests\Services\Repay;

use Common\Models\Order\Order;
use Common\Models\Trade\AdminTradeAccount;
use Common\Utils\Data\DateHelper;
use Tests\Admin\Repayment\ManualRepaymentTest;
use Tests\Services\BaseService;

class RepayTestServer extends BaseService
{
    /**
     * 出款全流程
     *
     * @param $userId
     */
    public function repay($orderId)
    {
        $order = Order::model()->getOne($orderId);
        if (!($adminTradeAccount = AdminTradeAccount::where([
            'type' => AdminTradeAccount::TYPE_IN,
            'status' => AdminTradeAccount::STATUS_ACTIVE,
        ])->first())) {
            dd('无正常收款账号');
        }

        $manualRepaymentTest = new ManualRepaymentTest();
        $manualRepaymentTest->setUp();
        $manualRepaymentTestData = [
            'id' => $order->id,
            'trade_account_id' => $adminTradeAccount->id,
            'remark' => '还款备注！！',
            'repay_name' => $order->user->fullname,
            'repay_telephone' => $order->user->telephone,
            'repay_account' => '123456@qq.com',
            'repay_time' => DateHelper::date(),
        ];
        $manualRepaymentTest->testRepaySubmit($manualRepaymentTestData);
    }
}
