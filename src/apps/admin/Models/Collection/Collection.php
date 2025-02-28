<?php

namespace Admin\Models\Collection;

use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Admin\Models\Staff\Staff;
use Admin\Models\User\User;
use Admin\Services\Collection\CollectionContactServer;
use Common\Models\Common\Config;
use Common\Models\Coupon\CouponReceive;
use Common\Utils\Data\DateHelper;
use Common\Utils\LoginHelper;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;

/**
 * Admin\Models\Collection\Collection
 *
 * @property int $id
 * @property int $admin_id 催收人员id
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property string $status 催收状态
 * @property string $level 催收等级(M M0 M1 M2)
 * @property string|null $assign_time 分配时间
 * @property string|null $finish_time 完结时间
 * @property string|null $bad_time 坏账时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereAssignTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereBadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereFinishTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereUserId($value)
 * @mixin \Eloquent
 * @phan-file-suppress PhanNoopProperty
 * @property string|null $collection_time 首催时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereCollectionTime($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\Collection whereMerchantId($value)
 */
class Collection extends \Common\Models\Collection\Collection
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_DEDUCTION_INFO = 'deduction_info';
    const SCENARIO_SIMPLE = 'simple';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_ASSIGN_AGAIN = 'assign_again';

    public function safes()
    {
        return [
            self::SCENARIO_UPDATE => [
                'status'
            ],
            self::SCENARIO_ASSIGN_AGAIN => [
                'admin_id',
                'status' => self::STATUS_WAIT_COLLECTION,
                'assign_time' => DateHelper::dateTime(),
            ],
        ];
    }

    public function texts()
    {
        return [
            self::SCENARIO_LIST => [
                'id',
                'admin_id',
                'user_id',
                'order_id',
                'status',
                'level',
                'assign_time',
                'finish_time',
                'bad_time',
                'promise_paid_time',
                'record_time',
                'call_test_status',
                'collection_last_time',
            ],
            self::SCENARIO_DETAIL => [
                'id',
                'admin_id',
                'user_id',
                'order_id',
                'status',
                'level',
                'assign_time',
                'finish_time',
                'bad_time',
                'record_time',
            ],
            self::SCENARIO_DEDUCTION_INFO => [
                'id',
                'admin_id',
                'user_id',
                'order_id',
                'status',
            ],
            self::SCENARIO_SIMPLE => [
                'id',
                'admin_id',
                'level',
                'order_id',
                'status',
                'bad_time',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'status' => ts(self::STATUS, 'collection'),
            ],
            'function' => [
                'id' => function ($data) {

                    $repaymentPlan = $data->order->lastRepaymentPlan;
                    # 催收列表的预期计算有问题
                    if($repaymentPlan && $repaymentPlan->appointment_paid_time<date("Y-m-d H:i:s")){
                        $data->overdue_days = $repaymentPlan->overdue_days;
                    }else{
                        /** @var Collection $data */
                        $data->overdue_days = $data->order->getOverdueDays();
                    }
                    # 列表
                    if ($this->scenario == self::SCENARIO_LIST) {
                        if ($data->order) {
                            $data->order->setScenario(Order::SCENARIO_LIST)->getText();
                            $data->order->amout_paid = $data->order->repay_amount ?: $data->order->part_repay_amount;
                            if (in_array($data->order->status, Order::WAIT_REPAYMENT_STATUS) && in_array($data->status, Collection::STATUS_NOT_COMPLETE)) {
                                $data->repay_tip = ($data->order->part_repay_amount && $data->order->part_repay_amount != '---')
                                    ? t('部分还款', 'collection') : t('未还款', 'collection');
                            }
                            # 第一期进行中
                            if (!in_array($repaymentPlan->status, RepaymentPlan::FINISH_STATUS)) {
                                # 配置逾期可部分还款，且不对所有生效
                                if ((new Config)->getRepayPartRepayOn() && ($repayPartRepayConfig = (new Config)->getRepayPartRepayConfig())
                                    && !(new Config)->getRepayPartAllOverdueOn()) {
                                    # 逾期最小天数已设定，且满足
                                    $repayPartMinOverdueDays = (new Config)->getRepayPartMinOverdueDays();
                                    if ($repayPartMinOverdueDays !== false && $repaymentPlan->overdue_days >= $repayPartMinOverdueDays) {
                                        $data->part_repay_on = $repaymentPlan->can_part_repay;
                                    }
                                }
                            }
                        }
                        $data->staff && $data->staff->setScenario(Staff::SCENARIO_INFO)->getText();
                        $data->collectionDetail && $data->collectionDetail->setScenario(CollectionDetail::SCENARIO_INFO)->getText();
                        # 承诺还款
                        if ($data->collectionRecordPromise) {
                            $data->collectionDetail->promise_paid_time = $data->collectionDetail->promise_paid_time.' '.$data->collectionRecordPromise->promise_paid_time_slot;
                        }
                        $data->collectionBlackList;
                        $installmentNumList = [];
                        if ($repaymentPlans = $data->overdueRepaymentPlans) {
                            foreach ($repaymentPlans as $repaymentPlan) {
                                /** @var RepaymentPlan $repaymentPlan */
                                if ($repaymentPlan->appointment_paid_time < DateHelper::date()) {
                                    $installmentNumList[] = $repaymentPlan->installment_num;
                                }
                            }
                        }
                        $data->installment_num_list = implode(',', $installmentNumList);
                    }
                    # 详情
                    if ($this->scenario == self::SCENARIO_DETAIL) {
                        //order
                        if ($data->order) {
                            $data->order->setScenario(Order::SCENARIO_DETAIL)->getText();
                            //$data->order_detail = $data->order->getOrderDetails();
                            foreach ($data->overdueRepaymentPlans as &$overdueRepaymentPlan) {
                                $overdueRepaymentPlan->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
                            }
                            foreach ($data->repaymentPlans as &$repaymentPlan) {
                                $repaymentPlan->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
                            }
                            if (in_array($data->order->status, Order::WAIT_REPAYMENT_STATUS) && in_array($data->status, Collection::STATUS_NOT_COMPLETE)) {
                                $data->repay_tip = ($data->order->part_repay_amount && $data->order->part_repay_amount != '---')
                                    ? t('部分还款', 'collection') : t('未还款', 'collection');
                            }
                        }
                        //user
                        if ($data->user) {
                            $data->user->setScenario(User::SCENARIO_DETAIL)->getText();
                            $data->user->userInfo;
                            $data->user->userInfo->city = isset($data->user->userInfo->city) ? \Common\Models\Common\Dict::getNameByCode($data->user->userInfo->city) : "---";
                            $data->user->userInfo->province = isset($data->user->userInfo->province) ? \Common\Models\Common\Dict::getNameByCode($data->user->userInfo->province) : "---";
                            // $data->user->userLoginLog;
                        }
                        //detail
                        if ($data->collectionDetail) {
                            $data->collectionDetail->setScenario(CollectionDetail::SCENARIO_INFO)->getText();
                            # 取本人，紧急联系人，催收员添加联系人
                            $collectionAddContacts = CollectionContact::model()->getCollectionContacts($data->id, 0, [
                                CollectionContact::TYPE_USER_SELF,
                                CollectionContact::TYPE_USER_CONTACT,
                                CollectionContact::TYPE_COLLECTION_CONTACT
                            ]);
                            foreach ($collectionAddContacts as $collectionAddContact) {
                                $collectionAddContact->setScenario(CollectionContact::SCENARIO_LIST)->getText();
                            }
                            # 取通讯录联系人
                            /* 210603 Frank 催收里面先暂停展示用户的通讯录号码
                            $collectionMessageContacts = CollectionContactServer::server()->getMessageContact($data, $data->collectionDetail->contact_num);
                            foreach ($collectionMessageContacts as $collectionContact) {
                                $collectionContact->setScenario(CollectionContact::SCENARIO_LIST)->getText();
                            }*/
                            $data->collection_contacts = array_merge($collectionAddContacts->toArray(), []);//$collectionMessageContacts->toArray()
                        }
                        # 承诺还款
                        if ($data->collectionRecordPromise) {
                            $data->collectionRecordPromise->promise_paid_time = DateHelper::formatToDate($data->collectionRecordPromise->promise_paid_time, 'd/m/Y');
                        }
                    }
                    if ($this->scenario == self::SCENARIO_SIMPLE) {
                        $data->staff && $data->staff->setScenario(Staff::SCENARIO_INFO)->getText();
                    }
                },
                'bad_time' => function () {
                    $overdueDays = $this->order->getOverdueDays($this->order->lastRepaymentPlan, true);
                    return "逾期{$overdueDays}天转坏账，暂停催收";
                }
            ]
        ];
    }

    public function canDeductionAmount($repaymentPlan = '')
    {
        if (!($reductionSetting = $this->collectionDetail->reduction_setting)) {
            return 0;
        }
        $subject = CalcRepaymentSubjectServer::server($this->order->lastRepaymentPlan)->getSubject();
        if ($reductionSetting == CollectionSetting::REDUCTION_SETTING_OVERDUE_INTEREST) {
            return $subject->overdueFee + $subject->forfeitPenalty + $this->order->interestFee();
        }
        if ($reductionSetting == CollectionSetting::REDUCTION_SETTING_PRINCIPAL_INTEREST) {
            return $this->order->getPaidPrincipal($repaymentPlan) + $subject->overdueFee + $subject->forfeitPenalty;
        }
        if ($reductionSetting == CollectionSetting::REDUCTION_SETTING_OVERDUE) {
            return $subject->overdueFee + $subject->forfeitPenalty;
        }
        if ($reductionSetting == CollectionSetting::REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE40) {
            return $subject->overdueFee + $subject->forfeitPenalty + $this->order->interestFee() + $this->order->getProcessingFee() * 0.4;
        }
        if ($reductionSetting == CollectionSetting::REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE60) {
            return $subject->overdueFee + $subject->forfeitPenalty + $this->order->interestFee() + $this->order->getProcessingFee() * 0.6;
        }
        if ($reductionSetting == CollectionSetting::REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE80) {
            return $subject->overdueFee + $subject->forfeitPenalty + $this->order->interestFee() + $this->order->getProcessingFee() * 0.8;
        }
        if ($reductionSetting == CollectionSetting::REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE90) {
            return $subject->overdueFee + $subject->forfeitPenalty + $this->order->interestFee() + $this->order->getProcessingFee() * 0.9;
        }
        return 0;
    }

    public function collectionDetail($class = CollectionDetail::class)
    {
        return parent::collectionDetail($class);
    }

    public function user($class = User::class)
    {
        return parent::user($class);
    }

    public function order($class = Order::class)
    {
        return parent::order($class);
    }

    public function staff($class = Staff::class)
    {
        return parent::staff($class);
    }

    public function collectionRecord($class = CollectionRecord::class)
    {
        return parent::collectionRecord($class);
    }

    public function collectionRecords($class = CollectionRecord::class)
    {
        return parent::collectionRecords($class);
    }

    public function collectionRecordPromise($class = CollectionRecord::class)
    {
        return parent::collectionRecord($class)->whereNotNull('promise_paid_time');
    }

    public function collectionContacts($class = CollectionContact::class)
    {
        return parent::collectionContacts($class);
    }

    public function collectionDeduction($class = CollectionDeduction::class)
    {
        return parent::collectionDeduction($class);
    }

    public function repaymentPlans($class = RepaymentPlan::class)
    {
        return parent::repaymentPlans($class);
    }

    public function couponReceive($class = CouponReceive::class)
    {
        return $this->hasOne($class, 'order_id', 'order_id');
    }

    public function overdueRepaymentPlans($class = RepaymentPlan::class)
    {
        return parent::overdueRepaymentPlans($class);
    }

    /**
     * @param $param
     * @param $isMyOrderList
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search($param, $isMyOrderList)
    {
        $query = $this->newQuery();

        /** 排序 start */
