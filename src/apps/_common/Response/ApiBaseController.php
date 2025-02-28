<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-28
 * Time: 15:53
 */

namespace Common\Response;

use Api\Services\Login\LoginServer;
use Api\Services\User\UserCheckServer;
use Common\Exceptions\ApiException;
use Common\Utils\MerchantHelper;
use Common\Utils\Until;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yunhan\Utils\Env;

class ApiBaseController extends BaseController
{
    protected $clientId;

    protected $appVersion;
    
    protected $locale = 'en-US';

    public function __construct()
    {
        parent::__construct();
        $this->clientId = $this->getParam('client_id');
        $this->appVersion = app(Request::class)->header('app-version');
        $this->locale = app(Request::class)->header('locale', 'en-US');
        if($this->locale == 'ar-EG'){
            app('translator')->setLocale($this->locale);
        }
    }

    /**
     * 登录状态判断
     * @return bool|mixed|string
     * @throws AuthorizationException
     * @throws \Common\Exceptions\ApiException
     */
    protected function identity()
    {
        if (Env::isDev()) {
            $this->fakeUser();
        }

        if (Auth::guard('api')->guest()) {
            throw new ApiException(t('登录已过期，请重新登录'), 1403);
            //throw new AuthorizationException('登录已过期，请重新登录');
        }
        $user = Auth::guard('api')->user();
        UserCheckServer::server()->checkUser($user->telephone, $user->id);

        $this->checkOnly($user);
        $this->checkApp($user);

        return $user;
    }

    private function fakeUser()
    {
        $class = Until::getCurrentUserModel();
        $token = $this->request->get('token');
        $user = null;
        if ($token) {
            if (is_numeric($token)) {
                $user = $class::find($token);
            }
        } else {
            if (app()->environment() == 'local') {
                $user = $class::find('210');
            }
        }

        if (!$user) {
            return false;
        }

        MerchantHelper::setAppId($user->app_id, $user->merchant_id);

        Auth::setUser($user);
    }

    public function checkApp($user)
    {
        if (!Env::isDev()) {
            $field = $user::$relationAppField;
            $appId = Auth::payload()->get($field);
            if (MerchantHelper::getAppId() != $appId) {
                throw new \Exception('app identifier error');
            }
        }
    }

    public function checkOnly($user)
    {
        $token = $this->request->get('token');
        if (Env::isDev() && is_numeric($token)) {
            return;
        }
        $uuid = $this->request->header('device-uuid');
        if($user->id && $uuid && $redisUuid = LoginServer::server()->getRedisUuid($user->id)){
            if($redisUuid != $uuid){
                throw new ApiException(t('登录已过期，请重新登录'), 1403);
            }
        }
    }
}
