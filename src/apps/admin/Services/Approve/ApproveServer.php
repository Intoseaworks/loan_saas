<?php

namespace Admin\Services\Approve;

use Admin\Models\Approve\Approve;
use Admin\Models\Order\Order;
use Admin\Services\BaseService;
use Admin\Services\Staff\StaffServer;
use Carbon\Carbon;
use Common\Models\User\UserAuth;
use Common\Redis\Approve\ApproveRedis;

class ApproveServer extends BaseService
{
    //人工审批列表分单过期时间
    public static $prepareApproveExpire = 60;

    private $order;

    //审批项对应的资料项 type
    public static $approveSelectRelateInfo = [
        Approve::SELECT_REPLENISH_FRONT_BREEZING => UserAuth::TYPE_ID_FRONT,
        Approve::SELECT_REPLENISH_BACK_BREEZING => UserAuth::TYPE_ID_BACK,
        Approve::SELECT_REPLENISH_FACE_NONSTANDARD => UserAuth::TYPE_FACES,
        Approve::SELECT_REPLENISH_HAND_ID_CARD_NONSTANDARD => UserAuth::TYPE_ID_HANDHELD,
        Approve::SELECT_REPLENISH_ID_CARD_OVERDUE => [
            UserAuth::TYPE_ID_BACK,
            UserAuth::TYPE_ID_FRONT,
        ],
    ];

    /**
     * @param null $orderId
     * @throws \Common\Exceptions\ApiException
     */
    public function __construct($orderId = null)
    {
        if ($orderId !== null && !$this->order = Order::model()->getOne($orderId)) {
            $this->outputException('订单数据不存在');
        }
    }

    public function list($params, $evolveStatus = false)
    {
        $query = Order::model()->search($params);
        $size = array_get($params, 'size');
        $list = $query->paginate($size);

        Carbon::setLocale('zh'); //diffForHumans 替换为中文
        $allotArr = [];
        $adminList = collect();
        if ($evolveStatus) {
            //@phan-suppress-next-line PhanUndeclaredMethod
            $allotArr = ApproveRedis::redis()->getAdminIdByValue($list->pluck('id')->toArray());
            $adminList = StaffServer::server()->getByIds(array_values($allotArr))->pluck('nickname', 'id');
        }

        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($list as $data) {
            /** @var $data Order */
            $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
            $data->user && $data->user->getText(['telephone', 'fullname', 'quality']);
            $data->setScenario(Order::SCENARIO_LIST)->getText();

            if ($evolveStatus) {
                $data->evolve_status = '';
                switch ($data->status) {
                    case Order::STATUS_WAIT_SYSTEM_APPROVE:
                        $data->evolve_status = '即将处理';
                        break;
                    case Order::STATUS_WAIT_MANUAL_APPROVE:
                        $data->evolve_status = isset($allotArr[$data->id]) ? '已分配->' . $adminList->get($allotArr[$data->id]) : '未分配';
                        break;
                    case Order::STATUS_REPLENISH:
                        $data->evolve_status = '用户未处理';
                        break;
                }
            }

            $data->wait_approve_time = Carbon::createFromTimeString($data->created_at)->diffForHumans(null, true, true,
                3);
        }

        return $list;
    }

    /**
     * 根据管理员id获取 人工审批列表
     * @param $adminId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator
     */
    /*public function approveList($adminId)
    {
        $orderIds = ApproveRedis::redis()->getSByAdminId($adminId);

        $count = count($orderIds);
        // 人工审批 可获取审批单数
        $manualAllotCount = Config::getApproveManualAllotCount();
        if ($count < $manualAllotCount) {
            // 补充订单数
            $manualAllotCount = $manualAllotCount - $count;
            // 分配订单
            $this->allotOrder($adminId, $manualAllotCount);
        } elseif ($count > $manualAllotCount) {
            // 随机剔除订单
            $rmOrderIds = array_random($orderIds, $count - $manualAllotCount);
            ApproveRedis::redis()->delS($adminId, $rmOrderIds);
            ApproveRedis::redis()->refreshSExpire($adminId, self::$prepareApproveExpire);
        } else {
            // 刷新过期时间
            ApproveRedis::redis()->refreshSExpire($adminId, self::$prepareApproveExpire);
        }

        $params = [
            // 重新获取最终分配的orderId
            'order_ids' => ApproveRedis::redis()->getSByAdminId($adminId),
            'sort_order_id' => 'asc',
        ];
        return $this->list($params, true);
    }*/

