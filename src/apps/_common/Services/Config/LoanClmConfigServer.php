<?php

namespace Common\Services\Config;

use Admin\Services\Config\ConfigServer;
use Common\Models\Common\Config;
use Common\Models\Config\LoanClmConfig;
use Common\Models\Config\LoanClmConfigLog;
use Common\Models\Merchant\Merchant;
use Common\Models\User\User;
use Common\Services\BaseService;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

class LoanClmConfigServer extends BaseService {

    public function saveLoanConfig($params) {
        // 贷款配置项保存
        LoanClmConfigServer::server()->saveConfig(array_get($params, 'loan_config'));

        return $this->outputSuccess();
    }

    public function saveConfig(array $data) {
        DB::beginTransaction();
        try {
            $merchantId = MerchantHelper::getMerchantId();

            $oldConfig = LoanClmConfig::getConfigByMerchant($merchantId);
            LoanClmConfig::clearConfig($merchantId);
            foreach ($data as $item) {
                LoanClmConfig::updateOrCreateConfig($merchantId, $item);
            }
            // 保存暂时没有删除配置项的逻辑，清理可以用清理当前有效的配置之外的配置
            $newConfig = LoanClmConfig::getConfigByMerchant($merchantId);
            LoanClmConfigLog::add($oldConfig, $newConfig);
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
    public function itemDel($id) {
        $model = (new LoanClmConfig())->getNormalById($id);
        if (!$model) {
            return $this->outputError('记录不存在');
        }
        if (!$model->toDelete()) {
            return $this->outputError('记录删除失败');
        }

        return $this->outputSuccess();
    }

    /*     * *********************************************** 业务逻辑相关☟ *************************************************** */

    /**
     * @param User $user
     * @param null $key
     * @return mixed
     * @throws \Exception
     */
    public function getConfigByUser(User $user, $key = null) {
        return LoanMultipleConfig::getConfigByCnt($user->merchant_id, $user->getRepeatLoanCnt(), $key);
    }

    /**
     * 贷款配置 - 借款金额范围
     * @param User $user
     * @return mixed|array
     * @throws \Exception
     */
    public function getLoanAmountRange(User $user) {
        return $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_AMOUNT_RANGE);
    }

    /**
     * 获取最大额度
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanAmountMax(User $user) {
        $loanAmountRange = $this->getLoanAmountRange($user);
        $loanAmountMax = max($loanAmountRange);
        return $loanAmountMax;
    }

    /**
     * 贷款配置 - 借款期限范围
     * @param $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanDaysRange(User $user) {
        return $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE);
    }

    /**
     * 获取最大借款期限
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanDaysMax(User $user) {
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
    public function getDailyRate(User $user) {
        $rate = $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_DAILY_LOAN_RATE);

        return $rate / 100;
    }

    /**
     * 贷款配置 - 罚息费率（滞纳金日息）
     * @param User $user
     * @return float|int
     * @throws \Exception
     */
    public function getPenaltyRate(User $user) {
        $rate = $this->getConfigByUser($user, LoanMultipleConfig::FIELD_PENALTY_RATE);

        return $rate / 100;
    }

    /**
     * 贷款配置 - 手续费费率
     * @param User $user
     * @return float|int
     * @throws \Exception
     */
    public function getProcessingRate(User $user) {
        $rate = $this->getConfigByUser($user, LoanMultipleConfig::FIELD_PROCESSING_RATE);

        return $rate / 100;
    }

    /**
     * 贷款配置 - 可重新借款天数
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function getLoanAgainDays(User $user) {
        return $this->getConfigByUser($user, LoanMultipleConfig::FIELD_LOAN_AGAIN_DAYS);
    }

}
