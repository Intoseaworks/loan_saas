<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-08
 * Time: 16:24
 */

namespace Approve\Admin\Services\Check;


use Approve\Admin\Services\Approval\CallApproveService;
use Approve\Admin\Services\Approval\FirstApproveService;
use Approve\Admin\Services\CommonService;
use Common\Exceptions\CustomException;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApproveResultSnapshot;
use Common\Models\Approve\ApproveUserPool;
use Common\Models\Order\Order;
use Common\Traits\GetInstance;
use Common\Utils\Code\OrderStatus;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Upload\ImageHelper;
use Illuminate\Support\Facades\DB;
use Common\Models\Approve\ApproveAssignLog;
use Common\Utils\LoginHelper;

class ApproveCheckService
{
    use GetInstance;

    const REDIS_KEY_SORT_VAL = 'APPROVE_SORT';

    /**
     * @param array $condition
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|ApprovePool[]
     * @throws CustomException
     */
    public function getList($condition = [])
    {
        $query = ApprovePool::query()
            ->withoutGlobalScope(ApprovePool::$bootScopeMerchant)
            ->from('approve_pool as m1')
            ->whereNotIn('m1.status', ApprovePool::NOT_SHOW_IN_CHECK_LIST)
            ->where('m1.merchant_id', MerchantHelper::getMerchantId());

        if (!empty($condition['order_status'])) {
            $query->whereIn('m1.order_status', (array)$condition['order_status']);
        }

        if (!empty($condition['status'])) {
            if ($condition['status'] == -1) {
                $condition['status'] = 0;
            }
            $query->where('m1.status', $condition['status']);
        }

        if (!empty($condition['order_created_time'])) {
            $orderCreatedTime = $condition['order_created_time'];
            if (!empty($orderCreatedTime[0])) {
                $query->where('m1.order_created_time', '>=', $orderCreatedTime[0]);
            }
            if (!empty($orderCreatedTime[1])) {
                $nextDay = CommonService::getInstance()->getNextDay($orderCreatedTime[0] ?? 0, $orderCreatedTime[1]);
                $query->where('m1.order_created_time', '<=', $nextDay);
            }
        }

        if (!empty($condition['order_no'])) {
            $query->where('m1.order_no', $condition['order_no']);
        }

        if (!empty($condition['user'])) {
            $userId = CommonService::getInstance()->getUserId($condition['user']);
            $query->whereIn('m1.user_id', $userId);
        }

        if (!empty($condition['admin_user'])) {
//            $adminUserId = CommonService::getInstance()->getAdminUserId($condition['admin_user']);
            $query->whereIn('m3.admin_id', [$condition['admin_user']]);
        }

        $perPage = !empty($condition['per_page']) ? $condition['per_page'] : 10;

        $approveUserPool = ApproveUserPool::query()
            ->withoutGlobalScope(ApproveUserPool::$bootScopeMerchant)
            ->from('approve_user_pool as m4')
            ->select('m4.*')
            ->leftJoin('approve_user_pool as m5', function ($join) {
                $join->on('m4.approve_pool_id', 'm5.approve_pool_id');
                $join->on('m4.id', '<', 'm5.id');
            })
            ->where('m4.merchant_id', MerchantHelper::getMerchantId())
            ->whereNull('m5.id');

        $rows = $query
            ->select(['m1.*', 'm3.admin_id', 'm3.id as approve_user_id', 'm3.status as user_pool_status'])
            ->leftJoinSub($approveUserPool, 'm3', 'm1.id', 'm3.approve_pool_id')
            ->orderBy('m1.sort', 'DESC')
            ->orderBy('m1.id', 'DESC')
            ->paginate($perPage);

        $data = [];
        $userIds = $rows->pluck('user_id')
            ->unique()
            ->filter()
            ->toArray();
        $adminUserIds = $rows->pluck('admin_id')
            ->unique()
            ->filter()
            ->toArray();
        if ($userIds) {
            $userInfo = CommonService::getInstance()->getUserInfo($userIds);
        }
        if ($adminUserIds) {
            $adminUserInfo = CommonService::getInstance()->getAdminUserInfo($adminUserIds);
        }
        /** @var ApprovePool[] $rows */
        foreach ($rows as $row) {
            $temp = [];
            $temp['id'] = $row->id;
            $temp['priority'] = $this->getPriority($row);
            $temp['order_no'] = $row->order_no;
            $temp['order_id'] = $row->order_id;
            $temp['user_name'] = $userInfo[$row->user_id]['fullname'] ?? '';
            $temp['telephone'] = \Common\Utils\Data\StringHelper::desensitization($row->telephone);
            switch ($condition['date_type']??"day") {
                case "year":
                    $temp['order_created_time'] = date('Y', $row->order_created_time);
                    break;
                case "month":
                    $temp['order_created_time'] = date('Y-m', $row->order_created_time);
                    break;
                case "week":
                    $temp['order_created_time'] = date("o#W",$row->order_created_time);
                    break;
                default:
                    $temp['order_created_time'] = date('Y-m-d', $row->order_created_time);
            }
            $temp['waiting_time'] = $this->getWaitingTime($row);
            $temp['order_status'] = $row->getOrderStatusText();
            $temp['approve_status'] = $row->getApproveStatusText();
            $receiveTime = ApproveUserPool::model()->where("order_id", $row->order_id)->orderByDesc("created_at")->value("created_at");
            $temp['receive_time'] = $receiveTime ? $receiveTime->toDateTimeString() : "";
            if($row->user_pool_status == ApproveUserPool::STATUS_STOP_WORK){
                $temp['admin_user_name'] = "-";
            }else{
                $temp['admin_user_name'] = $this->getAdminUserName($row, $adminUserInfo);
            }
            $data[] = $temp;
        }

        // 可审批单量
        $canApproveCount = ApprovePool::select([
            DB::raw('count(*) count '),
            'approve_pool.type',
        ])->join('order', 'order.id', '=', 'approve_pool.order_id')
            ->where('approve_pool.status', ApprovePool::STATUS_WAITING)
            ->whereIn("order.status", [Order::STATUS_WAIT_CALL_APPROVE, Order::STATUS_WAIT_MANUAL_APPROVE, Order::STATUS_WAIT_TWICE_CALL_APPROVE])
            ->groupBy('approve_pool.type')
            ->pluck('count', 'type');
        $rows = $rows->toArray();
        $rows['data'] = $data;
        $rows['can_approve_count'] = [
            'manual_count' => $canApproveCount[1] ?? 0,
            'call_count' => $canApproveCount[2] ?? 0,
            'approveing_count' => ApprovePool::model()
                ->join('order', 'order.id', '=', 'approve_pool.order_id')
                ->whereIn("order.status", [Order::STATUS_WAIT_CALL_APPROVE, Order::STATUS_WAIT_MANUAL_APPROVE, Order::STATUS_WAIT_TWICE_CALL_APPROVE])
                ->where("approve_pool.status", ApprovePool::STATUS_CHECKING)->count()
        ];
        return $rows;
    }

