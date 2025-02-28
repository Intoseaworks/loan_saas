<?php

namespace Common\Models\Order;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Common\Models\Order\OrderDetail
 *
 * @property int $order_id
 * @property string|null $key 键
 * @property string|null $value 值
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail whereValue($value)
 * @mixin \Eloquent
 * @property-read \Common\Models\Order\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail orderByCustom($column = null, $direction = 'asc')
 * @property int $id
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail whereMerchantId($value)
 */
class OrderDetail extends Model
{
    use StaticModel;

    const KEY_LOAN_LOCATION = 'loan_location';//借款所在地
    const KEY_LOAN_POSITION = 'loan_position';//借款所在地经纬度
    const KEY_LOAN_IP = 'loan_ip';
    const KEY_BANK_CARD_NO = 'bank_card_no';//银行卡号
    const KEY_BANK_NAME = 'bank_name';//银行名称
    const KEY_BANK_PAY_TYPE = 'pay_type';//银行放款渠道
    const KEY_REJECTED_DAYS = 'rejected_days';//拒绝天数
    // -----------------------cashnow------------------------------------
    const KEY_GST_PENALTY_RATE = 'gst_penalty_rate'; // GST逾期费率
    const KEY_GST_PROCESSING_RATE = 'gst_processing_rate';// GST手续费率
    const KEY_PROCESSING_RATE = 'processing_rate'; // 手续费率
    const KEY_LOAN_REASON = 'loan_reason';//借款原因
    const KEY_PRODUCT_ID = 'product_id';//产品ID
    const KEY_INTENTION_PRINCIPAL = 'intention_principal';//意向贷款金额
    const KEY_INTENTION_LOAN_DAYS = 'intention_loan_days';//意向贷款天数
    // -----------------------cashnow------------------------------------
    const KEY_IMEI = 'imei';//设备标识
    const KEY_HIGH_OVERDUE_FEE = 'high_overdue_fee';
    const KEY_CAN_CONTACT_TIME = 'can_contact_time';

    const KEY_INSTALLMENT = 'installment';//分期
    const KEY_LAST_INSTALLMENT_FREE_ON = 'last_installment_free_on';//分期最后一期是否可免
    const KEY_SERVICE_CHARGE_DISCOUNT = 'service_charge_discount';//CLM手续费折扣

    const KEY = [
        self::KEY_LOAN_LOCATION => '借款所在地',
        self::KEY_LOAN_POSITION => '借款所在地经纬度',
        self::KEY_BANK_CARD_NO => '银行卡号',
        self::KEY_BANK_NAME => '银行名称',
        self::KEY_REJECTED_DAYS => '拒绝天数',
        self::KEY_CAN_CONTACT_TIME => '方便联系时间',
    ];

    const OFF = 0;
    const ON = 1;

    /**
     * @var string
     */
    protected $table = 'order_detail';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public function getLocation($order)
    {
        return $this->getValueByKey($order, self::KEY_LOAN_LOCATION);
    }

    public function getPosition($order)
    {
        return $this->getValueByKey($order, self::KEY_LOAN_POSITION);
    }

    public function getBankCardNo($order)
    {
        return $this->getValueByKey($order, self::KEY_BANK_CARD_NO);
    }

    public function getBankName($order)
    {
        return $this->getValueByKey($order, self::KEY_BANK_NAME);
    }

    /**
     * CLM手续费折扣
     * @param $order
     * @return mixed
     */
    public function getServiceChargeDiscount($order)
    {
        return $this->getValueByKey($order, self::KEY_SERVICE_CHARGE_DISCOUNT);
    }

    public function getRejectDays($order)
    {
        return $this->getValueByKey($order, self::KEY_REJECTED_DAYS);
    }

    public function getProcessingRate($order)
    {
        return $this->getValueByKey($order, self::KEY_PROCESSING_RATE);
    }

    public function getGstProcessingRate($order)
    {
        return $this->getValueByKey($order, self::KEY_GST_PROCESSING_RATE);
    }

    public function getGstPenaltyRate($order)
    {
        return $this->getValueByKey($order, self::KEY_GST_PENALTY_RATE);
    }

    public function getImei($order)
    {
        return $this->getValueByKey($order, self::KEY_IMEI);
    }

    public function getInstallment($order)
    {
        return $this->getValueByKey($order, self::KEY_INSTALLMENT);
    }

    public function getIp($order)
    {
        return $this->getValueByKey($order, self::KEY_LOAN_IP);
    }

    public function getLoanReason($order)
    {
        return $this->getValueByKey($order, self::KEY_LOAN_REASON);
    }

    public function getCanContactTime($order)
    {
        return $this->getValueByKey($order, self::KEY_CAN_CONTACT_TIME);
    }

    /**
     * @param $order Order
     * @param $key
     * @return mixed
     */
    public function getValueByKey($order, $key)
    {
        $keyCache = "OrderDeail::getValueByKey::Order[{$order->id}]::key[{$key}]";
        return Cache::remember($keyCache, 60, function() use ($order, $key) {
            return optional($order->orderDetails->where('key', $key)->first())->value;
        });

        return self::whereOrderId($orderId)->where('key', $key)->value('value');
    }

    /**
     * @param $order Order
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setValueByKey($order, $key, $value)
    {
        /** @var static $data */
        $data = $order->orderDetails->where('key', $key)->first();
        if ($data) {
            $data->value = $value;
            $data->save();
        }
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order($class = Order::class)
    {
        return $this->belongsTo($class, 'order_id', 'id');
    }
}
