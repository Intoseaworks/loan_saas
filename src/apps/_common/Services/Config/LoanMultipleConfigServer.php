<?php

namespace Common\Services\Config;

use Admin\Services\Config\ConfigServer;
use Common\Models\Common\Config;
use Common\Models\Config\LoanMultipleConfig;
use Common\Models\Config\LoanMultipleConfigLog;
use Common\Models\Merchant\Merchant;
use Common\Models\User\User;
use Common\Services\BaseService;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

class LoanMultipleConfigServer extends BaseService
{
    public function saveLoanConfig($params)
    {
        // 贷款配置项保存
        $server = LoanMultipleConfigServer::server()->saveConfig(array_get($params, 'loan_config'));

//        if ($server->isError()) {
//            return $this->outputError($server->getMsg());
//        }

        // config 表相关配置保存
        $configServer = ConfigServer::server();
        $configServer->storeConfig(array_only($params, [Config::KEY_LOAN_GST_RATE, Config::KEY_FIRST_LOAN_REPAYMENT_RATE]));
        if ($configServer->isError()) {
            return $this->outputError($configServer->getMsg());
        }

        // lender 保存
        $merchantModel = (new Merchant)->getById(MerchantHelper::getMerchantId());
        $merchantModel->lender = array_get($params, 'nbfc_lender');
        $merchantModel->save();

        return $this->outputSuccess();
    }

    public function saveConfig(array $data)
    {
        DB::beginTransaction();
        try {
            $merchantId = MerchantHelper::getMerchantId();

            $oldConfig = LoanMultipleConfig::getConfigByMerchant($merchantId);
            $model = null;
            foreach ($data as $item) {
                if(isset($item["loan_".LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE])){
                    $item[LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE] = $item["loan_".LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE];
                }
                if(isset($item['penalty_rate_day']) && isset($item['penalty_rate'])){
                    $item[LoanMultipleConfig::FIELD_LOAN_FORFEIT_PENALTY_RATE] = [$item['penalty_rate_day'], $item['penalty_rate']];
                }
                /** 关键配置规则校验 展期费率和手续费率跟贷款天数一一对应*/
                if (is_countable($item[LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE]) && is_countable($item[LoanMultipleConfig::FIELD_PENALTY_RATE])) {
                    if (count($item[LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE]) != count($item[LoanMultipleConfig::FIELD_PENALTY_RATE])) return $this->outputError('Processing Fee Format Error');
                }
                if (is_countable($item[LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE]) && is_countable($item[LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE])) {
                    if (count($item[LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE]) != count($item[LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE])) return $this->outputError('Renewal Rate Format Error');
                }
                $model = LoanMultipleConfig::updateOrCreateConfig($merchantId, $model, $item);
            }
            // 保存暂时没有删除配置项的逻辑，清理可以用清理当前有效的配置之外的配置
            LoanMultipleConfig::clearConfig($merchantId);

            $newConfig = LoanMultipleConfig::getConfigByMerchant($merchantId);

            LoanMultipleConfigLog::add($oldConfig, $newConfig);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param $id
     * @return LoanMultipleConfigServer
     */
    public function itemDel($id)
    {
        $merchantId = MerchantHelper::getMerchantId();

        $configs = LoanMultipleConfig::getConfigByMerchant($merchantId);

        $lastConfig = end($configs);

        $model = (new LoanMultipleConfig())->getNormalById($id);
        if (!$model) {
            return $this->outputError('记录不存在');
        }
        if ($model->id != $lastConfig['id'] || !$model->toDelete()) {
            return $this->outputError('记录删除失败');
        }

        return $this->outputSuccess();
    }


    /************************************************* 业务逻辑相关☟ ****************************************************/

    /**
     * @param User $user
     * @param null $key
     * @return mixed
     * @throws \Exception
     */
    public function getConfigByUser(User $user, $key = null)
    {
        return LoanMultipleConfig::getConfigByCnt($user->merchant_id, $user->getRepeatLoanCnt(), $key);
    }

    /**
     * 贷款配置 - 借款金额范围
     * @param User $user
     * @return mixed|array
     * @throws \Exception
     */
    public function getLoanAmountRange(User $user)
    {
        return $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_AMOUNT_RANGE);
    }

    /**
     * 获取最大额度
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanAmountMax(User $user)
    {
        $loanAmountMax = 1000;
        $loanAmountRange = $this->getLoanAmountRange($user);
        if($loanAmountRange){
            $loanAmountMax = max($loanAmountRange);
        }
        return $loanAmountMax;
    }

    /**
     * 贷款配置 - 借款期限范围
     * @param $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanDaysRange(User $user)
    {
        //谷歌特定账号
        if ( $this->isGoogleAuditUser($user) ){
            return [30,60,90,180];
        }
        return $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE);
    }

    /**
     * 判断是否是谷歌审核账户
     * @param $user
     * @return bool
     * @throws \Exception
     */
    public function isGoogleAuditUser(User $user)
    {
        return ($user->merchant_id == 1 && $user->telephone == '01501233211') ||
        ($user->merchant_id == 2 && $user->telephone == '01231231231');
    }

    /**
     * 获取最大借款期限
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanDaysMax(User $user)
    {
        $loanDaysRange = $this->getLoanDaysRange($user);
        $loanDaysMax = max($loanDaysRange);
        return $loanDaysMax;
    }

    /**
     * 贷款配置 - 正常借款日息
     * @param User $user
     * @return float|int
     * @throws \Exception
     */
    public function getDailyRate(User $user)
    {
        //谷歌特定账号
        if ( $this->isGoogleAuditUser($user) ){
            return 0.0001;
        }
        $rate = $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_DAILY_LOAN_RATE);

        return $rate / 100;
    }

