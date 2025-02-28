<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Marketing;

use Carbon\Carbon;
use Common\Jobs\Push\Sms\SmsByCommonJob;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Order\Order;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Trade\TradeLog;
use Common\Utils\Data\DateHelper;
use Common\Utils\Sms\SmsHelper;
use Illuminate\Console\Command;
use Common\Models\Merchant\App;

class MarketingSms extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketing:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '短信营销';

    public function handle()
    {
        $this->repaySuccessRecall();
        $this->withdrawMoneyNotice();
    }

    /**
     * 短信营销-还款次日召回
     */
    protected function repaySuccessRecall()
    {
        $startTime = Carbon::yesterday()->startOfDay()->toDateTimeString();
        $endTime = Carbon::today()->startOfDay()->toDateTimeString();
        $query = RepaymentPlan::whereInstallmentNum(1)->whereBetween('repay_time', [$startTime, $endTime]);
        $count = $query->count();
        $success = 0;
        foreach ($query->get() as $repaymentPlan) {
            $appName = App::find($repaymentPlan->order->app_id)->app_name ?? '';
            if (dispatch(new SmsByCommonJob($repaymentPlan->user_id, SmsHelper::EVENT_OLD_USER_RECALL, ['appName' => $appName]))) {
                $success++;
            }
        }
        echo "短信营销-还款次日召回：{$success}/{$count}" . PHP_EOL;
    }

    /**
     * 放款短信-线下取款客户skypayl渠道再次提醒客户取款 执行放款后的3,5,7天 7:00
     */
    protected function withdrawMoneyNotice()
    {
        $last3Days = [Carbon::now()->subDay(3)->startOfDay()->toDateTimeString(), Carbon::now()->subDay(3)->endOfDay()->toDateTimeString()];
        $last5Days = [Carbon::now()->subDay(5)->startOfDay()->toDateTimeString(), Carbon::now()->subDay(5)->endOfDay()->toDateTimeString()];
        $last7Days = [Carbon::now()->subDay(7)->startOfDay()->toDateTimeString(), Carbon::now()->subDay(7)->endOfDay()->toDateTimeString()];
        $query = Order::query()->whereStatus(Order::STATUS_PAYING)->wherePayType(BankCardPeso::PAYMENT_TYPE_CASH)
            ->where(function ($query) use ($last3Days, $last5Days, $last7Days) {
                $query->orWhereBetween('confirm_pay_time', $last3Days);
                $query->orWhereBetween('confirm_pay_time', $last5Days);
                $query->orWhereBetween('confirm_pay_time', $last7Days);
            });
        $count = $query->count();
        $success = 0;
        foreach ($query->get() as $order) {
            $lastDay = 7 - DateHelper::diffInDays($order->updated_at);
            $lastDay = $lastDay < 1 ? 'today' : $lastDay . ' days';
            $channel = $order->user->bankCard->instituion_name ?? '---';
            $tradeLog = $order->tradeLogRemiting();
            switch($tradeLog->trade_platform){
                case TradeLog::TRADE_PLATFORM_SKYPAY:
                    $channel = implode(',', TradeLog::WITHDRAWAL_INSTITUTION_SKYPAY);
                    break;
                case TradeLog::TRADE_PLATFORM_DRAGONPAY:
                    $channel = implode(',', TradeLog::WITHDRAWAL_INSTITUTION_DRAGONPAY);
                    break;
            }
            if (dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_PAY_SUCCESS_AGAIN_SMS, ['withdraw_no' => $order->withdraw_no, 'days' => $lastDay, 'channel' => $channel]))) {
                $success++;
            }
        }
        echo "短信营销-线下取款客户skypayl渠道再次提醒：{$success}/{$count}" . PHP_EOL;
    }
}
