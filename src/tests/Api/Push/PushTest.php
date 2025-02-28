<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\Push;

use Common\Models\User\User;
use Common\Services\Push\PushScheduleService;
use Common\Utils\Data\DateHelper;
use Common\Utils\Push\Jpush;
use Tests\Api\TestBase;

class PushTest extends TestBase
{

    public function testCalculate()
    {
        $userId = 205;
        $title = "借款已逾期，请尽快处理还款";
        $content = "借款已逾期，请尽快处理还款：<br>借款金额：1000元<br>逾期罚息：1000元<br>已减免：1000元<br>应还金额：1000元<br>应还日期：2019-03-14<br><br><span style=\"color:#FF8C00;\">平台将按照逾期条款执行催还程序，为避免影响信用，请尽快处理还款！</span>";
        var_dump(Jpush::pushInbox($title, $content, $userId));
        exit();
    }

    /**
     * 日程测试
     */
    public function testSchedule()
    {
        $user = User::model()->getOne(229);
        $order = $user->order;
        $repaymentPlan = $order->lastRepaymentPlan;
        $appointmentPaidTimeMs = DateHelper::ms($repaymentPlan->appointment_paid_time);
        $appointmentPaidDate = DateHelper::formatToDate($repaymentPlan->appointment_paid_time);
        var_dump(PushScheduleService::server()->paySchedule($user->id, $appointmentPaidTimeMs - 24 * 3600 * 1000, $user->fullname, $appointmentPaidDate, '12'));
        exit();
    }

}
