<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 11:14
 */

namespace Common\Redis\Token;

use Common\Models\User\User;
use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class TokenRedis
{
    use BaseRedis;

    /**
     * @param $token
     * @return string
     */
    public function getAccessToken($token)
    {
        return $this->redis::get($this->getAccessTokenKey($token));
    }

    /**
     * @param $token
     * @return string
     */
    public function getAccessTokenKey($token)
    {
        return RedisKey::PREFIX_ACCESS_TOKEN . $token;
    }

    /**
     * @param $token
     * @return string
     */
    public function getRefreshToken($token)
    {
        return $this->redis::get($this->getRefreshTokenKey($token));
    }

    /**
     * @param $token
     * @return string
     */
    public function getRefreshTokenKey($token)
    {
        return RedisKey::PREFIX_REFRESH_TOKEN . $token;
    }

    /**
     * @param $token
     * @param $user User
     * @return mixed
     */
    public function setAccessToken($token, User $user)
    {
        $expireTime = config('config.access_token_ttl');
        return $this->redis::set($this->getAccessTokenKey($token), $user, 'EX', $expireTime);
    }

    /**
     * @param $token
     * @param $user User
     * @return mixed
     */
    public function setRefreshToken($token, User $user)
    {
        $expireTime = config('config.refresh_token_ttl');
        return $this->redis::set($this->getAccessTokenKey($token), $user, 'EX', $expireTime);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function delAccessToken($token)
    {
        return $this->redis::del($this->getAccessTokenKey($token));
    }

    /**
     * @param $token
     * @return mixed
     */
    public function delRefreshToken($token)
    {
        return $this->redis::del($this->getRefreshTokenKey($token));
    }
}
