<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-12
 * Time: 15:58
 */

namespace Approve\Admin\Services\Order;


use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApprovePoolLog;
use Common\Models\Approve\ApproveResultSnapshot;
use Common\Models\Approve\ApproveUserPool;
use Common\Traits\GetInstance;

class PushToPoolService
{
    use GetInstance;

    /**
     * 审批单数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $clear = [];

    /**
     * 快照数据
     *
     * @var array
     */
    protected $initSnapshoot = [];

    /**
     * HandlePush constructor.
     * @param $data
     * @param $clear
     * @param $initSnapshoot
     */
    public function __construct($data, $clear, $initSnapshoot)
    {
        $this->data = (array)$data;
        $this->clear = (array)$clear;
        $this->initSnapshoot = (array)$initSnapshoot;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $pushData = $this->getData();

        $columns = [
            'merchant_id',
            'order_id',
            'type',
            'grade',
            'order_no',
            'user_id',
            'telephone',
            'order_type',
            'order_status',
            'status',
            'order_created_time',
            'manual_time',
            'risk_pass_time',
        ];

        foreach ($pushData as $data) {
            ApprovePool::updateOrCreate(['order_id' => $data['order_id']], array_only($data, $columns));
        }

        // 需要清除的审批单
        $this->handleClear();

        $orderIds = array_column($pushData, 'order_id');

        // 记录日志
        /** @var ApprovePool[] $approvePoolRows */
        $approvePoolRows = ApprovePool::whereIn('order_id', $orderIds)->get();
        $logs = [];
        $time = date('Y-m-d H:i:s');
        foreach ($approvePoolRows as $approvePoolRow) {
            $temp = [
                'approve_pool_id' => $approvePoolRow->id,
                'merchant_id' => $approvePoolRow->merchant_id,
                'order_id' => $approvePoolRow->order_id,
                'type' => $approvePoolRow->type,
                'grade' => $approvePoolRow->grade,
                'order_status' => $approvePoolRow->order_status,
                'created_at' => $time,
                'updated_at' => $time,
            ];
            $logs[] = $temp;
        }

        ApprovePoolLog::query()->insert($logs);

        return $this->saveSnapshoot($this->getInitSnapshoot(), $approvePoolRows->pluck('id', 'order_id')->toArray(), $approvePoolRows[0]->merchant_id ?? 0);
    }

    /**
     * @return array
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * @return void
     */
    protected function handleClear()
    {
        $clearData = $this->getClear();
        $orderIds = array_column($clearData, 'order_id');
        // 清除审批池
        ApprovePool::whereIn('order_id', $orderIds)
            ->whereIn('status', [ApprovePool::STATUS_WAITING, ApprovePool::STATUS_CHECKING, ApprovePool::STATUS_NOT_CONDITION])
            ->update(['status' => ApprovePool::STATUS_CANCEL]);
        // 清除用户审批池
        ApproveUserPool::whereIn('order_id', $orderIds)
            ->whereIn('status', [ApproveUserPool::STATUS_CHECKING])
            ->update(['status' => ApproveUserPool::STATUS_CANCEL]);
    }

    /**
     * @return array
     */
    protected function getClear()
    {
        return $this->clear;
    }

    /**
     * @param $snapshoot
     * @param $poolMap
     * @param $merchantId
     * @return bool
     */
    protected function saveSnapshoot($snapshoot, $poolMap, $merchantId)
    {
        $insertFuc = function (array $data, $type) use ($poolMap, $merchantId) {
            $insert = [];
            $time = date('Y-m-d H:i:s');
            foreach ($data as $k => $item) {
                $temp = [
                    'merchant_id' => $merchantId,
                    'approve_user_pool_id' => 0,
                    // $k 是order_id  $poolMap是以order_id为key的数组
                    'approve_pool_id' => $poolMap[$k] ?? 0,
                    'result' => $item,
                    'approve_type' => $type,
                    'created_at' => $time,
                    'updated_at' => $time,
                ];

                $insert[] = $temp;
            }
            ApproveResultSnapshot::query()->insert($insert);
        };

        if (!empty($snapshoot[ApproveResultSnapshot::TYPE_FIRST_APPROVE])) {
            $insertFuc($snapshoot[ApproveResultSnapshot::TYPE_FIRST_APPROVE], ApproveResultSnapshot::TYPE_FIRST_APPROVE);
        }

        if (!empty($snapshoot[ApproveResultSnapshot::TYPE_CALL_APPROVE])) {
            $insertFuc($snapshoot[ApproveResultSnapshot::TYPE_CALL_APPROVE], ApproveResultSnapshot::TYPE_CALL_APPROVE);
        }

        return true;
    }

    /**
     * @return array
     */
    protected function getInitSnapshoot()
    {
        return $this->initSnapshoot;
    }

    /**
     * 去重
     * @param array $records
     * @return array
     */
    protected function distinctOrder($records)
    {

        $records = collect($records);
        if ($records->isNotEmpty()) {

            $orderIds = $records->pluck('order_id');
            // @phan-suppress-next-line PhanUndeclaredFunctionInCallable
            $records = $records->keyBy('order_id');

            $exists = ApprovePool::select('order_id')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->keyBy('order_id');

            $records = $records->diffKeys($exists);
        }

        return $records->toArray();

    }
}
