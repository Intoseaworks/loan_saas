<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Crm;

use Common\Jobs\Crm\MarketingSmsJob;
use Common\Models\Crm\CrmMarketingTask;
use Illuminate\Console\Command;

class MarketingSMS extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:marketing:sms {hour?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '营销短信定时脚本 hour = 整点“01”格式';

    public function handle() {
        if ($date = $this->argument('hour')) {
            $date = $date.":";
        }else{
            $date = date("H:");
        }
        $this->send($date);
        echo "End";
    }

    private function send($date) {
        echo $date;
        $query = CrmMarketingTask::model()->newQuery();
        $query->where("task_type", CrmMarketingTask::TYPE_SMS);
        $query->where("status", CrmMarketingTask::STATUS_NORMAL);
        $query->where(function($query) use ($date){
            $query->where("send_time", "like", "%\"{$date}%");
        });
        $res = $query->get();
        foreach ($res as $item) {
            echo "启动Task:{$item->id}".PHP_EOL;
            $job = new MarketingSmsJob($item->id);
            $job->queue = $job->queue . "-" . $item->merchant_id;
            dispatch($job);
        }
    }

}
