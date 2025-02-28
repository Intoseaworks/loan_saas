<?php

namespace Admin\Services\OperateData;

use Admin\Exports\OperateData\PostLoanExport;
use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Admin\Services\BaseService;
use Common\Models\Common\Date;
use Common\Models\Merchant\Merchant;
use Common\Utils\Data\StatisticsHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PostLoanServer extends BaseService {

    public function getList($params) {
        $merchantId = MerchantHelper::getMerchantId();
        $merchant = Merchant::model()->getOne($merchantId);
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
        $params['status'] = Order::REPAYMENT_PLAN_STATUS;

        $unfinishedStatus = implode(',', RepaymentPlan::UNFINISHED_STATUS);

        $select = DB::raw("
        DATE_FORMAT(date.date,'{$dateFormat}') as date,
        SUM(IF(paid_time,1,0)) paid_count,
        COALESCE(SUM(paid_amount), 0) paid_amount,
        SUM(IF(repayment_plan.status IN(" . $unfinishedStatus . "),1,0)) wait_repay_count,
        ROUND(SUM(IF(repayment_plan.status IN(" . $unfinishedStatus . "),
        order.principal*repayment_plan.repay_proportion/100 + order.principal*order.loan_days*order.daily_rate-reduction_fee,0)),2) wait_repay_amount,
        SUM(IF(repayment_plan.status = '" . RepaymentPlan::STATUS_FINISH . "',1,0)) repay_count,
        SUM(IF(repayment_plan.status = '" . RepaymentPlan::STATUS_FINISH . "',repay_amount,0)) repay_amount,
        SUM(IF(repayment_plan.status = '" . RepaymentPlan::STATUS_FINISH . "' AND overdue_days>0,1,0)) overdue_repay_count,
        SUM(IF(overdue_days>0,1,0)) overdue_1days_count,
        SUM(IF(overdue_days>2,1,0)) overdue_3days_count,
        SUM(IF(overdue_days>6,1,0)) overdue_7days_count,
        SUM(IF(order.status ='" . Order::STATUS_COLLECTION_BAD . "',1,0)) bad_count");

        $query = Date::query()->select($select)
                        ->join('order', function (JoinClause $join) use ($params, $merchantId, $dateFormat) {
                            $join->on('date.date', '=', DB::raw("DATE_FORMAT( paid_time, '%Y-%m-%d' )"))
                            ->where('order.merchant_id', '=', $merchantId);

                            // 状态
                            if ($status = array_get($params, 'status')) {
                                $join->whereIn('status', (array) $status);
                            }
                            // 新老用户
                            if ($quality = array_get($params, 'quality')) {
                                $join->where('quality', $quality);
                            }
                        })->join('repayment_plan', function (JoinClause $join) {
            /** 目前统计数据按照第一期还款算完结的口径来统计的 */
            $join->on('repayment_plan.order_id', '=', 'order.id')
                    ->where('repayment_plan.installment_num', '=', 1);
        });

        // 日期筛选
        if ($date = array_get($params, 'date')) {
            if (count($date) == 2) {
                $timeStart = current($date);
                $timeEnd = last($date);
                $query->whereBetween('date.date', [$timeStart, $timeEnd]);
            }
        }

        // 分析数据从商户创建日开始记录
        $query->where('date.date', '>=', $merchant->created_at)
                ->groupBy(DB::raw("DATE_FORMAT(date.date,'{$dateFormat}')"))->orderByDesc('date');

        if ($this->getExport()) {
            PostLoanExport::getInstance()->export($query, PostLoanExport::SCENE_LIST);
        }
        $size = array_get($params, 'size');
        $data = $query->paginate($size);

        foreach ($data->items() as $item) {
            /** 逾期还款占比 */
            $item->overdue_repay_rate = StatisticsHelper::percent($item->overdue_repay_count, $item->paid_count);
            /** 1天+逾期率 */
            $item->overdue_1days_rate = StatisticsHelper::percent($item->overdue_1days_count, $item->paid_count);
            /** 3天+逾期率 */
            $item->overdue_3days_rate = StatisticsHelper::percent($item->overdue_3days_count, $item->paid_count);
            /** 7天+逾期率 */
            $item->overdue_7days_rate = StatisticsHelper::percent($item->overdue_7days_count, $item->paid_count);
            /** 坏账率 */
            $item->bad_rate = StatisticsHelper::percent($item->bad_count, $item->paid_count);
        }

        return $data;
    }

    public function reloanRate($params) {
        $where = "";
        if(isset($params['app_client']) && $params['app_client'] != 'all'){
            $where = " AND o.app_client='{$params['app_client']}' ";
        }
        $sql = "SELECT count(1) as ct,DATE_FORMAT(paid_time,'%Y-%m') as m FROM `order` o  WHERE quality=1 {$where} AND paid_time is not null GROUP BY m ORDER BY m desc";
        $res = DB::select($sql);
        $data = [];
        foreach ($res as $item) {

            $startDatetime = Date("Y-m-26 00:00:00", strtotime($item->m . "-01") - 3600 * 24);
            $endDatetime = Date("Y-m-25 23:59:59", strtotime($item->m . "-01"));
            $sql = "SELECT count(1) ct FROM `order` o
INNER JOIN order_log ol ON o.id=ol.order_id AND (ol.to_status='finish' or ol.to_status='overdue_finish')
WHERE ol.created_at>='{$startDatetime}' AND ol.created_at<='{$endDatetime}'";
            $subRes = DB::select($sql);
            $data[] = [
                "date" => $item->m,
                "number_of_reloan" => ($subRes[0])->ct,
                "actual_number_of_reloan" => $item->ct,
                'reloan_retention_rate' => round($item->ct / ($subRes[0])->ct * 100, 2)."%"
            ];
        }
        $columns = [
            'date' => '月份',
            'number_of_reloan' => '可复贷数',
            'actual_number_of_reloan' => '实际复贷数',
            'reloan_retention_rate' => '复贷留存率',
        ];
        if ($this->getExport()) {
            PostLoanExport::getInstance()->csvProvider($data, $columns);
        }
        return ['data' => $data, 'total' => count($data), 'current' => 1];
        
    }

}
