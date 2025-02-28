<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/14
 * Time: 15:49
 */

namespace Api\Controllers\Data;

use Api\Models\Plugin\Facebook;
use Api\Rules\Plugin\ThirdDataRule;
use Common\Utils\Data\DateHelper;
use Api\Services\Upload\UploadServer;
use Common\Response\ApiBaseController;
use Api\Services\Order\OrderServer;

class ThirdDataController extends ApiBaseController {

    /**
     * Facebook授权个人信息上传
     * 备注：
     * @param UploadRule $rule
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function facebook(ThirdDataRule $rule) {
        $userId = $this->identity()->id;

        $data = $this->request->all();
        if (!isset($data['apply_id']) || !$data['apply_id']) {
            $lastOrder = OrderServer::server()->getLastOrder($this->identity());
            if ($lastOrder) {
                $data['apply_id'] = $lastOrder->id;
            }
        }
        if (!$rule->validate($rule::SCENARIO_FACEBOOK_CREATE, $data)) {
            return $this->resultFail($rule->getError());
        }
        if ($file = $this->request->file('picture')) {
            if (!$fileData = UploadServer::moveFile($file)) {
                return $this->resultFail('上传文件保存失败');
            }
            unset($data['picture']);
            $data['picture'] = $fileData['path'];
        }
        $data['user_id'] = $userId;
        $data['data_id'] = $data['id'];
        //print_r($fileData);exit;
        $data['updated_at'] = DateHelper::dateTime();
        if (!Facebook::model()->updateOrCreateModel(Facebook::SCENARIO_CREATE, ['apply_id' => $data['apply_id'], 'user_id' => $userId], $data)) {
            return $this->resultFail('Facebook信息保存失败');
        }

        return $this->resultSuccess(null, "Facebook信息保存成功");
    }

}
