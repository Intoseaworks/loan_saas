<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Marketing;

use Common\Models\Marketing\GsmTask;
use Common\Utils\Data\DateHelper;
use Illuminate\Console\Command;

class MarketingGsm extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketing:gsm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GSM营销';

    public function handle() {
        $tasks = GsmTask::model()->where("status", '1')->get();
        foreach ($tasks as $task) {
            echo "check {$task->id}" . PHP_EOL;
            $diffDays = DateHelper::diffInDays($task->created_at);
            $sendHz = [];
//            if($task->run_times >= $task->send_times){
//                echo "达到次数限制";
//                continue;
//            }
            # 频次发送
            if ($task->send_hz) {
                $sendHz = explode(",", $task->send_hz);
                if (in_array($diffDays, $sendHz) && date("H") == date("H", strtotime($task->send_time))) {
                    for ($i = 1; $i <= $task->send_times; $i++) {
                        $task->run_times += 1;
                        $task->last_runtime_start = date("Y-m-d H:i:s");
                        $task->save();
                        dispatch(new \Common\Jobs\Marketing\GsmTaskJob($task->id));
                        echo "启动任务{$task->id}=>{$i} times";
                    }
                    continue;
                }
            } else {
                # 本次定时
                if (date("YmdH") == date("YmdH", strtotime($task->send_time)) && $task->run_times == 0) {
                    for ($i = 1; $i <= $task->send_times; $i++) {
                        $task->run_times += 1;
                        $task->last_runtime_start = date("Y-m-d H:i:s");
                        $task->save();
                        dispatch(new \Common\Jobs\Marketing\GsmTaskJob($task->id));
                        echo "启动任务{$task->id}=>{$i} times" . PHP_EOL;
                    }
                    continue;
                }
            }
        }
        echo "end";
    }

}
