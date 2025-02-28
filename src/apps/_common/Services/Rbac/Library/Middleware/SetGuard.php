<?php

namespace Common\Services\Rbac\Library\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetGuard
{
    public function handle(Request $request, Closure $next, $guard)
    {
        if (!$guard) {
            abort(400, 'guard不存在');
        }

        Auth::setDefaultDriver($guard);
        return $next($request);
    }
}
