<?php

namespace Common\Models\Marketing;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class GsmRuntime extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'marketing_gsm_runtime';

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
