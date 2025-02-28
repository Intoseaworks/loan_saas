<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/26
 * Time: 10:18
 */

namespace Common\Jobs\Push\App;

use Common\Jobs\Job;
use Common\Services\Push\PushScheduleService;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;

/**
 * 日程推送
 *
 * Class AppByPaySchedule
 * @package Common\Jobs\Push\App
 */
class AppByPayScheduleJob extends Job
{

    public $userId;
    public $pushTime;
    public $fullname;
    public $appointmentPaidDate;
    public $pushHour;

    public function __construct($userId, $pushTime, $fullname, $appointmentPaidDate, $pushHour)
    {
        $this->userId = $userId;
        $this->pushTime = $pushTime;
        $this->fullname = $fullname;
        $this->appointmentPaidDate = $appointmentPaidDate;
        $this->pushHour = $pushHour;
    }

    public function handle()
    {
        try {
            MerchantHelper::clearMerchantId();
            $res = PushScheduleService::server()->paySchedule($this->userId, $this->pushTime, $this->fullname, $this->appointmentPaidDate, $this->pushHour);
        } catch (\Exception $e) {
            EmailHelper::send([
                'userId' => $this->userId,
                'e' => $e->getMessage(),
            ], 'push队列日程处理异常', ['chengxusheng@jiumiaodai.com']);
        }
    }

}
