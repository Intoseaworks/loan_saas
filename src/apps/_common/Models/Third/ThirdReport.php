<?php

namespace Common\Models\Third;

use Common\Traits\Model\StaticModel;
use Common\Utils\Data\StringHelper;
use Common\Utils\DingDing\DingHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Third\ThirdReport
 *
 * @property int $id
 * @property int $merchant_id 商户ID
 * @property int $user_id 用户ID
 * @property int $type 类型 1:征信报告
 * @property string $channel 调用渠道  HighMark
 * @property int|null $status 状态 1:正常
 * @property string|null $request_info 请求信息
 * @property string|null $report_body 报告主体
 * @property string|null $report_id 报告id
 * @property string|null $unique_no 唯一标识号
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereReportBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereRequestInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereUniqueNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdReport whereUserId($value)
 * @mixin \Eloquent
 */
class ThirdReport extends Model
{
    use StaticModel;

    protected $table = 'third_report';
    protected $fillable = [];
    protected $guarded = [];

    /** @var int 类型：征信报告 */
    const TYPE_CREDIT_REPORT = 1;

    /** @var string 渠道：HighMark */
    const CHANNEL_HIGH_MARK = 'HighMark';
    /** @var string 渠道：Experian */
    const CHANNEL_EXPERIAN = 'experian';

    /** @var int 状态：正常 */
    const STATUS_NORMAL = 1;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public static function getReport($userId, $type, $channel, $startTime = null)
    {
        $where = [
            'user_id' => $userId,
            'type' => $type,
            'channel' => $channel,
            'status' => self::STATUS_NORMAL,
        ];

        $query = self::query()->where($where)->orderBy('id', 'desc');

        if (isset($startTime)) {
            $query->where('created_at', '>=', $startTime);
        }

        return $query->first();
    }

    public static function getHmReportBody($userId, $startTime = null)
    {
        $model = ThirdReport::getReport($userId, ThirdReport::TYPE_CREDIT_REPORT, ThirdReport::CHANNEL_HIGH_MARK, $startTime);

        if (!$model) {
            return null;
        }

        if (!$report = StringHelper::xmlParser($model->report_body)) {
            DingHelper::notice(['user_id' => $userId], 'HM征信报告xml格式错误-请检查', DingHelper::AT_SOLIANG);
        }

        return $report;
    }
}
