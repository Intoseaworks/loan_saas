<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Common\Services\Collection;

use Admin\Services\Collection\CollectionRecordServer;
use Common\Console\Models\Collection\CollectionAssign;
use Common\Console\Models\Collection\CollectionContact;
use Common\Console\Models\Collection\CollectionDetail;
use Common\Models\Collection\Collection;
use Common\Models\Collection\CollectionFinish;
use Common\Models\Collection\CollectionLog;
use Common\Models\Collection\CollectionSetting;
use Common\Models\Order\Order;
use Common\Services\BaseService;
use Common\Utils\Email\EmailHelper;
use Common\Services\Third\YixiuyunServer;
use Common\Models\Repay\RepayDetail;
use Common\Models\CollectionStatistics\StatisticsCollectionStaffPeso;

class CollectionServer extends BaseService {

    /**
     * 新增催收&催收详情
     * @param $order Order
     * @param $adminId
     * @param $rule
     * @param bool $isRemind
     * @return bool|\Common\Console\Models\Collection\Collection
     */
    public function createCollection($order, $adminId, $rule = null, $isRemind = false) {
        if (!is_null($rule)) {
            $level = array_get($rule, 'overdue_level');
            $contactNum = array_get($rule, 'contact_num');
            $reductionSetting = array_get($rule, 'reduction_setting');
        } else {
            $level = '未逾期';
            $contactNum = '20';
            $reductionSetting = CollectionSetting::REDUCTION_SETTING_CANNOT;
        }
        $collectionData = [
            'admin_id' => $adminId,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'level' => $level,
            'status' => Collection::STATUS_WAIT_COLLECTION,
            'merchant_id' => $order->merchant_id,
        ];
        if ($isRemind) {
            $collectionData['status'] = Collection::STATUS_REPAY_REMIND;
        }
        $collection = \Common\Console\Models\Collection\Collection::model()->create($collectionData);
        $collectionDetailData = [
            'order_id' => $order->id,
            'collection_id' => $collection->id,
            'contact_num' => $contactNum,
            'reduction_setting' => $reductionSetting,
            'merchant_id' => $order->merchant_id,
        ];
        CollectionDetail::model()->createCollection($collectionDetailData);
        $collectionAssignData = [
            'order_id' => $collection->order_id,
            'collection_id' => $collection->id,
            'admin_id' => $adminId,
            'merchant_id' => $collection->merchant_id,
            'level' => $level,
            'overdue_days' => $collection->order->getOverdueDays(),
        ];
        CollectionAssign::model()->createCollection($collectionAssignData);
        (new CollectionLog())->addLog($collection, '', Collection::STATUS_WAIT_COLLECTION);
        //创建 collection_contact
        CollectionServer::server()->createCollectionContact($order, $collection);
        return $collection;
    }


    public function againCollection($collection, $adminId, $rule, $remark='')
    {
        $level = array_get($rule, 'overdue_level');
        $contactNum = array_get($rule, 'contact_num');
        $reductionSetting = array_get($rule, 'reduction_setting');
        $fromAdminId = $collection->admin_id;


        $fromStatus = $collection->status;
        $fromLevel = $collection->level;
        $collectionData = [
            'admin_id' => $adminId,
            'level' => $level,
        ];
        $collection = \Common\Console\Models\Collection\Collection::model()->assignAgain($collection, $collectionData);
        $collectionDetailData = [
            'contact_num' => $contactNum,
            'reduction_setting' => $reductionSetting,
        ];
        CollectionDetail::model()->assignAgain($collection->collectionDetail, $collectionDetailData);
        $collectionAssignData = [
            'order_id' => $collection->order_id,
            'collection_id' => $collection->id,
            'from_admin_id' => $fromAdminId,
            'admin_id' => $adminId,
            'from_status' => $fromStatus,
            'merchant_id' => $collection->merchant_id,
            'level' => $level,
            'from_level' => $fromLevel,
            'overdue_days' => $collection->order->getOverdueDays(),
            'remark' => $remark
        ];
        CollectionAssign::model()->assignAgain($collectionAssignData);
        (new CollectionLog())->addLog($collection, $fromStatus, Collection::STATUS_WAIT_COLLECTION);
    }

