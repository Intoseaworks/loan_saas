<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Tests\Services\Remit;

use Tests\Admin\TradeManage\RemitTest;
use Tests\Services\BaseService;

class RemitTestServer extends BaseService
{
    /**
     * 出款全流程
     *
     * @param $userId
     */
    public function remit($orderId)
    {
        //人工出款
        $remitTest = new RemitTest();
        $remitTest->setUp();
        //人工出款详情
        $remitTest->testManualRemitDetail($orderId);
        //人工出款提交
        $remitTest->testManualRemitSubmit($orderId);
    }
}
