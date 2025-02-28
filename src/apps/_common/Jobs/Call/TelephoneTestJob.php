<?php

namespace Common\Jobs\Call;

use Admin\Services\Collection\CollectionCallServer;
use Common\Jobs\Job;
use Common\Models\Approve\ApprovePool;
use Common\Models\Call\CallTelephoneTest;
use Common\Utils\CallCenter\Freeswitchesl;
use Common\Utils\MerchantHelper;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class TelephoneTestJob extends Job {

    const CALL_EXT_NUM = [
        "8870",
        "8871",
        "8872",
        "8873",
        "8874",
        "8875",
        "8876",
        "8877",
        "8878",
        "8879",
    ];
    const MP3_FILE = [
        "4" => "/tmp/s2mkt.mp3",
        "6" => "/tmp/u3mkt.mp3",
        "3" => "/tmp/u1mkt.mp3"
    ];

    public $queue = 'call-telephone-test';
    public $tries = 2;
    public $extNum;
    public $eventData;

    public function __construct($extNum, $eventData = "") {
        $this->extNum = $extNum;
        $this->eventData = $eventData;
    }

    public function handle() {
        MerchantHelper::setMerchantId(1);
        $startNew = false;
        if ($this->eventData) {
            $this->eventData = json_decode($this->eventData, true);

            $uuid = array_get($this->eventData, "Caller-Unique-ID");
            echo $uuid . PHP_EOL;
            $log = CallTelephoneTest::model()->where("uuid", $uuid)->first();
            if (!$log) {
                return;
            }

            $eventTelephone = array_get($this->eventData, "Caller-Destination-Number");
            $eventExt = array_get($this->eventData, "Caller-Caller-ID-Number");
            $eventTelephone = substr($eventTelephone, -10);

            $startNew = true;
            if ($log) {
                $callerName = array_get($this->eventData, "Caller-Caller-ID-Name");
                $callerNum = array_get($this->eventData, "Caller-Caller-ID-Number");

                $durationSecond = array_get($this->eventData, "variable_duration");
                $variableBillsec = array_get($this->eventData, "variable_billsec", 0);
                $endpointDisposition = array_get($this->eventData, 'variable_endpoint_disposition', '');
                $isAnswer = CallTelephoneTest::STATUS_FAILED;

                $log->variable_playback_seconds = array_get($this->eventData, "variable_playback_seconds", 0);
                $log->variable_sip_term_status = array_get($this->eventData, 'variable_sip_term_status', '');
                if ($log->variable_sip_term_status == "200" && ($variableBillsec - $log->variable_playback_seconds) > 0 && $variableBillsec > 25 && $callerName != $callerNum) {
                    $isAnswer = CallTelephoneTest::STATUS_SUCCESS;
                }
                $log->hangup_time = $this->fmtTime(array_get($this->eventData, "Caller-Channel-Hangup-Time"));
                $log->duration_second = $durationSecond;
                $log->endpoint_disposition = $endpointDisposition;
                $log->status = $isAnswer;
                $log->answer_state = array_get($this->eventData, "Answer-State");
                $log->variable_billsec = $variableBillsec;
                $log->hangup_cause = array_get($this->eventData, 'Hangup-Cause', '');
                $log->variable_sip_term_cause = array_get($this->eventData, 'variable_sip_term_cause', '');
                $log->times += 1;
//                $log->json = json_encode($this->eventData);
                $log->save();
            }
        }
        /** 时间判断自动外呼时间
          每天：8:00-21:00 */
        if (!$this->checkTime()) {
            return;
        }
        if ($startNew || !$this->eventData) {
            $callTest = CallTelephoneTest::model()->where("status", CallTelephoneTest::STATUS_WAITING)->orderBy("created_at")->first();
            if ($callTest) {

                $callTest->status = CallTelephoneTest::STATUS_CALLING;
                $callTest->save();
                echo "telephone:{$callTest->telephone} [mid{$callTest->merchant_id}]" . PHP_EOL;
                $fileMp3 = self::MP3_FILE[$callTest->merchant_id] ?? "/tmp/s2mkt.mp3";
                $res = CollectionCallServer::server()->call(["telephone" => $callTest->telephone],
                        $this->extNum, '1002' . $this->extNum, false, $fileMp3);
                if ($res->isSuccess()) {
                    echo "Successful";
                    $callTest->call_start_time = date("Y-m-d H:i:s");
                    $callTest->uuid = $res->getData();
                    $callTest->ext_num = $this->extNum;
                    $callTest->save();
                } else {
                    echo "Unsuccessful";
                }
            }
        }
    }

    private function checkTime() {
        $hour = ["08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21"];
        $currentHour = date("H");
        return in_array($currentHour, $hour);
    }

    private function fmtTime($time) {
        if ($time) {
            return date("Y-m-d H:i:s", $time / 1000 / 1000);
        }
        return null;
    }

}
