<?php

namespace Admin\Models\Trade;

use Admin\Models\Order\Order;
use Illuminate\Database\Eloquent\Builder;

/**
 * Admin\Models\Order\TradeLog
 *
 * @property int $id 交易记录自增长id
 * @property string $trade_platform 交易平台
 * @property int $user_id 用户id
 * @property int $master_related_id 业务主关联id (订单id)
 * @property string $master_business_no 主业务编号(订单编号)
 * @property string $batch_no 交易批次号
 * @property string $transaction_no 交易编号(生成唯一编号)
 * @property string $request_no 支付系统定义支付请求编号
 * @property string $trade_platform_no 第三方交易平台交易编号
 * @property string $business_type 业务类型 manual_remit:人工出款  repay:还款
 * @property int $trade_type 交易类型( 1:出款, 2:回款, 3:退款)
 * @property int $request_type 发起类型 1:系统  2:管理后台 3:用户
 * @property float $business_amount 业务金额
 * @property float $trade_amount 交易金额
 * @property string $bank_name 银行名称
 * @property string $trade_account_telephone 交易账户手机号(银行卡手机号、支付宝手机号)
 * @property string $trade_account_name 交易账户名(银行卡账户、支付宝姓名)
 * @property string $trade_account_no 交易账户号(用户银行卡、支付宝账户)
 * @property string $trade_desc 交易描述
 * @property string $trade_result_time 交易时间
 * @property string $trade_notice_time 交易通知时间
 * @property string $trade_settlement_time 清算时间
 * @property string $trade_request_time 请求时间
 * @property int $p_id 父id,退款用
 * @property int $admin_id 后台管理员id
 * @property string $handle_name 操作者名称
 * @property int $trade_evolve_status 交易进展状态
 * @property int $trade_result 交易结果(1成功,2失败)
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property \Illuminate\Support\Carbon $created_at 添加时间
 * @property-read \Admin\Models\Order\Order $order
 * @property-read \Admin\Models\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereBatchNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereBusinessAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereBusinessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereHandleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereMasterBusinessNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereMasterRelatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog wherePId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereRequestNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereRequestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeAccountTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeEvolveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeNoticeTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradePlatformNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeRequestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeResultTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeSettlementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTradeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereTransactionNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereUserId($value)
 * @mixin \Eloquent
 * @property int $admin_trade_account_id 关联 admin_pay_account
 * @property-read \Common\Models\Trade\AdminTradeAccount $adminTradeAccount
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereAdminTradeAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @property int $bank_card_id 银行卡id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereBankCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Trade\TradeLog whereMerchantId($value)
 * @property string|null $trade_result_code 交易结果CODE
 * @method static Builder|TradeLog whereTradeResultCode($value)
 * @property string|null $withdraw_no 取款码
 * @property string|null $reference_no 还款码
 * @method static Builder|TradeLog whereReferenceNo($value)
 * @method static Builder|TradeLog whereWithdrawNo($value)
 */
class TradeLog extends \Common\Models\Trade\TradeLog
{
    const SCENARIO_LIST = 'list';
    const SCENARIO_DETAIL = 'detail';

    public function texts()
    {
        return [
            self::SCENARIO_LIST => [
                'transaction_no',
                'trade_platform_no',
                'master_business_no',
                'trade_account_name',
                'trade_account_telephone',
                'trade_amount',
                'trade_account_no',
                'trade_result_time',
                'business_type',
                'request_no',
                'trade_platform',
                'trade_result',
                'out_trade_result_time',
                'in_trade_result_time',
                'renewal_result_time',
                'trade_request_time',
                'trade_desc',
                'created_at',
                'handle_name',
                'bank_name',
            ],
            self::SCENARIO_DETAIL => [
                'admin_trade_account_id',
                'master_business_no',
                'trade_account_name',
                'trade_account_telephone',
                'trade_amount',
                'trade_account_no',
                'trade_result_time',
                'business_type',
                'trade_platform',
                'trade_request_time',
                'request_no',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'trade_platform' => self::TRADE_PLATFORM,
                'business_type' => ts(self::BUSINESS_TYPE, 'pay'),
                'trade_type' => self::TRADE_TYPE,
                'request_type' => self::REQUEST_TYPE,
                'trade_evolve_status' => self::TRADE_EVOLVE_STATUS,
            ],
            'function' => [
                'trade_result' => function ($model) {
                    if ($model->trade_evolve_status == self::TRADE_EVOLVE_STATUS_UNUSED && $model->trade_result == self::TRADE_RESULT_NULL) {
                        return t(array_get(self::TRADE_EVOLVE_STATUS, $model->trade_evolve_status), 'pay');
                    }
                    return t(array_get(self::TRADE_RESULT, $model->trade_result), 'pay');
                }
            ],
        ];
    }

