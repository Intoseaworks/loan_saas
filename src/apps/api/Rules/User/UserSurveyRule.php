<?php

namespace Api\Rules\User;

use Common\Rule\Rule;

class UserSurveyRule extends Rule {

    const SCENARIO_CREATE = 'create';

    /**
     * @return array
     */
    public function rules() {
        return [
            self::SCENARIO_CREATE => [
                'step' => 'required',
                'option' => 'required|string',
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
        return [
        ];
    }

}
