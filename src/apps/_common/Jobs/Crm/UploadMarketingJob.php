<?php

namespace Common\Jobs\Crm;

use Admin\Models\User\UserBlack;
use Common\Jobs\Job;
use Common\Models\Crm\CrmMarketingBatch;
use Common\Models\Crm\CrmMarketingList;
use Common\Models\Crm\CrmWhiteFailed;
use Admin\Services\Crm\MarketingServer;
use Common\Models\Staff\Staff;
use Common\Utils\MerchantHelper;

class UploadMarketingJob extends Job {

    public $queue = 'telemarketing2';
    public $tries = 3;
    private $_batch;
    private $_list;
    private $_adminId;

    public function __construct(CrmMarketingBatch $batch, $res, $adminId) {
        $this->_batch = $batch;
        $this->_list = $res;
        $this->_adminId = $adminId;
    }

    public function handle() {
        $batch = $this->_batch;
        $badData = [];
        $matchRule = json_decode($batch->match_rule, 1);
        $success = 0; #成功数
        $merchantId = $batch->merchant_id;
        MerchantHelper::helper()->setMerchantId($merchantId);
        echo "start ".$batch->id.PHP_EOL;
        if($batch->success_count>0){
            return;
        }
        foreach ($this->_list[0] as $i => $item) {
            if ($i > 0) {
                if(!$item[0] && !$item[1]){
                    continue;
                }
                if (!CrmMarketingList::model()->newQuery()->where("batch_id", $batch->id)->where("telephone", $item[0])->exists()) {
                    $model = [
                        "merchant_id" => $merchantId,
                        'batch_id' => $batch->id,
                        'telephone' => $item[0] ?? "",
                        'type' => $item[1] ?? "",
                        'email' => $item[2] ?? "",
                        'fullname' => $item[3] ?? "",
                        'birthday' => $item[4] ?? "",
                        'id_type' => $item[5] ?? "",
                        'id_number' => $item[6] ?? "",
                        'remark' => $item[7] ?? "",
                        'indate' => $batch->indate,
                        'admin_id' => $batch->admin_id
                    ];
                    $pass = true;
                    $vailed = MarketingServer::server()->checkData($model);
                    if ($vailed->fails()) {
                        $pass = false;
                        $badData[] = ["msg" => "验证失败","data" => $model, "error" => $vailed->getMessageBag()->messages()];
                    } else {
                        $batch->total_count += 1;
                    }
                    # 黑名单
                    if ($query = MarketingServer::server()->checkBlackList($model, $matchRule)) {
                        if ($query->count() > 0) {
                            $badData[] = ["msg" => "黑名单命中","data" => $model, "error" => $query->get()->toArray()];
                            $pass = false;
                        }
                    }

                    #灰名单
                    if (UserBlack::model()->isActive()->whereTelephone($model['telephone'])->exists()) {
                        $badData[] = ["msg" => "灰名单命中","data" => $model, "error" => "灰名单不导入"];
                        $pass = false;
                    }

                    /*
                    if ($query = MarketingServer::server()->badUser($model['telephone'], $matchRule)) {
                        if (count($query) > 0) {
                            $badData[] = ["msg" => "未完成订单","data" => $model, "error" => $query];
                            $pass = false;
                        }
                    }
                    */

                    if ($pass) {
                        $res = CrmMarketingList::model()->createModel($model);
                        if ($res) {
                            MarketingServer::server()->intoCustomer($res);
                            $success++;
                        }
                    }
                }
            }
        }
        $failedModel = [
            'type' => 'MARKETING',
            'failed_data' => json_encode($badData),
            'batch_id' => $batch->id
        ];
        CrmWhiteFailed::model()->createModel($failedModel);
        if ($success) {
            $batch->success_count = $success;
            $batch->save();
        }
    }

}
