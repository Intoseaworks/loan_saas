<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-17
 * Time: 18:51
 */

namespace Approve\Admin\Services;

use Common\Interfaces\BaseApproveHandlerInterface;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApprovePoolLog;
use Common\Models\Common\Pincode;
use Common\Models\Order\Order;
use Common\Models\Order\OrderLog;
use Common\Models\Staff\Staff;
use Common\Models\User\User;
use Common\Traits\GetInstance;
use Illuminate\Support\Facades\Redis;

class CommonService
{
    use GetInstance;

    /**
     * @var string
     */
    const REDIS_KEY_START_CHECK = 'APPROVE_START:';

    /**
     * @var null| \Illuminate\Redis\Connections\Connection
     */
    protected $redis = null;

    /**
     * @param $poolId
     * @return array
     */
    public function getApproveResult($poolId)
    {
        $data = [];
        // 审批页面 1.初审待审批/审批中 2.初审通过机审拒绝 3.初审回退 4.初审拒绝 5.初审关闭 6.初审取消 7.电审待审批/审批中 8.电审通过机审拒绝 9.电审通过 11.电审拒绝 12.电审关闭 13.电审取消
        $textList = [
            ApprovePool::ORDER_FIRST_GROUP => [
                ApprovePool::STATUS_WAITING => ['First approval', 1],
                ApprovePool::STATUS_NOT_CONDITION => ['First approval', 1],
                ApprovePool::STATUS_CHECKING => ['In approval', 1],
                ApprovePool::STATUS_RETURN => ['First approval Return', 3],
                ApprovePool::STATUS_REJECT => ['First approval Reject', 4],
                ApprovePool::STATUS_CLOSED => ['First approval Closed', 5],
                ApprovePool::STATUS_CANCEL => ['First approval Cancel', 6],
            ],
            ApprovePool::ORDER_CALL_GROUP => [
                ApprovePool::STATUS_WAITING => ['Call approval', 7],
                ApprovePool::STATUS_NOT_CONDITION => ['Call approval', 7],
                ApprovePool::STATUS_CHECKING => ['In approval', 7],
                ApprovePool::STATUS_PASS => ['Call approval Finish', 9],
                ApprovePool::STATUS_REJECT => ['Call approval Reject', 11],
                ApprovePool::STATUS_CLOSED => ['Call approval Closed', 12],
                ApprovePool::STATUS_CANCEL => ['Call approval Cancel', 13],
            ],
        ];

        $approvePool = ApprovePool::where('id', $poolId)->first();
        $approvePoolLog = $approvePool->approvePoolLog;
        $data['approveResult'] = $textList[$approvePool->type][$approvePool->status][0] ?? '';
        $data['status'] = $textList[$approvePool->type][$approvePool->status][1] ?? 0;
        if ($approvePoolLog->exists) {
            // 初审机审拒绝
            if (in_array($approvePoolLog->result, [ApprovePoolLog::FIRST_APPROVE_RESULT_RISK_REJECT, ApprovePoolLog::CALL_APPROVE_RESULT_RISK_REJECT])) {
                $data['approveResult'] = 'System reject';
                if ($approvePoolLog->result == ApprovePoolLog::FIRST_APPROVE_RESULT_RISK_REJECT) {
                    $data['status'] = 2;
                }
                if ($approvePoolLog->result == ApprovePoolLog::CALL_APPROVE_RESULT_RISK_REJECT) {
                    $data['status'] = 8;
                }
            }
        }

        $data['orderNo'] = $approvePool->order_no;
        $data['confirm_income'] = \Common\Models\User\UserInfo::model()->where("user_id",$approvePool->user_id)->first()->confirm_month_income ?? "---";
        return $data;
    }

