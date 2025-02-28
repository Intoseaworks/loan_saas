<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 14:18
 */

namespace Api\Services\Login;

use Api\Models\User\User;
use Api\Services\BaseService;
use Common\Exceptions\ApiException;
use Common\Models\Login\AccessToken;
use Common\Redis\Token\TokenRedis;

class AccessTokenServer extends BaseService
{
    /**
     * 根据accessToken识别身份
     * @param $token
     * @return bool|mixed|string
     */
    public function findIdentityByAccessToken($token)
    {
        return $this->getUserByToken($token);
        //@phan-suppress-next-line PhanPluginUnreachableCode
        $user = TokenRedis::redis()->getAccessToken($token);
        if (!$user) {
            if (!$user = $this->getUserByToken($token)) {
                return false;
            } else {
                /** 刷新用户登录态 */
                TokenRedis::redis()->setAccessToken($token, $user);
            }
        }
        return $user;
    }

    /**
     * 根据accessTokenn获取用户
     * @param $accessToken
     * @return User|bool
     */
    public function getUserByToken($accessToken)
    {
        return AccessToken::model()->findByToken($accessToken)->user ?? false;
    }

    /**
     * 生成accessToken
     * @param $userId
     * @param $cliendId
     * @return string
     * @throws ApiException
     */
    public function build($userId, $cliendId)
    {
        if (!$model = $this->store($userId, $cliendId)) {
            throw new ApiException('令牌获取失败');
        }
        TokenRedis::redis()->setAccessToken($model->token, $model->user);
        return $model->token;
    }

    /**
     * 数据库保存accessToken
     * @param $userId
     * @param $cliendId
     * @return bool|AccessToken
     */
    public function store($userId, $cliendId)
    {
        $attributes = [];
        $attributes['user_id'] = $userId;
        $attributes['client_id'] = $cliendId;
        return AccessToken::model(AccessToken::SCENARIO_CREATE)->saveModel($attributes);
    }
}
