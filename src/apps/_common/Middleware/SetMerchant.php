<?php

namespace Common\Middleware;

use Closure;
use Common\Utils\MerchantHelper;
use Common\Utils\Until;
use Illuminate\Http\Request;
use Yunhan\Utils\Env;

class SetMerchant
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array 不需要设置商户标识的路由白名单
     */
    protected $whiteAllow = [
        'app/repay/to-pay',
        'app/callback/razorpay',
    ];

    /**
     * @param Request $request
     * @param Closure $next
     * @param $prefix
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next, $prefix = null)
    {
        $this->request = $request;

        if ($this->whiteAllow()) {
            return $next($request);
        }

        if ($appKey = $request->header(MerchantHelper::APP_ID_FIELD)) {
            MerchantHelper::helper()->safeSetAppIdByKey($appKey);
        } elseif ($merchantId = $request->get(MerchantHelper::MERCHANT_ID_FIELD)) {
            MerchantHelper::helper()->safeSetMerchantId($merchantId);
        } else {
            $this->fakeMerchant($prefix);
        }

        // 判断是否允许通过
        $this->checkAllow($prefix);

        return $next($request);
    }

    /**
     * app 前缀接口需要 标识 app_id
     * app测试环境伪造 商户标识
     * @param $prefix
     */
    public function fakeMerchant($prefix)
    {
        $token = $this->request->get('token');

        if (Env::isDev() && $token && is_numeric($token)) {
            $class = Until::getCurrentUserModel();
            $user = $class::find($token);
            if (!$user) {
                return false;
            }
            MerchantHelper::setAppId($user->app_id, $user->merchant_id);
        }
    }

    /**
     * @param $prefix
     * @throws \Exception
     */
    public function checkAllow($prefix)
    {
        if ($prefix == 'app') {
            if (!MerchantHelper::getAppId()) {
                throw new \Exception('app identifier not set');
            }
        }
        if (!MerchantHelper::getMerchantId()) {
            throw new \Exception('merchant identifier not set');
        }
    }

    protected function whiteAllow()
    {
        if (in_array($this->request->path(), $this->whiteAllow)) {
            return true;
        }

        return false;
    }
}
