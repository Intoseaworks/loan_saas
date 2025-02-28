<?php

namespace Common\Jobs;

use Common\Utils\Email\EmailHelper;

/**
 * Class SendEmailJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class SendEmailJob extends Job
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
    public $mail;

    public $attachments;

    /**
     * SendEmailJob constructor.
     * @param $content
     * @param null $title
     * @param null $mail
     * @param string|null $attachments
     */
    public function __construct($content, $title = null, $mail = null, $attachments = '')
    {
        $this->title = $title;
        $this->content = $content;
        $this->mail = $mail;
        $this->attachments = $attachments;
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
            EmailHelper::mailer($this->content, $this->title, $this->mail, $this->attachments);
        } catch (\Exception $e) {
            throw new $e;
        }
    }
}
