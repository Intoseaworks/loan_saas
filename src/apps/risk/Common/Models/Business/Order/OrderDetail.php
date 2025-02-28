<?php

namespace Risk\Common\Models\Business\Order;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\Order\OrderDetail
 *
 * @property int $id
 * @property int $order_id
 * @property int $app_id merchant_id
 * @property string|null $key 键
 * @property string|null $value 值
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereValue($value)
 * @mixin \Eloquent
 */
class OrderDetail extends BusinessBaseModel
{
    const KEY_LOAN_LOCATION = 'loan_location';//借款所在地
    const KEY_LOAN_POSITION = 'loan_position';//借款所在地经纬度
    const KEY_LOAN_IP = 'loan_ip';
    const KEY_BANK_CARD_NO = 'bank_card_no';//银行卡号
    const KEY_BANK_NAME = 'bank_name';//银行名称
    const KEY_REJECTED_DAYS = 'rejected_days';//拒绝天数
    // -----------------------cashnow------------------------------------
    const KEY_GST_PENALTY_RATE = 'gst_penalty_rate'; // GST逾期费率
    const KEY_GST_PROCESSING_RATE = 'gst_processing_rate';// GST手续费率
    const KEY_PROCESSING_RATE = 'processing_rate'; // 手续费率
    const KEY_LOAN_REASON = 'loan_reason';//借款原因
    const KEY_PRODUCT_ID = 'product_id';//产品ID
    // -----------------------cashnow------------------------------------
    const KEY_IMEI = 'imei';//设备标识

    const KEY_INSTALLMENT = 'installment';//分期
    const KEY_LAST_INSTALLMENT_FREE_ON = 'last_installment_free_on';//分期最后一期是否可免

    const KEY = [
        self::KEY_LOAN_LOCATION => '借款所在地',
        self::KEY_LOAN_POSITION => '借款所在地经纬度',
        self::KEY_BANK_CARD_NO => '银行卡号',
        self::KEY_BANK_NAME => '银行名称',
        self::KEY_REJECTED_DAYS => '拒绝天数',
    ];

    const OFF = 0;
    const ON = 1;
    public static $validate = [
        'data' => 'required|array',
        'data.*.id' => 'required|numeric', // 记录唯一ID
        'data.*.order_id' => 'required|numeric', // 订单ID
        'data.*.key' => 'required|string', // 订单详情key
        'data.*.value' => 'nullable', // 订单详情value
        'data.*.created_at' => 'required|date', // 创建时间
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_order_detail';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'order_id',
        'app_id',
        'key',
        'value',
        'created_at',
        'updated_at',
    ];

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

    /**
     * @param $order Order
     * @param $key
     * @return mixed
     */
    public function getValueByKey($order, $key)
    {
        return optional($order->orderDetails->where('key', $key)->first())->value;

        return self::whereOrderId($orderId)->where('key', $key)->value('value');
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

    public function getAllImei(array $orderIds)
    {
        return (array)$this->getValueByKeyAndUser($orderIds, self::KEY_IMEI);
    }

    /**
     * @param array $orderIds
     * @param $key
     * @return mixed
     */
    public function getValueByKeyAndUser(array $orderIds, $key)
    {
        return self::whereIn('order_id', $orderIds)->where('key', $key)->pluck('value')->toArray();
    }

    public function getInstallment($order)
    {
        return $this->getValueByKey($order, self::KEY_INSTALLMENT);
    }

    public function getIp($order)
    {
        return $this->getValueByKey($order, self::KEY_LOAN_IP);
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
