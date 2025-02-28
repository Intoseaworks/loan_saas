<?php

/**
 * 
 * 
 */

namespace Common\Models\Columbia;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ColClockinApprove extends Model {

    use StaticModel;

    const STATUS_APPROVE_FAILED = 2;
    const STATUS_APPROVE_PASS = 1;
    const STATUS_APPROVE_WAIT = 0;

    protected $table = 'col_clockin_approve';

}
