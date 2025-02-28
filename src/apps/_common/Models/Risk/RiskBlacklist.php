<?php

namespace Common\Models\Risk;

use Common\Models\Order\Order;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Risk\RiskBlacklist
 *
 * @property int $id id
 * @property int|null $merchant_id 商户ID
 * @property string|null $keyword 黑名单主键
 * @property string|null $value hit值
 * @property string|null $black_reason 入黑来源
 * @property int|null $apply_id 申请ID
 * @property string|null $related_info 关联信息
 * @property int|null $is_global 是否全局
 * @property int|null $status 状态
 * @property string|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist query()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereApplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereBlackReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereIsGlobal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereRelatedInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskBlacklist whereValue($value)
 * @mixin \Eloquent
 */
class RiskBlacklist extends Model
{
    use StaticModel;

    const SCENARIO_CREATE = 'create';
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;
    const IS_GLOBAL_YES = 'Y';
    const IS_GLOBAL_NO = 'N';

    const TYPE_OVERDUE = '贷后逾期入黑'; //定时任务触发
    const TYPE_REFUSAL_CODE = '贷前拒绝入黑'; //风控机审/人审拒绝码入黑
    const TYPE_RELATE = '关联入黑'; //风控机审
    const TYPE_OFFLINE_IMPORT_FAKE = '线下导入-欺诈'; //后台导入
    const TYPE_OFFLINE_IMPORT_EXTERNAL_OVERDUE = '线下导入-外部逾期'; //后台导入
    const TYPE_OFFLINE_IMPORT_SUSPECTED_FAKE = '线下导入-疑似欺诈'; //后台导入
    const TYPE_COMPLAINT_USER = "投诉用户";
    const TYPE_ALIAS = [
        self::TYPE_OVERDUE => '贷后逾期入黑',
        self::TYPE_REFUSAL_CODE => '贷前拒绝入黑',
        self::TYPE_RELATE => '关联入黑',
        self::TYPE_OFFLINE_IMPORT_FAKE => '线下导入-欺诈',
        self::TYPE_OFFLINE_IMPORT_EXTERNAL_OVERDUE => '线下导入-外部逾期',
        self::TYPE_OFFLINE_IMPORT_SUSPECTED_FAKE => '线下导入-疑似欺诈',
        self::TYPE_COMPLAINT_USER => "投诉用户",
    ];

    const KEYWORD_TELEPHONE = 'TELEPHONE'; //手机号
    const KEYWORD_CONTACT_TELEPHONE = 'CONTACT_TELEPHONE'; //紧急联系人
    const KEYWORD_ID_CARD_NO = 'ID_CARD_NO'; //证件号
    const KEYWORD_EMAIL = 'EMAIL'; //邮箱
    const KEYWORD_BANKCARD = 'BANKCARD'; //银行卡
    const KEYWORD_DEVICE_ID = 'DEVICE_ID'; //设备ID
    const KEYWORD_COOKIE_ID = 'COOKIE_ID'; //CookieID
    const KEYWORD_ADVERTISING_ID = 'ADVERTISING_ID'; //广告ID
    const KEYWORD_PERSISTENT_DEVICE_ID = 'PERSISTENT_DEVICE_ID'; //永久性设备ID
    const KEYWORD_NAME_BIRTHDAY = 'NAME_BIRTHDAY'; //姓名|生日 组合
    const KEYWORD_IP = 'IP'; //IP
    const KEYWORD_BANKCARD_CUT3 = 'BANKCARD_CUT3'; //银行卡去掉后3位
    const KEYWORD_BANKCARD_CUT4 = 'BANKCARD_CUT4'; //银行卡去掉后4位
    const KEYWORD_BANKCARD_CUT5 = 'BANKCARD_CUT5'; //银行卡去掉后5位
    const KEYWORD_BANKCARD_CUT6 = 'BANKCARD_CUT6'; //银行卡去掉后6位
    const KEYWORD_WORK_TELEPHONE = 'WORK_TELEPHONE'; //单位电话

