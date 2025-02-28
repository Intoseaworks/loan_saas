<?php

namespace Common\Console\Services\Collection;

use Admin\Models\Staff\Staff;
use Common\Models\Collection\CollectionSetting;
use Common\Models\Collection\CollectionRecord;
use Common\Services\BaseService;
use Common\Services\Collection\CollectionServer;
use Common\Services\Third\YixiuyunServer;
use Common\Utils\Data\DateHelper;
use Common\Services\Third\FeixiangServer;
use Common\Models\Collection\CollectionAdmin;
use Common\Models\Collection\Collection;
use Common\Utils\MerchantHelper;

class CollectionAssignServer extends BaseService {
    /* public function assignNewOrder()
      {
      try {
      $needCollectionOrders = Order::model()->needCollectionOrders();
      $ruleData = json_decode(CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE), true);
      if (!isset($ruleData[0])) {
      return $this->outputError('催收新案未配置催收员');
      }
      $rule = $ruleData[0];
      $adminIds = array_get($rule, 'admin_ids');
      $adminIds = Staff::model()->getNormalIdsByAdminIds($adminIds);
      $adminCount = count($adminIds);
      if ($adminCount == 0) {
      return $this->outputError('未设置催收员或选择催收员账号异常');
      }

      foreach ($needCollectionOrders as $key => $order) {
      $adminId = $adminIds[$key % $adminCount];
      $collection = \DB::transaction(function () use ($order, $adminId, $rule) {
      //创建 collection & collection_detail
      $collection = CollectionServer::server()->createCollection($order, $adminId, $rule);
      return $collection;
      });
      CollectionStatisticsRedis::redis()->hIncr($collection->admin_id, CollectionStatisticsRedis::FIELD_STAFF_ALLOT_ORDER, $order->merchant_id);
      }
      return $this->outputSuccess('催收新案分配成功');
      } catch (\Exception $e) {
      return $this->outputError('催收新案分配异常:' . $e->getMessage());
      }

      } */

    /**
     *
     * @return CollectionAssignServer
     * @suppress PhanTypeMismatchArgument
     */
    /* public function assignAgain()
      {
      try {
      $ruleData = json_decode(CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE), true);
      $lastOverdueDays = 0;
      if (!$ruleData) {
      return $this->outputError('未设置催收案件分配规则');
      }
      foreach ($ruleData as $rule) {
      $adminIds = array_get($rule, 'admin_ids');
      $adminIds = Staff::model()->getNormalIdsByAdminIds($adminIds);
      $adminCount = count($adminIds);
      if ($adminCount == 0) {
      return $this->outputError('未设置催收员或选择催收员账号异常');
      }
      $level = array_get($rule, 'overdue_level');
      $overdueDays = array_get($rule, 'overdue_days');
      $needUpdateCollections = Collection::model()->needUpdateCollections($lastOverdueDays, $overdueDays, $level);
      foreach ($needUpdateCollections as $key => $needUpdateCollection) {
      $adminId = $adminIds[$key % $adminCount];
      $collection = \DB::transaction(function () use ($needUpdateCollection, $adminId, $rule) {
      $fromAdminId = $needUpdateCollection->admin_id;
      $fromStatus = $needUpdateCollection->status;
      $fromLevel = $needUpdateCollection->level;
      $level = array_get($rule, 'overdue_level');
      $contactNum = array_get($rule, 'contact_num');
      $reductionSetting = array_get($rule, 'reduction_setting');
      $collectionData = [
      'admin_id' => $adminId,
      'level' => $level,
      ];
      //@phan-suppress-next-line PhanTypeMismatchArgument
      $collection = Collection::model()->assignAgain($needUpdateCollection, $collectionData);
      $collectionDetailData = [
      'contact_num' => $contactNum,
      'reduction_setting' => $reductionSetting,
      ];
      CollectionDetail::model()->assignAgain($needUpdateCollection->collectionDetail, $collectionDetailData);
      $collectionAssignData = [
      'order_id' => $needUpdateCollection->order_id,
      'collection_id' => $needUpdateCollection->id,
      'from_admin_id' => $fromAdminId,
      'admin_id' => $adminId,
      'from_status' => $fromStatus,
      'merchant_id' => $needUpdateCollection->merchant_id,
      'level' => $level,
      'from_level' => $fromLevel,
      ];
      CollectionAssign::model()->assignAgain($collectionAssignData);
      (new CollectionLog())->addLog($collection, $fromStatus, Collection::STATUS_WAIT_COLLECTION);
      return $collection;
      });
      CollectionStatisticsRedis::redis()->hIncr(
      $collection->admin_id,
      CollectionStatisticsRedis::FIELD_STAFF_ALLOT_ORDER,
      $needUpdateCollection->order->merchant_id
      );
      }
      $lastOverdueDays = $overdueDays;
      }
      return $this->outputSuccess('催收案件重新分配成功');
      } catch (\Exception $e) {
      return $this->outputError('催收案件重新分配异常:' . $e->getMessage());
      }
      } */

