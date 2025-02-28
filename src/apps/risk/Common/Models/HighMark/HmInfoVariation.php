<?php

namespace Risk\Common\Models\HighMark;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\HighMark\HmInfoVariation
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string|null $report_id REPORT-ID
 * @property string|null $key
 * @property string|null $value
 * @property string|null $reported_date REPORTED DATE
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation query()
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereReportedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmInfoVariation whereValue($value)
 * @mixin \Eloquent
 */
class HmInfoVariation extends RiskBaseModel
{
    public $table = 'hm_info_variation';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];

    public static function clearByKey($appId, $userId, $relateId, $key)
    {
        $where = [
            'app_id' => $appId,
            'user_id' => $userId,
            'relate_id' => $relateId,
            'key' => $key,
        ];

        return self::query()->where($where)->delete();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [];
    }
}
