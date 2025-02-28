<?php

namespace Common\Services\Rbac\Middleware;

use Closure;
use Illuminate\Http\Request;

class RbacConfigMiddleware
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        app()->register(\Common\Services\Rbac\Providers\RbacServiceProvider::class);

        return $next($request);
    }
}