//        $query->orderByCustom();
        /** 排序 end */

        $query->select(['collection.*']);

        if ($isMyOrderList) {
            $query->where('admin_id', LoginHelper::getAdminId());
            $query->whereIn('status', Collection::STATUS_NOT_COMPLETE);
        }

        # 状态
        if(isset($param['status']) && "undefined" == $param['status']){
            unset($param['status']);
        }
        if ($status = array_get($param, 'status')) {
            if ($status == Collection::STATUS_COMMITTED_REPAYMENT) {
                $query->whereIn('status', Collection::STATUS_NOT_COMPLETE)
                    ->whereHas('collectionDetail', function ($collectinoDetail) {
                        $collectinoDetail->where('promise_paid_time', '>=', DateHelper::date());
                    });
            } else {
                //催收中订单,未还款,已还款
                $status = (array)$status;
                $query->where(function($query) use ($status){

                    if ( in_array(Collection::STATUS_COLLECTIONING_UNREPAY,$status) &&
                        !in_array(Collection::STATUS_COLLECTIONING_PARTREPAY,$status) &&
                        !in_array(Collection::STATUS_COLLECTIONING,$status) ){
                        //非部分还款
                        $query->whereHas('lastRepaymentPlan',function ($query) {
                            $query->whereDoesntHave('lastRepayDetail');
                        });
                        $status[] = Collection::STATUS_COLLECTIONING;
                        $query->whereIn('collection.status', $status);
                        foreach ($status as $k=>$v){
                            if ( $v == Collection::STATUS_COLLECTIONING_UNREPAY || $v == Collection::STATUS_COLLECTIONING ){
                                unset($status[$k]);
                            }
                        }
                    }
                    if ( !in_array(Collection::STATUS_COLLECTIONING_UNREPAY,$status) &&
                        in_array(Collection::STATUS_COLLECTIONING_PARTREPAY,$status) &&
                        !in_array(Collection::STATUS_COLLECTIONING,$status) ){
                        //部分还款
                        $query->whereHas('lastRepaymentPlan',function ($query) {
                            $query->whereHas('lastRepayDetail');
                        });
                        $status[] = Collection::STATUS_COLLECTIONING;
                        # 催收中订单查询部分还款排除使用优惠券的
//                        $query->whereDoesntHave('couponReceive');
                        $query->whereIn('collection.status', $status);
                        foreach ($status as $k=>$v){
                            if ( $v == Collection::STATUS_COLLECTIONING_PARTREPAY || $v == Collection::STATUS_COLLECTIONING ){
                                unset($status[$k]);
                            }
                        }
                    }
                    if ( in_array(Collection::STATUS_COLLECTIONING_UNREPAY,$status) &&
                        in_array(Collection::STATUS_COLLECTIONING_PARTREPAY,$status) &&
                        !in_array(Collection::STATUS_COLLECTIONING,$status) ){
                        //部分还款
                        $query->whereHas('lastRepaymentPlan',function ($query) {
                            $query->whereHas('lastRepayDetail');
                        });
                        $status[] = Collection::STATUS_COLLECTIONING;
                        # 催收中订单查询部分还款排除使用优惠券的
//                        $query->whereDoesntHave('couponReceive');
                        $query->whereIn('collection.status', $status);
                        foreach ($status as $k=>$v){
                            if ( Collection::STATUS_COLLECTIONING_PARTREPAY==$v && $v == Collection::STATUS_COLLECTIONING_UNREPAY || $v == Collection::STATUS_COLLECTIONING ){
                                unset($status[$k]);
                            }
                        }
                    }
                    if ( count($status)>0 ){
                        $query->orWhereIn('collection.status', $status);
                    }

                });
            }
        }else{
            //非催收中部分
            $query->where(function ($query){
                $query->where(function ($query){
                    $query->whereIn('status',[self::STATUS_COLLECTION_SUCCESS,self::STATUS_COLLECTION_BAD,self::STATUS_WAIT_COLLECTION]);
                    $query->whereHas('lastRepaymentPlan');
                });
                //或催收中非部分还款
                $query->orWhere(function ($query){
                    $query->whereIn('status',[self::STATUS_COLLECTIONING]);
                    $query->whereHas('lastRepaymentPlan',function ($query) {
                        $query->whereDoesntHave('lastRepayDetail');
                    });
                });
            });

        }
        # 是否进行呼叫测试
        if($testCallStatus = array_get($param, 'call_test_status_txt')){
            $query->whereIn('call_test_status', $testCallStatus);
        }

        # 用户查询
        $keyword = array_get($param, 'keyword');
        if (isset($keyword)) {
            $query->whereHas('user', function ($user) use ($keyword) {
                $user->where(function ($user) use ($keyword) {
                    $keyword = trim($keyword);
                    $user->where('fullname', 'like', '%' . $keyword . '%');
                    $user->orWhere('telephone', 'like', '%' . $keyword . '%');
                    $user->orWhere('id_card_no', '=', $keyword);
                });
            });
        }
        # 订单查询
        if ($orderNo = array_get($param, 'order_no')) {
            $query->whereHas('order', function ($user) use ($orderNo) {
                $user->where(function ($user) use ($orderNo) {
                    $user->where('order_no', $orderNo);
                });
            });
        }
        # 应还款时间
        if ($appointmentPaidTime = array_get($param, 'appointment_paid_time')) {
            if (count($appointmentPaidTime) == 2) {
                $query->whereHas('lastRepaymentPlan', function ($query) use ($appointmentPaidTime) {
                    $start = current($appointmentPaidTime);
                    $end = last($appointmentPaidTime);
                    $query->whereBetween('repayment_plan.appointment_paid_time', [$start, $end]);
                });
            }
        }
        # 分案时间
        if ($assignTime = array_get($param, 'assign_time')) {
            if (count($assignTime) == 2) {
                $start = current($assignTime);
                $end = last($assignTime);
                $query->whereBetween('assign_time', [$start, $end]);
            }
        }
        # 催记时间
        if ($recordTime = array_get($param, 'record_time')) {
            if (count($recordTime) == 2) {
                $query->whereHas('collectionDetail', function ($query) use ($recordTime) {
                    $start = current($recordTime);
                    $end = last($recordTime);
                    $query->whereBetween('collection_detail.record_time', [$start, $end]);
                });
            }
        }

        # 催记时间
        $recordTime = array_get($param, 'record_time');
        # 承诺还款时间
        $promisePaidTime = array_get($param, 'promise_paid_time');
        # 联系结果
        $dial = array_get($param, 'dial');
        $progress = array_get($param, 'progress');
        if ($recordTime || $promisePaidTime || $dial) {
            $query->whereHas('collectionDetail', function ($query) use ($recordTime, $promisePaidTime, $dial, $progress) {
                # 催记时间
                if ($recordTime) {
                    $start = current($recordTime);
                    $end = last($recordTime);
                    $query->whereBetween('collection_detail.record_time', [$start, $end]);
                }
                # 承诺还款时间
                if ($promisePaidTime) {
                    $start = current($promisePaidTime);
                    $end = last($promisePaidTime);
                    $query->whereBetween('collection_detail.promise_paid_time', [$start, $end]);
                }
                # 联系结果
                if ($dial) {
                    $query->where('dial', $dial);
                }
                if($progress){
                    $query->where('progress', $progress);
                }
            });
        }
        # 催收成功时间
        if ($finishTime = array_get($param, 'finish_time')) {
            $start = current($finishTime);
            $end = last($finishTime);
            $query->whereBetween('collection.finish_time', [$start, $end]);
        }
        # 坏账时间
        if ($badTime = array_get($param, 'bad_time')) {
            $start = current($badTime);
            $end = last($badTime);
            $query->whereBetween('collection.bad_time', [$start, $end]);
        }
        # 催收员
        if ($adminIds = array_get($param, 'admin_ids')) {
            $query->whereIn('collection.admin_id', (array)$adminIds);
        }

        # 催收等级
        if ($level = array_get($param, 'level')) {
            $query->whereIn('collection.level', (array)$level);
        }

        # 逾期天数
        if ($overdueDays = array_get($param, 'overdue_days')) {
            if (count($overdueDays) == 2) {
                $query->whereHas('lastRepaymentPlan', function ($query) use ($overdueDays) {
                    $start = current($overdueDays);
                    $end = last($overdueDays);
                    //$query->whereBetween('overdue_days', [$start, $end]);

                    $query->where('installment_num', 1)->whereBetween('appointment_paid_time', [DateHelper::subDays($end), DateHelper::subDays($start)]);
                });
            }
        }
