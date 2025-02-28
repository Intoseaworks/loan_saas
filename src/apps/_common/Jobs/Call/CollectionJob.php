<?php

namespace Common\Jobs\Call;

use Admin\Services\Collection\CollectionCallServer;
use Common\Jobs\Job;
use Common\Models\Collection\Collection;
use Common\Utils\CallCenter\Freeswitchesl;
use Common\Utils\MerchantHelper;
use Exception;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class CollectionJob extends Job {

    public $queue = 'call-collection';
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
            $isAnswer = 0;
            $this->eventData = json_decode($this->eventData, true);
            $variableBillsec = array_get($this->eventData, "variable_billsec", 0);
            $variablePlaybackSeconds = array_get($this->eventData, "variable_playback_seconds", 0);
            $variableSipTermStatus = array_get($this->eventData, 'variable_sip_term_status', '');
            $callerName = array_get($this->eventData, "Caller-Caller-ID-Name");
            $callerNum = array_get($this->eventData, "Caller-Caller-ID-Number");

            if ($variableSipTermStatus == "200" && ($variableBillsec - $variablePlaybackSeconds) > 0 && $variableBillsec > 21 && $callerName != $callerNum) {
                $isAnswer = 1;
            }
            $uuid = array_get($this->eventData, "Caller-Unique-ID");
            echo "Processing data:[{$uuid}]" . PHP_EOL;
            if ($uuid) {
                $log = \Common\Models\Call\CallLog::model()->where("uuid", $uuid)->first();
                if ($log && $log->order_id) {
                    echo "Run:orderID-{$log->order_id}" . PHP_EOL;
                    $startNew = true;
                    $updateCollection = Collection::model()->where("order_id", $log->order_id)->orderByDesc("id")->first();
                    if ($updateCollection) {
                        if ($isAnswer) {
                            $updateCollection->call_test_status = 1;
                            dispatch(new\Common\Jobs\Call\AutoAssignJob($log->order_id));
                        } else {
                            $updateCollection->call_test_status = 2;
                        }
                        $updateCollection->save();
                        echo "Saved" . PHP_EOL;
                    } else {
                        echo "NO Saved" . PHP_EOL;
                    }
                }
            }
        }
        /** 时间判断自动外呼时间
          每天早上：5:00-8:00
          每天晚上：19:00-21:00 */
        if (!$this->checkTime()) {
            return;
        }
        if ($startNew || !$this->eventData) {
            $map = array_flip(Freeswitchesl::TEST_CALL_MERCHANT_MAP);
            MerchantHelper::setMerchantId($this->merchantId);
            $collection = Collection::model()->where("status", "<>", Collection::STATUS_COLLECTION_SUCCESS)->where("call_test_status", "0")->orderByDesc("created_at")->first();
            if ($collection && isset($map[$this->merchantId])) {
                echo "MerchantID:{$this->merchantId} Order:{$collection->order_id} telephone:{$collection->user->telephone} ";
                $res = CollectionCallServer::server()->call(["telephone" => $collection->user->telephone, "order_id" => $collection->order_id],
                        $map[$this->merchantId], '1000' . $map[$this->merchantId]);
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
