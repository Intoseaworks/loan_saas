<?php

namespace Common\Rule;

use Illuminate\Contracts\Validation\Rule;

class JsonRule implements Rule
{
    /**
     * 判断验证规则是否通过。
     * @param string $attribute
     * @param mixed $value
     * @suppress PhanPluginAlwaysReturnMethod
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (isset($value) && is_array($value)) {
            return true;
        }
    }

    /**
     * 获取验证错误消息。
     *
     * @return string
     */
    public function message()
    {
        return ':attribute 必须是 json 格式';
    }
}