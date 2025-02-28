<?php

namespace Common\Jobs\Crm;

use Common\Jobs\Job;
use Common\Models\Crm\CrmWhiteBatch;
use Common\Models\Crm\CrmWhiteFailed;
use Common\Models\Crm\CrmWhiteList;
use Common\Utils\LoginHelper;
use Admin\Models\User\UserBlack;
use Admin\Services\Crm\WhiteListServer;
use Common\Models\Staff\Staff;
use Common\Utils\MerchantHelper;

class UploadWhitelistJob extends Job {

    public $queue = 'telemarketing2';
    public $tries = 3;
    private $whiteBatch;
    private $list;
    private $_adminId;

    public function __construct(CrmWhiteBatch $batch, $res, $adminId) {
        $this->whiteBatch = $batch;
        $this->list = $res;
        $this->_adminId = $adminId;
    }

    public function handle() {
        $batch = $this->whiteBatch;
        $badData = [];
        $success = 0; #成功数
        MerchantHelper::helper()->setMerchantId($batch->merchant_id);
        echo "start ".$batch->id.PHP_EOL;
        if($batch->success_count>0){
            return;
        }
        foreach ($this->list[0] as $i => $item) {
            if ($i > 0) {
                if (!$item[0] && !$item[1]) {
                    continue;
                }
                if (!CrmWhiteList::model()->newQuery()->where("batch_id", $batch->id)->where("telephone", $item[0])->exists()) {
                    $model = [
                        "merchant_id" => $batch->merchant_id,
                        'batch_id' => $batch->id,
                        'telephone' => $item[0],
                        'type' => $item[1],
                        'email' => $item[2],
                        'fullname' => $item[3],
                        'birthday' => $item[4],
                        'id_type' => $item[5],
                        'id_number' => $item[6],
                        'remark' => $item[7],
                        'indate' => $batch->indate,
                        'admin_id' => $this->_adminId
                    ];
                    foreach ($model as $key => $value) {
                        if (!$model[$key]) {
                            unset($model[$key]);
                        }
                    }
                    $vailed = WhiteListServer::server()->checkData($model);
                    $pass = true;
                    if ($vailed->fails()) {
                        $pass = false;
                        $badData[] = ["msg" => "验证失败", "data" => $model, "error" => $vailed->getMessageBag()->messages()];
                    } else {
                        $batch->total_count += 1;
                    }
                    # 处理黑名单
                    if ($pass && $batch->match_blacklist == '1') {
                        if ($query = WhiteListServer::server()->checkBlackList($model)) {
                            if ($query->count() > 0) {
                                $badData[] = ["msg" => "黑名单命中", "data" => $model, "error" => $query->get()->toArray()];
                                $pass = false;
                            }
                        }
                    }
                    # 处理灰名单
                    if ($pass) {
                        switch ($batch->match_greylist_type) {
                            case "1":# 灰名单不导入
                                if (UserBlack::model()->isActive()->whereTelephone($model['telephone'])->exists()) {
                                    $badData[] = ["msg" => "灰名单命中", "data" => $model, "error" => "灰名单不导入"];
                                    $pass = false;
                                }
                                break;
                            case "2":#移出灰名单
                                UserBlack::model()->updateOrCreateModel(['status' => UserBlack::STATUS_INVALID], ['telephone' => $model['telephone']]);
                                break;
                        }
                    }
                    if ($pass) {
                        $created = CrmWhiteList::model()->createModel($model);
//                        $created = CrmWhiteList::model()->updateOrCreateModel($model, ['telephone' => $model['telephone']]);
                        if ($created) {
                            WhiteListServer::server()->intoCustomer($created);
                            $success++;
                        }
                    }
                }
            }
        }
        $failedModel = [
            'type' => 'WHITE_LIST',
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
