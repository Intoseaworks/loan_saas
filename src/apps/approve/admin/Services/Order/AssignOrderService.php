<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-11
 * Time: 12:14
 */

namespace Approve\Admin\Services\Order;


use Approve\Admin\Services\Approval\ApproveService;
use Common\Exceptions\CustomException;
use Common\Models\Approve\ApproveAssignLog;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApproveUserPool;
use Common\Models\Config\Config;
use Common\Models\Order\Order;
use Common\Traits\GetInstance;
use Common\Utils\Email\EmailHelper;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class AssignOrderService
{
    use GetInstance;

    const REDIS_KEY_USER_EXPIRE = 'approve:user:expire:';

    /**
     * @var null| \Illuminate\Redis\Connections\Connection
     */
    protected $redis = null;

    public function assignOrder($userId, $type, $assigned=0)
    {
        //$this->checkApproveType($userId, $type);
        $data = [];
        $logData = [];
        // 人工审批 可获取审批单数
        $manualAllotCount = Config::getApproveManualAllotCount();
        //判断人工审批最大审批单数指每个审批账号名下审批中的订单数最大的数量。包括初审，电审，挂起
        $manualMaxCount = (int)Config::getApproveManualMaxCount();
        //与$manualAllotCount比较取较小值
//        $manualAllotCount = $manualMaxCount < $manualAllotCount ? $manualMaxCount:$manualAllotCount;
        $manualAllotCount -= $assigned;
        //人工审批最大审批单数-userId已有的审批单数  与 $manualAllotCount 取最小值才是需要真正取的补单数
        $manualMaxCount = (int)Config::getApproveManualMaxCount();
        $adminNowApproveCount = (new ApproveService)->getApproveOrderCount($userId);
        $manualMaxCount = $manualMaxCount - $adminNowApproveCount;
        $manualMaxCount = $manualMaxCount > 0 ? $manualMaxCount:0;
        $manualAllotCount = $manualMaxCount > $manualAllotCount ? $manualAllotCount:$manualMaxCount;
        /** @var ApprovePool[] $approveOrders */
        $approveOrders = $this->getApproveOrders(null, $manualAllotCount);

        $time = date('Y-m-d H:i:s');
        foreach ($approveOrders as $key => $order) {
            /** 补件用户返回审批时需分配给原审批员 */
            /* 20211102 没有补件流程了
            $lastApproveLog = $order->order->lastApprove;
            if ($lastApproveLog && $lastApproveLog->to_order_status == Order::STATUS_REPLENISH && $userId != $lastApproveLog->admin_id) {
                unset($approveOrders[$key]);
                continue;
            }
             */
            $temp = [
                'merchant_id' => $order->merchant_id,
                'admin_id' => $userId,
                'approve_pool_id' => $order->id,
                'order_id' => $order->order_id,
                'status' => ApproveUserPool::STATUS_CHECKING,
                'created_at' => $time,
                'updated_at' => $time,
            ];

            $tempLog = [
                'merchant_id' => $order->merchant_id,
                'approve_pool_id' => $order->id,
                'order_id' => $order->order_id,
                'admin_id' => $userId,
                'telephone' => $order->telephone,
                'from_order_status' => $order->order_status,
                'to_order_status' => 0,
                'approve_status' => ApproveAssignLog::STATUS_CHECKING,
                'created_at' => $time,
                'updated_at' => $time,
            ];

            $logData[] = $tempLog;
            $data[] = $temp;
        }

        DB::transaction(function () use ($data, $approveOrders, $logData) {
            ApproveUserPool::query()->insert($data);
            // 更新审批池审批状态
            foreach ($approveOrders as $approveOrder) {
                $approveOrder->status = ApprovePool::STATUS_CHECKING;
                $approveOrder->save();
            }
            $this->saveAssingLog($logData);
        });

        // 设置工作状态
        $this->setWork($userId, $type);
        return true;
    }

    /**
     * 检查用户审批状态,用户在同一时间只能审批一种类型的订单
     *
     * @param $userId
     * @param $type
     * @return bool
     * @throws CustomException
     */
    public function checkApproveType($userId, $type)
    {
        $redis = $this->getRedis();
        $redisKey = $this->getRediskey($userId);
        $userApproveType = $redis->get($redisKey);
        if ($userApproveType && $userApproveType != $type) {
            $text = 'Please stop the call approve';
            if ($userApproveType == ApprovePool::ORDER_FIRST_GROUP) {
                $text = 'Please stop the first approve';
            }
            throw new CustomException($text);
        }

        return true;
    }

    /**
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function getRedis()
    {
        if ($this->redis == null) {
            $this->redis = Redis::connection();
        }

        return $this->redis;
    }

    /**
     * @param $userId
     * @return string
     */
    public function getRediskey($userId)
    {
        return static::REDIS_KEY_USER_EXPIRE . $userId;
    }

    /**
     * @param $type
     * @param int $limit
     * @param int $offset
     * @return Collection
     */
    protected function getApproveOrders($type, $limit = ApprovePool::APPROVE_POLICY_NUM, $offset = 0)
    {
        // 避免递归过深
        if ($offset > 5) {
            EmailHelper::send('[审批系统] assign order 递归深度超过5次,请检查代码是否正常!');
            return collect();
        }

        // 获取审批单
        $records = ApprovePool::model()->getWaitingCheckList($limit, $offset, $type);
        $originCount = $records->count();
        // 去除已经用户审批池的单
        foreach ($records as $k => $record) {
            $exists = ApproveUserPool::where('order_id', $record->order_id)
                ->whereIn("status",[ApproveUserPool::STATUS_CHECKING, ApproveUserPool::STATUS_NO_ANSWER])->exists();
            if ($exists) {
                unset($record[$k]);
                continue;
            }
        }
        // 审批池已经没有订单
        if ($originCount < $limit) {
            return $records;
        }

        // 补单
        if ($originCount < $limit) {
            $nRecords = $this->getApproveOrders($type, $limit, ++$offset);
            $records->merge($nRecords)->slice(0, $limit);
        }

        return $records;
    }

    /**
     *
     * @param $data
     */
    protected function saveAssingLog($data)
    {
        ApproveAssignLog::query()->insert($data);
    }

    /**
     * 设置超时时间
     * @param $userId
     * @param $type
     */
    public function setWork($userId, $type)
    {
        $redis = $this->getRedis();
        // 电审过期时间
        $callExpired = ApprovePool::APPROVE_IS_ELECTRIC;
        // 非电审过期时间
        $normalExpired = ApprovePool::APPROVE_NOT_ELECTRIC;
        $redisKey = $this->getRediskey($userId);
        $redis->set($redisKey, $type);
        // 电审
        if ($type == ApprovePool::ORDER_CALL_GROUP) {
            $redis->expire($redisKey, $callExpired);
        } else {
            $redis->expire($redisKey, $normalExpired);
        }
    }

    /**
     * 停止审批
     * @param $userId
     * @param string $type
     * @return bool
     */
    public function stopWork($userId, $type = '')
    {
        $redis = $this->getRedis();
        if ($type && ($type != $redis->get($this->getRediskey($userId)))) {
            return true;
        }
        $redis->del($this->getRediskey($userId));
        //$this->checkExpired(ApproveUserPool::STATUS_STOP_WORK, $userId);
        return true;
    }

    /**
     * 检查审批人员过期时间
     * @param int $userPoolStatus
     * @param null|int $userId
     * @return mixed
     */
    public function checkExpired($userPoolStatus = ApproveUserPool::STATUS_BIND_TIMEOUT, $userId = null)
    {
        return DB::transaction(function () use ($userId, $userPoolStatus) {
            // 获取再审人员
            $workingUsers = ApproveUserPool::model()->getWorkingUser($userId);
            /** @var ApproveUserPool $workingUser */
            foreach ($workingUsers as $workingUser) {
                // 用户不存在
                if (!$this->checkUserExists($workingUser->admin_id)) {
                    // 更新审批单
                    $approveUserPools = ApproveUserPool::where([
                        'status' => ApproveUserPool::STATUS_CHECKING,
                        'admin_id' => $workingUser->admin_id
                    ])->get();
                    /** @var ApproveUserPool $approveUserPool */
                    foreach ($approveUserPools as $approveUserPool) {
                        if (!$approveUserPool) continue;

                        /** 挂起状态不进行过期处理 */
                        if ($approveUserPool->status == ApproveUserPool::STATUS_NO_ANSWER) continue;

                        $approveUserPool->status = $userPoolStatus;
                        $approveUserPool->save();

                        $approvePool = ApprovePool::where(['id' => $approveUserPool->approve_pool_id])->first();
                        if (!$approvePool) continue;
                        // 审批池订单状态更新
                        $approvePool->status = ApprovePool::STATUS_WAITING;
                        $approvePool->save();

                        // 更新日志
                        ApproveAssignLog::where([
                            'order_id' => $approveUserPool->order_id,
                            'admin_id' => $workingUser->admin_id,
                            'approve_status' => ApproveAssignLog::STATUS_CHECKING,
                        ])->update(['approve_status' => ApproveAssignLog::STATUS_CANCEL]);
                    }
                }
            }
            return true;
        });
    }

    /**
     * @param $userId
     * @return bool
     */
    public function checkUserExists($userId)
    {
        return $this->getRedis()->exists($this->getRediskey($userId)) ? true : false;
    }

    /**
     * 增加用户过期时间
     * @param $userId
     * @param $type
     */
    public function addTime($userId, $type = null)
    {
        return $this->setWork($userId, $type ?? $this->getApproveType($userId));
    }

    /**
     * 审批类型
     * @param $userId
     * @return string
     */
    public function getApproveType($userId)
    {
        return $this->getRedis()->get($this->getRediskey($userId));
    }
}
