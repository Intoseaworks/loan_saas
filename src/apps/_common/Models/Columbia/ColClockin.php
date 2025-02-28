<?php

/**
 * 
 * 
 */

namespace Common\Models\Columbia;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ColClockin extends Model {

    use StaticModel;

    const STATUS_APPROVE_REFUSE = 1;
    const STATUS_APPROVE_PASS = 1;
    const STATUS_APPROVE_WAIT = 0;
    const TIME_FRAME = [
        "1" => [8, 7],
        "2" => [12],
        "3" => [18],
        "4" => [21]
    ];
    const TIME_FRAME_TITLE = [
        "1" => "7:00-9:00",
        "2" => "12:00-13:00",
        "3" => "18:00-19:00",
        "4" => "21:00-22:00"
    ];

    const APPROVE_REJECT_RESION = [
        "1. Address matching",
        "2. Wearing a mask",
        "3. Limited gathering",
        "4. Physical distance",
    ];
    protected $table = 'col_clockin';

}
