<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Call;

use Common\Jobs\Call\ApproveAutoJob;
use Common\Jobs\Call\CollectionJob;
use Common\Models\Approve\ApprovePool;
use Common\Models\Merchant\Merchant;
use Illuminate\Console\Command;
use Common\Jobs\Call\TelephoneTestJob;

class CallTest extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call:test {--type=} {--mid=} {--keepon} {--test_mobile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试号码 --type=[collection|approve] --mid=商铺ID --keepon 维持 --test_mobile';

    public function handle() {
        $mid = $this->option('mid');

        if ($this->option('test_mobile')) {
            \Common\Utils\MerchantHelper::setMerchantId(1);
            foreach (TelephoneTestJob::CALL_EXT_NUM as $extNum) {
                echo $extNum . ";";
                dispatch(new TelephoneTestJob($extNum));
            }
            echo PHP_EOL . "ONLY TEST MOBILE";
            exit;
        }
        $merchants = Merchant::model()->getNormalAll();

        foreach ($merchants as $merchant) {
            if ($mid && $mid != $merchant->id) {
                continue;
            }
            \Common\Utils\MerchantHelper::setMerchantId($merchant->id);
            echo "开始处理[{$merchant->id}]" . $merchant->product_name . PHP_EOL;
//            dispatch(new ApprovePoolJob($merchant->id));
            if ($this->option('type') == "collection") {
                echo "start collection" . PHP_EOL;
                dispatch(new CollectionJob($merchant->id));
            }
            if ($this->option('type') == "approve") {
                echo "start approve" . PHP_EOL;
                $merchantId = array_flip(ApproveAutoJob::EXT_LIST);
                dispatch(new ApproveAutoJob($merchantId[$merchant->id] ?? ""));
            }
            if ($this->option('keepon')) {
                echo "keepon approve";
                $merchantId = array_flip(ApproveAutoJob::EXT_LIST);
                $query = ApprovePool::model()
                        ->whereIn("auto_call_status", [ApprovePool::AUTO_CALL_STATUS_FIRST_CALLING, ApprovePool::AUTO_CALL_STATUS_TWICE_CALLING])
                        ->where("auto_call_time", "<", date("Y-m-d H:i:s", time() - 60 * 20));
                if ($query->count()) {
                    dispatch(new ApproveAutoJob($merchantId[$merchant->id] ?? ""));
                    (new ApproveAutoJob("", ""))->autoCallDated();
                    echo " going";
                } else {
                    echo " null";
                }
                echo PHP_EOL;
            }
        }
    }

}
