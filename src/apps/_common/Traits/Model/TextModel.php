<?php

namespace Common\Traits\Model;

/**
 * 获取器
 * Trait TextModel
 * @package App\Common\Model
 * @phan-file-suppress PhanUndeclaredProperty, PhanUndeclaredMethod
 */
trait TextModel
{
    /**
     * 时间格式化
     * @param $attribute
     * @param string $default
     * @param string $format
     * @return false|string
     */
    public function getAttributeTimeText($attribute, $default = '', $format = 'Y-m-d H:i:s')
    {
        if (!$this->$attribute) {
            return $default;
        }
        if (!is_integer($this->$attribute)) {
            return $this->$attribute;
        }
        if($this->$attribute > 10000000000){
            return date($format, $this->$attribute/1000);
        }
        return date($format, $this->$attribute);
    }

    public function getAttributeDateTimeText($attribute, $default = '', $format = 'd/m/Y H:i:s')
    {
        if (!$this->$attribute) {
            return $default;
        }
        if (!$time = strtotime($this->$attribute)) {
            return $this->$attribute;
        }
        return date($format, $time);
    }

    /**
     * @param $attributes
     * @param int $default
     * @return int
     */
    public function setAttributeTimeText($attributes, $default = 0)
    {
        if (!isset($this->$attributes)) {
            return $default;
        }
        return $this->getTime();
    }

    /**
     * @param $attribute
     * @param string $default
     * @return array|mixed|string
     */
    public function setAttributeJsonText($attribute, $default = '[]')
    {
        if (!isset($this->$attribute) || !$this->$attribute) {
            return $default;
        }
        if (!is_array($this->$attribute)) {
            return $this->$attribute;
        }
        return json_encode($this->$attribute, JSON_UNESCAPED_UNICODE);
    }

    /**
     * json转数组
     * @param $attribute
     * @param array $default
     * @return array|mixed
     */
    public function getAttributeJsonText($attribute, $default = [])
    {
        if (!isset($this->$attribute) || !$this->$attribute) {
            return $default;
        }
        if (is_array($this->$attribute)) {
            return $this->$attribute;
        }
        return json_decode($this->$attribute, true);
    }

    /**
     * 获取器 获取类型转换
     * @param $attribute
     * @param $provider
     * @param null $default
     * @return null
     */
    public function getAttributeArrayText($attribute, $provider, $default = null)
    {
        if ($default === null) {
            $default = $this->$attribute;
        }
        return $provider[$this->$attribute] ?? $default;
    }

    /**
     * @param $attribute
     * @param int $default
     * @param int $scale
     * @param $rightOperand
     * @param bool $zero
     * @return float|int|string
     */
    public function getAttributeDecimalText($attribute, $default = 0, $scale = 2, $rightOperand = 100, $zero = true)
    {
        if ($this->$attribute === null) {
            return $default;
        }
        if (!is_numeric($this->$attribute)) {
            return $this->$attribute;
        }
        $text = bcdiv($this->$attribute, (string)$rightOperand, $scale);
        if (!$zero) {
            $text = explode('.', $text);
            if (isset($text[1])) {
                $text[1] = rtrim($text[1], '0');
                if ($text[1]) {
                    return $text[0] . '.' . $text[1];
                }
            }
            return $text[0];
        }
        return $text;
    }

    /**
     * 手机号隐藏中间四位
     * @param $attribute
     * @param string $default
     * @return mixed|string
     */
    public function getAttributeHiddenPhoneText($attribute, $default = '')
    {
        if ($this->$attribute == null) {
            return $default;
        }
        return substr_replace($this->$attribute, '****', 3, 4);
    }

    /**
     * @param $attribute
     * @param $rightOperand
     * @param $default
     * @return int|string
     */
    public function setAttributeDecimalText($attribute, $rightOperand = 100, $default = 0)
    {
        if ($this->$attribute === null) {
            return $default;
        }
        return bcmul($this->$attribute, $rightOperand);
    }

    /**
     * 执行text
     * @param $type
     * @param $params
     * @return mixed
     */
    public function getAttributeText($type, $params)
    {
        switch ($type) {
            case 'time':
                $fun = [$this, 'getAttributeTimeText'];
                break;
            case 'datetime':
                $fun = [$this, 'getAttributeDateTimeText'];
                break;
            case 'json':
                $fun = [$this, 'getAttributeJsonText'];
                break;
            case 'array':
                $fun = [$this, 'getAttributeArrayText'];
                break;
            default:
                $fun = $type;
                break;
        }
        return call_user_func_array($fun, $params);
    }

