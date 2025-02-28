<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 14:25
 */

namespace Tests\Admin\Approve;

use Api\Models\User\UserAuth;
use Common\Models\Config\Config;
use Tests\Admin\TestBase;

class ConfigTest extends TestBase
{

    public function testOption()
    {
        $params = [];
        $this->json('GET', '/api/config-option/info', $params)->seeJson()->getData();
    }

    public function testView()
    {
        $params = [];
        $this->json('GET', '/api/config/view', $params)->seeJson()->getData();
    }

    public function testUpdateSafe()
    {
        $params = [
            'login_ip_filter' => [
                '127.0.0.'.rand(1, 255),
                '127.0.0.'.rand(1, 255),
            ],
            'login_ip_filter_on' => 0,
            'only_login_on' => 1,
            'default_password' => '111111',
            'ding_login_on' => 0,
            'login_expire_hours' => 12,
        ];
        $this->json('POST', '/api/config/update-safe', $params)->seeJson()->getData();
    }

    public function testUpdateLoan()
    {
        $params = [
            Config::KEY_LOAN_AMOUNT_RANGE => ['500', '1000'], //借款金额范围
            Config::KEY_LOAN_DAYS_RANGE => ['15', '30'], //借款期限范围
            Config::KEY_LOAN_AGAIN_DAYS_RANGE => '30', //可重借天数
            Config::KEY_LOAN_DAILY_LOAN_RATE => 0.2, //正常借款日息
            Config::KEY_LOAN_AMOUNT_RANGE_OLD => ['500', '1500'], //借款金额范围
            Config::KEY_LOAN_DAYS_RANGE_OLD => ['7', '30'], //借款期限范围
            Config::KEY_LOAN_AGAIN_DAYS_RANGE_OLD => '60', //可重借天数
            Config::KEY_LOAN_DAILY_LOAN_RATE_OLD => 0.06, //正常借款日息
            Config::KEY_DAILY_CREATE_ORDER_MAX => 1000, //日申请笔数策略
            Config::KEY_DAILY_LOAN_AMOUNT_MAX => 10000, //日最大放款金额策略
            Config::KEY_LOAN_RENEWAL_ON => 1, // 续期开关
            Config::KEY_LOAN_RENEWAL_RATE => 0.1, // 续期费率
        ];
        $this->json('POST', '/api/config/update-loan', $params)->seeJson()->getData();
    }

    /**
     * 修改运营配置
     */
    public function testOperate()
    {
        $params = [
            'daily_register_user_max' => '10001',
            'daily_create_order_max' => '601',
            'daily_create_order_max_old' => '1001',
            'sys_auto_remit' => '0',
            'daily_loan_amount_max' => '1000001',

            'warning_daily_remain_create_order_value' => '4',
            'warning_daily_remain_create_order_value_old' => '5',
            'warning_daily_remain_loan_amount_value' => '21',

            'customer_service_email' => 'example@email.com',
            'customer_work_time' => ["周一", "周日", "9:00-18:30"],
            'company_name' => 'test',
            'company_address' => 'test',
            'company_offline_rapay_bamk' => 'test',
            'customer_service_telephone_numbers' => '0755-05225566',
        ];
        $this->json('POST', '/api/config/update-operate', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    public function testUpdateAuth()
    {
        $params = [
            'auth_setting' => [
                UserAuth::TYPE_BASE_INFO => ['daysValid' => 90, 'isOpen' => 1, 'closeTip' => ''],
                UserAuth::TYPE_CONTACTS => ['daysValid' => 90, 'isOpen' => 0, 'closeTip' => ''],
                UserAuth::TYPE_KYC_DOCUMENTS => ['daysValid' => 90, 'isOpen' => 1, 'closeTip' => 'Please fill in the reminder information of kyc'],
                UserAuth::TYPE_USER_EXTRA_INFO => ['daysValid' => 90, 'isOpen' => 0, 'closeTip' => ''],
                UserAuth::TYPE_BANKCARD => ['daysValid' => 90, 'isOpen' => 0, 'closeTip' => ''],
            ]
        ];
        $this->json('POST', '/api/config/update-auth', $params)->seeJson()->getData();
    }

    public function testUpdateRepay()
    {
        $params = [
            Config::KEY_REPAY_PART_REPAY_ON => 1,
            Config::KEY_REPAY_PART_CONFIG => [
                'before' => ['on' => 0, 'min_part_repay' => 100],
                'expire' => ['on' => 0, 'min_part_repay' => 200],
                'overdue' => ['on' => 0, 'min_part_repay' => 300],
            ],
            Config::KEY_REPAY_PART_MIN_OVERDUE_DAYS => 0,
            Config::KEY_REPAY_PART_ALL_OVERDUE_ON => 0,
        ];
        $this->json('POST', '/api/config/update-repay', $params)->seeJson()->getData();
    }

}
