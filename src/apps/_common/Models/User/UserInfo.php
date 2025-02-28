<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\UserInfo
 *
 * @property int $id 用户信息扩展自增长ID
 * @property int $user_id 用户id
 * @property int|null $created_time
 * @property int|null $updated_time 更新时间
 * @property string|null $education_level 教育程度
 * @property string $province 省
 * @property string $city 市
 * @property string $address 详细地址
 * @property string $gender 性别:男,女
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereEducationLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereUpdatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $expected_amount 期望额度
 * @property string|null $company_name 公司名称
 * @property string|null $company_telephone 公司电话
 * @property string|null $salary 工资
 * @property string|null $marital_status 婚姻状况 0 未婚 1 已婚没有子女 2 已婚没有子女 3离婚 4丧偶
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereCompanyTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereExpectedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereSalary($value)
 * @property string|null $id_card_valid_date 身份证有效期限
 * @property string|null $id_card_issued_by 身份证签发机关
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereIdCardIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereIdCardValidDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo orderByCustom($column = null, $direction = 'asc')
 * @property string $pincode 邮编
 * @property string $permanent_province 永久州
 * @property string $permanent_city 永久城市
 * @property string $permanent_address 永久地址
 * @property string $permanent_pincode 永久邮编
 * @property string $birthday 生日
 * @property string $father_name
 * @property string $residence_type 住宅类型: RENTED租住;OWNED拥有;WITH RELATIVES跟家属住;OFFICE PROVIDED公司宿舍
 * @property string $religion 宗教
 * @property string $language 语言
 * @property string $pan_card_no pancard No
 * @property string $aadhaar_card_no aadhaar card No
 * @property string $passport_no 护照
 * @property string $voter_id_card_no 选民身份证
 * @property string $driving_license_no 驾驶证
 * @property string $address_aadhaar_no 地址aadhaar No
 * @property string|null $email 邮箱
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereAddressAadhaarNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereDrivingLicenseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereFatherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo wherePanCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo wherePassportNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo wherePermanentAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo wherePermanentCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo wherePermanentPincode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo wherePermanentProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo wherePincode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereResidenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereVoterIdCardNo($value)
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $last_name
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereMiddleName($value)
 * @property int $merchant_id merchant_id
 * @property string|null $google_token google_token
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\User\UserContact[] $userContact
 * @property-read \Common\Models\User\UserWork $userWork
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereAadhaarCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereGoogleToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserInfo whereMerchantId($value)
 * @property string|null $input_name 用户输入姓名
 * @property int|null $contacts_count 通讯录总数
 * @property string|null $input_birthday 用户输入生日
 * @property string|null $chirdren_count 子女数
 * @property string|null $loan_reason 借款目的
 * @property string|null $live_length 居住年限
 * @property int|null $bank_act_num 银行账号个数
 * @property string|null $district 公社/社区
 * @property-read int|null $user_contact_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereBankActNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereChirdrenCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereContactsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereInputBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereInputName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereLiveLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereLoanReason($value)
 * @property string|null $confirm_month_income 确认收入(电审)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereConfirmIncome($value)
 * @property string|null $social_info 社交账号信息
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereSocialInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereConfirmMonthIncome($value)
 */
class UserInfo extends Model
{
    use StaticModel;

    const PAN_CARD_NO = 'pan_card_no';
    const AADHAAR_CARD_NO = 'aadhaar_card_no';
    const PASSPORT_NO = 'passport_no';
    const VOTER_ID_CARD_NO = 'voter_id_card_no';
    const DRIVING_LICENSE_NO = 'driving_license_no';
    const ADDRESS_AADHAAR_NO = 'address_aadhaar_no';

    // 住宅类型
    const RESIDENCE_TYPE = [
        'RENTED',//租住
        'OWNED',//拥有
        'WITH RELATIVES',//跟家属住
        'OFFICE PROVIDED',//公司宿舍
    ];