    /**
     * 获取下一个订单
     * @return mixed
     */
    /*public function getNextOrder()
    {
        $adminId = LoginHelper::getAdminId();

        $orderId = ApproveRedis::redis()->srandmember($adminId);

        if (!$orderId) {
            $this->allotOrder($adminId);
            $orderId = ApproveRedis::redis()->srandmember($adminId);
        }

        if ($orderId && !$order = $this->canApproveSubmit($orderId)) {
            // 订单状态不正确，从set中移除
            ApproveRedis::redis()->delS($adminId, $orderId);
            $orderId = $this->getNextOrder();
        }

        // 刷新过期时间为 配置时间
        ApproveRedis::redis()->refreshSExpire($adminId);

        return $orderId;
    }*/

    /**
     * 分配订单
     * @param $adminId
     * @param $manualAllotCount
     * @return array
     */
    /*public function allotOrder($adminId, $manualAllotCount = null)
    {
        if (is_null($manualAllotCount)) {
            // 获取配置 分配订单数
            $manualAllotCount = Config::getApproveManualAllotCount();
        }

        // 全部已被分配订单
        $allocatedOrder = ApproveRedis::redis()->getSByAdminId();

        $orderM = Order::getWaitManualApprove($allocatedOrder, $manualAllotCount);
        $orderIds = $orderM->pluck('id')->toArray();

        // 分配订单给指定admin_id
        ApproveRedis::redis()->sadd($adminId, $orderIds, true, self::$prepareApproveExpire);

        return $orderIds;
    }*/

    /**
     * 判断能否进行人工审批
     * @param $orderId
     * @return mixed
     */
    /*public function canApproveSubmit($orderId)
    {
        $where = [
            'status' => Order::STATUS_WAIT_MANUAL_APPROVE,
        ];
        return Order::getById($orderId, $where);
    }*/

    /**
     * 设置订单为可人工审批
     * @param $orderId
     * @return mixed
     */
    /*public function setCanApproveSubmit($orderId)
    {
        $order = Order::model()->getOne($orderId);
        $order->status = Order::STATUS_WAIT_MANUAL_APPROVE;
        $order->save();
    }*/

    /**
     * 判断订单是否已经分配给当前用户
     * @param $orderId
     * @return bool
     */
    /*public function canApprove($orderId)
    {
        $adminId = LoginHelper::getAdminId();
        if (!ApproveRedis::redis()->sismember($adminId, $orderId)) {
            $allocated = ApproveRedis::redis()->getAdminIdByValue([$orderId]);
            // 订单已分配 || 订单不能审批 return false;
            if ($allocated || !$this->canApproveSubmit($orderId)) {
                return false;
            }
            // 将订单拉到当前用户下
            ApproveRedis::redis()->sadd($adminId, [$orderId]);
        }
        ApproveRedis::redis()->refreshSExpire($adminId);
        return true;
    }*/

