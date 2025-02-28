<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-11
 * Time: 10:25
 */

namespace Common\Models\Approve;


use Common\Traits\Model\GlobalScopeModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ApproveAssignLog
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $admin_id 分单人员id
 * @property string|null $fullname
 * @property string|null $telephone
 * @property int $from_order_status 订单修改前状态
 * @property int $to_order_status 订单修改后状态
 * @property int $approve_status 审批状态默认0审批中1审批完成2分单取消
 * @property int $merchant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereApproveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereFromOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereToOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $approve_pool_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereApprovePoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveAssignLog whereMerchantId($value)
 */
class ApproveAssignLog extends Model
{
    use GlobalScopeModel;

    /**
     * 审批状态 审批中
     */
    const STATUS_CHECKING = 0;
    /**
     * 审批状态 审批完成
     */
    const STATUS_DONE = 1;
    /**
     * 审批状态 取消
     */
    const STATUS_CANCEL = 2;

    protected $table = 'approve_assign_log';

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
