<?php

namespace Api\Rules\User;

use Api\Models\User\UserInfo;
use Auth;
use Common\Models\Common\Pincode;
use Common\Rule\JsonRule;
use Common\Rule\Rule;
use Common\Utils\CityHelper;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\ValidatorHelper;
use Common\Validators\Validation;
use Illuminate\Http\Request;
use Validator;

class UserInfoRule extends Rule
{

    /**
     * 验证场景 create
     */
    const SCENARIO_CREATE = 'create';
    const SCENARIO_WORK_EAMIL_VALID = 'work_eamil_valid';
    const SCENARIO_CREATE_USER_WORK = 'create_user_work';
    const SCENARIO_CREATE_USER_INFO = 'create_user_info';
    const SCENARIO_CREATE_BASE_USER_INFO = 'create_base_user_info';
    const SCENARIO_CREATE_USER_CONTACT = 'create_user_contact';
    const SCENARIO_CREATE_USER_INVITE_FRIEND = 'create_user_invite_friend';
    const SCENARIO_CREATE_USER_INTENTION = 'create_user_intention';

    /**
     * @return array
     */
    public function rules()
    {
        $this->extend();

        /** 根据app类型区分验证 */
        $appType = app(Request::class)->header('app-type');
        /** 新app将住址相关从扩展信息迁到基本信息 */
        $addressRule = [
            'pincode' => 'required|validate_workplace_pincode',
            'address' => 'required',
            'residence_type' => 'required|in:' . implode(',', UserInfo::RESIDENCE_TYPE),
        ];

        return [
            self::SCENARIO_CREATE => array_merge([
                'position' => [
                    'required_without:address',
                    new JsonRule,
                ],
                'marital_status' => 'required|string',
                'education_level' => 'required|string',
                'expected_amount' => 'required|string',
                /** 由于1.0.5未强更不能必填 */
                'email' => 'email',
                'contacts' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        foreach ($value as $key => $contact) {
                            if (!Validation::validateMobile('', array_get($contact, 'contact_telephone'))) {
                                return $fail('手机号码格式不正确');
                            }
                        }
                    },
                    function ($attribute, $value, $fail) {
                        $contactArr = array_column($value, 'contact_telephone');
                        if (count($contactArr) !== count(array_unique($contactArr))) {
                            return $fail('紧急联系人不能相同');
                        }
                    },
                    function ($attribute, $value, $fail) {
                        //@phan-suppress-next-line PhanUndeclaredProperty
                        if (in_array(\Auth::user()->telephone, array_column($value, 'contact_telephone'))) {
                            return $fail('紧急联系人不能为本人');
                        }
                    },
                    function ($attribute, $value, $fail) {
                        foreach ($value as $key => $contact) {
                            $tel = array_get($contact, 'contact_telephone');
                            $name = array_get($contact, 'contact_fullname');
                            $relation = array_get($contact, 'relation');
                            if (!ArrayHelper::allValuesNotEmpty(compact('tel', 'name', 'relation'))) {
                                return $fail('紧急联系人未完善');
                            }
                        }
                    },
                ],
            ], $appType ? $addressRule : []),
            //cashnow
            self::SCENARIO_WORK_EAMIL_VALID => [
                'captcha' => 'required',
                'email' => 'required|email',
            ],
            self::SCENARIO_CREATE_USER_WORK => [
                'workplace_pincode' => 'required|validate_workplace_pincode',
                'work_address1' => 'required',
                'work_address2' => 'required',
                'salary' => 'required',
                'work_start_date' => 'required|validate_work_start_date',
                'work_experience_years' => 'required',
                'work_experience_months' => 'required',
                'work_email' => 'required',
                'work_phone' => 'required|validate_work_phone',
                'employment_type' => 'required',
            ],

