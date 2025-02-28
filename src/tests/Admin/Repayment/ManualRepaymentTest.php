<?php

namespace Tests\Admin\Repayment;

use Carbon\Carbon;
use Common\Models\Collection\Collection;
use Tests\Admin\TestBase;

class ManualRepaymentTest extends TestBase
{
    /**
     * 人工还款列表
     */
    public function testIndex()
    {
        $params = [
            'sort' => 'reduction_fee',
        ];
        $this->get('/api/manual-repayment/index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/manual-repayment/index', $params);
        $this->assertResponseOk();
    }

    /**
     * 人工出款详情
     */
    public function testDetail()
    {
        $params = [
            'id' => 153,
        ];
        $this->get('/api/manual-repayment/detail', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 动态计算逾期费用
     */
    public function testCalcOverdue()
    {
        $params = [
//            'id' => 1021,
            'id' => 541,
            'repay_time' => date('Y-m-d', strtotime('-0 days')),
            'repay_amount' => 3000,
            'is_part' => 0,
        ];
        $this->get('/api/manual-repayment/calc-overdue', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData(true);
    }

    /**
     * 收款账户列表
     */
    public function testAdminTradeRepayAccountList()
    {
        $this->get('/api/manual-repayment/admin-trade-repay-account-list')
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 提交人工还款
     */
    public function testRepaySubmit($params = [])
    {
        if (!$params) {
            //$account = current(AccountServer::server()->getRepayAccount());
            $params = [
                'id' => 1021,
                //'trade_account_id' => $account['id'],
                'remark' => 'Successful manual repayment:P2001081750019559111162',
                'repay_name' => 'Alok Panday',
                'repay_telephone' => '8982966654',
                'repay_account' => '8982966654',
                'repay_time' => date('Y-m-d H:i:s', strtotime('-0 days')),
                'repay_amount' => 5900,
                'is_part' => 0,
            ];
        }
        $this->post('/api/manual-repayment/repay-submit', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 添加催收记录
     */
    public function testCollectionSubmit()
    {
        $dial = array_random(Collection::DIAL_SELF);
        $progress = array_random(Collection::PROGRESS_SELF[$dial]);
        $params = [
            'order_id' => '124',
            'dial' => $dial,
            'progress' => $progress,
            'remark' => 'test remark' . mt_rand(10, 100),
        ];
        in_array($progress, ['承诺还款', '有意帮还']) && $params['promise_paid_time'] = Carbon::now()->addDay(rand(1, 10))->toDateString();

        $this->post('/api/manual-repayment/collection_submit', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 催收记录列表
     */
    public function testCollectionRecordList()
    {
        $params = [
            'order_id' => '124',
            'size' => '5',
        ];

        $this->get('/api/manual-repayment/collection_record_list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }
}
