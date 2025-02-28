<?php

namespace Common\Jobs\Log;

use Common\Jobs\Job;
use Common\Models\Log\LogSystemRequest;
use Common\Utils\Email\EmailHelper;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author L.NIO
 */
class ResponseLogJob extends Job
{
//    public $queue = 'system-log';
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    public $data;

    /**
     * ActionLogJob constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        try {
            LogSystemRequest::model()->createModel($this->data);
        } catch (\Exception $e) {
            EmailHelper::send([
                'data' => $this->data,
                'e' => $e->getMessage(),
            ], '访问日志写入失败');
        }
    }
}
