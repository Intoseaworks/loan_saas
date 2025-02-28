<?php

namespace Risk\Common\Models\CreditReport;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\CreditReport\ExperianBaseInfo
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianBaseInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianBaseInfo newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianBaseInfo query()
 * @mixin \Eloquent
 */
class ExperianBaseInfo extends RiskBaseModel
{

    public $timestamps = false;
    protected $table = 'experian_base_info';
    protected $fillable = [];
    protected $guarded = [];

    public function textRules()
    {
        return [];
    }
}
