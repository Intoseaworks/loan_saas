<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Tests\Services\Order;

use Admin\Models\Collection\CollectionSetting;
use Admin\Services\Test\TestOrderServer;
use Common\Models\Order\Order;
use Common\Utils\Data\DateHelper;
use Tests\Admin\Collection\CollectionDeductionTest;
use Tests\Admin\Console\ConsoleTest;
use Tests\Services\BaseService;
use Tests\Services\Collection\CollectionRecordTestServer;

class CollectionTestServer extends BaseService
{
    /**
     * 逾期全流程
     *
     * @param $userId
     */
    public function collection($orderId)
    {
        //模拟逾期
        $order = Order::model()->getOne($orderId);
        $overdueDays = 15;
        TestOrderServer::server()->overdue($order->user, $overdueDays);

        //入催
        $consoleTest = new ConsoleTest();
        $consoleTest->setUp();
        $consoleTest->testCollectionAssign();

        $collection = $order->collection;
        //催记
        CollectionRecordTestServer::server()->collectionRecord($orderId);
        //减免
        if (in_array($collection->collectionDetail->reduction_setting, [
            CollectionSetting::REDUCTION_SETTING_OVERDUE_INTEREST,
            CollectionSetting::REDUCTION_SETTING_PRINCIPAL_INTEREST
        ])) {
            $collectionDeductionTest = new CollectionDeductionTest();
            $collectionDeductionTest->setUp();
            $deductionDays = 10;
            $collectionDeductionTestData = [
                'collection_id' => $collection->id,
                'deduction' => 20,
                'deduction_time' => [DateHelper::date(), DateHelper::addDays($deductionDays)],
            ];
            $collectionDeductionTest->testCreate($collectionDeductionTestData);
        }
    }
}
