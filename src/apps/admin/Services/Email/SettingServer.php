<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Email;

use Admin\Services\BaseService;
use Common\Models\Email\EmailUser;
use Common\Utils\MerchantHelper;
use Common\Utils\LoginHelper;
use Common\Models\Email\EmailTpl;
use Admin\Imports\Email\EmailTplImport;
use Maatwebsite\Excel\Facades\Excel;
use Common\Models\Collection\CollectionSetting;

class SettingServer extends BaseService {

    public function userView($id) {
        $query = EmailUser::model()->isActive()->with(['staff']);
        $query->where("id", $id);
        return $query->first();
    }

    public function userIndex($params) {
        $query = EmailUser::model()->isActive()->with(['staff']);
        if ($params['email']) {
            $query->where("email", "LIKE", "%{$params['email']}%");
        }
        return $query->orderByDesc("id")->get()->toArray();
    }

    public function userSave($params) {

        $data = [
            "merchant_id" => MerchantHelper::getMerchantId(),
            "email" => $params['email'],
            "password" => $params['password'],
            'admin_id' => LoginHelper::getAdminId()
        ];
        if (isset($params['id']) && $params['id']) {
            if (EmailUser::model()->isActive()->where("id", "<>", $params['id'])->where("email", $params['email'])->exists()) {
                return $this->outputError("The email existing");
            }
            $res = EmailUser::model()->newQuery()->where("id", $params['id'])->update($data);
        } else {
            if (EmailUser::model()->isActive()->where("email", $params['email'])->exists()) {
                return $this->outputError("The email existing");
            }
            $res = EmailUser::model()->createModel($data);
        }
        return $this->outputSuccess("success!");
    }

    public function userRemove($id) {
        EmailUser::model()->newQuery()->where("id", $id)->update(['status' => EmailUser::STATUS_FORGET]);
        return $this->outputSuccess();
    }

    public function tplIndex($params) {
        $query = EmailTpl::model()->isActive()->with(['staff']);
        if (isset($params['type'])) {
            $query->where("type", $params['type']);
        }
        if (isset($params['subject'])) {
            $query->where("subject", "LIKE", "%{$params['subject']}%");
        }
        return $query->orderByDesc("id")->get()->toArray();
    }

    public function tplSave($params) {
        $data = [
            "merchant_id" => MerchantHelper::getMerchantId(),
            "collection_level" => $params['collection_level'],
            "subject" => $params['subject'],
            "content" => $params['content'],
            "type" => $params['type'],
            'admin_id' => LoginHelper::getAdminId()
        ];
        if (isset($params['id']) && $params['id']) {
            if (EmailTpl::model()->isActive()->where("id", "<>", $params['id'])->where("subject", $params['subject'])->exists()) {
                return $this->outputError("The TPL existing");
            }
            $res = EmailTpl::model()->newQuery()->where("id", $params['id'])->update($data);
        } else {
            if (EmailTpl::model()->isActive()->where("subject", $params['subject'])->exists()) {
                return $this->outputError("The TPL existing");
            }
            $res = EmailTpl::model()->createModel($data);
        }
        if ($res) {
            return $this->outputSuccess("Success!");
        }
        return $this->outputError("Failed to save");
    }

    public function tplRemove($id) {
        EmailTpl::model()->newQuery()->where("id", $id)->update(['status' => EmailUser::STATUS_FORGET]);
        return $this->outputSuccess();
    }

    public function collectionLevel() {
        $data = CollectionSetting::model()->getSetting('');
        $rule = json_decode(array_get($data, 'rule', '{}'), true);
        return $rule;
    }

    public function import($params) {
        $res = Excel::toArray(new EmailTplImport(), $params['file']);
        if (isset($res[0]) && count($res[0]) >= 2) {
            foreach ($res[0] as $k => $item) {
                if ($k > 0) {
                    EmailTpl::updateOrCreateModel(
                            [
                                "merchant_id" => MerchantHelper::getMerchantId(),
                                "collection_level" => $item[2],
                                "subject" => $item[0],
                                "content" => $item[3],
                                "type" => $item[1],
                                'admin_id' => LoginHelper::getAdminId()
                            ],
                            [
                                "merchant_id" => MerchantHelper::getMerchantId(),
                                "collection_level" => $item[2],
                                "subject" => $item[0],
                    ]);
                }
            }
        }

        return $this->outputSuccess("success " . (count($res[0]) - 1) . "!");
    }

}
