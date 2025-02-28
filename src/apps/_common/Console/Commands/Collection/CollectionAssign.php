<?php

namespace Common\Console\Commands\Collection;

use Common\Console\Services\Collection\CollectionAssignServer;
use Common\Models\Merchant\Merchant;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class CollectionAssign extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:assign {mid?} {level_name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '催收分单';

    public function handle() {
        $assignAgain = CollectionAssignServer::server();
        if ($mid = $this->argument('mid') && $level = $this->argument('level_name')) {
            echo $mid . "=>" . $level . PHP_EOL;

            MerchantHelper::setMerchantId($mid);
            $res = $assignAgain->manualAssignOrder($level, false, false);
            echo $res->getMsg();
        } else {
            $merchants = Merchant::model()->getNormalAll();

            foreach ($merchants as $merchant) {
                echo "开始处理" . $merchant->product_name . PHP_EOL;
                MerchantHelper::setMerchantId($merchant->id);

                # 新案
                /* $assignNewOrder = CollectionAssignServer::server();
                  $assignNewOrder->assignNewOrder();
                  $this->info($assignNewOrder->getMsg()); */
                # 案件流转
                /* $assignAgain = CollectionAssignServer::server();
                  $assignAgain->assignAgain();
                  $this->info($assignAgain->getMsg()); */
                # 所有案件流转，兼容预提醒

                $assignAgain->assignOrder();
                $this->info($assignAgain->getMsg());
            }
        }
    }

}
