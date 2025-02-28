<?php

namespace Common\Services\NewClm\Rule;

use Common\Models\NewClm\ClmAmount;
use Common\Models\NewClm\ClmCustomer;
use Common\Models\Order\Order;
use Common\Models\User\UserInfo;

class RuleData
{
    protected $order;

    protected $user;

    protected $clmCustomer;

    protected $levelToAmount;

    public function __construct(ClmCustomer $customer, Order $order)
    {
        $this->order = $order;

        $this->user = $order->user;

        $this->clmCustomer = $customer;
    }

    /**
     * 获取历史逾期次数
     *
     * @return int
     */
    public function getHisOverdueCount()
    {
        $cnt = $this->getHisOrders()->whereIn('status', [Order::STATUS_OVERDUE, Order::STATUS_OVERDUE_FINISH])->count();
        # Sean 如果是次数的话，那就减1就可以
        if(in_array($this->order->status, [Order::STATUS_OVERDUE, Order::STATUS_OVERDUE_FINISH])){
            $cnt -= 1;
        }
        return $cnt;
    }

    /**
     * 获取当前订单收款方式
     *
     * @return string|null
     */
    public function getCurrentOrderPaymentType()
    {
        return $this->order->getPaymentType();
    }

    /**
     * 获取用户收款方式
     *
     * @return string|null
     */
    public function getUserPaymentType()
    {
        return optional($this->user->bankCard)->payment_type;
    }

    /**
     * 是否男性
     *
     * @return bool
     */
    public function isMale()
    {
        return $this->user->userInfo->gender == UserInfo::GENDER_MALE;
    }

    /**
     * 获取用户薪资
     * 取薪资等级区间 平均值
     *
     * @return float|int|null
     */
    public function getUserSalary()
    {
        $limitAmount = 191000;
        $salaryLevel = $this->user->userWork->salary;

        //$salaryRange = UserInfo::SALARY_RANGE;

        $newSalaryRange = [
            "MONEY1" => 191000,
            "MONEY2" => 191000,
            "MONEY3" => 223000,
            "MONEY4" => 318000,
            "MONEY5" => 430000,
            "MONEY6" => 558000,
            "MONEY7" => 797000
        ];
        if(is_numeric(str_replace(",","",$salaryLevel))){
            $limitAmount = str_replace(",","",$salaryLevel);
        }
        if (isset($newSalaryRange[$salaryLevel])) {
            $limitAmount = $newSalaryRange[$salaryLevel];
        }

        return $limitAmount;
    }

    /**
     * 获取用户电审确认月收入
     *
     * @return string|null
     */
    public function getUserConfirmSalary()
    {
        return is_numeric($this->user->userInfo->confirm_month_income) ? $this->user->userInfo->confirm_month_income : null;
    }

    /**
     * 根据金额获取等级限制
     *
     * @param $amount
     *
     * @return int|string|null
     */
    public function getLimitLevelByAmount($amount)
    {
        $levelToAmount = $this->getLevelToAmount();

        $limitLevel = key($levelToAmount); // 默认最低级
        foreach ($levelToAmount as $k => $v) {
            if ($amount < $v) {
                break;
            }

            $limitLevel = $k;
        }

        return $limitLevel;
    }

    /**
     * 根据 等级获取对应金额 列表
     *
     * @return mixed|null
     */
    public function getLevelToAmount()
    {
        if (!$this->levelToAmount) {
            $this->levelToAmount = ClmAmount::getLevelAndAmount()
                ->sortBy('clm_level')
                ->pluck('clm_amount', 'clm_level')
                ->toArray();
        }

        return $this->levelToAmount;
    }

    /**
     * 获取超过指定逾期天数的订单中 的最小贷款金额
     *
     * @param $days
     *
     * @return float|mixed|null
     */
    public function getOverdueDaysExceedMinAmount($days)
    {
        $hisOrders = $this->getHisOrders();

        $minAmount = null;
        foreach ($hisOrders as $order) {
            $overdueDays = $order->lastRepaymentPlan->overdue_days;
            $loanAmount = $order->principal;

            if ($overdueDays > $days) {
                $minAmount = isset($minAmount) ? min($minAmount, $loanAmount) : $loanAmount;
            }
        }

        return $minAmount;
    }

    /**
     * 获取客户当前等级对应可贷额度
     *
     * @return mixed|float
     * @throws \Exception
     */
    public function getCurrentLevelAmount()
    {
        $clmAmount = $this->clmCustomer->getCurrentLevelAmount();

        if (!$clmAmount) {
            throw new \Exception('cml amount not found');
        }

        return $clmAmount->clm_amount;
    }

    /**
     * 获取完结订单笔数
     *
     * @return int
     */
    public function getFinishNum(): int
    {
        return $this->getFinishedOrders()->count();
    }

    /**
     * 获取当前订单的逾期天数
     *
     * @return int|mixed|null
     */
    public function getCurrentOrderOverdueDays()
    {
        return $this->order->getOverdueDays();
    }

    /**
     * 获取最后一笔完结订单的贷款金额
     *
     * @return float|null
     */
    public function getLatelyFinishedLoanAmount()
    {
        $order = $this->getLatelyFinishedOrder();

        if (!$order) {
            return null;
        }

        return $order->principal;
    }

    /**
     * 获取最近一笔完结订单
     *
     * @return Order|null
     */
    public function getLatelyFinishedOrder()
    {
        return $this->getFinishedOrders()->sortByDesc('id')->first();
    }

    /**
     * 获取已完结订单
     *
     * @return Order[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getFinishedOrders()
    {
        return $this->getHisOrders(Order::FINISH_STATUS);
    }

    /**
     * 获取用户订单
     *
     * @param array|null $status
     *
     * @return Order[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getHisOrders(?array $status = null)
    {
        $orders = $this->user->orders->sortByDesc('id');

        if (isset($status)) {
            $orders = $orders->whereIn('status', $status);
        }

        return $orders;
    }
}
