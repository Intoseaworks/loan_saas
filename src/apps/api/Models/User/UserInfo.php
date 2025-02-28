<?php

namespace Api\Models\User;


use Auth;
use Common\Utils\MerchantHelper;

/**
 * Api\Models\User\UserInfo
 *
 * @property int $id 用户信息扩展自增长ID
 * @property int $user_id 用户id
 * @property string $province 省
 * @property string $city 市
 * @property string $address 详细地址
 * @property string $gender 性别:男,女
 * @property string|null $expected_amount 期望额度
 * @property string|null $company_name 公司名称
 * @property string|null $company_telephone 公司电话
 * @property string|null $salary 工资
 * @property string|null $education_level 教育程度
 * @property string|null $marital_status 婚姻状况 0 未婚 1 已婚没有子女 2 已婚没有子女 3离婚 4丧偶
 * @property int|null $created_time
 * @property int|null $updated_time 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereCompanyTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereEducationLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereExpectedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereUpdatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $id_card_valid_date 身份证有效期限
 * @property string|null $id_card_issued_by 身份证签发机关
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereIdCardIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereIdCardValidDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereUpdatedAt($value)
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
 * @property string $passport_no 护照
 * @property string $voter_id_card_no 选民身份证
 * @property string $driving_license_no 驾驶证
 * @property string $address_aadhaar_no 地址aadhaar No
 * @property string|null $email 邮箱
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereAddressAadhaarNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereDrivingLicenseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereFatherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo wherePanCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo wherePassportNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo wherePermanentAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo wherePermanentCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo wherePermanentPincode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo wherePermanentProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo wherePincode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereResidenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereVoterIdCardNo($value)
 * @property int $merchant_id merchant_id
 * @property string $aadhaar_card_no aadhaar No
 * @property string|null $google_token google_token
 * @property-read \Common\Models\User\UserContact $userContact
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereAadhaarCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereGoogleToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserInfo whereMerchantId($value)
 * @property-read \Common\Models\User\UserWork $userWork
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
class UserInfo extends \Common\Models\User\UserInfo
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_SAVE_ID_BACK_INFO = 'save_id_back_info';
    const SCENARIO_SAVE_ID_FRONT_INFO = 'save_id_front_info';
    const SCENARIO_HOME = 'home';
    const SCENARIO_USER_INFO = 'user_info';
    const SCENARIO_UPDATE_CARD = 'update_card';
    const SCENARIO_GOOGLE_TOKEN = 'google_token';
    const SCENARIO_CONTACTS_COUNT = 'contacts_count';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id' => Auth::user()->id,
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
                'passport_no',
                'voter_id_card_no',
                'driving_license_no',
                'address_aadhaar_no',
                'email',
                'input_name',
                'input_birthday',
                'chirdren_count',
                'district',
                'loan_reason',
                'live_length',
                'principal',
                'loan_days',
                'social_software',
                'social_software_phone',
                'social_info',
                'bank_act_num'
            ],
            self::SCENARIO_SAVE_ID_BACK_INFO => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'id_card_valid_date',
                'id_card_issued_by',
            ],
            self::SCENARIO_SAVE_ID_FRONT_INFO => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'gender',
                'address',
                'province',
            ],
            self::SCENARIO_UPDATE_CARD => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'pincode',
                'permanent_province',
                'permanent_city',
                'permanent_address',
                'permanent_pincode',
                'permanent_city',
                'address',
                'birthday',
                'father_name',
                'pan_card_no',
                UserInfo::AADHAAR_CARD_NO,
                UserInfo::ADDRESS_AADHAAR_NO,
                UserInfo::PASSPORT_NO,
                UserInfo::VOTER_ID_CARD_NO,
                UserInfo::DRIVING_LICENSE_NO,
                'gender'
            ],
            self::SCENARIO_GOOGLE_TOKEN => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'google_token'
            ],
            self::SCENARIO_CONTACTS_COUNT => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'contacts_count',
            ],
        ];
    }

    public function texts()
    {
        return [
            self::SCENARIO_HOME => [
                'gender',
            ],
            self::SCENARIO_USER_INFO => [
                'id',
                'birthday',
                'gender',
                'marital_status',
                'religion',
                'education_level',
                'language',
                'profession',
                'company',
                'working_since',
                'workplace_pincode',
                'income',
                'pincode',
                'city',
                'state',
                'address',
                'residence_type',
                'employment_type',
                'social_software',
                'social_software_phone'
            ],
        ];
    }

    public function textRules()
    {
        return [
            'function' => [
                'id' => function () {
                    $this->profession = $this->userWork->profession ?? '';
                    $this->company = $this->userWork->company ?? '';
                    $this->state = $this->province;
                    $this->workplace_pincode = $this->userWork->workplace_pincode ?? '';
                    $this->working_since = $this->userWork->work_start_date ?? '';
                    $this->income = $this->salary;
                    $this->employment_type = $this->userWork->employment_type ?? '';
                },
            ],
        ];
    }
}
