<?php

namespace Common\Models\Cloud;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ApiUser extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效

    protected $table = 'api_user';

}
