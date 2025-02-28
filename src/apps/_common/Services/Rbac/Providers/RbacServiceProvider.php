<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/7
 * Time: 10:19
 */

namespace Common\Services\Rbac\Providers;

use Common\Services\Rbac\Middleware\RbacMiddleware;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

/**
 * @property \Laravel\Lumen\Application $app
 * Class RbacServiceProvider
 * @package Common\Services\Rbac\Providers
 */
class RbacServiceProvider extends ServiceProvider
{
    public function register()
    {

        //1.加载配置文件
        $this->app->configure('permission');
        $this->mergeConfigFrom(__DIR__ . '/../config/permission.php', 'permission');

        //2.注册依赖
        $this->app->register(PermissionServiceProvider::class);

        //3.注册中间件
        $this->app->routeMiddleware([
            'RBAC' => RbacMiddleware::class,
        ]);

    }

    public function boot()
    {
        $this->registerModelBindings();
    }

    protected function registerModelBindings()
    {
        $this->app->bind('rbacUser', config('permission.user_model'));
    }

}
