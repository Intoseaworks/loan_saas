<?php

namespace Common\Services\Rbac\Library\Middleware;

use Closure;
use Common\Services\Rbac\Library\Contracts\Config;
use Common\Services\Rbac\Library\Utils\RbacHelper;
use Illuminate\Http\Request;

class RbacAuthCheck
{
    /**
     * @var string
     */
    protected $action = '/api/rbac/permission/check';

    public function handle(Request $request, Closure $next, $guard)
    {
        $path = $request->route()[1]['path'] ?? null;
        if (!$path) {
            abort(403, 'path属性不存在');
        }
        $route = $this->getRoutePath($request->method(), $request->path());

        /** @var Config $config */
        $config = app(Config::class);
        $params = [
            'path' => $path,
            'guard_name' => $guard,
            'route' => $route,
            'referer' => $config->getProjectName(),
            'uid' => $config->getUserId(),
            'app_key' => $config->getAppKey(),
        ];
        $params['sign'] = $config->getSign($params);
        $url = rtrim($config->getDomain(), '/') . $this->action;

        $result = RbacHelper::curlPost($url, $params);
        if (isset($result['code']) && $result['code'] === 18000) {
            return $next($request);
        }

        abort(403, $result['msg']);
    }

    /**
     * @param $method
     * @param $path
     * @return null|string
     */
    private function getRoutePath()
    {
        $request = app('request');
        $route = $request->route();
        $path = $request->path();
        $method = $request->method();
        if (isset($route[1]['symbol'])) {
            $symbol = $route[1]['symbol'];
            return $method . app()->router->irregularityRoutes[$symbol] ?? null;
        } else {
            return $method . '/' . $path;
        }
    }
}
