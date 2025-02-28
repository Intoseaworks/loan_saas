<?php

namespace Common\Models\Test;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Test\TestUser
 *
 * @property int $id 测试账号ID
 * @property int $merchant_id merchant_id
 * @property int $app_id app_id
 * @property int|null $user_id 用户ID
 * @property int|null $user_type 账号类型 0虚拟账号 1真实账号
 * @property int|null $status 账号状态 1正常 0禁用
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property \Illuminate\Support\Carbon $created_at 添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestUser whereUserType($value)
 * @mixin \Eloquent
 */
class TestUser extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'test_user';

    /**
     * 账号类型
     */
    /** @var int 正常 */
    const STATUS_NORMAL = 1;
    /** @var int 禁用 */
    const STATUS_DISABLE = 2;
    /** @var array status */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    /**
     * 账号类型
     */
    const USER_TYPE_INVENTED = 0;
    const USER_TYPE_REAL = 1;
    const USER_TYPE = [
        self::USER_TYPE_INVENTED => '虚拟账号',
        self::USER_TYPE_REAL => '真实账号',
    ];

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

        static::setAppIdBootScope();
    }


    public function textRules()
    {
        return [
            'array' => [
                'tags' => ts(self::USER_TYPE, 'test'),
                'status' => ts(self::STATUS, 'test'),
            ],
        ];
    }

    public function isTestUser($userId)
    {
        return $this->where('user_id', $userId)->exists();
    }

    public function add($user)
    {
        $model = new static();
        $model->merchant_id = $user->merchant_id;
        $model->app_id = $user->app_id;
        $model->user_id = $user->id;
        $model->user_type =  1;
        $model->status = 1;
        return $model->save();
    }

}
