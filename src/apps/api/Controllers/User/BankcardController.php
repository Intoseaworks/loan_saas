<?php

namespace Api\Controllers\User;

use Api\Models\User\UserAuth;
use Api\Rules\User\BankcardRule;
use Api\Services\Bankcard\BankcardPesoServer;
use Api\Services\User\BankCardServer;
use Api\Services\User\UserAuthServer;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Common\BankInfo;
use Common\Response\ApiBaseController;

class BankcardController extends ApiBaseController
{
    /**
     * 查找IFSC
     * @param BankcardRule $rule
     * @return array
     * @throws \Common\Exceptions\RuleException
     */
    public function lookUpIfsc(BankcardRule $rule)
    {
        $params = $rule->validateE($rule::SCENARIO_LOOK_UP_IFSC);

        $list = BankCardServer::server()->getIfscList($params);

        return $this->resultSuccess($list);
    }

    /**
     * @param BankcardRule $rule
     * @return array
     * @throws \Common\Exceptions\RuleException
     */
    public function getBranch(BankcardRule $rule)
    {
        $params = $rule->validateE($rule::SCENARIO_GET_BRANCH);

        return $this->resultSuccess(BankInfo::model()->getBranchList($params['state'], $params['city']));
    }

    /**
     *
     * @param BankcardRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(BankcardRule $rule)
    {
//        dd(444);
        $user = $this->identity();
        if ( count($user->bankCards)==5 ) {
            return $this->resultFail('bankCards is up to limit 5');
        }
        $params = $this->params;
        if (!$rule->validate($rule::STEP_SIX, $params)) {
            return $this->resultFail($rule->getError());
        }
        $params['user_id'] = $user->id;
        if ( isset($params['account_no']) && isset($params['bank_name'])
            && BankCardPeso::model()->andFilterWhere(['user_id'=>$params['user_id'],'account_no'=>$params['account_no'],'bank_name'=>$params['bank_name']])->first() ){
            return $this->resultFail('bankCards is already exits');
        }
        if (BankcardPesoServer::server()->createPeralending($params)) {
            UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_BANKCARD);
            return $this->resultSuccess();
        }
        return $this->resultFail();
        $params = $rule->validateE($rule::SCENARIO_CREATE);
        $params['card_no'] = trim($params['card_no']);

        $params['card_no'] = str_replace("\n", "", $params['card_no']);
        $bankCardServer = new BankCardServer();
        $bankCardServer->createBankCard($user, $params['card_no'], $params['ifsc']);
        if (!$bankCardServer->isSuccess()) {
            return $this->result($bankCardServer->getCode(), $bankCardServer->getMsg());
        }
        return $this->resultSuccess();
    }

    public function index()
    {
        $user = $this->identity();
//        $bankCardServer = new BankCardServer();
//        $bankCardServer->index();
        $bankCards = $user->bankCards;
        if ( isset($this->params['type']) && !empty(trim($this->params['type'])) ){
            $type = trim($this->params['type']);
            foreach ($bankCards as $k=>$bankCard){
                if ( $bankCard->payment_type != $type ){
                    unset($bankCards[$k]);
                }
            }
        }
        $bankCardsReturn = [];
        foreach ($bankCards as $bankCard){
            $bankCardsReturn[] = $bankCard;
        }
        return $this->resultSuccess($bankCardsReturn);
//        return $this->resultSuccess($bankCardServer->getData());
    }

}
