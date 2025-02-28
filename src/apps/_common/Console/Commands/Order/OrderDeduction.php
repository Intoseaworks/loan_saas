<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/31
 * Time: 15:42
 */

namespace Common\Console\Commands\Order;

use Admin\Services\Repayment\RepaymentPlanServer;
use Common\Services\Order\OrderServer;
use Common\Utils\Email\EmailHelper;
use Illuminate\Console\Command;
use Common\Models\Trade\TradeLog;
use Admin\Services\Repayment\ManualRepaymentServer;
use Common\Services\Repay\RepayServer;

class OrderDeduction extends Command {

    const DEDUCTION_AMOUNT = 20;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:deduction {--date=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时完结可减免订单 --date 指定时间 --all 全部未还款订单倒序1000条';

    # 自动减免小于20元的订单

    public function handle() {
        $all = $this->option('all');
        if ($all) {
            echo "ALL 1000";
            $orders = OrderServer::server()->getAllUnrepayOrders();
        } else {
            $date = $this->option('date');
            $date = $date ?? date("Y-m-d");
            echo $date . PHP_EOL;
            $orders = OrderServer::server()->getCanDeductionOrders($date);
        }
        foreach ($orders as $order) {
            \Common\Utils\MerchantHelper::helper()->setMerchantId($order->merchant_id);
            echo PHP_EOL . "开始处理订单({$order->id}):{$order->order_no}";
            try {
                $deduction = $order->repayAmount();
                if ($order && $deduction <= self::DEDUCTION_AMOUNT) {
                    $repaymentPlan = $order->lastRepaymentPlan;
                    $tradeParams = [
                        'remark' => '自动减免',
                        'repay_name' => $order->user->fullname,
                        'repay_telephone' => $order->user->telephone,
                        'repay_account' => '',
                        'repay_time' => date('Y-m-d H:i:s'),
                        'repay_channel' => TradeLog::TRADE_PLATFORM_MANUAL_DEDUCTION,
                        'repay_amount' => $deduction
                    ];

                    $trade = ManualRepaymentServer::server()->addRepayTradeLog($order, TradeLog::TRADE_PLATFORM_DEDUCTION, $deduction, $tradeParams);

                    RepayServer::server($repaymentPlan, $trade)->completeRepay();
                    echo " - 处理完毕";
                } else {
                    echo " - 不满足条件";
                }
            } catch (\Exception $ex) {
                echo $ex->getMessage();
                continue;
            }
        }
    }

    # 自动减免尾期，目前已经(废弃)

    public function old() {
        $canDeductionRepaymentPlans = OrderServer::server()->repayOneDayCanDeductionOrders();
        $count = $canDeductionRepaymentPlans->count();
        $success = 0;
        foreach ($canDeductionRepaymentPlans->get() as $repaymentPlan) {
            try {
                RepaymentPlanServer::server()->reductionRepaySuccess($repaymentPlan->order, $repaymentPlan);
                $success++;
                echo $repaymentPlan->id . PHP_EOL;
//                dispatch(new SmsByCommonJob($repaymentPlan->user_id, SmsHelper::EVENT_ORDER_DEDUCTION));
            } catch (\Exception $e) {
                EmailHelper::send([
                    'e' => $e->getMessage()
                        ], '尾期减免异常');
            }
        }
        EmailHelper::send("成功{$success}/{$count}", '定时完结可减免订单');
    }

}
