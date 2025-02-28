<?php

namespace Common\Console\Commands\Common;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DateRecordConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:date-record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '日期表每日记录';

    /**
     * @return void
     */
    public function handle()
    {
        $date = date('Y-m-d');

        $connect = DB::table('date');

        if (!$connect->where('date', $date)->first()) {
            $connect->insert([['date' => $date]]);
        }

        $this->line('日期记录完成');
    }
}
