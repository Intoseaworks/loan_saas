<?php

namespace Common\Models\Call;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class CallTelephoneTest extends Model {

    use StaticModel;

    const STATUS_WAITING = 0;
    const STATUS_CALLING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILED = 3;

    /**
     * @var string
     */
    protected $table = 'call_telephone_test';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

}
