<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionDeduction
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $order_id 用户id
 * @property int $collection_id 用户id
 * @property int $from_admin_id 当前案件归属人
 * @property int $deduction_admin_id 操作人
 * @property float $overdue_fee 减免时逾期息费
 * @property float $deduction 减免金额
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereDeductionAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereFromAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereOverdueFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @property int $repayment_plan_id 还款计划表id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction whereRepaymentPlanId($value)
 * @property int $collection_assign_id 催收分单ID
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionDeduction whereCollectionAssignId($value)
 */
class CollectionDeduction extends Model
{
    use StaticModel;

    /** 状态：正常 */
    const STATUS_NORMAL = 1;
    /** 状态：被覆盖 */
    const STATUS_COVER = -1;
    /** 状态 */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_COVER => '被覆盖',
    ];

    /**
     * @var string
     */
    protected $table = 'collection_deduction';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];
    /**
     * @var bool
     */
    //public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

}
