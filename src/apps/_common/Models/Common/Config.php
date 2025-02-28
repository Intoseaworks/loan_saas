<?php

namespace Common\Models\Common;

use Common\Models\Config\Config as CommonConfig;
use Common\Models\User\UserContact;
use Common\Models\User\UserInfo;
use Common\Models\User\UserWork;
use Illuminate\Http\Request;

/**
 * Common\Models\Common\Config
 *
 * @property int $id
 * @property int $merchant_id merchant_id
 * @property string $key
 * @property string $value
 * @property string $remark
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Config\Config orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Config whereValue($value)
 * @mixin \Eloquent
 */
class Config extends CommonConfig
{
    // 用户选择输入配置项
    const KEY_USER_INFO_CONFIG = 'user_info_config';
    // 用户选择输入配置项
    const KEY_ORDER_NO_SIGN_TIP = 'order_no_sign_tip';

    const VALUE_USER_INFO_CONFIG = [
        "gender" => [
            "desc" => "Sex",
            "value" => UserInfo::GENDER_TYPE,
        ],
        "maritalStatus" => [
            "desc" => "Marital status",
            "value" => UserInfo::MARITAL_STATUS_TEXT,
        ],
        "educationLevel" => [
            "desc" => "Education Qualification",
            "value" => UserInfo::EDUCATIONAL_TYPE,
        ],
        "language" => [
            "desc" => "Language",
            "value" => ["English", "Hindi", "Marathi", "Bengali", "Telugu", "Tamil", "Gujarati", "Kannada", "Urdu", "Malayalam", "Odia", "Punjabi", "Assamese"]
        ],
        "residence" => [
            "desc" => "Type of Residence",
            "value" => UserInfo::RESIDENCE_TYPE,
        ],
        "religion" => [
            "desc" => "Religion",
            "value" => UserInfo::RELIGION,
        ],
        "relation" => [
            "desc" => "FAMILY MEMBER",
            "value" => UserContact::RELATION_FAMILY_MEMBER,
        ],
        "relationFriend" => [
            "desc" => "FRIENDS REFERENCES",
            "value" => ["Friend", "Colleague", "Neighbor", "Schoolmate"]
        ],
        "warmNotice" => [
            "desc" => "资料准备提醒",
            "value" => "We gonna need your <span style='color:#1971FF'>Aadhaar Card</span> and <span style='color:#1971FF'>Pan Card</span>. Besides, we will sent an OTP to your <span style='color:#1971FF'>Aadhaar card's mobile number</span>. Please prepare it."
        ],
        "Salary" => [
            "desc" => "Salary",
            "value" => ["below and ₹15,000", "₹15,000-₹25,000", "₹25,000-₹35,000", "₹35,000-₹45,000", "₹45,000-₹55,000", "₹55,000 and above"]
        ],
        "employmentType" => [
            "desc" => "Employment type",
            "value" => UserWork::EMPLOYMENT_TYPE,
        ],
        "chirdrenCount" => [
            "desc" => "Chirdren count",
            "value" => ["0", "1", "2", "3", ">=4"]
        ],
        "loanReason" => [
            "desc" => "Loan reason",
            "value" => ["Bills", "Shopping", "Education", "Gifts", "Grocery", "Beauty", "Party", "Pet care", "Transport", "Electronics", "Wedding", "Business", "Rental", "Medical", "Travel", "Others"]
        ],
        "emailSuffix" => [
            "desc" => "Email suffix",
            "value" => ["gmail.com", "yahoo.com", "rediffmail.com", "hotmail.com", "outlook.com", "Other"],
        ],
        "liveLength" => [
            "desc" => "Live length",
            "value" => ["<1", "1-2", "2-3", ">3"],
        ],
        "userPrincipal" => [
            "desc" => "User principal",
            "value" => ["2000", "3000", "4000", "5000", "6000", "7000", "8000", "9000", "10000"],
        ],
        "userLoanDays" => [
            "desc" => "User loanDays",
            "value" => ["7", "14", "30", "60"],
        ],
    ];


    /**
     * @return array|bool
     */
    public function getUserInfo($merchantId = null)
    {
        $appVersion = app(Request::class)->header('app-version');
        # 紧急联系人1必填FATHER
//        if (in_array($appVersion, ['1.0.8', '1.0.9'])) {
//            $userInfoConfig = static::VALUE_USER_INFO_CONFIG;
//            $urelationFamilyMember = UserContact::RELATION_FAMILY_MEMBER;
//            $userInfoConfig['relation']['value'] = ['FATHER'];
//            $userInfoConfig['relationFriend']['value'] = array_splice($urelationFamilyMember, 1);
//            return $userInfoConfig;
//        }
        // 下拉选项列表无需分别配置在数据库
        return static::VALUE_USER_INFO_CONFIG;

        return static::get(static::KEY_USER_INFO_CONFIG, [], $merchantId);
    }

    public function getLanguage()
    {
        return array_get(Config::VALUE_USER_INFO_CONFIG, 'language.value', []);
    }

    public function getOrderNoSignTip($merchantId = null)
    {
        return static::get(static::KEY_ORDER_NO_SIGN_TIP, '', $merchantId);
    }
}
