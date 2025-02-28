<?php

namespace Common\Models\Email;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'email_log';
    
    protected $fillable = ["email_user_id","user_id","order_id","tpl_id","subject", "content", "result"];

    protected static function boot() {
        parent::boot();

        static::setMerchantIdBootScope();
    }

}
