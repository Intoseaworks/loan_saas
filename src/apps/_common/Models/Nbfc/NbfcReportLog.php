<?php

namespace Common\Models\Nbfc;

use Common\Models\Order\Order;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Nbfc\NbfcReportLog
 *
 * @property int $id
 * @property int $merchant_id
 * @property int $user_id
 * @property int $order_id
 * @property string $type 类型 create:创建  remit:放款
 * @property string $platform 平台
 * @property string $step 步骤
 * @property string $request 请求内容
 * @property string $response 响应内容
 * @property \Illuminate\Support\Carbon $created_at 记录时间
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NbfcReportLog whereUserId($value)
 * @mixin \Eloquent
 */
class NbfcReportLog extends Model
{
    use StaticModel;

    protected $table = 'nbfc_report_log';
    protected $fillable = [];
    protected $hidden = [];

    const UPDATED_AT = null;

    const TYPE_SIGN = 'sign';
    const TYPE_REMIT = 'remit';
    const TYPE_REPAY = 'repay';

    const TYPE = [
        self::TYPE_SIGN => '签约',
        self::TYPE_REMIT => '放款',
    ];

    public function textRules()
    {
        return [];
    }

    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'user_id',
                'order_id',
                'type',
                'request',
                'response',
                'platform',
                'step',
            ]
        ];
    }

    public function getLogByStep(Order $order, $step)
    {
        $where = [
            'merchant_id' => $order->merchant_id,
            'order_id' => $order->id,
            'step' => $step,
        ];
        return self::query()->where($where)->where('response', 'like', "%preparedbiTmpl%")->orWhere('response', 'like', "%partnerLoanIdsList%")
            ->orderBy('id', 'desc')->first();
    }

}
