<?php

namespace Common\Models\Common;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Common\Date
 *
 * @property string $date 日期
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Date newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Date newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Date orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Date query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Date whereDate($value)
 * @mixin \Eloquent
 */
class AppNotice extends Model {

    use StaticModel;

    protected $table = 'app_notice';
    protected $fillable = [];
    protected $hidden = [];

}
