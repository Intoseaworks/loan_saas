- library里面放的是一些项目RBAC验证需要的方法和菜单推送需要的命令
先放在这里.等版本稳定后集成到lumen-require包供各个项目使用

### 接入步骤

- 创建对应的数据库,用来存储权限

- 执行微服务`sqls/init/common_rbac.sql`文件.如果是子项目还需要执行`sqls/init/common_rbac_sub_project.sql`文件

- 申请域名.格式:`http://{app}.{project}.com`.
    ```
    比如`http://services.dev23.jiumiaodai.com`
    services.dev23就是app,jiumiaodai就是project.需要把点(.)替换成(*)
    配置如下:
    'app' => [
        //project
        'jiumiaodai' => [
            //app
            'services*dev23' => [
                //一个项目只能有一个master配置为true.也就是微服务的配置master才为true
                'master' => false,
                //module
                'admin' => [
                    //模块名称
                    'name' => '后端',
                    //数据库连接
                    'connection' => 'mysql',
                    //超管
                    'super' => [
                    ],
                ],
            ],
        ],
    ],
    ```

- 复制`Library`到子项目

- 实现`Common/Rbac/Library/Contracts/Config.php`接口并绑定
    ```
    可以在AppServiceProvider绑定
    //绑定接口
    $this->app->bind(Config::class, RbacConfig::class);
    ```
- 注册`RbacUtilserviceProvider`
    ```
    //rbac认证
    $app->register(\Common\Services\Rbac\Library\RbacUtilServiceProvider::class);
    ```
- 项目路由按照菜单进行分组

- 分组后在每个文件上面加上`path`属性,path对应前台菜单的路由

- 开发菜单推送接口,获取用户菜单列表,获取全部菜单列表
    ```
    <?php
        
    namespace App\Http\Admin\Controllers;
    
    use Common\Services\Rbac\Library\Contracts\Config;
    use Common\Services\Rbac\Library\Utils\RbacHelper;
    use App\Http\Admin\Controllers\Controller;
    use Auth;
    use JMD\Utils\SignHelper;
    
    class RbacController extends Controller
    {
        /**
         * 同步菜单
         *
         * @return array
         */
        public function pushMenus()
        {
            $menus = $this->request->input('menus');
            if (!$menus) {
                return $this->sendError('菜单不存在');
            }
            //同步菜单
            $response = RbacHelper::syncMenu($menus);
            if ($response === true) {
                //同步权限
                $response = RbacHelper::syncPermission(Auth::getDefaultDriver());
                if ($response !== true) {
                    return $this->sendError($response);
                }
            } else {
                return $this->sendError($response);
            }
    
            return $this->sendSuccess();
        }
    
        /**
         * 获取用户菜单
         *
         * @return array
         */
        public function userMenuList()
        {
            $menus = RbacHelper::userMenu();
            return $this->sendSuccess($menus);
        }
    
        /**
         * 获取全部菜单
         *
         * @return array
         */
        public function menuList()
        {
            $menus = RbacHelper::menuList();
            return $this->sendSuccess($menus);
        }
    
    }
    ```

- 前端页面加上`菜单管理页面`用来推送菜单文件,推送菜单到微服务.后期增加菜单和路由可以通过此页面同步

- 如果需要实现路由分组功能,复制`Common/Rbac/Library/Commands/operation-{module}.php`文件到文件夹.文件夹可以自己定义.实现接口`Config的getOperationFilePath方法`.方法返回值就是存储路径,不包含文件.文件格式为`operation-{module}.php` module是模块名称.比如后端(admin)就是`operation-admin.php`.

- 执行`php artisan rbac:operation:push {module}`推送功能.

- 在微服务配置权限
