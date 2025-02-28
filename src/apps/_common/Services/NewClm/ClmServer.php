<?php

namespace Common\Services\NewClm;

use Common\Models\Config\Config;
use Common\Models\NewClm\ClmCustomer;
use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Services\BaseService;
use Common\Services\NewClm\Rule\Rule;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

class ClmServer extends BaseService
{
    /**
     * 获取或初始化客户
     *
     * @param User $user
     *
     * @return ClmCustomer
     * @throws \Exception
     */
    public function getOrInitCustomer(User $user): ClmCustomer
    {
        return ClmCustomerServer::server()->getOrInitCustomer($user);
    }

    /**
     * 获取贷款额度
     *
     * @param User $user
     *
     * @return float
     * @throws \Exception
     */
    public function getAvailableAmount(User $user): float
    {
        return $this->getOrInitCustomer($user)->calcAvailableAmount();
    }

    /**
     * 手续费费率优惠(百分比)
     *
     * @param User $user
     *
     * @return float
     * @throws \Exception
     */
    public function getInterestDiscount(User $user): float
    {
        return $this->getOrInitCustomer($user)->getCurrentLevelAmount()->clm_interest_discount;
    }

    /**
     * 调整等级
     * @param Order $order
     *
     * @return bool
     * @throws \Throwable
     */
    public function adjustLevel(Order $order)
    {
        # 哥大项目不需要升降级
        if($order->merchant_id == \Common\Models\Merchant\Merchant::getId('c1')){
            return false;
        }
        $user = $order->user;
        $customer = $this->getOrInitCustomer($user);

        if (!$customer) {
            throw new \Exception('订单对应客户不存在');
        }

        if(\Common\Models\NewClm\ClmChangeLog::model()->where("applyid", $order->id)->exists()){
            return false;
        }
        // 手动在指定商户ID环境下执行，避免全局商户ID不可控。保证规则获取和规则调整在指定商户下执行
        MerchantHelper::callbackOnce(function () use ($customer, $order) {
            MerchantHelper::setMerchantId($order->merchant_id);

            $rule = new Rule($customer, $order);

            $toLevel = $rule->getAdjustLevel();

            DB::beginTransaction();
            try {
                $customer->finish($toLevel, $order->principal);

                // 记录调整明细日志
                $rule->logStorage();
                // 记录调整日志
                $rule->addChangeLog($toLevel, $order->merchant_id);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw $e;
            }
        });

        return true;
    }

    /**
     *
     * @param ClmCustomer $customer
     * @param type $amountLimit 额度1
     */
    public function isUpdateMaxLevel(ClmCustomer $customer, $amountLimit) {
        # 额度2 = max(select clm_level from clm_amount where clm_amount< 额度1
        # 如果 额度2 < max(在new_clm_customer_ext表中客户各品牌的current_level)，则不更新max_level
        $clmLevel2 = \Common\Models\NewClm\ClmAmount::model()->where("clm_amount", "<=", $amountLimit)->max('clm_level'); #额度2
        if (!$clmLevel2) {
            return true;
        }
        $sql = "SELECT MAX(current_level) as current_level FROM new_clm_customer_ext WHERE clm_customer_id='{$customer->clm_customer_id}'";
        $res = \DB::select($sql);
        if ($res[0]->current_level) {
            return $res[0]->current_level < $clmLevel2;
        }
        return true;
    }

    /**
     * 更新max_level
     * @param User $user
     */
    public function updateMaxLevel(User $user){
        $userInfo = $user->userInfo;
        $userWork = $user->userWork;
        //额度配置取值
        MerchantHelper::setMerchantId($user->merchant_id);
        $monthConfig = Config::getValueByKey(Config::KEY_CLM_MIN_MAX_RATION);
        //默认最低额度
        $monthIncome = $monthConfig['monthIncomeMin'];
        //默认系数
        $ratio = $monthConfig['ratio'];
        if ($userInfo && $userInfo->confirm_month_income && is_numeric($userInfo->confirm_month_income)) {
            //原来是在放款时更新new_clm_customer.total_amount=月收入，现在需要改为total_amount=月收入*1.2,20201014,改回20211124
//            $userInfo->confirm_month_income = 1.2*$userInfo->confirm_month_income;
            $monthIncome = max([$monthIncome, $userInfo->confirm_month_income]);
        }else{
            $salaryMap = [
                "MONEY1" => 191000,
                "MONEY2" => 191000,
                "MONEY3" => 223000,
                "MONEY4" => 318000,
                "MONEY5" => 430000,
                "MONEY6" => 558000,
                "MONEY7" => 797000
            ];
            if(isset($userWork->salary) && is_numeric(str_replace(",","",$userWork->salary))){
                //原来是在放款时更新new_clm_customer.total_amount=月收入，现在需要改为total_amount=月收入*1.2,20201014,改回20211124
                $userWork->salary = str_replace(",","",$userWork->salary);
                $monthIncome = max([$monthIncome, $userWork->salary]);
            }
            if($userWork && isset($userWork->salary) && isset($salaryMap[$userWork->salary])){
//                $monthIncome = $salaryMap[$userWork->salary];
                //原来是在放款时更新new_clm_customer.total_amount=月收入，现在需要改为total_amount=月收入*1.2,20201014,改回20211124
//                $monthIncome = $monthIncome;
                $monthIncome = max([$monthIncome, $salaryMap[$userWork->salary]]);
            }
        }
        $monthIncome = $ratio*$monthIncome;
        $customer = \Common\Services\NewClm\ClmServer::server()->getOrInitCustomer($user);
        //最高等级现在为50000
        $monthIncomeMax = $monthConfig['monthIncomeMax'];
        //最高clmamount
        $clmAmountMax = \Common\Models\NewClm\ClmAmount::getMaxAmount();
        if ($this->isUpdateMaxLevel($customer, round($monthIncome))) {
            $clmAmount = \Common\Models\NewClm\ClmAmount::getMaxLevelByAmount(round($monthIncome));
            //个人额度超过最大值
            if ($monthIncomeMax <= $monthIncome){
                $customer->max_level = 99;
                $customer->total_amount = $monthIncomeMax;
            }else{
                //不超过最大值但超过clmamoutMax
                if ( $clmAmountMax <= $monthIncome ){
                    $customer->max_level = 99;
                    $customer->total_amount = $monthIncome;
                }else{
                    $customer->max_level = $clmAmount->clm_level;
                    $customer->total_amount = $clmAmount->clm_amount;
                }
            }
            $customer->save();
        }
    }
}