    /**
     * @param $poolId
     * @param $snapshoot
     * @return mixed
     */
    public function getSnapShootApproveResultByPoolId($poolId, &$snapshoot)
    {
        $approvePool = ApprovePool::where('id', $poolId)->first();

        // 订单关闭
        if ($approvePool->status == ApprovePool::STATUS_CLOSED) {
            if ($approvePool->type == ApprovePool::ORDER_FIRST_GROUP) {
                if (isset($snapshoot['firstDetail']['approveResult'])) {
                    $snapshoot['firstDetail']['approveResult'] = 'First approval Closed - No one approval';
                }
            }
            if ($approvePool->type == ApprovePool::ORDER_CALL_GROUP) {
                if (isset($snapshoot['callDetail']['approveResult'])) {
                    $snapshoot['callDetail']['approveResult'] = 'Call approval Closed - No one approval';
                }
            }
        }

        // 订单取消
        if ($approvePool->status == ApprovePool::STATUS_CANCEL) {
            if ($approvePool->type == ApprovePool::ORDER_FIRST_GROUP) {
                if (isset($snapshoot['firstDetail']['approveResult'])) {
                    $snapshoot['firstDetail']['approveResult'] = 'First approval Cancel - Users Cancel Orders';
                }
            }
            if ($approvePool->type == ApprovePool::ORDER_CALL_GROUP) {
                // 电审审批的时候有个取消选项,这里的状态要跟那里的区分开
                if (isset($snapshoot['callDetail']['approveResult']) && $approvePool->approvePoolLog->exists) {
                    if (in_array($approvePool->approvePoolLog->result, ApprovePoolLog::CANCEL_COLLECTION)) {
                        $snapshoot['callDetail']['approveResult'] = 'Call approval Cancel - Users Cancel Orders';
                    }
                }
            }
        }

        return $snapshoot;
    }

    /**
     * @param $userPoolId
     * @param $snapshoot
     * @return mixed
     */
    public function getSnapShootApproveResultByUserPoolId($userPoolId, &$snapshoot)
    {
        $poolLog = ApprovePoolLog::where('approve_user_pool_id', $userPoolId)->first();

        if ($poolLog) {
            // 订单关闭
            if ($poolLog->result == ApprovePoolLog::RESULT_CRONTAB_CANCEL) {
                if ($poolLog->type == ApprovePool::ORDER_FIRST_GROUP) {
                    if (isset($snapshoot['firstDetail']['approveResult'])) {
                        $snapshoot['firstDetail']['approveResult'] = 'First approval Closed - No one approval';
                    }
                }
                if ($poolLog->type == ApprovePool::ORDER_CALL_GROUP) {
                    if (isset($snapshoot['callDetail']['approveResult'])) {
                        $snapshoot['callDetail']['approveResult'] = 'Call approval Closed - No one approval';
                    }
                }
            }

            // 订单取消
            if (in_array($poolLog->result, ApprovePoolLog::CANCEL_COLLECTION)) {
                if ($poolLog->type == ApprovePool::ORDER_FIRST_GROUP) {
                    if (isset($snapshoot['firstDetail']['approveResult'])) {
                        $snapshoot['firstDetail']['approveResult'] = 'First approval Cancel - Users Cancel Orders';
                    }
                }
                if ($poolLog->type == ApprovePool::ORDER_CALL_GROUP) {
                    if (isset($snapshoot['callDetail']['approveResult'])) {
                        $snapshoot['callDetail']['approveResult'] = 'Call approval Cancel - Users Cancel Orders';
                    }
                }
            }
        }

        return $snapshoot;
    }

    /**
     * 获取业务用户ID
     *
     * @param $username
     * @return array
     */
    public function getUserId($username)
    {
        $username = trim($username);
        if ($username) {
            return User::select(['id'])
                ->when(!empty($username), function ($query) use ($username) {
                    $query->where('fullname', 'like', "%{$username}%");
                    $query->orWhere('telephone', 'like', "%$username%");
                })
                ->get()
                ->pluck('id')
                ->toArray();
        }

        return [];
    }

    /**
     * 获取业务用户信息
     *
     * @param $userIds
     * @return array
     */
    public function getUserInfo($userIds)
    {
        if ($userIds) {
            return User::whereIn('id', $userIds)
                ->select(['telephone', 'fullname', 'id'])
                ->get()
                ->keyBy('id')
                ->toArray();
        }

        return [];
    }

    /**
     * 获取业务审批人员Id
     *
     * @param $username
     * @return array
     */
    public function getAdminUserId($username)
    {
        $username = trim($username);
        if ($username) {
            return Staff::select(['id'])
                ->when(!empty($username), function ($query) use ($username) {
                    $query->where('username', 'like', "%{$username}%");
                    $query->orWhere('nickname', 'like', "%$username%");
                })
                ->get()
                ->pluck('id')
                ->toArray();
        }

        return [];
    }

