<?php

namespace Common\Models\User;

use Api\Rules\User\LoginRule;
use Api\Services\User\UserAuthServer;
use Common\Models\Activity\ActivitiesRecord;
use Common\Models\Activity\Activity;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Channel\Channel;
use Common\Models\Collection\CollectionRecord;
use Common\Models\Config\Config;
use Common\Models\Login\UserLoginLog;
use Common\Models\Merchant\App;
use Common\Models\Order\Order;
use Common\Models\Upload\Upload;
use Common\Models\UserData\UserApplication;
use Common\Models\UserData\UserContactsTelephone;
use Common\Models\UserData\UserPhoneHardware;
use Common\Models\UserData\UserPosition;
use Common\Redis\CommonRedis;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Common\Models\User\User
 *
 * @property int $id 用户id
 * @property string|null $telephone 手机号码
 * @property string|null $fullname 姓名
 * @property string|null $id_card_no 身份证号
 * @property int|null $status 状态 1正常 2禁用
 * @property int|null $created_at 注册时间
 * @property int|null $updated_at 更新时间
 * @property int|null $level 用户评级
 * @property string|null $platform 下载平台
 * @property string|null $client_id 注册终端 Android/ios/h5
 * @property string|null $channel_id 注册渠道
 * @property int $quality 新老用户 0新用户 1老用户
 * @property string $app_version 注册版本
 * @property-read \Common\Models\Channel\Channel $channel
 * @property-read \Common\Models\Order\Order $order
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Order\Order[] $orders
 * @property-read \Common\Models\User\UserInfo $userInfo
 * @property-read \Common\Models\User\UserWork $userWork
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereIdCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Common\Models\BankCard\BankCardPeso $bankCard
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\BankCard\BankCardPeso[] $bankCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\User\UserContact[] $userContacts
 * @property-read \Common\Models\User\UserBlack $userBlack
 * @property-read \Common\Models\User\UserAuth[] $userAuths
 * @property string|null $api_token 令牌token
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereApiToken($value)
 * @property string|null $quality_time 转老用户时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereQualityTime($value)
 * @property int $merchant_id merchant_id
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User whereMerchantId($value)
 * @property string|null $card_type 证件卡号类型
 * @property string|null $password
 * @method static \Illuminate\Database\Eloquent\Builder|User orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @property string|null $firstname firstname
 * @property string|null $middlename middlename
 * @property string|null $lastname lastname
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMiddlename($value)
 */
