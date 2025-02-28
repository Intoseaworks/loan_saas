<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Config;

use Api\Models\Order\Order;
use Api\Models\Order\RepaymentPlan;
use Api\Models\User\User;
use Api\Services\BaseService;
use Common\Models\Common\BankInfo;
use Common\Models\Common\Config;
use Common\Models\Feedback\Feedback;
use Common\Models\Merchant\App;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;

class ConfigServer extends BaseService
{
    public function getConfig($clientId)
    {
        try {
            $config = [];
            $appId = MerchantHelper::getAppId();
            $appName = App::find($appId)->app_name ?? 'Indiaox SaaS';
            $config['appName'] = $appName;
            $config['customerUrl'] = '';
            $config['h5AppUrl'] = config('config.h5_client_domain');
            $config['warmNotice'] =
                "<font style='font-size:30px;' color='#333333'>Please keep your </font>
<font style='font-size:30px;' color='#333333'>Pan card</font>
<font style='font-size:30px;' color='#333333'> and </font>
<font style='font-size:30px;' color='#333333'>Address proof</font>
<font style='font-size:30px;' color='#333333'> (Aadhaar Card/Voter ID/Passport any one of them) ready.</font>
                <br>
                <font style='font-size:30px;' color='#FF5300'>* info. will be stored only for validation and not be used for any other purposes.</font>
                ";
            //提示文案
            $config['notice'] = [
                'panCard' => [
                    'tip' => 'Please upload the original one. And ensure the PAN CARD is clear or you will be rejected.',
                ],
                'passport' => [
                    'tip' => 'Please upload the original one. And ensure the Passport is clear or you will be rejected.',
                ],
                'drivingLicence' => [
                    'tip' => 'Please upload the original one. And ensure the Driving Licence is clear or you will be rejected.',
                ],
                'voterIdCard' => [
                    'tip' => 'Please upload the original one. And ensure the Voter ID Card is clear or you will be rejected.',
                ],
            ];
            // 公司名称
            $config['companyName'] = Config::model()->getCompanyName();
            // 公司地址
            $config['companyAddress'] = Config::model()->getCompanyAddress();
            // 客服电话
            $config['customerServicePhone'] = Config::model()->getCustomerTelephoneNumbers();
            // 客服邮箱
            $config['customerServiceEmail'] = Config::model()->getCustomerEmail();
            // 公司线下还款银行账户
            $config['companyOfflineRepayBank'] = Config::model()->getCompanyOfflineRepayBank();
            //贷款原因
            $config['loanReason'] = [
                'Bills',
                'Shopping',
                'Education',
                'Gifts',
                'Grocery',
                'Beauty',
                'Party',
                'Pet care',
                'Transport',
                'Electronics',
                'Wedding',
                'Business',
                'Rental',
                'Medical',
                'Travel',
                'Others',
            ];
            //首页提示语
            $config['headerTip'] = 'Not the ultimate credit amount';
            //补充资料提示语
            $config['supplementTip'] = 'Need to supplement following files';
            //个人中心
            $config['personalCenter'] = [
                'LoanHistory',
                //'MyProfile',
                //'ManageBankAccount',
                'CustomerService',
                'Help',
            ];
            $config['user_photo_num'] = 5;//获取照片数
            $config['user_photo_interval'] = 1;//获取照片间隔
        } catch (\Exception $e) {
            EmailHelper::sendException($e);
        }
        $config['h5AppPayUrl'] = HostHelper::getDomain() . '/app/repay/to-pay';
        return $config;
    }

    /**
     * @suppress PhanUndeclaredProperty
     * @return array
     */
    public function getLoan()
    {
        /** @var User $user */
        $user = \Auth::user();
        $loanAmountRange = LoanMultipleConfigServer::server()->getLoanAmountRange($user);
        $loanDaysRange = LoanMultipleConfigServer::server()->getLoanDaysRange($user);
        return [
            'principal' => range($loanAmountRange[0], $loanAmountRange[1], 100),
            'loan_days' => range($loanDaysRange[0], $loanDaysRange[1], 1),
        ];
    }

