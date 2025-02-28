<?php

namespace Admin\Services\OperateData;

use Admin\Exports\OperateData\BalanceOfPaymentsExport;
use Admin\Models\Order\RepaymentPlan;
use Admin\Models\Trade\TradeLog;
use Admin\Services\BaseService;
use Common\Models\Order\OrderDetail;
use Common\Models\Trade\AdminTradeAccount;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\Upload\ImageHelper;
use Illuminate\Support\Facades\DB;

class BalanceOfPaymentsServer extends BaseService {

    /**
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list($params) {
        $size = array_get($params, 'size');
        // 生成时间
        $startDate = $endDate = '';
        if ($date = array_get($params, 'date')) {
            $params['trade_result_time'] = [current($date), DateHelper::endOfDay(last($date))];
            $startDate = $params['trade_result_time'][0];
            $endDate = $params['trade_result_time'][1];
        }
        $params['trade_result'] = TradeLog::TRADE_RESULT_SUCCESS;
        $query = TradeLog::model()->search($params);
        $dateFormat = "%Y-%m-%d";
        if (isset($params['date_type'])) {
            switch ($params['date_type']) {
                case "Y":
                    $dateFormat = "%Y";
                    break;
                case "M":
                    $dateFormat = "%Y/%m";
                    break;
                case "U":
                    $dateFormat = "%Y#%U";
                    break;
            }
        }
        $columns = [
            DB::raw('SUM(IF(trade_type =' . TradeLog::TRADE_TYPE_RECEIPTS . ',trade_amount,0)) income,
                    SUM(IF(trade_type = ' . TradeLog::TRADE_TYPE_REMIT . ',trade_amount,0)) disburse,
                    COUNT(IF(trade_type =' . TradeLog::TRADE_TYPE_RECEIPTS . ',true,null)) income_count,
                    COUNT(IF(trade_type = ' . TradeLog::TRADE_TYPE_REMIT . ',true,null)) disburse_count'),
            DB::raw("DATE_FORMAT(trade_result_time,'{$dateFormat}') as trade_result_time"),
            'id',
        ];

        $query->select($columns)->groupBy(DB::raw("DATE_FORMAT(trade_result_time,'{$dateFormat}')"));
        $query->orderBy('trade_result_time', 'desc');

        // 导出
        if ($this->getExport()) {
            BalanceOfPaymentsExport::getInstance()->export($query, BalanceOfPaymentsExport::SCENE_LIST);
        }
        $data = $query->paginate($size);

        /** @var TradeLog $item */
        foreach ($data->items() as $item) {
            $date = $item->trade_result_time;
            $item->date = $date;
            $money = $this->sumPrincipalOverdueByDateType($dateFormat, $date, $startDate, $endDate) ?? '0.00';
            // 收入 - 实还罚息
            $item->overdue_fee = $money->overdue_fee ?? '0.00';
            // 收入 - 实还本金
            $item->principal = $money->principal ?? '0.00';
            // 支出 - 实际到账金额(放款金额)
            $item->paid_amount = $this->sumPaidAmountByDateType($dateFormat, $date, $startDate, $endDate)->paid_amount ?? '0.00';
            // 收入 - 续期费用
            $item->renewal_fee = $this->sumRenewalFee($date) ?? '0.00';
        }

