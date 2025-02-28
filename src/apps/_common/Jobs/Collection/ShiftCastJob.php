<?php

namespace Common\Jobs\Collection;

use Admin\Services\Collection\CollectionServer;
use Common\Jobs\Job;
use Common\Console\Models\Collection\Collection;
use Common\Models\Collection\CollectionSetting;
use Common\Utils\MerchantHelper;

class ShiftCastJob extends Job {

    public $queue = 'move-case-210804-5';
    public $tries = 0;
    public $fromAdminId;
    public $toAdminId;
    public $caseCount;

    public function __construct($fromAdminId, $toAdminId, $count) {
        $this->fromAdminId = $fromAdminId;
        $this->toAdminId = $toAdminId;
        $this->caseCount = $count;
    }

    public function handle() {
        $query = Collection::model()->newQuery()
                        ->where("admin_id", $this->fromAdminId)
                        ->whereIn("status", Collection::STATUS_NOT_COMPLETE)->limit($this->caseCount)->get();
        $success = 0;
        $total = count($query);
        echo "Start from {$this->fromAdminId} to {$this->toAdminId} total {$total}/{$this->caseCount}" . PHP_EOL;
        foreach ($query as $collection) {
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
            CollectionServer::server()->againCollection($collection, $this->toAdminId, $rule);
            $success++;
            echo "{$success}/{$total}" . PHP_EOL;
        }
    }

}
