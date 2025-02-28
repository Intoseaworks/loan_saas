<?php

namespace Risk\Common\Models\Business\User;

use Common\Utils\Data\ArrayHelper;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\Business\BankCard\BankCard;
use Risk\Common\Models\Business\BankCard\BankCardPeso;
use Risk\Common\Models\Business\BusinessBaseModel;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\UserData\UserApplication;
use Risk\Common\Models\Business\UserData\UserContactsTelephone;
use Risk\Common\Models\Business\UserData\UserPhoneHardware;
use Risk\Common\Models\Business\UserData\UserPosition;

/**
 * Risk\Common\Models\Business\User\User
 *
 * @property int $id 用户id
 * @property int $app_id merchant_id
 * @property int|null $business_app_id app_id
 * @property string|null $telephone 手机号码
 * @property string|null $fullname 姓名
 * @property string|null $id_card_no 身份证号
 * @property int|null $status 状态 1正常 2禁用
 * @property string|null $created_at 注册时间
 * @property string|null $updated_at 更新时间
 * @property string|null $platform 下载平台
 * @property string|null $client_id 注册终端 Android/ios/h5
 * @property string|null $channel_id 注册渠道
 * @property int $quality 新老用户 0新用户 1老用户
 * @property string|null $quality_time 转老用户时间
 * @property string|null $app_version 注册版本
 * @property string|null $api_token 令牌token
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBusinessAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIdCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereQualityTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends BusinessBaseModel
{
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
    public static $validate = [
        'data' => 'required|array',
        'data.telephone' => 'required|numeric', // 手机号
        'data.fullname' => 'required|string', // 用户姓名
        'data.id_card_no' => 'nullable|string', // 用户身份证号。pan card no
        'data.status' => 'required|numeric', // 用户状态 正常:1 禁用:2 删除:-1
        'data.created_at' => 'required|date', // 用户创建时间
        'data.updated_at' => 'required|date', // 用户修改时间
        'data.client_id' => 'required|string', // 用户注册设备类型 android  ios  h5
        'data.quality' => 'required|numeric', // 用户类型 0:新用户 1:老用户
        'data.quality_time' => 'nullable|date', // 成为复贷用户时间
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_user';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'business_app_id',
        'telephone',
        'fullname',
        'id_card_no',
        'status',
        'created_at',
        'updated_at',
        'platform',
        'client_id',
        'channel_id',
        'quality',
        'quality_time',
        'app_version',
        'api_token',
    ];

    protected static function boot()
    {
        parent::boot();

//        self::setAppIdOrMerchantIdBootScope();
        self::setMerchantIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
        ];
    }

    public function textRules()
    {
        return [];
    }

    public function userInfo($class = UserInfo::class)
    {
        return $this->hasOne($class, 'user_id', 'id');
    }

    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'user_id', 'id')->orderBy('id', 'desc');
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
        return $this->hasOne($class, 'user_id', 'id')->where('status', BankCard::STATUS_ACTIVE);
    }

    public function bankCards($class = BankCardPeso::class)
    {
        return $this->hasMany($class, 'user_id', 'id')->orderBy('id', 'desc');
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

    public function userWork($class = UserWork::class)
    {
        return $this->hasOne($class, 'user_id', 'id');
    }

    public function batchAddRiskData($userId, $data)
    {
        $data = ArrayHelper::isAssocArray($data) ? [$data] : $data;
        foreach ($data as &$item) {
            if (isset($item['app_id'])) {
                $item['business_app_id'] = $item['app_id'];
            }
            $item['app_id'] = MerchantHelper::getMerchantId();
            $item['id'] = $userId;
        }

        return $this->batchInsert($data);
    }
}
