<?php


use Common\Rule\Rule;

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | such as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted' => ':attribute 必须接受。',
    'active_url' => ':attribute 不是一个有效的网址。',
    'after' => ':attribute 必须是一个在 :date 之后的日期。',
    'alpha' => ':attribute 只能由字母组成。',
    'alpha_dash' => ':attribute 只能由字母、数字和斜杠组成。',
    'alpha_num' => ':attribute 只能由字母和数字组成。',
    'array' => ':attribute 必须是一个数组。',
    'before' => ':attribute 必须是一个在 :date 之前的日期。',
    'between' => [
        'numeric' => ':attribute 必须介于 :min - :max 之间。',
        'file' => ':attribute 必须介于 :min - :max kb 之间。',
        'string' => ':attribute 必须介于 :min - :max 个字符之间。',
        'array' => ':attribute 必须只有 :min - :max 个单元。',
    ],
    'boolean' => ':attribute 必须为布尔值。',
    'confirmed' => ':attribute 两次输入不一致。',
    'date' => ':attribute 不是一个有效的日期。',
    'date_format' => ':attribute 的格式必须为 :format。',
    'different' => ':attribute 和 :other 必须不同。',
    'digits' => ':attribute 必须是 :digits 位的数字。',
    'digits_between' => ':attribute 必须是介于 :min 和 :max 位的数字。',
    'distinct' => ':attribute 已經存在。',
    'email' => ':attribute 不是一个合法的邮箱。',
    'exists' => ':attribute 不存在。',
    'filled' => ':attribute 不能为空。',
    'image' => ':attribute 必须是图片。',
    'in' => '已选的属性 :attribute 非法。',
    'in_array' => ':attribute 没有在 :other 中。',
    'integer' => ':attribute 必须是整数。',
    'ip' => ':attribute 必须是有效的 IP 地址。',
    'json' => ':attribute 必须是正确的 JSON 格式。',
    'max' => [
        'numeric' => ':attribute 不能大于 :max。',
        'file' => ':attribute 不能大于 :max kb。',
        'string' => ':attribute 不能大于 :max 个字符。',
        'array' => ':attribute 最多只有 :max 个单元。',
    ],
    'mimes' => ':attribute 必须是一个 :values 类型的文件。',
    'min' => [
        'numeric' => ':attribute 必须大于等于 :min。',
        'file' => ':attribute 大小不能小于 :min kb。',
        'string' => ':attribute 至少为 :min 个字符。',
        'array' => ':attribute 至少有 :min 个单元。',
    ],
    'not_in' => '已选的属性 :attribute 非法。',
    'numeric' => ':attribute 必须是一个数字。',
    'present' => ':attribute 必须存在。',
    'regex' => ':attribute 格式不正确。',
    'required' => ':attribute 不能为空。',
    'required_if' => '当 :other 为 :value 时 :attribute 不能为空。',
    'required_unless' => '当 :other 不为 :value 时 :attribute 不能为空。',
    'required_with' => '当 :values 存在时 :attribute 不能为空。',
    'required_with_all' => '当 :values 存在时 :attribute 不能为空。',
    'required_without' => '当 :values 不存在时 :attribute 不能为空。',
    'required_without_all' => '当 :values 都不存在时 :attribute 不能为空。',
    'same' => ':attribute 和 :other 必须相同。',
    'size' => [
        'numeric' => ':attribute 大小必须为 :size。',
        'file' => ':attribute 大小必须为 :size kb。',
        'string' => ':attribute 必须是 :size 个字符。',
        'array' => ':attribute 必须为 :size 个单元。',
    ],
    'string' => ':attribute 必须是一个字符串。',
    'timezone' => ':attribute 必须是一个合法的时区值。',
    'unique' => ':attribute 已经存在。',
    'url' => ':attribute 格式不正确。',
    //新增
    'extension' => ':attribute 文件格式不正确',
    //新增
    'demo' => ':attribute demo 配置错误',
    //手机号验证
    'mobile' => ':attribute 不是有效的',
    //贷款城市
    'has_loan_city' => ':attribute 不是有效的',
    //用户资料
    'has_upload' => '用户资料 不是有效的',
    //申请贷款
    'has_apply_info' => ':attribute 不是有效的',
    //房产评估
    'can_evaluate_apply' => ':attribute 不是有效的',
    //申请产品
    'can_product_apply' => ':attribute 不是有效的',
    //申请最大金额
    'max_apply_money' => ':attribute 不能大于最大房产评估金额',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention 'attribute.rule' to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of 'email'. This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => array_merge([
        'name' => '名称',
        'username' => '用户名',
        'email' => '邮箱',
        'first_name' => '名',
        'last_name' => '姓',
        'password' => '密码',
        'password_confirmation' => '确认密码',
        'city' => '城市',
        'country' => '国家',
        'address' => '地址',
        'phone' => '电话',
        'mobile' => '手机',
        'age' => '年轻',
        'sex' => '性别',
        'gender' => '性别',
        'day' => '天',
        'month' => '月',
        'year' => '年',
        'hour' => '时',
        'minute' => '分',
        'second' => '秒',
        'title' => '标题',
        'content' => '内容',
        'description' => '描述',
        'excerpt' => '摘要',
        'date' => '日期',
        'time' => '时间',
        'available' => '可用的',
        'size' => '大小',
    ], Rule::$attributes),

];
