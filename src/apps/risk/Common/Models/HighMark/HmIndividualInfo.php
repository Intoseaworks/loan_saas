<?php

namespace Risk\Common\Models\HighMark;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\HighMark\HmIndividualInfo
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id 用户ID
 * @property int $relate_id
 * @property string|null $report_id REPORT-ID
 * @property string|null $date_of_request DATE-OF-REQUEST
 * @property string|null $date_of_issue DATE-OF-ISSUE
 * @property string|null $batch_id BATCH-ID
 * @property string|null $status STATUS
 * @property string|null $name NAME
 * @property string|null $father FATHER
 * @property string|null $dob DOB
 * @property string|null $pan PAN
 * @property string|null $email_1 EMAIL-1
 * @property string|null $address_1 ADDRESS-1
 * @property string|null $address_2 ADDRESS-2
 * @property string|null $address_3 ADDRESS-3
 * @property string|null $phone_1 PHONE-1
 * @property string|null $phone_2 PHONE-2
 * @property string|null $phone_3 PHONE-3
 * @property string|null $occupation OCCUPATION
 * @property string|null $income_frequency INCOME-FREQUENCY
 * @property string|null $income_indicator INCOME-INDICATOR
 * @property string|null $score_type SCORE-TYPE
 * @property string|null $score_value SCORE-VALUE
 * @property string|null $score_comments CORE-COMMENTS
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereAddress3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereDateOfIssue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereDateOfRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereEmail1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereFather($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereIncomeFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereIncomeIndicator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereOccupation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo wherePan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo wherePhone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo wherePhone3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereScoreComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereScoreType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereScoreValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmIndividualInfo whereUserId($value)
 * @mixin \Eloquent
 */
class HmIndividualInfo extends RiskBaseModel
{
    public $table = 'hm_individual_info';
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
