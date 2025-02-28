<?php

namespace Common\Console\Commands\HelperRun;

use Common\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class UserQualityAmend extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'helper-run:user-quality-amend';

    /**
     * The console command description.
     * @var string
     */
    protected $description = '修正用户quality字段值';

    public function handle()
    {
        return User::query()->where('quality', User::QUALITY_OLD)
            ->whereHas('orders', function (Builder $query) {
                $query->select(['user_id'])
                    ->groupBy('user_id')
                    ->havingRaw("count(*) < 2");
            })->update([
                'quality' => User::QUALITY_NEW,
                'quality_time' => null,
            ]);
    }

    protected function getNeedAmendUser()
    {
        return User::query()->where('quality', User::QUALITY_OLD)
            ->whereHas('orders', function (Builder $query) {
                $query->select(['user_id'])
                    ->groupBy('user_id')
                    ->havingRaw("count(*) < 2");
            })->get();
    }
}
