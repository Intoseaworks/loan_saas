<?php

namespace Risk\Common\Models\RiskData;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\RiskData\HighRiskPhonenumber
 *
 * @property int $id
 * @property string $type 类别 loanapp：用户通讯录存储名击中app名
 * @property string|null $name 名称
 * @property string|null $phonenumber 号码
 * @property string|null $init_dt 首次击中时间
 * @property string|null $last_dt 最后一次击中时间
 * @property int $times_cnt 累计击中次数
 * @property string $created_at 创建时间
 * @property string|null $result_json 结果
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber query()
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber whereInitDt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber whereLastDt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber wherePhonenumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber whereResultJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber whereTimesCnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HighRiskPhonenumber whereType($value)
 * @mixin \Eloquent
 */
class HighRiskPhonenumber extends RiskBaseModel
{
    /** 类型：贷款app */
    const TYPE_LOAN_APP = 'loanapp';
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'high_risk_phonenumber';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];
}
