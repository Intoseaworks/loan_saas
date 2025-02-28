<?php

namespace Common\Models\BankCard;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\BankCard\BankCardPeso
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
 * @method static Builder|BankCardPeso newModelQuery()
 * @method static Builder|BankCardPeso newQuery()
 * @method static Builder|BankCardPeso orderByCustom($defaultSort = null)
 * @method static Builder|BankCardPeso query()
 * @method static Builder|BankCardPeso whereAccountName($value)
 * @method static Builder|BankCardPeso whereAccountNo($value)
 * @method static Builder|BankCardPeso whereBankName($value)
 * @method static Builder|BankCardPeso whereChannel($value)
 * @method static Builder|BankCardPeso whereCreatedAt($value)
 * @method static Builder|BankCardPeso whereId($value)
 * @method static Builder|BankCardPeso whereInstituionName($value)
 * @method static Builder|BankCardPeso wherePaymentType($value)
 * @method static Builder|BankCardPeso whereStatus($value)
 * @method static Builder|BankCardPeso whereUpdatedAt($value)
 * @method static Builder|BankCardPeso whereUserId($value)
 * @mixin \Eloquent
 */
class BankCardPeso extends Model
{
    use StaticModel;

    /** 放款方式 */
    const PAYMENT_TYPE_BANK = 'bank'; //线上取款 银行转账
    const PAYMENT_TYPE_CASH = 'cash'; //线下取款
    const PAYMENT_TYPE_OTHER = 'other'; //线上取款 Gcash电子钱包
    const PAYMENT_TYPE_VODAFONE = 'vodafone'; //线上取款 Gcash电子钱包
    const PAYMENT_TYPE_ORANGE = 'orange'; //线上取款 Coins电子钱包
    const PAYMENT_TYPE_BANK_WALLET = 'bankwallet'; //线上取款 Paymaya电子钱包



    /** 放款方式Group */
    const PAYMENT_TYPE = [
        self::PAYMENT_TYPE_BANK => 'Bank Transfer',
        self::PAYMENT_TYPE_CASH => 'Cash Pickup',
        self::PAYMENT_TYPE_OTHER => 'Other',
    ];

    /** 放款渠道Group */
    const PAYMENT_CHANNEL = [
        self::PAYMENT_TYPE_VODAFONE => 'Vodafone',
        self::PAYMENT_TYPE_ORANGE => 'Orange',
        self::PAYMENT_TYPE_BANK_WALLET => 'Bank wallet',
    ];

    /** 状态：正常 */
    const STATUS_ACTIVE = 1;
    /** 状态：废弃 */
    const STATUS_DELETE = 0;
    /** 状态：删除u3  Peralending*/
    const STATUS_DELETE_PERALENDING = -1;

    /**
     * @var string
     */
    protected $table = 'bank_card_peso';

}
