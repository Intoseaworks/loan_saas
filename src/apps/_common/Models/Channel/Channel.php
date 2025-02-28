<?php

namespace Common\Models\Channel;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Channel\Channel
 *
 * @property int $id 自增长id
 * @property string|null $channel_code 渠道编码
 * @property string|null $channel_name 渠道名称
 * @property string|null $company 公司名字
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
 * @property int $cooperation_time 合作时间
 * @property string $url 推广地址
 * @property int $sort 排序
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereChannelCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereChannelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereCheckEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereCooperationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereCpa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereCps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereIsAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel wherePartnerAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel wherePartnerConfigDimension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel wherePartnerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel wherePartnerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel wherePreCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereSettlementWay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereUrl($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel orderByCustom($column = null, $direction = 'asc')
 * @property int|null $is_top 是否置顶
 * @property string|null $top_time 置顶时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereIsTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereTopTime($value)
 * @property int $merchant_id merchant_id
 * @property int $app_id app_id
 * @property string|null $page_name 包名
 * @property string|null $short_url 短链
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel wherePageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel whereShortUrl($value)
 */
class Channel extends Model
{
    use StaticModel;

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
    protected $table = 'channel';

    protected static function boot()
    {
        parent::boot();

        self::setAppIdOrMerchantIdBootScope();
    }

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

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

    public static function getNormalIds()
    {
        return self::whereStatus(self::STATUS_NORMAL)->pluck('id');
    }

    public static function getAllChannel()
    {
        return self::query()->get();
    }
}
