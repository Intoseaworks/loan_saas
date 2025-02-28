<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Feedback;

use Api\Rules\Feedback\FeedbackRule;
use Api\Services\Feedback\FeedbackService;
use Common\Response\ApiBaseController;
use Common\Services\Upload\UploadServer;

class FeedbackController extends ApiBaseController
{
    public function add(FeedbackRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate(FeedbackRule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $userId = 0;
        $telephone = '';
        if ($this->getParam('token')) {
            $user = $this->identity();
            $userId = $user->id;
            $telephone = $user->telephone;
        }
        
        $data = [
            'user_id' => $userId,
            'telephone' => $telephone,
            "contact_info" => $this->getParam("contact_info"),
            'content' => $this->getParam("content"),
        ];
        if (isset($params["pic1"])) {
            $pic1 = UploadServer::moveFile($this->request->file('pic1'));
            if($pic1){
                $data['pic_1'] = $pic1['path'];
            }
        }
        if (isset($params["pic2"])) {
            $pic2 = UploadServer::moveFile($this->request->file('pic2'));
            if($pic2){
                $data['pic_2'] = $pic2['path'];
            }
        }
        if (isset($params["pic3"])) {
            $pic3 = UploadServer::moveFile($this->request->file('pic3'));
            if($pic3){
                $data['pic_3'] = $pic3['path'];
            }
        }
        FeedbackService::server()->add($data);
        return $this->resultSuccess([], '提交成功');
    }

}