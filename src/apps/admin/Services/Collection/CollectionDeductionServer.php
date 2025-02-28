<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Models\Collection\Collection;
use Common\Models\Collection\Collection as BaseCollection;
use Admin\Models\Collection\CollectionDeduction;
use Admin\Models\Collection\CollectionSetting;
use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Admin\Services\BaseService;
use Admin\Services\Repayment\RepaymentPlanServer;
use Admin\Models\Collection\CollectionDeductionApply;
use Admin\Models\Repay\RepayDetail;
use Api\Models\Trade\TradeLog;
use Common\Utils\LoginHelper;
use Illuminate\Support\Facades\DB;
use Admin\Services\Repayment\ManualRepaymentServer;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Data\MoneyHelper;
use Common\Models\Config\Config;
use Common\Services\Repay\RepayServer;
use Common\Utils\Lock\LockRedisHelper;

class CollectionDeductionServer extends BaseService {

    /**
     * @param $params
     * @return mixed
     * @throws \Common\Exceptions\ApiException
     */
    public function getInfo($params) {
        $collectionId = array_get($params, 'collection_id');
        if (!$collectionId || !($collection = Collection::model()->getOne($collectionId))) {
            return $this->outputException('该催收记录不存在');
        }

        # repaymentPlan
        $repaymentPlanId = array_get($params, 'repayment_plan_id');
        $repaymentPlan = '';
        if ($repaymentPlanId) {
            $repaymentPlan = RepaymentPlan::model()->getOne(['order_id' => $collection->order_id, 'id' => $repaymentPlanId]);
        }

        # detail deduction
        $collection->collectionDeduction;
        if ($collection->collectionDetail) {
            $collection->deduction_mode = t(array_get(CollectionSetting::REDUCTION_SETTING, $collection->collectionDetail->reduction_setting));
            $collection->can_deduction_amount = strval($collection->canDeductionAmount($repaymentPlan));
        }

        # order
        $subject = CalcRepaymentSubjectServer::server($collection->order->lastRepaymentPlan)->getSubject();
        $collection->order->receivable_amount = strval(MoneyHelper::round2point(empty($collection->order->lastRepaymentPlan->status) ? $collection->order->principal + $collection->order->interestFee() : $collection->order->repayAmount()));
        $collection->order->interest_fee = strval($collection->order->interestFee($repaymentPlan));
        $collection->order->overdue_fee = strval($subject->overdueFee);
        $collection->order->receivable_amount_not_reduction = $collection->order->receivable_amount + strval($collection->order->getReductionFee($repaymentPlan));
        $collection->order->principal = strval($collection->order->getPaidPrincipal($repaymentPlan));

        list($collection->now_deduction_start, $collection->now_deduction_end, $collection->now_reduction_fee) = $collection->order->getReductionVal($repaymentPlan);
        return $collection;
    }

    public function create($data) {
        $collection = Collection::model()->getOne($data['collection_id']);
        if (!$collection) {
            return $this->outputException('该催收记录不存在');
        }
        //CollectionServer::server()->checkCaseBelong($collection);
        if (in_array($collection->status, Collection::STATUS_COMPLETE)) {
            return $this->outputException('该催收记录不可减免');
        }
        if (!in_array($collection->order->status, [Order::STATUS_OVERDUE])) {
            return $this->outputException('该订单不可减免');
        }
        if ($repaymentPlanId = array_get($data, 'repayment_plan_id')) {
            if (!($repaymentPlan = RepaymentPlan::model()->getOne($repaymentPlanId))) {
                return $this->outputException('该还款记录不存在');
            }
            if (!in_array($repaymentPlan->status, RepaymentPlan::UNFINISHED_STATUS)) {
                return $this->outputException('该还款记划已完结');
            }
        } else {
            $repaymentPlan = $collection->order->firstProgressingRepaymentPlan;
        }

        if (CollectionDeduction::model()->where("collection_assign_id", $collection->collectionAssign->id)->exists()){
                return $this->outputException('The collector has applied!');
        }
        $data['is_settle'] = (isset($data['is_settle']) && $data['is_settle'] == 1) ? 1 : 0;
//        if ($data['deduction'] > $collection->canDeductionAmount($repaymentPlan) && $data['is_settle'] == 0) {
//            return $this->outputException('减免金额大于可减免金额');
//        }
        $data['user_id'] = $collection->user_id;
        $data['order_id'] = $collection->order_id;
        $data['from_admin_id'] = $collection->admin_id;
        $data['collection_assign_id'] = $collection->collectionAssign->id;
        $data['overdue_fee'] = $collection->order->overdueFee($repaymentPlan);

        $deductionTime = array_get($data, 'deduction_time');
        if (count($deductionTime) == 2) {
            $deductionTimeStart = current($deductionTime);
            $deductionTimeEnd = last($deductionTime);
        } else {
            return $this->outputException('减免时间不正确');
        }

        $collectionDeduction = \DB::transaction(function () use ($collection, $data, $deductionTimeStart, $deductionTimeEnd, $repaymentPlan) {
                    RepaymentPlanServer::server()->updateDeductionFee($repaymentPlan, $data['deduction'], $deductionTimeStart, $deductionTimeEnd);
                    $collectionDeduction = CollectionDeduction::model()->create($data);
                    return $collectionDeduction;
                });
        if (!$collectionDeduction) {
            return $this->outputException('保存失败');
        }
        return $this->outputSuccess('保存成功');
    }

