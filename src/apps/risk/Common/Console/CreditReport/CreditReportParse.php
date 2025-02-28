<?php

namespace Risk\Common\Console\CreditReport;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Risk\Common\Models\Third\ThirdReport;
use Risk\Common\Services\CreditReport\ExperianCreditReportParseServer;
use Risk\Common\Services\CreditReport\HmCreditReportParseServer;

class CreditReportParse extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'risk:credit-report-parse:exec {date?} {--channel=}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = '征信报告解析存宽表';

    /**
     * @return void
     */
    public function handle()
    {
        $date = Carbon::yesterday();
        if ($param = $this->argument('date')) {
            $date = Carbon::parse($param);
        }

        $channel = $this->option('channel');

        if (is_null($channel)) {
            HmCreditReportParseServer::server($date)->parseToDatabase();

            ExperianCreditReportParseServer::server($date)->parseToDatabase();
        } else {
            switch ($channel) {
                case ThirdReport::CHANNEL_HIGH_MARK:
                    HmCreditReportParseServer::server($date)->parseToDatabase();
                    break;
                case ThirdReport::CHANNEL_EXPERIAN:
                    ExperianCreditReportParseServer::server($date)->parseToDatabase();
                    break;
                default:
                    $this->line('渠道参数不正确');
            }
        }
    }
}
