<?php

namespace Risk\Common\Models\Business\User;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\User\UserWork
 *
 * @property int $id 用户工作信息自增长ID
 * @property int $app_id merchant_id
 * @property int $user_id 用户id
 * @property string|null $profession 职业
 * @property string|null $workplace_pincode 工作地点邮编
 * @property string|null $company 公司
 * @property string|null $work_address1 工作地点1
 * @property string|null $work_address2 工作地点2
 * @property string|null $work_positon 工作位置
 * @property string|null $salary 薪水
 * @property string|null $work_email 工作邮箱
 * @property string|null $work_start_date 开始工作时间
 * @property int|null $work_experience_years 工作经验，单位/年
 * @property int|null $work_experience_months 工作经验，单位/月
 * @property string|null $work_phone 工作电话
 * @property int|null $work_card_front_file_id 工作证正面文件id
 * @property int|null $work_card_back_file_id 工作证反面文件id
 * @property string $employment_type 就业类型
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $occupation 一级职业
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereEmploymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereOccupation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereProfession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkCardBackFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkCardFrontFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkExperienceMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkExperienceYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkPositon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkplacePincode($value)
 * @mixin \Eloquent
 */
class UserWork extends BusinessBaseModel
{
    const SCENARIO_CREATE = 'create';

    // 全职
    const EMPLOYMENT_TYPE_FULL_TIME_SALARIED = 'full-time salaried';
    // 兼职
    const EMPLOYMENT_TYPE_PART_TIME_SALARIED = 'part-time salaried';
    // 个体经营
    const EMPLOYMENT_TYPE_SELF_EMPLOYED = 'self-employed';
    // 无业
    const EMPLOYMENT_TYPE_NO_JOB = 'no job';
    // 学生
    const EMPLOYMENT_TYPE_STUDENT = 'student';
    const EMPLOYMENT_TYPE_OTHER = 'other';
    const EMPLOYMENT_TYPE_NULL = 'null';

    const EMPLOYMENT_TYPE = [
        self::EMPLOYMENT_TYPE_FULL_TIME_SALARIED,
        self::EMPLOYMENT_TYPE_PART_TIME_SALARIED,
        self::EMPLOYMENT_TYPE_SELF_EMPLOYED,
        self::EMPLOYMENT_TYPE_NO_JOB,
        self::EMPLOYMENT_TYPE_STUDENT,
        self::EMPLOYMENT_TYPE_OTHER,
    ];

    const SALARY = ["below and ₹15,000", "₹15,000-₹25,000", "₹25,000-₹35,000", "₹35,000-₹45,000", "₹45,000-₹55,000", "₹55,000 and above"];

    // 其他
    public static $validate = [
        'data' => 'required|array',
        'data.id' => 'required|numeric',   // 记录列ID
        'data.employment_type' => 'required|string',   // 就业类型  全职:full-time salaried  兼职:part-time salaried  个体经营:self-employed  无业:no job  学生:student  其他:other
        'data.created_at' => 'required|date',   // 记录创建时间
        'data.updated_at' => 'nullable|date',   // 记录修改时间
        'data.profession' => 'nullable|string',   // 职业
        'data.workplace_pincode' => 'nullable|string',   // 工作地点pincode
        'data.company' => 'nullable|string',   // 公司
        'data.work_address1' => 'nullable|string',   // 工作地点1
        'data.work_address2' => 'nullable|string',   // 工作地点2
        'data.work_positon' => 'nullable|string',   // 工作位置
        'data.salary' => 'nullable|string',   // 薪水范围  "below and ₹15,000", "₹15,000-₹25,000", "₹25,000-₹35,000", "₹35,000-₹45,000", "₹45,000-₹55,000", "₹55,000 and above"
        'data.work_email' => 'nullable|string',   // 工作邮箱
        'data.work_start_date' => 'nullable|date_format:d/m/Y',   // 开始工作时间 格式 d/m/Y
        'data.work_experience_years' => 'nullable|integer',   // 工作经验 年
        'data.work_experience_months' => 'nullable|integer',   // 工作经验 月
        'data.work_phone' => 'nullable|string',   // 工作电话
    ];
    public $timestamps = false;
    protected $table = 'data_user_work';
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'profession',
        'workplace_pincode',
        'company',
        'work_address1',
        'work_address2',
        'work_positon',
        'salary',
        'work_email',
        'work_start_date',
        'work_experience_years',
        'work_experience_months',
        'work_phone',
        'work_card_front_file_id',
        'work_card_back_file_id',
        'employment_type',
        'created_at',
        'updated_at',
        'occupation',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
