<?php

namespace Admin\Services\Order;

use Admin\Exports\Repayment\RepaymentPlanExport;
use Admin\Models\Collection\Collection;
use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Admin\Models\User\User;
use Admin\Services\BaseService;
use Admin\Services\Data\DataServer;

class RepaymentPlanServer extends BaseService
{
    private $order;

    /**
     * @param null $orderId
     * @throws \Common\Exceptions\ApiException
     */
    public function __construct($orderId = null)
    {
        if ($orderId !== null && !$this->order = Order::model()->getOne($orderId)) {
            $this->outputException(t('订单数据不存在', 'exception'));
        }
    }

    public function repaymentPlanList($params, $sortDefault = null)
    {
        if (!is_null($sortDefault)) {
            $params['sort'] = $sortDefault;
        }
        $query = RepaymentPlan::model()->search($params, ['order', 'user']);
        if ($this->getExport()) {
            RepaymentPlanExport::getInstance()->export($query, $params['export_scene']);
        }

        $size = array_get($params, 'size');
        $datas = $query->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($datas as $data) {
            /** @var $data Order */
            $data->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
            $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
            $data->user->userInfo && $data->user->email = $data->user->userInfo->email;
            $data->user->telephone = \Common\Utils\Data\StringHelper::desensitization($data->user->telephone);
            $data->user && $data->user->setScenario(User::SCENARIO_INFO)->getText();
            $data->order && $data->order->setScenario(Order::SCENARIO_REPAYMENT_PLAN)->getText();
            //$data->collection && $data->collection->setScenario(Collection::SCENARIO_SIMPLE)->getText();
            //unset($data->lastRepaymentPlan);
        }
        return $datas;
    }

    /**
     * 还款计划列表
     * @param $params
     * @param $sortDefault
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list($params, $sortDefault = null)
    {
        if (!is_null($sortDefault)) {
            $params['sort'] = $sortDefault;
        }
        $query = Order::model()->search($params);

        $query->whereHas('repaymentPlans', function ($query) use ($params) {
            #应还款时间
            if ($paidTime = array_get($params, 'appointment_paid_time')) {
                if (count($paidTime) == 2) {
                    $timeStart = current($paidTime);
                    $timeEnd = last($paidTime);
                    $query->whereBetween('appointment_paid_time', [$timeStart, $timeEnd]);
                }
            }
        });

        if ($this->getExport()) {
            RepaymentPlanExport::getInstance()->export($query, $params['export_scene']);
        }

        $size = array_get($params, 'size');
        $datas = $query->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($datas as $data) {
            /** @var $data Order */
            $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
            $data->user->userInfo && $data->user->email = $data->user->userInfo->email;
            $data->user && $data->user->setScenario(User::SCENARIO_INFO)->getText();
            $data->collection && $data->collection->setScenario(Collection::SCENARIO_SIMPLE)->getText();
            $data->setScenario(Order::SCENARIO_LIST)->getText();
            unset($data->lastRepaymentPlan);
        }
        return $datas;
    }

    /**
     * 还款计划详情
     * @return array
     */
    public function view($repaymentPlanId)
    {
        if(!($repaymentPlan = RepaymentPlan::model()->getOne($repaymentPlanId))){
            return $this->outputException(t('还款计划不存在', 'exception'));
        }
        if(!($this->order = $repaymentPlan->order)){
            return $this->outputException(t('订单数据不存在', 'exception'));
        }
        $tabs = [
            DataServer::ORDER,
            DataServer::CONTRACT_LOG,
            DataServer::CONTRACT_AGREEMENT,
            DataServer::REPAY_LOG
        ];
        return DataServer::server($this->order->user_id, $this->order->id)->list($tabs);
    }

    /**
     * 已还款列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paidList($params)
    {
        if ($this->getExport()) {
            $params['export_scene'] = RepaymentPlanExport::SCENE_REPAYMENT_PAID_LIST;
        }
        $params['status'] = Order::FINISH_STATUS;
        return $this->list($params);
    }

    /**
     * 已还款详情
     * @return array
     */
    public function paidView()
    {
        $tabs = [
            DataServer::ORDER,
            DataServer::CONTRACT_LOG,
            DataServer::CONTRACT_AGREEMENT,
            DataServer::REPAY_LOG
        ];
        return DataServer::server($this->order->user_id, $this->order->id)->list($tabs);
    }

    /**
     * 已逾期列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function overdueList($params)
    {
        if ($this->getExport()) {
            $params['export_scene'] = RepaymentPlanExport::SCENE_REPAYMENT_OVERDUE_LIST;
        }
        $params['status'] = Order::STATUS_OVERDUE;
        return $this->list($params);
    }

    /**
     * 已逾期/已坏账详情
     * @return array
     */
    public function overdueView()
    {
        $tabs = [
            DataServer::ORDER,
            DataServer::CONTRACT_LOG,
            DataServer::CONTRACT_AGREEMENT,
            DataServer::COLLECTION_RECORD_LIST,
        ];
        return DataServer::server($this->order->user_id, $this->order->id)->list($tabs);
    }

    /**
     * 已坏账列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function badList($params)
    {
        if ($this->getExport()) {
            $params['export_scene'] = RepaymentPlanExport::SCENE_REPAYMENT_BAD_DEBT_LIST;
        }
        $params['status'] = Order::STATUS_COLLECTION_BAD;
        return $this->list($params);
    }
}