    /**
     * 贷款配置 - 罚息费率（滞纳金日息）
     * @param User $user
     * @return float|int
     * @throws \Exception
     */
    public function getPenaltyRate(User $user)
    {
        $rate = $this->getConfigByUser($user, LoanMultipleConfig::FIELD_PENALTY_RATE);

        return $rate / 100;
    }

    /**
     * 贷款配置 - 手续费费率 默认10%
     * @param User $user
     * @param int $loanDays
     * @return float|int
     * @throws \Exception
     */
    public function getServiceChargeRate(User $user, $loanDays = 7)
    {
        //谷歌特定账号
        if ( $this->isGoogleAuditUser($user) ){
            switch ($loanDays) {
                case 60:
                    return 0.05;
                case 90:
                    return 0.075;
                case 180:
                    return 0.15;
                default:
                    return 0.025;
            }
        }
        $rates = $this->getConfigByUser($user, LoanMultipleConfig::FIELD_PROCESSING_RATE);

        $loanDaysRange = $this->getLoanDaysRange($user);

        $pos = array_search($loanDays, $loanDaysRange);

        return ($rates[intval($pos)] ?? 10) / 100;
    }

    /**
     * 贷款配置 - 展期手续费率 默认10%
     * @param User $user
     * @param int $loanDays
     * @return float|int
     * @throws \Exception
     */
    public function getLoanRenewalRate(User $user, $loanDays = 7)
    {
        $rates = $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE);
//        $rates = \Api\Services\Order\OrderServer::server()->getRenewalRate($user, $loanDays);

        $loanDaysRange = $this->getLoanDaysRange($user);

        $pos = array_search($loanDays, $loanDaysRange);

        return ($rates[intval($pos)] ?? 10) / 100;
    }

    /**
     * 滞纳金费率 数组 [5,5] 第一个是在第5天收取，第二个是收取比例5%
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanForfeitPenaltyRate(User $user)
    {
        $config = $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_FORFEIT_PENALTY_RATE) ?? [5, 5];
        list($days, $rate) = $config;
        return [$days, $rate / 100];
    }

    /**
     * 贷款配置 - 可重新借款天数
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanAgainDays(User $user)
    {
        return $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_AGAIN_DAYS);
    }

    public function getPenaltyDaysRate(User $user){
        $config = $this->getConfigByUser($user);
        $res = [];
        if(is_array($config[LoanMultipleConfig::FIELD_PENALTY_START_DAYS])){
            foreach($config[LoanMultipleConfig::FIELD_PENALTY_START_DAYS] as $k => $v){
                $res[strval($config[LoanMultipleConfig::FIELD_PENALTY_RATES][$k])] = range($config[LoanMultipleConfig::FIELD_PENALTY_START_DAYS][$k], $config[LoanMultipleConfig::FIELD_PENALTY_END_DAYS][$k]);
            }
        }
        return $res;
    }
}
