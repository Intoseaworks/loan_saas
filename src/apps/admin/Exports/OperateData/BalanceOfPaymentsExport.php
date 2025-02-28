<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\OperateData;


use Admin\Services\OperateData\BalanceOfPaymentsServer;
use Common\Models\Order\OrderDetail;
use Common\Utils\Data\DateHelper;
use Common\Utils\Export\AbstractExport;

class BalanceOfPaymentsExport extends AbstractExport
{

    /**
     * 每日收支分析
     */
    const SCENE_LIST = 'SCENE_LIST';

    /**
     * 每日收入详情
     */
    const SCENE_INCOME = 'SCENE_INCOME';

    /**
     * 每日支出详情
     */
    const SCENE_DISBURSE = 'SCENE_DISBURSE';

    /**
     * @var null|BalanceOfPaymentsServer
     */
    protected $server;

    /**
     * BalanceOfPaymentsExport constructor.
     * @param array $parmas
     */
    public function __construct($parmas = [])
    {
        parent::__construct($parmas);
        $this->server = BalanceOfPaymentsServer::server();
    }

    /**
     * @param $scene
     * @return array
     */
    public function getColumns($scene = null)
    {

        $columns = [

            static::SCENE_LIST => [
                [
                    'name' => '日期',
                    'value' => function ($data) {
                        return DateHelper::formatToDate($data->trade_result_time);
                    },
                ],
                'income' => '当日总收入',
                'disburse' => '当日总支出',
                'principal' => '收入-实还本金',
                'overdue_fee' => '收入-实还罚息',
                'paid_amount' => '支出-实际到账金额',
            ],
            static::SCENE_INCOME => [
                'trade_account_name' => '姓名',
                'transaction_no' => '第三方交易编号',
                'order.order_no' => '合同编号',
                'principal' => '实还本金',
                'interest_fee' => '实还息费',
                'overdue_fee' => '实还罚息',
                'gst_penalty' => '实还罚息GST',
                'repay_time' => '实际还款时间',
                'pay_method' => '支付方式',
                'status' => '状态',
            ],
            static::SCENE_DISBURSE => [
                'trade_account_name' => '姓名',
                'transaction_no' => '第三方交易编号',
                'master_business_no' => '合同编号',
                'business_amount' => '放款金额',
                'bank_account' => '平台账户',
                'order.paid_time' => '打款时间',
                'pay_method' => '支付方式',
                'status' => '状态',
            ],
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $data
     * @return mixed|void
     */
    protected function beforePutCsv($data)
    {
        if ($this->sence == static::SCENE_LIST) {
            $date = date('Y-m-d', strtotime($data->trade_result_time));
            $data->date = $date;
            $money = $this->server->sumPrincipalOverdue($date);
            // 实还罚息
            $data->overdue_fee = $money->overdue_fee;
            // 实还本金
            $data->principal = $money->principal;
            // 实际到账金额
            $data->paid_amount = $this->server->sumPaidAmount($date)->paid_amount;
            // 收入 - 续期费用
            $data->renewal_fee = $this->server->sumRenewalFee($date) ?: '0.00';
        }

        if ($this->sence == static::SCENE_INCOME) {
            $repaymentPlan = $data->order->repaymentPlans->whereIn('id', $data->tradeLogDetail->pluck('business_id'));
            $calcMoney = $this->server->calcPrincipalOverdue($repaymentPlan);
            // 实还本金
            $data->principal = $calcMoney['principal'];
            // 实还息费
            $data->interest_fee = $calcMoney['interest_fee'];
            // 实还罚息
            $data->overdue_fee = $calcMoney['overdue_fee'];
            // 实还罚息
            $data->gst_penalty = $calcMoney['gst_penalty'];
            // 还款时间
            $data->repay_time = $data->trade_result_time;
            $data->pay_method = $data->trade_platform;
            $data->status = 'SUCCESS';
        }

        if ($this->sence == static::SCENE_DISBURSE) {
            $data->bank_account = $data->bank_name . '(' . substr(OrderDetail::model()->getBankCardNo($data->order),
                    -4) . ')';
            $data->pay_method = $data->trade_platform;
            $data->status = 'SUCCESS';
//            $data->platform_account = $this->server->platformAccount($data->adminTradeAccount);
//            $data->channel = $data->user->channel ? $data->user->channel->channel_code : '';
        }
    }
}
