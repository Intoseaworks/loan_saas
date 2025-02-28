<?php

namespace Tests\Admin\TradeManage;

use Common\Models\Trade\AdminTradeAccount;
use Tests\Admin\TestBase;

class TradeLogTest extends TestBase
{
    /**
     * 支付记录列表
     */
    public function testTradeLogList()
    {
        $this->json('GET', 'api/trade-manage/trade-log-list')->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();

//        $params = ['export' => 1];
//        $this->json('GET', 'api/trade-manage/trade-log-list', $params);
//        $this->assertResponseOk();
    }

    /**
     * 支付记录列表
     */
    public function testSystemPayList()
    {
        $this->json('GET', 'api/trade-manage/system-pay-list')->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();

        $params = ['export' => 1];
        $this->json('GET', 'api/trade-manage/system-pay-list', $params);
        $this->assertResponseOk();
    }

    /**
     * 账户管理列表
     */
    public function testAccountList()
    {
        $params = [
            'account_no' => 'saas@163.com',
            'type' => AdminTradeAccount::TYPE_OUT,
        ];
        $this->get('api/trade-manage/account-list', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 账户添加
     */
    public function testAccountCreate()
    {
        /** account_no同名拦截测试 */
        $params = [
            'type' => AdminTradeAccount::TYPE_IN,
            'account_no' => 'saastest@163.com',
            'account_name' => '陈*',
            'status' => AdminTradeAccount::STATUS_ACTIVE,
            'payment_method' => '', //AdminTradeAccount::PAYMENT_METHOD_ALIPAY,
        ];
        $this->post('api/trade-manage/account-create', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ])->getData();
    }

    /**
     * 禁用/启用 账户
     */
    public function testAccountDisableOrEnable()
    {
        $params = [];
        $this->post('api/trade-manage/account-disable-or-enable', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ])->getData();
        $params = [
            'id' => 1
        ];
        $this->post('api/trade-manage/account-disable-or-enable', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ])->getData();
        $params = [
            'id' => 3,
            'status' => AdminTradeAccount::STATUS_DELETE,
        ];
        $this->post('api/trade-manage/account-disable-or-enable', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ]);
        $params = [
            'id' => 3,
            'status' => AdminTradeAccount::STATUS_ACTIVE,
        ];
        $this->post('api/trade-manage/account-disable-or-enable', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testAccountDefault()
    {
        $params = [
            'id' => 100,
        ];
        $this->post('api/trade-manage/account-default', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ])->getData();
        $params = [
            'id' => 3,
        ];
        $this->post('api/trade-manage/account-default', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

}
