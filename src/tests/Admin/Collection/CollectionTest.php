<?php

namespace Tests\Admin\Collection;

use Admin\Models\Order\RepaymentPlan;
use Carbon\Carbon;
use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class CollectionTest extends TestBase
{
    /**
     * 我的催收订单列表
     */
    public function testList()
    {
        $params = [
            'keyword' => '程旭升',
            'sort_promise_paid_time' => 'acs',
            'sort' => 'bad_time',
        ];
        $this->json('GET', '/api/collection/my_order_index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    /**
     * 我的催收订单详情
     */
    public function testView()
    {
        $params = [
            'id' => 1,
        ];
        $this->json('GET', '/api/collection/my_order_view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    /**
     * 催收订单列表
     */
    public function testOrderList()
    {
        $params = [
            'overdue_days' => [0, 1],
            'status' => 'wait_collection',
            'assign_time' => ['2020-03-16 00:00:00', '2020-03-16 23:59:59']
        ];
        $this->json('GET', '/api/collection/order_index', $params)->getData()
            ->seeJson(['code' => self::SUCCESS_CODE]);
        die;

        $params['export'] = 1;
        $this->json('GET', '/api/collection/order_index', $params);
        $this->assertResponseOk();
    }

    /**
     * 催收订单详情
     */
    public function testOrderView()
    {
        $params = [
            'id' => 1,
        ];
        $this->json('GET', '/api/collection/order_view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    public function testSetOrderPartRepayOn()
    {
        $params = [
            'id' => 102,
            'on' => RepaymentPlan::CANNOT_PART_REPAY,
            //'on' => RepaymentPlan::CAN_PART_REPAY,
        ];
        $this->json('POST', '/api/collection/set-order-part-repay-on', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    public function testRenewalCalc()
    {
        $params = [
            'id' => 21294,
            'date' => Carbon::now()->toDateString(),
        ];
        $this->json('get', '/api/collection/renewal_calc', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])->getData(true, true);
    }
}
