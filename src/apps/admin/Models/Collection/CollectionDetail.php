<?php

namespace Admin\Models\Collection;

use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;

/**
 * Admin\Models\Collection\CollectionDetail
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $collection_id 催收id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string|null $promise_paid_time 承诺还款时间
 * @property string|null $record_time 催记时间
 * @property string $dial 联系结果 （正常联系，无法联系...）
 * @property string $progress 催收进度 （承诺还款，无意向...）
 * @property string $remark 备注
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereDial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail wherePromisePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereRecordTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $contact_num 显示联系人数
 * @property string $reduction_setting 减免设置
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereContactNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereReductionSetting($value)
 * @property int $merchant_id merchant_id
 * @property string|null $contact 联系值（手机号）
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDetail whereMerchantId($value)
 */
class CollectionDetail extends \Common\Models\Collection\CollectionDetail
{

    const SCENARIO_INFO = 'info';
    const SCENARIO_RECORD = 'record';

    public function safes()
    {
        return [
            self::SCENARIO_RECORD => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'promise_paid_time',
                'record_time' => DateHelper::dateTime(),
                'contact',
                'dial',
                'progress',
                'remark',
            ],
        ];
    }

    public function texts()
    {
        return [
            self::SCENARIO_INFO => [
                'order_id',
                'collection_id',
                'promise_paid_time',
                'record_time',
                'dial',
                'progress',
                'remark',
                'contact',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'progress' => Collection::model()->getProgressAll(),
                'dial' => ts(Collection::DIAL_ALL, 'collection'),
            ],
            'function' => [
                'collection_id' => function () {
                    $this->out_days = (strtotime($this->record_time) > 0) ? DateHelper::diffInDays($this->record_time) : '-';
                },
                'promise_paid_time' => function () {
                    if ($this->promise_paid_time) {
                        $this->promise_paid_time = DateHelper::formatToDate($this->promise_paid_time, 'd/m/Y');
                    }
                },
            ],
        ];
    }

    public function record($collectionDetail, $data)
    {
        return $collectionDetail->setScenario(self::SCENARIO_RECORD)->saveModel($data);
    }

}
