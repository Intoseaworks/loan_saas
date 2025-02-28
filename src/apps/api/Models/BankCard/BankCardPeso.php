<?php

namespace Api\Models\BankCard;

/**
 * Api\Models\BankCard\BankCardPeso
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $payment_type 支付方式类型
 * @property string|null $account_name 账户名
 * @property string|null $account_no 账户号码
 * @property string|null $bank_name 银行名
 * @property string|null $instituion_name 机构名称
 * @property string|null $channel 频道
 * @property int|null $status 状态:1正常 0废弃 -1待鉴权绑卡 -2系统清理(放款失败解绑银行卡)
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso newQuery()
 * @method static Builder|BankCardPeso orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereInstituionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereUserId($value)
 * @mixin \Eloquent
 */
class BankCardPeso extends \Common\Models\BankCard\BankCardPeso {

    public function clear($userId) {
        $where = [
            'user_id' => $userId,
        ];
        return $this->where($where)->where('status', self::STATUS_ACTIVE)->update([
            'status' => self::STATUS_DELETE,
        ]);
    }
    public function clearPeralending($id) {
        return $this->whereId($id)->update([
            'status' => self::STATUS_DELETE_PERALENDING,
        ]);
    }
}
