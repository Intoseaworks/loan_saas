<?php

use Yunhan\Utils\Env;

require_once __DIR__ . '/../vendor/autoload.php';

\Yunhan\Utils\Env::load(__DIR__ . '/../');

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Yunhan\Application(
    realpath(__DIR__ . '/../')
);



$app->withFacades(true, [
    \Illuminate\Support\Facades\Event::class => 'LumenEvent',
]);

$app->withEloquent();

// 配置
$app->configure('config');
// 广播配置
$app->configure('broadcasting');
// 日志
$app->configure('logging');
//域名配置
$app->configure('domain');
//商户系统配置
$app->configure('saas-master');
//下拉配置
$app->configure('option');
//lumen文件系统
$app->configure('filesystems');
//jwt-token
$app->configure('jwt');
//auth
$app->configure('auth');
//业务配置
$app->configure('saas-business');
//cors
$app->configure('cors');
//FCM
$app->configure('dingtalk');
//risk
$app->configure('risk');
//mail
$app->configure('mail');
//cashnow
//$app->configure('cashnow');
$app->configure('thirdparty-invoking');
$app->configure('geoip');
$app->configure('payment');

//越南配置
$app->configure('sms');
$app->configure('dict');

if (!Env::isProd()) {
    // swagger
    $app->configure('swagger-lume');
}
/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Common\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    Common\Console\Kernel::class
);

$app->singleton('filesystem', function ($app) {
    return $app->loadComponent('filesystems', 'Illuminate\Filesystem\FilesystemServiceProvider', 'filesystem');
});

$app->alias('cache', 'Illuminate\Cache\CacheManager');

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/
# 未使用暂不开启
$app->middleware([
    Barryvdh\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    # jwt身份认证
    'auth' => \Common\Middleware\Authenticate::class,
    'setMerchant' => Common\Middleware\SetMerchant::class,
    //Excel中间件,注册一些excel相关的部件
    'excel' => \Common\Middleware\ExcelMiddleware::class,
    //测试中间件
    'TEST' => \Common\Middleware\TestMiddleware::class,
    // 微服务中间件流程
    'auth.services' => \Common\Middleware\ServicesAuth::class,
    // 返回值日志
    'responseMiddleware' => \Common\Middleware\ResponseMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// 开发环境
if (\Yunhan\Utils\Env::isDevOrTest()) {
    $app->register(YunhanDev\YunhanDevServiceProvider::class);
    $app->register(Jormin\DDoc\DDocServiceProvider::class);
    $app->register(\Common\Providers\BackupDemoSqlServiceProvider::class);
}

$app->register(\Common\Providers\AppServiceProvider::class);
$app->register(\Common\Providers\EventServiceProvider::class);
//redis
$app->register(Illuminate\Redis\RedisServiceProvider::class);
//rbac认证
$app->register(\Common\Services\Rbac\Providers\RbacServiceProvider::class);
$app->register(\Common\Services\Rbac\Library\RbacUtilServiceProvider::class);

//jwt
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
// aliOss storage
$app->register(\Common\Services\AliOssStorage\AliOssServiceProvider::class);
// ufile storage
$app->register(\Common\Services\UFileStorage\UFileServiceProvider::class);
// cors
$app->register(Barryvdh\Cors\ServiceProvider::class);
// FCM
$app->register(LaravelFCM\FCMServiceProvider::class);
$app->register(\Lingan\DingtalkNotice\DingtalkNoticeServiceProvider::class);
//class_alias(\LaravelFCM\Facades\FCM::class, 'FCM');
//class_alias(\LaravelFCM\Facades\FCMGroup::class, 'FCMGroup');

$app->register(\Maatwebsite\Excel\ExcelServiceProvider::class);


$app->register(\Torann\GeoIP\GeoIPServiceProvider::class);
$app->register(\Milon\Barcode\BarcodeServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([], function ($router) {
    $routeList = [
        __DIR__ . "/../apps/admin/routes/index.php",
        __DIR__ . "/../apps/api/routes/index.php",
        __DIR__ . "/../apps/approve/admin/routes/index.php",
        __DIR__ . "/../apps/risk/Api/routes/index.php",
    ];

    foreach ($routeList as $list) {
        require $list;
    }
});

return $app;
