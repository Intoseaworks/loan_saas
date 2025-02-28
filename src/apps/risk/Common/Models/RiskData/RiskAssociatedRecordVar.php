<?php

namespace Risk\Common\Models\RiskData;

use Illuminate\Support\Facades\DB;
use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\RiskData\RiskAssociatedRecordVar
 *
 * @property int $id
 * @property int $risk_associated_record_id 关联ID
 * @property string $type 类型
 * @property string|null $value 值
 * @property int|null $cnt 出现次数
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Risk\Common\Models\RiskData\RiskAssociatedRecord $riskAssociatedRecord
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar query()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar whereCnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar whereRiskAssociatedRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecordVar whereValue($value)
 * @mixin \Eloquent
 */
class RiskAssociatedRecordVar extends RiskBaseModel
{
    const TYPE_AADHAAR = 'aadhaar';
    const TYPE_PANCARD = 'pancard';
    const TYPE_TELEPHONE = 'telephone';
    const TYPE_BANKCARD = 'bankcard';
    const TYPE_VOTEID = 'voteid';
    const TYPE_PASSPORT = 'passport';
    const TYPE_IMEI = 'imei';
    const TYPE_EMAIL = 'email';
    const TYPE_ORDER_NO = 'order_no';
    const TYPE_ISSUED_ORDER_NO = 'issued_order_no';
    const TYPE_MAX_OVERDUE_DAYS = 'max_overdue_days';
    const TYPE_MAX_ISSUED_AMOUNT = 'max_issued_amount';
    const TYPE_APP_ID = 'app_id';
    const TYPE = [
        self::TYPE_AADHAAR,
        self::TYPE_PANCARD,
        self::TYPE_TELEPHONE,
        self::TYPE_BANKCARD,
        self::TYPE_VOTEID,
        self::TYPE_PASSPORT,
        self::TYPE_IMEI,
        self::TYPE_EMAIL,
        self::TYPE_ORDER_NO,
        self::TYPE_ISSUED_ORDER_NO,
        self::TYPE_MAX_OVERDUE_DAYS,
        self::TYPE_MAX_ISSUED_AMOUNT,
        self::TYPE_APP_ID,
    ];
    /** @var array 需要统计cnt的类型 */
    const NEED_CNT_TYPE = [
        self::TYPE_AADHAAR,
        self::TYPE_PANCARD,
        self::TYPE_TELEPHONE,
        self::TYPE_BANKCARD,
        self::TYPE_VOTEID,
        self::TYPE_PASSPORT,
        self::TYPE_IMEI,
        self::TYPE_EMAIL,
        self::TYPE_APP_ID,
    ];
    /**
     * @var array 关联纬度类型，前面索引表示优先级，数字越小优先级越高
     * 前面三个数字为一个维度，区分强弱联系项 [imei、email] 是弱联系项
     * 后面三个数组为同一维度下的 区分
     * 最终实现：不算弱联系项的情况下，匹配到的项越多优先级越高，匹配到相同项个数时，按下面权重相加比对
     */
    const ASSOCIATED_TYPE = [
        self::TYPE_AADHAAR => 100010,
        self::TYPE_PANCARD => 100009,
        self::TYPE_TELEPHONE => 100008,
        self::TYPE_BANKCARD => 100007,
        self::TYPE_VOTEID => 100006,
        self::TYPE_PASSPORT => 100005,
        self::TYPE_IMEI => 100010,
        self::TYPE_EMAIL => 100009,
    ];
    /** @var array 附加属性 */
    const APPEND_TYPE = [
        self::TYPE_ORDER_NO,
        self::TYPE_ISSUED_ORDER_NO,
        self::TYPE_APP_ID,
        self::TYPE_MAX_OVERDUE_DAYS,
        self::TYPE_MAX_ISSUED_AMOUNT,
    ];
    protected $table = 'risk_associated_record_var';
    protected $fillable = [];
    protected $guarded = [];

    public static function getRecordVarByValue(array $associatedValue)
    {
        // 匹配信息小于两个字段，不进行匹配
        if (count($associatedValue) <= 1) {
            throw new \Exception('too few matching fields');
        }

        $query = null;

//        foreach ($associatedValue as $type => $value) {
//            if (!$query) {
//                $query = self::query();
//            }
//
//            $query->orWhere(function (Builder $query) use ($type, $value) {
//                $query->where('type', $type)
//                    ->where('value', $value);
//            });
//        }

        // 使用 union 方式
        foreach ($associatedValue as $type => $value) {
            $weightV = array_get(self::ASSOCIATED_TYPE, $type, 0);

            $q = static::query()->select()
                ->addSelect(DB::raw("'{$weightV}' as weight"))
                ->where('type', $type)
                ->where('value', $value)
                ->whereHas('riskAssociatedRecord');
            if (!$query) {
                $query = $q;
            } else {
                $query->union($q);
            }
        }

        return $query->get();
    }

    public function riskAssociatedRecord()
    {
        return $this->belongsTo(RiskAssociatedRecord::class, 'risk_associated_record_id', 'id');
    }
}
