<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 14:18
 */

namespace Api\Services\Login;

use Api\Services\BaseService;
use Common\Exceptions\ApiException;
use Common\Models\Login\RefreshToken;
use Common\Models\User\User;

class RefreshTokenServer extends BaseService
{
    /**
     * 根据refreshToken获取用户
     * @param $refreshToken
     * @return User|bool
     */
    public function getUserByToken($refreshToken)
    {
        return RefreshToken::model()->findByToken($refreshToken)->user ?? false;
    }

    /**
     * 生成refreshToken
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
        return $model->token;
    }

    /**
     * 数据库保存refreshToken
     * @param $userId
     * @param $cliendId
     * @return bool|RefreshToken
     */
    public function store($userId, $cliendId)
    {
        $attributes = [];
        $attributes['user_id'] = $userId;
        $attributes['client_id'] = $cliendId;
        return RefreshToken::model(RefreshToken::SCENARIO_CREATE)->saveModel($attributes);
    }
}
