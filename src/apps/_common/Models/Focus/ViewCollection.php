<?php

namespace Common\Models\Focus;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ViewCollection extends Model {

    use StaticModel;

    protected $table = 'view_collection';
    protected $fillable = [];
    protected $hidden = [];
    protected $primaryKey = 'id';
    protected $connection = 'mysql_focus';

}
