<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Crm;

use Admin\Imports\Crm\CollectionTemplateListImport;
use Admin\Services\BaseService;
use Common\Models\Crm\CollectionSmsTemplate;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Maatwebsite\Excel\Facades\Excel;

class CollectionSmsTemplateServer extends BaseService {

    public function save($attributes) {
        $attributes['admin_id'] = LoginHelper::getAdminId();
        $attributes['merchant_id'] = MerchantHelper::getMerchantId();
        if (isset($attributes["id"]) && $attributes["id"]) {
            $id = $attributes["id"];
            unset($attributes["id"]);
            $res = CollectionSmsTemplate::model()->where(['id' => $id])->update($attributes);
        } else {
            $res = CollectionSmsTemplate::model()->createModel($attributes);
        }
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function upload($batchInfo) {
        $res = Excel::toArray(new CollectionTemplateListImport(), $batchInfo['file']);
        dispatch(new \Common\Jobs\Crm\UploadCollectionSmsTemplateJob(MerchantHelper::getMerchantId(),$res, LoginHelper::getAdminId()));
        return $this->outputSuccess("success " . count($res[0]) . "!");
    }

    public function list($params) {
        $query = CollectionSmsTemplate::query()->whereHas('admin')->where("merchant_id",MerchantHelper::getMerchantId());
        if (isset($params['collection_level']) && !is_array($params['collection_level'])) {
            $query->where("collection_level", "LIKE", "%{$params['collection_level']}%");
        }
        if (isset($params['status'])) {
            $query->where("status",$params['status']);
        }else{
            $query->where("status",1);
        }
        if (isset($params['collection_level']) && is_array($params['collection_level'])) {
            $query->whereIn("collection_level", $params['collection_level']);
        }
        if (isset($params['tpl_name'])) {
            $query->where("tpl_name", "LIKE", "%{$params['tpl_name']}%");
        }
        $query->orderByDesc('id');
        $res = $query->paginate();
        foreach($res as $item){
            $item->admin_name = $item->admin->nickname;
        }
        return $res;
    }

    public function getOne($id) {
        return CollectionSmsTemplate::query()->where("id", $id)->first()->toArray();
    }

}
