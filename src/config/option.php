<?php

return [

    //婚姻状况
    'marital_status' => [
        'desc' => '婚姻状况',
        'value' => [
            '未婚',
            '已婚无子女',
            '已婚有子女',
            '离异',
            '丧偶',
        ]
    ],

    //期望额度
    'expected_amount' => [
        'desc' => '期望额度',
        'value' => [
            '1000-1500元',
            '1500-2000元',
            '2000-2500元',
            '2500-3000元',
            '3000元以上',
        ]
    ],

    //教育程度
    'education_level' => [
        'desc' => '教育程度',
        'value' => [
            '高中以下',
            '高中',
            '中专',
            '大专',
            '本科',
            '硕士',
            '博士',
        ]
    ],

    //直系亲属关系
    'relation_lineal' => [
        'desc' => '直系亲属关系',
        'value' => [
            '父母',
            '配偶',
            '子女',
        ]
    ],

    //常用联系人关系
    'relation_common' => [
        'desc' => '常用联系人关系',
        'value' => [
            '朋友',
            '同事',
            '亲属',
        ]
    ],

    'feedback_type' => [
        'desc' => '反馈类型',
        'value' => \Common\Utils\Data\ArrayHelper::arrToOption(\Common\Models\Feedback\Feedback::TYPE),
    ],

];
