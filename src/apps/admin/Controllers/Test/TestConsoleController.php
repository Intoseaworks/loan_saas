<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Test;

use Admin\Controllers\BaseController;
use Common\Console\Services\Order\OrderBadServer;
use Illuminate\Support\Facades\Artisan;

class TestConsoleController extends BaseController
{
    /**
     * 分单
     *
     * @return array
     */
    public function collectionAssign()
    {
        Artisan::call("collection:assign");
        return $this->resultSuccess();
        /*# 新案
        $assignNewOrder = CollectionAssignServer::server();
        $assignNewOrder->assignNewOrder();
        # 案件流转
        $assignAgain = CollectionAssignServer::server();
        $assignAgain->assignAgain();
        return $this->resultSuccess($assignNewOrder->getMsg() . ' | ' . $assignAgain->getMsg());*/
    }

    public function flowCollectionBad()
    {
        $orderBadServer = OrderBadServer::server();
        $orderBadServer->orderToBad();
        return $this->resultSuccess($orderBadServer->getMsg());
    }

}
