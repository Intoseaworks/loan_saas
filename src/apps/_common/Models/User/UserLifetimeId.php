<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class UserLifetimeId extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'user_lifetime_id';


}
