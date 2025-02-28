<?php

namespace Risk\Common\Models\HighMark;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\HighMark\HmEmploymentDetail
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string|null $report_id REPORT-ID
 * @property string|null $acct_type ACCT-TYPE
 * @property string|null $date_reported DATE-REPORTED
 * @property string|null $occupation OCCUPATION
 * @property string|null $income INCOME
 * @property string|null $income_frequency INCOME-FREQUENCY
 * @property string|null $income_indicator INCOME-INDICATOR
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereAcctType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereDateReported($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereIncomeFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereIncomeIndicator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereOccupation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmEmploymentDetail whereUserId($value)
 * @mixin \Eloquent
 */
class HmEmploymentDetail extends RiskBaseModel
{
    public $table = 'hm_employment_detail';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [];
    }
}
