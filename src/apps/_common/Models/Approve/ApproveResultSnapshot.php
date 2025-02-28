<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-11
 * Time: 16:42
 */

namespace Common\Models\Approve;


use Common\Traits\Model\GlobalScopeModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ApproveResultSnapshot
 *
 * @property int $id
 * @property int $approve_user_pool_id
 * @property string $first_approve_result 初审结果
 * @property string $call_approve_result 电审结果
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Common\Models\Approve\ApproveUserPool $approveUserPool
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereApproveUserPoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereCallApproveResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereFirstApproveResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $result 审批结果
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereResult($value)
 * @property int $approve_type 审批类型 1初审 2电审
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereApproveType($value)
 * @property int|null $approve_pool_id approve_pool表主键
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereApprovePoolId($value)
 * @property int|null $merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveResultSnapshot whereMerchantId($value)
 */
class ApproveResultSnapshot extends Model
{
    use GlobalScopeModel;

    /**
     * 初审
     */
    const TYPE_FIRST_APPROVE = 1;

    /**
     * 电审
     */
    const TYPE_CALL_APPROVE = 2;

    /**
     * @var string
     */
    protected $table = 'approve_result_snapshot';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approveUserPool()
    {
        return $this->belongsTo(ApproveUserPool::class, 'approve_user_pool_id', 'id');
    }
}
