<?php

namespace Risk\Admin\Rules\SystemApprove;

use Common\Rule\Rule;
use Common\Validators\Validation;
use Illuminate\Support\Facades\Validator;
use Risk\Common\Helper\CityHelper;
use Risk\Common\Models\Business\User\UserInfo;
use Risk\Common\Models\Business\User\UserWork;
use Risk\Common\Models\SystemApprove\SystemApproveRule;

class SystemApproveConfigRule extends Rule
{
    // 机审规则设置
    const SCENARIO_SYSTEM_APPROVE_SAVE = 'systemApproveSave';

    /**
     * @return array
     */
    public function rules()
    {
        Validator::extendImplicit('validate_system_rule', function ($attribute, $value, $parameters, Validation $validator) {
            $data = $validator->getData();
            $userTypeKey = array_shift($parameters);
            $userType = array_get($data, $userTypeKey);
            $ruleKey = array_shift($parameters);
            $rule = array_get($data, $ruleKey);

            // 判断用户类型对应rule是否能进行设置
            if (!in_array($rule, SystemApproveRule::getAllRuleByQuality($userType))) {
                $validator->setCustomMessages(['用户类型对应验证规则不存在']);
                return false;
            }

            // 判断字段值是否合法
            $ruleValidate = self::getValidateRule($rule, $attribute);

            if ($ruleValidate === false) {
                $validator->setCustomMessages(['rule 验证规则不存在']);
                return false;
            }

            ["rules" => $rules, "messages" => $messages] = $ruleValidate;

            $ruleValidator = Validator::make($data, $rules, $messages);

            if ($ruleValidator->fails()) {
                $validator->setCustomMessages(current($ruleValidator->errors()->getMessages()));
                return false;
            }

            return true;
        });

        return [
            self::SCENARIO_SYSTEM_APPROVE_SAVE => [
                'user_type' => 'required|in:' . implode(',', array_keys(SystemApproveRule::USER_QUALITY)),
                'rule' => 'required',
                'value' => 'validate_system_rule:user_type,rule',
                'status' => 'in:' . implode(',', [SystemApproveRule::STATUS_NORMAL, SystemApproveRule::STATUS_CLOSE]),
            ],
            'old' => [
                'new_user_age' => 'required|array|size:2',
                'new_user_age.*' => 'required|integer|between:18,60',
                'new_user_age.1' => 'gt:new_user_age.0',
                'new_rejected_days' => 'required|integer|gt:0',
                'new_ass_rejected_days' => 'required|integer|gt:0',
                'new_user_ass_account' => 'required|integer|gt:0',
                'new_user_sms_cnt' => 'required|integer|gt:0',
                'new_user_none_h5_contacts_cnt' => 'required|integer|gt:0',
                'old_user_age' => 'required|array',
                'old_user_age.0' => 'required|integer|min:18',
                'old_user_age.1' => 'required|integer|max:60|gt:old_user_age.0',
                'old_user_max_overdue_days' => 'required|integer|gt:0',
                'old_rejected_days' => 'required|integer|gt:0',
                'old_ass_rejected_days' => 'required|integer|gt:0',
                'old_user_ass_account' => 'required|integer|gt:0',
            ],
        ];
    }

    public static function getValidateRule($rule, $attribute)
    {
        $ruleValidate = array_get(self::getValidate(), $rule);

        if (!isset($rule) || !isset($ruleValidate)) {
            return false;
        }

        $vRules = array_get($ruleValidate, 'valid', []);
        $vMessages = array_get($ruleValidate, 'message', []);

        $vRules = self::buildValidateRule($vRules, $attribute, $attribute);

        $arrayMessages = [];
        foreach ($vMessages as $key => $value) {
            $arrayMessages[$attribute . '.' . $key] = $value;
        }

        return [
            'rules' => $vRules,
            'messages' => $arrayMessages,
        ];
    }