    /**
     * @param ApprovePool $row
     * @return int
     */
    protected function getPriority(ApprovePool $row)
    {
        $priority = 0;
        if ($row->status == ApprovePool::STATUS_WAITING) {
            if ($row->sort > 0) {
                $priority = 1;
            } else {
                $priority = 2;
            }
        }
        return $priority;
    }

    /**
     * @param ApprovePool $approvePool
     * @return string
     */
    protected function getWaitingTime(ApprovePool $approvePool)
    {
        // 还未达到审批条件
        if ($approvePool->status == ApprovePool::STATUS_NOT_CONDITION) {

            if ($approvePool->type == ApprovePool::ORDER_CALL_GROUP) {
                $interval = 0;
                // 电审二审一次
                if ($approvePool->grade == ApprovePool::GRADE_SECOND_CALL_APPROVE) {
                    $interval = 30 * 60;
                }

                // 电审二审二次
                if ($approvePool->grade == ApprovePool::GRADE_SECOND_CALL_TWICE_APPROVE) {
                    $interval = 60 * 60;
                }

                $interval = $interval - (time() - $approvePool->manual_time);
                return t('电审倒计时', 'approve') . '-' . DateHelper::usedDays(time() - $interval);
            }
        }

        if ($approvePool->status == ApprovePool::STATUS_WAITING) {
            return t('等待时间', 'approve') . '-' . DateHelper::usedDays($approvePool->risk_pass_time);
        }

        return '';
    }

