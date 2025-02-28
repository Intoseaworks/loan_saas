<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/20
 * Time: 11:28
 */

namespace Tests\Api\User;


use Common\Models\User\User;
use Tests\Api\TestBase;

class UserTest extends TestBase
{
    public function testHome($userId = 1155)
    {
        /** 无token/token过期 */
        $this->get('app/user/home')->seeJson([
            'code' => self::AUTHORIZATION_CODE
        ]);
        if ($userId == 0) {
            $user = User::where('status', User::STATUS_NORMAL)->first();
            if (!$user) {
                dd('无可登录用户');
            }
            $userId = $user->id;
        }
        $this->json('GET', 'app/user/home', ['token' => $userId])->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 用户详情
     */
    public function testUserInfoShow()
    {
        $this->get('app/user-info/show?token=' . $this->getToken())->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 用户创建
     */
    public function testUserInfoCreate($userId = 0)
    {
        $params = [
            'address' => 'KHARANGAJHAR KRISHNA MANDIR,PO-TELCo WORKS PS-TELCO',
            'profession' => 'Biztalk',
            'income' => '₹15,000-₹25,000',
            'workplace_pincode' => '400607',
            'pincode' => '831004',
            'city' => 'South Andaman',
            'state' => 'ANDAMAN & NICOBAR ISLANDS',
            'company' => 'global Creditech Pte ltd ',
            'residence_type' => 'RENTED',
            'working_since' => '11/06/2005',
            'employment_type' => 'part-time salaried',
        ];
        $this->post('app/user-info/create-user-info?token=' . $this->getToken($userId), $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testUserBaseCreate($userId = 0)
    {
        $json = '{"address":"广东省深圳市xx街xx楼1","marital_status":"未婚","education_level":"高中","expected_amount":"1000","contacts":[{"contact_fullname":"张宝宝333","contact_telephone":"13909331234","relation":"同事"},{"contact_fullname":"李宝宝333","contact_telephone":"13242932222","relation":"朋友"}]}';
        $params = json_decode($json, true);
        $this->post('app/user-info/create?token=' . $this->getToken($userId), $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 用户创建
     */
    public function testUserWorkCreate($userId = 0)
    {
        $json = '{"address":"广东省深圳市xx街xx楼1","marital_status":"未婚","education_level":"高中","expected_amount":"1000","contacts":[{"contact_fullname":"张宝宝333","contact_telephone":"13909331234","relation":"同事"},{"contact_fullname":"李宝宝333","contact_telephone":"13242932222","relation":"朋友"}]}';
        $params = json_decode($json, true);
        $this->post('app/user-info/create?token=' . $this->getToken($userId), $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    /**
     * 判断&获取银行卡信息
     */
    public function testBankCardInfo()
    {
        $this->getToken();

        $params = [
            'token' => $this->token,
        ];
        $this->json('get', 'app/user-info/bankcard-info', $params)->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }
}
