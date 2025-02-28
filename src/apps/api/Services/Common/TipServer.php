<?php

namespace Api\Services\Common;

use Api\Services\BaseService;

class TipServer extends BaseService
{
    public function getOrderTip(\Common\Models\Order\Order $order)
    {
        $daily_rate = $order->daily_rate * 100;
        $gst_processing_rate = $order->gst_processing_rate * 100;
        $gst_penalty_rate = $order->gst_penalty_rate * 100;
        $processing_rate = $order->processing_rate * 100;
        $principal = $order->principal;
        $processing = $order->principal * $order->processing_rate;
        $gst_processing = $processing * $order->gst_processing_rate;
        $penalty_rate = $order->overdue_rate * 100;
        return [
            'gstProcessingFeeTip' => $order->gst_processing_rate * 100 . ' %  on processing fee',
            'gstPenaltyFeeTip' => $gst_penalty_rate . ' %  on penalty fee',
            'deductedTip' => 'deducted from loan amount',
            'chargingTip' =>
                [
                    [
                        'title' => 'Rate of Interest',
                        'content' => "Our rate of interest is {$daily_rate}% p.d. Cash now calculate the interest by day. If you repay early, you only need to pay interest only till the date you pay.",
                    ],
                    [
                        'title' => 'Processing Fee',
                        'content' => "It will be {$processing_rate}% of the loan amount. For a example, we will charge Rs{$processing} for a loan Rs {$principal}.The fee will be deducted before the loan disbursement.",
                    ],
                    [
                        'title' => "GST ({$gst_processing_rate} % on processing fee)",
                        'content' => "GST@{$gst_processing_rate} % is levied in all fee. For a example, we will charge Rs{$gst_processing} for a processing fee Rs {$processing}.The fee will be deducted before the loan disbursement.",
                    ],
                    [
                        'title' => 'Penalty Fee',
                        'content' => "Penalty fee will be charged at {$penalty_rate}% p.d. On the total over due amount on dais basis.Meanwile, GST@{$gst_penalty_rate} % is levied in penalty fee.
                       \nRemember overdue is for 3 days overdue ,We will revise your CBIL Report ,But it will effet your individual credit.",
                    ],
                    [
                        'title' => 'GST(penalty fee)',
                        'content' => "GST@{$gst_penalty_rate} % is levied in penalty fee",
                    ],
                ]
        ];
    }
}
