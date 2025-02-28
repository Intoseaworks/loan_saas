<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Common\Services\Marketing;

use Admin\Imports\Sms\SmsTplImport;
use Common\Models\Marketing\GsmSender;
use Common\Models\Marketing\GsmTask;
use Common\Models\Marketing\GsmTpl;
use Common\Services\BaseService;
use Common\Utils\Data\DateHelper;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Maatwebsite\Excel\Facades\Excel;

class GsmTaskServer extends BaseService {

    public function getSendIdList($params) {
        $perPage = $params['size'] ?? 20;
        $query = GsmSender::model()->where("status", "1");
        if (isset($params['keywords'])) {
            if (is_array($params['keywords'])) {
                $query->whereIn("sender", $params['keywords']);
            } else {
                $query->where("sender", $params['keywords']);
            }
        }
        $query->orderByDesc("id");
        return $query->paginate($perPage);
    }

    public function saveSendId($params) {
        if (isset($params['sender']) && $params['sender']) {
            $params['sender'] = explode(',', $params['sender']);
            $success = 0;
            foreach ($params['sender'] as $item) {
                $item = trim($item);
                $insert = ['sender' => $item, 'admin_id' => LoginHelper::getAdminId()];
                $res = GsmSender::model()->updateOrCreateModel($insert, ['sender' => $item]);
                if ($res) {
                    $success++;
                }
            }
            return "Successful {$success}";
        }
        throw new \Common\Exceptions\ApiException("参数错误");
    }

    public function removeSendId($id) {
        return GsmSender::model()->where(['id' => $id])->update(['status' => '2']);
    }

    public function getGsmTplList($params) {
        $perPage = $params['size'] ?? 20;
        $query = GsmTpl::model()->where("status", "1");
        if (isset($params['merchant_id']) && is_array($params['merchant_id'])) {
            $query->whereIn("merchant_id", $params['merchant_id']);
        }
        if (isset($params['tpl_name'])) {
            $query->whereIn("tpl_name", $params['tpl_name']);
        }

        $query->orderByDesc("id");
        $list = $query->paginate($perPage);
        foreach ($list as $item) {
            $item['merchant_name'] = \Common\Models\Merchant\Merchant::model()->getById($item['merchant_id'])->product_name;
        }
        return $list;
    }

    public function saveGsmTpl($params) {
        $insert = [
            "merchant_id" => $params['merchant_id'] ?? "",
            "tpl_name" => $params['tpl_name'] ?? "",
            "tpl_content" => $params['tpl_content'] ?? "",
        ];
        if (isset($params['id'])) {
            return GsmTpl::model()->where(['id' => $params['id']])->update($insert);
        } else {
            return GsmTpl::model()->updateOrCreateModel($insert, ["merchant_id" => $insert['merchant_id'], "tpl_name" => $insert['tpl_name']]);
        }
    }

    public function removeGsmTpl($id) {
        return GsmTpl::model()->where(['id' => $id])->where(['id' => $id])->update(['status' => '2']);
    }

    public function importGsmTpl($params) {
        $res = Excel::toArray(new SmsTplImport, $params['file']);
        $success = 0;
        foreach ($res[0] as $i => $item) {
            $merchantId = \Common\Models\Merchant\Merchant::model()->getIdByName($item[0]);
            if ($merchantId) {
                try {
                    $res = GsmTpl::model()->updateOrCreateModel([
                        "merchant_id" => \Common\Models\Merchant\Merchant::model()->getIdByName($item[0]),
                        "tpl_name" => $item[1],
                        "tpl_content" => $item[2],
                        "admin_id" => LoginHelper::getAdminId(),
                        "updated_at" => DateHelper::dateTime(),
                            ],
                            [
                                "merchant_id" => \Common\Models\Merchant\Merchant::model()->getIdByName($item[0]),
                                "tpl_name" => $item[1],
                    ]);
                    if ($res) {
                        $success++;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return "Success total {$success}.";
    }

    public function getGsmTaskList($params) {
        $perPage = $params['size'] ?? 20;
        $query = GsmTask::model()->where("status", "1");
        if (isset($params['task_name'])) {
            if (is_array($params['task_name'])) {
                $query->whereIn("task_name", $params['task_name']);
            } else {
                $query->where("task_name", $params['task_name']);
            }
        }
        if (isset($params['merchant_id'])) {
            if (is_array($params['merchant_id'])) {
                $query->whereIn("merchant_id", $params['merchant_id']);
            } else {
                $query->where("merchant_id", $params['merchant_id']);
            }
        }
        if (isset($params['created_at'])) {
            if (is_array($params['created_at'])) {
                $query->whereBetween("created_at", [$params['created_at'][0], $params['created_at'][1]]);
            }
        }
        $query->orderByDesc("id");
        $list = $query->paginate($perPage);
        foreach ($list as $item) {
            $item['merchant_name'] = \Common\Models\Merchant\Merchant::model()->getById($item['merchant_id'])->product_name;
            if($item['if_now_send'] == '1'){
                $item['send_time'] = '';
            }
        }
        return $list;
    }

    public function saveGsmTask($params) {
        $sendTime = date("Y-m-d H:i:s");
        $insert = [
            "task_name" => $params['task_name'],
            "send_times" => $params['send_times'],
            "send_time" => $params['send_time'] ?: $sendTime,
            "if_now_send" => isset($params['send_time']) && $params['send_time'] ? 0 : 1,
            "send_hz" => $params['send_hz'],
            "telephones" => $params['telephones'],
            "telephone_count" => count(GsmTask::model()->getTelephones($params['telephones']))
        ];
        if (isset($params['id'])) {
            return GsmTask::model()->where(['id' => $params['id']])->update($insert);
        } else {
            if (isset($params['merchant_id']) && is_array($params['merchant_id'])) {
                $success = 0;
                foreach ($params['merchant_id'] as $merchantId) {
                    $insert["merchant_id"] = $merchantId;
                    $res = GsmTask::model()->createModel($insert);
                    if ($insert['if_now_send'] == 1) {
                        $res->run_times += 1;
                        $res->last_runtime_start = $sendTime;
                        $res->save();
                        dispatch(new \Common\Jobs\Marketing\GsmTaskJob($res->id));
                    }
                    if ($res) {
                        $success++;
                    }
                }
                return "Successful {$success}";
            }
        }
    }

    public function removeGsmTask($id) {
        return GsmTask::model()->where(['id' => $id])->where(['id' => $id])->update(['status' => '2']);
    }

}
