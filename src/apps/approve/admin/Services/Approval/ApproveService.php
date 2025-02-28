<?php

/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-13
 * Time: 20:33
 */

namespace Approve\Admin\Services\Approval;

use Admin\Models\Order\Order;
use Admin\Models\User\User;
use Api\Models\Order\OrderDetail;
use Approve\Admin\Services\Log\ApproveLogService;
use Approve\Admin\Services\Order\AssignOrderService;
use Closure;
use Common\Exceptions\CustomException;
use Common\Models\Approve\ApproveAssignLog;
use Common\Models\Approve\ApprovePageStatistic;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApprovePoolLog;
use Common\Models\Approve\ApproveQuality;
use Common\Models\Approve\ApproveResultSnapshot;
use Common\Models\Approve\ApproveUserPool;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\Common\Dict;
use Common\Redis\Lock\LockRedis;
use Common\Utils\Code\ApproveStatusCode;
use Common\Utils\Code\OrderStatus;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\LoginHelper;
use Common\Models\Approve\ApproveCallLog;
use DB;
use Exception;
use Illuminate\Support\Facades\Redis;
use Log;
use Yunhan\Utils\Env;
use Common\Models\Config\Config;
use Approve\Admin\Services\Check\ApproveCheckService;
use Cache;

// @phan-file-suppress PhanTypeInvalidDimOffset
class ApproveService {

    /**
     * @var string
     */
    const REDIS_KEY_START_CHECK = 'APPROVE_START:';

    /**
     * @var string
     */
    const REDIS_KEY_LOCK = 'APPROVE_LOCK:';

    /**
     * @param $userId
     * @param $data
     * @return \Illuminate\Support\Collection
     * @throws CustomException
     */
    public function startWork($userId, $data) {
        $type = array_get($data, 'type', '');
        AssignOrderService::getInstance()->addTime(LoginHelper::getAdminId(), $type);
        $manualAllotCount = Config::getApproveManualAllotCount();
        $assignedCount = 0;
        // 检查是否还有未审批完的单
        if ($list = $this->getUnFinishOrder($userId)) {
            $assignedCount = count($list);
            if ($manualAllotCount <= $assignedCount) {
                return $list;
            }
        }
        $admin24hourOrderCount = $this->get24HOrderCount($userId);
        if ($admin24hourOrderCount) {
            AssignOrderService::getInstance()->setWork($userId, $type);
            return $this->getApproveOrder($userId, $data);
        }
        // 审批完拉取新单
        if (AssignOrderService::getInstance()->assignOrder($userId, $type, $assignedCount)) {
            // 获取审批单
            return $this->getApproveOrder($userId, $data);
        } else {
            throw new CustomException(t('分单失败'));
        }
    }

    /**
     * 检查是否还有未审批完的单
     *
     * @param $userId
     * @param $data
     * @return array|\Illuminate\Support\Collection
     * @throws CustomException
     */
    public function getUnFinishOrder($userId, $params = []) {
        // 检查用户订单池是否有订单
        $services = AssignOrderService::getInstance();
        if ($services->checkUserExists($userId)) {

//            $services->checkApproveType($userId, $type);
            // 检查用户是否还有未审批的单
            return $this->getApproveOrder($userId, $params);
        }

        return [];
    }

    /**
     * 获取审批员所有正在审批的单
     * @param $userId
     * @return int
     */
    public function getApproveOrderCount($userId, $params = []) {
        $approveUserPoolIds = ApproveUserPool::model()
                ->getCheckingList($userId)
                ->pluck('id', 'approve_pool_id')
                ->toArray();
        $listQuery = ApprovePool::query()->join('order', 'order.id', '=', 'approve_pool.order_id')->whereIn('order.status', Order::APPROVAL_PENDING_STATUS)
                ->whereIn('approve_pool.id', array_keys($approveUserPoolIds));
        $listCount = $listQuery->orderBy("order.signed_time")
                        ->select(\DB::raw('approve_pool.*,order.signed_time'))->count();
        return $listCount;
    }

