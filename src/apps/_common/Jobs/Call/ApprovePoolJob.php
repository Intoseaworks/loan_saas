<?php

namespace Common\Jobs\Call;

use Admin\Services\Collection\CollectionCallServer;
use Common\Jobs\Job;
use Common\Models\Approve\ApprovePool;
use Common\Models\Collection\Collection;
use Common\Utils\CallCenter\Freeswitchesl;
use Common\Utils\MerchantHelper;
use Exception;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class ApprovePoolJob extends Job {

    public $queue = 'call-approve-pool';
    public $tries = 2;
    public $merchantId;
    public $eventData;

    public function __construct($merchantId, $eventData = "") {
        $this->merchantId = $merchantId;
        $this->eventData = $eventData;
    }

    public function handle() {
        $startNew = false;
        if ($this->eventData) {
            $this->eventData = json_decode($this->eventData, true);

            $uuid = array_get($this->eventData, "Caller-Unique-ID");
            if ($uuid) {
                $log = \Common\Models\Call\CallLog::model()->where("uuid", $uuid)->first();
                if ($log && $log->order_id) {
                    $startNew = true;
                    $updateCollection = ApprovePool::model()->where("order_id", $log->order_id)
                        ->where("order_status", 4)->orderByDesc("id")->first();
                    if ($updateCollection) {
                        if (in_array(array_get($this->eventData, "Caller-Caller-ID-Name"), ["Outbound%20Call","Inbound%20Call"])) {
                            $updateCollection->call_test_status = 1;
                        } else {
                            $updateCollection->call_test_status = 2;
                        }
                        $updateCollection->save();
                    }
                }
            }
        }
        /** 时间判断自动外呼时间
          每天：8:00-21:00 */
        if (!$this->checkTime()) {
            return;
        }
        if ($startNew || !$this->eventData) {
            $map = array_flip(Freeswitchesl::TEST_CALL_MERCHANT_MAP_APPROVE_POOL);
            MerchantHelper::setMerchantId($this->merchantId);
            $collection = ApprovePool::model()->where("order_status", 4)->where("call_test_status", 0)->orderByDesc("created_at")->first();
            if ($collection) {
                echo "MerchantID:{$this->merchantId} Order:{$collection->order_id} telephone:{$collection->order->user->telephone} ";
                $res = CollectionCallServer::server()->call(["telephone" => $collection->order->user->telephone, "order_id" => $collection->order_id],
                        $map[$this->merchantId], '1001' . $map[$this->merchantId],1);
                if ($res->isSuccess()) {
                    echo "Successful";
                    $collection->call_test_status = 3;
                    $collection->save();
                } else {
                    echo "Unsuccessful";
                }
            }
        }
    }

    private function checkTime() {
        $hour = ["08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21"];
        $currentHour = date("H");
        return in_array($currentHour, $hour);
    }

}
