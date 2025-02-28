<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Models\Collection\Collection;
use Admin\Services\Staff\StaffServer;
use Common\Console\Models\Collection\CollectionAssign;
use Common\Models\Collection\CollectionLog;
use Common\Services\BaseService;
use Common\Utils\LoginHelper;

class CollectionAssignServer extends BaseService
{
    public function assignToCollector($params)
    {
        $collectionIds = array_get($params, 'collection_ids');
        $adminIds = array_get($params, 'admin_ids');
        $collectionIdsCount = count($collectionIds);
        $adminIdsCount = count($adminIds);
        $adminList = StaffServer::server()->getByIds($adminIds);
        if($adminIdsCount != count($adminList)){
            return $this->outputException('部分催收员异常');
        }
        $collectionList = CollectionServer::server()->getByIds($collectionIds, Collection::STATUS_NOT_COMPLETE);
        if($collectionIdsCount != count($collectionList)){
            return $this->outputException('部分案件非进行中');
        }
        $collectionAdminIds = $collectionList->pluck('admin_id')->toArray();
        if(array_intersect($adminIds, $collectionAdminIds)){
            //return $this->outputException('部分案件已归属被选择催收员');
        }
        foreach ($collectionList as $key => $needUpdateCollection) {
            $adminId = $adminIds[$key % $adminIdsCount];
            if($needUpdateCollection->admin_id == $adminId){
                continue;
            }
            \DB::transaction(function () use ($needUpdateCollection, $adminId) {
                $fromAdminId = $needUpdateCollection->admin_id;
                $fromStatus = $needUpdateCollection->status;
                $fromLevel = $needUpdateCollection->level;
                $collectionData = [
                    'admin_id' => $adminId,
                ];
                $collection = Collection::model()->assignAgain($needUpdateCollection, $collectionData);
                $collectionAssignData = [
                    'order_id' => $needUpdateCollection->order_id,
                    'collection_id' => $needUpdateCollection->id,
                    'from_admin_id' => $fromAdminId,
                    'admin_id' => $adminId,
                    'from_status' => $fromStatus,
                    'merchant_id' => $needUpdateCollection->merchant_id,
                    'parent_id' => LoginHelper::getAdminId(),
                    'level' => $collection->level,
                    'from_level' => $fromLevel,
                    'overdue_days' => $collection->order->getOverdueDays(),
                ];
                CollectionAssign::model()->assignAgain($collectionAssignData);
                (new CollectionLog())->addLog($collection, $fromStatus, Collection::STATUS_WAIT_COLLECTION);
                return $collection;
            });
        }

    }

}
