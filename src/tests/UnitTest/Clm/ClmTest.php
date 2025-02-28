<?php

namespace Tests\UnitTest\Clm;

use Common\Models\NewClm\ClmAmount;
use Common\Models\NewClm\ClmInitLevel;
use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Services\NewClm\ClmConfigServer;
use Common\Services\NewClm\ClmServer;
use Common\Services\NewClm\Rule\Rule;
use Tests\TestCase;

class ClmTest extends TestCase
{
    /**
     * 获取客户
     *
     * @throws \Exception
     */
    public function testGetOrInitCustomer()
    {
        $userId = 68532; // 资料全，订单全

//        $userId = 7228; // 同手机号，姓名 商户1
//        $userId = 43777; // 同手机号，姓名 商户2

        $user = User::query()->where('id', $userId)->first();

        $customer = ClmServer::server()->getOrInitCustomer($user);

        dd($customer);
    }

    /**
     * 获取可用额度
     *
     * @throws \Exception
     */
    public function testGetAvailableAmount()
    {
        $userId = 68532; // 资料全，订单全
        $user = User::query()->where('id', $userId)->first();

        $res = ClmServer::server()->getAvailableAmount($user);
        dd($res);
    }

    /**
     * 获取手续费费率优惠(百分比)
     *
     * @throws \Exception
     */
    public function testGetInterestDiscount()
    {
        $userId = 68532; // 资料全，订单全
        $user = User::query()->where('id', $userId)->first();

        $res = ClmServer::server()->getInterestDiscount($user);
        dd($res);
    }

    /**
     * 状态流转流程
     * 拒绝|取消 流程
     *
     * @throws \Throwable
     */
    public function testRejectFlow()
    {
        $userId = 68532; // 资料全，订单全

        $user = User::query()->where('id', $userId)->first();

        $customer = ClmServer::server()->getOrInitCustomer($user);

        $amount = 3000;

        // 冻结
        $res = $customer->freeze($amount);

        $this->assertTrue($res);
        $this->assertTrue(ClmServer::server()->getOrInitCustomer($user)->isFrozen());

        // 解冻
        $res = $customer->unfreeze($amount);

        $this->assertTrue($res);
        $this->assertTrue(ClmServer::server()->getOrInitCustomer($user)->isNormal());
    }

    /**
     * 状态流转流程
     * 完结订单 流程
     */
    public function testLoanFlow()
    {
        $user = User::query()->where('id', 68532)->first();

        /** @var Order $order */
        $order = $user->orders->whereIn('status', Order::FINISH_STATUS)->sortByDesc('id')->first();

        if (!$order) {
            throw new \Exception('已完结订单不存在');
        }

        $customer = ClmServer::server()->getOrInitCustomer($user);

        $amount = $order->principal;

        // 签约 冻结
        $res = $customer->freeze($amount);

        $this->assertTrue($res);
        $this->assertTrue(ClmServer::server()->getOrInitCustomer($user)->isFrozen());

        // 放款 使用额度
        $res = $customer->use($amount);

        $this->assertTrue($res);
        $this->assertTrue(ClmServer::server()->getOrInitCustomer($user)->isNormal());

        // 完结订单 调整等级
        $res = ClmServer::server()->adjustLevel($order);

        $this->assertTrue($res);
        $this->assertTrue(ClmServer::server()->getOrInitCustomer($user)->isNormal());
    }

    /**
     * 获取调整等级(核心调整方法)
     *
     * @throws \Exception
     */
    public function testAdjustLevel()
    {
        // 68567
        // 68538
        // 68572
        $order = Order::query()->where('user_id', 68532)->latest()->firstOrFail();

        $order = Order::find(46645);

        $customer = ClmServer::server()->getOrInitCustomer($order->user);

        $rule = new Rule($customer, $order);

        $level = $rule->getAdjustLevel();

        dd($level, $rule->getLog());
    }

    /**
     * 初始化
     * clm规则表 & clm等级金额表
     *
     * @throws \Throwable
     */
    public function testInit()
    {
        $merchantId = 1;

        // 初始化规则
        $initRuleRes = ClmConfigServer::server()->initRule();
        // 初始化等级对应额度
        $initAmountRes = ClmConfigServer::server()->initAmount();
        // 初始化等级
        $initLevelRes = ClmConfigServer::server()->initLevel($merchantId);

        dd($initRuleRes, $initAmountRes, $initLevelRes);
    }

    /**********************************************************************************************
     * 接口☟
     *********************************************************************************************/

    /**
     * 获取等级金额配置
     */
    public function testGetClmAmountConfig()
    {
        $params = [];

        $this->json('GET', '/api/clm/clm-amount-config', $params)->seeJson()->getData();
    }

    /**
     * 新增等级金额配置
     */
    public function testAddClmAmountConfig()
    {
        $params = [
            'level' => -4,
            'clm_amount' => 800,
            'clm_interest_discount' => 50,
            'alias' => 'ssssss',
        ];

        $this->json('POST', '/api/clm/add-clm-amount-config', $params)->seeJson()->getData();
    }

    /**
     * 编辑等级金额配置
     */
    public function testEditClmAmountConfig()
    {
        $clmAmount = ClmAmount::query()->orderByDesc('id')->first();

        $params = [
            'id' => $clmAmount->id,
            'level' => $clmAmount->clm_level,
            'clm_amount' => 2050,
            'clm_interest_discount' => 51,
            'alias' => 'qwe',
        ];

        $this->json('POST', '/api/clm/edit-clm-amount-config', $params)->seeJson()->getData();
    }

    /**
     * 删除等级金额配置
     */
    public function testDelClmAmountConfig()
    {
        $clmAmount = ClmAmount::query()->orderByDesc('id')->first();

        $params = [
            'id' => $clmAmount->id,
        ];

        $this->json('POST', '/api/clm/del-clm-amount-config', $params)->seeJson()->getData();
    }

    /**
     * 获取初始化等级配置
     */
    public function testGetInitLevelConfig()
    {
        $params = [];

        $this->json('GET', '/api/clm/init-level-config', $params)->seeJson()->getData();
    }

    /**
     * 修改初始化等级配置
     */
    public function testEdieInitLevelConfig()
    {
        $clmInitLevel = ClmInitLevel::query()->orderByDesc('id')->first();

        $params = [
            'id' => $clmInitLevel->id,
            'level' => 1,
        ];

        $this->json('POST', '/api/clm/edit-init-level-config', $params)->seeJson()->getData();
    }
}
