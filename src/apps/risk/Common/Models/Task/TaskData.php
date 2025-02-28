<?php

namespace Risk\Common\Models\Task;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Risk\Common\Models\Business\BankCard\BankCard;
use Risk\Common\Models\Business\Collection\CollectionRecord;
use Risk\Common\Models\Business\Order\ManualApproveLog;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\Order\OrderDetail;
use Risk\Common\Models\Business\Order\RepaymentPlan;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Business\User\UserAuth;
use Risk\Common\Models\Business\User\UserContact;
use Risk\Common\Models\Business\User\UserInfo;
use Risk\Common\Models\Business\User\UserThirdData;
use Risk\Common\Models\Business\User\UserWork;
use Risk\Common\Models\Business\UserData\UserApplication;
use Risk\Common\Models\Business\UserData\UserContactsTelephone;
use Risk\Common\Models\Business\UserData\UserPhoneHardware;
use Risk\Common\Models\Business\UserData\UserPhonePhoto;
use Risk\Common\Models\Business\UserData\UserPosition;
use Risk\Common\Models\Business\UserData\UserSms;
use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Task\TaskData
 *
 * @property int $id
 * @property int $task_id 机审任务id
 * @property string $type 类型
 * @property int $status 状态
 * @property int $is_inner 是否内部数据项
 * @property string|null $expire_by 数据项准备过期时间(不为null的情况下，若过了过期时间还未完成当前项，则忽略)
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @method static Builder|TaskData newModelQuery()
 * @method static Builder|TaskData newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static Builder|TaskData query()
 * @method static Builder|TaskData whereCreatedAt($value)
 * @method static Builder|TaskData whereExpireBy($value)
 * @method static Builder|TaskData whereId($value)
 * @method static Builder|TaskData whereIsInner($value)
 * @method static Builder|TaskData whereStatus($value)
 * @method static Builder|TaskData whereTaskId($value)
 * @method static Builder|TaskData whereType($value)
 * @method static Builder|TaskData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TaskData extends RiskBaseModel
{
    /************************** 数据项类型:外部(业务) ***************************/
    // 银行卡
    const TYPE_BANK_CARD = 'BANK_CARD';
    // 催收记录
    const TYPE_COLLECTION_RECORD = 'COLLECTION_RECORD';
    // 订单
    const TYPE_ORDER = 'ORDER';
    // 订单详情
    const TYPE_ORDER_DETAIL = 'ORDER_DETAIL';
    // 还款计划
    const TYPE_REPAYMENT_PLAN = 'REPAYMENT_PLAN';
    // 用户
    const TYPE_USER = 'USER';
    // 用户认证
    const TYPE_USER_AUTH = 'USER_AUTH';
    // 紧急联系人
    const TYPE_USER_CONTACT = 'USER_CONTACT';
    // 用户信息
    const TYPE_USER_INFO = 'USER_INFO';
    // 第三方数据表
    const TYPE_USER_THIRD_DATA = 'USER_THIRD_DATA';
    // 用户工作信息
    const TYPE_USER_WORK = 'USER_WORK';
    // 应用列表
    const TYPE_USER_APPLICATION = 'USER_APPLICATION';
    // 通讯录
    const TYPE_USER_CONTACTS_TELEPHONE = 'USER_CONTACTS_TELEPHONE';
    // 硬件信息
    const TYPE_USER_PHONE_HARDWARE = 'USER_PHONE_HARDWARE';
    // 相册信息
    const TYPE_USER_PHONE_PHOTO = 'USER_PHONE_PHOTO';
    // 位置信息
    const TYPE_USER_POSITION = 'USER_POSITION';
    // 短信记录
    const TYPE_USER_SMS = 'USER_SMS';
    // 人审记录表
    const TYPE_MANUAL_APPROVE_LOG = 'MANUAL_APPROVE_LOG';

    const TYPE = [
        // outer
        self::TYPE_BANK_CARD => 'bank card info',
        self::TYPE_COLLECTION_RECORD => 'collection record',
        self::TYPE_ORDER => 'order info',
        self::TYPE_ORDER_DETAIL => 'order detail',
        self::TYPE_REPAYMENT_PLAN => 'repayment plan',
        self::TYPE_USER => 'user info',
        self::TYPE_USER_AUTH => 'user auth',
        self::TYPE_USER_CONTACT => 'user contact',
        self::TYPE_USER_INFO => 'user info',
        self::TYPE_USER_THIRD_DATA => 'user third data',
        self::TYPE_USER_WORK => 'user work info',
        self::TYPE_USER_APPLICATION => 'user application',
        self::TYPE_USER_CONTACTS_TELEPHONE => 'user contacts telephone',
        self::TYPE_USER_PHONE_HARDWARE => 'user phone hardware',
        self::TYPE_USER_PHONE_PHOTO => 'user phone photo',
        self::TYPE_USER_POSITION => 'user position',
        self::TYPE_USER_SMS => 'user sms',
        self::TYPE_MANUAL_APPROVE_LOG => 'manual approve log',
    ];

    /** 外部(业务)数据项枚举 */
    const OUTER_TYPE = [
        self::TYPE_BANK_CARD,
        self::TYPE_COLLECTION_RECORD,
        self::TYPE_ORDER,
        self::TYPE_ORDER_DETAIL,
        self::TYPE_REPAYMENT_PLAN,
        self::TYPE_USER,
        self::TYPE_USER_AUTH,
        self::TYPE_USER_CONTACT,
        self::TYPE_USER_INFO,
        self::TYPE_USER_THIRD_DATA,
        self::TYPE_USER_WORK,
        self::TYPE_USER_APPLICATION,
        self::TYPE_USER_CONTACTS_TELEPHONE,
        self::TYPE_USER_PHONE_HARDWARE,
        self::TYPE_USER_PHONE_PHOTO,
        self::TYPE_USER_POSITION,
        self::TYPE_USER_SMS,
        self::TYPE_MANUAL_APPROVE_LOG,
    ];

    /**
     * 外部数据项 表单验证 时对应的 model class
     */
    const TYPE_MODEL_CLASS = [
        self::TYPE_BANK_CARD => BankCard::class,
        self::TYPE_COLLECTION_RECORD => CollectionRecord::class,
        self::TYPE_ORDER => Order::class,
        self::TYPE_ORDER_DETAIL => OrderDetail::class,
        self::TYPE_REPAYMENT_PLAN => RepaymentPlan::class,
        self::TYPE_USER => User::class,
        self::TYPE_USER_AUTH => UserAuth::class,
        self::TYPE_USER_CONTACT => UserContact::class,
        self::TYPE_USER_INFO => UserInfo::class,
        self::TYPE_USER_THIRD_DATA => UserThirdData::class,
        self::TYPE_USER_WORK => UserWork::class,
        self::TYPE_USER_APPLICATION => UserApplication::class,
        self::TYPE_USER_CONTACTS_TELEPHONE => UserContactsTelephone::class,
        self::TYPE_USER_PHONE_HARDWARE => UserPhoneHardware::class,
        self::TYPE_USER_PHONE_PHOTO => UserPhonePhoto::class,
        self::TYPE_USER_POSITION => UserPosition::class,
        self::TYPE_USER_SMS => UserSms::class,
        self::TYPE_MANUAL_APPROVE_LOG => ManualApproveLog::class,
    ];

    /**
     * 必填的数据项
     */
    const NECESSARY_TYPE = [
        // outer
        self::TYPE_BANK_CARD,
        self::TYPE_COLLECTION_RECORD,
        self::TYPE_ORDER,
        self::TYPE_ORDER_DETAIL,
        self::TYPE_REPAYMENT_PLAN,
        self::TYPE_USER,
        self::TYPE_USER_AUTH,
        self::TYPE_USER_CONTACT,
        self::TYPE_USER_INFO,
        self::TYPE_USER_THIRD_DATA,
        self::TYPE_USER_WORK,
        self::TYPE_USER_APPLICATION,
        self::TYPE_USER_CONTACTS_TELEPHONE,
        self::TYPE_USER_PHONE_HARDWARE,
        self::TYPE_USER_PHONE_PHOTO,
        self::TYPE_USER_POSITION,
        self::TYPE_USER_SMS,
    ];

    /** 状态：创建 */
    const STATUS_NULL = 0;
    /** 状态：完成 */
    const STATUS_FINISH = 1;

    /** 是否内部数据项：是 */
    const IS_INNER_TRUE = 1;
    /** 是否内部数据项：否 */
    const IS_INNER_FALSE = 0;

    protected $table = 'task_data';
    protected $fillable = [];
    protected $guarded = [];

    public static function initTaskData($taskId)
    {
        return DB::connection((new static())->getConnectionName())->transaction(function () use ($taskId) {
            foreach (self::NECESSARY_TYPE as $type) {
                self::firstOrcreate([
                    'task_id' => $taskId,
                    'type' => $type,
                ], [
                    'status' => self::STATUS_NULL,
                    'is_inner' => self::IS_INNER_FALSE,
                ]);
            }

            return true;
        });
    }

    /**
     * 根据type获取对应数据项的等待超时时间
     * 设置为null的表示没有等待超时时间
     * 超过过期时间还是没有准备好的话，忽略该数据项
     * @param $type
     * @return false|string|null
     */
    public static function getTypeExpireBy($type)
    {
        return date('Y-m-d H:i:s', strtotime('+30 min'));
    }

    /**
     * 获取待完善的外部(业务)数据项
     * @param $taskId
     * @return array
     */
    public static function getTaskDataOuterLacking($taskId)
    {
        $required = TaskData::query()->where('task_id', $taskId)
            ->where('status', '!=', TaskData::STATUS_FINISH)
            ->where('is_inner', '!=', TaskData::IS_INNER_TRUE)
            ->pluck('type')
            ->toArray();

        return $required;
    }

    /**
     * 获取待完善的内部数据项
     * @param $taskId
     * @return array
     */
    public static function getTaskDataInnerLacking($taskId)
    {
        $required = TaskData::query()->where('task_id', $taskId)
            ->where('status', '!=', TaskData::STATUS_FINISH)
            ->where('is_inner', TaskData::IS_INNER_TRUE)
            ->where(function (Builder $query) {
                $query->whereNull('expire_by')
                    ->orWhere('expire_by', '>=', date('Y-m-d H:i:s'));
            })
            ->pluck('type')
            ->toArray();

        return $required;
    }
}
