<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Models\Collection\Collection;
use Admin\Models\Collection\CollectionContact;
use Admin\Models\Collection\CollectionDetail;
use Admin\Models\Collection\CollectionRecord;
use Admin\Models\Staff\Staff;
use Admin\Services\BaseService;
use Admin\Services\Repayment\RenewalRepaymentServer;
use Api\Services\User\UserBlackServer;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\Collection\CollectionLog;
use Common\Utils\Data\DateHelper;
use Common\Utils\DingDing\DingHelper;
use Illuminate\Support\Facades\DB;

class CollectionRecordServer extends BaseService
{
    /**
     * @param $param
     * @return mixed
     */
    public function getPageList($param)
    {
        $data = CollectionRecord::model()->search($param, ['order.lastRepaymentPlan'])->paginate();

        /** @var CollectionRecord $item */
        foreach ($data as $item) {
            $item->repayment_plan_no = optional(optional($item->order)->lastRepaymentPlan)->no;
            $item->staff && $item->staff->setScenario(Staff::SCENARIO_INFO)->getText();
            unset($item->order);
        }

        return $data;
    }

    public function getList($param = [])
    {
        $data = CollectionRecord::model()->search($param, ['staff'])->get();
        foreach ($data as $item) {
            $item->staff && $item->staff->setScenario(Staff::SCENARIO_INFO)->getText();
            $item->contact_method = \Common\Models\Collection\CollectionRecord::CONTACT_METHOD[$item->contact_method] ?? '---';
            $item->promise_paid_time = DateHelper::formatToDate($item->promise_paid_time, 'Y-m-d').' '.$item->promise_paid_time_slot;
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOne($id)
    {
        return CollectionRecord::model()->getOne($id);
    }

    public function create($param, $isRemind = false)
    {
        $collectionId = array_get($param, 'collection_id');
        /** @var Collection $collection */
        $collection = Collection::model()->getOne($collectionId);
        if (!$collection) {
            return $this->outputException('记录不存在');
        }
        CollectionServer::server()->checkCaseBelong($collection);
        //if (!$collection->admin_id != LoginHelper::getAdminId()) {
        //return $this->outputException('无权限操作');
        //}
        if (!in_array($collection->status, array_merge(Collection::STATUS_NOT_COMPLETE, Collection::STATUS_HIDDEN))) {
            return $this->outputException('当前案子状态不允许操作');
        }
        $contactId = array_get($param, 'contact_id');
        $contact = CollectionContact::model()->getOne($contactId);
        if (!$contact || $contact->order_id != $collection->order_id) {
            return $this->outputException('联系人记录id不存在');
        }
        DB::beginTransaction();

        //更新联系人信息
        $fullname = array_get($param, 'fullname');
        $relation = array_get($param, 'relation');
        if ($fullname || $relation) {
            //if ($contact->relation != CollectionContact::RELATION_ONESELF && $contact->contact != $collection->user->telephone) {
            if (!CollectionContact::model()->updateContact($contact, $param)) {
                DB::rollBack();
                return $this->outputException('联系人信息修改失败');
            };
            //}
        }

        //添加催记
        $promisePaidTime = array_get($param, 'promise_paid_time');
        $dial = array_get($param, 'dial');
        $progress = array_get($param, 'progress');
        $remark = array_get($param, 'remark', '');
        $overdueDays = $collection->order->getOverdueDays();
        $data = [
            'fullname' => $contact->fullname,
            'relation' => $contact->relation,
            'dial' => $dial,
            'progress' => $progress,
            //'promise_paid_time' => $promisePaidTime,
            'remark' => $remark,
            'collection_id' => $collection->id,
            'order_id' => $collection->order_id,
            'user_id' => $collection->user_id,
            'contact' => $contact->contact,
            'from_status' => $collection->status,
            'to_status' => $isRemind ? Collection::STATUS_REPAY_REMIND : Collection::STATUS_COLLECTIONING,
            'overdue_days' => $overdueDays,
            'reduction_fee' => $collection->order->getReductionFee(),
            'level' => $collection->level,
            'receivable_amount' => $collection->order->repayAmount(),
            'collection_assign_id' => $collection->collectionAssign->id ?? 0,
            'contact_method' => $param['contact_method']??"",
        ];
        # 承诺还款
//        if ($progress == Collection::PROGRESS_COMMITTED_REPAYMENT or $progress == Collection::PROGRESS_INTENTIONAL_HELP) {
        if (isset(Collection::PROGRESS_COMMITTED_REPAYMENT["{$progress}"]) or isset(Collection::PROGRESS_INTENTIONAL_HELP["{$progress}"])) {
            if (!$promisePaidTime) {
                # 由于前端未处理，暂时没有值传入
                //return $this->outputException('承诺还款需选择时间');
            }else{
                $data['promise_paid_time'] = $promisePaidTime;
                $data['promise_paid_time_slot'] = array_get($param, 'promise_paid_time_slot');
            }
        }

        # 催收在催记里下了对应的代码，renewal_repayment 触发展期操作
        if ($progress == Collection::PROGRESS_RENEWAL_REPAYMENT) {
            $return = RenewalRepaymentServer::server(['no' => $collection->lastRepaymentPlan->no])->applyRenewal();
            if (!$return) {
                return $this->outputException('展期失败');
            }
            if ( ($return instanceof RenewalRepaymentServer) && $return->isError() ) {
                return $this->outputException($return->getMsg());
            }
        }

        //催记时间最后一次
        $collectionRecord = CollectionRecord::model()->create($data);
        if (!$collectionRecord) {
            DB::rollBack();
            return $this->outputException('催记添加失败');
        }
        $collectionData = [
            'collection_last_time' => $collectionRecord->created_at
        ];
        if (!$collection->setScenario($collectionData)->save()) {
            return $this->outputException('催收最后一次催收时间更新失败');
        }


        //处理详情
        if (!CollectionDetail::model()->record($collection->collectionDetail, $data)) {
            DB::rollBack();
            return $this->outputException('催记详情更新失败');
        }

        //更新催收状态
        if ($collection->status == Collection::STATUS_WAIT_COLLECTION) {
            $collectionData = [
                'status' => Collection::STATUS_COLLECTIONING,
            ];
            //更新首催时间
            if (!$collection->collection_time) {
                $collectionData['collection_time'] = DateHelper::dateTime();
            }
            if (!$collection->setScenario($collectionData)->save()) {
                return $this->outputException('催收首催更新失败');
            }
            (new CollectionLog)->addLog($collection, Collection::STATUS_WAIT_COLLECTION, Collection::STATUS_COLLECTIONING);
        }

        DB::commit();

        /*if (!$isRemind) {
            # 承诺还款统计
            if ($progress == '承诺还款') {
                CollectionStatisticsRedis::redis()->incr(CollectionStatisticsRedis::KEY_PROMISE_PAID_COUNT, $collection->order->merchant_id);
                CollectionStatisticsRedis::redis()->hIncr($collection->admin_id, CollectionStatisticsRedis::FIELD_STAFF_PROMISE_PAID, $collection->order->merchant_id);
            }
            # 催收次数统计
            CollectionStatisticsRedis::redis()->incr(CollectionStatisticsRedis::KEY_COLLECTION_COUNT, $collection->order->merchant_id);
            CollectionStatisticsRedis::redis()->hIncr($collection->admin_id, CollectionStatisticsRedis::FIELD_STAFF_COLLECTION_COUNT, $collection->order->merchant_id);
        }*/

        # 本人无意向还款，拉入注册黑名单
        if ($overdueDays > 0 && $contact->relation == CollectionContact::RELATION_ONESELF && $dial == Collection::DIAL_NORMAL_CONTACT && $progress == Collection::PROGRESS_SELF_INADVERTENTLY_REPAY) {
            UserBlackServer::server()->addCannotRegister($collection->user->telephone, 'collection inadvertently');
        }
        # 本人和紧急联系人无法联系
        if ($overdueDays > 0 && $dial == Collection::DIAL_UNABLE_CONTACT && CollectionRecordServer::server()->getUnableContactCount($collection->id) >= 3) {
            UserBlackServer::server()->addCannotRegister($collection->user->telephone, 'collection unable contact');
        }

        event(new RiskDataSendEvent($collection->user_id, RiskDataSendEvent::NODE_ORDER_COLLECTION));
    }

    public function createRenewalRecord($collection, $fromStatus, $overdueDays, $fromLevel = null)
    {
        $data = [
            'collection_id' => $collection->id,
            'order_id' => $collection->order_id,
            'user_id' => $collection->user_id,
            'fullname' => '',
            'relation' => '',
            'contact' => '',
            'dial' => '',
            'progress' => '',
            'remark' => '用户已续期，关闭催收',
            'from_status' => $fromStatus,
            'to_status' => $collection->status,
            'overdue_days' => $overdueDays,
            'reduction_fee' => $collection->order->getReductionFee(),
            'level' => $fromLevel ?? $collection->level,
            'receivable_amount' => $collection->order->repayAmount(),
            'collection_assign_id' => $collection->collectionAssign->id,
        ];

        return CollectionRecord::model()->create($data);
    }

    /**
     * 本人和所有紧急联系人无法联系数
     */
    public function getUnableContactCount($collectionId)
    {
        $contacts = \Common\Models\Collection\CollectionContact::query()
            ->where('collection_id', $collectionId)
            ->whereIn('type', [CollectionContact::TYPE_USER_SELF, CollectionContact::TYPE_USER_CONTACT])
            ->pluck('contact')->toArray();
        if (!$contacts) {
            DingHelper::notice(['collectionId' => $collectionId], '催收无联系人');
            return 0;
        }
        return count(\Common\Models\Collection\CollectionRecord::query()
            ->where('collection_id', $collectionId)
            ->whereIn('contact', $contacts)
            ->where('dial', \Common\Models\Collection\Collection::DIAL_UNABLE_CONTACT)
            ->groupBy('contact')->pluck('contact')->toArray());
    }
}