    public function apply($params) {

        $data = [];
        $data['collection_id'] = $params['collection_id'];
        $collection = Collection::model()->getOne($data['collection_id']);
        $data['order_id'] = $collection->order_id;
        $data['deduction'] = $params['deduction'];
        $data['trade_id'] = 0; //申请时为0，申请通过后记录TradeID
        $data['is_settle'] = array_get($params, 'settle', 1) ? 1 : 0;

        if (!$collection) {
            return $this->outputException('该催收记录不存在');
        }
        //CollectionServer::server()->checkCaseBelong($collection);
        if (in_array($collection->status, Collection::STATUS_COMPLETE)) {
            return $this->outputException('该催收记录不可减免');
        }
        if (!in_array($collection->order->status, [Order::STATUS_OVERDUE])) {
            return $this->outputException('该订单不可减免');
        }
        if ($repaymentPlanId = array_get($data, 'repayment_plan_id')) {
            if (!($repaymentPlan = RepaymentPlan::model()->getOne($repaymentPlanId))) {
                return $this->outputException('该还款记录不存在');
            }
            if (!in_array($repaymentPlan->status, RepaymentPlan::UNFINISHED_STATUS)) {
                return $this->outputException('该还款记划已完结');
            }
        } else {
            $repaymentPlan = $collection->order->firstProgressingRepaymentPlan;
        }
        if ($data['deduction'] > $collection->canDeductionAmount($repaymentPlan) && $data['is_settle'] == 0) {
            return $this->outputException('减免金额大于可减免金额');
        }
        $subject = CalcRepaymentSubjectServer::server($collection->order->lastRepaymentPlan)->getSubject();
        if (bcsub($subject->repaymentPaidAmount, $data['deduction'], 2) > 0) {
            return $this->outputException('减免后订单未能结清');
        }
        $perDayMaxAmount = Config::getValueByKey(Config::KEY_COLLECTION_MAX_DEDUCTION_AMOUNT_PER_DAY);
        if ($perDayMaxAmount) {
            $sumAmount = CollectionDeductionApply::model()->newQuery()
                    ->where("admin_id", LoginHelper::helper()->getAdminId())
                    ->where("created_at", ">", date("Y-m-d"))
                    ->sum('deduction');
            if (($sumAmount + $data['deduction']) > $perDayMaxAmount) {
                return $this->outputException('超出单一账户每日最大可申请减免金额.');
            }
        } else {
            return $this->outputException('未配置,单一账户每日最大可申请减免金额.');
        }
        if (CollectionDeductionApply::model()->newQuery()
                        ->where("admin_id", LoginHelper::helper()->getAdminId())
                        ->where("order_id", $data['order_id'])
                        ->where("status", CollectionDeductionApply::STATUS_APPLY)->exists()) {
            return $this->outputException('There is already an unapproved request for this order.');
        }
        $perDayMaxDeductionCount = Config::getValueByKey(Config::KEY_COLLECTION_MAX_DEDUCTION_USERS_PER_DAY);
        if ($perDayMaxDeductionCount) {
            $totalCount = CollectionDeductionApply::model()->newQuery()
                    ->where("admin_id", LoginHelper::helper()->getAdminId())
                    ->where("created_at", ">", date("Y-m-d"))
                    ->count();
            if (($totalCount + 1) > $perDayMaxDeductionCount) {
                return $this->outputException('超出单一账户每日最多申请减免数.');
            }
        } else {
            return $this->outputException('未配置,单一账户每日最多申请减免数未配置.');
        }
        return CollectionDeductionApply::model()->create($data);
    }