    /**
     * @param $userId
     * @return array|ApprovePool[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    protected function getApproveOrder($userId, $params = []) {
        $approveUserPoolIds = ApproveUserPool::model()
                ->getCheckingList($userId)
                ->pluck('id', 'approve_pool_id')
                ->toArray();
        $listQuery = ApprovePool::query()->join('order', 'order.id', '=', 'approve_pool.order_id')->whereIn('order.status', Order::APPROVAL_PENDING_STATUS)
                ->whereIn('approve_pool.id', array_keys($approveUserPoolIds));
        
        //判断人工审批最大审批单数指每个审批账号名下审批中的订单数最大的数量。包括初审，电审，挂起
        $manualMaxCount = (int) Config::getApproveManualMaxCount();
        $list = $listQuery->orderByDesc("order.status")->orderBy("order.signed_time")
                        ->select(\DB::raw('approve_pool.*,order.signed_time'))->get();
        $orderIds = $list->pluck('order_id')->toArray();
        $riskResult = \Common\Models\Risk\RiskStrategyResult::model()->whereIn("order_id", $orderIds)->pluck('remark', 'order_id');
        //审批列表查询
        $order_params = $params;
        $searchedOrderids = [];
        if (count($order_params) > 0) {
            $order_params['order_ids'] = $orderIds;
            if (isset($order_params['status'])) {
                unset($order_params['status']);
            }
            $query = Order::model()->search($order_params);
            $searchedOrderids = $query->pluck('id')->toArray();
        }
        if ($list->isNotEmpty()) {
            /** @var ApprovePool $model */
            foreach ($list as $k => $model) {
                //避免stopwork后单没了
                if (isset($params['status']) && !empty(trim($params['status']))) {
                    if (!in_array($model->order_status, (array) $params['status'])) {
                        $list->forget($k);
                        continue;
                    }
                }
                $model->last_remark = substr($model->last_remark, 0, 5)."...";
                if (!in_array($model->order->id, $searchedOrderids)) {
                    $list->forget($k);
                    continue;
                }
                $order = $model->order;
                $orderStatus = $order->status;
                /** 是否挂起状态 */
                $model->order_id = $model->order->id;
                $order_type = [];
                $model->is_hang_up = $orderStatus == Order::STATUS_WAIT_TWICE_CALL_APPROVE;
                unset($model->approveUserPool);
                unset($model->order);
                $model->getText();
                $model->id = $approveUserPoolIds[$model->id];
                $model->order_status = $model->getOrderStatusText();
                $model->approve_at = DateHelper::dateFormatByEnv($model->risk_pass_time);
                $model->wait_time_text = DateHelper::usedDays(strtotime($model->created_at));
                $model->telephone = \Common\Utils\Data\StringHelper::desensitization($model->telephone);
                if(isset($riskResult[$model->order_id]) && $riskResult[$model->order_id] == "Needs to call Client's phone only as long as its ringing pass normally."){
                    $model->telephone .= "(CCO)";
                    $order_type[] = 'CCO';
                }
                if($model->tags == ApprovePool::TAGS_SECOND_TWICE) {
                    $model->telephone .= "(C2T)";
                    $order_type[] = 'C2T';
                }
                if(isset($params['order_type']) && is_array($params['order_type'])) {
                    $diff = array_intersect($params['order_type'], $order_type);
                    if(count($diff) == 0){
                        $list->forget($k);
                    }
                }
                $model->can_contact_time = Dict::getNameByCode(OrderDetail::model()->getCanContactTime($order));
                $model->customer_type = $order->quality;
                $model->customer_type_text = ts(User::QUALITY, 'user');
                $model->fullname = $order->user->fullname;
                if ($model->call_test_status == 1){
                    $model->order_no = $model->order_no.'(call success)';
                }
                if ( $model->is_hang_up ){
                    $firstLogInstance = optional(ApproveLogService::getInstance()->getFirstLogInstance($order->id, ManualApproveLog::APPROVE_TYPE_MANUAL));
                    $model->reason_for_remark = ArrayHelper::jsonToArray($firstLogInstance->remark);
                }
            }
            return $list->values();
        }

        // 如果没有审批单，自动停止审批
        $this->stopWork($userId);

        return [];
    }

    /**
     * @param $userId
     * @param string $type
     * @return bool
     */
    public function stopWork($userId, $type = '') {
        // 统计等待时间
        $approveOrders = ApproveUserPool::where(['status' => ApproveUserPool::STATUS_CHECKING, 'admin_id' => $userId])
                ->with('approvePool')
                ->get();
        foreach ($approveOrders as $approveOrder) {
            if (!$approveOrder || !$approveOrder->approvePool)
                continue;
            $waitTime = time() - $approveOrder->approvePool->risk_pass_time;
            $poolLog = ApprovePoolLog::where('approve_pool_id', $approveOrder->approve_pool_id)
                    ->orderBy('id', 'DESC')
                    ->first();
            if ($poolLog) {
                $poolLog->wait_time = $waitTime;
                $poolLog->save();
            }
        }
        return AssignOrderService::getInstance()->stopWork($userId, $type);
    }

    /**
     * 初审详情页
     *
     * @param $data
     * @return mixed
     * @throws CustomException
     * @throws \ReflectionException
     */
    public function firstApproveView($data) {
        return $this->handle(function () use ($data) {
                    return WorkFlowService::getInstance()->firstApproveWorkFlow($data['order_id']);
                }, $data['id']);
    }

    /**
     * @param Closure $closure
     * @param null|int $userPoolId
     * @return mixed
     * @throws CustomException
     * @throws \ReflectionException
     */
    public function handle(Closure $closure, $userPoolId = null) {
        try {
            $result = $closure();
            /** 增加用户过期时间 */
            AssignOrderService::getInstance()->addTime(LoginHelper::getAdminId());
            return $result;
        } catch (Exception $exception) {

            if ($exception instanceof CustomException) {
                // 订单状态异常、订单不存在...应该完结审批单
                $needCompleteCode = ApproveStatusCode::getInstance()->needCompleteOrder();
                if (in_array($exception->getCode(), $needCompleteCode) && $userPoolId) {
                    $userPool = ApproveUserPool::where('id', $userPoolId)->first();
                    if ($userPool && $userPool->status == ApproveUserPool::STATUS_CHECKING) {
                        //$this->refreshApproveOrder($userPoolId, ['approveUserStatus' => ApproveUserPool::STATUS_EXCEPTION, 'approveResult' => ApprovePoolLog::RESULT_EXCEPTION]);
                        DingHelper::notice([
                            'userPoolId' => $userPoolId,
                            'e' => EmailHelper::warpException($exception),
                                ], '审批：订单异常，锁定审批单');
                    }
                }
            }

            throw $exception;
        }
    }

    /**
     * @param $userPoolId
     * @param $data
     * @return bool
     */
    protected function refreshApproveOrder($userPoolId, $data) {
        return DB::transaction(function () use ($userPoolId, $data) {

                    // 审批人员审批单
                    $approveUserPool = ApproveUserPool::where('id', $userPoolId)->first();
                    $approveUserPool->status = $data['approveUserStatus'];
                    $approveUserPool->finish_at = time();
                    $approveUserPool->save();

                    /** @var ApprovePool $approvePool */
                    $approvePool = ApprovePool::where('id', $approveUserPool->approve_pool_id)->first();
                    $pageStatistic = ApprovePageStatistic::where('approve_user_pool_id', $userPoolId)
                            ->where('status', ApprovePageStatistic::STATUS_EFFECTIVE)
                            ->sum('cost');

                    // 等待时间 = time() - 审批时间 - 机审时间
                    $waitTime = time() - $pageStatistic - $approvePool->risk_pass_time;

                    // 保存审批结果
                    $approvePoolLog = ApprovePoolLog::where('approve_pool_id', $approvePool->id)
                            ->where('result', ApprovePoolLog::WAITING_APPROVE)
                            ->first();
                    if ($approvePoolLog) {
                        $approvePoolLog->result = $data['approveResult'];
                        $approvePoolLog->approve_user_pool_id = $approveUserPool->id;
                        $approvePoolLog->wait_time = $waitTime;
                        $approvePoolLog->save();
                    }

                    // 分单日志
                    $logUpdate = [
                        'approve_status' => $data['approveUserStatus'],
                        'from_order_status' => $data['orderStatus'] ?? 0,
                        'to_order_status' => $data['modifyStatus'] ?? 0,
                    ];
                    $approveAssignLog = ApproveAssignLog::where('approve_pool_id', $approvePool->id)
                            ->orderBy('id', 'DESC')
                            ->first();
                    $approveAssignLog->fill(array_filter($logUpdate))->save();

                    $refresh = [];
                    $refresh['sort'] = 0;
                    switch ($data['approveResult']) {
                        // 初审通过,更新审批单
                        case ApprovePoolLog::FIRST_APPROVE_RESULT_PASS:
                            $refresh['status'] = ApprovePool::STATUS_FIRST_APPROVE_PASS;
                            break;
                        case ApprovePoolLog::FIRST_APPROVE_RESULT_SUPPLEMENT:
                            $refresh['status'] = ApprovePool::STATUS_RETURN;
                            break;
                        case ApprovePoolLog::CALL_APPROVE_RESULT_TWICE_ONE:
                            $refresh['status'] = ApprovePool::STATUS_NO_ANSWER;
                            break;
                        case ApprovePoolLog::CALL_APPROVE_RESULT_TWICE_TWO:
                            $refresh['status'] = ApprovePool::STATUS_NO_ANSWER;
                            break;
                        case ApprovePoolLog::FIRST_APPROVE_RESULT_RISK_REJECT:
                        case ApprovePoolLog::CALL_APPROVE_RESULT_RISK_REJECT:
                            $refresh['order_status'] = OrderStatus::SYSTEM_REJECT;
                            $refresh['status'] = ApprovePool::STATUS_REJECT;
                            break;
                        case ApprovePoolLog::CALL_APPROVE_RESULT_PASS:
                            $refresh['status'] = ApprovePool::STATUS_PASS;
                            break;
                        case ApprovePoolLog::CALL_APPROVE_RESULT_CANCEL:
                        case ApprovePoolLog::CALL_APPROVE_RESULT_THREE_CANCEL:
                            $refresh['status'] = ApprovePool::STATUS_CANCEL;
                            break;
                        case ApprovePoolLog::FIRST_APPROVE_RESULT_REJECT:
                        case ApprovePoolLog::CALL_APPROVE_RESULT_REJECT:
                            $refresh['status'] = ApprovePool::STATUS_REJECT;
                            break;
                        case ApprovePoolLog::RESULT_EXCEPTION:
                            $refresh['status'] = ApprovePool::STATUS_EXCEPTION;
                            break;
                    }

                    $approvePool->fill($refresh)->save();
                    /** 初审被拒和电审通过/拒绝才需要质检，特殊免电审情况，初审通过/拒绝需要质检  */
                    $needQualityStatus = [ApprovePoolLog::FIRST_APPROVE_RESULT_REJECT, ApprovePoolLog::CALL_APPROVE_RESULT_PASS, ApprovePoolLog::CALL_APPROVE_RESULT_REJECT];
                    if ($approvePool->order->call_check != Order::CALL_CHECK_REQUIRE) {
                        $needQualityStatus = [ApprovePoolLog::FIRST_APPROVE_RESULT_REJECT, ApprovePoolLog::FIRST_APPROVE_RESULT_PASS];
                    }
                    // 生成质检记录
                    if (in_array($data['approveResult'], $needQualityStatus)) {
                        $approveQuality = new ApproveQuality();
                        $approveQuality->merchant_id = $approvePool->merchant_id;
                        $approveQuality->approve_user_pool_id = $approveUserPool->id;
                        $approveQuality->quality_status = ApproveQuality::QUALITY_STATUS_WAITING;
                        $approveQuality->save();
                    }

                    return true;
                });
    }

    /**
     * 初审提交
     *
     * @param $data
     * @return array
     * @throws CustomException
     * @throws \ReflectionException
     */
    public function firstApproveSubmit($data) {
        /** 检查订单状态&拦截重复请求 */
        $this->beforeSubmit($data['id'], $data['order_id']);
        $result = $this->handle(function () use ($data) {
            $data['admin_id'] = LoginHelper::getAdminId();
            return FirstApproveService::getInstance($data['order_id'])->submit($data);
        }, $data['id']);
        //抛出证件重复错误
        if ($result == 'Card number update failed, failed reason: already exist') {
            return $result;
        }
        //  存在submit代表最终提交
        if (array_get($result, 'code') != 0) {
            /** 记录日志 */
            $this->saveRequestParams('first approve');
            $this->refreshApproveOrder($data['id'], $result['code']);
            $this->saveResult($result['snapshoot'], $data['id'], ApproveResultSnapshot::TYPE_FIRST_APPROVE);
            return $this->getNextOrder(ApprovePool::ORDER_FIRST_GROUP);
        }

        return ['order_id' => 0, 'id' => 0];
    }

    /**
     * @param $id
     * @param $orderId
     * @throws CustomException
     */
    protected function beforeSubmit($id, $orderId) {
        // 检查状态
        $this->checkStatus($id, $orderId);

        // 避免请求过于频繁
        if (!$this->getLock($id)) {
            throw new CustomException('Too Many Attempts');
        }
    }

    /**
     * @param $userPoolId
     * @param $orderId
     * @return bool
     * @throws CustomException
     */
    protected function checkStatus($userPoolId, $orderId) {
        $userPool = ApproveUserPool::where('id', $userPoolId)->first();

        if ($userPool->order_id != $orderId) {
            EmailHelper::send(json_encode(request()->all()), '订单Id和审批人员Id不匹配', true);
            $this->saveRequestParams('debug');
            throw new CustomException('Wrong id');
        }

        if (!in_array($userPool->status, [ApproveUserPool::STATUS_CHECKING, ApproveUserPool::STATUS_NO_ANSWER])) {
            throw new CustomException('The approval order has expired, please restart the approval.');
        }
        if ($userPool->admin_id !== LoginHelper::getAdminId() && Env::isProd()) {
            throw new CustomException('The approval order has been cancelled/changed');
        }
        return true;
    }

    /**
     * 记录日志
     *
     * @param $type
     */
    protected function saveRequestParams($type) {
        Log::driver('approve')->info($type, request()->all());
    }

    /**
     * @param $id
     * @return bool
     */
    protected function getLock($id) {
        $redisKey = static::REDIS_KEY_LOCK . $id;
        return LockRedis::redis()->getLock($redisKey, 1);
    }

    /**
     * @param $data
     * @param $poolId
     * @param int $type
     * @return bool
     */
    protected function saveResult($data, $poolId, $type) {

        $result = ['result' => json_encode($data, JSON_UNESCAPED_UNICODE)];
        $userPool = ApproveUserPool::where('id', $poolId)->first();

        $model = ApproveResultSnapshot::where('approve_user_pool_id', $poolId)->first();
        if (!$model) {
            $model = new ApproveResultSnapshot();
            $model->merchant_id = $userPool->merchant_id;
            $model->approve_user_pool_id = $poolId;
            $model->approve_pool_id = $userPool->approve_pool_id;
            $model->approve_type = $type;
        }

        return $model->fill($result)->save();
    }

    /**
     * 获取下个审批单
     * @param $type
     * @return array
     */
    protected function getNextOrder($type) {
        $adminId = LoginHelper::getAdminId();
        $fun = function () use ($adminId, $type) {
            return ApproveUserPool::where(['admin_id' => $adminId, 'status' => ApproveUserPool::STATUS_CHECKING])
                            ->whereHas('approvePool', function ($query) use ($type) {
                                $query->where('type', $type);
                            })
                            ->first();
        };

        $row = $fun();

        // 如果用户审批池没有订单给用户加审批单
        if (!$row) {
            AssignOrderService::getInstance()->assignOrder($adminId, $type);
            $row = $fun();
        }

        // 如果没有审批单，自动停止审批
        if (!$row && $type == "1") {
            $this->stopWork($adminId, $type);
        }

        return [
            'order_id' => $row ? $row->order_id : 0,
            'id' => $row ? $row->id : 0,
        ];
    }

    /**
     * 电审详情页
     *
     * @param $data
     * @return mixed
     * @throws CustomException
     */
    public function callApproveView($data) {
        return $this->handle(function () use ($data) {
                    return WorkFlowService::getInstance()->callApproveWorkFlow($data['order_id']);
                }, $data['id']);
    }

    /**
     * 电审提交
     * @param $data
     * @return array
     * @throws CustomException
     * @throws \ReflectionException
     */
    public function callApproveSubmit($data) {
        $this->beforeSubmit($data['id'], $data['order_id']);
        $result = $this->handle(function () use ($data) {
            $data['admin_id'] = LoginHelper::getAdminId();
            return CallApproveService::getInstance($data['order_id'])->submit($data);
        }, $data['id']);
        // 存在submit代表最终提交
        if (array_get($result, 'code') != 0) {
            $this->saveRequestParams('call approve');
            $this->refreshApproveOrder($data['id'], $result['code']);
            $this->saveResult($result['snapshoot'], $data['id'], ApproveResultSnapshot::TYPE_CALL_APPROVE);
            return $this->getNextOrder(ApprovePool::ORDER_CALL_GROUP);
        }

        return ['order_id' => 0, 'id' => 0];
    }
    
    public function onlySave($data){
        $data['admin_id'] = LoginHelper::getAdminId();
        return CallApproveService::getInstance($data['order_id'])->onlySave($data);
    }

    /**
     * @param $data
     * @return mixed
     * @throws CustomException
     * @throws \ReflectionException
     */
    public function getApproveLog($data) {
        return $this->handle(function () use ($data) {
                    return ApproveLogService::getInstance()->getLogNew($data['order_id'], $data['order_type'], $data['page'] ?? null);
                });
    }

    /**
     * @param $userPoolId
     * @return bool
     */
    public function startCheck($userPoolId) {
        // 更新开始时间
        ApproveUserPool::where('id', $userPoolId)
                ->where('status', ApproveUserPool::STATUS_CHECKING)
                ->update(['start_at' => time()]);
        // 记录开始时间
        $this->getRedis()->set($this->getStartCheckRedisKey($userPoolId), time(), 'EX', 1500);
        return true;
    }

    /**
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function getRedis() {
        return Redis::connection();
    }

    /**
     * @param $userPoolId
     * @return string
     */
    protected function getStartCheckRedisKey($userPoolId) {
        return static::REDIS_KEY_START_CHECK . $userPoolId;
    }

    public function backCase($userPoolIds) {
        $poolIds = [];
        $sucess = 0;
        $failed = 0;
        $total = count($userPoolIds);
        # 这个组可以直接分走
        $isMng = \Admin\Services\Staff\StaffServer::server()->checkInRoles(['运营组长Operation-TL', '超级管理员']);
        foreach ($userPoolIds as $poolId) {
            # 打过电话不允许退件
            $userPool = ApproveUserPool::model()->newQuery()->where("id", $poolId)->first();

            if ($userPool) {
                if (!ApproveCallLog::model()->where("approve_pool_id", $userPool->approve_pool_id)->where('admin_id', LoginHelper::getAdminId())->exists() || $isMng) {
                    $poolIds[] = $userPool->approve_pool_id;
                    $sucess++;
                } else {
                    $failed++;
                }
            }
        }
        if ($poolIds) {
            $res = (new ApproveCheckService())->backCase($poolIds);
            $failed = $total - $res;
            return "$total total,$res successes, $failed failures";
        }
        return "$total total, $failed failures";
    }

    /**
     * 返回当日审批员的效率
     * @param type $adminId
     * @return type
     */
    public function todayFinish($adminId) {
        $finishCount = ApproveUserPool::model()->newQuery()->where("admin_id", $adminId)->where("finish_at", ">", strtotime(date("Y-m-d")))->count();
        $passCount = ApproveUserPool::model()->newQuery()->where("admin_id", $adminId)->where("finish_at", ">", strtotime(date("Y-m-d")))->where("status", ApproveUserPool::STATUS_CALL_PASS)->count();

        return ["coverage" => $finishCount, "rate" => $finishCount ? (round($passCount / $finishCount, 2) * 100) . "%" : "0%"];
    }
    # 返回24小时未处理订单个数
    public function get24HOrderCount($userId) {
        $allocateTime = date("Y-m-d H:i:s", time() - 86400);
        $approveUserPoolIds = ApproveUserPool::model()
                ->getCheckingList($userId, $allocateTime)
                ->pluck('id', 'approve_pool_id')
                ->toArray();
        $listQuery = ApprovePool::query()->join('order', 'order.id', '=', 'approve_pool.order_id')->whereIn('order.status', Order::APPROVAL_PENDING_STATUS)
                ->whereIn('approve_pool.id', array_keys($approveUserPoolIds))->whereNotIn("approve_pool.order_id", $this->cardNumberExist());
        $listCount = $listQuery->orderBy("order.signed_time")
                        ->select(\DB::raw('approve_pool.*,order.signed_time'))->count();
        return $listCount;
    }

    # 返回24小时未处理订单
    public function get24HOrder($userId) {
        $allocateTime = date("Y-m-d H:i:s", time() - 86400);
        $approveUserPoolIds = ApproveUserPool::model()
                ->getCheckingList($userId, $allocateTime)
                ->pluck('id', 'approve_pool_id')
                ->toArray();
        $listQuery = ApprovePool::query()->join('order', 'order.id', '=', 'approve_pool.order_id')->whereIn('order.status', Order::APPROVAL_PENDING_STATUS)
                ->whereIn('approve_pool.id', array_keys($approveUserPoolIds))->whereNotIn("approve_pool.order_id", $this->cardNumberExist());
        $order = $listQuery->orderBy("order.signed_time")
                        ->select(\DB::raw('approve_pool.*,order.signed_time'))->first();
        return $order;
    }

    /**
     * 更新卡号出现重复的订单存入缓存
     * @param type $orderId
     * @return type
     */
    public function cardNumberExist($orderId = "") {
        $cacheKey = 'CardNumberExist:' . \Common\Utils\MerchantHelper::getMerchantId();

        $orderIds = Cache::get($cacheKey);
        if (!$orderIds) {
            $orderIds = [];
        }
        if ($orderId) {
            if (!in_array($orderId, $orderIds)) {
                $orderIds[] = $orderId;
            }
        }
        Cache::forever($cacheKey, $orderIds);
        return $orderIds;
    }


        /**
     * 更新卡号出现重复的订单存入缓存
     * @param type $orderId
     * @return type
     */
    public function cardNumberClear($orderId) {
        $cacheKey = 'CardNumberExist:' . \Common\Utils\MerchantHelper::getMerchantId();

        $orderIds = Cache::get($cacheKey);
        if (!$orderIds) {
            $orderIds = [];
        }
        if ($orderId) {
            if (in_array($orderId, $orderIds)) {
                $orderIds = array_diff($orderIds, [$orderId]);
            }
        }
        sort($orderIds);
        Cache::forever($cacheKey, $orderIds);
        return $orderIds;
    }
}
