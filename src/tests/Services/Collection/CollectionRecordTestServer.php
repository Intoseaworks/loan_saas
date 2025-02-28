<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Tests\Services\Collection;

use Common\Models\Collection\CollectionContact;
use Common\Models\Order\Order;
use Tests\Admin\Collection\CollectionRecordTest;
use Tests\Services\BaseService;

class CollectionRecordTestServer extends BaseService
{
    /**
     * 催记全流程
     *
     * @param $userId
     */
    public function collectionRecord($orderId)
    {
        //承诺还款
        $order = Order::model()->getOne($orderId);
        if (!$order) {
            dd('【催记】订单不存在');
        }
        $collection = $order->collection;
        $collectionContacts = $collection->collectionContacts;
        $collectionRecordTest = new CollectionRecordTest();
        $collectionRecordTest->setUp();
        $recordRandCount = rand(2, 5);
        $recordCount = 0;
        foreach ($collectionContacts as $collectionContact) {
            $collectionRecordTestData = [
                'collection_id' => $collection->id,
                'contact_id' => $collectionContact->id,
                'fullname' => $collectionContact->fullname,
                'relation' => $collectionContact->relation,
            ];
            if ($collectionContact->relation == CollectionContact::RELATION_ONESELF) {
                $collectionRecordTestData = array_merge($collectionRecordTestData, [
                    'dial' => '正常联系',
                    'progress' => '承诺还款',
                    'remark' => '还',
                    'promise_paid_time' => \Common\Utils\Data\DateHelper::date(),
                ]);
            } else {
                $collectionRecordTestData = array_merge($collectionRecordTestData, [
                    'dial' => '正常联系',
                    'progress' => '有意告知',
                    'remark' => '好',
                ]);
                $recordCount++;
            }
            $collectionRecordTest->testCreate($collectionRecordTestData);
            if ($recordCount > $recordRandCount) break;
        }
    }
}