    public function applyList($params) {
        $pageSize = array_get($params, "page_size", 10);
        $query = CollectionDeductionApply::model()->query();
        if (isset($params['status'])) {
            $query->whereIn("status", $params['status']);
        }
        if (isset($params['order_ids'])) {
            $query->whereIn("order_id", $params['order_ids']);
        }
        $res = $query->orderByDesc("id")->paginate($pageSize);
        $res->show = [];
        foreach ($res as $item) {
            $item->s_fullname = $item->order->user->fullname;
            $item->s_order_no = $item->order->order_no;
            $item->s_principal = $item->order->principal;
            $item->s_interest = $item->order->interestFee();
            $item->s_due_date = $item->order->getAppointmentPaidTime();
            $item->s_overdue_period = $item->order->getOverdueDays();
            $item->s_penalty = $item->order->overdueFee();
            $item->s_deduction = $item->deduction;
            $item->s_unpaid_amount = $item->order->repayAmount();
            $item->s_operator = $item->operator ? $item->operator->nickname :null;
            $item->s_auditer = $item->auditer ? $item->auditer->nickname:null;
            $item->s_status = CollectionDeductionApply::STATUS[$item->status];
        }
        return $res;
    }

    public function applyListAll($params) {
        $query = CollectionDeductionApply::model()->query();
        if (isset($params['status'])) {
            $query->whereIn("status", $params['status']);
        }
        if (isset($params['order_ids'])) {
            $query->whereIn("order_id", $params['order_ids']);
        }
        $res = $query->orderByDesc("id")->get();
        return $res;
    }

    public function credited(BaseCollection $collection, $deduction) {
        $order = Order::model()->getOne($collection->order_id);
        $repaymentPlan = $order->lastRepaymentPlan;
        DB::beginTransaction();
        $tradeParams = [
            'remark' => '人工减免',
            'repay_name' => $collection->user->fullname,
            'repay_telephone' => $collection->user->telephone,
            'repay_account' => '',
            'repay_time' => date('Y-m-d H:i:s'),
            'repay_channel' => TradeLog::TRADE_PLATFORM_MANUAL_DEDUCTION,
            'repay_amount' => $deduction
        ];

        $trade = ManualRepaymentServer::server()->addRepayTradeLog($order, TradeLog::TRADE_PLATFORM_DEDUCTION, $deduction, $tradeParams);


        $repay = RepayServer::server($repaymentPlan, $trade)->completeRepay(TRUE);
        DB::commit();
        return $repay;
    }

    public function approve($params) {
        $lockKey = "CollectionDeductionApprove_".$params['apply_id'];
        if(LockRedisHelper::helper()->hasLock($lockKey)){
            return true;
        }
        LockRedisHelper::helper()->addLock($lockKey);
        DB::beginTransaction();
        try {
            $res = false;
            $apply = CollectionDeductionApply::model()->getOne($params['apply_id']);
            if($apply->status != "1"){
                DB::rollBack();
                return true;
            }
            $isPass = $params['is_pass'] == "1" ? 3 : 2;
            $data['collection_id'] = $apply->collection_id;
            $data['deduction_time'] = [date("Y-m-d H:i:s"), date("Y-m-d H:i:s", strtotime("+100 days"))];
            $data['deduction'] = $apply->deduction;
            $data['status'] = $isPass;
            if ($apply->settle == CollectionDeductionApply::SETTLE_YES) {
                $order = Order::model()->getById($apply->order_id);
                $apply->deduction = $order->repayAmount();
            }
            if ($params['is_pass'] == 1) {
                $res = $this->create($data);
                if ($res) {
                    $collection = $apply->collection;
                    $this->credited($collection, $apply->deduction);
                }
            }
            $apply->status = $isPass;
            $apply->approval_admin_id = LoginHelper::getAdminId();
            $res = $apply->save();
            DB::commit();
            return $res;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

}
