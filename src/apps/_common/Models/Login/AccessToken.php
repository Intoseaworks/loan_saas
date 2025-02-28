<?php

namespace Common\Models\Login;

use Api\Models\User\User;
use Carbon\Carbon;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\StringHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Login\AccessToken
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property string $token 身份令牌
 * @property string $client_id 终端
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property \Illuminate\Support\Carbon $expired_time 过期时间，单位为秒
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken whereExpiredTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Api\Models\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\AccessToken whereAppId($value)
 */
class AccessToken extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'access_token';

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
        return $this->whereToken($token)->where('expired_time', '>', $this->getTime())->first();
    }

    public function getExpiredTime()
    {
        $expireTime = config('config.access_token_ttl');
        return Carbon::now()->addSeconds($expireTime);
    }

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'user_id')->withDefault();
    }
}
