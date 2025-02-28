<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 10:45
 */

namespace Common\Utils\Sms;


use Common\Models\Merchant\App;
use Common\Models\Sms\SmsLog;
use Common\Redis\Captcha\SmsCaptchaRedis;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\StringHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Helper;
use Common\Utils\MerchantHelper;
use Admin\Services\Sms\SmsServer;

class SmsHelper
{
    use Helper;

    const EVENT_CAPTCHA = '4560A8';//验证码sms {{captcha}} is your verification code, welcome to register in {{appName}}, this code will expire in 5 minutes.
    const EVENT_PWD_CAPTCHA = '0C9706';//修改密码验证码 You are using a dynamic code {{captcha}} to reset your password. Make sure it is used by yourself. This dynamic code iwill expire in 5 mins.
    const EVENT_QUOTA_CHANGE = '0C9705';//修改额度触发 Reminder of {{appName}}, your final approved loan amount is php {{amount}}, and will be disbursed to your designated account.
    const EVENT_ORDER_PASS_SMS = '096DA8';//审批通过成功短信 Dear user: You have been successfully approved {{amount}}PHP/{{loan_days}} days, funds will arrive at your designated account within 2 working days.
    const EVENT_OFFLINE_DRAW = '219851';//放款短信-线下取款客户 Reminder of {{appName}}, your Control No. is:{{withdraw_no}}, use it to claim cash with your valid ID at any {{channel}} branch.
    const EVENT_PAY_SUCCESS_SMS_BANK = 'B64AAD';//放款成功短信-线上取款客户 Reminder！{{appName}} loan of PHP{{amount}} was disbursed to your designated account,please check your account.Your repayment Reference No. is {{reference_no}}.
    const EVENT_PAY_SUCCESS_SMS_ALL = 'B64DDD';//放款成功短信-所有客户 Congrats! Your loan of {{amount}}PHP/{{loan_days}} days is successfully disbursed. Repay via DragonPay Partners  7-11,LBC,Bayad Center, Cebuana.Dragonpay Ref. Number {{reference_no}}.
    const EVENT_PAY_SUCCESS_AGAIN_SMS = 'B64ADD';//放款短信-线下取款客户skypayl渠道再次提醒客户取款 执行放款后的3,5,7天 7:00 Your Control No. is {{withdraw_no}}，which will expire in {{days}} days，claim cash with your valid ID at any {{channel}} branch
    const EVENT_ORDER_PAY_FAIL = 'B64ADC';//放款短信-放款失败（核心状态为撤销） Reminder of {{appName}}, the disbursement was failed due to bank problems or expired Control No.,pls reapply if needed.
    const EVENT_BEFORE_EXPIRE_SMS = "65A8AF";//到期T-2提醒短信模板 9：00 Kindly remind you that your {{appName}} loan: php{{amount}} will due soon. Repay via paynamics Partners 7-11, Cebuana,Ecpay,Gcash The Ref. No. {{reference_no}}
    const EVENT_EXPIRE_SMS = "65A8AD";//到期T提醒短信模板  7：00 This is to remind you that your {{appName}} loan:  php{{amount}} is due today. Repay via paynamics Partners 7-11, Cebuana,Ecpay,Gcash The Ref. No. {{reference_no}}

    const EVENT_OVERDUE_FIRST_DAY_SMS = "B49B8B";//逾期T1  9:00 Your {{appName}} loan: php{{amount}} is overdued already.You might missed your due date. kindly pay your balance ASAP to maintain a good credit standing
    const EVENT_OVERDUE_FIRST_WEEK_SMS = "E84A90";//逾期T+3  9:00 Warning: No payment from you was received despite all reminders. Pls Pay your balance ASAP to avoid futher collection efforts. Thank you

    const EVENT_RENEWAL = "ACCFCE";//展期预约 You have successfully applied for a extension, please pay in full today: {{amount}} to avoid rollover failure, thank you for your cooperation
    const EVENT_APPROVE_REPLENISH = "B64AAA";//补件 Dear {{appName}} users,you submitted information does not meet the approval criteria, please resubmission of this information on {{appName}} APP within {{days}} days

    const EVENT_OLD_USER_RECALL = 'ACCFCD'; //结清客户召回 仅在结清次日10:00发送 After the system re evaluation, we believe that your credit is in good condition and your loan has entered the electricity review stage. We will synchronize the loan progress in time

    /**
     * 验证码短信Group
     */
    const CAPTCHA_EVENT = [
        self::EVENT_CAPTCHA,
        self::EVENT_PWD_CAPTCHA,
    ];

    public function send($mobile, $eventId, $values)
    {
        try {
            // 非正式环境不做真实发送
            if (!app()->environment('prod') && !(new SmsCaptchaHelper)->hasSmsOn()) {
                $content = SmsServer::server()->getTplByEventId($eventId);
                if(!$content){
                        $content = t($eventId, "sms-peso");
                }
                foreach ($values as $k => $v) {
                    $content = str_replace("{{" . $k . "}}", $v, $content);
                }
                $log = [
                    "telephone" => $mobile,
                    "event_id" => $eventId,
                    "send_content" => $content,
                    "created_at" => DateHelper::dateTime(),
                    "remark" => 'dev非正式发送',
                    "type" => SmsLog::TYPE_SMS,
                    "status" => 0,
                ];
                SmsLog::model()->create($log);
                return true;
            }
            $senderBuk = new SmsEgpHelper();
            //$senderBuk = new SmsChuangLanHelper();
            $sendId = App::getDataById(MerchantHelper::getAppId(), 'send_id');
            return $senderBuk->send($mobile, $eventId, $values, $sendId);
        } catch (\Exception $e) {
            EmailHelper::send([
                'mobile' => $mobile,
                'event_id' => $eventId,
                'vals' => $values,
                'e' => $e->getMessage(),
            ], '短信服务异常');
            return false;
        }
    }

    public function sendCaptcha($mobile, $code, $use = SmsCaptchaRedis::USE_APP_LOGIN)
    {
        $appId = MerchantHelper::getAppId();
        $appName = App::find($appId)->app_name ?? '';
        $params = ['captcha' => $code, 'appName' => $appName];
        switch ($use) {
            case SmsCaptchaRedis::USE_APP_LOGIN:
                $eventId = self::EVENT_CAPTCHA;
                break;
            case SmsCaptchaRedis::USE_FORGOT_PASSWORD:
                $eventId = self::EVENT_PWD_CAPTCHA;
                break;
            default :
                $eventId = self::EVENT_CAPTCHA;
        }
        return $this->send($mobile, $eventId, $params);
    }

    public function sendVoiceCaptcha($mobile, $code, $use = SmsCaptchaRedis::USE_APP_LOGIN)
    {
        $appId = MerchantHelper::getAppId();
        $appName = App::find($appId)->app_name ?? '';
        $code = StringHelper::numToEn($code);
        $params = ['captcha' => $code, 'appName' => $appName];
        return $this->send($mobile, self::EVENT_CAPTCHA, $params);
    }
}
