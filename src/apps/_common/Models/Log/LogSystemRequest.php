<?php

namespace Common\Models\Log;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class LogSystemRequest extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    public $table = 'log_system_request';

}
