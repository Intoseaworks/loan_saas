<?php

namespace Risk\Common\Models\Business\User;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\User\UserAuth
 *
 * @property int $id 用户信息扩展自增长ID
 * @property int $app_id merchant_id
 * @property int $user_id 用户id
 * @property string $type 用户数据类型
 * @property string|null $time 用户数据时间
 * @property int $status 状态
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property int $auth_status
 * @property int $quality 新老用户 0新用户 1老用户
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereAuthStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereUserId($value)
 * @mixin \Eloquent
 */
class UserAuth extends BusinessBaseModel
{
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

    const TYPE = [
        self::TYPE_BASE_INFO => '个人信息',
        self::TYPE_CONTACTS => '紧急联系人',
        self::TYPE_FACES => '人脸识别',
        self::TYPE_AADHAAR_CARD_KYC => 'aadhaarKYC认证',
        self::TYPE_AADHAAR_CARD => 'aadhaar认证',
        self::TYPE_PAN_CARD => 'Pancard',
        self::TYPE_ADDRESS_VOTER => '选民证认证',
        self::TYPE_ADDRESS_PASSPORT => '护照认证',
        self::TYPE_USER_EXTRA_INFO => '扩展信息',
        self::TYPE_BANKCARD => '银行卡绑定',
    ];

    /**
     * 完善所需认证项
     */
    const TYPE_IS_COMPLETED = [
        self::TYPE_ID_FRONT,
        self::TYPE_ID_BACK,
        self::TYPE_ID_HANDHELD,
        self::TYPE_FACES,
        self::TYPE_BASE_INFO,
        self::TYPE_BANKCARD,
    ];

    const AUTH_CHECK_LIST = [
        self::TYPE_BASE_INFO => [
            'daysValid' => 90,//天
        ],
        self::TYPE_USER_EXTRA_INFO => [
            'daysValid' => 90,//天
        ],
        self::TYPE_TELEPHONE => [
            'daysValid' => 90,//天
        ],
        self::TYPE_PAN_CARD => [
            'daysValid' => 90,//天
        ],
        self::TYPE_AADHAAR_CARD_KYC => [
            'daysValid' => 90,//天
        ],
        self::TYPE_AADHAAR_CARD => [
            'daysValid' => 90,//天
        ],
        self::TYPE_FACES => [
            'daysValid' => 90,//天
        ],
        self::TYPE_ADDRESS_VOTER => [
            'daysValid' => 90,//天
        ],
        self::TYPE_ADDRESS_PASSPORT => [
            'daysValid' => 90,//天
        ],
        self::TYPE_USER_EXTRA_INFO => [
            'daysValid' => 90,//天
        ],
        self::TYPE_IDENTITY => [
            'daysValid' => 90,//天
        ],
        self::TYPE_CONTACTS => [
            'daysValid' => 90,//天
        ],
        self::TYPE_BANK_BILL => [
            'daysValid' => 90,//天
        ],
        self::TYPE_USER_WORK => [
            'daysValid' => 90,//天
        ],
        self::TYPE_ADDRESS => [
            'daysValid' => 90,//天
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
    public static $validate = [
        'data' => 'array',
        'data.*.id' => 'required|numeric', // 应用记录列ID
        'data.*.type' => 'required|string', // 认证项类型
        'data.*.time' => 'required|date', // 认证时间
        'data.*.status' => 'required|integer', // 认证项状态 1:有效 0:失效
        'data.*.created_at' => 'required|date', // 记录创建时间
        'data.*.updated_at' => 'required|date', // 记录修改时间
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_user_auth';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'type',
        'time',
        'status',
        'created_at',
        'updated_at',
        'auth_status',
        'quality',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function safes()
    {
        return [
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

    public function getAuthDetail($appId, $userId, $type)
    {
        $where = [
            'app_id' => $appId,
            'type' => $type,
            'user_id' => $userId,
        ];

        return self::query()->where($where)->first();
    }

    public function itemFormat($item)
    {
        if (!isset($item['status'])) {
            $item['status'] = 1;
        }
        if (!isset($item['auth_status'])) {
            $item['auth_status'] = 1;
        }
        return $item;
    }
}
