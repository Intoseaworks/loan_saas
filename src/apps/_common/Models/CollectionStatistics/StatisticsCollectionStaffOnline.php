<?php

namespace Common\Models\CollectionStatistics;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;


class StatisticsCollectionStaffOnline extends Model
{
    use StaticModel;

    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'statistics_collection_staff_online';

    protected $fillable = [];
    protected $guarded = [];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

}
