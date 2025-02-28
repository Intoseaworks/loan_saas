<?php

namespace Common\Models\Login;

use Carbon\Carbon;
use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\StringHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Login\RefreshToken
 *
 * @property int $id
 * @property int|null $user_id 用户id
 * @property string $refresh_token 刷新令牌
 * @property string $client_id 客户端id
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property int $expired_time 过期时间，单位为秒
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken whereExpiredTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken whereToken($value)
 * @mixin \Eloquent
 * @property string $token 刷新令牌
 * @property-read \Common\Models\User\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\RefreshToken whereAppId($value)
 */
class RefreshToken extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'refresh_token';

    const SCENARIO_CREATE = 'create';

    protected static function boot()
    {
        parent::boot();

        static::setAppIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'app_id' => MerchantHelper::getAppId(),
                'token' => function () {
                    return StringHelper::generateUuid();
                },
                'client_id',
                'user_id',
                'expired_time' => function () {
                    return $this->getExpiredTime();
                },
            ]
        ];
    }

    public function findByToken($token)
    {
        return $this->whereToken($token)->first();
    }

    public function isExpired()
    {
        return $this->expired_time > Carbon::now();
    }

    public function getExpiredTime()
    {
        $expireTime = config('config.refresh_token_ttl');
        return Carbon::now()->addSeconds($expireTime);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault();
    }
}
