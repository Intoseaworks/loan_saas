<?php

namespace Common\Services\Rbac\Models;

use Common\Services\Rbac\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasRoles;

    /**
     * laravel-permission插件需要
     *
     * @var string
     */
    public $guard_name = 'admin';
}