    /**
     * 保存人工审批结果
     * @param $orderId
     * @param $approveResult
     * @param $remark
     * @return bool|mixed
     * @throws \Throwable
     */
    /*public function approveSubmit($orderId, $approveResult, $remark)
    {
        $select = (array)$approveResult;
        $adminId = LoginHelper::getAdminId();

        // redis set 不存在 | 订单状态不正确 => 跳过
        $redisExist = ApproveRedis::redis()->sismember($adminId, $orderId);

        if (Env::isDev()) {
            $redisExist = true;
            $adminId = 1;
        }

        if (!$redisExist || !$order = $this->canApproveSubmit($orderId)) {
            return false;
        }

        $result = str_before(array_search(array_first($select), array_dot(Approve::SELECT_GROUP)), '.');
        $submitResult =  DB::transaction(function () use ($order, $select, $remark, $adminId, $result) {

            if (
                !in_array($result, array_keys(Approve::RESULT)) ||
                !Approve::selectIsSameGroup($select)
            ) {
                throw new \Exception('系统错误：审批状态不正确');
            }
            Approve::coverAdd($order->id, $result, $select, $remark);

            $orderServer = OrderServer::server();
            switch ($result) {
                case Approve::RESULT_PASS:
                    //审批通过，记录审批时间
                    $orderServer->manualPass($order->id);
                    break;
                case Approve::RESULT_REPLENISH:
                    $orderServer->manualReplenish($order->id);
                    //清除对应资料
                    $this->clearUserInfoBySelect($order->user_id, $select);
                    //推送
                    event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_REPLENISH));
                    break;
                case Approve::RESULT_REJECTED:
                    //审批拒绝，记录审批时间
                    $orderServer->manualReject($order->id,$order->user_id);
                    //记录重借等待天数
                    OrderDetailServer::server()->saveRejectedDays($order);
                    event(new OrderServicesPushEvent($order->id, OrderServicesPushEvent::TYPE_ORDER_REJECT));
                    break;
            }

            // 去除已审批order_id
            ApproveRedis::redis()->delS($adminId, $order->id);

            return true;
        });

        if($result == Approve::RESULT_PASS){
            //审批通过推送
            event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_APPROVE_PASS));
        }
        if($result == Approve::RESULT_REPLENISH){
            //待补充推送
            event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_REPLENISH));
        }

        return $submitResult;
    }*/

    /**
     * 获取人工审批选项列表
     * @return array
     */
    /*public function getResultSelectGroup()
    {
        $resultGroup = [];
        $resultSelect = Approve::SELECT_GROUP;

        foreach ($resultSelect as $key => $value) {
            $selectArr = [];
            foreach ($value as $select) {
                $selectArr[] = [
                    'value' => $select,
                    'label' => Approve::SELECT[$select],
                ];
            }
            $resultGroup[] = [
                'value' => $key,
                'label' => Approve::RESULT[$key],
                'children' => $selectArr,
            ];
        }

        return $resultGroup;
    }*/

    /**
     * 根据审批选项清除 用户对应资料项
     * @param $userId
     * @param $select
     * @return bool
     */
    /*public function clearUserInfoBySelect($userId, $select)
    {
        if (!$type = array_only(self::$approveSelectRelateInfo, (array)$select)) {
            return false;
        }
        return UserAuth::model()->clearAuth($userId, $type);
    }

    public function view()
    {
        $tabs = config('saas-business.approve_tabs');
        return DataServer::server($this->order->user_id, $this->order->id)->list($tabs);
    }*/

    /**
     * 被拒订单列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator
     */
    /*public function rejectList($params)
    {
        $size = array_get($params, 'size');
        $with = ['approve'];
        $query = Order::model()->search($params, $with);
        #审批人员
        if ($approveIds = array_get($params, 'approve_ids')) {
            $query->whereHas('approve', function ($query) use ($approveIds) {
                $query->whereIn('admin_id', (array)$approveIds);
            });
        }

        if ($this->getExport()) {
            ApproveExport::getInstance()->export($query, ApproveExport::SCENE_REJECT_LIST);
        }

        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($orders = $query->paginate($size) as $order) {
            $order->user->channel && $order->user->channel->getText(['channel_code', 'channel_name']);
            $order->user && $order->user->getText(['telephone', 'fullname', 'quality']);
            $order->setScenario(Order::SCENARIO_LIST)->getText();
            $order->reject_reason = $this->getRejectReasonText($order->id, $order->status);
            $order->risk_score = $this->getSystemScore($order->id);
            $approverId = $this->getApproverIdByOrderId($order->id);
            $order->approver = Staff::model()->getNameById($approverId) ?? '---';
            $order->addHidden('approve', 'channel');
        }
        return $orders;
    }*/

