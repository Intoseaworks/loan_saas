<?php

namespace Api\Services\SaasMaster;

use Api\Models\Order\Order;
use Api\Services\BaseService;
use Common\Models\SystemApprove\SystemApproveTask;

class PullConsumeLogServer extends BaseService
{
    public function pullOrderData($timeStart, $timeEnd)
    {
        // 查找 已放款(待还款&已结清&还款中)的订单
        $orders = Order::query()->with('merchant')->select(['merchant_id', 'order_no', 'paid_time', 'user_id',])
            ->whereBetween('paid_time', [$timeStart, $timeEnd])
            ->whereIn('status', array_merge(Order::WAIT_REPAYMENT_STATUS, Order::FINISH_STATUS, [Order::STATUS_REPAYING]))
            ->get();

        $data = [];
        foreach ($orders as $order) {
            if (!$order->merchant) {
                continue;
            }

            $data[] = [
                'unique_no' => $order->order_no,
                'app_key' => $order->merchant->app_key,
                'trade_time' => $order->paid_time,
                'user_id' => $order->user_id,
                'content' => '', // 附加信息
            ];
        }

        return collect($data)->toJson();
    }

    public function pullSystemApproveData($timeStart, $timeEnd)
    {
        $records = SystemApproveTask::query()->with('merchant')
            ->select(['merchant_id', 'id', 'created_at', 'user_id', 'result'])
            ->whereBetween('created_at', [$timeStart, $timeEnd])
            ->get();

        $data = [];
        foreach ($records as $record) {
            if (!$record->merchant) {
                continue;
            }

            $data[] = [
                'unique_no' => $record->merchant->merchant_no . "_" . $record->id,
                'app_key' => $record->merchant->app_key,
                'trade_time' => (string)$record->created_at,
                'user_id' => $record->user_id,
                'content' => json_encode(['result' => $record->result]), // 附加信息
            ];
        }

        return collect($data)->toJson();
    }
}
