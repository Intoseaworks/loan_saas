<?php

namespace Risk\Common\Models\Business\Common;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\Common\Channel
 *
 * @property int $id 自增长id
 * @property int $app_id
 * @property int $business_app_id app_id
 * @property string|null $channel_code 渠道编码
 * @property string|null $channel_name 渠道名称
 * @property string|null $company 公司
 * @property string|null $cpa
 * @property string|null $cps
 * @property string|null $pre_cost 预设成本
 * @property string|null $percent 折扣比例
 * @property int|null $settlement_way 结算方式
 * @property string $email 渠道对接人邮箱
 * @property int $status 状态
 * @property string $check_email 内部审核邮箱
 * @property string|null $partner_name 合作方
 * @property string|null $partner_account 合作方帐号
 * @property string|null $partner_config_dimension 合作方维度配置
 * @property int|null $partner_user_id 合作方用户id
 * @property int|null $is_auth 是否授权 1授权 0未授权
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property string|null $cooperation_time 合作时间
 * @property string $url 推广地址
 * @property int $sort 排序
 * @property int|null $is_top 是否置顶
 * @property string|null $top_time 置顶时间
 * @property string|null $page_name 包名
 * @property string|null $short_url 短链
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|Channel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereBusinessAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereChannelCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereChannelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereCheckEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereCooperationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereCpa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereCps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereIsAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereIsTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel wherePageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel wherePartnerAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel wherePartnerConfigDimension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel wherePartnerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel wherePartnerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel wherePreCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereSettlementWay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereShortUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereTopTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereUrl($value)
 * @mixin \Eloquent
 */
class Channel extends BusinessBaseModel
{
    /** @var int 正常 */
    const STATUS_NORMAL = 1;
    /** @var int 禁用 */
    const STATUS_DISABLE = 2;
    /** @var int 删除 */
    const STATUS_DELETE = 3;
    /** @var array status */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    const NOT_TOP = 0;
    const IS_TOP = 1;
    const TOP = [
        self::NOT_TOP => '非置顶',
        self::IS_TOP => '置顶',
    ];

    /**
     * @var string
     */
    protected $table = 'common_channel';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'business_app_id',
        'channel_code',
        'channel_name',
        'company',
        'cpa',
        'cps',
        'pre_cost',
        'percent',
        'settlement_way',
        'email',
        'status',
        'check_email',
        'partner_name',
        'partner_account',
        'partner_config_dimension',
        'partner_user_id',
        'is_auth',
        'created_at',
        'updated_at',
        'cooperation_time',
        'url',
        'sort',
        'is_top',
        'top_time',
        'page_name',
        'short_url',
    ];

    public static function getNormalIds()
    {
        return self::whereStatus(self::STATUS_NORMAL)->pluck('id');
    }

    protected static function boot()
    {
        parent::boot();

        self::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public function getIdByCode($code)
    {
        return $this->whereChannelCode($code)->value('id');
    }

    public function getByCode($code)
    {
        return $this->whereChannelCode($code)->first();
    }
}
