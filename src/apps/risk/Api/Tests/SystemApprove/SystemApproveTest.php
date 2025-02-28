<?php
/**
 * Created by PhpStorm.
 * User: Sun
 * Date: 2020/3/13
 * Time: 18:28
 * author: soliang
 */

namespace Risk\Api\Tests\SystemApprove;

use Common\Utils\MerchantHelper;
use Risk\Admin\Tests\TestBase;
use Risk\Common\Events\SystemApprove\SystemApproveFinishEvent;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\Order\OrderDetail;
use Risk\Common\Models\Task\Task;
use Risk\Common\Models\UserAssociated\UserAssociatedInfo;
use Risk\Common\Services\Risk\RiskPositionServer;
use Risk\Common\Services\SystemApprove\RuleData\RuleData;
use Risk\Common\Services\SystemApprove\SystemApproveServer;
use Risk\Common\Services\UserAssociated\UserAssociatedRecordServer;

class SystemApproveTest extends TestBase
{
    /**
     * 机审总流程
     */
    public function testRulePasses()
    {
        // 6000001099|APWPD5178H
        $order = Order::with([])->where('id', 1114)->first(); // 451 517

        MerchantHelper::setMerchantId($order->app_id);

        $res = SystemApproveServer::server()->rulePasses($order);

        dd($res, 'end');
    }

    /**
     * basic 规则
     * @throws \Exception
     */
    public function testSystemApproveBasic()
    {
        $order = Order::with([])->where('id', 389)->first(); // 923 985

        MerchantHelper::setMerchantId($order->app_id);

        $result = SystemApproveServer::server()->basicRulePasses($order);

        dd($result, 'end');
    }

    /**
     * 执行用户关联数据更新
     */
    public function testUpdateUserAssociatedRecord()
    {
        MerchantHelper::setMerchantId(75);
        $userId = 996;
        UserAssociatedRecordServer::server($userId)->handle();
    }

    /**
     * 机审完成事件
     */
    public function testSystemApproveFinishEvent()
    {
//        $record = RiskAssociatedRecord::query()->find(1);
//
//        $record->refreshVar();
//        dd();

//        $order = Order::with([])->where('id', 1150)->first(); // 451 517

        $task = Task::query()->where('id', 168)->first();
//        $task = Task::query()->where('id', 132)->first();

        $res = event(new SystemApproveFinishEvent($task->id, $task->app_id, $task->order_no));

        dd($res, 'end');
    }

    public function testPosition()
    {
        $order = Order::with([])->where('id', 451)->first(); // 451 517

        MerchantHelper::setMerchantId($order->app_id);

        $res = RiskPositionServer::server()->hasApplyWithinRadiusCount($order, 1, 500);
        dd($res);
    }

    public function testAssociated()
    {
//        UserAssociatedInfo::getAssociatedByUserId(209, [UserAssociatedInfo::TYPE_IMEI]);

        $order = Order::with([])->where('id', 451)->first(); // 451 517

        MerchantHelper::setMerchantId($order->app_id);

        $res = (new RuleData($order))->getAssociatedOrderWithoutMerchantScope(UserAssociatedInfo::CORE_TYPE);

        $merchantId = MerchantHelper::getMerchantId();

        dd($res->count(), $merchantId);
    }

    public function testLocationNotInIndia()
    {
        $order = Order::with([])->where('id', 1117)->first(); // 451 517
        $location = (new OrderDetail())->getLocation($order);

        if (!$location) {
            return false;
        }

        dd(!str_contains($location, 'India'));
    }
}
