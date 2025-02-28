<?php

namespace Admin\Services\Repayment;

use Admin\Exports\Repayment\RepaymentPlanExport;
use Admin\Models\Order\RepaymentPlan;
use Admin\Models\Order\RepaymentPlanRenewal;
use Admin\Models\User\User;
use Admin\Services\BaseService;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Data\DateHelper;

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-6
 * Time: 上午11:34
 */
class RenewalRepaymentServer extends BaseService
{
    public $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 展期管理 - 预 列表
     */
    public function preList()
    {
        $params = $this->params;

        $params['installment_num'] = 1;
        $params['status'] = [RepaymentPlan::STATUS_PART_REPAY,RepaymentPlan::STATUS_CREATE, RepaymentPlan::STATUS_RENEWAL];

        $sortDefault = '-appointment_paid_time';
        if (!is_null($sortDefault)) {
            $params['sort'] = $sortDefault;
        }
        $query = RepaymentPlan::model()->search($params, ['order', 'user']);
        if ($this->getExport()) {
            RepaymentPlanExport::getInstance()->export($query, $params['export_scene']);
        }

        $size  = array_get($params, 'size');
        $datas = $query->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach

        foreach ($datas as & $data) {
            $subject = CalcRepaymentSubjectServer::server($data)->getSubject();

            $renewalPreInfo = [
                'renewal_days'                   => RepaymentPlanRenewal::RENEWAL_DEFAULT_DAYS,
                'renewal_paid_amount'            => $subject->renewalPaidAmount,
                'appointment_paid_time'          => $subject->appointmentPaidTime,
                'valid_period'                   => date('Y/m/d'),
                'extend_appointment_paid_amount' => $subject->repaymentPaidAmount
            ];

            $data->renewalPreInfo = $renewalPreInfo;

            /** @var $data RepaymentPlan */
            $data->user && $data->user->setScenario(User::SCENARIO_INFO)->getText();
            unset($data->order->lastRepaymentPlan);
            unset($data->order->orderDetails);
        }
        return $datas;
    }

    /**
     * 申请续期
     */
    public function applyRenewal()
    {
        /** @var RepaymentPlan $repaymentPlan */
        $repaymentPlan = RepaymentPlan::getByNo($this->params['no']);

        if ($repaymentPlan->status == RepaymentPlan::STATUS_FINISH) {
            return $this->outputError('该笔还款计划已完结');
        }

        $subject = CalcRepaymentSubjectServer::server($repaymentPlan)->getSubject();
        $data    = [
            'merchant_id'              => $repaymentPlan->merchant_id,
            'order_id'                 => $repaymentPlan->order_id,
            'renewal_days'             => RepaymentPlanRenewal::RENEWAL_DEFAULT_DAYS,
            'repayment_plan_id'        => $repaymentPlan->id,
            'uid'                      => $repaymentPlan->user_id,
            'valid_period'             => time() > strtotime($repaymentPlan->appointment_paid_time) ? date('Y-m-d') : date('Y-m-d', strtotime($repaymentPlan->appointment_paid_time)),
            'status'                   => RepaymentPlanRenewal::STATUS_CREATE,
            'payable_renewal_amount'   => $subject->renewalPaidAmount,
        ];


        $repaymentRenewal = new RepaymentPlanRenewal();

        $repaymentRenewalToday = RepaymentPlanRenewal::model()->where('repayment_plan_id',$repaymentPlan->id)
            ->where('status',RepaymentPlanRenewal::STATUS_CREATE)
            ->whereDate('created_at',date('Y-m-d'))->first();
        //一次一天一个订单只能展期一次
        if ( $repaymentRenewalToday ){
            \Log::info('一天一个订单只能展期一次----订单id'.$repaymentRenewalToday->order_id);
            return $this->outputError('An order can only be renewed once a day!');
        }

        if ($existsRepaymentRenewal = (new RepaymentPlanRenewal())->getValidRenewal($repaymentPlan->id)) {
            $existsRepaymentRenewal->update($data);

            /** 发送展期短信 */
            event(new OrderFlowPushEvent($repaymentPlan->order, OrderFlowPushEvent::TYPE_RENEWAL_SUCCESS));

            return $existsRepaymentRenewal;
        }
        /** 发送展期短信 */
        event(new OrderFlowPushEvent($repaymentPlan->order, OrderFlowPushEvent::TYPE_RENEWAL_SUCCESS));
        return $repaymentRenewal->add($data);
    }

    /**
     * 续期列表
     */
    public function renewalList()
    {
        $params = $this->params;

        $query = RepaymentPlanRenewal::model()->search($params);

        $size  = array_get($params, 'size');
        $datas = $query->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($datas as $data) {
            //     /** @var $data Order */
            $data->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
            //     $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
            //     $data->user->userInfo && $data->user->email = $data->user->userInfo->email;
            //     $data->user && $data->user->setScenario(User::SCENARIO_INFO)->getText();
            //     $data->order && $data->order->setScenario(Order::SCENARIO_REPAYMENT_PLAN)->getText();
            //     //$data->collection && $data->collection->setScenario(Collection::SCENARIO_SIMPLE)->getText();
            //     //unset($data->lastRepaymentPlan);
        }
        return $datas;
    }
}
