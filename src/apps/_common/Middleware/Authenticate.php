<?php

namespace Common\Middleware;

use Admin\Models\Staff\Staff;
use Admin\Services\Ticket\TicketServer;
use Closure;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Auth as AuthFacades;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Yunhan\Utils\Env;

class Authenticate
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
     * @param  \Illuminate\Contracts\Auth\Factory $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        AuthFacades::setDefaultDriver($guard);

        $token = LoginHelper::helper()->getTicket();
        if ($token) {
            request()->merge(['token' => $token]);
        }

        $this->fakeUser($token);

        if ($this->auth->guard($guard)->guest() || !AuthFacades::user()->canLogin()) {
            /** 清理登录态缓存 */
            TicketServer::server()->logoutByTicket($token);
            throw new HttpException(401, "登录已失效,请重新登录");
        }

        $user = AuthFacades::user();
        LoginHelper::$modelUser = $user;
        MerchantHelper::setMerchantId($user->merchant_id);
        // 更新merchantId之后，需要重新载入staff Model的全局约束，因为全局约束是静态的
        Staff::boot();

        return $next($request);
    }

    private function fakeUser($token)
    {
        if (!$token && Env::isDev()) {
            $user = Staff::find(1);

            AuthFacades::setUser($user);
        }
    }
}
