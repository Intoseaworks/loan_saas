<?php

namespace Common\Jobs;

use Api\Services\Action\ActionLogService;
use Common\Utils\Email\EmailHelper;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class ActionLogJob extends Job
{
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
            ActionLogService::server()->create($this->data);
        } catch (\Exception $e) {
            EmailHelper::send([
                'data' => $this->data,
                'e' => $e->getMessage(),
            ], '行为写入失败');
        }
    }
}
