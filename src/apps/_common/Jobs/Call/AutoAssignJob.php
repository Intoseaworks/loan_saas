<?php

namespace Common\Jobs\Call;

use Admin\Services\Collection\CollectionCallServer;
use Common\Jobs\Job;
use Common\Models\Collection\CollectionSetting;
use Common\Services\Collection\CollectionServer;
use Common\Utils\MerchantHelper;
use Admin\Models\Order\Order;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class AutoAssignJob extends Job {

    public $queue = 'call-collection';
    public $tries = 2;
    public $orderId;

    public function __construct($orderId) {
        $this->orderId = $orderId;
    }

    public function handle() {
        echo "呼叫成功开始转移案件:{$this->orderId}";
        $order = Order::model()->getOne($this->orderId);
        if (isset($order->collectionUnfinished) && $collection = $order->collectionUnfinished) {
            # 设置品牌
            MerchantHelper::helper()->setMerchantId($collection->merchant_id);
            $rules = CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE);
            $rules = json_decode($rules, true);
            $rule = [
                "overdue_level" => $collection->level,
                "contact_num" => 20,
                "reduction_setting" => "cannot",
            ];
            foreach ($rules as $item) {
                if ($item['overdue_level'] == $collection->level) {
                    $rule = $item;
                    break;
                }
            }
            $toAdminId = CollectionCallServer::server()->getNextAdminId($collection->level);
            if ($toAdminId > 0) {
                CollectionServer::server()->againCollection($collection, $toAdminId, $rule, "外呼成功后转移{$toAdminId}");
                CollectionCallServer::server()->setNextAdminId($collection->level);
                echo " =>>SUCCESS";
                return;
            }
            echo " =>> FAIL";
        }
    }

}
