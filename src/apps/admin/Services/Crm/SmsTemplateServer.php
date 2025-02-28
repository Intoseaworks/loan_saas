<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Crm;

use Admin\Services\BaseService;
use Common\Models\Crm\SmsTemplate;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;

class SmsTemplateServer extends BaseService {

    public function save($attributes) {
        $attributes['admin_id'] = LoginHelper::getAdminId();
        $attributes['merchant_id'] = MerchantHelper::getMerchantId();
        if (isset($attributes["id"]) && $attributes["id"]) {
            $id = $attributes["id"];
            unset($attributes["id"]);
            $res = SmsTemplate::model()->where(['id' => $id])->update($attributes);
        } else {
            $res = SmsTemplate::model()->createModel($attributes);
        }
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function list($params) {
        $query = SmsTemplate::query()->whereHas('admin')->where("merchant_id", MerchantHelper::getMerchantId());
        if (isset($params['status'])) {
            $query->where("status", $params['status']);
        }
        if (isset($params['tpl_name'])) {
            $query->where("tpl_name", "LIKE", "%{$params['tpl_name']}%");
        }
        $query->orderByDesc('id');
        $size = isset($params['size']) ? $params['size'] : 1000;
        $res = $query->paginate($size);
        foreach ($res as $item) {
            $item->admin_name = $item->admin->nickname;
        }
        return $res;
    }

    public function getOne($id) {
        return SmsTemplate::query()->where("id", $id)->first()->toArray();
    }

}
