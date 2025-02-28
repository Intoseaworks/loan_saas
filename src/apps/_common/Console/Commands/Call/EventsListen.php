<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Call;

use Common\Jobs\Call\ApprovePoolJob;
use Common\Jobs\Call\CollectionJob;
use Common\Jobs\Call\EventsJob;
use Common\Utils\CallCenter\Freeswitchesl;
use Illuminate\Console\Command;
use Common\Models\Call\CallLog;
use Common\Jobs\Call\ApproveAutoJob;
use Common\Jobs\Call\TelephoneTestJob;

class EventsListen extends Command {
    # 每次提取用户数

    const PER_NUM = 1000;
    const LISTEN_EVENT = [
        'CHANNEL_HANGUP_COMPLETE', # 挂断事件
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call:events {--test_model} {--fail} {--success}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '侦听Freeswitch事件';

    public function handle() {
        if ($this->option('test_model')) {
            return $this->testModel();
        }
        #手动触发放款成功
        $freeswitch = Freeswitchesl::factory('dev', 1);
        $status = $freeswitch->events("json", "CHANNEL_HANGUP_COMPLETE");
        while (true) {
            $receivedParameters = $freeswitch->recvEvent();
            if (!empty($receivedParameters)) {
                $eventName = $freeswitch->getHeader($receivedParameters, "Event-Name");
                echo date("Ymd_H:i:s") . PHP_EOL;
                if (in_array($eventName, self::LISTEN_EVENT)) {
                    $data = $freeswitch->serialize($receivedParameters, "json");
                    dispatch(new EventsJob($eventName, $data));
                    $arrayData = json_decode($data, true);
                    $extNum = array_get($arrayData, "variable_sip_from_user");
                    if ($extNum && isset(Freeswitchesl::TEST_CALL_MERCHANT_MAP[$extNum])) {
                        dispatch(new CollectionJob(Freeswitchesl::TEST_CALL_MERCHANT_MAP[$extNum], $data));
                    }
                    if ($extNum && isset(Freeswitchesl::TEST_CALL_MERCHANT_MAP_APPROVE_POOL[$extNum])) {
                        //dispatch(new ApprovePoolJob(Freeswitchesl::TEST_CALL_MERCHANT_MAP_APPROVE_POOL[$extNum], $data));
                    }
                    if (isset(ApproveAutoJob::EXT_LIST[$extNum])) {
                        dispatch(new ApproveAutoJob($extNum, $data));
                    }
                    if (in_array($extNum, TelephoneTestJob::CALL_EXT_NUM)) {
                        echo $extNum . 'ok' . PHP_EOL;
                        dispatch(new TelephoneTestJob($extNum, $data));
                    }
                    echo '.';
                }
            }
        }
    }

    public function testModel() {
        echo "----====#### TEST MODEL ####====----";
        $exts = array_keys(ApproveAutoJob::EXT_LIST);

        $logList = CallLog::model()->query()->whereNull('start_time')->whereIn('extension_num', $exts)->orderBy('id')->get();
        foreach ($logList as $callLog) {
            if ($this->option('fail')) {
                echo "拨打失败处理" . PHP_EOL;
                $data = json_encode($this->getEventData($callLog, "no"));
            } elseif ($this->option('success')) {
                echo "拨打成功处理" . PHP_EOL;
                $data = json_encode($this->getEventData($callLog, "yes"));
            } else {
                echo "随机" . PHP_EOL;
                $data = json_encode($this->getEventData($callLog));
            }
            $evJob = new EventsJob("CHANNEL_HANGUP_COMPLETE", $data);
            $evJob->queue = "call-collection";
            dispatch($evJob);
            dispatch(new ApproveAutoJob($callLog->extension_num, $data));
            sleep(1);
            echo '.';
        }
        echo "END" . PHP_EOL;
    }

    public function getEventData(CallLog $callLog, $key = '') {
        $data = [
            "yes" => [
                "Caller-Caller-ID-Number" => $callLog->extension_num, #分机号
                "Caller-Callee-ID-Name" => "OK", #被呼叫名
                "Caller-Callee-ID-Number" => $callLog->telephone, #被呼叫号码
                "Caller-Unique-ID" => $callLog->uuid, # uuid
                "Caller-Destination-Number" => $callLog->telephone, #电话号
                "Caller-Channel-Created-Time" => (time() - 100) * 1000 * 1000, #开始时间
                "Caller-Destination-Number" => $callLog->telephone, #电话号
                "Caller-Channel-Answered-Time" => (time() - 50) * 1000 * 1000, #拨通时间
                "Caller-Channel-Hangup-Time" => (time() - 100) * 1000 * 1000, #挂断
                "Caller-Direction" => "outbound", #Caller-Direction
                "variable_endpoint_disposition" => "EARLY%20MEDIA", #电话号
                "variable_duration" => 30,
            ],
            "no" => [
                "Caller-Caller-ID-Number" => $callLog->extension_num, #分机号
                "Caller-Callee-ID-Name" => $callLog->telephone, #被呼叫名
                "Caller-Callee-ID-Number" => $callLog->telephone, #被呼叫号码
                "Caller-Unique-ID" => $callLog->uuid, # uuid
                "Caller-Destination-Number" => $callLog->telephone, #电话号
                "Caller-Channel-Created-Time" => (time() - 100) * 1000 * 1000, #开始时间
                "Caller-Destination-Number" => $callLog->telephone, #电话号
                "Caller-Channel-Answered-Time" => (time() - 50) * 1000 * 1000, #拨通时间
                "Caller-Channel-Hangup-Time" => (time() - 100) * 1000 * 1000, #挂断
                "Caller-Direction" => "outbound", #Caller-Direction
                "variable_endpoint_disposition" => "EARLY%20MEDIA", #电话号
            ],
        ];
        if (isset($data[$key])) {
            return $data[$key];
        }
        return $data[array_rand($data)];
    }

}
