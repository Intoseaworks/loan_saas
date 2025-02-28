<?php

namespace Common\Console\Commands\CollectionNotice;

use Carbon\Carbon;
use Common\Jobs\Push\App\AppByCommonJob;
use Common\Jobs\Push\Sms\SmsByCommonJob;
use Common\Models\Merchant\App;
use Common\Models\Order\RepaymentPlan;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Sms\SmsHelper;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CollectionNotice extends Command
{
    /** 方式：短信 */
    const MODE_SMS = 'sms';
    /** 方式：IVR */
    const MODE_IVR = 'ivr';
    /** 方式：APP */
    const MODE_APP = 'app';

    /** 到期前几天 */
    const DAY_BEFORE_EXPIRE = 'beforeExpire';
    /** 到期前一天 */
    const DAY_WILL_EXPIRE = 'willExpire';
    /** 到期当天 */
    const DAY_EXPIRE = 'expire';
    /** 逾期一天 */
    const DAY_OVERDUE_FIRST_DAY = 'overdueFirstDay';
    /** 逾期2到7天 */
    const DAY_OVERDUE_FIRST_WEEK = 'overdueFirstWeek';

    const EVENT_EXPIRE_APP_955 = 'eventExpireApp955';
    const EVENT_EXPIRE_APP_1625 = 'eventExpireApp1625';

    protected $mode;
    protected $dayType;
    protected $merchantId;
    protected $event;

    protected $appName;
    protected $url;
    /** @var Collection $appList */
    protected $appList;//app集合

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection-notice:notice {mode?} {day?} {merchant_id?} {event?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '催收：催收推送';

    /**
     * @var Carbon
     */
    protected $date;

    public function handle()
    {
        try {
            $this->mode = explode(',', $this->argument('mode'));
            $this->dayType = $this->argument('day');
            $this->merchantId = $this->argument('merchant_id');
            $this->event = $this->argument('event');
            /*$this->mode = 'app';
            $this->dayType = 'overdueFirstWeek';*/
            $this->setAppList();
            $fun = camel_case($this->dayType);
            if (!method_exists($this, $fun)) {
                throw new \Exception('逾期通知时间类型异常');
            }
            $time = date("Y-m-d H:i:s");
            echo $time . " {$this->dayType} GO" . PHP_EOL;
            call_user_func([$this, $fun]);
            echo PHP_EOL . $time . " {$this->dayType} END".PHP_EOL;
        } catch (\Exception $e) {
            EmailHelper::send([
                'mode' => $this->mode,
                'dayType' => $this->dayType,
                'e' => EmailHelper::warpException($e)
            ], '定时推送异常', 'chengxusheng1114@dingtalk.com');
        }
    }

    public function setAppList()
    {
        $this->appList = App::model()->getNormal();
    }

    public function setAppData($order)
    {
        MerchantHelper::setAppId($order->app_id, $order->merchant_id);
        $appId = $order->app_id;
        $app = $this->appList->where('id', $appId)->first();
        $this->appName = array_get($app, 'app_name', '');
        $this->url = array_get($app, 'app_url', '');
    }

    /**
     * 到期当天
     */
    public function expire()
    {
        $startTime = date('Y-m-d');
        $afterTime = date('Y-m-d', strtotime('+1 day'));
        $repayList = $this->getRepayList($startTime, $afterTime);
        $count = 0;
        /** @var RepaymentPlan $repay */
        foreach ($repayList as $repay) {
            $this->setAppData($repay->order);
            $fullname = $repay->user->fullname;
            $subject = CalcRepaymentSubjectServer::server($repay)->getSubject();
            $repaymentAmount = $subject->repaymentPaidAmount;
            //短信
            if ($this->checkNeedPush(self::MODE_SMS)) {
                $event = $this->event ?: SmsHelper::EVENT_EXPIRE_SMS;
                dispatch(new SmsByCommonJob($repay->user_id, $event, [
                    'fullname' => $fullname, 'appName' => $this->appName, 'url' => $this->url, 'amount' => $repaymentAmount, 'reference_no' => $repay->order->reference_no,
                    'dragonpay_lifetimeID' => $repay->user->userInfo->dg_pay_lifetime_id]));
            }
            //语音提醒
//            if ($this->checkNeedPush(self::MODE_IVR)) {
//                $event = $this->event ?: SmsHelper::EVENT_EXPIRE_IVR;
//                dispatch(new IvrByCommonJob($repay->user_id, $event, [
//                    'fullname' => $fullname, 'appName' => $this->appName]));
//            }
            //APP推送
            if ($this->checkNeedPush(self::MODE_APP)) {
                $title = "Repayment notice";
                $content = "Dear {$fullname},  today is your repaument date.Please pay off the loan in time to avoid overdue fees.You can apply next loan after repayment.Remember do not overdue. Thank you very much.";
                if ($this->event == CollectionNotice::EVENT_EXPIRE_APP_955) {
                    $content = "Dear {$fullname},  today is your repaument date.Please pay off the loan in time to avoid overdue fees.You can apply next loan after repayment.Remember do not overdue. Thank you very much.";
                }
                if ($this->event == CollectionNotice::EVENT_EXPIRE_APP_1625) {
                    $content = "Pay off your loan, no overdue fees and more credit amount. Overdue repayment will bring you more overdue fees.";
                }
                dispatch(new AppByCommonJob($repay->user_id, $title, $content));
            }
            $count++;
            if ($count % 50 == 0) {
                sleep(1);
            }
        }
        return $this->result($count);
    }

    /**
     * 逾期一天
     */
    public function overdueFirstDay()
    {
        echo "逾期1天start:" . PHP_EOL;
        $startTime = date('Y-m-d', strtotime('-1 day'));
        $afterTime = date('Y-m-d');
        $repayList = $this->getRepayList($startTime, $afterTime);
        echo "总数：" . count($repayList);
        $count = 0;
        /** @var RepaymentPlan $repay */
        foreach ($repayList as $repay) {
            $this->setAppData($repay->order);
            $subject = CalcRepaymentSubjectServer::server($repay)->getSubject();
            $repaymentAmount = $subject->repaymentPaidAmount;
            //语音提醒this->
//            if ($this->checkNeedPush(self::MODE_IVR)) {
//                dispatch(new IvrByCommonJob($repay->user_id, SmsHelper::EVENT_OVERDUE_FIRST_DAY_IVR, ['amount' => $repaymentAmount, 'appName' => $this->appName]));
//            }
            //App推送
            if ($this->checkNeedPush(self::MODE_APP)) {
                $title = "Repayment notice";
                $content = "Dear {$this->appName} user, your loan is one day overdue. As of now, your total repayment amount is RS {$repaymentAmount}, please pay immediately, otherwise it will be reported to credit investigation department. Thank you.";
                dispatch(new AppByCommonJob($repay->user_id, $title, $content));
            }
            //发送短信
            if ($this->checkNeedPush(self::MODE_SMS)) {
                dispatch(new SmsByCommonJob($repay->user_id, SmsHelper::EVENT_OVERDUE_FIRST_DAY_SMS, [
                    'amount' => $repaymentAmount, 'appName' => $this->appName, 'url' => $this->url]));
            }
            $count++;
            if ($count % 50 == 0) {
                sleep(1);
            }
        }
        return $this->result($count);
    }

    /**
     * 逾期2到7天
     */
    public function overdueFirstWeek()
    {
        $startTime = date('Y-m-d', strtotime('-7 day'));
        $afterTime = date('Y-m-d', strtotime('-1 day'));
        $repayList = $this->getRepayList($startTime, $afterTime);
        $count = 0;
        /** @var RepaymentPlan $repay */
        foreach ($repayList as $repay) {
            $this->setAppData($repay->order);
            $overdueDays = $repay->order->getOverdueDays();
            $dayText = $overdueDays == 1 ? 'day' : 'days';
            $subject = CalcRepaymentSubjectServer::server($repay)->getSubject();
            $repaymentAmount = $subject->repaymentPaidAmount;
            //语音提醒
//            if ($this->checkNeedPush(self::MODE_IVR)) {
//                dispatch(new IvrByCommonJob($repay->user_id, SmsHelper::EVENT_OVERDUE_FIRST_WEEK_IVR, [
//                    'amount' => $repaymentAmount, 'day' => "{$overdueDays} {$dayText}", 'appName' => $this->appName
//                ]));
//            }
            //App推送
            if ($this->checkNeedPush(self::MODE_APP)) {
                $title = "Repayment notice";
                $content = "Dear {$this->appName} user, your loan is overdue {$overdueDays} {$dayText}, and the overdue loan amount is rs. {$repaymentAmount}. It has been reported to the credit investigation team. Please pay as soon as possible. We can cancel your credit investigation. Thank you.";
                dispatch(new AppByCommonJob($repay->user_id, $title, $content));
            }
            //发送短信
            if ($this->checkNeedPush(self::MODE_SMS)) {
                dispatch(new SmsByCommonJob($repay->user_id, SmsHelper::EVENT_OVERDUE_FIRST_WEEK_SMS, [
                    'amount' => $repaymentAmount, 'day' => "{$overdueDays} {$dayText}", 'appName' => $this->appName, 'url' => $this->url
                ]));
            }
            $count++;
            if ($count % 50 == 0) {
                sleep(1);
            }
        }
        return $this->result($count);
    }

    /**
     * 到期前几天
     */
    protected function beforeExpire()
    {
        echo "到期前几天start:";
        $startTime = date('Y-m-d', strtotime('+2 day'));
        $afterTime = date('Y-m-d', strtotime('+3 day'));
        $repayList = $this->getRepayList($startTime, $afterTime);
        $count = 0;
        echo "总数：" . count($repayList);
        /** @var RepaymentPlan $repay */
        foreach ($repayList as $repay) {
            $this->setAppData($repay->order);
            $fullname = $repay->user->fullname;
            $principal = $repay->order->principal;
            $appointmentDay = $repay->order->getAppointmentPaidTime();
            $subject = CalcRepaymentSubjectServer::server($repay)->getSubject();
            $repaymentAmount = $subject->repaymentPaidAmount;
            //短信提醒
            if ($this->checkNeedPush(self::MODE_SMS)) {
                dispatch(new SmsByCommonJob($repay->user_id, SmsHelper::EVENT_BEFORE_EXPIRE_SMS, [
                    'fullname' => $fullname, 'principal' => $principal, 'day' => $appointmentDay,
                    'url' => $this->url, 'appName' => $this->appName, 'amount' => $repaymentAmount, 'reference_no' => $repay->order->reference_no,
                    'dragonpay_lifetimeID' => $repay->user->userInfo->dg_pay_lifetime_id
                ]));
            }
            //语音提醒
//            if ($this->checkNeedPush(self::MODE_IVR)) {
//                dispatch(new IvrByCommonJob($repay->user_id, SmsHelper::EVENT_BEFORE_EXPIRE_IVR, [
//                    'fullname' => $fullname, 'principal' => $principal, 'day' => $appointmentDay
//                ]));
//            }
            $count++;
            if ($count % 50 == 0) {
                sleep(1);
            }
        }
        return $this->result($count);
    }

    /**
     * 统一获取需通知的单
     *
     * @param $startTime
     * @param $afterTime
     * @return mixed
     */
    public function getRepayList($startTime, $afterTime)
    {
        $query = RepaymentPlan::query()
            ->with('order')
            ->whereIn('repayment_plan.status', RepaymentPlan::UNFINISHED_STATUS)
            ->where('repayment_plan.installment_num', '=', 1)
            ->whereNull('repay_time')
            ->where('appointment_paid_time', '>=', $startTime)
            ->where('appointment_paid_time', '<', $afterTime);
        if ($this->merchantId) {
            $query->where('repayment_plan.merchant_id', $this->merchantId);
        }
        return $query->get();
    }

    /**
     * 判断是否需要通过此方式推送
     *
     * @param $mode
     * @return bool
     */
    protected function checkNeedPush($mode)
    {
        return in_array($mode, $this->mode);
    }

    /**
     * 统一返回处理
     *
     * @param $count
     * @return bool
     */
    protected function result($count)
    {
        EmailHelper::send([
            'mode' => $this->mode,
            'dayType' => $this->dayType,
            'count' => $count,
        ], '定时推送', 'chengxusheng1114@dingtalk.com');
        return true;
    }

    /**
     * 到期前一天
     */
    protected function willExpire()
    {
        $startTime = date('Y-m-d', strtotime('+1 day'));
        $afterTime = date('Y-m-d', strtotime('+2 day'));
        $repayList = $this->getRepayList($startTime, $afterTime);
        $count = 0;
        /** @var RepaymentPlan $repay */
        foreach ($repayList as $repay) {
            $this->setAppData($repay->order);
            $fullname = $repay->user->fullname;
            $principal = $repay->order->principal;
            $appointmentDay = $repay->order->getAppointmentPaidTime();
            $subject = CalcRepaymentSubjectServer::server($repay)->getSubject();
            $repaymentAmount = $subject->repaymentPaidAmount;
            //短信提醒
            if ($this->checkNeedPush(self::MODE_SMS)) {
                dispatch(new SmsByCommonJob($repay->user_id, SmsHelper::EVENT_BEFORE_EXPIRE_SMS, [
                    'fullname' => $fullname, 'principal' => $principal, 'day' => $appointmentDay, 'appName' => $this->appName, 'url' => $this->url, 'amount' => $repaymentAmount, 'reference_no' => $repay->order->reference_no
                ]));
            }
            //语音提醒
//            if ($this->checkNeedPush(self::MODE_IVR)) {
//                dispatch(new IvrByCommonJob($repay->user_id, SmsHelper::EVENT_BEFORE_EXPIRE_IVR, [
//                    'fullname' => $fullname, 'principal' => $principal, 'day' => $appointmentDay, 'appName' => $this->appName
//                ]));
//            }
            //App推送
            if ($this->checkNeedPush(self::MODE_APP)) {
                $title = "Repayment notice";
                $content = "Dear {$fullname}, Your loan amount {$principal} is due tomorrow.Please make your repayment on time to avoid overdue charges. Thank You.";
                dispatch(new AppByCommonJob($repay->user_id, $title, $content));
            }
            $count++;
            if ($count % 50 == 0) {
                sleep(1);
            }
        }
        return $this->result($count);
    }

}
