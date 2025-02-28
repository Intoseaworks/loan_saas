<?php

namespace Common\Jobs\Sms;

use Common\Jobs\Job;
use Common\Utils\Sms\SmsPesoLocalGsmHelper;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class SendStatusJob extends Job {

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $queue = 'sms-send-status-20211214';
    public $tries = 2;
    public $logId;
    public $count;

    /**
     * ActionLogJob constructor.
     * @param $data
     */
    public function __construct($logId, $count = 1) {
        $this->logId = $logId;
        $this->count = $count;
    }

    /**
     * @throws \Exception
     */
    public function handle() {

        $log = \Common\Models\Cloud\ApiSmsLog::model()->getOne($this->logId);
        if (!$log) {
            return;
        }
        if ($this->count > 3) {
            $log->status = "-1";
            $log->save();
            return;
        }
        echo "Task:" . $log->task_id . "[{$this->count}]" . PHP_EOL;
        $res = SmsPesoLocalGsmHelper::helper()->getSmsStatus($log->task_id);
        if (isset($res['result']) && $res['result'] == 'ok') {
            $taskResult = explode(";", str_replace(["[", "]"], '', $res['content']));

            if (isset($taskResult[1])) {
                foreach ($taskResult as $k => $taskR) {
                    if ($k >= 1) {
                        $record = explode(":", $taskR);
                        if ($log->telephone == $record[1]) {
                            $log->port_id = $record[0];
                            $log->status = $record[2];
                            $log->save();
                            if (in_array($log->status, [0, 1, 2])) {
                                sleep(3);
                                dispatch(new \Common\Jobs\Sms\SendStatusJob($log->id, $this->count + 1));
                            }
                        }
                    }
                }
            }
        }
    }

}
