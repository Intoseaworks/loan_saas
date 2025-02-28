<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\UserAuth
 *
 * @property int $id 用户信息扩展自增长ID
 * @property int $user_id 用户id
 * @property string $type 用户数据类型
 * @property int $time 用户数据时间
 * @property int $status 状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $auth_status
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereAuthStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth orderByCustom($column = null, $direction = 'asc')
 * @property int $quality 新老用户 0新用户 1老用户
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereQuality($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth whereMerchantId($value)
 */
class UserAuth extends Model
{
    use StaticModel;

    const TYPE_IDENTITY = 'identity';
    const TYPE_BASE_INFO = 'base_info';
    const TYPE_TELEPHONE = 'telephone';
    const TYPE_ID_FRONT = 'id_front';
    const TYPE_ID_BACK = 'id_back';
    const TYPE_ID_HANDHELD = 'id_handheld';
    const TYPE_FACES = 'faces';
    const TYPE_BANKCARD = 'bankcard';
    const TYPE_COMPLETED = 'completed';//完善时间
    const TYPE_ALIPAY = 'alipay';
    const TYPE_ZHIMA = 'zhima';
    const TYPE_COMPANY_ID = 'company_id'; //工作证

    const TYPE_USER_EXTRA_INFO = 'user_extra_info'; // 用户扩展信息
    const TYPE_PAN_CARD = 'pan_card';
    const TYPE_AADHAAR_CARD = 'aadhaar_card';
    const TYPE_AADHAAR_CARD_KYC = 'aadhaar_card_kyc';
    const TYPE_FACEBOOK = 'facebook';
    const TYPE_CONTACTS = 'contacts';
    const TYPE_BANK_BILL = 'bank_bill';
    const TYPE_PAY_SLIP = 'pay_slip';
    const TYPE_USER_WORK = 'user_work';
    const TYPE_ADDRESS = 'address';
    const TYPE_ADDRESS_VOTER = 'address_voter';
    const TYPE_ADDRESS_DRIVING = 'address_driving';
    const TYPE_ADDRESS_AADHAAR = 'address_aadhaar';
    const TYPE_ADDRESS_PASSPORT = 'address_passport';
    const TYPE_AADHAAR_CARD_FRONT = 'aadhaar_card_front';
    const TYPE_AADHAAR_CARD_BACK = 'aadhaar_card_back';
    const TYPE_OLA = 'ola';
    const TYPE_KYC_DOCUMENTS = 'kyc_documents';

    const TYPE = [
        self::TYPE_BASE_INFO => '个人信息',
        self::TYPE_CONTACTS => '紧急联系人',
        self::TYPE_FACES => '人脸识别',
//        self::TYPE_AADHAAR_CARD_KYC => 'aadhaarKYC认证',
//        self::TYPE_AADHAAR_CARD => 'aadhaar认证',
//        self::TYPE_PAN_CARD => 'Pancard',
//        self::TYPE_ADDRESS_VOTER => '选民证认证',
//        self::TYPE_ADDRESS_PASSPORT => '护照认证',
//        self::TYPE_USER_EXTRA_INFO => '扩展信息',
        self::TYPE_BANKCARD => '银行卡绑定',
        self::TYPE_COMPANY_ID => '工作证',
    ];

    const TYPE_BY_SETTING_TYPE = [
        self::TYPE_BASE_INFO => self::TYPE_BASE_INFO,
        self::TYPE_TELEPHONE => self::TYPE_CONTACTS,
        self::TYPE_CONTACTS => self::TYPE_CONTACTS,
        self::TYPE_FACES => self::TYPE_KYC_DOCUMENTS,
        self::TYPE_AADHAAR_CARD_KYC => self::TYPE_KYC_DOCUMENTS,
        self::TYPE_AADHAAR_CARD => self::TYPE_KYC_DOCUMENTS,
        self::TYPE_PAN_CARD => self::TYPE_KYC_DOCUMENTS,
        self::TYPE_ADDRESS => self::TYPE_KYC_DOCUMENTS,
        self::TYPE_ADDRESS_VOTER => self::TYPE_KYC_DOCUMENTS,
        self::TYPE_ADDRESS_PASSPORT => self::TYPE_KYC_DOCUMENTS,
        self::TYPE_USER_EXTRA_INFO => self::TYPE_USER_EXTRA_INFO,
        self::TYPE_BANKCARD => self::TYPE_BANKCARD,
    ];

    /**
     * 完善所需认证项
     */
    const TYPE_IS_COMPLETED = [
        UserAuth::TYPE_BASE_INFO,
        UserAuth::TYPE_USER_WORK,
        UserAuth::TYPE_CONTACTS,
        UserAuth::TYPE_FACES,
        UserAuth::TYPE_ID_FRONT,
//        UserAuth::TYPE_BANKCARD,
    ];

    const AUTH_CHECK_LIST = [
        self::TYPE_BASE_INFO => [
            'daysValid' => 180,//天
        ],
        self::TYPE_USER_EXTRA_INFO => [
            'daysValid' => 180,//天
        ],
        self::TYPE_TELEPHONE => [
            'daysValid' => 180,//天
        ],
        self::TYPE_AADHAAR_CARD => [
            'daysValid' => 180,//天
        ],
        self::TYPE_FACES => [
            'daysValid' => 180,//天
        ],
        self::TYPE_ADDRESS_VOTER => [
            'daysValid' => 180,//天
        ],
        self::TYPE_ADDRESS_PASSPORT => [
            'daysValid' => 180,//天
        ],
        self::TYPE_IDENTITY => [
            'daysValid' => 180,//天
        ],
        self::TYPE_CONTACTS => [
            'daysValid' => 180,//天
        ],
        self::TYPE_BANK_BILL => [
            'daysValid' => 180,//天
        ],
        self::TYPE_USER_WORK => [
            'daysValid' => 180,//天
        ],
        self::TYPE_ADDRESS => [
            'daysValid' => 180,//天
        ],
        self::TYPE_BANKCARD => [
            'daysValid' => 180,//天
        ],
        self::TYPE_ID_FRONT => [
            'daysValid' => 180, //天
        ],
        self::TYPE_COMPLETED => [
            'daysValid' => 180, //天
        ],
    ];

    # 风控认证项
    const AUTH_TYPE = [
        self::TYPE_TELEPHONE,
        self::TYPE_ALIPAY,
        self::TYPE_ZHIMA,
        self::TYPE_FACEBOOK
    ];

    const STATUS_VALID = 1;//有效
    const STATUS_INVALID = 0;//失效
    const STATUS = [
        self::STATUS_VALID => '有效',
        self::STATUS_INVALID => '失效',
    ];

    const AUTH_STATUS_NOT = 0;//去认证
    const AUTH_STATUS_SUCCESS = 1;//已认证
    const AUTH_STATUS_EXPIRE = 2;//已过期
    const AUTH_STATUS_IN = 3;//认证中
    const AUTH_STATUS_FAIL = 4;//认证失败
    const AUTH_STATUS_SKIP = 5;//认证跳过
    const AUTH_STATUS = [
        self::AUTH_STATUS_NOT => '去认证',
        self::AUTH_STATUS_SUCCESS => '已认证',
        self::AUTH_STATUS_EXPIRE => '已过期',
        self::AUTH_STATUS_IN => '认证中',
        self::AUTH_STATUS_FAIL => '认证失败',
        self::AUTH_STATUS_SKIP => '认证跳过',
    ];

    /**
     * 用户app认证列表标识
     */
    /** 身份认证 */
    const AUTH_IDENTITY = 'AUTH_IDENTITY';
    /** 个人信息 */
    const AUTH_BASE = 'AUTH_BASE';
    /**  运营商认证 */
    const AUTH_TELEPHONE = 'AUTH_TELEPHONE';
    /** 芝麻信用 */
    const AUTH_ZHIMA = 'AUTH_ZHIMA';
    /** 支付宝 */
    const AUTH_ALIPAY = 'AUTH_ALIPAY';

    /** 用户扩展信息 */
    const AUTH_USER_INFO = 'AUTH_USER_INFO';
    /** 紧急联系人 */
    const AUTH_CONTACTS = 'AUTH_CONTACTS';
    /** 工作信息 */
    const AUTH_USER_WORK = 'AUTH_USER_WORK';
    /** facebook验证 */
    const AUTH_FACEBOOK = 'AUTH_FACEBOOK';
    /** 银行卡账单 */
    const AUTH_BANK_BILL = 'AUTH_BANK_BILL';
    /** OLA认证 */
    const AUTH_OLA = 'AUTH_OLA';
    /** 银行卡绑定 */
    const AUTH_BANK_CARD = 'AUTH_BANK_CARD';

    /**
     * 列表标识
     * name => title
     */
    const AUTH = [
        self::AUTH_IDENTITY => '身份认证',
        self::AUTH_BASE => '个人信息',
        //self::AUTH_TELEPHONE => '运营商认证',
        //self::AUTH_ZHIMA => '芝麻信用',
        //self::AUTH_ALIPAY => '支付宝',
        self::AUTH_CONTACTS => '紧急联系人',
        //self::AUTH_USER_WORK => '工作信息',
        //self::AUTH_FACEBOOK => 'facebook验证',
        //self::AUTH_BANK_BILL => '银行卡账单',
        //self::AUTH_OLA => 'OLA认证',
        self::AUTH_USER_INFO => '工作信息',//用户扩展信息认证整合了工作信息
        self::AUTH_BANK_CARD => '银行卡信息',
    ];

    /** 运营商认证 白骑士认证 */
    const AUTH_TELEPHONE_PROVIDER_BQS = 'AUTH_TELEPHONE_PROVIDER_BQS';

    /** 运营商认证 新颜认证 */
    const AUTH_TELEPHONE_PROVIDER_XINYAN = 'AUTH_TELEPHONE_PROVIDER_XINYAN';


    const SCENARIO_SET = 'set';

    /** ocr识别类型 */
    const OCR_TYPE = [
        self::TYPE_PAN_CARD,
        self::TYPE_AADHAAR_CARD_FRONT,
        self::TYPE_AADHAAR_CARD_BACK,
        self::TYPE_ADDRESS_VOTER,
        self::TYPE_ADDRESS_DRIVING,
        self::TYPE_ADDRESS_AADHAAR,
        self::TYPE_ADDRESS_PASSPORT,
    ];

    /** CARD类型 */
    const CARD_TYPE = [
        self::TYPE_PAN_CARD,
        self::TYPE_AADHAAR_CARD,
        self::TYPE_ADDRESS_VOTER,
        //self::TYPE_ADDRESS_DRIVING,
        //self::TYPE_ADDRESS_AADHAAR,
        self::TYPE_ADDRESS_PASSPORT,
    ];

    const ADDRESS_CARD_TYPE = [
        self::TYPE_ADDRESS_VOTER,
        self::TYPE_ADDRESS_DRIVING,
        self::TYPE_ADDRESS_AADHAAR,
        self::TYPE_ADDRESS_PASSPORT,
    ];

    /**
     * 初审回退 具体查看 Params::getFirstApprove('base_detail_failed_list')
     */
    const BASE_DETAIL_FAILED_LIST = [
        1 => self::TYPE_PAN_CARD,
        2 => self::TYPE_ADDRESS_VOTER,
        3 => self::TYPE_ADDRESS_VOTER,
        4 => self::TYPE_ADDRESS_DRIVING,
        5 => self::TYPE_ADDRESS_DRIVING,
        6 => self::TYPE_AADHAAR_CARD,
        7 => self::TYPE_AADHAAR_CARD,
        8 => self::TYPE_ADDRESS_PASSPORT,
        9 => self::TYPE_ADDRESS_PASSPORT,
        10 => self::TYPE_ADDRESS_PASSPORT,
        11 => self::TYPE_FACES,
        12 => self::TYPE_ADDRESS_DRIVING,
    ];

    /**
     * @var string
     */
    protected $table = 'user_auth';
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

    public function safes()
    {
        return [
            self::SCENARIO_SET => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id',
                'type',
                'time',
                'status' => self::STATUS_VALID,
                'auth_status',
                'quality',
            ],
        ];
    }

    public function textRules()
    {
        return [];
    }

    /**
     * 单个验证
     * @param $userId
     * @param $type
     * @return mixed
     */
    public function getAuth($userId, $type)
    {
        $condition = [
            'user_id' => $userId,
            'type' => $type,
            'status' => self::STATUS_VALID,
        ];
        return self::model()->where($condition)->first();
    }

    public function getAuths($userId, $types)
    {
        $condition = [
            'user_id' => $userId,
            'status' => self::STATUS_VALID,
        ];
        $query = self::model()->where($condition);
        $query->whereIn('type', $types);
        return $query->get();
    }

    /**
     * @param $userId
     * @param $type
     * @return bool
     */
    public function clearAuth($userId, $type)
    {
        $where = [
            'user_id' => $userId,
            'status' => self::STATUS_VALID,
        ];
        $type = (array)$type;
        return self::query()->where($where)
            ->whereIn('type', $type)
            ->update(['status' => self::STATUS_INVALID]);
    }

    /**
     * 清除所有认证项
     *
     * @param $userId
     * @return bool
     */
    public function clearAllAuth($userId)
    {
        return $this->clearAuth($userId, static::AUTH);
    }

    /**
     * @param User $user
     * @param $type
     * @param $time
     * @param $authStatus
     * @return UserAuth|bool
     */
    public function setAuth(User $user, $type, $time, $authStatus)
    {
        $data = [
            'user_id' => $user->id,
            'merchant_id' => $user->merchant_id,
            'type' => $type,
            'time' => $time,
            'auth_status' => $authStatus,
            'quality' => $user->getRealQuality(),
        ];
        return self::model()->setScenario(self::SCENARIO_SET)->saveModel($data);
    }

    public function getAuthDetail($userId, $type)
    {
        $where = [
            'type' => $type,
            'user_id' => $userId,
        ];

        return self::query()->where($where)->first();
    }

    /**
     * 获取认证项
     */
    public static function getTypeIsCompleted()
    {
        return self::TYPE_IS_COMPLETED;
    }

    /**
     * 获取选填认证项
     * @return mixed
     */
    public static function getTypeOptionalIsCompleted()
    {
        return config('saas-business.user_optional_auth_type_is_completed');
    }
}
