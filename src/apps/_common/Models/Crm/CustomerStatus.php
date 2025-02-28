<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Crm;

use Common\Models\Coupon\Coupon;
use Common\Traits\Model\StaticModel;
use Common\Models\Crm\CrmWhiteBatch;
use Common\Models\User\User;
use Common\Models\User\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Common\Utils\MerchantHelper;

/**
 * Common\Models\Crm\Customer
 *
 * @property int $id
 * @property int|null $main_user_id 主用户ID
 * @property int|null $clm_level clm等级
 * @property string|null $telephone 手机号
 * @property int|null $telephone_status 手机状态(-1:未检测;1=正常;2=无效;3=停机)
 * @property string|null $telephone_check_time 手机检测时间
 * @property int|null $type 客户类型(1=首贷普通名单;2=首贷白名单;3=首贷一般客户;4=复贷客户)
 * @property string|null $email email
 * @property string|null $fullname 全名
 * @property string|null $birthday 生日
 * @property string|null $id_type 证件类型
 * @property string|null $id_number 证件号码
 * @property string $gender 性别:男,女
 * @property int|null $status 用户状态(
 * 1:未注册;
 * 2:注册未申请;
 * 3:审批中;
 * 4:审批拒绝;
 * 5:放款处理中
 * 6:待还款
 * 7:逾期
 * 8:结清)
 * @property int|null $batch_id 导入批次号(白名单|营销)
 * @property string|null $suggest_time 建议致电时间
 * @property string|null $last_login_time 最后登录时间
 * @property int|null $max_overdue_days 最大逾期天数
 * @property int|null $status_stop_days 当前状态停留时间
 * @property int|null $settle_times 结清次数
 * @property string|null $last_settle_time 最后结清时间
 * @property string|null $status_updated_time 状态变更时间
 * @property string|null $remark 上传时备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Coupon[] $coupons
 * @property-read int|null $coupons_count
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereClmLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereIdType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereLastSettleTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereMainUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereMaxOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereSettleTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereStatusStopDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereStatusUpdatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereSuggestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereTelephoneCheckTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereTelephoneStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomerStatus extends Model {

    use StaticModel;

    const TYPE_MARKETING = 1; //首贷-营销
    const TYPE_WHITELIST = 2; //首贷-白名单
    const TYPE_GENERAL = 3; //首贷-一般用户
    const TYPE_RELOAN = 4; //首贷-复贷
    const TYPE_LIST = [
        self::TYPE_MARKETING => "首贷-营销名单",
        self::TYPE_WHITELIST => "首贷-白名单",
        self::TYPE_GENERAL => "首贷-一般用户",
        self::TYPE_RELOAN => "首贷-复贷",
    ];
    const TYPE_MAP = [
        "首贷-营销名单" => self::TYPE_MARKETING,
        "首贷-白名单" => self::TYPE_WHITELIST,
        "首贷-一般用户" => self::TYPE_GENERAL,
        "首贷-复贷" => self::TYPE_RELOAN,
    ];
    const STATUS_UNREGISTERED = 1;
    const STATUS_NOT_APPLY = 2;
    const STATUS_APPLYING = 9;
    const STATUS_APPROVAL_PROGRESS = 3;
    const STATUS_APPROVAL_REJECT = 4;
    const STATUS_LENDING = 5;
    const STATUS_PAID = 6;
    const STATUS_OVERDUE = 7;
    const STATUS_FINISH = 8;
    const STATUS_CUSTOMER = [
        self::STATUS_UNREGISTERED => "未注册",
        self::STATUS_NOT_APPLY => "注册未申请",
        self::STATUS_APPLYING => "申请中",
        self::STATUS_APPROVAL_PROGRESS => "审批中",
        self::STATUS_APPROVAL_REJECT => "审批拒绝",
        self::STATUS_LENDING => "放款处理中",
        self::STATUS_PAID => "待还款",
        self::STATUS_OVERDUE => "逾期",
        self::STATUS_FINISH => "结清",
    ];

    protected $table = 'crm_customer_status';

    public function crmWhiteBatch($class = CrmWhiteBatch::class) {
        return $this->hasOne($class, 'id', 'batch_id')->orderBy('id', 'desc');
    }

    public function user($class = User::class){
        return $this->hasOne($class, 'id', 'main_user_id')->orderBy('id', 'desc');
    }

    public function customer($class = Customer::class){
        return $this->hasOne($class, 'id', 'customer_id')->orderBy('id', 'desc');
    }

    public function userInfo($class = UserInfo::class){
        return $this->hasOne($class, 'user_id', 'main_user_id')->orderBy('id', 'desc');
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class,'coupon_receive','user_id','coupon_id','main_user_id');
    }

    public function getStatus($customerId, $merchantId = null){
        if($merchantId == null){
            $merchantId = MerchantHelper::getMerchantId();
        }
        return $this->newQuery()->where("customer_id", $customerId)->where("merchant_id", $merchantId)->first();
    }
}
