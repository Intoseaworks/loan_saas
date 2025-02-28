<?php

namespace Common\Models\Email;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;
use Common\Models\Staff\Staff;

class EmailTpl extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效
    const TYPE_COLLECTION = "催收";
    const TYPE_MARKETING = "营销";
    const TYPE = [
        self::TYPE_COLLECTION,
        self::TYPE_MARKETING
    ];

    /**
     * @var string
     */
    protected $table = 'email_tpl';

    protected static function boot() {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function staff($class = Staff::class) {
        return $this->hasOne($class, 'id', 'admin_id');
    }

    public function isActive() {
        return $this->where("status", self::STATUS_NORMAL);
    }

}
