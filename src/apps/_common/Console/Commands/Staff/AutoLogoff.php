<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Staff;

use Common\Models\Collection\CollectionOnlineLog;
use Common\Models\Merchant\Merchant;
use Common\Utils\Data\DateHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;
use DB;
use Illuminate\Console\Command;

class AutoLogoff extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:auto-logoff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '操作员自动下线';

    public function handle() {
        $this->logoff();
    }

    protected function logoff() {
        $merchants = Merchant::model()->getNormalAll();
        foreach ($merchants as $merchant) {
            echo "开始处理" . $merchant->product_name . PHP_EOL;
            MerchantHelper::setMerchantId($merchant->id);
            $sql = "select (select `status` from collection_online_log where admin_id=staff.id AND created_at>date(now()) order by id desc limit 1) as online_status,staff.* from staff where `status` = 1 AND merchant_id='{$merchant->id}' having online_status=1";
            $list = DB::select($sql);
            foreach ($list as $item) {
                $lastLog = CollectionOnlineLog::model()->newQuery()->where("admin_id", $item->id)->orderByDesc('id')->first();
                $insert = [
                    "status" => CollectionOnlineLog::STATUS_OFFLINE,
                    "status_value" => "system out",
                    "admin_id" => $item->id,
                    "created_at" => DateHelper::dateTime(),
                    "ip" => '0.0.0.0',
                ];
                if ($lastLog) {
                    if (CollectionOnlineLog::STATUS_ONLINE == $lastLog->status) {
                        $lastLog->use_time = time() - strtotime($lastLog->created_at);
                        $lastLog->save();
                        CollectionOnlineLog::model()->createModel($insert);
                    }
                }
            }
        }
    }

}
