<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-21
 * Time: 19:32
 */

namespace Common\Models\Approve;


use Common\Traits\Model\GlobalScopeModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ApproveFailRecord
 *
 * @property int $id
 * @property int $merchant_id
 * @property int $order_id
 * @property int $user_id
 * @property int $type 1 初审 2电审
 * @property int $option_type 初审 1 approve filed , 2 Suspected fraud/disqualified
 * @property int $option_value 选择项值
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereOptionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereOptionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveFailRecord whereUserId($value)
 * @mixin \Eloquent
 */
class ApproveFailRecord extends Model
{
    use GlobalScopeModel;

    /**
     * 初审
     *
     * @var integer
     */
    const TYPE_MANUAL_APPROVE = 1;

    /**
     * 电审
     *
     * @var integer
     */
    const TYPE_CALL_APPROVE = 2;

    /**
     * 初审 approve filed
     *
     * @var integer
     */
    const OPTION_TYPE_FAILED = 1;

    /**
     * 初审  Suspected fraud/disqualified
     *
     * @var integer
     */
    const OPTION_TYPE_SUSPECTED = 2;

    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var string
     */
    protected $table = 'approve_fail_record';

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
