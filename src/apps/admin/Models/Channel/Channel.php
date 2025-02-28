<?php

namespace Admin\Models\Channel;

use Common\Utils\Data\DateHelper;
use Common\Utils\Data\StatisticsHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

/**
 * Admin\Models\Channel\Channel
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereChannelCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereChannelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereCheckEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereCooperationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereCpa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereCps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereIsAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel wherePartnerAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel wherePartnerConfigDimension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel wherePartnerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel wherePartnerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel wherePreCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereSettlementWay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereUrl($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\Channel orderByCustom($column = null, $direction = 'asc')
 * @property int|null $is_top 是否置顶
 * @property string|null $top_time 置顶时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereIsTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereTopTime($value)
 * @property int $merchant_id merchant_id
 * @property int $app_id app_id
 * @property string|null $page_name 包名
 * @property string|null $short_url 短链
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel wherePageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\Channel whereShortUrl($value)
 */
class Channel extends \Common\Models\Channel\Channel
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';
    const SCENARIO_INFO = 'info';
    const SCENARIO_TOP = 'top';

    const SCENARIO_STATISTICS = 'statistics';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'app_id',
                'channel_name',
                'channel_code',
                'cooperation_time',
                'sort',
                'status' => self::STATUS_NORMAL,
                'url',
                'short_url',
                'page_name',
            ],
            self::SCENARIO_UPDATE => [
                'cooperation_time',
                'sort',
            ],
            self::SCENARIO_TOP => [
                'is_top',
                'top_time',
            ],
        ];
    }

    public function texts()
    {
        return [
            self::SCENARIO_LIST => [
                'id',
                'channel_name',
                'channel_code',
                'cooperation_time',
                'sort',
                'status',
                'url',
                'is_top',
                'top_time',
            ],
            self::SCENARIO_INFO => [
                'id',
                'channel_name',
                'channel_code',
                'is_top',
                'top_time',
            ],
            self::SCENARIO_STATISTICS => [
                'id',
                'channel_name',
                'channel_code',
                'url',
                'user_count',
                'order_count',
                'order_pass_count',
                'order_remit_count',
                'order_repay_count',
                'overdue_count',
                'bad_count',
                'loan_user_count',
                'app_version',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'status' => self::STATUS,
            ],
            'function' => [
                'user_count' => function () {
                    if ($this->scenario == self::SCENARIO_STATISTICS) {
                        $this->loan_rate = StatisticsHelper::percent($this->loan_user_count, $this->user_count);
                        $this->pass_rate = StatisticsHelper::percent($this->order_pass_count, $this->order_count);
                        $this->order_repay_rate = StatisticsHelper::percent($this->order_repay_count,
                            $this->order_remit_count);
                        $this->overdue_rate = StatisticsHelper::percent($this->overdue_count, $this->order_remit_count);
                        $this->bad_rate = StatisticsHelper::percent($this->bad_count, $this->order_remit_count);
                    }
                }
            ],
        ];
    }

    public function getChannelArr()
    {
        return Channel::query()->pluck('channel_name', 'id')->toArray();
    }

    /**
     * @param $param
     * @return Channel|\Illuminate\Database\Eloquent\Builder
     */
    public function search($param)
    {
        $keyword = array_get($param, 'keyword');
        $status = array_get($param, 'status');

        $query = $this->newQuery();
        $query->whereIn('status', [self::STATUS_NORMAL, self::STATUS_DISABLE]);
        //状态
        if (isset($status)) {
            $query->where('status', $status);
        }
        //关键词
        if (isset($keyword)) {
            $query->where(function ($query) use ($keyword) {
                $keyword = '%' . trim($keyword) . '%';
                $query->where('channel_code', 'like', $keyword);
                $query->orWhere('channel_name', 'like', $keyword);
            });
        }
        $query->orderBy('is_top', 'desc');
        $query->orderBy('top_time', 'desc');
        $query->orderBy('id', 'asc');

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchStatistics()
    {
        $query = self::query()->select(DB::raw("
channel_name
,channel_code
,url
,user.app_version
, count(user.id) as user_count
, count(order.id) as order_count 
, count(DISTINCT order.user_id) as loan_user_count
, sum(IF(order.pass_time, 1, 0)) as order_pass_count
, sum(IF(order.paid_time, 1, 0)) as order_remit_count
, sum(IF(repayment_plan.repay_time, 1, 0)) as order_repay_count
, sum(case when order.status = 'overdue' then 1 else 0 end) as overdue_count
, sum(case when order.status = 'collection_bad' then 1 else 0 end) as bad_count
        "));
        $query->leftJoin('user', 'user.channel_id', '=', 'channel.id');

        //$query->leftJoin('order', 'order.user_id', '=', 'user.id');
        $orderMaxId = $this::query()
            ->from('order')->select(DB::raw("MIN(id) AS oid, user_id"))->groupBy('user_id');
        $orderMax = $this::query()
            ->select('b.*')
            ->from('order as b')->leftJoinSub($orderMaxId, 'a', function ($join) {
                $join->on('a.user_id', '=', 'b.user_id');
            })->whereColumn('a.oid', '=', 'b.id');
        $query->leftJoinSub($orderMax, 'order', function ($join) {
            $join->on('order.user_id', '=', 'user.id');
        });

        $query->leftJoin('repayment_plan', 'repayment_plan.order_id', '=', 'order.id');
        return $query;
    }

    /**
     * @param $id
     * @return bool|int
     */
    public function delById($id)
    {
        return $this->whereId($id)->update(['status' => self::STATUS_DELETE]);
    }

    public function updateStatus($id, $status)
    {
        return $this->whereId($id)->update(['status' => $status]);
    }

    public function getAll($name)
    {
        return self::whereIn('status', [self::STATUS_NORMAL, self::STATUS_DISABLE])->pluck($name, $name);
    }

    /**
     * @param Channel $channel
     * @param $top
     * @return bool
     */
    public function updateTop(Channel $channel, $top)
    {
        $data = [
            'is_top' => $top,
            'top_time' => $top == self::IS_TOP ? DateHelper::dateTime() : null,
        ];
        return $channel->setScenario(self::SCENARIO_TOP)->update($data);
    }

}
