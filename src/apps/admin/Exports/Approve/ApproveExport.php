<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Approve;

use Admin\Models\Order\Order;
use Admin\Models\Staff\Staff;
use Admin\Services\NewApprove\NewApproveServer;
use Common\Utils\Data\StringHelper;
use Common\Utils\Export\AbstractExport;

class ApproveExport extends AbstractExport
{
    /**
     * 放款订单
     */
    const SCENE_REJECT_LIST = 'SCENE_REJECT_LIST';

    /**
     * @var NewApproveServer
     */
    protected $server;

    /**
     * ApproveExport constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->server = NewApproveServer::server();
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_REJECT_LIST => [
                'order_no' => '订单号',
                'user.fullname' => '真实姓名',
                'user.telephone' => '手机号码',
                'principal' => '借款金额',
                'loan_days' => '借款期限',
                'interest_fee' => '综合费用',
                'created_at' => '订单创建时间',
                'approver' => '审批人',
                'status_text' => '被拒状态',
                'reject_reason' => '拒单原因',
            ],
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $order
     * @return mixed|void
     */
    protected function beforePutCsv($order)
    {
        if ($this->sence == static::SCENE_REJECT_LIST) {
            /** @var Order $order */
            $order->user && $order->user->getText(['telephone', 'fullname', 'quality']);
            $order->setScenario(Order::SCENARIO_LIST)->getText();

            $rejectReason = ts($this->server->getRejectReasonText($order->id, $order->status), 'approve');
            $order->reject_reason = is_array($rejectReason) ? implode(',', $rejectReason) : $rejectReason;
            $approverId = $this->server->getRejectApproverIdByOrderId($order->id);
            $order->approver = Staff::model()->getNameById($approverId) ?? '---';
        }
        $order->user->telephone = StringHelper::maskTelephone($order->user->telephone);
    }
}
