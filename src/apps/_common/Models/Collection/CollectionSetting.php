<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionSetting
 *
 * @property int $id
 * @property string $key 名称
 * @property string $value 值
 * @property int $status 配置状态
 * @property string $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting whereValue($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting whereMerchantId($value)
 * @property int|null $admin_id 操作人员id
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionSetting whereAdminId($value)
 */
class CollectionSetting extends Model {

    use StaticModel;

    const REDUCTION_SETTING_CANNOT = 'cannot';
    const REDUCTION_SETTING_OVERDUE = 'overdue';
    const REDUCTION_SETTING_OVERDUE_INTEREST = 'overdue_interest';
    const REDUCTION_SETTING_PRINCIPAL_INTEREST = 'principal_interest';
    const REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE40 = 'overdue_interest_service40';
    const REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE60 = 'overdue_interest_service60';
    const REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE80 = 'overdue_interest_service80';
    const REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE90 = 'overdue_interest_service90';
    const REDUCTION_SETTING = [
        self::REDUCTION_SETTING_CANNOT => '无减免规则',
        self::REDUCTION_SETTING_OVERDUE => '逾期息费',
        self::REDUCTION_SETTING_OVERDUE_INTEREST => '逾期息费和利息',
        self::REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE40 => '逾期息费、利息以及40%的服务费',
        self::REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE60 => '逾期息费、利息以及60%的服务费',
        self::REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE80 => '逾期息费、利息以及80%的服务费',
        self::REDUCTION_SETTING_OVERDUE_INTEREST_SERVICE90 => '逾期息费、利息以及90%的服务费',
    ];
    const KEY_RULE = 'rule'; //规则设置
    const KEY_OVERDUE_DAYS_TO_BAD = 'overdue_days_to_bad'; //逾期天数转坏账

    /**
     * 状态
     */
    const STATUS_NORMAL = 1;
    const STATUS_DELETE = -1;
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DELETE => '已删除',
    ];
    
    const TARGET_TYPE_RATE = "rate";
    const TARGET_TYPE_MONEY = "sum";
    const TARGET_TYPE = [
        self::TARGET_TYPE_RATE => "按户数比例",
        self::TARGET_TYPE_MONEY => "按金额",
    ];

    /**
     * @var string
     */
    protected $table = 'collection_setting';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

    /**
     * @var bool
     */
    //public $timestamps = false;

    protected static function boot() {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules() {
        return [];
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getSetting($key) {
        $query = self::where('status', self::STATUS_NORMAL);
        if ($key) {
            $query->where('key', $key);
        }
        return $query->pluck('value', 'key');
    }

    public function getSettingVal($key) {
        $setting = self::where('status', self::STATUS_NORMAL)
                        ->where('key', $key)->first();
        if (!$setting) {
            return '';
        }
        return $setting->value;
    }

    public function getSettingVals($key) {
        $settings = self::where('status', self::STATUS_NORMAL)
                        ->where('key', $key)->get();
        $res = [];
        foreach ($settings as $setting) {
            $res[$setting->merchant_id] = $setting->value;
        }
        return $res;
    }

}
