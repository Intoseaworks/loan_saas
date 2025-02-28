<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Coupon;

use Admin\Services\Coupon\CouponReceiveServer;
use Admin\Services\Coupon\CouponTaskServer;
use Carbon\Carbon;
use Common\Models\Coupon\Coupon;
use Common\Models\Coupon\CouponReceive;
use Common\Models\Coupon\CouponTask;
use Common\Models\Crm\Customer;
use Common\Models\User\User;
use Common\Models\User\UserBlack;
use Common\Services\Crm\CustomerServer;
use Common\Utils\Data\DateHelper;
use Illuminate\Console\Command;

class IssueUFour extends Command {
    # 每次提取任务数

    const PER_NUM = 10000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon:issueUFour';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发放优惠券u4';

    public function handle() {
        /** 扫描用户表 */
        $this->issueCoupon();
        echo "End";
    }

    private function issueCoupon($page = 1) {
        echo "start " . $page * self::PER_NUM . PHP_EOL;
        $taskQuery = CouponTask::model()->newQuery()->join('coupon','coupon_task.coupon_id','=','coupon.id')
            ->where('coupon.merchant_id',2);
        //无需领取的优惠券才自动发,生效的任务
        $taskQuery = $taskQuery->where('get_way',1)->where('coupon_task.status',1);
//        $taskQuery = $taskQuery->where('get_way',1);
        $taskQuery = $taskQuery->orderByDesc("coupon_task.id")->paginate(self::PER_NUM, ['coupon_task.*'], 'page', $page);
//        $taskQuery = $taskQuery->paginate(self::PER_NUM, ['*'], 'page', $page);
        foreach($taskQuery->items() as $task){
            echo ".";
            $this->sendCoupon($task);
        }
        # 继续直到最有一页
        if ($taskQuery->lastPage() > $page) {
            $this->issueCoupon($page + 1);
        }
    }

    private function sendCoupon($task) {
        $params['coupon_id'] = $task->coupon_id;
        $coupon = Coupon::model()->newQueryWithoutScopes()->where('id',$params['coupon_id'])->first();
//        if ($task->id == 61){
//            print_r($coupon);
//        }
        //$customers其实是customerstatus
//        echo $coupon->merchant_id;
        $customers = CouponTaskServer::server()->getCustomers($task,$coupon->merchant_id);
//        echo count($customers);
//        exit;
        $params['coupon_task_id'] = $task->id;
        foreach ($customers as $customer) {
            if ( 0 == $customer->main_user_id ) {
                echo 'user_id为0'.PHP_EOL;
                continue;
            }
            $params['user_id'] = $customer->main_user_id;
            $user = User::model()->getOne($params['user_id']);
            if (!$user) {
                //用户不存在
                continue;
            }
            if ($coupon && $user && $coupon->merchant_id != $user->merchant_id ) {
                //品牌不一样,跳过
                continue;
            }
            echo 'user_id为'.$customer->main_user_id.PHP_EOL;
            echo $coupon->merchant_id.'--'.$user->merchant_id;
            $realCustomer = Customer::model()->getOne($customer->customer_id);
            //黑名单排除
            if ( $task->is_blacklist ) {
                $res = \Admin\Services\Crm\CustomerServer::server()->isBlack($realCustomer);
                if ($res) {
                    echo '黑名单'.PHP_EOL;
                    continue;
                }
            }
            //灰名单排除
            if ( $task->is_graylist ) {
                if (UserBlack::model()->isActive()->whereTelephone($realCustomer->telephone)->exists()) {
                    echo '灰名单'.PHP_EOL;
                    continue;
                }
            }
            //状态停留超过多少天发放
            if ($user) {
                $stopDays = CustomerServer::server()->getCrmCustomer($user)->getStatusStopDays();
                if ( 2 == $task->grant_type && $stopDays <= $task->grant_type_scope ) {
                    echo '状态停留不符和要求'.PHP_EOL;
                    continue;
                }
            }
            //生日当天发放
            if ( 3 == $task->grant_type && DateHelper::format($realCustomer->birthday,'m-d') != DateHelper::format(Carbon::now()->toDateString(),'m-d') ) {
                echo '生日当天不符和要求'.PHP_EOL;
                continue;
            }
            //特定日期发放
            if ( 4 == $task->grant_type && Carbon::parse($task->grant_type_scope)->toDateString() != Carbon::now()->toDateString() ) {
                echo $task->id.PHP_EOL;
                echo '特定日期发放不符和要求'.PHP_EOL;
                continue;
            }
            //当前全品牌最大逾期天数限定
            if ( null !== $task->max_overdue_day && $customer->max_overdue_days > $task->max_overdue_day ) {
                echo '当前全品牌最大逾期天数限定不符和要求'.PHP_EOL;
                continue;
            }
            //最后登录时间距今限定
            $customer->last_login_time = CustomerServer::server()->getLastLoginTime($user->id);
            if ( !empty(trim($task->last_login_limit_scope)) && !empty($customer->last_login_time ) ) {
                $days = explode('-',trim($task->last_login_limit_scope));
                if ( $days[1]>0 ) {
                    $current = Carbon::now();
                    $loginDay = $current->diffInSeconds($customer->last_login_time);         // 6
                    if ( ($loginDay < $days[0]*24*3600 || $loginDay > $days[1]*24*3600) ) {
                        echo '最后登录时间距今限定不符和要求'.PHP_EOL;
                        continue;
                    }
                }
            }
            //批次问题边界
            if ( $customer->batch_id  == 0 && is_array($task->batch) && count($task->batch) ) {
                echo '首贷普通名单,首贷白名单,批次不符和要求,任务有批次,客户不在批次内'.PHP_EOL;
                continue;
            }
            //首贷普通名单,首贷白名单,批次
            if ( $customer->batch_id >0 && (1 == $task->customer_group || 2 == $task->customer_group) ) {
                if ( !in_array($customer->batch_id,$task->batch) ) {
                    echo '首贷普通名单,首贷白名单,批次不符和要求'.PHP_EOL;
                    continue;
                }
            }
            $res = CouponReceive::model()->where('coupon_id',$params['coupon_id'])->where('user_id',$params['user_id'])->where('coupon_task_id',$params['coupon_task_id'])->count();
            if ($res) {
                echo '已发放优惠券'.PHP_EOL;
                continue;
            }
            if ($coupon && $user && $coupon->merchant_id == $user->merchant_id ) {
                CouponReceiveServer::server()->addCouponReceive($params);
            }
        }
    }

}
