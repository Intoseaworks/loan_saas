<?php

namespace Common\Models\Clm;

use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Clm\ClmCustomer
 *
 * @property int $id
 * @property int|null $merchant_id
 * @property int|null $user_id
 * @property string|null $idcard_type
 * @property int|null $idcard_no
 * @property string|null $name
 * @property string|null $phone
 * @property int|null $current_level
 * @property int|null $max_level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereCurrentLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereIdcardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereIdcardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereMaxLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomer whereUserId($value)
 * @mixin \Eloquent
 */
class ClmCustomer extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'clm_customer';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

    public function user($class = User::class) {
        return $this->hasOne($class, 'id', 'user_id');
    }

    public function clmLevel($class = ClmLevel::class){
        return $this->hasOne($class, 'clm_level', 'current_level');
    }
}
