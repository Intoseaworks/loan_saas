<?php

namespace Common\Console\Commands\Common;

use Common\Models\User\UserAuth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ChangeUserAuthOverdays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:change-user-auth-overdays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '作废超过180的用户认证信息';

    /**
     * @return void
     */
    public function handle()
    {
//        $date = date('Y-m-d H:i:s',strtotime('-180 days'));
//        $take = 2000;
//        while ($one= UserAuth::model()->where('status','!=',0)->where('status','!=',2)->where('time','<', $date)->take($take)->get()) {
//            if ($one){
//                foreach ($one as $v){
//                    $v->status = 2;
//                    $v->save();
//                    echo  '更改认证记录'.$v->id.PHP_EOL;
//                }
//            }else{
//                echo '无需更改的认证记录';
//                exit;
//            }
//        }
    }
}