    /**
     * 创建collection_contact
     * @param $order Order
     * @param $collection Collection
     */
    public function createCollectionContact($order, $collection) {
        # 本人
        $userContactData = [
            'merchant_id' => $order->merchant_id,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'collection_id' => $collection->id,
            'type' => CollectionContact::TYPE_USER_SELF,
            'fullname' => $order->user->fullname,
            'contact' => $order->user->telephone,
            'relation' => CollectionContact::RELATION_ONESELF,
        ];
        CollectionContact::model()->create($userContactData);

        # 紧急联系人
        foreach ($userContacts = $order->user->userContacts as $userContact) {
            $userContactData = [
                'merchant_id' => $order->merchant_id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'collection_id' => $collection->id,
                'type' => CollectionContact::TYPE_USER_CONTACT,
                'fullname' => $userContact->contact_fullname,
                'contact' => $userContact->contact_telephone,
                'relation' => $userContact->relation ?: "OTHER",
            ];
            CollectionContact::model()->create($userContactData);
        }

        # 工作电话
        $userWork = $order->user->userWork;
        if ($userWork && $userWork->work_phone) {
            $userContactData = [
                'merchant_id' => $order->merchant_id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'collection_id' => $collection->id,
                'type' => CollectionContact::TYPE_USER_CONTACT,
                'fullname' => "Work Phone",
                'contact' => $userWork->work_phone,
                'relation' => "OTHER",
            ];
            CollectionContact::model()->create($userContactData);
        }

        # Sim卡手机号
        $hardware = $order->hardware;
        if ($hardware && $hardware->native_phone) {
            $userContactData = [
                'merchant_id' => $order->merchant_id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'collection_id' => $collection->id,
                'type' => CollectionContact::TYPE_USER_CONTACT,
                'fullname' => "Hardware Sim Phone NUM",
                'contact' => $hardware->native_phone,
                'relation' => "OTHER",
            ];
            CollectionContact::model()->create($userContactData);
        }

        # 通讯录联系人 不取风控
        /* $messageContacts = RiskServer::server()->contactsFrequent($order->user_id, 14);
          if ($messageContacts) {
          foreach ($messageContacts as $messageContact) {
          $userContactData = [
          'merchant_id' => $order->merchant_id,
          'order_id' => $order->id,
          'user_id' => $order->user_id,
          'collection_id' => $collection->id,
          'type' => CollectionContact::TYPE_MESSAGE_CONTACT,
          'fullname' => $messageContact['contact_fullname'],
          'contact' => $messageContact['contact_telephone'],
          ];
          CollectionContact::model()->create($userContactData);
          }
          } */
    }

    public function finish($orderId) {
        if (!($collection = Collection::model()->getIsCollectionProcess($orderId))) {
            return true;
        }
        if ($collection->status == Collection::STATUS_COLLECTION_SUCCESS) {
            //EmailHelper::send(['order_id' => $orderId], '催收提前结清');
            return true;
        }
        try {
            $fromStatus = $collection->status;
            $collectionFinshData = [
                'merchant_id' => $collection->merchant_id,
                'collection_id' => $collection->id,
                'order_id' => $collection->order_id,
                'admin_id' => $collection->admin_id,
                'from_status' => $fromStatus,
                'to_status' => Collection::STATUS_COLLECTION_SUCCESS,
                'collection_assign_id' => $collection->collectionAssign->id,
            ];
            if (CollectionFinish::model()->create($collectionFinshData) && Collection::model()->toFinsh($collection)) {
                (new CollectionLog())->addLog($collection, $fromStatus, Collection::STATUS_COLLECTION_SUCCESS);
                //YixiuyunServer::server()->checkAdminId($collection->admin_id)->stop_agent_coll($collection);
                return true;
            }
            EmailHelper::send(['order_id' => $orderId, 'collection_id' => $collection->id], '催收结清失败');
            return false;
        } catch (\Exception $e) {
            EmailHelper::send([
                'order_id' => $orderId,
                'e' => $e->getMessage()
                    ], '催收结清异常');
            return false;
        }
    }

    /**
     * @param $collection Collection
     * @param $oldOverdueDays
     * @return bool
     */
    public function renewalDispose($collection, $oldOverdueDays) {
        if (!$collection) {
            return false;
        }
        $fromStatus = $collection->status;
        $fromLevel = $collection->level;
        $collection->status = Collection::STATUS_OVERDUE_RENEWAL;
        $collection->level = '逾期续期';
        $collection->save();

        CollectionRecordServer::server()->createRenewalRecord($collection, $fromStatus, $oldOverdueDays, $fromLevel);

        return true;
    }

    public function todayFinish($adminId) {
        $isMoney = true;
        $ruleData = json_decode(CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE), true);
        $levels = \Common\Models\Collection\CollectionAdmin::model()->where("status", 1)
                        ->where("admin_id", $adminId)->get();
        $res = [];
        foreach ($levels as $level) {
            $res[$level->level_name]['your'] = 0;
            $targetType = true;
            foreach ($ruleData as $item) {
                if ($item['overdue_level'] == $level->level_name) {
                    $targetType = $item['target_type']??"";
                    $res[$level->level_name]['target'] = $item['target_value']??0;
                    break;
                }
            }
            $isMoney = $targetType == CollectionSetting::TARGET_TYPE_MONEY ? true: false;
            $data = StatisticsCollectionStaffPeso::model()
                            ->where("admin_id", $adminId)
                            ->where("level", $level->level_name)
                            ->where("date", Date("Y-m-d"))->first();
            if (!$isMoney) {
                if (isset($data->today_assign_finish_count)) {
                    $res[$level->level_name]['target'] = floor($data->need_collection_count*$res[$level->level_name]['target']/100)+1;
                    $res[$level->level_name]['your'] = $data->today_assign_finish_count;
                }
            } else {
                if (isset($data->repay_all_amount)) {
                    $res[$level->level_name]['your'] = $data->repay_all_amount;
                }
                if(isset($res[$level->level_name]['target'])){
                    $res[$level->level_name]['target'] .= " PHP";
                }
                if(isset($res[$level->level_name]['your'])){
                    $res[$level->level_name]['your'] .= " PHP";
                }
            }
        }
        return $res;
    }

    public function stop($orderId){
        $res['Collection'] = Collection::model()->where('order_id', $orderId)->delete();
        $res['CollectionAssign'] = CollectionAssign::model()->where('order_id', $orderId)->delete();
        return $res;
    }
}
