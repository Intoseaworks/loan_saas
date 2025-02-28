<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/26
 * Time: 10:18
 */

namespace Common\Jobs\Push\Ivr;

use Common\Jobs\Job;
use Common\Models\User\User;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;

class EmailByCommonJob extends Job {

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
    public $fromEmail;

    /**
     * @var
     */
    public $subject;

    /**
     * @var string
     */
    public $content;

    public function __construct($userId, $fromEmail, $subject = [], $content = '') {
        $this->userId = $userId;
        $this->fromEmail = $fromEmail;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function handle() {
        $this->send();
    }

    public function send() {
        try {
            MerchantHelper::clearMerchantId();
            $user = User::model()->getOne($this->userId);
            $userInfo = $user->userInfo;
            if (!$userInfo || !$userInfo->email) {
                throw new \Exception('用户或用户邮箱不存在');
            }
            MerchantHelper::setAppId($user->app_id, $user->merchant_id);
            \Common\Helper\Push\EmailHelper::send($this->fromEmail, $userInfo->email, $this->subject, $this->content);
        } catch (\Exception $e) {
            EmailHelper::send([
                'userId' => $this->userId,
                'eventId' => $this->eventId,
                'value' => $this->value,
                'e' => $e->getMessage(),
                    ], 'Ivr队列处理异常', ['chengxusheng@jiumiaodai.com']);
        }
    }

}
