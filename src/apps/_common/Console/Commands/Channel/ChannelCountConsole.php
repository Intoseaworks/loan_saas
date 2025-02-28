<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/14
 * Time: 17:14
 */

namespace Common\Console\Commands\Channel;

use Carbon\Carbon;
use Common\Models\Channel\Channel;
use Common\Models\Channel\ChannelCount;
use Common\Models\User\User;
use Common\Redis\Channel\ChannelRecordRedis;
use Illuminate\Console\Command;

class ChannelCountConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channel:count {date?}';

    /**
     * The console command description.
     *
     * 可手动统计3天内数据
     * php artisan channel:count 2019-02-14
     *
     * @var string
     */
    protected $description = '渠道统计：统计每天用户的注册下载等数据';

    /**
     * @var Carbon
     */
    protected $date;

    public function handle()
    {
        $date = Carbon::yesterday()->toDateString();
        if ($dateManual = $this->argument('date')) {
            $date = $dateManual;
        }

        $dateAfter = Carbon::parse($date)->addDays(1)->toDateString();

        $ids = Channel::getNormalIds();

        foreach ($ids as $id) {
            $model = ChannelCount::firstOrNewModel([], ['count_at' => $date, 'channel_id' => $id]);
            $model->channel_id = $id ?? 0;
            $model->count_at = $date;
            $model->{ChannelCount::REGISTER_UV} = User::model()
                ->whereBetween('created_at', [$date, $dateAfter])->where('channel_id', $id)->count();

            foreach (array_keys(ChannelCount::COUNT_TYPE) as $type) {
                $count = ChannelRecordRedis::redis()->getValue($id . ':' . $type, $date);
                $model->{$type} = $count ?? 0;
            }
            $model->save();
        }

    }

}