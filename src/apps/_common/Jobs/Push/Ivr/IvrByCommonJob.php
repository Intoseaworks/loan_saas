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
use Common\Services\Push\PushCheckService;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Sms\SmsHelper;

class IvrByCommonJob extends Job
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
    public $eventId;

    /**
     * @var
     */
    public $value;

    /**
     * @var string
     */
    public $func;
    public $params;

    public function __construct($userId, $eventId, $value = [], $func = '', $params = [])
    {
        $this->userId = $userId;
        $this->eventId = $eventId;
        $this->value = $value;
        $this->func = $func;
        $this->params = $params;
    }

    public function handle()
    {
        $this->send();
    }

    public function send()
    {
        try {
            MerchantHelper::clearMerchantId();
            $user = User::model()->getOne($this->userId);
            if (!$user || !$user->telephone) {
                throw new \Exception('用户或用户手机不存在');
            }
            MerchantHelper::setAppId($user->app_id, $user->merchant_id);
            $func = $this->func;
            //if ($func && !$func()) {
            if ($func
                && method_exists((new PushCheckService($this->params)), $func)
                && !(new PushCheckService($this->params))->$func()
            ) {
                return false;
            }
            SmsHelper::helper()->send($user->telephone, $this->eventId, $this->value);
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
