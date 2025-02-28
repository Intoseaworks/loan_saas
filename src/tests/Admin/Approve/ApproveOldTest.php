<?php

namespace Tests\Admin\Approve;

use Common\Models\Approve\Approve;
use Tests\Admin\TestBase;

class ApproveOldTest extends TestBase
{
    /**
     * 待审批订单
     */
    public function testIndex()
    {
        $params = [
            'status' => [
            ]
        ];
        $this->json('GET', '/api/approve/index', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 人工审批列表
     */
    public function testApproveList()
    {
        $params = [
            'status' => [
            ]
        ];
        $this->json('GET', '/api/approve/approve-list', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 获取人工审批选项列表
     */
    public function testSelectGroup()
    {
        $params = [

        ];
        $this->json('GET', '/api/approve/select-group', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 测试人工审批 重新提交资料
     */
    public function testApproveSubmitReplenish($orderId = 0)
    {
        if ($orderId == 0) {
            $orderId = $this->getOrderId();
        }
        $params = [
//            'first' => '1',
            'order_id' => $orderId,
            'approve_result' => [Approve::SELECT_REPLENISH_FACE_NONSTANDARD],
            'remark' => 'remmmmmmmmark',
        ];
        $this->json('POST', '/api/approve/approve-submit', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 测试人工通过
     */
    public function testApproveSubmitPass($orderId = 0)
    {
        if ($orderId == 0) {
            $orderId = 1;
        }
        $params = [
//            'first' => '1',
            'order_id' => $orderId,
            'approve_result' => [Approve::SELECT_PASS],
            'remark' => 'remmmmmmmmark',
        ];
        $this->json('POST', '/api/approve/approve-submit', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 判断订单能否进入审批
     */
    public function testCanApprove()
    {
        $params = [
            'order_id' => 123,
        ];
        $this->json('GET', '/api/approve/can-approve', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData(true, true);
    }

    /**
     * 待审批订单
     */
    public function testApproveView()
    {
        $this->json('GET', '/api/approve/view', ['id' => 0])->seeJson([
            'code' => self::ERROR_CODE
        ]);
        $this->json('GET', '/api/approve/view', ['id' => 1])->seeJson([
            'code' => self::SUCCESS_CODE
        ]);
    }

    /**
     * 人工审批列表
     * @return mixed
     */
    public function testRejectList()
    {
        $params = [
            'approve_ids' => [1, 2],
            'status' => ['manual_reject', 'system_reject']
        ];
        $this->json('GET', '/api/approve/reject-list', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/approve/reject-list', $params);
        $this->assertResponseOk();
    }

    /**
     * 人工审批被拒原因
     * @return mixed
     */
    public function testRejectReason()
    {
        $params = [
            'id' => 70
        ];
        $this->json('GET', '/api/approve/reject-reason', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }
}
