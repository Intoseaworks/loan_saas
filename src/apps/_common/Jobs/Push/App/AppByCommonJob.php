<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/26
 * Time: 10:18
 */

namespace Common\Jobs\Push\App;

use Common\Jobs\Job;
use Common\Services\Push\PushCheckService;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Push\Push;

class AppByCommonJob extends Job
{
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;
    /**
     * @var
     */
    public $userId;

    /**
     * @var
     */
    public $title;

    /**
     * @var
     */
    public $content;

    public $custom;

    /**
     * @var string
     */
    public $func;
    public $params;

    public function __construct($userId, $title, $content, $custom = [], $func = '', $params = [])
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->content = $content;
        $this->custom = $custom;
        $this->func = $func;
        $this->params = $params;
    }

    public function handle()
    {
        try {
            MerchantHelper::clearMerchantId();
            $func = $this->func;
            //if ($func && !$func()) {
            if ($func
                && method_exists((new PushCheckService()), $func)
                && !(new PushCheckService($this->params))->$func()
            ) {
                return false;
            }
            Push::helper()->pushInbox($this->title, $this->content, $this->userId, $this->custom);
        } catch (\Exception $e) {
            EmailHelper::send([
                'userId' => $this->userId,
                'title' => $this->title,
                'content' => $this->content,
                'e' => EmailHelper::sendException($e),
            ], 'AppPush队列处理异常', ['chengxusheng@jiumiaodai.com']);
        }
    }

}
