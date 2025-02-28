<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-21
 * Time: 19:53
 */

namespace Approve\Admin\Services\Log;

use Approve\Admin\Services\CommonService;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\Order\Order;
use Common\Models\Order\OrderLog;
use Common\Traits\GetInstance;

class ApproveLogService
{
    use GetInstance;

    /**
     * @param $orderId
     * @param $type
     * @param null|integer $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLog($orderId, $type, $page = null)
    {
        if ($type == 1) {
            $type = [1];
        } else {
            $type = [1, 2];
        }
        $data = ManualApproveLog::where(['order_id' => $orderId])
            ->whereIn('approve_type', $type)
            ->select(['order_id', 'admin_id', 'result', 'created_at', 'to_order_status', 'from_order_status'])
            ->with(['user' => function ($query) {
                $query->select(['id', 'username', 'nickname']);
            }])
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'page', $page);

        /** @var ManualApproveLog $item */
        foreach ($data->items() as $item) {
            $orderStatus = $this->getRealOrderStatus($item->order_id, $item->created_at, $item->from_order_status) ?: $item->from_order_status;
            $item->order_status = CommonService::getInstance()->orderStatus($orderStatus);
            $item->submission_time = $item->created_at;
            //$item->approve_result = t($item->resultToText(), 'approve');
            $item->approve_result = $item->result;
            $item->admin_user = $item->user->nickname ?? '';
            unset($item->result);
            unset($item->user);
        }

        return $data;
    }
    
    public function getLogNew($orderId, $type, $page = 1){
        $sql = "select a.*,staff.nickname as admin_user from (select order_id,admin_id,created_at,'' as from_order_status, 'Assign' result from approve_assign_log where order_id='{$orderId}'
UNION
select order_id,admin_id,created_at,from_order_status,result from manual_approve_log where order_id='{$orderId}')a
INNER JOIN staff on staff.id=a.admin_id
order by a.created_at asc";
        $pageSize = 10;
        $currentPage = $page ?? 1;
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $res = \DB::select($sql);
        $data = [];
        $data['total'] = count($res);
        $data['data'] = \DB::select($sql . $limit);
        foreach ($data['data'] as $item) {
            $orderStatus = $this->getRealOrderStatus($item->order_id, $item->created_at, $item->from_order_status) ?: $item->from_order_status;
            if($item->result == 'Assign'){
                $order = OrderLog::model()->getLastStatusByCreatedTime($item->order_id, $item->created_at);
                if($order){
                    $orderStatus = $order->to_status;
                }
            }
            $item->order_status = CommonService::getInstance()->orderStatus($orderStatus);
            $item->submission_time = $item->created_at;
            $item->approve_result = $item->result;
        }
        $data['page_total'] = ceil($data['total'] / $pageSize);
        $data['current_page'] = $currentPage;
        $data['per_page'] = $pageSize;
        return $data;
    }
    
    /**
     * @param $orderId
     * @param $time
     * @param $fromOrderStatus
     * @return bool|string
     */
    public function getRealOrderStatus($orderId, $time, $fromOrderStatus)
    {
        if ($fromOrderStatus == Order::STATUS_WAIT_MANUAL_APPROVE) {
            // 查看是否是待补充资料的
            $count = OrderLog::where('order_id', $orderId)
                ->where('from_status', Order::STATUS_REPLENISH)
                ->where('to_status', Order::STATUS_WAIT_MANUAL_APPROVE)
                ->where('created_at', '<=', $time)
                ->count();
            // 状态26是从39流转的(39->26)
            if ($count > 0) {
                return Order::STATUS_REPLENISH;
            } else {
                return Order::STATUS_WAIT_MANUAL_APPROVE;
            }
        }

        return false;
    }

    /**
     * @param $orderId
     * @param $tpye
     * @return string
     */
    public function getFirstLog($orderId, $tpye)
    {
        $log = ManualApproveLog::where(['order_id' => $orderId, 'approve_type' => $tpye])
            ->orderBy('id', 'DESC')
            ->first();
        if ($log) {
            return $log->resultToText();
        }

        return '';
    }

    /**
     * @param $orderId
     * @param $tpye
     * @return ManualApproveLog
     */
    public function getFirstLogInstance($orderId, $tpye)
    {
        return $log = ManualApproveLog::where(['order_id' => $orderId, 'approve_type' => $tpye])
            ->orderBy('id', 'DESC')
            ->first();
    }
}
