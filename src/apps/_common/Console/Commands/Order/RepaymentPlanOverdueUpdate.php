<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Order;

use Common\Models\Order\RepaymentPlan;
use Common\Services\RepaymentPlan\RepaymentPlanServer;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class RepaymentPlanOverdueUpdate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:repayment-plan-overdue-update {type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '逾期天数更新';

    public function handle() {
        if ($all = $this->argument('type')) {
            MerchantHelper::callback(function () {
                $this->updateRepayAmount();
            });
        } else {
            MerchantHelper::callback(function () {
                $this->overdueUpdate();
            });
        }
    }

    protected function overdueUpdate() {
        $repaymentPlans = RepaymentPlanServer::server()->overdue();
        $count = $repaymentPlans->count();
        $success = $fail = 0;

        foreach ($repaymentPlans->get() as $repaymentPlan) {
            echo "orderID(OverdueDays):{$repaymentPlan->order_id}" . PHP_EOL;
            /** @var $repaymentPlan RepaymentPlan */
            $order = $repaymentPlan->order;
            $overdueDays = $order->getOverdueDays($repaymentPlan);
            $repaymentPlan->setScenario([
                'overdue_days' => $overdueDays,
            ])->save();
            $success++;
        }
        if ($count > 0) {
            EmailHelper::send("成功[{$success}/{$count}", '更新逾期天数');
        }
    }

    protected function updateRepayAmount($all = false) {
        if ($all) {
            echo 'update all start' . PHP_EOL;
            $repaymentPlans = RepaymentPlanServer::server()->all();
        } else {
            $repaymentPlans = RepaymentPlanServer::server()->unFinish();
        }
        $total = $repaymentPlans->count();
        $success = 0;
        $merchantId = MerchantHelper::helper()->getMerchantId();
        echo 'update principal_total START' . PHP_EOL;
        foreach ($repaymentPlans->get() as $repaymentPlan) {
            //echo "orderId:".$repaymentPlan->order_id.PHP_EOL;
            /** @var $repaymentPlan RepaymentPlan */
            $order = $repaymentPlan->order;
            if ($order) {
                $principalTotal = $order->allPrincipal();
                $repaymentPlan->setScenario([
                    'principal_total' => $principalTotal,
                ])->save();
                //echo $order->id, "=>" . $principalTotal . PHP_EOL;
                echo ".";
                $success++;
            }
            unset($order);
            unset($repaymentPlan);
            echo "UPING[$merchantId] {$success}/{$total} over " . PHP_EOL;
        }
        unset($repaymentPlans);
        unset($merchantId);
        return $success;
    }

}
