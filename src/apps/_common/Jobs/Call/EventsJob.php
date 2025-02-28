<?php

namespace Common\Jobs\Call;

use Common\Jobs\Job;
use Common\Models\Call\CallLog;
use Common\Models\Call\CallAdmin;
use Common\Models\Call\CallLogDetail;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class EventsJob extends Job {

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $queue = 'call-events';
    public $tries = 2;
    public $data;
    public $eventName;

    /**
     * ActionLogJob constructor.
     * @param $data
     */
    public function __construct($eventName, $data) {
        $this->data = $data;
        $this->eventName = $eventName;
    }

    /**
     * @throws \Exception
     */
    public function handle() {
        echo $this->eventName.PHP_EOL;
        $data = json_decode($this->data, true);
        switch ($this->eventName) {
            case "CHANNEL_HANGUP_COMPLETE":
                if ($extNum = array_get($data, "Caller-Caller-ID-Number")) {
                    $callAdmin = CallAdmin::model()->where("extension_num", $extNum)->first();
                    $isAnswer = 2;

                    $callerName = array_get($data, "Caller-Caller-ID-Name");
                    $callerNum = array_get($data, "Caller-Caller-ID-Number");
                    
                    $colleeName = array_get($data, "Caller-Callee-ID-Name");
                    $calleeNum = array_get($data, "Caller-Callee-ID-Number");
                    if($callerName && $callerNum && $callerName!=$callerNum){
                        $isAnswer = 1;
                    }
                    if($colleeName && $calleeNum && $colleeName!=$calleeNum){
                        $isAnswer = 1;
                    }
                    $telephone = array_get($data, "Caller-Destination-Number");
                    $insert = [
                        "admin_id" => $callAdmin->admin_id ?? 0,
                        "type" => $callAdmin->type ?? 0,
                        "extension_num" => array_get($data, "Caller-Caller-ID-Number"),
                        "uuid" => array_get($data, "Caller-Unique-ID"),
                        "telephone" => $telephone,
                        "start_time" => $this->fmtTime(array_get($data, "Caller-Channel-Created-Time")),
                        "answered_time" => $this->fmtTime(array_get($data, "Caller-Channel-Answered-Time")),
                        "hangup_time" => $this->fmtTime(array_get($data, "Caller-Channel-Hangup-Time")),
                        "duration_second" => array_get($data, "variable_duration"),
                        "caller_direction" => array_get($data, "Caller-Direction"),
                        "endpoint_disposition" => array_get($data, 'variable_endpoint_disposition', ''),
                        "is_answer" => $isAnswer,
                        "answer_state" => array_get($data, "Answer-State"),
                        "hangup_cause" => array_get($data, "Hangup-Cause"),
                        "variable_sip_term_status" => array_get($data, "variable_sip_term_status"),
                        "variable_sip_term_cause" => array_get($data, "variable_sip_term_cause"),
                        "variable_billsec" => array_get($data, "variable_billsec", 0),
                        "variable_sip_user_agent" => array_get($data, "variable_sip_user_agent"),
                        "variable_playback_seconds" => array_get($data, "variable_playback_seconds", 0),
                        //"json_detail" => $this->data
                    ];
//                    CallLog::model()->updateOrCreateModel($insert, ["uuid" => $insert['uuid']]);
                    $log = CallLog::model()->where("uuid", $insert['uuid'])->first();
                    if($log){
                        $log->start_time = $insert['start_time'];
                        $log->answered_time = $insert['answered_time'];
                        $log->hangup_time = $insert['hangup_time'];
                        $log->duration_second = $insert['duration_second'];
                        $log->endpoint_disposition = $insert['endpoint_disposition'];
                        $log->is_answer = $insert['is_answer'];
                        $log->answer_state = $insert['answer_state'];
                        $log->hangup_cause = $insert['hangup_cause'];
                        $log->variable_sip_term_status = $insert['variable_sip_term_status'];
                        $log->variable_sip_term_cause = $insert['variable_sip_term_cause'];
                        $log->variable_billsec = $insert['variable_billsec'];
                        $log->variable_sip_user_agent = $insert['variable_sip_user_agent'];
                        $log->variable_playback_seconds = $insert['variable_playback_seconds'];
                        //$log->json_detail = $insert['json_detail'];
                        $log->save();
                    }else{
                        $log = CallLog::model()->createModel($insert);
                    }
                    if(in_array($extNum, ['1027', '1091'])){
                        CallLogDetail::model()->updateOrCreateModel(["json_detail" => $this->data,"call_log_id" => $log->id], ["call_log_id" => $log->id]);
                    }
                    echo $insert['uuid'] . PHP_EOL;
                }
                break;
        }
    }

    private function fmtTime($time){
        if($time){
            return date("Y-m-d H:i:s", $time / 1000 / 1000);
        }
        return null;
    }
}
