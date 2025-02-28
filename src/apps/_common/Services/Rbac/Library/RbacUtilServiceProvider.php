<?php

namespace Common\Services\Rbac\Library;

use Common\Services\Rbac\Library\Commands\OperationHelper;
use Common\Services\Rbac\Library\Commands\PermissionHelper;
use Common\Services\Rbac\Library\Middleware\RbacAuthCheck;
use Common\Services\Rbac\Library\Middleware\SetGuard;
use Illuminate\Support\ServiceProvider;

class RbacUtilServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->routeMiddleware([
            'rbacCheck' => RbacAuthCheck::class,
            'setGuard' => SetGuard::class,
        ]);

        $this->commands([
            PermissionHelper::class,
            OperationHelper::class,
        ]);
    }

    public function boot()
    {
        //重新绑定Router
        $routes = app('router')->getRoutes();
        $this->app->singleton('router', function () use ($routes) {

            $router = new \Common\Services\Rbac\Library\Routing\Router($this->app);
            //把已加载的路由绑定到新的对象上面
            foreach ($routes as $route) {
                $router->addRoute($route['method'], $route['uri'], $route['action']);
            }
            return $router;
        });
        $this->app->router = $this->app->make('router');
    }
}
