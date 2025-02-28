<?php

namespace Api\Services\Coupon;

use Admin\Services\Repayment\RepaymentPlanServer;
use Api\Services\BaseService;
use Carbon\Carbon;
use Common\Models\Coupon\CouponReceive;
use Api\Models\Coupon\Coupon;
use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Models\Trade\TradeLog;
use Common\Models\Repay\RepayDetail;
use Common\Services\Repay\RepayServer;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\DingDing\DingHelper;
use Admin\Services\Repayment\ManualRepaymentServer;
use Common\Utils\Lock\LockRedisHelper;
use Illuminate\Support\Facades\DB;

class CouponServer extends BaseService {

    public function checking($userId, $couponId, $orderId) {
        $order = \Admin\Models\Order\Order::model()->getOne(User::model()->getOne($userId)->order->id);
        //防止用户多次点击按钮或调用接口
        if (!LockRedisHelper::helper()->addLock('checking-coupon-usage'.$userId.$couponId.$order->id,3)){
            if(\Common\Utils\MerchantHelper::getMerchantId() != \Common\Models\Merchant\Merchant::getId('c1')){
                return "The order is using coupon now ,please wait!";
            }
        }
        $coupon = Coupon::model()->query()->where("id", $couponId)->first();
        if (!$coupon){
            return "The coupon does not exist";
        }
//        if ( !in_array($order->status,[Order::STATUS_SYSTEM_PAID,Order::STATUS_MANUAL_PAID,Order::STATUS_REPAYING,Order::STATUS_OVERDUE]) ){
//            return "Only repaying order can use coupon!";
//        }
        //优惠券逾期是否可以使用限制
        if (2 == $coupon->overdue_use && 0 < $order->getOverdueDays()){
            return "The order has overdued already,this coupon can't be used for overdued order!";
        }
        DB::beginTransaction();
        try {
            $receive = CouponReceive::model()->query()
                ->where("user_id", $userId)
                ->where("order_id",$order->id)->first();
            if ($receive) {
                if($order->merchant_id != \Common\Models\Merchant\Merchant::getId('c1')){
                    //限制使用一张优惠券
                    return "The order has used a coupon already,only one coupon can be used!";
                }
            }

            $receive = CouponReceive::model()->query()
                            ->where("user_id", $userId)
                            ->where("coupon_id", $couponId)
                            ->whereNull("order_id")->first();
            if ($receive) {
                $user = User::model()->getOne($userId);
                if ($coupon) {
                    if ($coupon->coupon_type == 2 && $coupon->usage == 2 && ($coupon->with_amount > $order->getPartRepayAmount())) {
                        return "The order you partly repayed amount does not meet the coupon usage conditions";
                    }
                    //优惠券有效期以发券时间为准
                    if ((new Carbon)->diffInSeconds(Carbon::parse($receive->created_at)->addDays($coupon->effectivedays),false) < 0) {
                        return "The coupon has expired.";
                    }
                    if ($coupon->status != '1') {
                        return "The coupon status is not available.";
                    }
                    if ($order->status == Order::STATUS_OVERDUE && $coupon->overdue_use == 2){
                        return "Overdue orders cannot use this coupon.";
                    }
                    if (!$order->firstProgressingRepaymentPlan){
                        return "repaymentPlan is not exist!";
                    }
                    $receive->order_id = $order->id;
                    $receive->use_time = date("Y-m-d H:i:s");
                    $deduction = 0;
                    switch($coupon->coupon_type){
                        case 1:
                            $deduction = $order->interestFee();
                            if ($deduction <= 0){
                                return 'no interestFee need to be repayed!';
                            }
                            break;
                        case 2:
                            $deduction = $coupon->used_amount;
                            break;
                    }
                    $res = $receive->save();
                    if ($res) {
                        $tradeParams = [
                            'remark' => '优惠券',
                            'repay_name' => $user->fullname,
                            'repay_telephone' => $user->telephone,
                            'repay_account' => '',
                            'repay_time' => date('Y-m-d H:i:s'),
                            'repay_channel' => TradeLog::TRADE_PLATFORM_MANUAL_DEDUCTION,
                            'repay_amount' => $deduction
                        ];
                        $trade = ManualRepaymentServer::server()->addRepayTradeLog($order, TradeLog::TRADE_PLATFORM_MANUAL_DEDUCTION, $deduction, $tradeParams);
                        # 还款计划减免
                        RepaymentPlanServer::server()->updateDeductionFee($order->lastRepaymentPlan, $deduction, date("Y-m-d H:i:s"), date("Y-m-d H:i:s", time()+86400*180));
//                        还款详情在后面会有处理completeRepay()
//                        $repayDetail = [
//                            'uid' => $userId,
//                            'trade_id' => $trade->id,
//                            'order_id' => $orderId,
//                            'repayment_plan_id' => $order->firstProgressingRepaymentPlan->id,
//                            'certificate' => $trade->transaction_no,
//                            'origin_data' => json_encode([
//                                'repayment_plan' => $order->firstProgressingRepaymentPlan
//                            ]),
//                            'appointment_paid_time' => $order->firstProgressingRepaymentPlan->appointment_paid_time,
//                            'actual_paid_time' => $trade->trade_result_time,
//                            'overdue_days' => $order->getOverdueDays(),
//                            'status' => RepayDetail::STATUS_IS_VALID,
//                            'paid_amount' => $trade->trade_amount,
//                            'repay_type' => RepayDetail::REPAY_TYPE_COUPON,
//                        ];
//                        RepayDetail::model(RepayDetail::SCENARIO_CREATE)->add($repayDetail);
                    }
                    //不要判断是否还款结清,直接把这个交易加入计算
                    DB::commit();
                    $result = RepayServer::server($order->firstProgressingRepaymentPlan, $trade)->completeRepay(false,true);
                    if (!$result) {
                        return 'coupon completeRepay failed!';
                    }
//                        return 'coupon completeRepay success submit!';
                    return true;
                }
            } else {
                return "The coupon does not exist or has been used";
            }
        } catch (\Exception $e) {
            dd($e->getFile().PHP_EOL.$e->getLine().PHP_EOL.$e->getMessage());
            DB::rollBack();
            DingHelper::notice("userID:$userId couponID:$couponId;oid{$order->id}", 'Coupon 消费失败');
            exit();
        }
    }

