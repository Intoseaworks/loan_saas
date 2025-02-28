<?php

namespace Risk\Common\Models\Business\User;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\User\UserInfo
 *
 * @property int $id
 * @property int $app_id merchant_id
 * @property int $user_id 用户id
 * @property string|null $gender 性别:男,女
 * @property string|null $province 省
 * @property string|null $city 市
 * @property string|null $address 户籍地址
 * @property string|null $pincode 邮编
 * @property string|null $permanent_province 永久居住州
 * @property string|null $permanent_city 永久居住城市
 * @property string|null $permanent_address 永久居住地址
 * @property string|null $permanent_pincode 永久居住地址pincode
 * @property string|null $expected_amount 期望额度
 * @property string|null $salary 工资
 * @property string|null $education_level 教育程度
 * @property string|null $marital_status 婚姻状况 0 未婚 1 已婚没有子女 2 已婚没有子女 3离婚 4丧偶
 * @property string|null $id_card_valid_date 身份证有效期限
 * @property string|null $id_card_issued_by 身份证签发机关
 * @property string $birthday
 * @property string|null $father_name
 * @property string|null $residence_type 住宅类型: RENTED租住;OWNED拥有;WITH RELATIVES跟家属住;OFFICE PROVIDED公司宿舍
 * @property string|null $religion 宗教
 * @property string $language 语言
 * @property string $pan_card_no pancard No
 * @property string $aadhaar_card_no aadhaar No
 * @property string|null $company_telephone 公司电话
 * @property string|null $company_name 公司名称
 * @property string|null $passport_no 护照
 * @property string|null $voter_id_card_no 选民身份证
 * @property string|null $driving_license_no 驾驶证
 * @property string|null $address_aadhaar_no 地址aadhaar No
 * @property string|null $email
 * @property string|null $google_token google_token
 * @property string|null $created_at
 * @property string|null $updated_at 更新时间
 * @property string|null $input_name 用户输入姓名
 * @property int|null $contacts_count 通讯录总数
 * @property string|null $input_birthday 用户输入生日
 * @property string|null $chirdren_count 子女数
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereAadhaarCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereAddressAadhaarNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereChirdrenCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereCompanyTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereContactsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereDrivingLicenseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereEducationLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereExpectedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereFatherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereGoogleToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereIdCardIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereIdCardValidDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereInputBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereInputName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo wherePanCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo wherePassportNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo wherePermanentAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo wherePermanentCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo wherePermanentPincode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo wherePermanentProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo wherePincode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereResidenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereVoterIdCardNo($value)
 * @mixin \Eloquent
 */
class UserInfo extends BusinessBaseModel
{
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

    // 婚姻状况
    const MARITAL_STATUS_TEXT = [
        self::MARITAL_MARRIED,//已婚
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

    const LANGUAGE = [
        "Hindi",
        "Marathi",
        "Bengali",
        "Telugu",
        "Tamil",
        "Gujarati",
        "Kannada",
        "Urdu",
        "Malayalam",
        "Odia",
        "Punjabi",
        "Assamese"
    ];
    public static $validate = [
        'data' => 'required|array',
        'data.id' => 'required|numeric',   // 用户详情记录列ID
        'data.gender' => 'required|string',   // 性别 男:Male  女:Female
        'data.province' => 'string',   // 所在州(用户填写)
        'data.city' => 'string',   // 所在城市(用户填写)
        'data.address' => 'required|string',   // 地址(用户填写)
        'data.pincode' => 'required|string',   // pincode(用户填写)
        'data.education_level' => 'required|string',   // 教育程度 小学:Primary  初级中学学历:General Secondary  高级中学学历:Senior Secondary  本科:University Degree  研究生:Postgraduate  博士:Doctoral Degree  其他:other
        'data.marital_status' => 'required|string',   // 婚姻状况  已婚:Married  未婚:Unmarried  离异:Divorce  丧偶:Widowhood
        'data.birthday' => 'required|date_format:d/m/Y',   // 生日
        'data.father_name' => 'nullable|string',   // 父亲姓名
        'data.residence_type' => 'required|string',   // 住宅类型  租住:RENTED  拥有:OWNED  跟家属住:WITH RELATIVES  公司宿舍:OFFICE PROVIDED
        'data.religion' => 'nullable|string',   // 宗教  印度教:Hinduism  伊斯兰教:Islam  基督教:Christianity  锡克教:Sikhism  耆那教:Jainism  无宗教:No religion  其他:other
        'data.language' => 'required|string',   // 语言  印度语:Hindi  马拉地语:Marathi  孟加拉语:Bengali  泰卢固语:Telugu  泰米尔语:Tamil  古吉拉特语:Gujarati  卡纳达语:Kannada  乌尔都语:Urdu  马拉雅拉姆语:Malayalam  奥里亚:Odia  旁遮普语:Punjabi  阿萨姆语:Assamese
        'data.pan_card_no' => 'required|string',   // pan card no
        'data.aadhaar_card_no' => 'string',   // aadhaar card no
        'data.email' => 'nullable|email',   // email
        'data.created_at' => 'required|date',   // 记录创建时间
        'data.updated_at' => 'required|date',   // 最后修改时间
        'data.input_name' => 'nullable|string',   // 用户输入的姓名
        'data.contacts_count' => 'nullable|numeric',   // 通讯录总数
        'data.input_birthday' => 'nullable|string',   // 用户输入生日
        'data.chirdren_count' => 'nullable|string',   // 子女数
        'data.expected_amount' => 'nullable|string',   // 期望额度
        'data.passport_no' => 'nullable|string',   // 护照编号
        'data.voter_id_card_no' => 'nullable|string',   // 选民身份证
        'data.driving_license_no' => 'nullable|string',   // 驾驶证
        'data.permanent_province' => 'nullable|string',   // 永久居住州(证件识别)
        'data.permanent_city' => 'nullable|string',   // 永久居住城市(证件识别)
        'data.permanent_address' => 'nullable|string',   // 永久居住地址(证件识别)
        'data.permanent_pincode' => 'nullable|string',   // 永久居住地址pincode(证件识别)
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_user_info';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'gender',
        'province',
        'city',
        'address',
        'pincode',
        'permanent_province',
        'permanent_city',
        'permanent_address',
        'permanent_pincode',
        'expected_amount',
        'salary',
        'education_level',
        'marital_status',
        'id_card_valid_date',
        'id_card_issued_by',
        'birthday',
        'father_name',
        'residence_type',
        'religion',
        'language',
        'pan_card_no',
        'aadhaar_card_no',
        'company_telephone',
        'company_name',
        'passport_no',
        'voter_id_card_no',
        'driving_license_no',
        'address_aadhaar_no',
        'email',
        'google_token',
        'created_at',
        'updated_at',
        'input_name',
        'contacts_count',
        'input_birthday',
        'chirdren_count',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
