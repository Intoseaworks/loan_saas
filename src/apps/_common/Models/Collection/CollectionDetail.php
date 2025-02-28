<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionDetail
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereDial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail wherePromisePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereRecordTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $contact_num 显示联系人数
 * @property string $reduction_setting 减免设置
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereContactNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereReductionSetting($value)
 * @property int $merchant_id merchant_id
 * @property string|null $contact 联系值（手机号）
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDetail whereMerchantId($value)
 */
class CollectionDetail extends Model
{
    use StaticModel;

    /**
     * @var bool
     */
    //public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'collection_detail';
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

}
