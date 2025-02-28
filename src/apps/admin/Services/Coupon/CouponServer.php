<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Coupon;

use Admin\Models\Order\Order;
use Admin\Models\User\User;
use Admin\Services\BaseService;
use Admin\Services\Repayment\ManualRepaymentServer;
use Admin\Services\Repayment\RepaymentPlanServer;
use Carbon\Carbon;
use Common\Models\Coupon\Coupon;
use Common\Models\Coupon\CouponTask;
use Common\Models\Trade\TradeLog;
use Common\Services\Repay\RepayServer;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Third\AirudderHelper;
use phpDocumentor\Reflection\Types\Null_;
use Risk\Common\Models\Third\ThirdDataAirudder;
use function array_get;

class CouponServer extends BaseService {

    public function list($params) {
        $size = array_get($params, 'size');
        $query = Coupon::model()->newQuery()->join('staff','coupon.create_user','=','staff.id');
        if (isset($params['title']) && trim($params['title'])) {
            $query->where("coupon.title",'like', '%'.$params['title'].'%');
        }
        if (isset($params['coupon_type'])) {
            $query->where("coupon.coupon_type", $params['coupon_type']);
        }
        if (isset($params['status'])) {
            $query->where("coupon.status", $params['status']);
        }
        if (isset($params['overdue_use'])) {
            $query->where("coupon.overdue_use", $params['overdue_use']);
        }
        $query->select(['coupon.*','staff.username']);
        $query->orderByDesc("coupon.id");
        return $query->paginate($size);
    }

    public function listAll($merchant_id=null) {
        if ( $merchant_id ){
            $model = Coupon::model()->newQueryWithoutScopes()->whereMerchantId($merchant_id);
        }else{
            $model = Coupon::model()->newQuery();
        }
        $query = $model->where("coupon.status", 1)->where('end_time','>',Carbon::now()->toDateTimeString());
        return $query->orderByDesc("id")->get(['id','title','end_time','created_at']);
    }

    public function view($id) {
        $query = Coupon::model()->newQuery()->where("id", $id)->first();
        return $query;
    }

    public function addCoupon($params) {
        $res = Coupon::model()->createModel([
            "title" => $params['title'],
            "coupon_type" => $params['coupon_type'],
            "end_time" => Carbon::now()->addDays($params['end_time'])->toDateTimeString(),
            "used_amount" => $params['used_amount'] ?? 0,
            "usage" => $params['usage'] ?? null,
            "with_amount" => $params['with_amount'] ?? 0,
            "overdue_use" => $params['overdue_use'],
//            "status" => $params['status'],
            "update_user" => LoginHelper::getAdminId(),
            "create_user" => LoginHelper::getAdminId(),
            'merchant_id' => MerchantHelper::getMerchantId(),
            'app_id' => MerchantHelper::getAppId()
        ]);
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function setCoupon($params){
        $coupon = Coupon::model()->getOne($params['id']);
        if($coupon){
            $params['update_user'] = LoginHelper::getAdminId();
            if (isset($params['end_time'])) {
                $params['end_time'] = Carbon::now()->addDays($params['end_time'])->toDateTimeString();
            }
            if ( $coupon->saveModel($params) ) {
                //停用优惠券才关联停用券任务
                if (isset($params['status']) && 2 == $params['status'] ) {
                    CouponTask::where('coupon_id',$params['id'])->update(['status'=>$params['status'],'update_user'=>LoginHelper::getAdminId()]);
                }
                return $this->outputSuccess();
            }else {
                return $this->outputError("Record update error");
            }
        }else{
            return $this->outputError("Record does not exist");
        }
    }

    public function checking($user, $receive, $order) {
        $coupon = $receive->coupon;
        \DB::beginTransaction();
        try {
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
                    //不要判断是否还款结清,直接把这个交易加入计算
                    \DB::commit();
                    $result = RepayServer::server($order->firstProgressingRepaymentPlan, $trade)->completeRepay(false,true);
                    if (!$result) {
                        return 'coupon completeRepay failed!';
                    }
//                        return 'coupon completeRepay success submit!';
                    return true;
        } catch (\Exception $e) {
            dd($e->getFile().PHP_EOL.$e->getLine().PHP_EOL.$e->getMessage());
            DB::rollBack();
            DingHelper::notice("userID:$user->id couponID:$coupon->id;oid$order->id", 'Coupon 消费失败');
            exit();
        }
    }
}
