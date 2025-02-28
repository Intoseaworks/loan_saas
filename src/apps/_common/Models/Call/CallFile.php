<?php

namespace Common\Models\Call;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class CallFile extends Model {

    use StaticModel;
    
    const HTTP_HOST = "https://record.e-perash.com/";

    /**
     * @var string
     */
    protected $table = 'call_file';

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
