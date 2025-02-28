<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\Collection
 *
 * @property int $id
 * @property int $admin_id 催收人员id
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property string $status 催收状态
 * @property string $level 催收等级(M M0 M1 M2)
 * @property string|null $assign_time 分配时间
 * @property string|null $finish_time 完结时间
 * @property string|null $bad_time 坏账时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereAssignTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereBadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereFinishTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $collection_time 首催时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereCollectionTime($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereMerchantId($value)
 * @property string|null $level_name 等级名称“，”分隔
 * @property string|null $language 擅长语言
 * @property int|null $weight 权重
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAdmin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAdmin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAdmin orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAdmin query()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAdmin whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAdmin whereLevelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAdmin whereWeight($value)
 */
class CollectionAdmin extends Model
{
    use StaticModel;

    const STATUS_NORMAL = '1';
    const STATUS_DELETE = '2';
    /**
     * @var string
     */
    protected $table = 'collection_admin';
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
