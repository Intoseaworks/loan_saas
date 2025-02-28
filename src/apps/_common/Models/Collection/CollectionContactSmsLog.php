<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionContact
 *
 * @property int $id
 * @property int $order_id 订单ID
 * @property int $user_id 用户ID
 * @property int $collection_id 订单ID
 * @property string $type 联系来源类型
 * @property string $fullname 姓名
 * @property string $contact 联系值（手机号）
 * @property string $relation 关系
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereUserId($value)
 * @mixin \Eloquent
 * @property string $content
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereContent($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionContact whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionContact orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionContact query()
 */
class CollectionContactSmsLog extends Model
{
    use StaticModel;

    # 短信类型
    const TYPE_SEND = 2;//发送

    /**
     * @var string
     */
    protected $table = 'collection_contact_sms_log';
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

    public function collectionContactSms($class = CollectionContactSms::class)
    {
        return $this->belongsTo($class, 'contact_sms_id');
    }

}
