<?php

namespace Common\Models\User;

use Auth;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * CashNow\Common\Models\User\UserWork
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property string $profession 职业
 * @property string $employment_type 就业类型
 * @property string $workplace_pincode 工作地点邮编
 * @property string $company 公司
 * @property string $salary 薪水
 * @property string $work_positon 工作位置
 * @property string $work_address1 工作地点1
 * @property string $work_address2 工作地点2
 * @property string $work_email 工作邮箱
 * @property string $work_start_date 开始工作时间
 * @property int $work_experience_years 工作经验，单位/年
 * @property int $work_experience_months 工作经验，单位/月
 * @property string $work_phone 工作电话
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int $work_card_front_file_id 工作证正面文件id
 * @property int $work_card_back_file_id 工作证反面文件id
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereEmploymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereProfession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkCardBackFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkCardFrontFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkExperienceMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkExperienceYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkPositon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CashNow\Common\Models\User\UserWork whereWorkplacePincode($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserWork whereMerchantId($value)
 * @property string|null $occupation 一级职业
 * @property string|null $industry 行业
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereIndustry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereOccupation($value)
 * @property string|null $working_time_type 在职时长
 * @method static \Illuminate\Database\Eloquent\Builder|UserWork whereWorkingTimeType($value)
 */
class UserWork extends Model
{
    use StaticModel;
    const SCENARIO_CREATE = 'create';

    protected $table = 'user_work';
    protected $fillable = [];
    protected $hidden = [];

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
    // 其他
    const EMPLOYMENT_TYPE_OTHER = 'other';
    const EMPLOYMENT_TYPE_NULL = 'null';
    // 职业类型
    const EMPLOYMENT_TYPE = [
        self::EMPLOYMENT_TYPE_FULL_TIME_SALARIED,
        self::EMPLOYMENT_TYPE_PART_TIME_SALARIED,
        self::EMPLOYMENT_TYPE_SELF_EMPLOYED,
        self::EMPLOYMENT_TYPE_NO_JOB,
        self::EMPLOYMENT_TYPE_STUDENT,
        //self::EMPLOYMENT_TYPE_OTHER,
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [
            'array' => [
            ],
        ];
    }

    public function safes()
    {
        return [
            static::SCENARIO_CREATE => [
                'user_id' => Auth::user()->id ?? 0,
                'merchant_id' => MerchantHelper::getMerchantId(),
                'profession',
                'employment_type',
                'workplace_pincode',
                'company',
                'salary',
                'work_positon',
                'work_address1',
                'work_address2',
                'work_email',
                'work_start_date',
                'work_experience_years',
                'work_experience_months',
                'work_phone',
                'work_card_front_file_id',
                'work_card_back_file_id',
                'occupation',
                'industry',
                'working_time_type'
            ],
        ];
    }
}
