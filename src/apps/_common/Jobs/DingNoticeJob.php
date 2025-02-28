<?php

namespace Common\Jobs;

use Common\Utils\Data\ArrayHelper;
use Common\Utils\DingDing\DingHelper;

/**
 * Class DingNoticeJob
 * @package App\Jobs
 */
class DingNoticeJob extends Job
{
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;
    /**
     * @var string|null
     */
    public $title;
    /**
     * @var string
     */
    public $content;
    /**
     * @var string|array
     */
    public $at;

    public $robot;

    /**
     * SendEmailJob constructor.
     * @param $content
     * @param null $title
     * @param null $at
     * @param $robot
     */
    public function __construct($content, $title = null, $at = null, $robot = '')
    {
        $this->title = $title;
        $this->content = ArrayHelper::arrayToJson($content);
        $this->at = $at;
        $this->robot = $robot;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        if (app()->environment() == 'local') {
            return;
        }
        try {
            DingHelper::notice($this->content, $this->title, $this->at, false, $this->robot);
        } catch (\Exception $e) {
            throw new $e;
        }
    }
}