    /**
     * 黑名单别名 黑名单匹配顺序按照数组顺序进行
     */
    const KEYWORD_ALIAS = [
        self::KEYWORD_ID_CARD_NO => '证件号',
        self::KEYWORD_BANKCARD => '银行卡',
        self::KEYWORD_BANKCARD_CUT3 => '银行卡去掉后3位',
        self::KEYWORD_BANKCARD_CUT4 => '银行卡去掉后4位',
        self::KEYWORD_BANKCARD_CUT5 => '银行卡去掉后5位',
        self::KEYWORD_BANKCARD_CUT6 => '银行卡去掉后6位',
        self::KEYWORD_IP => 'IP',
        self::KEYWORD_DEVICE_ID => '设备ID',
        self::KEYWORD_COOKIE_ID => 'CookieID',
        self::KEYWORD_ADVERTISING_ID => '广告ID',
        self::KEYWORD_PERSISTENT_DEVICE_ID => '永久性设备ID',
        self::KEYWORD_TELEPHONE => '手机号',
        self::KEYWORD_WORK_TELEPHONE => '单位电话',
        self::KEYWORD_CONTACT_TELEPHONE => '紧急联系人',
        self::KEYWORD_EMAIL => '邮箱',
        self::KEYWORD_NAME_BIRTHDAY => '姓名|生日组合',
    ];

    /**
     * 银行关键字集合
     */
    const KEYWORD_BANKCARD_GROUP = [
        self::KEYWORD_BANKCARD,
        self::KEYWORD_BANKCARD_CUT3,
        self::KEYWORD_BANKCARD_CUT4,
        self::KEYWORD_BANKCARD_CUT5,
        self::KEYWORD_BANKCARD_CUT6,
    ];

    /**
     * 系统黑名单字段
     */
    const KEYWORD_SYSTEM = [
        self::KEYWORD_TELEPHONE,
        self::KEYWORD_CONTACT_TELEPHONE,
        self::KEYWORD_ID_CARD_NO,
        self::KEYWORD_EMAIL,
        self::KEYWORD_BANKCARD,
        #暂停cookie & device
        #self::KEYWORD_DEVICE_ID,
        #self::KEYWORD_COOKIE_ID,
        #self::KEYWORD_ADVERTISING_ID,
        #self::KEYWORD_PERSISTENT_DEVICE_ID,
        self::KEYWORD_NAME_BIRTHDAY,
    ];

    /**
     * 线下导入额外字段
     */
    const KEYWORD_MANUAL = [
        self::KEYWORD_IP,//IP
        self::KEYWORD_BANKCARD_CUT3, //银行卡去掉后3位
        self::KEYWORD_BANKCARD_CUT4, //银行卡去掉后4位
        self::KEYWORD_BANKCARD_CUT5, //银行卡去掉后5位
        self::KEYWORD_BANKCARD_CUT6, //银行卡去掉后5位
        self::KEYWORD_WORK_TELEPHONE, //单位电话
    ];

    /**
     * @var string
     */
    public $table = 'risk_blacklist';

