<?php

namespace Common\Providers;

use Common\Events\Broadcast\AdminMessageBroadcast;
use Common\Validators\Validation;
use DB;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * boot
     */
    public function boot()
    {
        Validator::resolver(function ($translator, $data, $rules, $messages) {
            return new Validation($translator, $data, $rules, $messages);
        });

        $this->_bootBroadcast();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('config.app_sql_log')) {
            DB::listen(function (QueryExecuted $query) {
                $sqlWithPlaceholders = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
                $bindings = $query->connection->prepareBindings($query->bindings);
                $pdo = $query->connection->getPdo();
                Log::info(vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings)));
            });
        }

        // 邮箱
        $this->app->singleton('mailer', function ($app) {
            return $app->loadComponent('mail', 'Illuminate\Mail\MailServiceProvider', 'mailer');
        });

        $this->app->singleton(
            \Illuminate\Contracts\Filesystem\Factory::class,
            function ($app) {
                return new \Illuminate\Filesystem\FilesystemManager($app);
            }
        );

    }

    /**
     * 广播相关boot
     * 广播认证接口见：broadcasting/auth
     */
    private function _bootBroadcast()
    {
        Broadcast::channel(AdminMessageBroadcast::getChannelName('{merchantId}'), function ($user, $merchantId) {
            $id = isset($user) && $user->merchant_id ? (int)$user->merchant_id : null;
            return $id === (int)$merchantId;
        });
    }
}
