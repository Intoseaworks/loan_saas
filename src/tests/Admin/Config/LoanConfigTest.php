<?php

namespace Tests\Admin\Approve;

use Common\Models\User\User;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Utils\MerchantHelper;
use Tests\Admin\TestBase;

class LoanConfigTest extends TestBase
{
    public function testT()
    {
        MerchantHelper::setMerchantId(1);
        $merchantId = MerchantHelper::getMerchantId();

//        $res = LoanMultipleConfig::getConfigByCnt($merchantId, 11, LoanMultipleConfig::FIELD_LOAN_AMOUNT_RANGE);
//        dd($res);

        $user = User::query()->find(156);
        $res1 = LoanMultipleConfigServer::server()->getLoanAmountRange($user);
        $res2 = LoanMultipleConfigServer::server()->getLoanAmountMax($user);
        $res3 = LoanMultipleConfigServer::server()->getLoanDaysRange($user);
        $res4 = LoanMultipleConfigServer::server()->getLoanDaysMax($user);
        $res5 = LoanMultipleConfigServer::server()->getDailyRate($user);
        $res6 = LoanMultipleConfigServer::server()->getPenaltyRate($user);
        $res7 = LoanMultipleConfigServer::server()->getServiceChargeRate($user, 7);
        $res9 = LoanMultipleConfigServer::server()->getLoanRenewalRate($user, 7);
        $res10 = LoanMultipleConfigServer::server()->getLoanForfeitPenaltyRate($user);
        $res8 = LoanMultipleConfigServer::server()->getLoanAgainDays($user);

        dd($res1, $res2, $res3, $res4, $res5, $res6, $res7, $res8);
    }

    public function testSaveConfig()
    {
        MerchantHelper::setMerchantId(1);
        $server = LoanMultipleConfigServer::server();

        $data = [
            ['number_end' => '0', 'merchant_id' => 15, 'loan_amount_range' => [2000, 3000], 'loan_days_range' => [14, 30], 'loan_daily_loan_rate' => '0.1', 'penalty_rate' => '0.5', 'processing_rate' => '10', 'loan_again_days' => 30],
            ['number_end' => '4', 'loan_amount_range' => [1000, 2000, 3000], 'loan_days_range' => [20, 30], 'loan_daily_loan_rate' => '0.09', 'penalty_rate' => '0.49', 'processing_rate' => '9', 'loan_again_days' => 15],
            ['number_end' => '5', 'merchant_id' => 15, 'loan_amount_range' => [3000], 'loan_days_range' => [25, 30], 'loan_daily_loan_rate' => '0.08', 'penalty_rate' => '0.48', 'processing_rate' => '8', 'loan_again_days' => 14],
            ['number_end' => '9', 'merchant_id' => 15, 'loan_amount_range' => [3001], 'loan_days_range' => [31], 'loan_daily_loan_rate' => '0.07', 'penalty_rate' => '0.47', 'processing_rate' => '7.9', 'loan_again_days' => 15],
        ];

        $res = $server->saveConfig($data);
        dd($res);
    }

    /**
     * 查看贷款设置
     */
    public function testView()
    {
        $params = [
        ];
        $this->get('/api/loan-config/view', $params)
            ->seeJson(['code' => 18000])
            ->getData();
    }

    /**
     * 保存贷款设置
     */
    public function testSave()
    {
        $loanConfigData = [
            ['number_end' => '0', 'loan_amount_range' => [2000, 3000], 'loan_days_range' => [14, 30], 'loan_daily_loan_rate' => '0.1', 'penalty_rate' => '0.5', 'processing_rate' => '10', 'loan_again_days' => 30],
            ['number_end' => '4', 'loan_amount_range' => [1000, 2000, 3000], 'loan_days_range' => [20, 30], 'loan_daily_loan_rate' => '0.09', 'penalty_rate' => '0.49', 'processing_rate' => '9', 'loan_again_days' => 15],
            ['number_end' => '7', 'loan_amount_range' => [3000], 'loan_days_range' => [25, 30], 'loan_daily_loan_rate' => '0.08', 'penalty_rate' => '0.48', 'processing_rate' => '8', 'loan_again_days' => 14],
            ['number_end' => '9', 'loan_amount_range' => [4000], 'loan_days_range' => [30, 40], 'loan_daily_loan_rate' => '0.1', 'penalty_rate' => '0.5', 'processing_rate' => '8', 'loan_again_days' => 14],
        ];

        $params = [
            'loan_config' => $loanConfigData,
            'first_loan_repayment_rate' => 99.98,
            'loan_gst_rate' => 18,
            'nbfc_lender' => 'STAR FINSERV INDIA LIMITED-test',
        ];
        $this->post('/api/loan-config/save', $params)
            ->seeJson(['code' => 18000])
            ->getData();
    }

    /**
     * 删除贷款设置配置项
     */
    public function testItemDel()
    {
        $params = [
            'id' => 12,
        ];
        $this->post('/api/loan-config/item-del', $params)
            ->seeJson(['code' => 18000])
            ->getData();
    }
}