    /**
     * @param $params
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search($params, $with = [])
    {
        $query = self::query()->with($with);
        // 收款人姓名|手机号码
        $keywordUser = array_get($params, 'keyword_user');
        if (isset($keywordUser)) {
            $query->where(function ($query) use ($keywordUser) {
                $keywordUser = '%' . trim($keywordUser) . '%';
                $query->where('trade_account_name', 'like', $keywordUser);
                $query->orWhere('trade_account_telephone', 'like', $keywordUser);
            });
        }
        // 订单号/交易编号
        $keywordNo = array_get($params, 'keyword_no');
        if (isset($keywordNo)) {
            $keywordNo = '%' . trim($keywordNo) . '%';
            $query->where(function ($query) use ($keywordNo) {
                $query->where('master_business_no', 'like', $keywordNo);
                $query->orWhere('trade_platform_no', 'like', $keywordNo);
            });
        }
        // 打款时间,改为请求时间
        if ($tradeResultTime = array_get($params, 'trade_result_time')) {
            $tradeResultTimeStart = current($tradeResultTime);
            $tradeResultTimeEnd = last($tradeResultTime);
            $query->whereBetween('trade_request_time', [$tradeResultTimeStart, $tradeResultTimeEnd]);
        }
        // 还款时间
        if ($inTradeResultTime = array_get($params, 'in_trade_result_time')) {
            $inTradeResultTimeStart = current($inTradeResultTime);
            $inTradeResultTimeEnd = last($inTradeResultTime);
            $query->where('business_type', TradeLog::BUSINESS_TYPE_REPAY);
            $query->whereBetween('trade_result_time', [$inTradeResultTimeStart, $inTradeResultTimeEnd]);
        }
        // 交易平台
        if ($tradePlatform = array_get($params, 'trade_platform')) {
            $query->whereIn('trade_platform', (array)$tradePlatform);
        }
        // 业务类型
        if ($businessType = array_get($params, 'business_type')) {
            $query->whereIn('business_type', (array)$businessType);
        }
        // 请求类型
        if ($requestType = array_get($params, 'request_type')) {
            $query->where('request_type', $requestType);
        }
        // 交易类型
        if ($tradeType = array_get($params, 'trade_type')) {
            $query->where('trade_type', $tradeType);
        }
        // 支付状态
        $tradeResult = array_get($params, 'trade_result');
        if (isset($tradeResult)) {
            $tradeResult = (array)$tradeResult;
            if (in_array(TradeLog::TRADE_RESULT_NULL, $tradeResult)) {
                $query->where(function (Builder $query) {
                    $query->where('trade_evolve_status', TradeLog::TRADE_EVOLVE_STATUS_TRADING)
                        ->where('trade_result', TradeLog::TRADE_RESULT_NULL);
                });
                $tradeResult = array_diff($tradeResult, [TradeLog::TRADE_RESULT_NULL]);
                $tradeResult && $query->orWhereIn('trade_result', $tradeResult);
            } else {
                $query->whereIn('trade_result', $tradeResult);
            }
        }
        // 支付操作者
        if ($tradeResult = array_get($params, 'admin_id')) {
            $query->where('admin_id', $tradeResult);
        }
        // 订单渠道
        if ($channelCode = array_get($params, 'channel_code')) {
            $query->whereHas('user.channel', function ($query) use ($channelCode) {
                $query->whereIn('channel_code', (array)$channelCode);
            });
        }
        //$query->orderByCustom('-created_at');

        return $query;
    }


    /**
     * @param $params
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function incomeListSearch($params, $with = [])
    {
        $with = array_merge(['order', 'adminTradeAccount'], $with);
        $query = static::query()
            ->with($with)
            ->where('trade_result', static::TRADE_RESULT_SUCCESS);

        // 生成时间
        if ($date = array_get($params, 'date')) {
            $dateStart = current($date);
            $dateEnd = last($date);
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        return $query;
    }

    public function order($class = Order::class)
    {
        return parent::order($class);
    }
}
