<?php

namespace Tests\Admin\Approve;

use Common\Models\Approve\ApproveUserPool;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\MerchantHelper;
use Tests\TestCase;

class ApproveTest extends TestCase
{
    public function testStartWork()
    {
        $params = [
            'type' => 1
        ];
        $this->json('GET', '/api/approve/first-approve/start-work', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testFirstApproveList()
    {
        $params = [
            'type' => 1
        ];
        $this->json('GET', '/api/approve/first-approve/list', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testFirstApproveStopWork()
    {
        $params = [
            'type' => 1
        ];
        $this->json('GET', '/api/approve/first-approve/stop-work', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testFirstApproveDetail()
    {
        $approveUserPool = ApproveUserPool::model()->where([
            'status' => ApproveUserPool::STATUS_CHECKING,
            'admin_id' => 5
        ])->first();
        if (!$approveUserPool) {
            echo '无待审批订单';
            exit();
        }
        $params = [
            'id' => $approveUserPool->id,
            'order_id' => $approveUserPool->order_id,
        ];
        $this->json('GET', '/api/approve/first-approve/detail', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testCallApproveDetail()
    {
        $params = [
            'id' => 89,
            'order_id' => 46604,
        ];
        $this->json('GET', '/api/approve/call-approve/detail', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData(true, true);
    }

    public function testApproveQualityDetail()
    {
        $params['id'] = 165;
        $this->json('GET', '/api/approve/approve-quality/detail', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testFirstApproveSubmit()
    {
        //初审审批拒绝
        $params = ArrayHelper::jsonToArray('{"confirm_selfie_id_phone":1,"confirm_selfie_work_phone":1,"fullname":" hfjjk rrii","confirm_fullname":2,"id_card_type":"UMID","id_card_no":"592956485659","confirm_id_card":2,"telephone":"9123000047","loan_days":2,"principal":"4000.00","user_type":"新用户","birthday":"2031-12-18T16:00:00.000Z","age":37,"gender":"Male","current_address":"fjreeeerr","base_detail_status":"manual_approve_fraud","refusal_code":"R03WD","id":"1336","order_id":"46700"}');
        //初审审批补件
        $params1 = ArrayHelper::jsonToArray('{"confirm_selfie_id_phone":1,"confirm_selfie_work_phone":1,"fullname":"tes tes tes","confirm_fullname":1,"id_card_type":"UMID","id_card_no":"562956485659","confirm_id_card":1,"telephone":"9123000046","loan_days":2,"principal":"1500.00","user_type":"新用户","birthday":"2001-01-19","age":51,"gender":"Male","current_address":"hfufhrr","base_detail_status":"manual_approve_supplementary","suspected_fraud_status":"NEED_APTITUDE_FACE","id":"1341","order_id":"46702"}');
        $this->json('POST', '/api/approve/first-approve/submit', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testCallApproveSubmit()
    {
        MerchantHelper::setMerchantId(1);
        $params = ArrayHelper::jsonToArray('{"fullname":"dhdhf fj fjfb fjdud","id_card_type":"TIN","gender":"Male","id_card_no":"806485689000","birthday":"2001-01-18T16:00:00.000Z","telephone":"9123000058","marital_status":"Married","age":51,"education_level":"Vocational Course","monthly_income":"30000 <monthly income <40000","switch":"","work_company":"dideifbfiww","work_phone":"56561888","work_experience_years":0,"industry":"Energy/Water Supply/Electricity/Gas","profession":"","working_years":"3-10 years","bank_name":"Eastwest Bank","bank_card_no":"4868358588","bank_city":null,"can_contact_time":"12:00-14:00 PM","confirm_month_income":10000,"contact_fullname0":"xbfjd","relation0":"SISTER","contact_telephone0":"9123000000","manual_call_result0":"no_answer","contact_fullname1":"ffhf","relation1":"CHILDREN","contact_telephone1":"9123126588","manual_call_result1":"no_answer","contact_fullname2":"djf","relation2":"SISTER","contact_telephone2":"9123570856","manual_call_result2":"no_answer","status":"call_approve_no_answer","emergency_contact":[{"relation":"SISTER","contactFullname":"xbfjd","contactTelephone":"9123000000","manualCallResult":"no_answer"},{"relation":"CHILDREN","contactFullname":"ffhf","contactTelephone":"9123126588","manualCallResult":"no_answer"},{"relation":"SISTER","contactFullname":"djf","contactTelephone":"9123570856","manualCallResult":"no_answer"}],"emergency_contact_other":[{"relation":"SISTER","contactFullname":"xbfjd","contactTelephone":"9123000001","manualCallResult":"no_answer"},{"relation":"CHILDREN","contactFullname":"ffhf","contactTelephone":"9123126582","manualCallResult":"no_answer"},{"relation":"SISTER","contactFullname":"djf","contactTelephone":"9123570853","manualCallResult":"no_answer"}],"social_info":{},"id":"2180","order_id":"46727"}');
        $this->json('POST', '/api/approve/call-approve/submit', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }
}
