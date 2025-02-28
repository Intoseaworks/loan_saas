<?php

/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-29
 * Time: 21:45
 */

namespace Common\Console\Commands\User;

use Illuminate\Console\Command;
use Common\Models\User\UserAuth;

class UserAuthCmd extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:auth:expires';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用户授权过期';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        foreach (UserAuth::AUTH_CHECK_LIST as $key => $value) {
            $date = date("Y-m-d", time() - $value['daysValid'] * 86400);
            $list = UserAuth::model()->where("auth_status", 1)->where("status",'1')->where("type", $key)->where("time", "<=", $date)->get();
            foreach($list as $item){
                $delSql = "delete from user_init_step where user_id='{$item->user_id}'";
                \DB::update($delSql);
            }
            $sql = "UPDATE user_auth SET auth_status=2,`updated_at`=now() WHERE auth_status=1 AND `status`=1 AND `type`='{$key}' AND time<='{$date}'";
            //echo PHP_EOL;
            \DB::update($sql);
        }
        echo PHP_EOL . "OVER";
    }

}
