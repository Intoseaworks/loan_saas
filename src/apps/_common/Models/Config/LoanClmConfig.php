<?php

namespace Common\Models\Config;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Config\LoanClmConfig
 *
 * @property int $id
 * @property int $merchant_id merchant_id
 * @property string|null $name
 * @property int|null $days 贷款天数
 * @property string $daily_loan_rate 日利率(/天)
 * @property string $penalty_rate 罚息率(/天)
 * @property string $service_rate_front 前置服务费率
 * @property string|null $service_rate_front_gst 前置服务费税率
 * @property string|null $service_rate_back 后置服务费
 * @property string|null $service_rate_back_gst 后置服务费税率
 * @property string|null $defer_rate 展期费率
 * @property int $status 状态 1:正常 -1:删除
 * @property int|null $is_default 是否为默认费率（1=默认）
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereDailyLoanRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereDeferRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig wherePenaltyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereServiceRateBack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereServiceRateBackGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereServiceRateFront($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereServiceRateFrontGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LoanClmConfig extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'loan_clm_config';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = ['merchant_id',
                'name',
                'days',
                'daily_loan_rate',
                'penalty_rate',
                'service_rate_front',
                'service_rate_front_gst',
                'service_rate_back',
                'service_rate_back_gst',
                'defer_rate',];

    /** 状态：正常 */
    const STATUS_NORMAL = 1;

    /** 状态：删除 */
    const STATUS_DELETE = -1;

    /** 状态 */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DELETE => '删除',
    ];
    const HEADER_NUMBER_START = 0;
    // 借款金额范围
    const FIELD_NAME = 'name';
    //贷款天数
    const FIELD_DAYS = 'days';
    //日利率(/天)
    const FIELD_DAILY_LOAN_RATE = 'daily_loan_rate';
    //罚息率(/天)
    const FIELD_PENALTY_RATE = 'penalty_rate';
    //前置服务费率
    const FIELD_SERVICE_RATE_FRONT = 'service_rate_front';
    //前置服务费税率
    const FIELD_SERVICE_RATE_FRONT_GST = 'service_rate_front_gst';
    //后置服务费
    const FIELD_SERVICE_RATE_BACK = 'service_rate_back';
    //后置服务费税率
    const FIELD_SERVICE_RATE_BACK_GST = 'service_rate_back_gst';
    //展期费率
    const FIELD_DEFER_RATE = 'defer_rate';
    const FIELD_TYPE = [
        self::FIELD_NAME => 'string',
        self::FIELD_DAYS => 'int',
        self::FIELD_DAILY_LOAN_RATE => 'float',
        self::FIELD_PENALTY_RATE => 'float',
        self::FIELD_SERVICE_RATE_FRONT => 'float',
        self::FIELD_SERVICE_RATE_FRONT_GST => 'float',
        self::FIELD_SERVICE_RATE_BACK => 'float',
        self::FIELD_SERVICE_RATE_BACK_GST => 'float',
        self::FIELD_DEFER_RATE => 'int',
    ];
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    protected $hidden = [];

    public function safes() {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'name',
                'days',
                'daily_loan_rate',
                'penalty_rate',
                'service_rate_front',
                'service_rate_front_gst',
                'service_rate_back',
                'service_rate_back_gst',
                'defer_rate',
            ],
            self::SCENARIO_UPDATE => [
                'merchant_id',
                'name',
                'days',
                'daily_loan_rate',
                'penalty_rate',
                'service_rate_front',
                'service_rate_front_gst',
                'service_rate_back',
                'service_rate_back_gst',
                'defer_rate',
            ]
        ];
    }

    /**
     * 更新or创建配置
     * @param $merchantId
     * @param static|null $parentModel
     * @param $data
     * @return LoanMultipleConfig
     */
    public static function updateOrCreateConfig($merchantId, $data) {
        $data['merchant_id'] = $merchantId;
        $data['status'] = self::STATUS_NORMAL;
        if ($model = self::model()->getNormalByName($data['name'])) {
            $res = $model->update($data);
            return $res;
        }
        return static::model()->setScenario(self::SCENARIO_CREATE)->saveModel($data);
    }

    /**
     * 格式化字段
     * @param array $arr
     * @return array
     */
    protected static function fieldFormat(array $arr) {
        $arr = array_only($arr, array_keys(self::FIELD_TYPE));

        foreach ($arr as $k => &$v) {
            $type = array_get(LoanMultipleConfig::FIELD_TYPE, $k);
            if ($type == 'array') {
                $v = json_encode($v);
            } elseif ($type == 'float') {
                // 此处将数据格式化成数据库对应的 decimal(10,4) 格式，不然在update判断model是否有更新时=>更新updated_at时会不准确
                $v = number_format($v, '4', '.', '');
            }
        }

        return $arr;
    }

    /**
     * 反向格式化数据
     * @param $arr
     * @return mixed
     */
    protected static function fieldReversFormat($arr) {
        foreach ($arr as $k => &$v) {
            $type = array_get(LoanMultipleConfig::FIELD_TYPE, $k);
            if ($type == 'array') {
                $v = json_decode($v, true);
            } elseif ($type == 'float') {
                // 暂时展示两位小数
                $v = number_format($v, '2', '.', '');
            }
        }

        return $arr;
    }

    /**
     * @param $name
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public function getNormalByName($name) {
        return $this->getNormal($name, 'name');
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public function getNormalById($id) {
        return $this->getNormal($id, 'id');
    }

    public function getNormal($value, $column) {
        $where = [
            $column => $value,
            'status' => self::STATUS_NORMAL,
        ];

        return self::query()->where($where)->first();
    }

    public function toDelete() {
        $this->status = self::STATUS_DELETE;
        return $this->save();
    }
    
    public static $configCache = [];

    /**
     * 根据商户获取所有配置
     * @param $merchantId
     * @param bool $cache
     * @return array
     */
    public static function getConfigByMerchant($merchantId, $cache = false) {
        if (isset(self::$configCache[$merchantId]) && $cache) {
            return self::$configCache[$merchantId];
        }

        $where = [
            'merchant_id' => $merchantId,
            'status' => self::STATUS_NORMAL,
        ];

        $configLink = static::query()->where($where)->get();

        $data = $configLink->toArray();


        return $data;
    }

    /**
     * 删除配置链外的配置
     * @param $merchantId
     * @return int
     */
    public static function clearConfig($merchantId) {
        $config = self::getConfigByMerchant($merchantId);
        $ids = array_column($config, 'id');

        $where = [
            'merchant_id' => $merchantId,
            'status' => self::STATUS_NORMAL,
        ];

        return self::query()->where($where)
                        ->whereNotIn('id', $ids)
                        ->update(['status' => self::STATUS_DELETE]);
    }

}
