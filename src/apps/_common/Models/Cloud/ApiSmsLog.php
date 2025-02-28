<?php

namespace Common\Models\Cloud;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ApiSmsLog extends Model {

    use StaticModel;

    protected $table = 'api_sms_log';

}
