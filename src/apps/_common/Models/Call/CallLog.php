<?php

namespace Common\Models\Call;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'call_log';

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