        return $data;
    }

    /**
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function incomeList($params) {
        $size = array_get($params, 'size');
        $query = $this->buildListQuery($params)->where('trade_type', TradeLog::TRADE_TYPE_RECEIPTS);

        // 导出
        if ($this->getExport()) {
            BalanceOfPaymentsExport::getInstance()->export($query, BalanceOfPaymentsExport::SCENE_INCOME);
        }

        $rows = $query->paginate($size);

        $data = [];
        /** @var TradeLog $item */
        foreach ($rows->items() as $item) {
            $temp = [];
            $temp['id'] = $item->id;
            $temp['order_no'] = $item->order->order_no;
            $temp['user_name'] = $item->trade_account_name;
            $temp['contract_url'] = ImageHelper::getPicUrl(optional($item->order->contractAgreementCashnowLoan)->url);
            $temp['transaction_no'] = $item->trade_platform_no;

            $repaymentPlan = $item->order->repaymentPlans->whereIn('id', $item->tradeLogDetail->pluck('business_id'));
            $calcMoney = $this->calcPrincipalOverdue($repaymentPlan);
            // 实还本金
            $temp['principal'] = $calcMoney['principal'];
            $temp['interest_fee'] = $calcMoney['interest_fee'];
            // 实还罚息
            $temp['penalty'] = $calcMoney['overdue_fee'];
            $temp['gst_penalty'] = $calcMoney['gst_penalty'];
            // 还款时间
            $temp['repay_time'] = $item->trade_result_time;
            $temp['pay_method'] = $item->trade_platform;
            $temp['status'] = 'SUCCESS';
            $data[] = $temp;
        }

        $rows->setCollection(collect($data));

        return $rows;
    }

    /**
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function disburseList($params) {
        $size = array_get($params, 'size');
        $query = $this->buildListQuery($params)
                ->where('trade_type', TradeLog::TRADE_TYPE_REMIT);

        // 导出
        if ($this->getExport()) {
            BalanceOfPaymentsExport::getInstance()->export($query, BalanceOfPaymentsExport::SCENE_DISBURSE);
        }

        $rows = $query->paginate($size);

        $data = [];
        /** @var TradeLog $item */
        foreach ($rows->items() as $item) {
            $temp = [];
            $temp['id'] = $item->id;
            $temp['order_no'] = $item->master_business_no;
            $temp['contract_url'] = ImageHelper::getPicUrl(optional($item->order->contractAgreementCashnowLoan)->url,
                            0);
            $temp['transaction_no'] = $item->trade_platform_no;
            $temp['user_name'] = $item->trade_account_name;
            // 实际到账金额
            $temp['paid_amount'] = $item->business_amount;
            // 打款时间
            $temp['paid_time'] = $item->trade_result_time;
            $temp['bank_account'] = $item->bank_name . '(' . substr(OrderDetail::model()->getBankCardNo($item->order),
                            -4) . ')';
            // 财务人员
            $temp['pay_method'] = $item->trade_platform;
            $temp['status'] = 'SUCCESS';
            $data[] = $temp;
        }

        $rows->setCollection(collect($data));

        return $rows;
    }

    /**
     * @param $params
     * @return TradeLog|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    protected function buildListQuery($params) {
        $date = [DateHelper::startOfDay($params['date']), DateHelper::endOfDay($params['date'])];
        $query = TradeLog::query()
                ->whereBetween('trade_result_time', $date)
                ->with([
                    'order' => function ($query) {
                        $query->select(['id', 'pay_channel', 'principal', 'order_no', 'paid_time', 'paid_amount']);
                    },
                    'user' => function ($query) {
                        $query->select(['id', 'channel_id', 'fullname', 'telephone']);
                    },
                    'user.channel' => function ($query) {
                        $query->select(['id', 'channel_code']);
                    },
                    'adminTradeAccount',
                    'order.repaymentPlans' => function ($query) use ($date) {
                        $query->select([
                            'id',
                            'order_id',
                            'principal',
                            'overdue_fee',
                            'repay_time',
                            'interest_fee',
                            'gst_penalty',
                            'gst_processing'
                        ])
                        // order和repayment_plan是一对多的关系.这里只查对应日期的数据
                        ->whereBetween('repay_time', $date)
                        ->where('status', RepaymentPlan::STATUS_FINISH);
                    },
                ])
                ->where('trade_result', TradeLog::TRADE_RESULT_SUCCESS);

        return $query->orderBy('id', 'desc');
    }

    /**
     * @param RepaymentPlan[] $repaymentPlans
     * @return array
     */
    public function calcPrincipalOverdue($repaymentPlans) {
        $calc = [
            'principal' => 0,
            'overdue_fee' => 0,
            'interest_fee' => 0,
            'gst_penalty' => 0,
        ];
        foreach ($repaymentPlans as $repaymentPlan) {
            $calc['principal'] += $repaymentPlan->principal;
            $calc['overdue_fee'] += $repaymentPlan->overdue_fee;
            $calc['interest_fee'] += $repaymentPlan->interest_fee;
            $calc['gst_penalty'] += $repaymentPlan->gst_penalty;
        }

        return array_map([MoneyHelper::class, 'round2point'], $calc);
    }

    /**
     * @param $repayTime
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function sumPrincipalOverdue($repayTime) {
        return DB::table('repayment_plan')
                        ->select(DB::raw('SUM(overdue_fee)+SUM(gst_penalty)-SUM(reduction_fee) overdue_fee,SUM(principal) principal'))
                        ->whereBetween('repay_time', [DateHelper::startOfDay($repayTime), DateHelper::endOfDay($repayTime)])
                        ->where('status', RepaymentPlan::STATUS_FINISH)
                        ->first();
    }

    /**
     * @param $repayTime
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function sumPrincipalOverdueByDateType($dateFormat, $dateVal, $startDate = '', $endDate = '') {
        $query = DB::table('repayment_plan')
                ->select(DB::raw('SUM(overdue_fee)+SUM(gst_penalty)-SUM(reduction_fee) overdue_fee,SUM(principal) principal'))
                ->where(DB::raw("DATE_FORMAT(repay_time,'{$dateFormat}')"), $dateVal)
                ->where('status', RepaymentPlan::STATUS_FINISH);
        if ($startDate && $endDate) {
            $query->whereBetween('repay_time', [DateHelper::startOfDay($startDate), DateHelper::endOfDay($endDate)]);
        }
        return $query->first();
    }

    /**
     * @param $paidTime
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function sumPaidAmount($paidTime) {
        return DB::table('order')
                        ->select(DB::raw('SUM(paid_amount) paid_amount'))
                        ->whereBetween('paid_time', [DateHelper::startOfDay($paidTime), DateHelper::endOfDay($paidTime)])
                        ->first();
    }

    /**
     * @param $paidTime
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function sumPaidAmountByDateType($dateFormat, $dateVal, $startDate = '', $endDate = '') {
        $query = DB::table('order')
                ->select(DB::raw('SUM(paid_amount) paid_amount'))
                ->where(DB::raw("DATE_FORMAT(paid_time,'{$dateFormat}')"), $dateVal);
        if ($startDate && $endDate) {
            $query->whereBetween('paid_time', [DateHelper::startOfDay($startDate), DateHelper::endOfDay($endDate)]);
        }
        return $query->first();
    }

    /**
     * 收入 - 续期费用
     * @param $date
     * @return string
     */
    public function sumRenewalFee($date) {
        $where = [
            'business_type' => TradeLog::BUSINESS_TYPE_RENEWAL,
            'trade_evolve_status' => TradeLog::TRADE_EVOLVE_STATUS_OVER,
            'trade_result' => TradeLog::TRADE_RESULT_SUCCESS,
        ];
        return TradeLog::query()
                        ->where($where)
                        ->whereBetween('trade_result_time', [DateHelper::startOfDay($date), DateHelper::endOfDay($date)])
                        ->sum('business_amount');
    }

    /**
     * 废弃
     * @param AdminTradeAccount $adminTradeAccount
     * @return string
     */
    public function platformAccount(AdminTradeAccount $adminTradeAccount) {
        if ($adminTradeAccount->payment_method == AdminTradeAccount::PAYMENT_METHOD_BANK) {
            return "{$adminTradeAccount->bank_name} (尾号" . substr($adminTradeAccount->account_no, -4) . ')';
        }

        return $adminTradeAccount->account_no;
    }

    /**
     * @param $paymentMethod
     * @return mixed
     */
    public function getPayMethod($paymentMethod) {
        return AdminTradeAccount::PLATFORM_ALIAS[$paymentMethod];
    }

}