    /**
     *  用户配置信息
     *
     * @return array|bool
     */
    public function getUserInfoConfig()
    {
        return Config::model()->getUserInfo();
    }

    public function getOption($type)
    {
        if ($type == 'feedback_type') {
            return ArrayHelper::arrToOption(Feedback::TYPE);
        }
        if ($type == 'payment_type') {
            return \Common\Models\BankCard\BankCardPeso::PAYMENT_TYPE;
        }
        return [
            'feedback_type' => Feedback::TYPE,
            'bank_list' => BankInfo::model()->getBankList()
        ];
    }

    /**
     * 根据订单阶段和后台配置，判断是否可部分还款
     *
     * @param Order $order
     * @return bool
     */
    public function getPartRepayOn(Order $order)
    {
        list($partRepayOn, $minRepayAmount) = $this->getOrderPartRepay($order);
        return $partRepayOn;
    }

    /**
     * 根据订单阶段和后台配置，判断最低还款金额
     *
     * @param \Common\Models\Order\Order $order
     * @return mixed|string
     */
    public function getMinPartRepay(\Common\Models\Order\Order $order)
    {
        list($partRepayOn, $minRepayAmount) = $this->getOrderPartRepay($order);
        return $minRepayAmount;
    }

    /**
     * 订单部分还款
     *
     * @param \Common\Models\Order\Order $order
     * @return array
     */
    public function getOrderPartRepay(\Common\Models\Order\Order $order)
    {
        $repayAmount = $order->repayAmount();
        if (!(new Config)->getRepayPartRepayOn()
            || !($repayPartRepayConfig = (new Config)->getRepayPartRepayConfig())) {
            return [false, $repayAmount];
        }
        # 第一期
        if (optional($order->firstProgressingRepaymentPlan)->installment_num == 1) {
            /** @var RepaymentPlan $repaymentPlan */
            $repaymentPlan = $order->firstProgressingRepaymentPlan;
            # 到期前
            if (DateHelper::formatToDate($repaymentPlan->appointment_paid_time) > DateHelper::date()
                && array_get($repayPartRepayConfig, 'before.on')) {
                return [true, min(array_get($repayPartRepayConfig, 'before.min_part_repay'), $repayAmount)];
            }
            # 到期
            if (DateHelper::formatToDate($repaymentPlan->appointment_paid_time) == DateHelper::date()
                && array_get($repayPartRepayConfig, 'expire.on')) {
                return [true, min(array_get($repayPartRepayConfig, 'expire.min_part_repay'), $repayAmount)];
            }
            # 逾期 全局打开或者单条记录打开
            if (DateHelper::formatToDate($repaymentPlan->appointment_paid_time) < DateHelper::date()
                && array_get($repayPartRepayConfig, 'overdue.on')) {
                # 部分还款最低逾期天数配置
                $repayPartMinOverdueDays = (new Config)->getRepayPartMinOverdueDays();
                if ($repayPartMinOverdueDays === false) {
                    return [false, $repayAmount];
                }
                # 逾期天数小于配置跳过
                if ($repaymentPlan->overdue_days < $repayPartMinOverdueDays) {
                    return [false, $repayAmount];
                }
                # 催收设置可部分还款
                if ($repaymentPlan->can_part_repay == RepaymentPlan::CAN_PART_REPAY) {
                    return [true, min(array_get($repayPartRepayConfig, 'overdue.min_part_repay'), $repayAmount)];
                }
                # 所有逾期部分开关关闭
                if (!(new Config)->getRepayPartAllOverdueOn()) {
                    return [false, $repayAmount];
                }
                return [true, min(array_get($repayPartRepayConfig, 'overdue.min_part_repay'), $repayAmount)];
            }
        }
        return [false, $repayAmount];
    }

}
