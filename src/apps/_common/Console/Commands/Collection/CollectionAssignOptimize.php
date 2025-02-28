<?php

namespace Common\Console\Commands\Collection;

use Common\Console\Models\Collection\Collection;
use Common\Models\Collection\CollectionAdmin;
use Common\Models\Collection\CollectionSetting;
use Common\Services\Collection\CollectionServer;
use Common\Utils\MerchantHelper;
use DB;
use Illuminate\Console\Command;

class CollectionAssignOptimize extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:assign-optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '催收分单优化(2日未下催记将分走)';

    public function handle() {
        MerchantHelper::callback(function () {

            echo "开始处理小于30天逾期2日未处理订单" . PHP_EOL;
            $this->relocation(2, "<=30");
            if (date("w") == "1") {
                echo "开始处理30天逾期7日未处理订单" . PHP_EOL;
                $this->relocation(7, ">30");
            }
        });
    }

    public function relocation($reDays, $overdueDays) {
        $merchantId = MerchantHelper::helper()->getMerchantId();
        $merchantId . PHP_EOL;
        //$reDays = self::REL_DAYS;
        $sql = "select collection.*,overdue_days from collection
inner JOIN repayment_plan ON collection.order_id=repayment_plan.order_id AND installment_num=1 AND repayment_plan.overdue_days{$overdueDays}
where collection.`status`='wait_collection' AND collection.merchant_id='{$merchantId}' having DATEDIFF(now(),collection.assign_time)>={$reDays} ORDER BY collection.assign_time DESC";
        $res = DB::select($sql);
        foreach ($res as $item) {
            $collectionModel = Collection::model()->getOne($item->id);
            if (in_array($collectionModel->admin_id, [407, 540, 542, 406, 574])) {
                continue;
            }
            $rule = $this->getRule($collectionModel->level);
            $adminId = $this->getAdminId($collectionModel->level, $collectionModel->admin_id);
            if ($rule && $adminId) {
                $collection = CollectionServer::server()->againCollection($collectionModel, $adminId, $rule, '优化再分配');
                echo "订单{$collectionModel->order_id}分配成功" . PHP_EOL;
            } else {
                echo "订单{$collectionModel->order_id}[cid{$collectionModel->id}]未能重分配" . PHP_EOL;
//                var_dump($rule);
//                var_dump($adminId);
            }
        }
    }

    public function getRule($levelName) {
        $ruleData = json_decode(CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE), true);
        foreach ($ruleData as $rule) {
            foreach ($ruleData as $rule) {
//            echo $rule['overdue_level'].PHP_EOL;
                if ($rule['overdue_level'] == $levelName) {
                    return $rule;
                }
            }
        }
        return 0;
    }

    private function getAdminId($levelName, $adminId) {
        $mid = MerchantHelper::helper()->getMerchantId();
        $admins = CollectionAdmin::model()
                ->where("collection_admin.merchant_id", "=", $mid)
                ->where("level_name", "=", $levelName)
                ->where('collection_admin.status', '=', CollectionAdmin::STATUS_NORMAL)
                ->where('collection_admin.weight', '>', 0)
                ->where("admin_id", "<>", $adminId);
        $admins->join("staff", function($join) {
            $join->on("staff.id", "=", "collection_admin.admin_id");
            $join->where("staff.status", '=', '1');
        });
        $admins->select("collection_admin.*"); //->get()->toArray();
        $adminIds = $admins->get()->toArray();
        if ($adminIds && $admin = $adminIds[array_rand($adminIds)]) {
            return $admin['admin_id'];
        }
        return 0;
    }

}
