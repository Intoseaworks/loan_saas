<?php

namespace Api\Models\Trade;

/**
 * Api\Models\Trade\AdminTradeAccount
 *
 * @property int $id id
 * @property int $type 类型 1:放款  2:还款
 * @property int $payment_method 类型 1:银行卡转账  2:支付宝
 * @property string $account_no 银行卡号
 * @property string $account_name 银行卡户名
 * @property string $bank_name 银行名称
 * @property int $status 状态  1:正常  -1:已删除  -2:禁用
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $is_default 默认方式
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereIsDefault($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Trade\AdminTradeAccount whereMerchantId($value)
 */
class AdminTradeAccount extends \Common\Models\Trade\AdminTradeAccount
{

    const SCENARIO_MODE = 'mode';

    public function texts()
    {
        return [
            self::SCENARIO_MODE => [
                'id',
                'account_no',
                'account_name',
                'payment_method',
                'created_at',
                'bank_name',
                'is_default',
            ],
        ];
    }

    public function getModeList($param)
    {
        return $this
            ->where('type', self::TYPE_IN)
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('is_default', 'desc')
            ->get();
    }

}
