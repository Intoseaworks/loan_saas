<?php

namespace Common\Models\Statistics;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Statistics\StatisticsData
 *
 * @property int $id id
 * @property string $date 统计日期
 * @property string $statistics 统计
 * @property int $count
 * @property float $quota 数值
 * @property string $client_id 注册终端 Android/ios/h5
 * @property string $quality 用户质量 1为老用户 0为新用户 2为当天注册用户
 * @property string $channel_id 注册渠道
 * @property string $platform 下载平台
 * @property int $status 统计记录状态 1:启用 -1:删除
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereQuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereStatistics($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsData whereMerchantId($value)
 */
class StatisticsData extends Model
{
    use StaticModel;

    const TYPE_REGISTER = 'register';
    const TYPE_AUTH = 'auth';
    const TYPE_LOAN = 'loan';
    const TYPE_PASS = 'pass';
    const TYPE_REMIT = 'remit';
    const TYPE_REMIT_AMOUNT = 'remit_amount';
    const TYPE_REPAY = 'repay';
    const TYPE_OVERDUE = 'overdue';

    const STATISTICS_REGISTER_COUNT = 'register_count';
    const STATISTICS_AUTH_COUNT = 'auth_count';
    const STATISTICS_LOAN_COUNT = 'loan_count';
    const STATISTICS_PASS_COUNT = 'pass_count';
    const STATISTICS_REMIT_COUNT = 'remit_count';
    const STATISTICS_REPAY_COUNT = 'repay_count';
    const STATISTICS_OVERDUE_COUNT = 'overdue_count';
    const STATISTICS_REMIT_SUM = 'remit_sum';
    const STATISTICS_REPAY_SUM = 'repay_sum';
    const STATISTICS_OVERDUE_SUM = 'overdue_sum';

    const QUALITY_NEW_USER = 0;  //新用户
    const QUALITY_OLD_USER = 1;  //老用户
    const QUALITY_REGISTER_USER = 2;//当天注册用户

    /** @var int 正常 */
    const STATUS_NORMAL = 1;
    /** @var int 删除 */
    const STATUS_DELETE = -1;

    /**
     * @var string
     */
    protected $table = 'statistics_data';
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

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public function deleteData($where)
    {
        return StatisticsData::where($where)
            ->update(['status' => self::STATUS_DELETE]);
    }

    public function addData($datas)
    {
        return StatisticsData::insert($datas);
    }

}
