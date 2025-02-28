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
class CollectionContact extends Model
{
    use StaticModel;

    # 联系人类型
    const TYPE_USER_SELF = 'user_self';//本人
    const TYPE_USER_CONTACT = 'user_contact';//紧急联系人
    const TYPE_MESSAGE_CONTACT = 'message_contact';//通讯录联系人
    const TYPE_TELEPHONE_CONTACT = 'telephone_contact';//运营商联系人
    const TYPE_COLLECTION_CONTACT = 'collection_contact';//催收添加联系人
    # 关系
    const RELATION_ONESELF = 'oneself';
    const RELATION_CONTACT = 'contact';
    const RELATION_PARENT = 'parent';
    const RELATION_SPOUSE = 'spouse';
    const RELATION_CHILDREN = 'children';
    const RELATION_FRIEND = 'friend';
    const RELATION_COLLEAGUE = 'colleague';
    const RELATION_RELATIVES = 'relatives';
    const RELATION = [
        self::RELATION_ONESELF => '本人',
        self::RELATION_CONTACT => '联系人',
        self::RELATION_PARENT => '父母',
        self::RELATION_SPOUSE => '配偶',
        self::RELATION_CHILDREN => '子女',
        self::RELATION_FRIEND => '朋友',
        self::RELATION_COLLEAGUE => '同事',
        self::RELATION_RELATIVES => '亲属',
    ];

    /**
     * @var string
     */
    protected $table = 'collection_contact';
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

    public function collectionRecord($class = CollectionRecord::class)
    {
        return $this->hasOne($class, 'contact', 'contact')->where('order_id', $this->order_id)->orderBy('id', 'desc');
    }

}
