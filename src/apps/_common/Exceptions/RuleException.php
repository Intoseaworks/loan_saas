<?php

namespace Common\Exceptions;

use Exception;

class RuleException extends Exception
{
    /**
     * @var \Common\Rule\Rule
     */
    public $rule;

    /**
     * The validator instance.
     * @var \Illuminate\Contracts\Validation\Validator
     */
    public $validator;

    /**
     * Create a new exception instance.
     * @param $rule \Common\Rule\Rule
     * @param $msg
     */
    public function __construct($rule, $msg = null)
    {
        $msg = t($msg ?? $rule->getError());
        parent::__construct($msg);

        $this->rule = $rule;
        $this->validator = $rule->validator;
    }

    /**
     * Get all of the validation error messages.
     * @return array
     */
    public function errors()
    {
        return $this->validator->errors()->messages();
    }
}
