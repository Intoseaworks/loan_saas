<?php

namespace Common\Providers;

use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\ServiceProvider;

class BackupDemoSqlServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        //backup
        $this->app->configure('backup');
        $params = [
            // 5 minute timeout
            'timeout' => 60 * 5,
            // 不保存表结构
            'add_extra_option' => '--no-create-info',
        ];
        config(['database.connections.mysql.dump' => $params]);
        config(['backup.cleanup.defaultStrategy.keepAllBackupsForDays' => 1]);
        config(['backup.backup.destination.disks' => ['demo-sql']]);
        $this->app->bindIf(Dispatcher::class, function () {
            return $this->notifyEntity();
        });
        $this->app->register(\Spatie\Backup\BackupServiceProvider::class);
    }

    public function notifyEntity()
    {
        return new class() implements Dispatcher
        {

            /**
             * Send the given notification to the given notifiable entities.
             *
             * @param  \Illuminate\Support\Collection|array|mixed $notifiables
             * @param  mixed $notification
             * @return void
             */
            public function send($notifiables, $notification)
            {
                //
            }

            /**
             * Send the given notification immediately.
             *
             * @param  \Illuminate\Support\Collection|array|mixed $notifiables
             * @param  mixed $notification
             * @return void
             */
            public function sendNow($notifiables, $notification)
            {
                //
            }
        };
    }

    /**
     * Boot the authentication services for the application.
     * @return void
     * @suppress PhanTypeArraySuspicious
     */
    public function boot()
    {

    }
}
