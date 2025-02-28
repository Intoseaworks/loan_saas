<?php

namespace Common\Models\Clm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Clm\ClmRule
 *
 * @property int $id
 * @property string|null $rule_type
 * @property string|null $value
 * @property string|null $level_opt
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereLevelOpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereRuleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmRule whereValue($value)
 * @mixin \Eloquent
 */
class ClmRule extends Model {

    use StaticModel;
    
    const INCOME = [
        "" => [],
        
    ];

    /**
     * @var string
     */
    protected $table = 'clm_rule';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

}
