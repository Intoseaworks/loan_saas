<?php

namespace Risk\Common\Console\CreditReport;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Risk\Common\Services\CreditReport\ExperianCreditReportParseServer;

class ExperianCreditReportParseBatch extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'risk:credit-report-parse-batch:experian {batchNo} {date?}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Experian跑批的征信报告解析存宽表';

    /**
     * @return void
     */
    public function handle()
    {
        $batchNo = $this->argument('batchNo');
        $date = Carbon::yesterday();
        if ($param = $this->argument('date')) {
            $date = Carbon::parse($param);
        }

        ExperianCreditReportParseServer::server($date)->parseBatchReport($batchNo);
    }
}