//        dd($query->toSql());
        return $query->orderBy('collection.collection_last_time', 'desc')->orderBy('collection.id', 'desc');
    }

    /**
     * 根据id获取
     * @param $id
     * @return mixed
     */
    public function getOne($id)
    {
        return self::where('id', '=', $id)->first();
    }

    /**
     * @param $id
     * @param int $adminId
     * @return mixed
     */
    public function getLastOne($id, $adminId = 0)
    {
        $query = self::where('id', '>', $id);
        if ($adminId != 0) {
            $query->where('admin_id', $adminId);
        }
        return $query->whereIn('status', self::STATUS_NOT_COMPLETE)
            ->first();
    }

    /**
     * @param $id
     * @param int $adminId
     * @return mixed
     */
    public function getNextOne($id, $adminId = 0)
    {
        $query = self::where('id', '<', $id);
        if ($adminId != 0) {
            $query->where('admin_id', $adminId);
        }
        return $query->whereIn('status', self::STATUS_NOT_COMPLETE)->orderBy('id', 'desc')
            ->first();
    }

    public function updateStatus($collection, $status)
    {
        return $collection->setScenario(self::SCENARIO_UPDATE)->saveModel(['status' => $status]);
    }

    public function assignAgain($collection, $data)
    {
        /** @var \Common\Models\Collection\Collection $collection */
        return $collection->setScenario(self::SCENARIO_ASSIGN_AGAIN)->saveModel($data);
    }

    public function getBackOut() {
        $hour = '';
        foreach (\Common\Models\Collection\CollectionRecord::TIME_SLOT as $item) {
            $hourTmp = explode(':', $item);
            if ($hourTmp[0] == date("G")) {
                $hour = $item;
                break;
            }
        }

        $query = Collection::model()->query();
        $query->where('admin_id', LoginHelper::getAdminId());
        $query->whereIn('status', Collection::STATUS_NOT_COMPLETE);
        $query->whereHas('collectionRecords', function ($collectinoRecords) use ($hour) {
            $collectinoRecords->where('promise_paid_time', '=', date("Y-m-d 00:00:00"));
            $collectinoRecords->where('promise_paid_time_slot', '=', $hour);
        });
        $list = $query->get();
        $res = ["slot" => $hour,"order_list"=>[]];
        foreach ($list as $collection) {
            if ($collection->collectionRecord->promise_paid_time == date("Y-m-d 00:00:00") && $collection->collectionRecord->promise_paid_time_slot == $hour) {
                $res['order_list'][] = [
                    "collection_id" => $collection->id,
                    "order_no" => $collection->order->order_no,
                ];
            }
        }
        return $res;
    }

}