    public function checkingOrderSign($userId, $couponId, $orderId) {
        $order = \Admin\Models\Order\Order::model()->getOne(User::model()->getOne($userId)->order->id);
        //防止用户多次点击按钮或调用接口
        if (!LockRedisHelper::helper()->addLock('checking-coupon-usage'.$userId.$couponId.$order->id,3)){
            return "The order is using coupon now ,please wait!";
        }
        $coupon = Coupon::model()->query()->where("id", $couponId)->first();
        if (!$coupon){
            return "The coupon does not exist";
        }
//        if ( !in_array($order->status,[Order::STATUS_SYSTEM_PAID,Order::STATUS_MANUAL_PAID,Order::STATUS_REPAYING,Order::STATUS_OVERDUE]) ){
//            return "Only repaying order can use coupon!";
//        }
        //优惠券逾期是否可以使用限制
        if (2 == $coupon->overdue_use && 0 < $order->getOverdueDays()){
            return "The order has overdued already,this coupon can't be used for overdued order!";
        }
        try {
            $receive = CouponReceive::model()->query()
                ->where("user_id", $userId)
                ->where("order_id",$order->id)->first();
            if ($receive) {
                return "The order has used a coupon already,only one coupon can be used!";
            }

            $receive = CouponReceive::model()->query()
                ->where("user_id", $userId)
                ->where("coupon_id", $couponId)
                ->whereNull("order_id")->first();
            if ($receive) {
//                $user = User::model()->getOne($userId);
                if ($coupon) {
                    if ($coupon->coupon_type == 2 && $coupon->usage == 2 && ($coupon->with_amount > $order->getPartRepayAmount())) {
                        return "The order you partly repayed amount does not meet the coupon usage conditions";
                    }
                    //优惠券有效期以发券时间为准
                    if ((new Carbon)->diffInSeconds(Carbon::parse($receive->created_at)->addDays($coupon->effectivedays),false) < 0) {
                        return "The coupon has expired.";
                    }
                    if ($coupon->status != '1') {
                        return "The coupon status is not available.";
                    }
                    if ($order->status == Order::STATUS_OVERDUE && $coupon->overdue_use == 2){
                        return "Overdue orders cannot use this coupon.";
                    }
                    $receive->order_id = $order->id;
                    $receive->use_time = date("Y-m-d H:i:s");
                    $res = $receive->save();
                    if ($res) {
                        return true;
                    }
                }
            } else {
                return "The coupon does not exist or has been used";
            }
        } catch (\Exception $e) {
            dd($e->getFile().PHP_EOL.$e->getLine().PHP_EOL.$e->getMessage());
            DingHelper::notice("userID:$userId couponID:$couponId;oid{$order->id}", 'Coupon 消费失败');
            exit();
        }
    }

    public function myCoupon($userId) {
        $myCouponReceive = CouponReceive::model()->query()->where(['user_id' => $userId])->get();
        $res = [];
        foreach ($myCouponReceive as $receive) {
            $coupon = Coupon::model()->query()->where("id", $receive->coupon_id)->first();
            if ($coupon){
                $coupon = $coupon->toArray();
                $coupon['used'] = $receive->order_id ? 1 : 0;
                $coupon['use_time'] = $receive->use_time;
                $coupon['end_time'] = Carbon::parse($receive->created_at)->addDays($coupon['effectivedays'])->toDateTimeString();
                $coupon['coupon_id'] = $receive->id;
                if ($coupon['status']!=1){
                    $coupon['status']=0;
                }else{
                    $coupon['status'] = $coupon['end_time'] < date('Y-m-d H:i:s') ? 0 :1;
                }
                $res[] = $coupon;
            }
        }
        return $res;
    }

    public function myEffectiveCoupon($userId,$params) {
        $myCouponReceive = CouponReceive::model()->query()->where(['user_id' => $userId])->whereNull('order_id')
            ->where('use_time','<','2021-01-01 00:00:00')->get();
        $res = [];
        foreach ($myCouponReceive as $k => $receive) {
            $coupon = Coupon::model()->query()->where("id", $receive->coupon_id)->where('status',1)->first();
            if ($coupon){
                $loan_amount = intval(array_get($params,'loan_amount'));
                //满多少贷款金额才可用的优惠券才有效,满减定义为单笔订单最低还款为多少才可以用
//                 if ($loan_amount && $coupon->coupon_type == 2 && $coupon->usage == 2 && ($coupon->with_amount > $loan_amount)) {
                 if ( $coupon->coupon_type == 2 && $coupon->usage == 2 ) {
                     continue;
                 }
                $coupon = $coupon->toArray();
                $coupon['end_time'] = Carbon::parse($receive->created_at)->addDays($coupon['effectivedays'])->toDateTimeString();
                $coupon['coupon_id'] = $receive->id;
                $coupon['status'] = $coupon['end_time'] < date('Y-m-d H:i:s') ? 0 :1;
                if ($coupon['status']) {
                    $res[] = $coupon;
                }
            }
        }
        return $res;
    }

}
