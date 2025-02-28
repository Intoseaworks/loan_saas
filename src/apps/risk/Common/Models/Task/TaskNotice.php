<?php

namespace Risk\Common\Models\Task;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Task\TaskNotice
 *
 * @property int $id 机审通知表主键
 * @property int $task_id
 * @property string $notice_url 通知地址
 * @property string|null $notice_info 通知信息
 * @property string|null $response_info 响应信息
 * @property int $status 通知状态
 * @property string $remark 备注
 * @property \Illuminate\Support\Carbon $created_at 更新时间
 * @property \Illuminate\Support\Carbon $updated_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereNoticeInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereNoticeUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereResponseInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNotice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TaskNotice extends RiskBaseModel
{
    /**
     * 状态 - 未通知
     */
    const STATUS_NO_NOTICE = 0;
    /**
     * 状态 - 响应成功
     */
    const STATUS_RESPONSE_SUCCESS = 1;
    /**
     * 状态 - 响应失败
     */
    const STATUS_RESPONSE_FAILED = 2;
    /**
     * 通知次数限制
     */
    const NOTICE_NUM_LIMIT = 6;
    /**
     * 响应类容 - 成功标识
     */
    const RESPONSE_SUCCESS_FLAG = 'SUCCESS';
    public $table = 'task_notice';
    protected $guarded = [];

    /**
     * 添加通知记录
     * @param array $attribute
     * @return mixed
     */
    public function add(array $attribute)
    {
        return static::create($attribute);
    }

    /**
     * 更新信息
     * @param $id
     * @param array $update
     * @return $this
     */
    public function updateInfo($id, array $update)
    {
        $update['update_time'] = time();
        $this->where('id', $id)->update($update);
        return $this;
    }

    /**
     * 获取通知次数
     * @param $taskId
     * @return mixed
     */
    public function getCountByTask($taskId)
    {
        return $this->where('task_id', $taskId)->count();
    }

    /**
     * 判断是否存在 已成功的通知
     * @param $taskId
     * @return bool
     */
    public function hasSuccessNotice($taskId)
    {
        $where = [
            'task_id' => $taskId,
            'status' => self::STATUS_RESPONSE_SUCCESS,
        ];
        return $this->newQuery()->where($where)->exists();
    }
}