    /**
     * @param ApprovePool $row
     * @param $adminuserInfo
     * @return string
     */
    protected function getAdminUserName(ApprovePool $row, &$adminuserInfo)
    {
//        if (in_array($row->status, [ApprovePool::STATUS_WAITING, ApprovePool::STATUS_NOT_CONDITION])) {
        if (in_array($row->status, [ApprovePool::STATUS_CANCEL])  || $row->approveUserPool->status == ApproveUserPool::STATUS_CANCEL) {
            return '';
        }

        return $adminuserInfo[$row->admin_id]['nickname'] ?? '';
    }

    /**
     * @param $id
     * @return bool
     * @throws CustomException
     */
    public function setPriority($id)
    {
        $row = ApprovePool::where('id', $id)->first();
        if ($row) {
            if ($row->sort > 0) {
                $row->sort = 0;
            } else {
                $row->sort = $this->getSortVal();
            }
            $row->save();

            return true;
        }

        throw new CustomException(t('数据不存在'));
    }

    /**
     * @return int
     */
    protected function getSortVal()
    {
        return CommonService::getInstance()->getRedis()->incr(static::REDIS_KEY_SORT_VAL);
    }

    /**
     * @return array
     */
    public function getOrderStatusList()
    {
        $list = OrderStatus::getInstance()->orderStatusList();
        $nList = [0 => t('全部', 'approve')];
        foreach ($list as $k => $item) {
            if ($k == 0) {
                $k = -1;
            }
            $nList[$k] = $item;
        }
        return ArrayHelper::arrToOption($nList, 'key', 'val');
    }

    /**
     * @param $poolId
     * @return array
     */
    public function detail($poolId)
    {
        $data = CommonService::getInstance()->getApproveResult($poolId);
        /** @var ApprovePool $approvePool */
        $approvePool = ApprovePool::where('id', $poolId)->first();
//        $snapshoot = $this->getSnapshot($poolId);
//        CommonService::getInstance()->getSnapShootApproveResultByPoolId($poolId, $snapshoot);
//
//        // 中英
//        if ($userType = array_get($snapshoot, 'firstDetail.bashDetail.user_type')) {
//            $snapshoot['firstDetail']['bashDetail']['user_type'] = t($userType, 'user');
//        }
//        if ($aadhaarCardFileChecked = array_get($snapshoot, 'firstDetail.bashDetail.aadhaar_card_file_checked')) {
//            $snapshoot['firstDetail']['bashDetail']['aadhaar_card_file_checked'] = t($aadhaarCardFileChecked, 'approve');
//        }
//        if ($riskResult = array_get($snapshoot, 'firstDetail.riskResult')) {
//            $snapshoot['firstDetail']['riskResult'] = t($riskResult, 'approve');
//        }
        /** 质检不取快照数据 避免数据格式混乱 */
        $baseDetail = FirstApproveService::getInstance($approvePool->order_id)->bashDetail();
        $baseDetail = array_except($baseDetail, ['base_detail_status', 'fullname_radio_list', 'id_card_radio_list', 'refusal_code', 'suspected_fraud_status', 'user_face_comparison_selector']);
        $callDetail = CallApproveService::getInstance($approvePool->order_id)->detail();
        $callDetail = array_except($callDetail, ['call_approve_result', 'contact_relation', 'refusal_code']);
        $data['firstDetail'] = $baseDetail ?? null;
        $data['callDetail'] = $callDetail ?? null;
        return $data;
    }