    /**
     * 获取列表被拒原因文案描述
     * @param $orderId
     * @param $status
     * @return mixed|string
     */
    public function getRejectReasonText($orderId, $status)
    {
        switch ($status) {
            case Order::STATUS_SYSTEM_REJECT:
                $reasons = '命中被拒规则';
                break;
            case Order::STATUS_MANUAL_REJECT:
                $reasons = $this->getApproveSelect($orderId);
                $count = is_array($reasons) ? count($reasons) : 0;
                $reasons = $count . '项资料未通过';
                break;
        }
        return $reasons ?? [];
    }

    /**
     * 获取人审被拒原因详情
     * @return array
     */
    /*public function getRejectReasonDetail()
    {
        // 人审
        $orderId = $this->order->id;
        if ($this->order->status == Order::STATUS_MANUAL_REJECT) {
            $result = $this->buildManualRejectReasons();
            if (!$reasons = $this->getApproveSelect($orderId)) {
                return ArrayHelper::arrToOption($result, 'label', 'value');
            }
            $reasons = array_values(array_only(Approve::SELECT, $reasons));
            foreach ($result as $key => &$val) {
                foreach ($reasons as $reason) {
                    if (strpos($reason, $key) !== false) {
                        $val = Approve::RESULT_REJECTED;
                    }
                }
            }
            return ArrayHelper::arrToOption($result, 'label', 'value');
        } else {
            // 机审
            $result = $this->buildSystemRejectReasons($orderId);
            return ArrayHelper::arrToOption($result, 'label', 'value');
        }
    }*/

    /**
     * 获取订单审批人id
     * @param $orderId
     * @return mixed
     */
    public function getApproverIdByOrderId($orderId)
    {
        return Approve::model()->whereOrderId($orderId)->value('admin_id');
    }

    /**
     * 获取审批人ids列表
     * @return mixed
     */
    /*public function getApproverIds()
    {
        return Approve::model()->select('admin_id')->get();
    }*/

    /**
     * 获取审批人列表
     * @return mixed
     */
    /*public function getApproverList()
    {
        $ids = $this->getApproverIds();
        $adminList = StaffServer::server()->getByIds($ids)->pluck('nickname', 'id');
        return $adminList;
    }*/

    /**
     * 构建人审项文案详情
     * @return array
     */
    /*public function buildManualRejectReasons()
    {
        return [
            '人审风险报告' => Approve::RESULT_PASS,
            '机审详情' => Approve::RESULT_PASS,
            '基本信息' => Approve::RESULT_PASS,
            '运营商报告' => Approve::RESULT_PASS,
            '多头报告' => Approve::RESULT_PASS,
            '通讯录' => Approve::RESULT_PASS,
            '短信记录' => Approve::RESULT_PASS,
            '借款信息' => Approve::RESULT_PASS,
            '银行卡' => Approve::RESULT_PASS,
            '位置信息' => Approve::RESULT_PASS,
            'APP应用列表' => Approve::RESULT_PASS,
        ];
    }*/

    /**
     * 构建机审被拒原因项
     * @param $orderId
     * @return array
     */
    /*public function buildSystemRejectReasons($orderId)
    {
        return ArrayHelper::jsonToArray(array_get(ArrayHelper::jsonToArray(RiskApproveLog::model()->getRejectResult($orderId)),
            'extra', []));
    }*/

    /**
     * 获取机审分
     * @param $orderId
     * @return array
     */
    /*public function getSystemScore($orderId)
    {
        return array_get($this->buildSystemRejectReasons($orderId), 'score');
    }*/

    /**
     * 获取人工审批结果
     * @param $orderId
     * @param string $status
     * @return mixed|string
     */
    public function getApproveSelect($orderId, $status = Approve::RESULT_REJECTED)
    {
        return Approve::model()->whereOrderId($orderId)
            ->whereResult($status)
            ->value('approve_select');
    }
}
