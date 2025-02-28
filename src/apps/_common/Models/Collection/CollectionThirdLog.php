<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionThirdLog
 *
 * @property int $id
 * @property string|null $third_name 第三方催收名称
 * @property int|null $order_id
 * @property int|null $admin_id
 * @property string|null $apply_data 发送数据
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog whereApplyData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog whereThirdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionThirdLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CollectionThirdLog extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'collection_third_log';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];


    public function textRules()
    {
        return [];
    }

}
