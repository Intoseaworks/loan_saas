<?php

namespace Common\Models\Call;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class CallAutoAssign extends Model {

    use StaticModel;

    protected $table = 'call_auto_assign';

    protected static function boot() {
        parent::boot();

        static::setMerchantIdBootScope();
    }

}
