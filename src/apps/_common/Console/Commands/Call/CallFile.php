<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Call;

use Common\Utils\Freeswitch\FtpFileHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Common\Models\Statistics\StatisticsCallRecording;

class CallFile extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call:file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '录音文件同步';

    public function handle() {
        echo "start";
        $sql = "select 
            m.product_name as merchant_name,
            m.id as merchant_id,
            log.extension_num,
	concat(s.username, '_', s.nickname) as username,
        s.id as user_id,
	count(1) call_total,
	date(log.start_time) call_date,
	sum(duration_second) as duration_second,
	min(log.start_time) as min_time,
	max(log.start_time) as max_time,
        sum(case when log.is_answer='1' AND duration_second>0 then 1 else 0 end) as answered,
	round(sum(case when log.is_answer='1' AND duration_second>0 then 1 else 0 end)/count(1)*100,2) as completing_rate
from call_log log
INNER JOIN call_admin ca ON log.extension_num=ca.extension_num
INNER JOIN staff s on s.id=ca.admin_id
INNER JOIN merchant m on ca.merchant_id=m.id
WHERE log.caller_direction='outbound' AND log.is_answer in (1,2) AND length(log.extension_num)=4
GROUP BY date(log.created_at),log.extension_num
ORDER BY date(log.created_at) DESC";
        $list = DB::select($sql);
        foreach ($list as $item) {
            $update = [
                "merchant_name" => $item->merchant_name,
                "merchant_id" => $item->merchant_id,
                "extension_num" => $item->extension_num,
                "username" => $item->username,
                "user_id" => $item->user_id,
                "call_total" => $item->call_total,
                "call_date" => $item->call_date,
                "max_time" => $item->max_time,
                "min_time" => $item->min_time,
                "duration_second" => $item->duration_second,
                "answered" => $item->answered
            ];
            StatisticsCallRecording::model()->updateOrCreateModel($update, ["user_id" => $item->user_id, "call_date" => $item->call_date]);
            echo '.';
        }
        echo "Download File List" . PHP_EOL;
        FtpFileHelper::helper()->ftp();
        echo "over";
    }

}
