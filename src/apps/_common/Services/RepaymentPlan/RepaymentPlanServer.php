<?php


namespace Common\Services\RepaymentPlan;


use Carbon\Carbon;
use Common\Models\Order\Order;
use Common\Models\Order\RepaymentPlan;
use Common\Services\BaseService;

class RepaymentPlanServer extends BaseService
{
    /**
     * 获取逾期订单
     * @return Order|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function overdue()
    {
        /** 当天零点时间 */
        $zeroOfDay = Carbon::now()->toDateString();
        $query = RepaymentPlan::query();
        return $query->where('repay_time', '=', null)
            ->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)
            ->where('appointment_paid_time', '<', (string)$zeroOfDay)->orderByDesc("id");
    }
    
    public function unFinish() {
        $query = RepaymentPlan::query();
        return $query->where("installment_num", "1")
                ->where("overdue_days", "<" ,40)
                ->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)->orderByDesc("id");
    }

    public function all(){
        $query = RepaymentPlan::query();
        return $query->where("installment_num", "1")->where("principal_total", 0)->orderByDesc("id");
    }
}
