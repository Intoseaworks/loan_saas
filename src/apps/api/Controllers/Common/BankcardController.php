<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Common;

use Api\Rules\Common\BankCardRule;
use Api\Services\Bankcard\BankcardServer;
use Api\Services\Common\DictServer;
use Common\Response\ApiBaseController;

class BankcardController extends ApiBaseController
{
    /**
     * 获取银行列表
     * @return type
     */
    public function getBankList() {
        return $this->resultSuccess(DictServer::server()->getList('BANK'), '');
    }
    
    /**
     * 获取银行卡bin（废弃）
     * @return array
     */
    public function bin()
    {
        $bankcardNo = $this->getParam('bankcard_no');
        $items = BankcardServer::server()->bin($bankcardNo);
        if(!$items){
            return $this->resultFail('获取银行卡bin失败');
        }
        return $this->resultSuccess($items);
    }
    
    /**
     * 获取分行列表 
     * @param BankCardRule $rule
     * @return type
     */
    public function getBranch(BankCardRule $rule) {
        $params = $rule->validateE($rule::SCENARIO_GET_BRANCH);
        $res = DictServer::server()->getListByArray(["parent_code" => "BRANCH", "attr2" => $params["state"], "attr1" => $params["bank"]]);
        if (!$res) {
            $res = [["code" => "OTHER", "name" => t('其他')]];
        }
        return $this->resultSuccess($res, '');
    }
    
    /**
     *
     * @param BankCardRule $rule
     *
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function bindStart(BankCardRule $rule)
    {
        $user = $this->identity();
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_BIND_SEND_CODE, $params)) {
            return $this->resultFail($rule->getError());
        }

        if (!BankcardServer::server()->canBindBankCard($user)) {
            return $this->resultFail('请先完善身份认证！');
        }

        $data = BankcardServer::server()->bindStart($user, $params);

        return $this->resultSuccess($data);
    }
    
    /**
     * 获取银行城市
     * @return type
     */
    public function getBankCity() {
        $cityList = DictServer::server()->getList('STATE');
        return $this->resultSuccess($cityList, '');
    }
    
    public function getInstitution(){
        $institutionList = config('payment.institution_list');
        return $this->resultSuccess($institutionList);
    }
    
    public function getChannel(){
        $channelList = config('payment.channel_list');
        return $this->resultSuccess($channelList);
    }
}