    /**
     * @param $attribute
     * @return mixed
     */
    public function getTextOne($attribute)
    {
        $textRules = $this->getTextRules();
        if (!isset($textRules[$attribute])) {
            return $this->$attribute;
        }
        return $this->getAttributeText(
            $textRules[$attribute]['type'],
            $textRules[$attribute]['params']
        );
    }

    /**
     * get Text
     * @param null $attributes
     * @return $this
     */
    public function getText($attributes = null)
    {
        if ($attributes === null) {
            $attributes = $this->getTextAttributes();
        } elseif (!is_array($attributes)) {
            $this->scenario = $attributes;
            $attributes = $this->getTextAttributes();
        }
        foreach ($this->getAttributes() as $attribute => $value) {
            if (!in_array($attribute, $attributes)) {
                $this->addHidden($attribute);
            }
        }
        $textRules = $this->getTextRules();
        foreach ($attributes as $attribute) {
            if (!isset($this->$attribute)) {
                continue;
            }
            if (!isset($textRules[$attribute])) {
                continue;
            }
            $attributeText = $attribute . '_' . $textRules[$attribute]['suffix'];
            $this->$attributeText = $this->getAttributeText(
                $textRules[$attribute]['type'],
                $textRules[$attribute]['params']
            );

        }
        return $this;
    }


    /**
     * text rules
     * @return array
     */
    public function textRules()
    {
        return [
        ];
    }

    /**
     * @return array
     */
    public function texts()
    {
        return [
        ];
    }

    /**
     * @return array
     */
    public function unTexts()
    {
        return [
        ];
    }

    /**
     * @return array
     */
    public function getTextAttributes()
    {
        $texts = $this->texts();
        $attributes = [];
        if (isset($texts[$this->scenario])) {
            $attributes = $texts[$this->scenario];
        } elseif ($texts && count($texts) == count($texts, 1)) {
            $attributes = $texts;
        }
        if (!$texts) {
            $attributes = array_keys($this->getAttributes());
        }
        if ($attributes) {
            $unTexts = $this->unTexts();
            $unAttributes = [];
            if (isset($unTexts[$this->scenario])) {
                $unAttributes = $unTexts[$this->scenario];
            } elseif ($unTexts && count($unTexts) == count($unTexts, 1)) {
                $unAttributes = $unTexts;
            }
            if ($unAttributes) {
                $attributes = array_flip($attributes);
                foreach ($unAttributes as $attribute) {
                    if (in_array($attribute, $attributes)) {
                        unset($attributes[$attribute]);
                    }
                }
                $attributes = array_flip($attributes);
            }
        }
        return $attributes;
    }

    /**
     * @param $type
     * @param $params
     * @param string $suffix
     * @return array
     */
    public function setTextRule($type, $params, $suffix = 'text')
    {
        return [
            'type' => $type,
            'params' => $params,
            'suffix' => $suffix,
        ];
    }

    /**
     * @return array
     */
    public function getTextRules()
    {
        $textRuleAttributes = [];
        $textRules = $this->textRules();
        foreach ($textRules as $type => $attributes) {
            foreach ($attributes as $attribute => $config) {
                if (is_int($attribute)) {
                    $textRuleAttributes[$config] = $this->setTextRule($type, ['attribute' => $config]);
                } elseif (is_array($config)) {
                    if ($type == 'array' && !isset($config['provider'])) {
                        $configCache = $config;
                        $config = [];
                        $config['attribute'] = $attribute;
                        $config['provider'] = $configCache;
                    } else {
                        $config['attribute'] = $attribute;
                    }
                    if (isset($config['suffix'])) {
                        $suffix = $config['suffix'];
                        unset($config['suffix']);
                        $textRuleAttributes[$attribute] = $this->setTextRule(
                            $type,
                            $config,
                            $suffix
                        );
                    } else {
                        $textRuleAttributes[$attribute] = $this->setTextRule(
                            $type,
                            $config
                        );
                    }
                } elseif (is_object($config)) {
                    $textRuleAttributes[$attribute] = $this->setTextRule(
                        $config,
                        ['model' => $this, 'attribute' => $attribute]
                    );
                } else {
                    $textRuleAttributes[$attribute] = $this->setTextRule(
                        $type,
                        ['attribute' => $attribute, 'default' => $config]
                    );
                }
            }
        }
        return $textRuleAttributes;
    }
}