class User extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use StaticModel;
    use Authenticatable, Authorizable;

    /** @var int 正常 */
    const STATUS_NORMAL = 1;
    /** @var int 禁用 */
    const STATUS_DISABLE = 2;
    /** @var array status */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    /** @var int 新用户 */
    const QUALITY_NEW = 0;
    /** @var int 复贷用户 */
    const QUALITY_OLD = 1;
    /** @var int 当日注册用户 */
    const QUALITY_REGISTER = 2;
    /** @var array status */
    const QUALITY = [
        self::QUALITY_NEW => '新用户',
        self::QUALITY_OLD => '复贷用户',
        // self::QUALITY_REGISTER => '当日注册用户',
    ];

    const CLIENT_ANDROID = 'android';
    const CLIENT_IOS = 'ios';
    const CLIENT_H5 = 'h5';
    const CLIENT = [
        self::CLIENT_ANDROID => 'Android',
        self::CLIENT_IOS => 'IOS',
        self::CLIENT_H5 => 'H5',
    ];

    /**
     * @var string
     */
    protected $table = 'user';

    protected static function boot()
    {
        parent::boot();

        self::setAppIdOrMerchantIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            LoginRule::SCENARIO_LOGIN => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'app_id' => MerchantHelper::getAppId(),
                'password',
                'telephone',
                'platform',
                'client_id',
                'app_version',
                'channel_id',
                "firebase_uid"
            ]
        ];
    }

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    public function textRules()
    {
        return [];
    }

    public static function getByTelephone($telephone)
    {
        return self::whereTelephone($telephone)->first();
    }

    public function getIsCompleted()
    {
        $res = UserAuthServer::server()->getIsCompleted($this);
        return $res;
    }

    public function getOptionalIsCompleted()
    {
        return UserAuthServer::server()->getOptionalIsCompleted($this->id);
    }

    public function getQuality()
    {
        return ts(User::QUALITY, 'user');
    }

    /**
     * 身份验证汇总
     *
     * @return bool|int
     */
    public function getIdentityStatus()
    {
        return $this->getPanCardStatus() == UserAuth::STATUS_VALID &&
        $this->getFaceStatus() == UserAuth::STATUS_VALID &&
        # aadhaarKYC认证 或者 aadhaar认证加地址认证
        ($this->getAadhaarCardStatus() == UserAuth::STATUS_VALID || $this->getAadhaarCardKYCStatus() == UserAuth::STATUS_VALID)
            ? UserAuth::STATUS_VALID : UserAuth::STATUS_INVALID;
    }

    /**
     * 基本信息验证
     *
     * @return int
     */
    public function getBaseInfoStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_BASE_INFO);
    }

    /**
     * 基本信息过期日期
     *
     * @return int
     */
    public function getBasicDetailEpireDate()
    {
        return UserAuthServer::server()->getExpireDate($this->id, UserAuth::TYPE_BASE_INFO);
    }

    /**
     * @return int
     */
    public function getTelephoneStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_TELEPHONE);
    }

    /**
     * @return int
     */
    public function getBankCardStatus()
    {
        if($this->bankCard){
            return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_BANKCARD);
        }
        return 0;
    }

    /**
     * @return date
     */
    public function getBankCardEpireDate()
    {
        return UserAuthServer::server()->getExpireDate($this->id, UserAuth::TYPE_BANKCARD);
    }

    /**
     * 获取芝麻信用认证状态
     */
    public function getZhimaStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_ZHIMA);
    }

    /**
     * 获取支付宝认证状态
     */
    public function getAlipayStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_ALIPAY);
    }

    public function userInfo($class = UserInfo::class)
    {
        return $this->hasOne($class, 'user_id', 'id');
    }

    public function userLoginLog($class = UserLoginLog::class)
    {
        return $this->hasOne($class, 'user_id', 'id')->orderBy('id', 'desc');
    }

    public function channel($class = Channel::class)
    {
        return $this->hasOne($class, 'id', 'channel_id');
    }

    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'user_id', 'id')->orderBy('created_at', 'desc');
    }

    public function orders($class = Order::class)
    {
        return $this->hasMany($class, 'user_id', 'id');
    }

    public function userContacts($class = UserContact::class)
    {
        return $this->hasMany($class, 'user_id', 'id')->where('status', UserContact::STATUS_ACTIVE);
    }

    public function bankCard($class = BankCardPeso::class)
    {
        return $this->hasOne($class, 'user_id', 'id')->where('status', BankCardPeso::STATUS_ACTIVE);
    }

    public function bankCards($class = BankCardPeso::class)
    {
        //u3银行卡排除
        return $this->hasMany($class, 'user_id', 'id')->where('status', '<>',BankCardPeso::STATUS_DELETE_PERALENDING)->orderBy('id', 'desc');
    }

    public function userBlack($class = UserBlack::class)
    {
        return $this->hasOne($class, 'telephone', 'telephone')->where('status',
            UserBlack::STATUS_NORMAL)->withDefault();
    }

    public function invitedCode($class = UserInviteCode::class)
    {
        return $this->hasOne($class, 'invited_user');
    }

    public function invitedUsers($class = UserInviteCode::class)
    {
        return $this->hasMany($class, 'user_id')->where('invited_user','>',0);
    }

    public function userAuths($class = UserAuth::class)
    {
        return $this->hasMany($class, 'user_id', 'id')
            ->where('status', UserAuth::STATUS_VALID)
            ->where('auth_status', UserAuth::AUTH_STATUS_SUCCESS);
    }

    public function userComplete($class = UserAuth::class)
    {
        return $this->hasMany($class, 'user_id', 'id')
            ->where('status', UserAuth::STATUS_VALID)
            ->where('type', '=', UserAuth::TYPE_COMPLETED)
            ->where('auth_status', UserAuth::AUTH_STATUS_SUCCESS);
    }

    public function uploads($class = Upload::class)
    {
        return $this->hasMany($class, '', '')->where('status', Upload::STATUS_NORMAL);
    }

    public function collectionRecords($class = CollectionRecord::class)
    {
        return $this->hasMany($class, 'user_id', 'id');
    }

    public function userPosition($class = UserPosition::class)
    {
        return $this->hasOne($class, 'user_id', 'id')->orderBy('id', 'desc');
    }

    public function userApplications($class = UserApplication::class)
    {
        return $this->hasMany($class, 'user_id', 'id');
    }

    public function userContactsTelephones($class = UserContactsTelephone::class)
    {
        return $this->hasMany($class, 'user_id', 'id');
    }

    public function userPhoneHardwares($class = UserPhoneHardware::class)
    {
        return $this->hasMany($class, 'user_id', 'id');
    }

    public function activitiesRecords()
    {
        return $this->hasMany(ActivitiesRecord::class,'user_id');
    }

    public function app($class = App::class)
    {
        return $this->BelongsTo($class, 'app_id', 'id');
    }

    /**
     * @param $cardNo
     * @return \Illuminate\Database\Eloquent\Builder|$this|object|null
     */
    public function getByIdCardNo($cardNo)
    {
        return self::whereIdCardNo($cardNo)->first();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        $apiDomain = config('config.api_client_domain');
        return [
            'verify_url' => $apiDomain . '/app/user/home',
            'token_key' => 'token',
            // 引入商户后，用来标识用户所属app
            self::$relationAppField => $this->app_id,
        ];
    }

    public function getAuthPassword()
    {
        throw new \Exception('api 暂不支持密码登录');
    }

    /**
     * 更新用户来源渠道
     * @param $userId
     * @param $clientId
     * @return mixed
     */
    public function updateClientId($userId, $clientId)
    {
        if (is_array($userId) || is_object($userId)) {
            $query = $this->whereIn('id', $userId);
        } else {
            $query = $this->whereIn('id', (array)$userId);
        }

        return $query->update(['client_id' => $clientId]);
    }

    /**
     * 紧急联系人效验
     *
     * @return int
     */
    public function getContactsStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_CONTACTS);
    }

    /**
     * 过期日期
     *
     * @return Date
     */
    public function getContactseEpireDate()
    {
        return UserAuthServer::server()->getExpireDate($this->id, UserAuth::TYPE_CONTACTS);
    }

    /**
     * @return int
     */
    public function getUserWorkStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_USER_WORK);
    }

    /**
     * @return int
     */
    public function getIdCardStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_ID_FRONT);
    }

    /**
     * @return int
     */
    public function getAddressAuthStatus()
    {
        /* 走新逻辑风控去验nio.wang */
        if ($this->app_id != "1") {
            return 1;
        }
        $status = UserAuthServer::server()->getAuthStatusOnly($this->id, [
            UserAuth::TYPE_ADDRESS_PASSPORT,
            UserAuth::TYPE_ADDRESS_VOTER,
            UserAuth::TYPE_ADDRESS_DRIVING,
            UserAuth::TYPE_ADDRESS_AADHAAR,
        ]);
        if ($status) {
            return 1;
        }
        # 跳过地址认证
        $authStatus = UserAuthServer::server()->getAuth($this->id, UserAuth::TYPE_ADDRESS);
        if ($authStatus == UserAuth::AUTH_STATUS_SKIP) {
            return UserAuth::AUTH_STATUS_SKIP;
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getFacebookAuthStatus()
    {
        $status = UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_FACEBOOK);
        $cacheKey = 'auth:facebook:' . $this->id;
        if ($status != UserAuth::AUTH_STATUS_SUCCESS && CommonRedis::redis()->get($cacheKey)) {
            $status = UserAuth::AUTH_STATUS_IN;
        }
        return $status;
    }

    /**
     * @return int
     */
    public function getBankBillStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_BANK_BILL);
    }

    /**
     * @return int
     */
    public function getPaySlipStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_PAY_SLIP);
    }

    /**
     * @return int
     */
    public function getOLAAuthStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_OLA);
    }

    /**
     * @return int
     */
    public function getAadhaarCardStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_AADHAAR_CARD);
    }

    /**
     * @return int
     */
    public function getAadhaarCardKYCStatus()
    {
        $status = UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_AADHAAR_CARD_KYC);
        if ($status == UserAuth::AUTH_STATUS_SUCCESS) {
            return $status;
        }
        # 如果ekyc未打开，直接跳过
        if (!Config::model()->getAuthEkycOn()) {
            return UserAuth::AUTH_STATUS_SKIP;
        }
        return $status;
    }

    /**
     * pan card
     *
     * @return int
     */
    public function getPanCardStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_PAN_CARD);
    }

    /**
     * 脸部识别校验
     *
     * @return int
     */
    public function getFaceStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_FACES);
    }

    /**
     * 护照
     *
     * @return int
     */
    public function getAddressPassportStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_ADDRESS_PASSPORT);
    }

    /**
     * voterId
     *
     * @return int
     */
    public function getAddressVoterIdCardStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_ADDRESS_VOTER);
    }

    /**
     * 驾驶证
     *
     * @return int
     */
    public function getAddressDrivingLicenseStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_ADDRESS_DRIVING);
    }

    /**
     * aadhaar
     *
     * @return int
     */
    public function getAddressAadhaarStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_ADDRESS_AADHAAR);
    }

    /**
     * 扩展信息验证
     *
     * @return int
     */
    public function getPersonalInfoStatus()
    {
        return UserAuthServer::server()->getAuthStatus($this->id, UserAuth::TYPE_USER_EXTRA_INFO);
    }

    /**
     * 获取实时用户Quality
     * @return bool
     */
    public function getRealQuality()
    {
        #哥大项目特殊处理，必须复贷
        if($this->merchant_id == \Common\Models\Merchant\Merchant::getId("c1")){
            return 1;
        }
        return intval($this->getRepeatLoanCnt() >= 1);
    }

    /**
     * 获取复贷次数
     * @return mixed
     */
    public function getRepeatLoanCnt()
    {
        return $this->orders->whereIn('status', Order::CONTRACT_STATUS)->count();
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userWork($class = UserWork::class)
    {
        return $this->hasOne($class, 'user_id', 'id');
    }
}
