<?php

namespace Common\Models\Cloud;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ApiAccessLog extends Model {

    use StaticModel;

    protected $table = 'api_access_log';

}
