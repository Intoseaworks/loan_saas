<?php

namespace Common\Models\Third;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Third\ThirdRzpRepayReport
 *
 * @property int $id
 * @property string $Settle_Date
 * @property string|null $Credit
 * @property string|null $Principal
 * @property string|null $Interest
 * @property string|null $Penalty
 * @property string|null $Penalty_GST
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport whereCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport whereInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport wherePenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport wherePenaltyGST($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport whereSettleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdRzpRepayReport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ThirdRzpRepayReport extends Model {

    use StaticModel;

    protected $table = 'third_rzp_repay_report';
    protected $fillable = [];
    protected $guarded = [];

    protected static function boot() {
        parent::boot();
    }

    public function textRules() {
        return [];
    }

}
