<?php

namespace Common\Middleware;

use Closure;
use Common\Models\Merchant\Merchant;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Auth\Factory as Auth;
use JMD\JMD;
use JMD\Libs\Services\AuthService;
use JMD\Utils\SignHelper;

class ServicesAuth
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next, $guard = null)
    {
        JMD::init(['projectType' => 'lumen']);

        $params = $request->post();

        if (isset($params['token'])) {
            if (!$this->validateToken($params['token'])) {
                return $this->sendError('token invalid', 401, $params);
            }
            return $next($request);
        }

        try {
            if (array_get($params, 'app_key') == '') {
                return $this->sendError('no app_key', 401, $params);
            }
            $merchant = Merchant::getByAppKey($params['app_key']);
            if (!$merchant) {
                return $this->sendError('app_key no register', 401, $params);
            }

            if (!SignHelper::validateSign($params, $merchant->app_secret_key)) {
                return $this->sendError('auth fail', 401, $params);
            }

            MerchantHelper::setMerchantId($merchant->id);

            if ($merchant->status != Merchant::STATUS_NORMAL) {
                return $this->sendError('merchant not open', 401, $params);
            }
        } catch (\Exception $e) {
            DingHelper::notice(json_encode($e->getMessage(), 256), '【异常】风控中间件异常');
            return $this->sendError('auth exception', 401, $params);
        }

        return $next($request);
    }

    protected function validateToken($token)
    {
        $authService = AuthService::validateToken($token);
        if (!$authService->isSuccess()) {
            DingHelper::notice(['token' => $token, 'res' => $authService->getAll()], '业务身份认证不通过 - ' . app()->environment());
            return false;
        }
        return true;
    }

    /**
     * @param $message
     * @param int $status
     * @param array $error
     * @return array
     */
    public function sendError($message, $status = 400, $error = [])
    {
        $response = [
            'status' => $status,
            'msg' => $message,
        ];
        if ($error) {
            $response['error'] = $error;
        }
        return $response;
    }
}
