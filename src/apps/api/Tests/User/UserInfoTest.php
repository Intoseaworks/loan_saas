<?php


namespace Api\Tests\User;


use Api\Services\Common\CaptchaServer;
use Api\Tests\TestBase;

class UserInfoTest extends TestBase
{
    public function testWorkEmailValid()
    {
        $catpcha = CaptchaServer::server();
        $testEmail = '123@qq.com';
        $code = 1234;
        $key = $catpcha->getCacheKey(CaptchaServer::CACHE_KEY_EMAIL, $testEmail);
        $catpcha->cacheCode(1234, 0, $key);

        $params = [
            'captcha' => $code,
            'email' => $testEmail,
        ];

        $this->seeRequest('POST', '/app/user-info/work-eamil-valid', $params);
    }

    public function testCreateUserWork()
    {
        $params = [
            'workplace_pincode' => '560037',
            'company' => '	Amazon Development Center India Pvt Ltd',
            'work_address1' => '2870, Orion Building, Bagmane Constellation Business Park, Outer Ring Road',
            'work_address2' => 'Doddanekundi Circle, Marathahalli Post, Ferns city',
            'profession' => 'Transaction Risk Investigator',
            'salary' => '₹25，000-₹35，000',
            'work_start_date' => '14/05/2019',
            'work_experience_years' => '3',
            'work_experience_months' => '6',
            'work_email' => 'rupsas@amazon.com',
            'work_phone' => '08066050000',
            'employment_type' => 'full-time salaried',
            'clientId' => 'h5',
        ];

        $this->seeRequest('POST', '/app/user-info/create-user-work', $params);
    }

    public function testCreateUserInfo()
    {
        $params = [
            'token' => 163,
            'address' => 'mmmmmmmmmm1',
            'profession' => 'Animator',
            'income' => '₹35，000 - ₹45，000',
            'workplace_pincode' => '500038',
            'pincode' => '500072',
            'city' => 'East Godavari',
            'device_uuid' => '09163afafb759583ad7aaaba772d2471',
            'access_token' => '8f38bf0224d2494abcbdf69bc33fd8ce',
            'timestamp' => 1557972660446,
            'province' => 'Andhra Pradesh',
            'company' => 'grand lane system private Ltd',
            'residence_type' => 'RENTED',
            'working_since' => '20/08/2018',
            'clientId' => 'android',
            'app_version' => '1.4.01',
            'employment_type' => 'full-time salaried',
            'chirdren_count' => 1,
            'loan_reason' => 'Bills',
        ];

        $this->seeRequest('POST', '/app/user-info/create-user-info', $params);
    }

    public function testCreateBaseUserInfo()
    {
        $params = [
            'token' => 163,
            'fullname' => 'ssssss',
            'clientId' => 'android',
            'app_version' => '1.4.01',
            'marital_status' => 'Married',
            'education_level' => 'Primary',
            'device_uuid' => '09163afafb759583ad7aaaba772d2471',
            'religion' => 'Hinduism',
            'accessToken' => 'c5e1be80cf9a4e338b0635bf3f18500d',
            'timestamp' => '1558005023515',
            'gender' => 'Male',
            'language' => 'Urdu,Malayalam',
            'birthday' => '01/01/1995',
            'chirdren_count' => 1,
            'live_length' => '>1',
        ];

        $this->seePostRequest('/app/user-info/create-base-user-info', $params);
    }

    public function testCreateUserIntention()
    {
        $params = [
            'token' => 163,
            'principal' => 3000,
            'loan_days' => 7,
        ];

        $this->seePostRequest('/app/user-info/create-user-intention', $params);
    }

    public function testGetUserInfo()
    {
        $this->seeGetRequest('/app/user-info/get-user-info');
    }

    public function testGetUserDetail()
    {
        $this->seeGetRequest('/app/user-info/get-user-detail');
    }
}
