<?php

namespace Risk\Common\Models\Experian;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Experian\ExperianRequestInfo
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string $relate_type 关联类型  third_experian
 * @property string|null $pan
 * @property string|null $func_type
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $mobile
 * @property string|null $gender
 * @property string|null $date_of_birth
 * @property string|null $city
 * @property string|null $state
 * @property string|null $pin_code
 * @property string|null $address
 * @property string|null $office_name
 * @property int|null $request_time
 * @property string|null $report_id
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereFuncType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereOfficeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo wherePan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo wherePinCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereRelateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereRequestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianRequestInfo whereUserId($value)
 * @mixin \Eloquent
 */
class ExperianRequestInfo extends RiskBaseModel
{
    /** 关联：third_experian表 */
    const TYPE_THIRD_EXPERIAN = 'third_experian';
    /** 关联：third_report */
    const TYPE_THIRD_REPORT = 'third_report';
    public $table = 'experian_request_Info';
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
