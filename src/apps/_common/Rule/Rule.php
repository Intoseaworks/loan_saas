<?php

namespace Common\Rule;

use Common\Exceptions\RuleException;
use Illuminate\Support\Facades\Validator;

/**
 * Class Rule
 * @package Common\Rule\Rule
 * @author ChangHai Zhan
 */
class Rule
{
    /**
     * @var array
     */
    public static $attributes = [];
    /**
     * @var string
     */
    public $scenario;
    /**
     * @var mixed
     */
    public $validator;

    /**
     * @param $scenario
     * @param $data
     * @return mixed
     */
    public function validate($scenario, $data)
    {
        $this->scenario = $scenario;
        self::$attributes = array_merge(self::$attributes, $this->getAttributes());
        $this->validator = Validator::make(
            $data,
            $this->getRules(),
            $this->getMessages(),
            $this->getCustomAttributes()
        );
        return !$this->validator->fails();
    }

    /**
     * @param $scenario
     * @param $data
     * @param $msg
     * @return array|null
     * @throws RuleException
     */
    public function validateE($scenario, $data = null, $msg = null)
    {
        !isset($data) && $data = request()->all();
        if (!$this->validate($scenario, $data)) {
            throw new RuleException($this, $msg);
        }
        return $data;
    }

    /**
     * @return array|mixed
     */
    public function getAttributes()
    {
        $attributes = $this->attributes();
        return $attributes[$this->scenario] ?? (count($attributes) == count($attributes, 1) ? $attributes : []);
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * @return array|mixed
     */
    public function getRules()
    {
        $rules = $this->rules();
        return $rules[$this->scenario] ?? [];
    }

    /**
     * http://www.yyuc.net/laravel/validation.html 验证规则
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return array|mixed
     */
    public function getMessages()
    {
        $messages = $this->messages();
        return $messages[$this->scenario] ?? [];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * @return array|mixed
     */
    public function getCustomAttributes()
    {
        $customAttributes = $this->customAttributes();
        return $customAttributes[$this->scenario] ?? (count($customAttributes) == count($customAttributes, 1) ? $customAttributes : []);
    }

    /**
     * @return array
     */
    public function customAttributes()
    {
        return [];
    }

    /**
     * @param null $name
     * @return array|mixed
     */
    public function getError($name = null)
    {
        $messages = $this->getErrors($name);
        return is_array($messages) ? current(current($messages)) : $messages;
    }

    /**
     * @param null $name
     * @return array
     */
    public function getErrors($name = null)
    {
        $messages = $this->validator->errors()->getMessages();
        if ($name === null) {
            return $messages;
        }
        return $messages[$name] ?? [];
    }

    /**
     * 扩展验证类
     * @param $ruleName
     * @param $callback
     *      function ($attribute, $value, $parameters, $validator) {
     *      /** @param string $msg
     * @var \Common\Validators\Validation $validator *
     *      //获取全部参数 $validator->getData();
     *
     *      // 添加自定义错误提示msg
     *      $validator->setCustomMessages(['测试文案']);
     *
     *      // return false;
     *      return true;
     *      }
     */
    public function extendImplicit($ruleName, $callback, $msg = '')
    {
        Validator::extendImplicit($ruleName, $callback, $msg);
    }
}