    public function assignOrder() {
        try {
            $rules = CollectionSetting::model()->getSettingVals(CollectionSetting::KEY_RULE);
            foreach ($rules as $mid => $rule) {
                $ruleData = json_decode($rule, true);
                if (!$ruleData) {
                    return $this->outputError('未设置催收案件分配规则');
                }
                foreach ($ruleData as $rule) {
//                if (array_get($rule, 'overdue_level') <> 'Reminding') {
//                    continue;
//                }
                    $this->newAssignOrderByAdminIds($rule, false, true);
                }
            }

            //一休云需要每日重新推送
            //$this->yixiuyunPush();
            return $this->outputSuccess('案件分配成功');
        } catch (\Exception $e) {
            return $this->outputError('案件分配异常:' . $e->getMessage());
        }
    }

    public function manualAssignOrder($levelName, $ptp = false, $compel = false) {
        $ruleData = json_decode(CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE), true);
        if (!$ruleData) {
            return $this->outputError('未设置催收案件分配规则');
        }
        foreach ($ruleData as $rule) {
//            echo $rule['overdue_level'].PHP_EOL;
            if ($rule['overdue_level'] == $levelName) {
                $this->newAssignOrderByAdminIds($rule, $compel, $ptp);
                return $this->outputSuccess('案件分配成功');
            }
        }
        return $this->outputError('规则未找到');
    }

    public function newAssignOrderByAdminIds($rule, $compel = false, $ptp = false, $mid = 1) {
//        $compel = true;
//        $ptp = true;
        $mid = MerchantHelper::getMerchantId();
        $levelName = array_get($rule, 'overdue_level');
        if (app()->runningInConsole()) {
            echo $levelName . "->";
        }
//        echo $levelName . PHP_EOL;
        $admins = CollectionAdmin::model()->where("collection_admin.merchant_id", "=", $mid)->where("level_name", "=", $levelName)->where('collection_admin.status', '=', CollectionAdmin::STATUS_NORMAL);
        $admins->join("staff", function($join) {
            $join->on("staff.id", "=", "collection_admin.admin_id");
            $join->where("staff.status", '=', '1');
        });
        $admins->select("collection_admin.*"); //->get()->toArray();
        $admins = $admins->get()->toArray();
        if (app()->runningInConsole()) {
            echo "催收员count:" . count($admins) . PHP_EOL;
        }
        # 手动时
        if ($compel == false) {
            //自动重分配,根据设置改表是否强制重分单
            $autoReassign = (int) array_get($rule, 'auto_reassign');
            $compel = $autoReassign ? true : false;
        }
        $adminIds = $adminWeight = [];
        $totalWeight = 0;
        foreach ($admins as $admin) {
            if ($admin['weight'] > 0) {
                $adminWeight[$admin['admin_id']] = $admin['weight'];
                $totalWeight += $admin['weight'];
            }
        }
        $adminCount = count($admins);
        if ($adminCount == 0) {
            return $this->outputError('未设置催收员或选择催收员账号异常');
        }
        $level = array_get($rule, 'overdue_level');
        $overdueDays = array_get($rule, 'overdue_days');
        $startOverdueDays = array_get($rule, 'start_overdue_days');
        list($minDate, $maxDate) = $this->getDatesByOverdueDays($startOverdueDays, $overdueDays);
        /* 需要催收订单 */
        $needCollectionOrders = \Admin\Models\Order\Order::model()->needCollectionOrderByDatesAndLevel($minDate, $maxDate, $level, ['compel' => $compel]);
        if (app()->runningInConsole()) {
            echo PHP_EOL . "日期({$minDate}->{$maxDate})处理订单:" . count($needCollectionOrders) . PHP_EOL;
        }
        foreach ($needCollectionOrders as $key => $needCollectionOrder) {
//            echo $needCollectionOrder->id.PHP_EOL;
            /* 处理保留P期订单 */
            if ($ptp) {
                //committed_repayment
                if (isset($needCollectionOrder->collection->collectionDetail->progress) &&
                        isset(\Admin\Models\Collection\Collection::PROGRESS_COMMITTED_REPAYMENT[$needCollectionOrder->collection->collectionDetail->progress])) {
                    //获取第一次下批时间
                    $ptpTime = CollectionRecord::model()->getFirstPtpTime($needCollectionOrder->collection->admin_id, $needCollectionOrder->collection->id);

                    /* P期天数 */
                    $pDays = $this->getPtpDays($needCollectionOrder->collection->level);
                    if (isset($ptpTime) && (strtotime($ptpTime) + 86400 * $pDays) > time()) {
                        unset($needCollectionOrders[$key]);
                    }
                }
            }
        }
        $orderCount = count($needCollectionOrders);
        if (app()->runningInConsole()) {
            echo $level . PHP_EOL;
//        print_r([$minDate, $maxDate]);
            echo "订单个数:" . $orderCount . PHP_EOL;
        }
        $allocatedNum = 0;
        $i = 0;
        foreach ($adminWeight as $k => $v) {
            $i++;
            if ($i == count($adminWeight)) {
                $adminIds[$k] = $orderCount - $allocatedNum;
            } else {
                $adminIds[$k] = round($orderCount * $v / $totalWeight);
                $allocatedNum += $adminIds[$k];
            }
        }
        $this->retain_key_shuffle($adminIds);
        asort($adminIds);
        $this->newSaveAssignOrder($needCollectionOrders, $adminIds, $rule);
    }

    private function retain_key_shuffle(array &$arr) {
        if (!empty($arr)) {
            $key = array_keys($arr);
            shuffle($key);
            foreach ($key as $value) {
                $arr2[$value] = $arr[$value];
            }
            $arr = $arr2;
        }
    }

    public function newSaveAssignOrder($needCollectionOrders, $adminIds, $rule) {
        $succNum = [];
        $point = 0;
        $wheel = array_keys($adminIds);
        foreach ($needCollectionOrders as $key => $needCollectionOrder) {
            foreach ($adminIds as $adminId => $orderCount) {
                $key = array_search($adminId, $wheel);
                if ($key != $point) {
                    continue;
                }
                if (!isset($succNum[$adminId])) {
                    $succNum[$adminId] = 0;
                }
                if ($succNum[$adminId] > $orderCount) {
                    $point++;
                    continue;
                }
                $succNum[$adminId]++;
//                echo "OID:".$needCollectionOrder->id.", AID".$adminId.PHP_EOL;
                $collection = \DB::transaction(function () use ($needCollectionOrder, $adminId, $rule) {
                            if ($collection = $needCollectionOrder->collectionUnfinished) {
                                $collection = CollectionServer::server()->againCollection($collection, $adminId, $rule);
                            } else {
                                $collection = CollectionServer::server()->createCollection($needCollectionOrder, $adminId, $rule);
                            }
                            /* 分配一休云 */
                            //YixiuyunServer::server()->checkAdminId($adminId)->push_agent_coll($needCollectionOrder);
                            return $collection;
                        });
                $point++;
                if ($point >= count($wheel)) {
                    $point = 0;
                }
                break;
            }
        }
    }

    public function assignOrderByAdminIds($rule) {
        $adminIds = array_get($rule, 'admin_ids');
        $adminIds = Staff::model()->getNormalIdsByAdminIds($adminIds);
        $adminCount = count($adminIds);
        if ($adminCount == 0) {
            return $this->outputError('未设置催收员或选择催收员账号异常');
        }
        $level = array_get($rule, 'overdue_level');
        $overdueDays = array_get($rule, 'overdue_days');
        $startOverdueDays = array_get($rule, 'start_overdue_days');
        list($minDate, $maxDate) = $this->getDatesByOverdueDays($startOverdueDays, $overdueDays);
        $needCollectionOrders = \Admin\Models\Order\Order::model()->needCollectionOrderByDatesAndLevel($minDate, $maxDate, $level);
        $this->saveAssignOrder($needCollectionOrders, $adminIds, $rule);
    }

    public function assignOrderByLanguage($rule) {
        $adminRules = array_get($rule, 'admin_rules');
        foreach ($adminRules as $adminRule) {
            $adminIds = array_get($adminRule, 'admin_ids');
            $language = array_get($adminRule, 'language');
            $adminIds = Staff::model()->getNormalIdsByAdminIds($adminIds);
            $adminCount = count($adminIds);
            if ($adminCount == 0) {
                return $this->outputError('未设置催收员或选择催收员账号异常');
            }
            $level = array_get($rule, 'overdue_level');
            $overdueDays = array_get($rule, 'overdue_days');
            $startOverdueDays = array_get($rule, 'start_overdue_days');
            list($minDate, $maxDate) = $this->getDatesByOverdueDays($startOverdueDays, $overdueDays);
            $params = [
                'language' => $language,
            ];
            $needCollectionOrders = \Admin\Models\Order\Order::model()->needCollectionOrderByDatesAndLevel($minDate, $maxDate, $level, $params);
            $this->saveAssignOrder($needCollectionOrders, $adminIds, $rule);
        }
    }

    public function saveAssignOrder($needCollectionOrders, $adminIds, $rule) {
        $adminCount = count($adminIds);
        /* 已经分配给飞象将不再二次分配 */
        foreach ($needCollectionOrders as $key => $needCollectionOrder) {
            if ($collection = $needCollectionOrder->collectionUnfinished) {
                if ($needCollectionOrder->getOverdueDays() <= 7 && FeixiangServer::server()->checkAdminId($collection->admin_id)->isPass()) {
                    $collection = CollectionServer::server()->againCollection($collection, $collection->admin_id, $rule);
                    unset($needCollectionOrders[$key]);
                }
            }
        }
        foreach ($needCollectionOrders as $key => $needCollectionOrder) {
            $adminId = $adminIds[$key % $adminCount];
            $collection = \DB::transaction(function () use ($needCollectionOrder, $adminId, $rule, $key, $adminIds, $adminCount) {
                        if ($collection = $needCollectionOrder->collectionUnfinished) {
                            /* 不再发配新的二期订单给飞象 */
                            if (FeixiangServer::server()->checkAdminId($adminId)->isPass()) {
                                $startOverdueDays = array_get($rule, 'start_overdue_days');
                                if ($startOverdueDays != 1) {
                                    $adminId = $adminIds[($key + 1) % $adminCount];
                                }
                            }
                            $collection = CollectionServer::server()->againCollection($collection, $adminId, $rule);
                        } else {
                            $collection = CollectionServer::server()->createCollection($needCollectionOrder, $adminId, $rule);
                        }
                        /* 分配一休云 */
                        YixiuyunServer::server()->checkAdminId($adminId)->push_agent_coll($needCollectionOrder);
                        return $collection;
                    });
        }
    }

    public function getDatesByOverdueDays($startOverdueDays, $endOverdueDays) {
        $maxDate = $startOverdueDays <= 0 ? DateHelper::addDays(-$startOverdueDays) : DateHelper::subDays($startOverdueDays);
        $minDate = $endOverdueDays <= 0 ? DateHelper::addDays(-$endOverdueDays) : DateHelper::subDays($endOverdueDays);
        return [
            $minDate,
            DateHelper::addDays(1, 'Y-m-d', $maxDate)
        ];
    }

    /**
     * 每日推送一休云订单
     */
    public function yixiuyunPush() {
        $collections = Collection::model()->newQuery()->whereIn('admin_id', YixiuyunServer::server()->adminIds)->whereIn('status', Collection::STATUS_NOT_COMPLETE)->get();
        foreach ($collections as $collection) {
            echo '-';
            YixiuyunServer::server()->checkAdminId($collection->admin_id)->push_agent_coll($collection->order);
        }
    }

    public function yixiuyunPushList() {
        $collections = Collection::model()->newQuery()->whereIn('admin_id', YixiuyunServer::server()->adminIds)->whereIn('status', Collection::STATUS_NOT_COMPLETE)->get();
        $collectionList = [];
        foreach ($collections as $key => $collection) {
            $collectionList[] = $collection;
            if ($key % 50 == 0 && $key > 0) {
                YixiuyunServer::server()->push_agent_coll_list($collectionList);
                $collectionList = [];
            }
        }
        if ($collectionList)
            YixiuyunServer::server()->push_agent_coll_list($collectionList);
    }

    public function feixiangPush() {
        $collections = Collection::model()->newQuery()->whereIn('admin_id', FeixiangServer::server()->adminIds)->whereIn('status', Collection::STATUS_NOT_COMPLETE)->get();
        foreach ($collections as $collection) {
            echo '-';
            FeixiangServer::server()->checkAdminId($collection->admin_id)->collectionApply($collection->order);
            FeixiangServer::server()->checkAdminId($collection->admin_id)->collectionUploadContact($collection->order, 20);
            FeixiangServer::server()->checkAdminId($collection->admin_id)->collectionOrderOverdue($collection->order);
        }
    }

    public function getPtpDays($levelName) {
        $ruleData = json_decode(CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE), true);
        foreach ($ruleData as $rule) {
            foreach ($ruleData as $rule) {
//            echo $rule['overdue_level'].PHP_EOL;
                if ($rule['overdue_level'] == $levelName) {
                    return array_get($rule, 'p_period_days');
                }
            }
        }
        return 0;
    }

}
