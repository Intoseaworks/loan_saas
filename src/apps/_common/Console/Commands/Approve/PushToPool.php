<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-29
 * Time: 21:45
 */

namespace Common\Console\Commands\Approve;

use Approve\Admin\Services\Order\ApproveOrderService;
use Approve\Admin\Services\Order\PushToPoolService;
use Illuminate\Console\Command;

class PushToPool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approve:push-to-pool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取审批订单';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = ApproveOrderService::getInstance()->getOrder();
        // 数组数据需要改动
        $params = [
            'data' => $data['data'] ?? [],
            'clear' => $data['clear'] ?? [],
            'initSnapshoot' => $data['initSnapshoot'] ?? [],
        ];
        if (PushToPoolService::getInstance(...array_values($params))->save()) {
            $orderIds = array_pluck($params['data'], 'order_id');
            ApproveOrderService::getInstance()->afterPush($orderIds);
        }
    }
}
