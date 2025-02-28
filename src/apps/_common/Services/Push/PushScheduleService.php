<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/10
 * Time: 15:02
 */

namespace Common\Services\Push;


use Common\Services\BaseService;
use Common\Utils\Data\DateHelper;
use Common\Utils\Push\Push;

/**
 * 日程推送
 *
 * Class PushScheduleService
 * @package Common\Services\Push
 */
class PushScheduleService extends BaseService
{

    /**
     * 还款日程推送
     */
    public function paySchedule($userId, $pushTime, $fullname, $appointmentPaidDate, $pushHour = '')
    {
        $appointmentPaidTime = DateHelper::format($pushTime, 'Y,m,d,H,i,m');
        list($year, $month, $day, $hour, $minute) = explode(',', $appointmentPaidTime);
        $title = 'Repayment Notice';
        $content = "Dear {$fullname}, {$appointmentPaidDate} is the repayment day. In order to avoid causing late fees and penalities, and affect your personal credit, please login Cash Now in time to settle the loan. Thank you.";
        $custom = [
            "type" => Push::TYPE_LOAN_SUCCESS_SCHEDULE,
            'year' => $year,
            'month' => $month - 1,
            'day' => $day,
            'hour' => $pushHour ?: $hour,
            'minute' => '00',
            'label' => $title,
            'content' => $content
        ];
        return Push::sendMessage($userId, $title, $content, $custom);
    }

}