    /**
     * @param $statusArray
     * @return array
     */
    public function approveStatusList($statusArray = [])
    {
        $approveList = [
            ApprovePool::STATUS_WAITING => t('待审批', 'approve'),
            ApprovePool::STATUS_CHECKING => t('审批中', 'approve'),
            ApprovePool::STATUS_RETURN => t('已回退', 'approve'),
            ApprovePool::STATUS_REJECT => t('已拒绝', 'approve'),
            ApprovePool::STATUS_CLOSED => t('已关闭', 'approve'),
            ApprovePool::STATUS_CANCEL => t('已取消', 'approve'),
            ApprovePool::STATUS_PASS => t('已通过', 'approve'),
        ];

        $list = [
            // 全部
            'all' => array_keys($approveList),

            OrderStatus::FIRST_APPROVAL => [
                ApprovePool::STATUS_WAITING,
                ApprovePool::STATUS_CHECKING,
                ApprovePool::STATUS_RETURN,
                ApprovePool::STATUS_REJECT,
                ApprovePool::STATUS_CLOSED,
                ApprovePool::STATUS_CANCEL,
            ],
            OrderStatus::FIRST_APPROVAL_SUPPLEMENT => [
                ApprovePool::STATUS_WAITING,
                ApprovePool::STATUS_CHECKING,
                ApprovePool::STATUS_RETURN,
                ApprovePool::STATUS_REJECT,
                ApprovePool::STATUS_CLOSED,
                ApprovePool::STATUS_CANCEL,
            ],
            OrderStatus::CALL_APPROVAL => [
                ApprovePool::STATUS_WAITING,
                ApprovePool::STATUS_CHECKING,
                ApprovePool::STATUS_REJECT,
                ApprovePool::STATUS_PASS,
                ApprovePool::STATUS_CANCEL,
                ApprovePool::STATUS_CLOSED,
            ],
            OrderStatus::CALL_APPROVAL_SECOND => [
                ApprovePool::STATUS_WAITING,
                ApprovePool::STATUS_CHECKING,
                ApprovePool::STATUS_REJECT,
                ApprovePool::STATUS_PASS,
                ApprovePool::STATUS_CANCEL,
                ApprovePool::STATUS_CLOSED,
            ],
            OrderStatus::SYSTEM_REJECT => [
                ApprovePool::STATUS_REJECT,
            ],
        ];

        if (empty($statusArray)) {
            $checked = $list['all'];
        } else {
            $checked = [];
            foreach ($statusArray as $status) {
                if (isset($list[$status])) {
                    array_push($checked, ...$list[$status]);
                }
            }
            $checked = array_unique($checked);
        }

        $approveList = array_intersect_key(
            $approveList,
            array_flip($checked)
        );
        $nList = [0 => t('全部', 'approve')];
        foreach ($approveList as $k => $item) {
            // 有个状态是0,这里转换一下
            if ($k == 0) {
                $k = -1;
            }
            $nList[$k] = $item;
        }
        return ArrayHelper::arrToOption($nList, 'key', 'val');
    }

    /**
     * @param $poolId
     * @return array
     */
    protected function getSnapshot($poolId)
    {
        $data = [
            'firstDetail' => [],
            'callDetail' => [],
        ];
        $result = ApproveResultSnapshot::where('approve_pool_id', $poolId)
            ->get()
            ->groupBy('approve_type');
        foreach ($result as $k => $item) {
            if ($k == ApproveResultSnapshot::TYPE_FIRST_APPROVE) {
                $data['firstDetail'] = json_decode($item->last()->result, true);
            }
            if ($k == ApproveResultSnapshot::TYPE_CALL_APPROVE) {
                $data['callDetail'] = json_decode($item->last()->result, true);
            }
        }

        // 获取 OSS url
        $this->getOssUrl($data['firstDetail']);

        return $data;
    }

    /**
     * @param $data
     */
    public function getOssUrl(&$data)
    {
        if (!empty($data['bashDetail']['user_face'])) {
            if (!is_array($data['bashDetail']['user_face'])) {
                $data['bashDetail']['user_face'] = ImageHelper::getPicUrl($data['bashDetail']['user_face'], 0);
            }
        }

        if (!empty($data['bashDetail']['id_card'])) {
            $data['bashDetail']['id_card'] = ImageHelper::getPicUrl($data['bashDetail']['id_card'], 0);
        }

        if (!empty($data['bashDetail']['company_id'])) {
            $data['bashDetail']['company_id'] = ImageHelper::getPicUrl($data['bashDetail']['company_id'], 0);
        }

        if (!empty($data['bankStatement']['bank_statement_pdf']['bill'])) {
            $data['bankStatement']['bank_statement_pdf']['bill'] = ImageHelper::getPicUrl($data['bankStatement']['bank_statement_pdf']['bill'], 0);
        }

        if (!empty($data['paySlip']['payslip_pdf']['paySlip'])) {
            $data['paySlip']['payslip_pdf']['paySlip'] = ImageHelper::getPicUrl($data['paySlip']['payslip_pdf']['paySlip'], 0);
        }

        if (!empty($data['employeeCard']['employee_card'])) {
            foreach ($data['employeeCard']['employee_card'] as &$item) {
                $item['src'] = ImageHelper::getPicUrl($item['src'], 0);
            }
        }

    }

