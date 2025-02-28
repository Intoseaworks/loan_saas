<?php

namespace Common\Console\Commands\PostLoan;

use Carbon\Carbon;
use Common\Models\Config\Config;
use Common\Models\Order\Order;
use Common\Models\PostLoan\PostLoanAnalyze as PostLoanAnalyzeModel;
use Common\Models\User\User;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PostLoanAnalyze extends Command
{
    /**
     * The name and signature of the console command.
     * 每日贷后分析统计
     * 注意：需要在逾期脚本&坏账脚本之后进行
     * @var string
     */
    protected $signature = 'post-loan:analyze {--is_today} {--merchant_id=} {--select_type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每日贷后分析统计';

    /**
     * @var Carbon
     */
    protected $date;

    protected $startTime;
    protected $endTime;

    /** @var int cache 放款订单缓存时间,分钟 */
    protected $payOrderCacheExpired = 3;
    /** @var int cache 待还款订单缓存时间,分钟 */
    protected $waitRepayOrderCacheExpired = 60;
    /** @var int cache 还款订单缓存时间,分钟 */
    protected $repayOrderCacheExpired = 3;
    /** @var int cache 获取1/3/7+逾期订单缓存时间,分钟 */
    protected $overdueDayOrdersCacheExpired = 3;
    /** @var int cache 坏账订单缓存时间,分钟 */
    protected $badOrderCacheExpired = 3;

    /** @var array 统计类型对应用户类型 */
    const TYPE_TO_USER_QUALITY = [
        PostLoanAnalyzeModel::USER_TYPE_ALL => [User::QUALITY_NEW, User::QUALITY_OLD],
        PostLoanAnalyzeModel::USER_TYPE_NEW => [User::QUALITY_NEW],
        PostLoanAnalyzeModel::USER_TYPE_OLD => [User::QUALITY_OLD],
    ];

    /** @var array */
    protected $userQuality;
    /** @var string */
    protected $userType;

    public function handle()
    {
        $date = Carbon::yesterday();
        if ($this->option('is_today')) {
            $date = Carbon::today();
        }
        $selectType = $this->option('select_type');

        $this->date = $date;
        $this->startTime = $this->date->copy()->startOfDay();
        $this->endTime = $this->date->copy()->endOfDay();

        $merchantId = $this->option('merchant_id');
        Config::openCache();
        MerchantHelper::callback(function ($merchant) use ($selectType) {
            foreach (self::TYPE_TO_USER_QUALITY as $key => $value) {
                if (isset($selectType) && $key != $selectType) {
                    continue;
                }

                $this->userType = $key;
                $this->userQuality = $value;

                $this->handleItem();
            }
        }, $merchantId);
        Config::closeCache();

        $this->line('每日贷后分析统计成功');
    }

    protected function handleItem()
    {
        $dateString = $this->date->toDateString();

        // 当日放款订单
        $payOrders = $this->getPayOrders();
        $payCount = $payOrders->count();
        $payAmount = $payOrders->sum('paid_amount');

        // 未还款订单
        $waitRepayOrders = $this->getWaitRepayOrders();
        $waitRepayCount = $waitRepayOrders->count();
        $waitRepayAmount = $waitRepayOrders->sum('waitRepayAmount');

        // 当日还款订单
        $repayOrders = $this->getRepayOrders();
        $repayCount = $repayOrders->count();
        $repayAmount = $repayOrders->sum('lastRepaymentPlan.repay_amount');

        // 当日逾期还款订单
        $overdueRepayOrders = $this->getOverdueRepayOrders();
        $overdueRepayCount = $overdueRepayOrders->count();
        //$overdueRepayAmount = $overdueRepayOrders->sum('lastRepaymentPlan.repay_amount');

        // 逾期还款占比：当日逾期还款数/当日还款数
        $overdueRepayRate = $overdueRepayCount ? bcdiv($repayCount, $overdueRepayCount, '2') : 0;

        // 1/3/7天+ 逾期单
        $overdueDay = $this->getOverdueDay();
        // 1/3/7天+ 逾期率？

        // 当日坏账数
        $badOrders = $this->getBadOrders();
        $badCount = $badOrders->count();
        // 坏账率？


        PostLoanAnalyzeModel::updateOrCreate([
            'date' => $dateString,
            'merchant_id' => MerchantHelper::getMerchantId(),
            'user_type' => $this->userType,
        ], [
            'pay_count' => $payCount,
            'pay_amount' => $payAmount,
            'wait_repay_count' => $waitRepayCount,
            'wait_repay_amount' => $waitRepayAmount,
            'repay_count' => $repayCount,
            'repay_amount' => $repayAmount,
            'overdue_repay_count' => $overdueRepayCount,
            'overdue_repay_rate' => $overdueRepayRate,
            'overdue_1days_count' => $overdueDay['1_days'],
            'overdue_1days_rate' => 0,
            'overdue_3days_count' => $overdueDay['3_days'],
            'overdue_3days_rate' => 0,
            'overdue_7days_count' => $overdueDay['7_days'],
            'overdue_7days_rate' => 0,
            'bad_count' => $badCount,
            'bad_rate' => 0,
        ]);
    }

    protected function filterUserType(Collection &$orders)
    {
        $orders = $orders->whereIn('quality', $this->userQuality);

        return $orders;
    }

    /**
     * 获取当日放款订单
     * @return mixed|Collection
     */
    protected function getPayOrders()
    {
        $res = Cache::remember($this->getCacheKey(__FUNCTION__), $this->payOrderCacheExpired, function () {
            $order = Order::query()->whereBetween('paid_time', [$this->startTime, $this->endTime])->get();
            return $order;
        });

        $this->filterUserType($res);

        return $res;
    }

    /**
     * 获取累计 未还款订单
     * @return mixed|Collection
     */
    protected function getWaitRepayOrders()
    {
        $res = Cache::remember($this->getCacheKey(__FUNCTION__), $this->waitRepayOrderCacheExpired, function () {
            $orders = Order::query()->whereIn('status', Order::WAIT_REPAYMENT_STATUS)->get();

            foreach ($orders as $order) {
                $order->waitRepayAmount = $order->repayAmount();
            }

            return $orders;
        });

        $this->filterUserType($res);

        return $res;
    }

    /**
     * 获取当日还款订单
     * @return mixed|Collection
     */
    protected function getRepayOrders()
    {
        $res = Cache::remember($this->getCacheKey(__FUNCTION__), $this->repayOrderCacheExpired, function () {
            $orders = Order::with('lastRepaymentPlan')->whereIn('status', Order::FINISH_STATUS)
                ->whereHas('lastRepaymentPlan', function (Builder $query) {
                    $query->whereBetween('repay_time', [$this->startTime, $this->endTime]);
                })
                ->get();

            return $orders;
        });

        $this->filterUserType($res);

        return $res;
    }

    /**
     * 获取当日逾期还款订单
     * @return Collection|mixed
     */
    protected function getOverdueRepayOrders()
    {
        $orders = $this->getRepayOrders();

        $orders = $orders->where('status', Order::STATUS_OVERDUE_FINISH);

        return $orders;
    }

    /**
     * 获取1/3/7+逾期单数
     * @return mixed
     */
    protected function getOverdueDay()
    {
        $orders = $this->getOverdueDayOrders();

        $res['1_days'] = $orders->where('lastRepaymentPlan.overdue_days', '>=', 1)->count();
        $res['3_days'] = $orders->where('lastRepaymentPlan.overdue_days', '>=', 3)->count();
        $res['7_days'] = $orders->where('lastRepaymentPlan.overdue_days', '>=', 7)->count();

        return $res;
    }

    /**
     * 获取1/3/7+逾期订单
     */
    protected function getOverdueDayOrders()
    {
        $res = Cache::remember($this->getCacheKey(__FUNCTION__), $this->overdueDayOrdersCacheExpired, function () {
            $orders = Order::query()->where('status', Order::STATUS_OVERDUE)
                ->whereHas('lastRepaymentPlan')
                ->get();

            return $orders;
        });

        $this->filterUserType($res);

        return $res;
    }

    /**
     * 获取当日进入坏账订单
     * @return mixed
     */
    protected function getBadOrders()
    {
        $res = Cache::remember($this->getCacheKey(__FUNCTION__), $this->badOrderCacheExpired, function () {
            $order = Order::query()->where('status', Order::STATUS_COLLECTION_BAD)
                ->whereHas('collection', function (Builder $query) {
                    $query->whereBetween('bad_time', [$this->startTime, $this->endTime]);
                })
                ->get();
            return $order;
        });

        $this->filterUserType($res);

        return $res;
    }

    protected function getCacheKey($key)
    {
        $keys = [
            __CLASS__,
            MerchantHelper::getMerchantId(),
            $key,
        ];

        return implode(':', $keys);
    }
}
