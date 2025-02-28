<?php

namespace Risk\Common\Models\Business\Collection;

use Risk\Common\Models\Business\BusinessBaseModel;
use Risk\Common\Models\Business\Order\Order;

/**
 * Risk\Common\Models\Business\Collection\CollectionRecord
 *
 * @property int $id
 * @property int $app_id merchant_id
 * @property int|null $collection_id 催收记录ID
 * @property int $order_id 管理员ID
 * @property int $user_id 管理员ID
 * @property string|null $admin_id 管理员ID
 * @property string $fullname 姓名
 * @property string|null $relation 亲戚关系
 * @property string|null $contact 联系值（手机号）
 * @property string|null $promise_paid_time 承诺还款时间
 * @property string|null $remark 备注
 * @property string $dial 联系结果 （正常联系，无法联系...）
 * @property string $progress 催收进度 （承诺还款，无意向...）
 * @property string|null $from_status 催记前案子状态
 * @property string|null $to_status 催记后案子状态
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property int $overdue_days 当前逾期天数
 * @property string $reduction_fee 当前减免金额
 * @property string|null $level 当前催收等级
 * @property string|null $receivable_amount 当前应还金额
 * @property int|null $collection_assign_id 催收分单ID
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereCollectionAssignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereDial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord wherePromisePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereReceivableAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereReductionFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereUserId($value)
 * @mixin \Eloquent
 */
class CollectionRecord extends BusinessBaseModel
{
    const DIAL_NORMAL_CONTACT = 'normal_contact';
    const DIAL_UNABLE_CONTACT = 'unable_contact';
    const PROGRESS_COMMITTED_REPAYMENT = 'committed_repayment';
    const PROGRESS_SELF_INADVERTENTLY_REPAY = 'self_inadvertently_repay';//正常联系
    const PROGRESS_INTENTIONAL_NOTIFICATION = 'intentional_notification';//无法联系

    // 承诺还款
    const PROGRESS_INTENTIONAL_HELP = 'intentional_help';
    // 本人无意还款
    const PROGRESS_UNINTENTIONAL = 'unintentional';
    // 有意告知
    const PROGRESS_TOO_MANY_PROMISES = 'too_many_promises';
    // 有意帮还
    const PROGRESS_SHUT_DOWN_NO = 'shut_down_no';
    // 无意向
    const PROGRESS_SHUTDOWN = 'shutdown';
    // 过多承诺跳票
    const PROGRESS_REFUSAL_TO_ANSWER = 'refusal_to_answer';
    // 停机空号
    const PROGRESS_BUSY_LINE = 'busy_line';
    // 关机
    const PROGRESS_INVALID_SOUND = 'invalid_sound';
    // 拒绝接听
    const PROGRESS_CALL_REMINDER = 'call_reminder';
    // 忙线
    const PROGRESS_TEMPORARY_NOBODY_CONNECTION = 'temporary_nobody_connection';
    // 无效音
    const PROGRESS_REPEATEDLY_UNANSWERED = 'repeatedly_unanswered';
    // 来电提醒
    const PROGRESS_REPEATEDLY_HANG_UP = 'repeatedly_hang_up';
    // 暂时无人接通
    public static $validate = [
        'data' => 'array',
        'data.*.id' => 'required|numeric',   // 业务系统催收记录ID，如记录自增id
        'data.*.order_id' => 'required|numeric',   // 订单ID
        'data.*.fullname' => 'required|string',   // 被催收人姓名
        'data.*.relation' => 'nullable|string',   // 被催收人与借款人关系  父亲:FATHER 母亲:MOTHER  兄弟:BROTHERS  姐妹:SISTERS  儿子:SON  女儿:DAUGHTER  妻子:WIFE  丈夫:HUSBAND  其他:OTHER
        'data.*.contact' => 'required|string',   // 被催收电话
        'data.*.dial' => 'required|string',   // 联系情况  正常联系:normal_contact  无法联系:unable_contact
        'data.*.progress' => 'required|string',   // 催收进度  承诺还款:committed_repayment  本人无意还款:self_inadvertently_repay  有意告知:intentional_notification  有意帮还:intentional_help  无意向:unintentional  过多承诺跳票:too_many_promises  停机空号:shut_down_no  关机:shutdown  拒绝接听:refusal_to_answer  忙线:busy_line  无效音:invalid_sound  来电提醒:call_reminder  暂时无人接通:temporary_nobody_connection  反复无人接听:repeatedly_unanswered  反复接听挂断:repeatedly_hang_up
        'data.*.created_at' => 'required|date_format:Y-m-d H:i:s', // 催收时间
        'data.*.overdue_days' => 'required|numeric',   // 催收时逾期天数

        'data.*.promise_paid_time' => 'nullable|date',   // 承诺还款时间 合法时间格式。如：Y-m-d H:i:s 或 Y-m-d 等
        'data.*.reduction_fee' => 'numeric',   // 当前减免金额
        'data.*.receivable_amount' => 'numeric',   // 当前应还金额
        'data.*.remark' => 'string',   // 备注
        'data.*.admin_id' => 'nullable',   // 催收员ID，字符串
        'data.*.updated_at' => 'nullable|date_format:Y-m-d H:i:s', // 催收时间
    ];
    // 反复无人接听
    public $timestamps = false;
    // 反复接听挂断
    /**
     * @var string
     */
    protected $table = 'data_collection_record';
    /**
     * @var array
     */
    protected $hidden = [];
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'collection_id',
        'order_id',
        'user_id',
        'admin_id',
        'fullname',
        'relation',
        'contact',
        'promise_paid_time',
        'remark',
        'dial',
        'progress',
        'from_status',
        'to_status',
        'created_at',
        'updated_at',
        'overdue_days',
        'reduction_fee',
        'level',
        'receivable_amount',
        'collection_assign_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'order_id');
    }
}
