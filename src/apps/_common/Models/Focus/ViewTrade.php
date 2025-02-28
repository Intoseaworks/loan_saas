<?php

namespace Common\Models\Focus;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ViewTrade extends Model {

    use StaticModel;

    protected $table = 'view_trade';
    protected $fillable = [];
    protected $hidden = [];
    protected $primaryKey = 'id';
    protected $connection = 'mysql_focus';

}
