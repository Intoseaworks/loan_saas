<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Common\Models\Order\Order;
use Common\Models\Staff\Staff;
use Common\Models\User\User;
use Common\Models\Collection\Collection;
use Illuminate\Database\Eloquent\Model;

class CollectionDeductionApply extends Model {

    use StaticModel;

    /** 状态：申请 */
    const STATUS_APPLY = 1;

    /** 状态：拒绝 */
    const STATUS_REJECT = 2;

    /** 状态：通过 */
    const STATUS_PASS = 3;

    /** 状态 */
    const STATUS = [
        self::STATUS_APPLY => '申请',
        self::STATUS_REJECT => '被驳回',
        self::STATUS_PASS => '通过',
    ];
    const SETTLE_YES = 0;
    const SETILE_NO = 1;
    const SETTLE = [
        self::SETTLE_YES => '一律结清',
        self::SETILE_NO => '按金额处理',
    ];

    /**
     * @var string
     */
    protected $table = 'collection_deduction_apply';

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

    public function collection($class = Collection::class) {
        return $this->hasOne($class, 'id', 'collection_id')->orderBy('id', 'desc');
    }

    public function order($class = Order::class) {
        return $this->hasOne($class, 'id', 'order_id')->orderBy('id', 'desc');
    }

    public function operator($class = Staff::class) {
        return $this->hasOne($class, 'id', 'admin_id')->orderBy('id', 'desc');
    }

    public function auditer($class = Staff::class) {
        return $this->hasOne($class, 'id', 'approval_admin_id')->orderBy('id', 'desc');
    }

    public function user() {
        return User::model()->getOne($this->order->user_id);
    }

}
