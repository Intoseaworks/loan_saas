<?php

namespace Common\Models\Statistics;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Statistics\StatisticsLog
 *
 * @property int $id id
 * @property int|null $user_id 用户id
 * @property string $statistics 统计
 * @property float $quota 数值
 * @property string $client_id 注册终端 Android/ios/h5
 * @property string $quality 用户质量 1为老用户 0为新用户 2为当天注册用户
 * @property string $channel_id 注册渠道
 * @property string $platform 下载平台
 * @property int $status 统计记录状态 1:启用 -1:删除
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property string|null $created_time 统计日期
 * @property string|null $created_date 统计日期
 * @property int $created_hour 创建时间(小时)
 * @property int $created_day 创建时间(天)
 * @property int $created_week 创建时间(周)
 * @property int $created_month 创建时间(月)
 * @property int $created_year 创建时间(年)
 * @property string|null $user_register_time
 * @property string|null $user_register_date
 * @property int $user_register_hour 创建时间(小时)
 * @property int $user_register_day 用户注册日期
 * @property int $user_register_week 用户注册日期(周)
 * @property int $user_register_month 用户注册日期(月份)
 * @property int $user_register_year 用户注册日期(年)
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereCreatedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereCreatedDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereCreatedHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereCreatedMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereCreatedWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereCreatedYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereQuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereStatistics($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUserRegisterDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUserRegisterDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUserRegisterHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUserRegisterMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUserRegisterTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUserRegisterWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereUserRegisterYear($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Statistics\StatisticsLog whereMerchantId($value)
 */
class StatisticsLog extends Model
{
    use StaticModel;

    const STATISTICS_REGISTER = 'register';
    const STATISTICS_AUTH = 'auth';
    const STATISTICS_LOAN = 'loan';
    const STATISTICS_PASS = 'pass';
    const STATISTICS_REMIT = 'remit';
    const STATISTICS_REPAY = 'repay';
    const STATISTICS_OVERDUE = 'overdue';

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
    protected $table = 'statistics_log';
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

    public function textRules()
    {
        return [];
    }

    public function deleteLog($statistics, $startTime, $endTime)
    {
        return StatisticsLog::where('statistics', $statistics)
            ->whereBetween('created_time', [$startTime, $endTime])
            ->update(['status' => self::STATUS_DELETE]);
    }

    public function addLog($datas)
    {
        return StatisticsLog::insert($datas);
    }

}
