<?php

namespace Api\Models\Trade;

use Api\Models\Order\Order;

/**
 * Api\Models\Order\TradeLog
 *
 * @property int $id 交易记录自增长id
 * @property int $admin_trade_account_id 关联 admin_pay_account
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
 * @property-read \Common\Models\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereAdminTradeAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereBatchNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereBusinessAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereBusinessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereHandleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereMasterBusinessNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereMasterRelatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog wherePId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereRequestNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereRequestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeAccountTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeEvolveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeNoticeTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradePlatformNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeRequestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeResultTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeSettlementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTradeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereTransactionNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereUserId($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @property int $bank_card_id 银行卡id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereBankCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\TradeLog whereMerchantId($value)
 * @property string|null $trade_result_code 交易结果CODE
 * @property-read \Common\Models\Trade\AdminTradeAccount|null $adminTradeAccount
 * @method static \Illuminate\Database\Eloquent\Builder|TradeLog whereTradeResultCode($value)
 * @property string|null $withdraw_no 取款码
 * @property string|null $reference_no 还款码
 * @method static \Illuminate\Database\Eloquent\Builder|TradeLog whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeLog whereWithdrawNo($value)
 */
class TradeLog extends \Common\Models\Trade\TradeLog
{
    const SCENARIO_LIST = 'list';
    const SCENARIO_DETAIL = 'detail';

    public function texts()
    {
        return [
            self::SCENARIO_LIST => [
                'master_business_no',
                'trade_account_name',
                'trade_account_telephone',
                'trade_amount',
                'trade_account_no',
                'trade_result_time',
                'business_type',
                'trade_platform',
                'trade_result',
                'out_trade_result_time',
                'in_trade_result_time',
                'trade_request_time',
                'trade_desc',
                'created_at',
                'handle_name',
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
            ],
        ];
    }

    public function order($class = Order::class)
    {
        return parent::order($class);
    }
}
