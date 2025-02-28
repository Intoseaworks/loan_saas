<?php

namespace Common\Models\Config;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Config\LoanMultipleConfig
 *
 * @property int $id
 * @property int $merchant_id merchant_id
 * @property int $parent_id 父id
 * @property int $number_start 起始复借次数
 * @property int $number_end 结束复借次数
 * @property string $loan_amount_range 借款金额选项范围
 * @property string $loan_days_range 借款期限选项范围
 * @property string $loan_daily_loan_rate 正常借款日息
 * @property string $penalty_rate 滞纳金日息
 * @property string $processing_rate 手续费费率
 * @property int $loan_again_days 可重借天数(天)
 * @property int $status 状态 1:正常 -1:删除
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property-read LoanMultipleConfig|null $child
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereLoanAgainDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereLoanAmountRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereLoanDailyLoanRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereLoanDaysRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereNumberEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereNumberStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig wherePenaltyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereProcessingRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanMultipleConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LoanMultipleConfig extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'loan_multiple_config';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

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

    const FIELD_NUMBER_START = 'number_start';
    const FIELD_NUMBER_END = 'number_end';
    // 借款金额范围
    const FIELD_LOAN_AMOUNT_RANGE = 'loan_amount_range';
    // 借款期限范围
    const FIELD_LOAN_DAYS_RANGE = 'loan_days_range';
    // 正常借款日息
    const FIELD_LOAN_DAILY_LOAN_RATE = 'loan_daily_loan_rate';
    // 罚息(滞纳金日息)
    const FIELD_PENALTY_RATE = 'penalty_rate';
    // 手续费费率(与贷款天数一一对应)
    const FIELD_PROCESSING_RATE = 'processing_rate';
    // 展期手续费率(与贷款天数一一对应)
    const FIELD_LOAN_RENEWAL_RATE = 'renewal_rate';
    // 滞纳金费率 数组 [5,5] 第一个是在第5天收取，第二个是收取比例5%
    const FIELD_LOAN_FORFEIT_PENALTY_RATE = 'forfeit_penalty_rate';
    // 可重借天数
    const FIELD_LOAN_AGAIN_DAYS = 'loan_again_days';
    # 罚息开始天数
    const FIELD_PENALTY_START_DAYS = "penalty_start_days";
    # 罚息结束天数
    const FIELD_PENALTY_END_DAYS = "penalty_end_days";
    # 罚息率
    const FIELD_PENALTY_RATES = "penalty_rates";

    const FIELD_TYPE = [
        self::FIELD_NUMBER_START => 'int',
        self::FIELD_NUMBER_END => 'int',
        self::FIELD_LOAN_AMOUNT_RANGE => 'array',
        self::FIELD_LOAN_DAYS_RANGE => 'array',
        self::FIELD_LOAN_DAILY_LOAN_RATE => 'float',
        self::FIELD_PENALTY_RATE => 'float',
        self::FIELD_PROCESSING_RATE => 'array',
        self::FIELD_LOAN_FORFEIT_PENALTY_RATE => 'array',
        self::FIELD_LOAN_RENEWAL_RATE => 'array',
        self::FIELD_LOAN_AGAIN_DAYS => 'int',
        self::FIELD_PENALTY_START_DAYS => 'array',
        self::FIELD_PENALTY_END_DAYS => 'array',
        self::FIELD_PENALTY_RATES => 'array',
    ];

    const SCENARIO_CREATE = 'create';

    protected $hidden = [
        'child',
    ];

    protected static $configCache;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'number_start',
                'number_end',
                'loan_amount_range',
                'loan_days_range',
                'loan_daily_loan_rate',
                'penalty_rate',
                'processing_rate',
                'loan_again_days',
                'renewal_rate',
                'forfeit_penalty_rate',
                'status',
                'forfeit_penalty_rate',
                'penalty_start_days',
                'penalty_end_days',
                'penalty_rates'
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
    public static function updateOrCreateConfig($merchantId, $parentModel, $data)
    {
        if ($parentModel && $parentModel->exists) {
            $parentId = $parentModel->id;
            $numberStart = $parentModel->number_end + 1;
        } else {
            $parentId = 0;
            $numberStart = LoanMultipleConfig::HEADER_NUMBER_START;
        }

        $data[self::FIELD_NUMBER_START] = $numberStart;
        $data = self::fieldFormat($data);

        if ($data[self::FIELD_NUMBER_START] > $data[self::FIELD_NUMBER_END]) {
            throw new \Exception(self::FIELD_NUMBER_END . ' not less than' . self::FIELD_NUMBER_START);
        }

        return self::updateOrCreateModel(self::SCENARIO_CREATE, [
            'merchant_id' => $merchantId,
            'parent_id' => $parentId,
            'status' => self::STATUS_NORMAL,
        ], $data);
    }

    /**
     * 格式化字段
     * @param array $arr
     * @return array
     */
    protected static function fieldFormat(array $arr)
    {
        $arr = array_only($arr, array_keys(self::FIELD_TYPE));

        foreach ($arr as $k => &$v) {
            $type = array_get(LoanMultipleConfig::FIELD_TYPE, $k);
            if ($type == 'array') {
                $v = json_encode($v);
            } elseif ($type == 'float') {
                // 此处将数据格式化成数据库对应的 decimal(10,4) 格式，不然在update判断model是否有更新时=>更新updated_at时会不准确
                $v = number_format($v, '6', '.', '');
            }
        }

        return $arr;
    }

    /**
     * 反向格式化数据
     * @param $arr
     * @return mixed
     */
    protected static function fieldReversFormat($arr)
    {
        foreach ($arr as $k => &$v) {
            $type = array_get(LoanMultipleConfig::FIELD_TYPE, $k);
            if ($type == 'array') {
                $v = json_decode($v, true);
            } elseif ($type == 'float') {
                // 暂时展示两位小数
                $v = number_format($v, '6', '.', '');
            }
        }

        return $arr;
    }

    /**
     * 删除配置链外的配置
     * @param $merchantId
     * @return int
     */
    public static function clearConfig($merchantId)
    {
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

    /**
     * 根据商户获取所有配置
     * @param $merchantId
     * @param bool $cache
     * @return array
     */
    public static function getConfigByMerchant($merchantId, $cache = false)
    {
        if (isset(self::$configCache[$merchantId]) && $cache) {
            return self::$configCache[$merchantId];
        }

        $where = [
            'merchant_id' => $merchantId,
            'parent_id' => 0,
            'number_start' => self::HEADER_NUMBER_START,
            'status' => self::STATUS_NORMAL,
        ];

        $configLink = static::query()->where($where)->first();

        $data = [];
        if (!$configLink) {
            return $data;
        }

        $config = $configLink;
        while ($config && $config->exists) {
            $res = self::fieldReversFormat($config->getOriginal());
            $res['loan_renewal_rate'] = $res['renewal_rate'] ?? [];#修复前后端数据不统一问题
            $res['penalty_rate'] = $res[self::FIELD_LOAN_FORFEIT_PENALTY_RATE][1] ?? 0;
            $res['penalty_rate_day'] = $res[self::FIELD_LOAN_FORFEIT_PENALTY_RATE][0] ?? 0;
            $res[self::FIELD_PENALTY_START_DAYS] = $res[self::FIELD_PENALTY_START_DAYS] ?? [];
            $res[self::FIELD_PENALTY_END_DAYS] = $res[self::FIELD_PENALTY_END_DAYS] ?? [];
            $res[self::FIELD_PENALTY_RATES] = $res[self::FIELD_PENALTY_RATES] ?? [];
            $data[] = $res;

            $config = $config->child;
        }
        if ($cache) {
            self::$configCache[$merchantId] = $data;
        }

        return $data;
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public function getNormalById($id)
    {
        $where = [
            'id' => $id,
            'status' => self::STATUS_NORMAL,
        ];

        return self::query()->where($where)->first();
    }

    public function toDelete()
    {
        // 拥有子节点 || 为第一条记录 不能删除
        if ($this->child || $this->parent_id == 0) {
            return false;
        }
        $this->status = self::STATUS_DELETE;
        return $this->save();
    }

    /**
     * 根据复贷次数获取对应配置
     * @param $merchantId
     * @param $repeatCnt
     * @param null $key
     * @return mixed
     * @throws \Exception
     */
    public static function getConfigByCnt($merchantId, $repeatCnt, $key = null)
    {
        $configs = collect(self::getConfigByMerchant($merchantId, true));

        if ($configs->isEmpty()) {
            throw new \Exception('贷款配置为空');
        }

        $config = $configs->where(self::FIELD_NUMBER_START, '<=', $repeatCnt)
            ->where(self::FIELD_NUMBER_END, '>=', $repeatCnt)
            ->first();

        // 复贷次数大于配置的最大天数，取最后一条
        if (!$config && $configs->max(self::FIELD_NUMBER_END) < $repeatCnt) {
            $config = $configs->last();
        }

        if (!$config) {
            throw new \Exception('贷款配置匹配为空');
        }

        if (isset($key)) {
            return array_get($config, $key);
        }

        return $config;
    }

    /**
     * 子节点
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function child()
    {
        return $this->hasOne(self::class, 'parent_id', 'id')
            ->where('status', self::STATUS_NORMAL);
    }
}
