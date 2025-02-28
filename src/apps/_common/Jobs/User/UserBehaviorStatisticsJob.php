<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/31
 * Time: 10:55
 */

namespace Common\Jobs\User;

use Common\Jobs\Job;
use Common\Models\Order\Order;
use Common\Models\UserData\UserBehaviorStatistics;
use Common\Services\User\UserBehaviorServer;

class UserBehaviorStatisticsJob extends Job
{
//    public $queue = 'user-behavior-statistics';
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;
    /**
     * @var string|null
     */

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        if(in_array($this->order->merchant_id, [1])){
            $data = UserBehaviorServer::server()->getBehaviorStatisticsData($this->order->id);
            $data['user_id'] = $this->order->user_id;
            $data['order_id'] = $this->order->id;
            return UserBehaviorStatistics::model()->updateOrCreateModel($data, ['order_id'=>$data['order_id']]);
        }
        return;
    }
}
