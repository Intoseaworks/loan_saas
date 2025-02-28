<?php

namespace Api\Rules\Captcha;

use Common\Rule\Rule;

class FxRule extends Rule
{

    const SCENARIO_ACCEPT_USER = 'accept_user';
    const SCENARIO_ACCEPT_ORDER = 'accept_order';
    const SCENARIO_PUSH_USER = 'push_user';
    const SCENARIO_PUSH_DETAIL = 'push_detail';
    const SCENARIO_PUSH_CONTACT = 'push_contact';
    const SCENARIO_PUSH_OVERDUE = 'push_overdue';
    const SCENARIO_CREATE_REPAYMENT = 'create_repayment';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_ACCEPT_USER => [
                'phoneMd5' => 'required',
                'panMd5' => 'required',
                'aadNo' => 'required',
                'creditLimit' => 'required',
            ],
            self::SCENARIO_ACCEPT_ORDER => [
                'orderID' => 'required',
                'panMd5' => 'required',
                'aadNo' => 'required',
                'bankNo' => 'required',
                'ifsc' => 'required',
                'phone' => 'required',
                'email' => 'required',
                'name' => 'required',
                'disbursalAmount' => 'required',
                'creditLimitMax' => 'required',
                'creditLimitMin' => 'required'
            ],
            self::SCENARIO_PUSH_USER => [
                'orderID' => 'required',
                'panMd5' => 'required',
                'userInfo.phone' => 'required',
                'userInfo.panCode' => 'required',
                'userInfo.userName' => 'required',
                'userInfo.birthday' => 'required',
                'userInfo.gender' => 'required',
                'userInfo.marital' => 'required',
                'userInfo.email' => 'required',
                'userInfo.companyName' => 'required',
                'userInfo.industry' => 'required',
                'userInfo.education' => 'required',
                'userInfo.monthlySalary' => 'required',
                'userInfo.aadhaarPinCode' => 'required',
                'userInfo.aadhaarAddress1' => 'required',
                'userInfo.aadhaarAddress2' => 'required',
                'userInfo.aadhaarDetailAddress' => 'required',
                'userInfo.residentialPinCode' => 'required',
                'userInfo.residentialAddress1' => 'required',
                'userInfo.residentialAddress2' => 'required',
                'userInfo.residentialDetailAddress' => 'required',
            ],
            self::SCENARIO_PUSH_DETAIL => [
                'orderID' => 'required',
                'panMd5' => 'required',
                'orderInfo.applyApp' => 'required',
                'orderInfo.disbursalAmount' => 'required',
                'orderInfo.repaymentAmount' => 'required',
                'orderInfo.interestAmount' => 'required',
                'orderInfo.costFee' => 'required',
                'orderInfo.overdueFee' => 'required',
                'orderInfo.costRate' => 'required',
                'orderInfo.interestRate' => 'required',
                'orderInfo.overdueRate' => 'required',
                'orderInfo.loanTerm' => 'required',
                'orderInfo.loanTime' => 'required',
                'orderInfo.dueTime' => 'required',
            ],
            self::SCENARIO_PUSH_CONTACT => [
                'orderID' => 'required',
                'panMd5' => 'required',
                'userContact.relative' => 'required',
                'userContact.name' => 'required',
                'userContact.phone' => 'required',
                'userContact.otherRelative' => 'required',
                'userContact.otherName' => 'required',
                'userContact.otherPhone' => 'required',
                'mobileBook' => 'required',
            ],
            self::SCENARIO_PUSH_OVERDUE => [
                'orderID' => 'required',
                'panMd5' => 'required',
                'overdueDay' => 'required',
                'overdueFee' => 'required'
            ],
            self::SCENARIO_CREATE_REPAYMENT => [
                'orderID' => 'required',
                'panMd5' => 'required',
                'amount' => 'required',
            ]
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [];
    }
}