            self::SCENARIO_CREATE_USER_INFO => array_merge([
                'profession' => 'required',
                'employment_type' => 'required',
                'working_since' => 'required|validate_work_start_date',
                'pincode' => 'required|validate_workplace_pincode',
                'workplace_pincode' => 'required|validate_workplace_pincode',
                'income' => 'required',
                //'city' => 'required|validate_state_city',
                //'province' => 'required|validate_state_city',
                'loan_reason' => 'string'
            ], $appType ? [] : $addressRule),
            self::SCENARIO_CREATE_BASE_USER_INFO => array_merge([
                'fullname' => 'required|validate_fullname',
                'marital_status' => 'required|in:' . implode(',', UserInfo::MARITAL_STATUS_TEXT),
                /** 生日性别改由卡片获取 */
                //'birthday' => 'validate_birthday',
                //'gender' => 'in:' . implode(',', UserInfo::GENDER_TYPE),
                'education_level' => 'required',
                'language' => 'required',
                'religion' => 'required',
                /** 由于1.0.5未强更不能必填 */
                'email' => 'email',
            ], $appType ? $addressRule : [
                'birthday' => 'validate_birthday',
                'gender' => 'required|in:' . implode(',', UserInfo::GENDER_TYPE),
            ]),
            self::SCENARIO_CREATE_USER_CONTACT => [
                'contactTelephone1' => 'required|mobile',
                'contactFullname1' => 'required|string',
                'relation1' => 'required|string',
                'contactTelephone2' => 'required|mobile|different:contactTelephone1',
                'contactFullname2' => 'required|string',
                'relation2' => 'required|string',
            ],
            self::SCENARIO_CREATE_USER_INVITE_FRIEND => [
                'gcash' => 'required',
                'contactTelephone1' => 'required|mobile',
                'contactTelephone2' => 'required|mobile|different:contactTelephone1',
                'contactTelephone3' => 'required|mobile|different:contactTelephone2',
            ],
            self::SCENARIO_CREATE_USER_INTENTION => [
                'principal' => 'required',
                'loan_days' => 'required',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_WORK_EAMIL_VALID => [
                'email.email' => t('邮箱不正确'),
                'captcha.required' => t('请输入验证码'),
            ],
            self::SCENARIO_CREATE_USER_WORK => [
                'work_phone.validate_work_phone' => t('工作电话格式不正确', 'rule'),
            ],
            self::SCENARIO_CREATE_USER_CONTACT => [
                'contactTelephone1.mobile' => t('手机号不正确', 'auth'),
                'contactTelephone2.mobile' => t('手机号不正确', 'auth'),
                'contactTelephone2.different' => t("Couldn't choose the same Phone No.", 'auth'),
            ],
            self::SCENARIO_CREATE_USER_INVITE_FRIEND => [
                'contactTelephone1.mobile' => t('手机号不正确', 'auth'),
                'contactTelephone2.mobile' => t('手机号不正确', 'auth'),
                'contactTelephone3.mobile' => t('手机号不正确', 'auth'),
                'contactTelephone2.different' => t("Couldn't choose the same Phone No.", 'auth'),
                'contactTelephone3.different' => t("Couldn't choose the same Phone No.", 'auth'),
            ],
        ];
    }

    public function attributes()
    {
        return [];
    }

    protected function extend()
    {
        Validator::extendImplicit('validate_work_phone', function ($attribute, $value, $parameters, Validation $validator) {
            if (!ValidatorHelper::mobile($value) && !ValidatorHelper::telephone($value)) {
                return false;
            }
            return true;
        });

        Validator::extendImplicit('validate_work_start_date', function ($attribute, $value, $parameters, Validation $validator) {
            if (!DateHelper::checkDateIsValid($value, ['d/m/Y'])) {
                $validator->setCustomMessages(['validate_work_start_date' => t('非法日期')]);
                return false;
            }

            $timestamp = strtotime(str_replace('/', '-', $value));
            if ($timestamp > time()) {
                $validator->setCustomMessages(['validate_work_start_date' => t('日期不能大于当前')]);
                return false;
            }

            $userInfo = UserInfo::whereUserId(Auth::id())->first();
            $birthdayTimestamp = strtotime(str_replace('/', '-', $userInfo->birthday));
            if ($userInfo && ($timestamp < $birthdayTimestamp)) {
                $validator->setCustomMessages(['validate_work_start_date' => t('日期不能小于出生日期')]);
                return false;
            }

            return true;
        });

        Validator::extendImplicit('validate_state_city', function ($attribute, $value, $parameters, Validation $validator) {
            $data = $validator->getData();
            if (!CityHelper::helper()->checkStateCity($data['province'], $data['city'])) {
                $validator->setCustomMessages(['validate_state_city' => t('州与城市不对应')]);
                return false;
            }
            return true;
        });

        Validator::extendImplicit('validate_workplace_pincode', function ($attribute, $value, $parameters, Validation $validator) {
            if (strlen($value) != 6 || !is_numeric($value)) {
                $validator->setCustomMessages(['validate_workplace_pincode' => $attribute . ' must be 6 bits numbers.']);
                return false;
            }
            if (!ValidatorHelper::ValidPincodeValid($value)) {
                $validator->setCustomMessages(['validate_workplace_pincode' => $attribute . ' can not start with "0" "9"']);
                return false;
            }
            if (!Pincode::model()->checkPincode($value)) {
                $validator->setCustomMessages(['validate_workplace_pincode' => "Invalid {$attribute}, please enter again"]);
                return false;
            };
            return true;
        });

        Validator::extendImplicit('validate_birthday', function ($attribute, $value, $parameters, Validation $validator) {
            if (!DateHelper::checkDateIsValid($value)) {
                $validator->setCustomMessages(['validate_birthday' => t('非法日期')]);
                return false;
            }

            $timestamp = strtotime(str_replace('/', '-', $value));
            if ($timestamp > time()) {
                $validator->setCustomMessages(['validate_birthday' => 'Cannot select future date']);
                return false;
            }

            return true;
        });

        Validator::extendImplicit('validate_fullname', function ($attribute, $value, $parameters, Validation $validator) {
            if (!ValidatorHelper::india($value)) {
                $validator->setCustomMessages(['validate_fullname' => t('该姓名含非法字符，请重新上传', 'auth')]);
            }
            return true;
        });
    }
}
