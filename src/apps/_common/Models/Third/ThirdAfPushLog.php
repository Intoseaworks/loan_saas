<?php

namespace Common\Models\Third;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ThirdAfPushLog extends Model {

    use StaticModel;

    protected $table = 'third_af_push_log';
    protected $fillable = [];
    protected $guarded = [];

}
