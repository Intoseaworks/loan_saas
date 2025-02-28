<?php

/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-10
 * Time: 14:33
 */

namespace Common\Models\Approve;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ApproveCallLog extends Model {

    use StaticModel;

    const TV1 = [
        "Customer Phone No.",
        "Company Tele",
        "Phone No. of Immediate",
        "Family Members",
        "Phone No. of other contact",
        "Phone No. of colleague",
        "Family Tele",
        "Others",
    ];
    const TV2 = [
        "No Answer",
        "Invalid Number",
        "Found Abnormal",
        "No Need to Dial",
        "Normal",
        "Others",
    ];

    /**
     * @var string
     */
    protected $table = 'approve_call_log';

}
