<?php

namespace Common\Models\Order;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;


/**
 * Common\Models\Order\OrderRollover
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRollover newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRollover newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRollover orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRollover query()
 * @mixin \Eloquent
 */
class OrderRollover extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'order_rollover';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }
    
}
