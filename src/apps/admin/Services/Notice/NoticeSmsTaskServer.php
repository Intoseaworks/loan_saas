<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Notice;

use Admin\Services\BaseService;
use Admin\Services\Upload\UploadServer;
use Common\Models\Notice\SmsTask;
use Common\Models\Risk\RiskBlacklist;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class NoticeSmsTaskServer extends BaseService {

    public function saveTask($attributes) {
        if (isset($attributes["id"]) && $attributes["id"]) {
            $id = $attributes["id"];
            unset($attributes["id"]);
            $task = SmsTask::model()->getOne($id);
            $res = false;
            if ($task) {
                $res = $task->saveModel($attributes);
            }
        } else {
            $attributes['merchant_id'] = MerchantHelper::getMerchantId();
            $res = SmsTask::model()->create($attributes);
        }
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function getTaskList($params)
    {
        $datas = SmsTask::model()->getTaskList($params);
        return $datas;
    }
}