    /**
     * @var bool
     */
    public $timestamps = false;
    protected $primaryKey = 'id';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $dates = [];

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                "merchant_id" => MerchantHelper::getMerchantId(),
                "keyword",
                "value",
                "black_reason",
                "apply_id",
                "related_info",
                "is_global",
                "status" => self::STATUS_ACTIVE,
                "created_at" => DateHelper::dateTime(),
            ],
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * 显示场景过滤
     * @return array
     */
    public function texts()
    {
        return [
        ];
    }

    /**
     * 显示非过滤
     * @return array
     */
    public function unTexts()
    {
        return [
        ];
    }

    /**
     * 显示规则
     * @return array
     */
    public function textRules()
    {
        return [
            'array' => [
                'keyword' => self::KEYWORD_ALIAS,
            ],
        ];
    }

    public function create($data)
    {
        $keyword = array_get($data, 'keyword');
        $value = array_get($data, 'value');
        $isGlobal = array_get($data, 'is_global');
        if (empty($value)) return false;
        /** 对于当前已经在库的黑名单值不需导入，但如果本次导入的is_global为"Y"，系统原有记录值为'N'，则把系统原有记录定位失效，并把本次记录导入到系统 */
        $exist = self::whereKeyword($keyword)->whereValue($value)->whereStatus(self::STATUS_ACTIVE)->withoutGlobalScopes()->first();
        if ($exist) {
            if ($isGlobal == $exist->is_global || $exist->is_global == self::IS_GLOBAL_YES) {
                return false;
            } elseif ($isGlobal == self::IS_GLOBAL_YES) {
                $exist->status = self::STATUS_DISABLE;
                $exist->save();
            }
        }
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    public function batchAdd($data)
    {
        $success = 0;
        foreach ($data as $item) {
            $this->create($item) ? $success++ : null;
        }
        return $success;
    }

    /**
     * 搜索构造器
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search($params)
    {
        $keyword = array_get($params, 'keyword');
        $value = array_get($params, 'value');
        $isGlobal = array_get($params, 'is_global');
        $applyId = array_get($params, 'apply_id');
        $blackReason = array_get($params, 'black_reason');
        $query = self::query()->whereStatus(self::STATUS_ACTIVE);
        if ($keyword) {
            $query->whereKeyword($keyword);
        }
        if ($value) {
            $query->whereValue($value);
        }
        if ($isGlobal) {
            $query->whereIsGlobal($isGlobal);
        }
        if ($applyId) {
            $query->whereApplyId($applyId);
        }
        if ($blackReason) {
            $query->whereBlackReason($blackReason);
        }
        return $query->orderBy('id', 'desc');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'apply_id');
    }

    public function hitBlackList($params) {
        $res = [];
        foreach ($params as $key => $val) {
            if (isset($res[$key])) {
                continue;
            }
            if(in_array($key, ["bank_card_type","id_card_type", 'access_name', 'secret'])){
                continue;
            }
            if(!$val){
                continue;
            }
            $res[$key] = -1;
            switch ($key) {
                case "id_card_no":
                    if ($this->newQuery()->whereStatus(self::STATUS_ACTIVE)
                                    ->where("is_global", "Y")
                                    ->where("keyword", self::KEYWORD_ID_CARD_NO)
                                    ->where("value", $val)->exists()) {
                        $res[$key] = 1;
                    } else {
                        $res[$key] = 0;
                    }
                    break;
                case "email":
                    if ($this->newQuery()->whereStatus(self::STATUS_ACTIVE)
                                    ->where("is_global", "Y")
                                    ->where("keyword", self::KEYWORD_EMAIL)
                                    ->where("value", $val)->exists()) {
                        $res[$key] = 1;
                    } else {
                        $res[$key] = 0;
                    }
                    break;
                case "telephone":
                    if ($this->newQuery()->whereStatus(self::STATUS_ACTIVE)
                                    ->where("is_global", "Y")
                                    ->where("keyword", self::KEYWORD_TELEPHONE)
                                    ->where("value", $val)->exists()) {
                        $res[$key] = 1;
                    } else {
                        $res[$key] = 0;
                    }
                    break;
                case "fullname_birthday":
                    if ($this->newQuery()->whereStatus(self::STATUS_ACTIVE)
                                    ->where("is_global", "Y")
                                    ->where("keyword", self::KEYWORD_NAME_BIRTHDAY)
                                    ->where("value", $val)->exists()) {
                        $res[$key] = 1;
                    } else {
                        $res[$key] = 0;
                    }
                    break;
                case "bank_card_no":
                    $keyword = self::KEYWORD_BANKCARD;
                    if (isset($params['bank_card_type'])) {
                        switch ($params['bank_card_type']) {
                            case "3":
                                $keyword = self::KEYWORD_BANKCARD_CUT3;
                                break;
                            case "4":
                                $keyword = self::KEYWORD_BANKCARD_CUT4;
                                break;
                            case "5":
                                $keyword = self::KEYWORD_BANKCARD_CUT5;
                                break;
                            case "6":
                                $keyword = self::KEYWORD_BANKCARD_CUT6;
                                break;
                        }
                    }
                    if ($this->newQuery()->whereStatus(self::STATUS_ACTIVE)
                                    ->where("is_global", "Y")
                                    ->where("keyword", $keyword)
                                    ->where("value", $val)->exists()) {
                        $res[$key] = 1;
                    } else {
                        $res[$key] = 0;
                    }
                    break;
                case "ip":
                    if ($this->newQuery()->whereStatus(self::STATUS_ACTIVE)
                                    ->where("is_global", "Y")
                                    ->where("keyword", self::KEYWORD_IP)
                                    ->where("value", $val)->exists()) {
                        $res[$key] = 1;
                    } else {
                        $res[$key] = 0;
                    }
                    break;
            }
        }

        return $res;
    }

}
