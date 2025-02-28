<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Api\Controllers\Auth;

use Api\Rules\Auth\AuthRule;
use Api\Services\Auth\Card\AuthCardServer;
use Api\Services\Auth\Face\AuthFaceServer;
use Api\Services\Auth\AuthServer;
use Api\Services\Auth\Ocr\OcrServer;
use Api\Services\Auth\Skip\SkipServer;
use Api\Services\User\UserAuthBlackServer;
use Common\Redis\CommonRedis;
use Common\Response\ApiBaseController;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;

class AuthController extends ApiBaseController
{
    public function index()
    {
        $user = $this->identity();
        $param = $this->request->all();
        $data = AuthServer::server()->getList($param);
        return $this->resultSuccess($data);
    }

    /**
     * @param AuthRule $rule
     * @suppress PhanUndeclaredMethod
     * @return array
     */
    public function view(AuthRule $rule)
    {
        if (!$rule->validate(AuthRule::SCENARIO_DETAIL, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(AuthServer::server()->getOne($this->request->input('id'))->getText());
    }

    public function ocr(AuthRule $rule)
    {
        $user = $this->identity();
        $params = $rule->validateE($rule::SCENARIO_OCR);
        $type = array_get($params, 'type');
        $ocrServer = new OcrServer();
        $ocrServer->auth($user, $type);
        return $this->resultSuccess($ocrServer->getData(), t('认证成功', 'auth'));
    }

    public function checkCard(AuthRule $rule)
    {
        $user = $this->identity();
        $params = $rule->validateE($rule::SCENARIO_CHECK);
        $authCardServer = new AuthCardServer($params);
        $authCardServer->updateCheckCard();
        return $this->resultSuccess($authCardServer->getData(), t('认证成功', 'auth'));
    }

    public function face(AuthRule $rule)
    {
        $user = $this->identity();
        $params = $rule->validateE($rule::SCENARIO_FACE);
        $authFaceServer = new AuthFaceServer();
        $faceAll = $this->request->file();
        $requestId = $this->request->get('request_id');
        $authFaceServer->uploadFace($faceAll, $requestId);
        return $this->resultSuccess($authFaceServer->getData(), t('认证成功', 'auth'));
    }

    public function facebook()
    {
        //var_dump(EmailHelper::send('test', 'facebook-app', 'chengxusheng1114@dingtalk.com'));exit();
        $user = $this->identity();
        $status = $this->getParam('status');
        $taskid = $this->getParam('taskid');
        EmailHelper::send([
            'user_id' => $user->id,
            'status' => $status,
            'taskid' => $taskid,
        ], 'facebook-app', 'chengxusheng1114@dingtalk.com');
        if ($status != 'success') {
            return $this->resultFail(t('验证不通过', 'auth'));
        }
        $cacheKey = 'auth:facebook:' . $user->id;
        CommonRedis::redis()->set($cacheKey, 1, 120);
        return $this->resultSuccess(t('验证通过', 'auth'));
    }

    public function aadhaarKYC(AuthRule $rule)
    {
        $user = $this->identity();

        /*$allData = $this->request->all();
        DingHelper::notice(json_encode([
            'data' => $allData,
        ], 256), '终端aadhaarKYC回调测试', DingHelper::AT_CXS, false);*/

        $params = $rule->validateE($rule::SCENARIO_FACE);
        $authCardServer = new AuthCardServer($params);
        $aadhaarImg = $this->request->file('file');
        $requestId = $this->request->get('request_id');
        $authCardServer->authAadhaar($aadhaarImg, $requestId);
        return $this->resultSuccess($authCardServer->getData(), t('认证成功', 'auth'));
    }

    public function skip()
    {
        $user = $this->identity();
        $params = $this->getParams();
        $type = array_get($params, 'type');
        $ocrServer = new SkipServer();
        $ocrServer->auth($user, $type);
        return $this->resultSuccess($ocrServer->getData(), t('认证成功', 'auth'));
    }

    public function noAuthList(){
        $user = $this->identity();
        $list = \Common\Services\User\UserAuthServer::server()->getNoCompletedList($user);
        if($list){
            return $this->resultFail("The data is not completed", $list);
        }
        return $this->resultSuccess();
    }
}