    public function backCase($poolIds) {
        $success = 0;
        if (is_array($poolIds)) {
            foreach ($poolIds as $poolID) {
                $approveUserPools = ApproveUserPool::where('approve_pool_id' , $poolID)
                        ->whereIn('status', [ApproveUserPool::STATUS_CHECKING,ApproveUserPool::STATUS_NO_ANSWER])->get();
                /** @var ApproveUserPool $approveUserPool */
                foreach ($approveUserPools as $approveUserPool) {
                    if (!$approveUserPool)
                        continue;

                    /** 挂起状态不进行过期处理*/
                    if ($approveUserPool->status == ApproveUserPool::STATUS_NO_ANSWER && $approveUserPool->admin_id==LoginHelper::getAdminId())
                        continue;

                    $approveUserPool->status = ApproveUserPool::STATUS_CANCEL;
                    $approveUserPool->save();

                    $approvePool = ApprovePool::where(['id' => $approveUserPool->approve_pool_id])->first();
                    if (!$approvePool)
                        continue;
                    // 审批池订单状态更新
                    $approvePool->status = ApprovePool::STATUS_WAITING;
                    $approvePool->save();

                    // 更新日志
                    ApproveAssignLog::where([
                        'order_id' => $approveUserPool->order_id,
                        'admin_id' => $approveUserPool->admin_id,
                        'approve_status' => ApproveAssignLog::STATUS_CHECKING,
                    ])->update(['approve_status' => ApproveAssignLog::STATUS_CANCEL]);
                    $success++;
                    break;
                }
            }
        }
        return $success;
    }

    public function turnCase($poolIds,$admin) {
        $admin = $admin ? $admin:LoginHelper::getAdminId();
        if (is_array($poolIds)) {
            foreach ($poolIds as $poolID) {
                $approveUserPools = ApproveUserPool::where('approve_pool_id' , $poolID)
                    ->whereIn('status', [ApproveUserPool::STATUS_CHECKING,ApproveUserPool::STATUS_NO_ANSWER])->get();
//                $approveUserPools = ApproveUserPool::where([
//                    'status' => ApproveUserPool::STATUS_CHECKING,
//                    'approve_pool_id' => $poolID
//                ])->get();
                if (!count($approveUserPools)){
                    return false;
                }
                /** @var ApproveUserPool $approveUserPool */
                foreach ($approveUserPools as $approveUserPool) {
                    if (!$approveUserPool)
                        continue;

                    /** 挂起状态不进行转单处理 */
                    if ($approveUserPool->status == ApproveUserPool::STATUS_NO_ANSWER && $approveUserPool->admin_id==$admin)
                        continue;
                    $approveUserPool->admin_id = $admin;
                    $approveUserPool->save();
                    $approvePool = ApprovePool::where(['id' => $approveUserPool->approve_pool_id])->first();
                    if (!$approvePool)
                        continue;

                    // 更新日志
//                    ApproveAssignLog::where([
//                        'order_id' => $approveUserPool->order_id,
//                        'admin_id' => $approveUserPool->admin_id,
//                        'approve_status' => $approveUserPool->status,
//                    ])->update(['admin_id' => LoginHelper::getAdminId()]);
                    $newAssignLog = ApproveAssignLog::where([
                        'order_id' => $approveUserPool->order_id])->orderByDesc("id")->first()->replicate();
                    if($newAssignLog){
                        $newAssignLog->admin_id = $admin;
                        $newAssignLog->save();
                    }
                }
            }
        }
        return true;
    }

}
