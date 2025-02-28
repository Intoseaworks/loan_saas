<?php

namespace Risk\Common\Models\Task;

use Common\Models\Merchant\Merchant;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\RiskBaseModel;
use Risk\Common\Models\SystemApprove\SystemApproveRecord;

/**
 * Risk\Common\Models\Task\Task
 *
 * @property int $id
 * @property int $app_id
 * @property string $task_no 机审任务编号
 * @property int $user_id 用户id
 * @property string $order_no 订单no
 * @property string $status 状态
 * @property string $result 结果
 * @property string|null $hit_rule_code
 * @property string|null $task_desc 描述
 * @property string|null $notice_url 通知地址
 * @property int|null $account_id 关联 risk_associated_record id
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property-read Merchant $app
 * @property-read SystemApproveRecord|null $lastRejectSystemApproveRecord
 * @property-read SystemApproveRecord|null $lastSystemApproveRecord
 * @property-read \Illuminate\Database\Eloquent\Collection|SystemApproveRecord[] $systemApproveRecord
 * @property-read int|null $system_approve_record_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Risk\Common\Models\Task\TaskData[] $taskData
 * @property-read int|null $task_data_count
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereHitRuleCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereNoticeUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTaskDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTaskNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUserId($value)
 * @mixin \Eloquent
 */
class Task extends RiskBaseModel
{
    /** 状态：创建 等待数据完善 */
    const STATUS_CREATE = 'CREATE';
    /** 状态：等待机审 */
    const STATUS_WAITING = 'WAITING';
    /** 状态：机审中 */
    const STATUS_PROCESSING = 'PROCESSING';
    /** 状态：任务完结 */
    const STATUS_FINISH = 'FINISH';
    /** 状态：异常 */
    const STATUS_EXCEPTION = 'EXCEPTION';


    /** 结果：空 */
    const RESULT_NULL = 'NA';
    /** 结果：通过 */
    const RESULT_PASS = 'PASS';
    /** 结果：拒绝 */
    const RESULT_REJECT = 'REJECT';

    protected $table = 'task';
    protected $fillable = [];
    protected $guarded = [];

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null|static
     */
    public static function getById($id)
    {
        $query = static::query()->where('id', $id);

        return $query->first();
    }

    public static function getByOrderNo($orderNo)
    {
        if (!$appId = MerchantHelper::getMerchantId()) {
            throw new \Exception('app id 未设置');
        }

        $where = [
            'app_id' => $appId,
            'order_no' => $orderNo,
        ];

        return self::query()->where($where)
            ->first();
    }

    public static function add($userId, $orderId, $noticeUrl = null)
    {
        if (!$appId = MerchantHelper::getMerchantId()) {
            throw new \Exception('app id 未设置');
        }

        $data = [
            'app_id' => $appId,
            'order_no' => $orderId,
            'user_id' => $userId,
            'task_no' => self::generateNo(),
            'status' => self::STATUS_CREATE,
            'result' => self::RESULT_NULL,
            'notice_url' => $noticeUrl,
        ];

        return self::create($data);
    }

    public static function generateNo($prefix = null)
    {
        $prefix = $prefix ?: (string)mt_rand(1, 99999);
        $no = 'TASK_' . strtoupper(substr(md5(uniqid($prefix)), 8, 16));
        if ((new Task())->getByTaskNo($no)) {
            return self::generateNo($prefix);
        }

        return $no;
    }

    /**
     * @param $no
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null|static
     */
    public static function getByTaskNo($no)
    {
        return self::query()->where('task_no', $no)->first();
    }

    public static function getWaitSystemApprove()
    {
        return self::query()->where('status', self::STATUS_WAITING)->get();
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function toWaiting()
    {
        $this->status = self::STATUS_WAITING;
        return $this->save();
    }

    public function toProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
        return $this->save();
    }

    public function toException($desc = '')
    {
        $this->status = self::STATUS_EXCEPTION;

        if ($desc) {
            $this->task_desc = $desc;
        }

        return $this->save();
    }

    public function toFinish($result)
    {
        $this->status = self::STATUS_FINISH;
        $this->result = $result;

        return $this->save();
    }

    /**
     * 外部数据项是否完善
     * @return bool
     */
    public function isFinishDataSend()
    {
        return !(bool)$this->getTaskDataLacking();
    }

    /**
     * 获取待同步的外部数据项
     * @return mixed
     */
    public function getTaskDataLacking()
    {
        return TaskData::getTaskDataOuterLacking($this->id);
    }

    /**
     * 内部数据项是否完善
     * @return bool
     */
    public function isFinishDataInner()
    {
        return !(bool)$this->getTaskDataInnerLacking();
    }

    /**
     * 获取待执行的内部数据项
     * @return mixed
     */
    public function getTaskDataInnerLacking()
    {
        return TaskData::getTaskDataInnerLacking($this->id);
    }

    public function updateAccountId($accountId)
    {
        $this->account_id = $accountId;
        return $this->save();
    }

    /**
     * 关联task_data
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskData()
    {
        return $this->hasMany(TaskData::class, 'task_id', 'id');
    }

    /**
     * 关联app
     */
    public function app()
    {
        return $this->belongsTo(Merchant::class, 'app_id', 'id');
    }

    public function lastSystemApproveRecord()
    {
        return $this->hasOne(SystemApproveRecord::class, 'task_id', 'id')
            ->orderByDesc('id');
    }

    public function lastRejectSystemApproveRecord()
    {
        return $this->hasOne(SystemApproveRecord::class, 'task_id', 'id')
            ->where('result', SystemApproveRecord::RESULT_REJECT)
            ->orderByDesc('id');
    }

    public function systemApproveRecord()
    {
        return $this->hasMany(SystemApproveRecord::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
