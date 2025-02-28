<?php

namespace Risk\Common\Validators;

trait SendDataValidation
{
    /**
     * 判断二维数组下某个字段的值是否有包含指定值
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     */
    public function validateDyadicArrayFieldValueContain($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'dyadic_array_field_value_contain');

        if (!$this->validateArray($attribute, $value)) {
            return false;
        }

        $field = array_shift($parameters);
        $values = array_pluck($value, $field);

        return empty(array_diff($parameters, $values));
    }
}
