<?php

namespace Common\Services\Rbac\Middleware;

use Closure;
use Common\Services\Rbac\Utils\Helper;
use Illuminate\Http\Request;

class RbacMiddleware
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        try {
            $user = Helper::getUser();
            $route = Helper::getRoutePath();
            $path = $request->route()[1]['path'] ?? null;
            if (!$path) {
                abort(403, 'path属性不存在');
            }
            if ($user->checkPermission($route, $path)) {
                return $next($request);
            } else {
                abort(403, '该用户无权限');
            }
        } catch (\Exception $exception) {
            abort(403, $exception->getMessage());
        }
    }
}
