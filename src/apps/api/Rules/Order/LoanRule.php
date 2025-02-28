<?php

namespace Api\Rules\Order;

use Common\Rule\Rule;
use Illuminate\Http\Request;

class LoanRule extends Rule {

    /**
     * 验证场景 create
     */
    const RELOAN = 'reloan'; //复贷

    /**
     * @return array
     */
    public function rules() {
        $this->extend();
        $telephone = app(Request::class)->get('telephone');
        return [
            self::RELOAN => [
                'bank_account_type' => 'required|integer',
                'no' => 'required|numeric',
                'bank_name' => 'required',
                'contactTelephone' => 'required|mobile',
                'contactFullname' => 'required|string',
                'relation' => 'required|string',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages() {
        return [
        ];
    }

    public function attributes() {
        return [];
    }

    protected function extend() {
        
    }

}