    /**
     * 获取业务审批人员信息
     *
     * @param $adminUserIds
     * @return array
     */
    public function getAdminUserInfo($adminUserIds)
    {
        if ($adminUserIds) {
            return Staff::whereIn('id', $adminUserIds)
                ->select(['username', 'nickname', 'id'])
                ->get()
                ->keyBy('id')
                ->toArray();
        }

        return [];
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
     * @param $first
     * @param $second
     * @return float|int|string
     */
    public function getNextDay($first, $second)
    {
        if ($first && $second && $first == $second) {
            return $second + 24 * 60 * 60 - 2;
        }

        return $second;
    }

    /**
     * 获取订单的真是状态,订单回退后状态会变为39.用户补充资料会变为26.这里要获取用户补充资料前的状态
     *
     * @param Order $order
     * @return Order
     */
    public function getRealOrderStatus(Order $order)
    {
        if ($order->status == Order::STATUS_WAIT_MANUAL_APPROVE) {
            // 查看是否是待补充资料的
            $count = OrderLog::where('order_id', $order->id)
                ->where('from_status', Order::STATUS_REPLENISH)
                ->where('to_status', Order::STATUS_WAIT_MANUAL_APPROVE)
                ->count();
            // 状态26是从39流转的(39->26)
            if ($count > 0) {
                $order->_status = Order::STATUS_REPLENISH;
            } else {
                $order->_status = Order::STATUS_WAIT_MANUAL_APPROVE;
            }
        } else {
            $order->_status = $order->status;
        }

        return $order;
    }

    /**
     * @return mixed|string
     */
    public function orderStatus($status)
    {
        $list = [
            Order::STATUS_WAIT_MANUAL_APPROVE => t('待初审', 'approve'),
            Order::STATUS_REPLENISH => t('待初审-已补充资料', 'approve'),
            Order::STATUS_WAIT_TWICE_CALL_APPROVE => t('待电审二审', 'approve'),
            Order::STATUS_WAIT_CALL_APPROVE => t('待电审', 'approve'),
            Order::STATUS_MANUAL_PASS => t('人审通过待签约', 'approve'),
            Order::STATUS_MANUAL_CANCEL => t('人工取消', 'approve'),
            Order::STATUS_MANUAL_REJECT => t('人工已拒绝', 'approve'),
        ];

        return $list[$status] ?? $status;
    }

    /**
     * @param $pincode
     * @return mixed
     */
    public function getAddress($pincode, $user = '', $addressType = '')
    {
        /** @var User $user */
        // 永久地址自动填充
        if($addressType == 'permanent' && $user && $user->userInfo->permanent_province && $user->userInfo->permanent_city && $user->userInfo->permanent_pincode){
            $address = [
                'pincode' => $user->userInfo->permanent_pincode,
                'city' => $user->userInfo->permanent_province . '/' . $user->userInfo->permanent_city,
                'exist' => true,
            ];
            return $address;
        }
        // 居住地址自动填充
        if($addressType == 'current' && $user && $user->userInfo->province && $user->userInfo->city && $user->userInfo->pincode){
            $address = [
                'pincode' => $user->userInfo->pincode,
                'city' => $user->userInfo->province . '/' . $user->userInfo->city,
                'exist' => true,
            ];
            return $address;
        }

        $data = optional(Pincode::model()->getPincodeData($pincode))
            ->toArray();
        $address = [
            'pincode' => array_get($data, 'pincode', ''),
            'city' => array_get($data, 'statename', '') . '/' . array_get($data, 'districtname', ''),
            'exist' => array_get($data, 'type', 1) == Pincode::TYPE_OFFICIAL ? true : false,
        ];

        if ($address['city'] == '/') {
            $address['city'] = '';
        }

        return $address;
    }

    /**
     * @param $pincode
     * @return mixed
     */
    public function getAddressDetail($pincode)
    {
        return optional(Pincode::model()->getPincodeData($pincode))
            ->toArray();
    }

    /**
     * @return float
     */
    public function getOrderVersion()
    {
        if (strtotime('2019-04-08 12:24:03') < time()) {
            return 1.5;
        } else {
            return 1.4;
        }
    }

    /**
     * @param $userPoolId
     * @return string
     */
    public function getStartCheckRedisKey($userPoolId)
    {
        return static::REDIS_KEY_START_CHECK . $userPoolId;
    }
}
