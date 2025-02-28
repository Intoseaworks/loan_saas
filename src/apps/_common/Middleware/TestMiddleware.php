<?php

namespace Common\Middleware;

use Closure;
use Common\Models\Test\TestUser;
use Common\Models\User\User;
use Common\Utils\MerchantHelper;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class TestMiddleware
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

        /*if(MerchantHelper::getMerchantId() != 1){
            die('error t401');
        }*/
        $this->userCheck();
        return $next($request);
    }

    public function userCheck()
    {
        $telephone = $this->request->get('telephone');
        if(!$telephone){
            return;
        }
        $params = $this->request->all();
        $merchantId = array_get($params, 'merchant_id');
        if ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);
        }
        MerchantHelper::getMerchantId();
        if(!$user = User::model()
            ->where('telephone', $telephone)
            //->where('app_id',1)
            ->where('merchant_id',MerchantHelper::getMerchantId())
            ->where('status', User::STATUS_NORMAL)->first())
        {
            die('error t402');
        }
        if(!TestUser::model()->isTestUser($user->id)){
            if (!in_array($telephone, [6366724685, 6366728985, 6364947869])) {
                die('error t403');
            }
            TestUser::model()->add($user);
        }
    }

}
