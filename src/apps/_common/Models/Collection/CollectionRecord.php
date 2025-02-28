<?php

namespace Common\Models\Collection;

use Common\Models\Order\Order;
use Common\Models\Staff\Staff;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Common\Models\Collection\CollectionRecord
 *
 * @property int $id
 * @property int $collection_id 催收记录ID
 * @property int $order_id 管理员ID
 * @property int $admin_id 管理员ID
 * @property string $fullname 姓名
 * @property string|null $relation 亲戚关系
 * @property string|null $contact 联系值（手机号）
 * @property string|null $promise_paid_time 承诺还款时间
 * @property string $remark 备注
 * @property string $dial 联系结果 （正常联系，无法联系...）
 * @property string $progress 催收进度 （承诺还款，无意向...）
 * @property string $from_status 催记前案子状态
 * @property string $to_status 催记后案子状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereDial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord wherePromisePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $overdue_days 当前逾期天数
 * @property float $reduction_fee 当前减免金额
 * @property string $level 当前催收等级
 * @property float $receivable_amount 当前应还金额
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereReceivableAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereReductionFee($value)
 * @property int $user_id 管理员ID
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereUserId($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord whereMerchantId($value)
 * @property int $collection_assign_id 催收分单ID
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereCollectionAssignId($value)
 */
class CollectionRecord extends Model {

    use StaticModel;
    const CONTACT_METHOD_PHONE = 1;
    const CONTACT_METHOD_SMS = 2;
    
    const CONTACT_METHOD = [
        self::CONTACT_METHOD_PHONE => "Make a telephone call",
        self::CONTACT_METHOD_SMS => "Send SMS",
    ];

    # 承诺还款时间段
    const TIME_SLOT = [
        '9:01-10:00',
        '10:01-11:00',
        '11:01-12:00',
        '12:01-13:00',
        '13:01-14:00',
        '14:01-15:00',
        '15:01-16:00',
        '16:01-17:00',
        '17:01-18:00',
        '18:01-19:00',
        '19:01-20:00',
        '20:01-21:00',
        '21:01-22:00',
        '22:01-23:00'
    ];

    /**
     * @var string
     */
    protected $table = 'collection_record';

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

    protected static function boot() {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules() {
        return [];
    }

    public function staff($class = Staff::class) {
        return $this->hasOne($class, 'id', 'admin_id');
    }

    public function order($class = Order::class) {
        return $this->hasOne($class, 'id', 'order_id');
    }

    public function getFirstPtpTime($adminId, $collectionId) {
        $query = $this->newQuery()->where("admin_id", "=", $adminId)->whereCollectionId($collectionId)->whereIn("progress", array_keys(\Admin\Models\Collection\Collection::PROGRESS_COMMITTED_REPAYMENT));
        $query->select(DB::raw("min(created_at) as created_at"));
        return isset($query->get()->first()->created_at) ? $query->get()->first()->created_at : null;
    }

}
