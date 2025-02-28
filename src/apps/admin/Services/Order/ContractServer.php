<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:06
 */

namespace Admin\Services\Order;


use Admin\Exports\Order\ContractExport;
use Admin\Models\Order\Contract;
use Admin\Models\Order\Order;
use Admin\Models\User\UserInfo;
use Admin\Services\BaseService;
use Admin\Services\Data\DataServer;
use Common\Models\Common\Dict;

class ContractServer extends BaseService
{
    private $order;

    /**
     * @param null $orderId
     * @throws \Common\Exceptions\ApiException
     */
    public function __construct($orderId = null)
    {
        if ($orderId !== null && !$this->order = Order::model()->getOne($orderId)) {
            $this->outputException('订单数据不存在');
        }
    }

    public function list($params)
    {
        $query = Contract::model()->search($params, ['userInfo', 'orderFinish', 'orderOverdue']);
        if ($this->getExport()) {
            ContractExport::getInstance()->export($query, ContractExport::SCENE_LOAN);
        }

        $orders = $query->paginate(array_get($params, 'size'));
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($orders as $order) {
            /** @var $order Order */
            $order->user->telephone = \Common\Utils\Data\StringHelper::desensitization($order->user->telephone);
            $order->user->channel && $order->user->channel->getText(['channel_code', 'channel_name']);
            $order->user->getText(['telephone', 'fullname', 'quality']);
            $order->setScenario(Order::SCENARIO_LIST)->getText();
            # 取第一笔还款计划
            $order->receivable_amount = $order->repayAmount($order->lastRepaymentPlan);//应还金额
            if ($order->userInfo) {
                $order->userInfo->setScenario(UserInfo::SCENARIO_LIST)->getText();
                $order->userInfo->city = Dict::getNameByCode($order->userInfo->city);
                $order->userInfo->province = Dict::getNameByCode($order->userInfo->province);
            }
            $order->order_count = $order->orderFinish ? $order->orderFinish->where('created_at', '<', $order->created_at)->count() + 1 : 1;
            $order->overdue_order_count = $order->orderOverdue ? $order->orderOverdue->where('order_id', '<=', $order->id)->count() : 0;
            unset($order->orderFinish, $order->orderOverdue);
        }
        return $orders;
    }

    public function view()
    {
        $tabs = [
            DataServer::ORDER,
            DataServer::CONTRACT_LOG,
            DataServer::CONTRACT_AGREEMENT,
        ];
        return DataServer::server($this->order->user_id, $this->order->id)->list($tabs);
    }
}