    /**
     * 表单验证规则
     * 数组字段 integer|numeric 必须同时加
     * ' 999999 '在integer是可以通过的
     * @return array
     */
    protected static function getValidate()
    {
        return [
            /** 申请规则 */
            SystemApproveRule::RULE_APPLY_AGE_UNQUALIFIED => [
                'valid' => [
                    'array|size:2',
                    [
                        'required|integer|numeric|between:18,99',
                        'required|integer|numeric|between:18,99|gte:{attribute}.0'
                    ],
                ],
                'message' => [
                    'size' => '年龄不能为空',
                    '*.integer' => '年龄填写不正确',
                    '*.numeric' => '年龄填写不正确(不能含有空格)',
                    '*.required' => '年龄不能为空',
                    '*.between' => '年龄必须在 [18,99] 之间',
                    '1.gte' => '第二个输入框必须大于等于第一个',
                ],
            ],
            SystemApproveRule::RULE_APPLY_HAS_REJECTED_ORDERS => [
                'valid' => 'required|integer|numeric|between:0,9999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段类型必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段值必须在 [0,9999] 之间',
                ],
            ],
            SystemApproveRule::RULE_APPLY_ON_BLACK_LIST => [],
            SystemApproveRule::RULE_APPLY_EDUCATION_UNQUALIFIED => [
                'valid' => [
                    'array',
                    '*' => 'string|in:' . implode(',', UserInfo::EDUCATIONAL_TYPE),
                ],
                'message' => [
                    '*.in' => '学历值不正确',
                ],
            ],
            SystemApproveRule::RULE_APPLY_CAREER_TYPE_UNQUALIFIED => [
                'valid' => [
                    'array',
                    '*' => 'string|in:' . implode(',', UserWork::EMPLOYMENT_TYPE),
                ],
                'message' => [
                    '*.in' => '职业类型不正确',
                ],
            ],
            SystemApproveRule::RULE_APPLY_FIRST_LOAN_INFORMATION_FILLING_TIME => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '时间类型必须为整数',
                    'numeric' => '时间填写不正确(不能含有空格)',
                    'between' => '时间范围必须在 [1,999] 之间',
                ],
            ],
            SystemApproveRule::RULE_APPLY_CREATE_TIME => [
                'valid' => [
                    'required|array|size:2',
                    [
                        [
                            'array|size:2',
                            [
                                'required|date_format:H:i',
                                'required|date_format:H:i|after:{attribute}.0.0'
                            ],
                        ],
                        'required|integer|numeric|between:1,999',
                    ],
                ],
                'message' => [
                    'required' => '字段值不能为空',
                    'size' => '数组大小必须为2',

                    '0.size' => '时间段数组大小必须为2',
                    '0.*.required' => '时间范围不能为空',
                    '0.*.date_format' => '时间格式不正确 [H:i]',
                    '0.1.after' => '第二个时间必须大于第一个时间',

                    '1.required' => '字段值不能为空',
                    '1.integer' => '字段值必须为整数',
                    '1.numeric' => '字段填写不正确(不能含有空格)',
                    '1.between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_APPLY_ADDRESS_UNQUALIFIED => [
                'valid' => [
                    'array',
                    '*' => 'string|in:' . implode(',', CityHelper::helper()->getStateList()->toArray()),
                ],
                'message' => [
                    '*.in' => '地址选择不正确',
                ],
            ],
            SystemApproveRule::RULE_APPLY_START_VERIFY_TIME => [
                'valid' => [
                    'array|size:2',
                    [
                        'required|date_format:H:i',
                        'required|date_format:H:i|after:{attribute}.0'
                    ],
                ],
                'message' => [
                    '*.required' => '时间范围不能为空',
                    '*.date_format' => '时间格式不正确 [H:i]',
                    '1.after' => '第二个时间必须大于第一个时间',
                ],
            ],
            SystemApproveRule::RULE_APPLY_IP_NOT_IN_INDIA => [],
            SystemApproveRule::RULE_APPLY_GPS_NOT_IN_INDIA => [],
            SystemApproveRule::RULE_APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE => [
                'valid' => [
                    'array|size:4',
                    [
                        [
                            'array',
                            '*' => 'string',
                        ],
                        [
                            'array',
                            '*' => 'string|in:' . implode(',', UserInfo::MARITAL_STATUS_TEXT),
                        ],
                        [
                            'array',
                            '*' => 'string|in:' . implode(',', UserInfo::EDUCATIONAL_TYPE),
                        ],
                        [
                            'array',
                            '*' => 'string|in:' . implode(',', UserInfo::GENDER_TYPE),
                        ],
                    ]
                ],
                'message' => [
                    'required' => '字段值不能为空',
                    'size' => '数组大小必须为2',
                ],
            ],
            /** 设备验证 */
            SystemApproveRule::RULE_DEVICE_SAME_APPLY_COUNT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_DEVICE_SAME_TIME_RANGE_APPLY_COUNT => [
                'valid' => [
                    'array|size:2',
                    '*' => 'required|integer|numeric|between:1,999',
                ],
                'message' => [
                    'size' => '数组大小必须为2',
                    '*.integer' => '字段值必须为整数',
                    '*.numeric' => '字段填写不正确(不能含有空格)',
                    '*.required' => '字段值不能为空',
                    '*.between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_DEVICE_SAME_IN_APPROVE_COUNT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_DEVICE_SAME_OVERDUE_COUNT => [
                'valid' => [
                    'array|size:2',
                    '*' => 'required|integer|numeric|between:1,999',
                ],
                'message' => [
                    'size' => '数组大小必须为2',
                    '*.integer' => '字段值必须为整数',
                    '*.numeric' => '字段填写不正确(不能含有空格)',
                    '*.required' => '字段值不能为空',
                    '*.between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_DEVICE_SAME_MATCHING_IDENTITY => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_DEVICE_SAME_MATCHING_TELEPHONE => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_DEVICE_TYPE => [],
            SystemApproveRule::RULE_DEVICE_PHONE_MEMORY => [
                'valid' => 'required|integer|numeric',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                ],
            ],
            SystemApproveRule::RULE_DEVICE_UNIQUE_NUMBER_CHANGE => [],
            /** 身份验证 */
            SystemApproveRule::RULE_AUTH_SAME_BANK_CARD_MATCHING_IDENTITY => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_AUTH_SAME_BANK_CARD_MATCHING_TELEPHONE => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_AUTH_SAME_BANK_CARD_MATCHING_MERCHANT_PAID_ORDER => [
                'valid' => [
                    'array|size:2',
                    '*' => 'required|integer|numeric|between:1,999',
                ],
                'message' => [
                    'size' => '数组大小必须为2',
                    '*.integer' => '字段值必须为整数',
                    '*.numeric' => '字段填写不正确(不能含有空格)',
                    '*.required' => '字段值不能为空',
                    '*.between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_AUTH_SAME_IDENTITY_MATCHING_TELEPHONE => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_AUTH_SAME_IDENTITY_MATCHING_DEVICE => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER => [],
            SystemApproveRule::RULE_AUTH_FACE_MATCH => [
                'valid' => 'required|integer|numeric|between:0,100',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [0,100]',
                ],
            ],
            SystemApproveRule::RULE_AUTH_OCR_NAME_COMPARISON => [],
            /** 通讯录验证 */
            SystemApproveRule::RULE_CONTACTS_IN_APPROVE_COUNT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_CONTACTS_IN_OVERDUE_COUNT => [
                'valid' => [
                    'array|size:2',
                    '*' => 'required|integer|numeric|between:1,999',
                ],
                'message' => [
                    'size' => '数组大小必须为2',
                    '*.integer' => '字段值必须为整数',
                    '*.numeric' => '字段填写不正确(不能含有空格)',
                    '*.required' => '字段值不能为空',
                    '*.between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_CONTACTS_COUNT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            /** 借款行为 */
            SystemApproveRule::RULE_BEHAVIOR_REJECT_COUNT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_BEHAVIOR_OVERDUE_COUNT => [
                'valid' => [
                    'array|size:2',
                    '*' => 'required|integer|numeric|between:1,999',
                ],
                'message' => [
                    'size' => '数组大小必须为2',
                    '*.integer' => '字段值必须为整数',
                    '*.numeric' => '字段填写不正确(不能含有空格)',
                    '*.required' => '字段值不能为空',
                    '*.between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_BEHAVIOR_MAX_OVERDUE_DAYS => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_BEHAVIOR_LATELY_OVERDUE_DAYS => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_BEHAVIOR_LATELY_COLLECTION_TELEPHONE_COUNT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            /** 其他 */
            SystemApproveRule::RULE_OTHER_HAS_LOAN_APP_COUNT => [
                'valid' => 'required|integer|numeric|between:1,999',
                'message' => [
                    'required' => '字段值不能为空',
                    'integer' => '字段值必须为整数',
                    'numeric' => '字段填写不正确(不能含有空格)',
                    'between' => '字段范围必须在 [1,999]',
                ],
            ],
            SystemApproveRule::RULE_OTHER_RECENT_INSTALL_LOAN_APP_COUNT => [
                'valid' => [
                    'array|size:2',
                    '*' => 'required|integer|numeric|between:1,999',
                ],
                'message' => [
                    'size' => '数组大小必须为2',
                    '*.integer' => '字段值必须为整数',
                    '*.numeric' => '字段填写不正确(不能含有空格)',
                    '*.required' => '字段值不能为空',
                    '*.between' => '字段范围必须在 [1,999]',
                ],
            ]
        ];
    }

    protected static function buildValidateRule($vRule, $prefix, $attribute)
    {
        if (is_array($vRule)) {
            $vRules = [];

            $allMatching = array_get($vRule, '*');
            if ($allMatching) {
                $vRules[$prefix . '.*'] = str_replace('{attribute}', $attribute, $allMatching);
                array_forget($vRule, '*');
            }

            $rule = reset($vRule);
            $child = end($vRule);

            if ($rule === false) {
                return [];
            }
            if (!is_string($rule)) {
                throw new \Exception('rule 表单验证规则定义有误');
            }

            $vRules[$prefix] = str_replace('{attribute}', $attribute, $rule);

            if (is_array($child)) {
                foreach ($child as $key => $item) {
                    $vRules += self::buildValidateRule($item, $prefix . '.' . $key, $attribute);
                }
            }
        } else {
            $vRules = [$prefix => str_replace('{attribute}', $attribute, $vRule)];
        }

        return $vRules;
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            'old' => [
                'new_user_age.1.gt' => ':attribute 必须大于起始值。',
                'new_user_ass_account.gt' => '关联账户超过阈值 必须大于0。',
                'new_user_open_time.gt' => '在网时长小于阈值 必须大于0。',
                'new_user_android_sms_cnt.gt' => '安卓用户短信条数小于阈值 必须大于0。',
                'new_user_none_h5_contacts_cnt.gt' => '非H5客户端通讯录联系人数量少于阈值 必须大于0。',
                'new_user_top_call_time_sum.gt' => '通话时间top少于阈值 必须大于0。',
                'old_user_age.1.gt' => '年龄要求范围 结束值必须大于起始值。',
                'old_user_ass_account.gt' => '关联账户超过阈值 必须大于0。',
                'old_user_max_overdue_days.gt' => '历史最大逾期天数≥阈值 必须大于0。',
                'old_user_total_overdue_days.gt' => '历史累计逾期天数 必须大于0。',
            ],
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
