<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Focus;

use Illuminate\Console\Command;
use Common\Models\Focus\ViewOverview;
use Illuminate\Support\Facades\DB;

class StatisticalProgram extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'focus:statis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '中台数据汇总';

    public function handle() {
        $this->collecion();
        echo "End";
    }

    private function collecion() {
        $col = [
            "total_principal" => "select sum(principal) as a from `order` where paid_time>'2021-05-01'",
            "total_outstanding" => "select sum(o.principal) as a from `order` o INNER JOIN repayment_plan rp ON rp.order_id=o.id where isnull(repay_time) AND appointment_paid_time>NOW() AND o.`status`='manual_paid' AND installment_num=1",
            "total_bad" => "select sum(o.principal) as a from `order` o INNER JOIN repayment_plan rp ON rp.order_id=o.id where o.`status` in ('manual_paid', 'overdue', 'collection_bad') AND rp.installment_num=1 AND rp.overdue_days>30 AND o.created_at>'2021-05-01'",
            "total_reg_user" => "select count(1) as a from `user`",
            "total_trade_user" => "select count(1) as a from (select user_id from `order` where paid_time>'2021-05-01' group by user_id) a",
        ];
        $data = [];
        foreach ($col as $col_name => $sql) {
            $iData = DB::connection("mysql_readonly")->table(DB::raw("({$sql}) list"))->get()->toArray();
            $data[$col_name] = $iData[0]->a ?: 0;
        }
        ViewOverview::model()->updateOrCreateModel($data, [
            "id" => 1
        ]);
    }

}
