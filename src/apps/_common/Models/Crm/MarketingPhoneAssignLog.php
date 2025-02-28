<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Crm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class MarketingPhoneAssignLog extends Model {

    use StaticModel;

    protected $table = 'crm_marketing_phone_assign_log';
}
