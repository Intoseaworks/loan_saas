<?php

namespace Common\Models\Payment;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Payment\PaymentRazorpayNotify
 *
 * @property int $id
 * @property string|null $event 事件名称
 * @property string|null $data 数据
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRazorpayNotify whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentRazorpayNotify extends Model {

    use StaticModel;

    const SCENARIO_CREATE = 'create';

    protected $table = 'payment_razorpay_notify';

    /**
     * @return array
     */
    public function safes() {
        return [
            self::SCENARIO_CREATE => [
                'event',
                'data',
            ],
        ];
    }

}
