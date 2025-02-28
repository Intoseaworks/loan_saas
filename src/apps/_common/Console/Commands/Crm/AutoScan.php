<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Crm;

use Common\Jobs\Crm\UpdateCustomerJob;
use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Services\Crm\CustomerServer;
use Illuminate\Console\Command;

class AutoScan extends Command {
    # 每次提取用户数

    const PER_NUM = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:autoscan {userId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '扫描用户表合并到CRM客户 crm:autoscan {userId?}';

    public function handle() {
        if ($uid = $this->argument('userId')) {
            echo $uid . PHP_EOL;
            CustomerServer::server()->getCrmCustomer(User::model()->getOne($uid));
        } else {
            /** 扫描用户表 */
            $this->scanUser();
        }
        echo "End";
    }

    private function scanUser($page = 1) {
        $date = date("Y-m-d", strtotime('-1 day'));
        if ($page == 1) {
            echo "user start " . $date;
            $users = User::model()->newQuery()->where("updated_at", ">", $date)->get();
            foreach ($users as $user) {
                echo ".";
                $this->exist($user);
            }
        }
        /*
        echo "start " . $page * self::PER_NUM . PHP_EOL;
        echo "order start " . $date;
        $orders = Order::model()->newQuery()->where("updated_at", ">", $date)->paginate(self::PER_NUM, ['*'], 'page', $page);
        foreach ($orders->items() as $order) {
            echo ".";
            $this->exist($order->user);
        }
        if ($orders->lastPage() > $page) {
            $this->scanUser($page + 1);
        }
         * 
         */
        echo " end";
    }

    private function exist(User $user) {
        $job = new UpdateCustomerJob($user);
        $job->queue = "crm-customer-update-new-9";
        dispatch($job);
    }

}
