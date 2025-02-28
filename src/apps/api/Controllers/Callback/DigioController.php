<?php

namespace Api\Controllers\Callback;

use Api\Services\Order\OrderServer;
use Common\Jobs\ContractByDigioPdfJob;
use Common\Models\Order\OrderSignDoc;
use Common\Models\Third\ThirdPartyLog;
use Common\Response\ServicesApiBaseController;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Host\HostHelper;

class DigioController extends ServicesApiBaseController
{
    public $digioDocId;

    /**
     * 回调处理
     */
    public function signCallback()
    {
        $params = $this->getParams();

        EmailHelper::send($params, 'digio回调111', 'wangshaliang@jiumiaodai.com', false);

        $callbackStr = file_get_contents('php://input');
        EmailHelper::send($callbackStr, 'digio回调222', 'wangshaliang@jiumiaodai.com', false);

        try {
            if (!isset($params['digio_doc_id'])) {
                EmailHelper::send(['params' => $params,], 'wangshaliang@jiumiaodai.com');
                $this->addReturnHeader(false);
                exit();
            }
            $this->digioDocId = $digioDocId = $params['digio_doc_id'];
            $message = $params['message'] ?? '';

            if (!isset($params['status']) || $params['status'] != 'success') {
                $this->updateCallback(false, $message . '--digio第三方返回签约失败', false);
                exit();
            }

            $orderDigio = OrderSignDoc::model()->getByDocId($digioDocId);
            if (!$orderDigio) {
                $this->updateCallback(false, $message . '--数据库orderDigio数据不存在', false);
                exit();
            }
            $user = $orderDigio->user;
            $order = $orderDigio->order;

            if (!$user || !$user->bankcard) {
                $this->updateCallback(false, $message . '--签名用户未绑定银行卡', false);
                exit();
            }
            $newCardNo = $user->bankcard->no;

            //$order->order($newCardNo);
            OrderServer::server()->sign($user, $params);
            $this->updateCallback(true, $message);

            // 签名后合同拉取
            dispatch(new ContractByDigioPdfJob($orderDigio->id));

            $this->addReturnHeader(true);
            exit();
        } catch (\Exception $e) {
            $paramsStr = json_encode($params, true);
            $errorStr = "\n{$e->getMessage()}\nfile:{$e->getFile()}:{$e->getLine()}";
            EmailHelper::send($paramsStr . $errorStr, 'digio回调报错', 'wangshaliang@jiumiaodai.com', false);

            $this->addReturnHeader(false);
            exit();
        }
    }

    private function addReturnHeader($isSuccess = true)
    {
        $location = 'Location:' . HostHelper::getDomain();
        if ($isSuccess) {
            header($location . "/app/callback/digio/success");
        } else {
            header($location . "/app/callback/digio/fail");
        }
    }

    private function updateCallback($isSuccess = true, $message = '', $returnHeader = null)
    {
        $toStatus = $isSuccess ? ThirdPartyLog::REQUEST_STATUS_SUCCESS : ThirdPartyLog::REQUEST_STATUS_FAIL;
        $currentUrl = $this->request->url();

        $thirdPartyLog = new ThirdPartyLog();
        $thirdPartyLog->updatedByCallbackByReportId(
            $this->digioDocId,
            $toStatus,
            $this->getParams(),
            $currentUrl,
            $message
        );
        if (!is_null($returnHeader)) {
            $this->addReturnHeader($returnHeader);
        }
    }

    public function returnSuccess()
    {
        return 'success';
    }

    public function returnFail()
    {
        return 'fail';
    }
}
