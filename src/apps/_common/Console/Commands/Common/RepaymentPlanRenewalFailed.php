<?php

namespace Common\Console\Commands\Common;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepaymentPlanRenewalFailed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:repayment-plan-renewal-failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天晚上定时把当天未展期的展期失效';

    /**
     * @return void
     */
    public function handle()
    {
        $date = date('Y-m-d',strtotime("-1 day"));
        $date_begin = $date." 00:00:00";
        $date_end = $date." 23:59:59";

        $connect = DB::table('repayment_plan_renewal');

        $connect->whereStatus(1)->whereBetween('created_at', [$date_begin,$date_end])->update(['status' => 3]);
    }
}
