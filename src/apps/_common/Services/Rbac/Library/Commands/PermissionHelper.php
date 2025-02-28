<?php

namespace Common\Services\Rbac\Library\Commands;

use Common\Services\Rbac\Models\Permission;
use Illuminate\Console\Command;

class PermissionHelper extends Command
{
    /**
     * @var string
     */
    protected $module;

    /**
     * 控制台命令名称
     * @var string
     */
    protected $signature = 'rbac:permission:push 
                                {module : 同步的模块.对应中间件加的gurade:例如`"middleware" => "setGuard:admin"`模块为`admin`}';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '路由推送';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->module = $this->argument('module');
        $routes = $this->getRoutes();
        if (!$routes) {
            $this->error('路由不存在');
            return;
        }

        (new Permission())->pushPermissions($routes);

        $this->info('同步权限成功');
    }

    /**
     * 获取路由
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = app('router')->getRoutes();
        $filterRoutes = [];
        array_walk($routes, function ($item, $k) use (&$filterRoutes) {
            if (isset($item['action']['middleware'])) {
                $middlewares = $item['action']['middleware'];
                $flag = false;
                foreach ($middlewares as $middleware) {
                    if (strpos($middleware, $this->module) !== false) {
                        $flag = true;
                        break;
                    }
                }

                if ($flag) {
                    $path = $item['action']['path'] ?? false;
                    if (!$path || in_array($path, ['admin', 'admin.'])) {
                        return false;
                    }

                    $filterRoutes[] = [
                        'remark' => '',
                        'name' => $k,
                        'guard_name' => $this->module,
                        'menu_key' => $path
                    ];
                }
            }
        });

        return $filterRoutes;
    }
}