    const MARITAL_MARRIED = 'Married'; //已婚
    const MARITAL_UNMARRIED = 'Unmarried'; //未婚
    const MARITAL_DIVORCE = 'Divorce'; //离异
    const MARITAL_WIDOWHOOD = 'Widowhood'; //丧偶
    const MARITAL_MARRIED_WITH_CHILDREN = "Married with kid";//已婚有小孩
    const MARITAL_MARRIED_WITHOUT_CHILDREN = "Married without kid";//已婚无小孩

    // 婚姻状况
    const MARITAL_STATUS_TEXT = [
        self::MARITAL_MARRIED_WITH_CHILDREN,//已婚有小孩
        self::MARITAL_MARRIED_WITHOUT_CHILDREN,//已婚无小孩
        self::MARITAL_UNMARRIED,//未婚
        self::MARITAL_DIVORCE,//离异
        self::MARITAL_WIDOWHOOD,//丧偶
    ];

    const GENDER_MALE = 'Male'; //男性
    const GENDER_FEMALE = 'Female'; //女性
    // 性别类型
    const GENDER_TYPE = [
        self::GENDER_MALE,//男性
        self::GENDER_FEMALE,//女性
    ];

    // 小学学历
    const EDUCATIONAL_TYPE_PRIMARY = 'Primary';
    // 初级中学学历
    const EDUCATIONAL_TYPE_GENERAL_SECONDARY = 'General Secondary';
    // 高级中学学历
    const EDUCATIONAL_TYPE_SENIOR_SECONDARY = 'Senior Secondary';
    // 本科学历
    const EDUCATIONAL_TYPE_UNIVERSITY_DEGREE = 'University Degree';
    // 研究生学历
    const EDUCATIONAL_TYPE_POSTGRADUATE = 'Postgraduate';
    // 博士
    const EDUCATIONAL_TYPE_DOCTORAL_DEGREE = 'Doctoral Degree';
    // 其他
    const EDUCATIONAL_TYPE_DOCTORAL_OTHER = 'other';
    // 学历
    const EDUCATIONAL_TYPE = [
        self::EDUCATIONAL_TYPE_PRIMARY,
        self::EDUCATIONAL_TYPE_GENERAL_SECONDARY,
        self::EDUCATIONAL_TYPE_SENIOR_SECONDARY,
        self::EDUCATIONAL_TYPE_UNIVERSITY_DEGREE,
        self::EDUCATIONAL_TYPE_POSTGRADUATE,
        self::EDUCATIONAL_TYPE_DOCTORAL_DEGREE,
        self::EDUCATIONAL_TYPE_DOCTORAL_OTHER,
    ];

    const RELIGION_HINDUISM = 'Hinduism';
    const RELIGION_ISLAM = 'Islam';
    const RELIGION_CHRISTIANITY = 'Christianity';
    const RELIGION_SIKHISM = 'Sikhism';
    const RELIGION_JAINISM = 'Jainism';
    const RELIGION_NO_RELIGION = 'No religion';
    const RELIGION_OTHER = 'other';
    /** 宗教 */
    const RELIGION = [
        self::RELIGION_HINDUISM,
        self::RELIGION_ISLAM,
        self::RELIGION_CHRISTIANITY,
        self::RELIGION_SIKHISM,
        self::RELIGION_JAINISM,
        self::RELIGION_NO_RELIGION,
        self::RELIGION_OTHER,
    ];

    // 此处只是用来计算，具体存值见 dictionary 表 MONTHLY_INCOME
    const SALARY_RANGE = [
        'MONEY1' => [-INF, 5000],
        'MONEY2' => [5000, 10000],
        'MONEY3' => [10000, 20000],
        'MONEY4' => [20000, 50000],
        'MONEY5' => [50000, 100000],
        'MONEY6' => [100000, +INF],
    ];

    /**
     * @var string
     */
    protected $table = 'user_info';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    /**
     * @param $userId
     * @return Model|static|null
     */
    public function getInfoByUid($userId)
    {
        return static::where(['user_id' => $userId])->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userWork()
    {
        return $this->hasOne(UserWork::class, 'user_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userContact()
    {
        return $this->hasMany(UserContact::class, 'user_id', 'user_id')
            ->where('status', UserContact::STATUS_ACTIVE);
    }

}
