<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\TestAccount
 *
 * @property int $id 测试账号ID
 * @property int $user_id 用户ID
 * @property int|null $type 账号类型  1真实账号 2虚拟账号
 * @property int|null $status 账号状态 1正常 2禁用
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read \Common\Models\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\TestAccount whereUserId($value)
 * @mixin \Eloquent
 */
class TestAccount extends Model
{
    use StaticModel;

    /** @var int 正常 */
    const STATUS_NORMAL = 1;
    /** @var int 禁用 */
    const STATUS_DISABLE = 2;
    /** @var array status */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];
    /** 类型：真实账号 */
    const TYPE_REAL = 1;
    /** 类型：虚拟账号 */
    const TYPE_VIRTUAL = 2;
    /** 类型 */
    const TYPE = [
        self::TYPE_REAL => '真实账号',
        self::TYPE_VIRTUAL => '虚拟账号',
    ];
    /**
     * @var string
     */
    protected $table = 'test_account';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];
    /**
     * @var array
     */
    protected $hidden = [];

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 校验是否测试账号(虚拟账号)
     * @param $userId
     * @return bool
     */
    public static function isTestAccount($userId)
    {
        return static::where('user_id', $userId)->exists();
    }
}
