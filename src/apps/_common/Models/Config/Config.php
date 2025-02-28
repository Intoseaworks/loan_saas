<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-23
 * Time: 18:13
 */

namespace Common\Models\Config;


use Common\Models\Order\Order;
use Common\Models\Trade\TradeLog;
use Common\Models\User\User;
use Common\Redis\RedisKey;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\Pay\BasePayServer;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Common\Models\Config
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string|null $remark
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereValue($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config orderByCustom($column = null, $direction = 'asc')
 * @property int $status 1:正常，-1:失效
 * @property string|null $forget_at 失效时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereForgetAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereStatus($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config whereMerchantId($value)
 */
class Config extends Model
{
    use StaticModel;

    /*------------------公共start----------------------*/
    /**
     * @var string
     */
    protected $table = 'config';

    const STATUS_NORMAL = 1;//正常
    const STATUS_FORGET = -1;//失效

    /** @var int cache 缓存，分钟 */
    const CACHE_EXPIRED = 1;
    /** @var bool 缓存开关 */
    protected static $cacheSwitch = false;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * 公共获取方法
     * @param $key
     * @param bool $default
     * @param $merchantId
     * @return array|bool|mixed
     * @throws \Exception
     */
    public static function get($key, $default = false, $merchantId = null)
    {
        $merchantId           = MerchantHelper::getMerchantId() ?? $merchantId;
        $where                = [
            'key' => $key,
            'status' => self::STATUS_NORMAL,
        ];
        $where['merchant_id'] = $merchantId;
        $query                = static::query()->where($where);
        if ($query->exists()) {
            $value = $query->value('value');
            if (is_array(json_decode($value, true))) {
                $value = (array)json_decode($value, true);
            }
            return $value;
        } else {
            return $default;
        }
    }

    /**
     * 公共设置方法
     * @param $merchantId
     * @param $key
     * @param $value
     * @param string $remark
     * @return bool
     * @throws \Exception
     */
    public static function set($merchantId, $key, $value, $remark = '')
    {
        $merchantId = MerchantHelper::getMerchantId() ?? $merchantId;
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $where = [
            'key' => $key,
            'merchant_id' => $merchantId
        ];
        $model = static::where($where)->first();
        if (!$model) {
            $model              = new static();
            $model->merchant_id = $merchantId;
            $model->key         = $key;
            $model->created_at  = date('Y-m-d H:i:s');
        }
        $oldValue          = $model->value;
        $model->value      = $value;
        $model->status     = self::STATUS_NORMAL;
        $model->updated_at = date('Y-m-d H:i:s');
        if ($remark != '') {
            $model->remark = $remark;
        }
        if ($model->save()) {
            ConfigLog::model()->add($key, $oldValue, $value);
            return true;
        }
    }

    protected static function getMerchantId($merchantId = null)
    {
        if ($oldMerchantId = MerchantHelper::getMerchantId()) {
            $merchantId = $oldMerchantId;
        }
        if (!isset($merchantId)) {
            throw new \Exception('config merchant_id 对应错误');
        }
        return $merchantId;
    }

    /**
     * 公共失效方法
     *
     * @param $key
     * @return bool
     */
    public static function forget($key)
    {
        $model = static::where('key', $key)->first();
        if ($model) {
            $model->status     = self::STATUS_FORGET;
            $model->updated_at = date('Y-m-d H:i:s');
            $model->forget_at  = date('Y-m-d H:i:s');
            $model->save();
        } else {
            return false;
        }
    }

    /**
     * 公共获取多个配置
     *
     * @param array $keys
     * @return mixed
     */
    public static function gets(array $keys)
    {
        $datas = static::whereIn('key', $keys)
            ->where('status', self::STATUS_NORMAL)
            ->pluck('value', 'key');
        foreach ($datas as $key => $val) {
            if (is_array(json_decode($val, true))) {
                $datas[$key] = (array)json_decode($val, true);
            }
        }
        return $datas;
    }

    /**
     * 公共获取所有配置
     *
     * @return mixed
     */
    public static function getAll()
    {
        $datas = static::where('status', self::STATUS_NORMAL)
            ->pluck('value', 'key');
        foreach ($datas as $key => $val) {
            if (is_array(json_decode($val))) {
                $datas[$key] = (array)json_decode($val, true);
            }
        }
        return $datas;
    }

    public static function openCache()
    {
        self::$cacheSwitch = true;
    }

    public static function closeCache()
    {
        self::$cacheSwitch = false;
    }

    /*------------------公共end----------------------*/

    /*------------------自定义业务start--------------------*/

    const SCENARIO_CREATE = 'create';


    /** 安全设置begin */
    const KEY_LOGIN_IP_FILTER     = 'login_ip_filter';//ip白名单
    const KEY_LOGIN_IP_FILTER_ON  = 'login_ip_filter_on';//ip白名单开关
    const KEY_LOGIN_ONLY_LOGIN_ON = 'only_login_on';//多地登录限制开关
    /** @deprecated */
    const KEY_DING_LOGIN_ON      = 'ding_login_on';//钉钉登录开关 --
    const KEY_DEFAULT_PASSWORD   = 'default_password';//默认密码
    const KEY_LOGIN_EXPIRE_HOURS = 'login_expire_hours';//账号有效时间
    /** 安全设置end */

    /** 认证设置 认证设置begin */
    const KEY_AUTH_EKYC_ON      = 'auth_ekyc_on'; //ekyc认证开关
    const KEY_AADHAAR_VERIFY_ON = 'aadhaar_verify_on'; //aadhaar验卡认证开关
    const KEY_AUTH_SETTING      = 'auth_setting';//认证项设置
    /** 认证设置 认证设置end */

    /** 还款设置 begin */
    const KEY_REPAY_PART_REPAY_ON         = 'repay_part_repay_on';//部分还款开关
    const KEY_REPAY_PART_CONFIG           = 'repay_part_repay_config';//部分还款配置
    const KEY_REPAY_PART_MIN_OVERDUE_DAYS = 'repay_part_repay_min_overdue_days';//部分还款最低逾期天数配置
    const KEY_REPAY_PART_ALL_OVERDUE_ON   = 'repay_part_repay_all_overdue_on';//部分还款所有逾期生效开关
    /** 还款设置 end */

    /** 贷款设置 新用户begin  */
    /** @deprecated */
    const KEY_LOAN_AMOUNT_RANGE = 'loan_amount_range'; //借款金额范围
    /** @deprecated */
    const KEY_LOAN_DAYS_RANGE = 'loan_days_range'; //借款期限范围
    /** @deprecated */
    const KEY_LOAN_AGAIN_DAYS_RANGE = 'loan_again_days_range'; //可重借天数
    /** @deprecated */
    const KEY_LOAN_DAILY_LOAN_RATE = 'loan_daily_loan_rate'; //正常借款日息
    /** @deprecated */
    const KEY_PROCESSING_RATE = 'processing_rate'; // 手续费 (手续费费率)
    /** @deprecated */
    const KEY_GST_PROCESSING_RATE = 'gst_processing_rate';// GST手续费率 (手续费GST)
    /** @deprecated */
    const KEY_PENALTY_RATE = 'penalty_rate'; // 滞纳金 (滞纳金日息)
    /** @deprecated */
    const KEY_GST_PENALTY_RATE = 'gst_penalty_rate'; // GST滞纳金税费 (滞纳金GST)
    /** 贷款设置 新用户end  */

    /** 贷款设置 老用户begin  */
    /** @deprecated */
    const KEY_LOAN_AMOUNT_RANGE_OLD = 'loan_amount_range_old'; //借款金额范围
    /** @deprecated */
    const KEY_LOAN_DAYS_RANGE_OLD = 'loan_days_range_old'; //借款期限范围
    /** @deprecated */
    const KEY_LOAN_AGAIN_DAYS_RANGE_OLD = 'loan_again_days_range_old'; //可重借天数
    /** @deprecated */
    const KEY_LOAN_DAILY_LOAN_RATE_OLD = 'loan_daily_loan_rate_old'; //正常借款日息
    /** @deprecated */
    const KEY_PROCESSING_RATE_OLD = 'processing_rate_old'; // 手续费 (手续费费率)
    /** @deprecated */
    const KEY_GST_PROCESSING_RATE_OLD = 'gst_processing_rate_old';// GST手续费率 (手续费GST)
    /** @deprecated */
    const KEY_PENALTY_RATE_OLD = 'penalty_rate_old'; // 滞纳金 (滞纳金日息)
    /** @deprecated */
    const KEY_GST_PENALTY_RATE_OLD = 'gst_penalty_rate_old'; // GST滞纳金税费 (滞纳金GST)
    /** 贷款设置 老用户end  */

    /** 贷款设置 公共begin  */
    const KEY_LOAN_RENEWAL_RATE = 'loan_renewal_rate'; //续期费率(预留)
    const KEY_LOAN_RENEWAL_ON = 'loan_renewal_on'; //是否开启续期，开启后在“待还款”、“逾期中”借款首页出现【续期】入口(预留)
    const KEY_LOAN_FORFEIT_PENALTY_RATE = 'loan_forfeit_penalty_rate'; //滞纳金费率
    const KEY_WITHDRAWAL_SERVICE_CHARGE = 'withdrawal_service_charge'; //线下取款手续费
    const KEY_WITHDRAWAL_ONL_SERVICE_CHARGE = 'withdrawal_online_service_charge'; //线下取款手续费

    const KEY_LOAN_GST_RATE = 'loan_gst_rate'; // GST费率
    const KEY_CLM_MIN_MAX_RATION = 'clm_min_max_ration'; // clm边界和系数提额配置
    /** 贷款设置 公共end  */

    /** 贷款设置 分期begin  */
    const KEY_FIRST_LOAN_REPAYMENT_RATE = 'first_loan_repayment_rate';//第一期贷款还款比率
    const KEY_LAST_LOAN_REPAYMENT_DEDUCTION_ON = 'last_loan_repayment_deduction_on';//尾期贷款还款减免开关
    const VALUE_MAX_LOAN_DAY = 150;//谷歌上架限制，借款必须大于60天
    /** 贷款设置 分期end  */

    /** 审批配置 begin */
    /** @deprecated */
    const KEY_APPROVE_ALL_RISK = 'approve_all_risk';  //是否开启全机审 开启后机审通过订单将进入放款流程 关闭后机审通过订单将转为人工复审
    /** @deprecated */
    const KEY_APPROVE_RISK_SCORE         = 'approve_risk_score';  //机审通过分数设置 建议设置30，分数越高逾期率越低，也将影响机审通过率
    const KEY_APPROVE_MANUAL_ALLOT_COUNT = 'approve_manual_allot_count';  //人工审批 可获取审批单数
    const KEY_APPROVE_MANUAL_OVERTIME    = 'approve_manual_overtime';  //每笔审批单超时（分钟）
    const KEY_APPROVE_MANUAL_MAX_COUNT    = 'approve_manual_max_count';  //指每个审批账号名下审批中的订单数最大的数量。包括初审，电审，挂起
    const KEY_APPROVE_NEW_USER_PROCESS   = 'approve_new_user_process';  //新用户审批流程
    const KEY_APPROVE_OLD_USER_PROCESS   = 'approve_old_user_process';  //老用户审批流程
    const KEY_APPROVE_EKYC_FAIL_PROCESS  = 'approve_ekyc_fail_process'; //特殊流程e-kyc审批流程
    /** 审批配置 end */

    /** 运营配置  */
    const KEY_DAILY_REGISTER_USER_MAX            = 'daily_register_user_max'; // 日最大注册用户数量
    const KEY_DAILY_CREATE_ORDER_MAX             = 'daily_create_order_max'; // 新用户日最大申请笔数
    const KEY_DAILY_CREATE_ORDER_MAX_OLD         = 'daily_create_order_max_old'; // 复贷用户日最大申请笔数
    const KEY_DAILY_LOAN_AMOUNT_MAX              = 'daily_loan_amount_max'; // 日最大放款金额
    const KEY_CUSTOMER_SERVICE_TELEPHONE_NUMBERS = 'customer_service_telephone_numbers'; // 电话
    const KEY_CUSTOMER_SERVICE_EMAIL             = 'customer_service_email'; // 邮箱
    const KEY_CUSTOMER_WORK_TIME                 = 'customer_work_time'; // 工作时间
    const KEY_COMPANY_NAME                       = 'company_name'; // 公司名称
    const KEY_COMPANY_ADDRESS                    = 'company_address'; // 公司地址
    const KEY_COMPANY_OFFLINE_RAPAY_BAMK         = 'company_offline_rapay_bamk'; // 公司线下还款银行账户
    // 预警设置
    const KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE     = 'warning_daily_remain_create_order_value'; // 新用户日剩余可申请笔数 预警值
    const KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE_OLD = 'warning_daily_remain_create_order_value_old'; // 复贷用户日剩余可申请笔数 预警值
    const KEY_WARNING_DAILY_REMAIN_LOAN_AMOUNT_VALUE      = 'warning_daily_remain_loan_amount_value'; // 日剩余可放款金额 预警值
    /** 运营配置  */

    /** 催收配置  */
    const KEY_COLLECTION_BAD_DAYS = 'collection_bad_days'; // 逾期天数转坏账
    const KEY_COLLECTION_MAX_DEDUCTION_USERS_PER_DAY = 'collection_max_deduction_users_per_day'; //单一账户每日最多申请减免数
    const KEY_COLLECTION_MAX_DEDUCTION_AMOUNT_PER_DAY = 'collection_max_deduction_amount_per_day'; //单一账户每日最大可申请减免金额
    const KEY_COLLECTION_MAX_DEDUCTION_SMS_MESSAGES_PER_ORDER = 'collection_max_deduction_sms_messages_per_order'; //单笔订单做多可发催收短信数
    const KEY_COLLECTION_MAX_DEDUCTION_STAFF_SMS_AMOUNT_PER_DAY = 'collection_max_deduction_staff_sms_amount_per_day'; //单个催收员每日最多可发催收短信数
    /** 催收配置  */

    /** 商户信息设置 */
    /** @deprecated */
    const KEY_PARTNER_ACCOUNT_COMPANY_NAME = 'partner_account_company_name'; // 公司主体名称 --
    /** @deprecated */
    const KEY_PARTNER_ACCOUNT_APP_NAME = 'partner_account_app_name'; // APP产品名称 --
    /** @deprecated */
    const KEY_PARTNER_ACCOUNT_CONTRACT = 'partner_account_contract'; // 公司联系人 --
    /** @deprecated */
    const KEY_PARTNER_ACCOUNT_CONTRACT_TEL = 'partner_account_contract_tel'; // 公司联系人号码 --
    /** @deprecated */
    const KEY_PARTNER_ACCOUNT_LENDER_NAME = 'partner_account_lender_name'; // 出借人姓名 --
    /** @deprecated */
    const KEY_PARTNER_ACCOUNT_LENDER_ID_CARD = 'partner_account_lender_id_card'; // 出借人身份证 --

    const KEY_PARTNER_ACCOUNT_COMPANY_ADDRESS = 'partner_account_company_address'; // 公司主体地址 --
    /** 商户信息设置 */

    /** 初始化角色标识 */
    const KEY_INIT_ROLE_SYMBOL = 'init_role_symbol';
    /** 初始化角色标识 */

    /** 用 php artisan permission:init-role 初始化的记录,用来区分用户配置的 */
    const KEY_HISTORY_MENU = 'history_menu';
    /** 用 php artisan permission:init-role 初始化的记录,用来区分用户配置的 */

    /** 终端oc SDK 开关 */
    const KEY_HYPER_OCR_SWITCH = 'hyper_ocr_switch';

    /** 系统设置 begin */
    /** @see BasePayServer::hasDaifuOpen() */
    const KEY_SYS_AUTO_REMIT          = 'sys_auto_remit';    // 商户代付开关
    const KEY_SYS_AUTO_REMIT_PLATFORM = 'sys_auto_remit_platform'; // 商户代付平台
    const KEY_SYS_REPAY_PLATFORM      = 'sys_repay_platform'; //商户还款方式
    /** 系统设置 end */

    /** App版本更新begin */
    const KEY_APP_VERSION_NO                   = 'app_version_no';//最新版本
    const KEY_APP_VERSION_UPDATEABLE           = 'app_version_updateable';
    const KEY_APP_VERSION_FORCIBLE             = 'app_version_forcible';//是否强制更新
    const KEY_APP_VERSION_TITLE                = 'app_version_title';//强更标题
    const KEY_APP_VERSION_CONTENT              = 'app_version_content';//强更内容
    const KEY_APP_VERSION_HOT_RELATIVE_VERSION = 'app_version_hot_relative_version';//热更
    const KEY_APP_VERSION_URL                  = 'app_version_url';//强更链接

    const KEY_APP_VERSION_NO_IOS              = 'app_version_no_ios';//最新版本
    const KEY_APP_VERSION_UPDATEABLE_IOS           = 'app_version_updateable_ios';
    const KEY_APP_VERSION_FORCIBLE_IOS             = 'app_version_forcible_ios';//是否强制更新
    const KEY_APP_VERSION_TITLE_IOS                = 'app_version_title_ios';//强更标题
    const KEY_APP_VERSION_CONTENT_IOS              = 'app_version_content_ios';//强更内容
    const KEY_APP_VERSION_HOT_RELATIVE_VERSION_IOS = 'app_version_hot_relative_version_ios';//热更
    const KEY_APP_VERSION_URL_IOS                  = 'app_version_url_ios';//强更链接
    /** App版本更新end */
    
    const KEY_SMS_GLOBE_TOKEN = 'sms_globe_token';

    /** 商户信息 */
    /** @deprecated */
    const CONFIG_PARTNER_ACCOUNT_INFO = [
        self::KEY_PARTNER_ACCOUNT_COMPANY_NAME,
        self::KEY_PARTNER_ACCOUNT_APP_NAME,
        self::KEY_PARTNER_ACCOUNT_CONTRACT,
        self::KEY_PARTNER_ACCOUNT_CONTRACT_TEL,
        self::KEY_PARTNER_ACCOUNT_LENDER_NAME,
        self::KEY_PARTNER_ACCOUNT_LENDER_ID_CARD,
        self::KEY_PARTNER_ACCOUNT_COMPANY_ADDRESS,
        self::KEY_COMPANY_NAME,
        self::KEY_COMPANY_ADDRESS,
    ];

    const CONFIG_ALIAS = [
        // 认证设置
        self::KEY_AUTH_EKYC_ON => 'ekyc认证开关',
        self::KEY_AADHAAR_VERIFY_ON => 'aadhaar验卡认证开关',
        self::KEY_AUTH_SETTING => '认证项设置',
        // 还款设置
        self::KEY_REPAY_PART_REPAY_ON => '部分还款开关',
        self::KEY_REPAY_PART_CONFIG => '部分还款配置',
        self::KEY_REPAY_PART_MIN_OVERDUE_DAYS => '部分还款最低逾期天数配置',
        self::KEY_REPAY_PART_ALL_OVERDUE_ON => '部分还款所有逾期生效开关',
        // 安全设置
        self::KEY_LOGIN_IP_FILTER => 'ip白名单',
        self::KEY_LOGIN_IP_FILTER_ON => 'ip白名单开关',
        self::KEY_LOGIN_ONLY_LOGIN_ON => '多地登录开关',
        self::KEY_DING_LOGIN_ON => '钉钉登录开关',
        self::KEY_DEFAULT_PASSWORD => '默认密码',
        self::KEY_LOGIN_EXPIRE_HOURS => '账号有效时间',
        // 贷款设置 新用户
        self::KEY_LOAN_AMOUNT_RANGE => '借款金额范围',
        self::KEY_LOAN_DAYS_RANGE => '借款期限范围',
        self::KEY_LOAN_DAILY_LOAN_RATE => '正常借款日息',
        self::KEY_LOAN_AGAIN_DAYS_RANGE => '可重借天数(天)',
        self::KEY_PROCESSING_RATE => 'Fees charged by banks when lending',
        self::KEY_GST_PROCESSING_RATE => 'Taxes and charges on bank transfers when lending',
        self::KEY_PENALTY_RATE => 'Daily charges within 120 days of overdue',
        self::KEY_GST_PENALTY_RATE => 'Taxes and charges for overdue expenses',
        // 贷款设置 老用户
        self::KEY_LOAN_AMOUNT_RANGE_OLD => '借款金额范围 老用户',
        self::KEY_LOAN_DAYS_RANGE_OLD => '借款期限范围 老用户',
        self::KEY_LOAN_AGAIN_DAYS_RANGE_OLD => '可重借天数 老用户',
        self::KEY_LOAN_DAILY_LOAN_RATE_OLD => '正常借款日息 老用户',
        self::KEY_PROCESSING_RATE_OLD => 'Fees charged by banks when lending',
        self::KEY_GST_PROCESSING_RATE_OLD => 'Taxes and charges on bank transfers when lending',
        self::KEY_PENALTY_RATE_OLD => 'Daily charges within 120 days of overdue',
        self::KEY_GST_PENALTY_RATE_OLD => 'Taxes and charges for overdue expenses',
        // 贷款设置 公共
        self::KEY_LOAN_RENEWAL_RATE => '续期费率',
        self::KEY_LOAN_RENEWAL_ON => '续期开关',
        self::KEY_LOAN_GST_RATE => 'GST费率',
        self::KEY_LOAN_FORFEIT_PENALTY_RATE => '滞纳金费率',
        self::KEY_WITHDRAWAL_SERVICE_CHARGE => '线下取款手续费',
        // 贷款设置 分期
        self::KEY_FIRST_LOAN_REPAYMENT_RATE => '第一期贷款还款比率',
        self::KEY_LAST_LOAN_REPAYMENT_DEDUCTION_ON => '尾期贷款还款减免开关(0关闭 1开启)',
        // 审批配置
        self::KEY_APPROVE_ALL_RISK => '是否开启全机审',
        self::KEY_APPROVE_RISK_SCORE => '机审通过分数设置',
        self::KEY_APPROVE_MANUAL_ALLOT_COUNT => '人工审批 可获取审批单数',
        self::KEY_APPROVE_MANUAL_OVERTIME => '每笔审批单超时（分钟）',
        self::KEY_APPROVE_MANUAL_MAX_COUNT => '每人审批中的订单最大数量(初审电审挂起)',
        self::KEY_APPROVE_NEW_USER_PROCESS => '新用户审批流程',
        self::KEY_APPROVE_OLD_USER_PROCESS => '老用户审批流程',
        self::KEY_APPROVE_EKYC_FAIL_PROCESS => 'e-KYC审批流程',
        // 运营配置
        self::KEY_DAILY_REGISTER_USER_MAX => '日最大注册用户数量',
        self::KEY_DAILY_CREATE_ORDER_MAX => '新用户日最大申请笔数',
        self::KEY_DAILY_CREATE_ORDER_MAX_OLD => '复贷用户日最大申请笔数',
        self::KEY_DAILY_LOAN_AMOUNT_MAX => '日最大放款金额',
        self::KEY_CUSTOMER_SERVICE_TELEPHONE_NUMBERS => '客服电话号码',
        self::KEY_CUSTOMER_WORK_TIME => '客服工作时间',
        self::KEY_CUSTOMER_SERVICE_EMAIL => '客服邮箱',
        self::KEY_COMPANY_NAME => '公司名称',
        self::KEY_COMPANY_ADDRESS => '公司地址',
        self::KEY_COMPANY_OFFLINE_RAPAY_BAMK => '公司线下还款银行账户',
        self::KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE => '新用户日剩余可申请笔数预警值',
        self::KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE_OLD => '复贷用户日剩余可申请笔数预警值',
        self::KEY_WARNING_DAILY_REMAIN_LOAN_AMOUNT_VALUE => '日剩余可放款金额预警值',
        // 催收设置
        self::KEY_COLLECTION_BAD_DAYS => '逾期天数转坏账',
        // 其他
        self::KEY_PARTNER_ACCOUNT_COMPANY_NAME => '公司主体名称',
        self::KEY_PARTNER_ACCOUNT_APP_NAME => 'app产品名称',
        self::KEY_PARTNER_ACCOUNT_CONTRACT => '公司联系人',
        self::KEY_PARTNER_ACCOUNT_CONTRACT_TEL => '公司联系人号码',
        self::KEY_PARTNER_ACCOUNT_LENDER_NAME => '出借人姓名',
        self::KEY_PARTNER_ACCOUNT_LENDER_ID_CARD => '出借人身份证号码',
        // 系统相关
        self::KEY_INIT_ROLE_SYMBOL => '初始化角色标识,初始化角色只会执行一次,再次执行是更新 1未执行 2已初始化',
        self::KEY_HISTORY_MENU => '用 php artisan permission:init-role 初始化的记录',
        self::KEY_HYPER_OCR_SWITCH => 'hyper ocr是否开启(0关闭 1开启)',
        self::KEY_SYS_AUTO_REMIT => '商户代付开关',
        self::KEY_SYS_AUTO_REMIT_PLATFORM => '商户代付平台',

        self::KEY_APP_VERSION_NO => '最新版本',
        self::KEY_APP_VERSION_UPDATEABLE => 'updateable',
        self::KEY_APP_VERSION_FORCIBLE => '是否强制更新',
        self::KEY_APP_VERSION_TITLE => '强更标题',
        self::KEY_APP_VERSION_CONTENT => '强更内容',
        self::KEY_APP_VERSION_HOT_RELATIVE_VERSION => '热更',
        self::KEY_APP_VERSION_URL => '强更链接',

        self::KEY_APP_VERSION_NO_IOS => 'IOS 最新版本',
        self::KEY_APP_VERSION_UPDATEABLE_IOS => 'IOS updateable',
        self::KEY_APP_VERSION_FORCIBLE_IOS => 'IOS 是否强制更新',
        self::KEY_APP_VERSION_TITLE_IOS => 'IOS 强更标题',
        self::KEY_APP_VERSION_CONTENT_IOS => 'IOS 强更内容',
        self::KEY_APP_VERSION_HOT_RELATIVE_VERSION_IOS => 'IOS 热更',
        self::KEY_APP_VERSION_URL_IOS => 'IOS 强更链接',
    ];
    protected $guarded = [];

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'key',
                'value',
                'remark',
            ]
        ];
    }

    public static function getKeys($name = null)
    {
        $configs = Config::CONFIG_ALIAS;
        !is_null($name) && $configs = array_only(Config::CONFIG_ALIAS, (array)$name);

        return array_keys($configs);
    }

    /**
     * 获取配置值必要方法 [值]
     * @param $key
     * @param bool $default
     * @param $merchantId
     * @return array|bool|mixed
     * @throws \Exception
     */
    public static function getValueByKey($key, $default = false, $merchantId = null)
    {
        if (!$merchantId) {
            $merchantId = MerchantHelper::getMerchantId();
        }

        if (static::$cacheSwitch) {
            return Cache::remember(static::getCacheKey($key, $merchantId), static::CACHE_EXPIRED,
                function () use ($key, $default, $merchantId) {
                    return self::get($key, $default, $merchantId);
                });
        }

        return self::get($key, $default, $merchantId);

        /*if (static::where('key', $key)->exists()) {
            $value = static::where('key', $key)->value('value');
            if (is_array(json_decode($value))) {
                $value = (array)json_decode($value, true);
            }
            return $value;
        } else {
            return $default;
        }*/
    }

    protected static function getCacheKey($key, $merchantId)
    {
        return RedisKey::CACHE_CONFIG . ':' . $merchantId . ':' . $key;
    }

    /**
     * 获取配置值必要方法['键' => '值']
     * @param $keys
     * @return mixed
     */
    public static function getValueByKeys($keys)
    {
        return self::gets($keys);

        /*$keys = (array)$keys;
        $datas = static::whereIn('key', $keys)->pluck('value', 'key');
        foreach ($datas as $key => $val) {
            if (is_array(json_decode($val))) {
                $datas[$key] = (array)json_decode($val, true);
            }
        }
        return $datas;*/
    }

    public static function findOrCreate($merchantId, $key, $value)
    {
        if (self::get($key, false, $merchantId) !== false) {
            return true;
        }
        $remark = array_get(self::CONFIG_ALIAS, $key, '');
        return self::set($merchantId, $key, $value, $remark);

        /*if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $data = [
            'value' => $value,
            'remark' => $remark = array_get(self::CONFIG_ALIAS, $key, '')
        ];
        return self::firstOrCreateModel(self::SCENARIO_CREATE, ['key' => $key], $data);*/
    }

    /**
     * 创建/更新配置必要方法
     * @param $key
     * @param $value
     * @param $merchantId
     * @return Config
     */
    public static function createOrUpdate($key, $value, $merchantId = null)
    {
        $remark = array_get(self::CONFIG_ALIAS, $key, '');
        return self::set($merchantId, $key, $value, $remark);

        /*if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $data = [
            'value' => $value,
            'remark' => $remark = array_get(self::CONFIG_ALIAS, $key, '')
        ];
        return self::updateOrCreateModel(self::SCENARIO_CREATE, ['key' => $key], $data);*/
    }

    /**
     * 安全配置 - 登录过期时间
     * @return array|bool
     */
    public function getLoginExpireHours()
    {
        return self::getValueByKey(self::KEY_LOGIN_EXPIRE_HOURS);
    }

    /**
     * 安全配置 - IP白名单
     * @return array|bool
     */
    public function getLoginIpFilter()
    {
        return self::getValueByKey(self::KEY_LOGIN_IP_FILTER);
    }

    /**
     * 安全配置 - IP拦截开关
     * @return array|bool
     */
    public function getLoginIpFilterOn()
    {
        return self::getValueByKey(self::KEY_LOGIN_IP_FILTER_ON);
    }

    /**
     * 安全配置 - 获取默认密码
     */
    public static function getDefaultPassword()
    {
        return self::getValueByKey(static::KEY_DEFAULT_PASSWORD);
    }

    /**
     * 认证设置 - ekyc认证开关
     * @return bool
     */
    public function getAuthEkycOn()
    {
        return self::getValueByKey(static::KEY_AUTH_EKYC_ON);
    }

    /**
     * 认证设置 - aadhaar验卡认证开关
     * @return bool
     */
    public function getAadhaarVerifyOn()
    {
        return self::getValueByKey(static::KEY_AADHAAR_VERIFY_ON);
    }

    public function getAuthSetting()
    {
        return self::getValueByKey(static::KEY_AUTH_SETTING, null);
    }

    /**
     * 贷款配置 - 借款金额范围
     * @param int $quality
     * @return array|bool
     * @deprecated
     * @see LoanMultipleConfigServer::getLoanAmountRange()
     */
    public function getLoanAmountRange($quality = User::QUALITY_NEW)
    {
        if ($quality == User::QUALITY_OLD) {
            return self::getValueByKey(self::KEY_LOAN_AMOUNT_RANGE_OLD);
        }
        return self::getValueByKey(self::KEY_LOAN_AMOUNT_RANGE);
    }

    /**
     * 贷款配置 - 借款期限范围
     * @param int $quality
     * @return array|bool
     * @deprecated
     * @see LoanMultipleConfigServer::getLoanDaysRange()
     */
    public function getLoanDaysRange($quality = User::QUALITY_NEW)
    {
        if ($quality == User::QUALITY_OLD) {
            return self::getValueByKey(self::KEY_LOAN_DAYS_RANGE_OLD);
        }
        return self::getValueByKey(self::KEY_LOAN_DAYS_RANGE);
    }

    /**
     * 获取最大额度
     * @param int $quality
     * @return mixed
     * @deprecated
     * @see LoanMultipleConfigServer::getLoanAmountMax()
     */
    public function getLoanAmountMax($quality = User::QUALITY_NEW)
    {
        $loanAmountRange = Config::model()->getLoanAmountRange($quality);
        $loanAmountMax   = max($loanAmountRange);
        return $loanAmountMax;
    }

    /**
     * 获取最大借款期限
     * @param int $quality
     * @return mixed
     * @deprecated
     * @see LoanMultipleConfigServer::getLoanDaysMax()
     */
    public function getLoanDaysMax($quality = User::QUALITY_NEW)
    {
        $loanDaysRange = Config::model()->getLoanDaysRange($quality);
        $loanDaysMax   = max($loanDaysRange);
        return $loanDaysMax;
    }

    /**
     * 贷款配置 - 正常借款日息
     * @param int $quality
     * @return float|int
     * @throws \Exception
     * @deprecated
     * @see LoanMultipleConfigServer::getDailyRate()
     */
    public function getDailyRate($quality = User::QUALITY_NEW)
    {
        /** 元数据单位% */
        if ($quality == User::QUALITY_OLD) {
            return self::getValueByKey(self::KEY_LOAN_DAILY_LOAN_RATE_OLD) / 100;
        }
        return self::getValueByKey(self::KEY_LOAN_DAILY_LOAN_RATE) / 100;
    }

    /**
     * 贷款配置 - 续期费率
     * @return float|int
     * @deprecated
     * @see LoanMultipleConfigServer::getLoanRenewalRate()
     */
    public function getRenewalRate()
    {
        return self::getValueByKey(self::KEY_LOAN_RENEWAL_RATE) / 100;
    }

    /**
     * 贷款配置 - 滞纳金费率
     * @return float|int
     * @deprecated
     * @see LoanMultipleConfigServer::getLoanForfeitPenaltyRate()
     */
    public function getForfeitPenaltyRate()
    {
        return self::getValueByKey(self::KEY_LOAN_FORFEIT_PENALTY_RATE) / 100;
    }

    /**
     * 贷款配置 - 尾期贷款还款减免开关
     * @return bool|int|string
     */
    public static function getLastLoanRepaymentDeductionOn()
    {
        return self::getValueByKey(self::KEY_LAST_LOAN_REPAYMENT_DEDUCTION_ON);
    }

    /**
     * 贷款配置 - GST费率
     * @param $useCalc
     * @return array|bool|mixed
     * @throws \Exception
     */
    public static function getLoanGstRate($useCalc = true)
    {
        $rate = self::getValueByKey(self::KEY_LOAN_GST_RATE);

        return $useCalc ? $rate / 100 : $rate;
    }

    /**
     * 贷款配置 - 第一期贷款还款比率
     * @return bool|int|string
     */
    public static function getFirstLoanRepaymentRate()
    {
        return self::getValueByKey(self::KEY_FIRST_LOAN_REPAYMENT_RATE);
    }

    /**
     * 贷款配置 - 是否开启续期
     * @return bool|int|string
     */
    public static function getLoanRenewalOn()
    {
        return self::getValueByKey(self::KEY_LOAN_RENEWAL_ON, false);
    }

    /**
     * 线下取款手续费
     * @return mixed
     * @throws \Exception
     */
    public static function getWithdrawalServiceCharge()
    {
        return self::getValueByKey(self::KEY_WITHDRAWAL_SERVICE_CHARGE, 0);
    }
    
    public static function getOnlineServiceCharge(){
        return self::getValueByKey(self::KEY_WITHDRAWAL_ONL_SERVICE_CHARGE, 20);
    }

    /**
     * 审批设置 是否开启全机审
     * @return bool|int|string
     * @deprecated
     */
    public static function getApproveAllRisk()
    {
        return self::getValueByKey(self::KEY_APPROVE_ALL_RISK, true);
    }

    /**
     * 审批设置 机审通过分数设置
     * @return bool|int|string
     * @deprecated
     */
    public static function getApproveRiskScore()
    {
        return self::getValueByKey(self::KEY_APPROVE_RISK_SCORE, 30);
    }

    /**
     * 审批设置 可获取审批单数
     * @return bool|int|string
     */
    public static function getApproveManualAllotCount()
    {
        return self::getValueByKey(self::KEY_APPROVE_MANUAL_ALLOT_COUNT);
    }

    /**
     * 审批设置 每笔审批单超时（分钟）
     * @return bool|int|string
     */
    public static function getApproveManualOvertime()
    {
        return self::getValueByKey(self::KEY_APPROVE_MANUAL_OVERTIME);
    }

    /**
     * 审批设置 每人审批中的订单最大数量(初审电审挂起)
     * @return bool|int|string
     */
    public static function getApproveManualMaxCount()
    {
        return self::getValueByKey(self::KEY_APPROVE_MANUAL_MAX_COUNT);
    }

    /**
     * 获取可重新借款天数
     * @param $quality
     * @return bool|string
     * @deprecated
     * @see LoanMultipleConfigServer::getLoanAgainDays()
     */
    public function getLoanAgainDays($quality)
    {
        if ($quality == User::QUALITY_OLD) {
            return self::getValueByKey(self::KEY_LOAN_AGAIN_DAYS_RANGE_OLD);
        }
        return self::getValueByKey(self::KEY_LOAN_AGAIN_DAYS_RANGE);
    }

    /**
     * 获取客服电话
     * @return array
     */
    public function getCustomerTelephoneNumbers()
    {
        $telephone = (array)self::getValueByKey(self::KEY_CUSTOMER_SERVICE_TELEPHONE_NUMBERS);
//        list($weekStart, $weekEnd, $time) = self::getValueByKey(self::KEY_CUSTOMER_WORK_TIME);
        return $telephone;
    }

    /**
     * 获取客服邮箱
     * @return array|bool
     */
    public function getCustomerEmail()
    {
        return self::getValueByKey(self::KEY_CUSTOMER_SERVICE_EMAIL);
    }

    /**
     * 公司名称
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getCompanyName()
    {
        return self::getValueByKey(self::KEY_COMPANY_NAME, '');
    }

    /**
     * 公司地址
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getCompanyAddress()
    {
        return self::getValueByKey(self::KEY_COMPANY_ADDRESS, '');
    }

    /**
     * 公司线下还款银行账户
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getCompanyOfflineRepayBank()
    {
        return self::getValueByKey(self::KEY_COMPANY_OFFLINE_RAPAY_BAMK, '');
    }

    /**
     * @return Collection
     */
    public function getPartnerAccountInfo()
    {
        return self::getValueByKeys(static::CONFIG_PARTNER_ACCOUNT_INFO);
    }

    /**
     * 1未执行 2已初始化
     *
     * @return array|bool
     */
    public function getInitRoleSymbol()
    {
        return self::getValueByKey(static::KEY_INIT_ROLE_SYMBOL);
    }

    /**
     * @return array|bool
     */
    public function getHistoryMenu()
    {
        return self::getValueByKey(static::KEY_HISTORY_MENU);
    }

    /**
     * @return string
     * @deprecated
     * @see Config::getLoanGstRate()
     */
    public function getGstProcessingRate($quality)
    {
        if ($quality == User::QUALITY_OLD) {
            return static::getValueByKey(static::KEY_GST_PROCESSING_RATE_OLD) / 100;
        }
        return static::getValueByKey(static::KEY_GST_PROCESSING_RATE) / 100;
    }

    /**
     * @return string
     * @deprecated
     * @see Config::getLoanGstRate()
     */
    public function getGstPenaltyRate($quality)
    {
        if ($quality == User::QUALITY_OLD) {
            return static::getValueByKey(static::KEY_GST_PENALTY_RATE_OLD) / 100;
        }
        return static::getValueByKey(static::KEY_GST_PENALTY_RATE) / 100;
    }

    /**
     * @return string
     * @deprecated
     * @see LoanMultipleConfigServer::getServiceChargeRate()
     */
    public function getProcessingRate($quality)
    {
        if ($quality == User::QUALITY_OLD) {
            return static::getValueByKey(static::KEY_PROCESSING_RATE_OLD) / 100;
        }
        return static::getValueByKey(static::KEY_PROCESSING_RATE) / 100;
    }

    /**
     * @param $quality
     * @return string
     * @throws \Exception
     * @deprecated
     * @see LoanMultipleConfigServer::getPenaltyRate()
     */
    public function getPenaltyRate($quality)
    {
        if ($quality == User::QUALITY_OLD) {
            return static::getValueByKey(static::KEY_PENALTY_RATE_OLD) / 100;
        }
        return static::getValueByKey(static::KEY_PENALTY_RATE) / 100;
    }

    /**
     * @param $quality
     * @return array
     * @throws \Exception
     */
    public function getUserApproveProcess($quality)
    {
        if ($quality == Order::QUALITY_OLD) {
            return static::getValueByKey(Config::KEY_APPROVE_OLD_USER_PROCESS);
        }
        return static::getValueByKey(Config::KEY_APPROVE_NEW_USER_PROCESS);
    }

    /**
     * ekyc认证失败特殊审批流程
     * @return array
     */
    public function getEKYCFailApproveProcess()
    {
        return static::getValueByKey(Config::KEY_APPROVE_EKYC_FAIL_PROCESS);
    }

    /**
     * hyper ocr开关
     *
     * @return bool
     */
    public function getHyperOcrIsOpen()
    {
        return static::getValueByKey(static::KEY_HYPER_OCR_SWITCH) == 1 ?: false;
    }

    /**
     * 商户自动代付开关是否打开
     * @return bool
     * @throws \Exception
     */
    public function sysAutoRemitIsOpen()
    {
        return static::getValueByKey(static::KEY_SYS_AUTO_REMIT, 0) == 1 ?: false;
    }

    /**
     * 获取商户代付支付平台
     * @return array
     * @throws \Exception
     */
    public function sysAutoRemitPlatform()
    {
        $platform = static::getValueByKey(static::KEY_SYS_AUTO_REMIT_PLATFORM, TradeLog::TRADE_PLATFORM_PAYMOB);

        if (!in_array($platform, TradeLog::TRADE_PLATFORM_HAS_PAYOUT)) {
            throw new \Exception('config配置支付渠道不支持，merchant_id:' . MerchantHelper::getMerchantId());
        }

        return $platform;
    }

    /**
     * 获取还款方式
     * @return array
     * @throws \Exception
     */
    public function sysRepayPlatform()
    {
        $platform = TradeLog::TRADE_PLATFORM;

        return $platform;
    }

    /**
     * 日最大注册用户数量
     * 设置为0表示不限注册=>INF
     * @return float
     * @throws \Exception
     */
    public function getDailyRegisterUserMax()
    {
        $maxRegister = (float)static::getValueByKey(static::KEY_DAILY_REGISTER_USER_MAX, 0);

//        if ($maxRegister == 0) {
//            return INF;
//        }

        return $maxRegister;
    }

    /**
     * 日最大申请笔数
     * @param $quality
     * @return float
     * @throws \Exception
     */
    public function getDailyCreateOrderMax($quality)
    {
        if ($quality == User::QUALITY_OLD) {
            return static::getValueByKey(Config::KEY_DAILY_CREATE_ORDER_MAX_OLD, 0);
        }
        return static::getValueByKey(Config::KEY_DAILY_CREATE_ORDER_MAX, 0);
    }

    /**
     * 日最大放款金额
     * 设置为0表示不限制放款额=>INF
     * @return float
     * @throws \Exception
     */
    public function getDailyLoanAmountMax()
    {
        $maxLoanAmount = (float)static::getValueByKey(static::KEY_DAILY_LOAN_AMOUNT_MAX, 0);

//        if ($maxLoanAmount == 0) {
//            return INF;
//        }

        return $maxLoanAmount;
    }

    /**
     * 预警值 日剩余可申请笔数
     * 未设置值时，不进行预警
     * @param $quality
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getWarningDailyRemainCreateOrderValue($quality)
    {
        if ($quality == Order::QUALITY_OLD) {
            return static::getValueByKey(Config::KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE_OLD);
        }
        return static::getValueByKey(Config::KEY_WARNING_DAILY_REMAIN_CREATE_ORDER_VALUE);
    }

    /**
     * 预警值 日剩余可放款金额
     * 未设置值时，不进行预警
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getWarningDailyRemainLoanAmountValue()
    {
        return static::getValueByKey(static::KEY_WARNING_DAILY_REMAIN_LOAN_AMOUNT_VALUE, '');
    }

    /** App版本更新begin */
    public function getAppVersionNo($platform = 'android')
    {
        $key = static::KEY_APP_VERSION_NO;
        if($platform == "ios"){
            $key = static::KEY_APP_VERSION_NO_IOS;
        }
        return static::getValueByKey($key, '1.0.0');
    }

    public function getAppVersionUpdateable($platform = 'android')
    {
        $key = static::KEY_APP_VERSION_UPDATEABLE;
        if($platform == "ios"){
            $key = static::KEY_APP_VERSION_UPDATEABLE_IOS;
        }
        return static::getValueByKey($key, 0) ? true : false;
    }

    public function getAppVersionForcible($platform = 'android')
    {
        $key = static::KEY_APP_VERSION_FORCIBLE;
        if($platform == "ios"){
            $key = static::KEY_APP_VERSION_FORCIBLE_IOS;
        }
        return static::getValueByKey($key, 0) ? true : false;
    }

    public function getAppVersionTitle($platform = 'android')
    {
        $key = static::KEY_APP_VERSION_TITLE;
        if($platform == "ios"){
            $key = static::KEY_APP_VERSION_TITLE_IOS;
        }
        return static::getValueByKey($key, 'New Version Update');
    }

    public function getAppVersionContent($platform = 'android')
    {
        $key = static::KEY_APP_VERSION_CONTENT;
        if($platform == "ios"){
            $key = static::KEY_APP_VERSION_CONTENT_IOS;
        }
        return static::getValueByKey($key, 'Optimize product experience');
    }

    public function getAppVersionHotRelativeVersion($platform = 'android')
    {
        $key = static::KEY_APP_VERSION_HOT_RELATIVE_VERSION;
        if($platform == "ios"){
            $key = static::KEY_APP_VERSION_HOT_RELATIVE_VERSION_IOS;
        }
        return static::getValueByKey($key, '');
    }

    public function getAppVersionUrl($platform = 'android')
    {
        $key = static::KEY_APP_VERSION_URL;
        if($platform == "ios"){
            $key = static::KEY_APP_VERSION_URL_IOS;
        }
        return static::getValueByKey($key, '');
    }
    /** App版本更新end */

    /** 还款设置 begin */
    public function getRepayPartRepayOn()
    {
        return static::getValueByKey(static::KEY_REPAY_PART_REPAY_ON, 0);
    }

    public function getRepayPartRepayConfig()
    {
        return static::getValueByKey(static::KEY_REPAY_PART_CONFIG);
    }

    public function getRepayPartMinOverdueDays()
    {
        return static::getValueByKey(static::KEY_REPAY_PART_MIN_OVERDUE_DAYS);
    }

    public function getRepayPartAllOverdueOn()
    {
        return static::getValueByKey(static::KEY_REPAY_PART_ALL_OVERDUE_ON);
    }
    /** 还款设置 end */

}
