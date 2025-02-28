<?php

namespace Common\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Common\Models\Order\Order;
use Common\Models\Trade\TradeLog;
use Common\Models\Third\ThirdRzpRepayReport;

class RepaymentExport implements FromArray {

    public function array(): array {
        $tableData = $this->init();
        if ($tableData !== false) {
            return $tableData;
        }
        $tableData = [];
        $tableData[] = ["Settled Date", "Credit", "Principal", "Interest", "Penalty", "Penalty GST"];
        $list = ThirdRzpRepayReport::model()->newQuery()->orderBy("Settle_Date")->get();
        foreach ($list as $res) {
            $tableData[] = [
                $res['Settle_Date'], $res['Credit'], $res['Principal'], $res['Interest'], $res['Penalty'], $res['Penalty_GST']
            ];
        }
        return $tableData;
    }

    public function init() {
        if (ThirdRzpRepayReport::model()->count()) {
            $date = strtotime("-1 day");
            $date = date("Y-m-d", $date);
            $res = $this->getM($date);
            ThirdRzpRepayReport::model()->updateOrCreateModel($res, ['Settle_Date' => $date]);
            return false;
        }
        $tableData = [];
        $start = strtotime("2020-04-04");
        $end = strtotime("-1 day");
        for ($i = $start; $i < $end; $i += 3600 * 24) {
            $date = date('Y-m-d', $i);
            $res = $this->getM($date);
            ThirdRzpRepayReport::model()->updateOrCreateModel($res, ['Settle_Date' => $date]);
            $tableData[] = [
                $res['Settle_Date'], $res['Credit'], $res['Principal'], $res['Interest'], $res['Penalty'], $res['Penalty_GST']
            ];
        }
        return $tableData;
    }

    public function getM($date) {
        echo "日期:" . $date . PHP_EOL;
        $query = TradeLog::model()->newQuery();
        $query->where(["business_type" => "repay", "trade_result" => "1"])->where("trade_notice_time", "LIKE", "{$date}%")->where("master_related_id", ">", "1");
        $tradeList = $query->get()->toArray();
        $res = [
            "Settle_Date" => $date,
            "Credit" => 0,
            "Principal" => 0,
            "Interest" => 0,
            "Penalty" => 0,
            "Penalty_GST" => 0,
        ];
        foreach ($tradeList as $item) {
            $order = Order::model()->getOne($item['master_related_id']);
            echo "订单ID：[" . $item['master_related_id'] . "]" . $order->order_no . PHP_EOL;
            echo "还款金额：" . $item['trade_amount'];
            echo "利息：" . $order->interestFee() . PHP_EOL;
            $res['Credit']++;
            $res['Principal'] += $item['trade_amount'];
            $res['Interest'] += $order->interestFee();
            if ($item['trade_notice_time'] < '2020-08-12 07:00:10') {
                $penalty = 0;
            } else {
                $penalty = $this->getPenalty($order, $item['trade_notice_time']);
            }
            $res['Penalty'] += $penalty;
            echo "罚息：" . $penalty . PHP_EOL;
            $res['Penalty_GST'] += $penalty ? $penalty * 0.18 : 0;
        }
        $res['Penalty'] = round($res['Penalty'], 2);
        $res['Penalty_GST'] = $res['Penalty'] ? round($res['Penalty'] * 0.18, 2) : 0;
        $res['Principal'] = $res['Principal'] - $res['Interest'] - $res['Penalty'];

        return $res;
    }

    public function getPenalty(Order $order, $repayDatetime) {
        $appointmentDatetime = $order->repaymentPlans->first()->appointment_paid_time;
        if ($appointmentDatetime >= $repayDatetime) {
            return 0;
        }
        return \Common\Utils\Data\DateHelper::diffInDays($appointmentDatetime, $repayDatetime) * $order->overdue_rate * $order->repaymentPlans->first()->principal;
    }

    public function getRepayAmountByOid($oid, $datetime) {
        $query = TradeLog::model()->newQuery();
        $res = $query->where(["business_type" => "repay", "trade_result" => "1", "master_related_id" => $oid])->where("trade_notice_time", "<", $datetime)->get()->toArray();
        $paid_amount = 0;
        foreach ($res as $item) {
            $paid_amount += $item['trade_amount'];
        }
        return $paid_amount;
    }

}
