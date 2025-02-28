<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-14
 * Time: 10:36
 */

namespace Approve\Admin\Rules;


use Admin\Models\Upload\Upload;
use Approve\Admin\Services\Approval\CallApproveService;
use Approve\Admin\Services\Approval\FirstApproveService;
use Common\Exceptions\CustomException;
use Common\Models\Approve\ApprovePool;
use Common\Models\Order\Order;
use Common\Rule\Rule;
use Common\Utils\Code\ApproveStatusCode;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\ValidatorHelper;
use Common\Validators\Validation;
use Validator;

class ApproveRule extends Rule
{
    /**
     * index
     */
    const SENARIO_INDEX = 'SENARIO_INDEX';

    /**
     * 开始审批
     */
    const SENARIO_START_WORK = 'SENARIO_START_WORK';

    /**
     * 停止审批
     */
    const SENARIO_STOP_WORK = 'SENARIO_STOP_WORK';

    /**
     * 初审详情
     */
    const SENARIO_FIRST_APPROVE_VIEW = 'SENARIO_FIRST_APPROVE_VIEW';

    /**
     * 电审详情
     */
    const SENARIO_CALL_APPROVE_VIEW = 'SENARIO_CALL_APPROVE_VIEW';

    /**
     * 电审提交
     */
    const SENARIO_CALL_SUBMIT = 'SENARIO_CALL_SUBMIT';

    /**
     * 初审提交
     */
    const SENARIO_FIRST_SUBMIT = 'SENARIO_FIRST_SUBMIT';

    /**
     * log
     */
    const SENARIO_APPROVE_LOG = 'SENARIO_APPROVE_LOG';

    /**
     * 开始审批
     */
    const SENARIO_START_CHECK = 'SENARIO_START_CHECK';

    /**
     * 地址信息
     */
    const SENARIO_ADDRESS_INFO = 'SENARIO_ADDRESS_INFO';

    /**
     * @var array
     */
    protected $userFiles = [];

    /**
     * @var null|Order
     */
    protected $order = null;

    /**
     * @throws CustomException
     */
    public function init()
    {
        if (!$this->order) {

            $orderId = request()->input('order_id');
            $this->order = Order::model()->getOne($orderId);
            if (!$this->order) {
                throw new CustomException('参数错误', ApproveStatusCode::PARAMS_VERFIY_FAILED);
            }
        }

        if (!$this->userFiles) {
            $this->userFiles = FirstApproveService::getFile($this->order->user_id, $this->order->id)['data'];
        }
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [

            static::SENARIO_INDEX => [
                'type' => 'required',
            ],

            static::SENARIO_START_WORK => [
                'type' => 'required',
            ],

            static::SENARIO_STOP_WORK => [
                'type' => 'required',
            ],

            static::SENARIO_FIRST_APPROVE_VIEW => [
                'order_id' => 'required|integer',
                'id' => 'required|integer',
            ],

            static::SENARIO_CALL_APPROVE_VIEW => [
                'order_id' => 'required|integer',
                'id' => 'required|integer',
            ],

            static::SENARIO_FIRST_SUBMIT => array_merge(
                [
                    'order_id' => 'required|integer',
                    'id' => 'required|integer',
                    'page' => 'bail|sometimes|string',
                    'suspected_fraud_other_remark' => 'bail|suspected_fraud_other_remark_extend',
                    'id_card_type' => 'required|in:' . implode(',', array_values(Upload::TYPE_BUK)),
//                    'id_card_no' => 'required|validate_card_num',
                    'id_card_no' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            $id_card_type = request()->input('id_card_type');
                            switch ($id_card_type) {
                                case 'Driver’s License':
                                    if (!ValidatorHelper::validPhDriverNum($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'SSS card':
                                    if (!ValidatorHelper::validPhSSSCard($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'PRC ID':
                                    if (!ValidatorHelper::validPhPrcID($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'UMID':
                                    if (!ValidatorHelper::validPhUMID($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'TIN':
                                    if (!ValidatorHelper::validPhTIN($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case "PASSPORT":
                                    if (!ValidatorHelper::validPhPPT($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'PAG-IBIG':
                                    if (!ValidatorHelper::validPhPAG($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'GSIS ID':
                                    if (!ValidatorHelper::validPhGSIS($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'Voter\'s ID':
                                    if (!ValidatorHelper::validPhVoter($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'PIC':
                                    if (!ValidatorHelper::validPhPIC($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'NBI':
                                    if (!ValidatorHelper::validPhNBI($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                                case 'PSN':
                                    if (!ValidatorHelper::validPhPSN($value)) {
                                        return $fail('The ID No. is incorrect');
                                    }
                                    break;
                            }
                        }
                    ],
                ],
                $this->firstSubmit()
            ),

            static::SENARIO_CALL_SUBMIT => array_merge(
                [
                    'order_id' => 'required|integer',
                    'id' => 'required|integer',
                    'confirm_month_income' => 'required',
                    'status' => 'required',
                    'emergency_contact' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            foreach ($value as $key => $contact) {
                                $tel = array_get($contact, 'contactTelephone');
                                $name = array_get($contact, 'contactFullname');
                                $relation = array_get($contact, 'relation');
                                if (!ArrayHelper::allValuesNotEmpty(compact('tel', 'name', 'relation'))) {
                                    return $fail('Emergency contact incomplete');
                                }
                            }
                        },
                        function ($attribute, $value, $fail) {
                            foreach ($value as $key => $contact) {
                                if (!Validation::validateMobile('', array_get($contact, 'contactTelephone'))) {
                                    return $fail('Incorrect Format');
                                }
                            }
                        },
                        function ($attribute, $value, $fail) {
                            $contactArr = array_column($value, 'contactTelephone');
                            if (count($contactArr) !== count(array_unique($contactArr))) {
                                return $fail('Emergency contacts cannot be the same');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            $contactArr = array_column($value, 'contactTelephone');
                            $compareArr = array_column(request()->input('emergency_contact_other'), 'contactTelephone');
                            if (count(array_intersect($contactArr, $compareArr)) > 0) {
                                return $fail('Emergency contacts already exists');
                            }
                        },
                    ],
                    'emergency_contact_other' => [
                        function ($attribute, $value, $fail) {
                            foreach ($value as $key => $contact) {
                                $tel = array_get($contact, 'contactTelephone');
                                $name = array_get($contact, 'contactFullname');
                                $relation = array_get($contact, 'relation');
                                if (!ArrayHelper::allValuesNotEmpty(compact('tel', 'name', 'relation'))) {
                                    return $fail('Other emergency contact incomplete');
                                }
                            }
                        },
                        function ($attribute, $value, $fail) {
                            foreach ($value as $key => $contact) {
                                if (!Validation::validateMobile('', array_get($contact, 'contactTelephone'))) {
                                    return $fail('Incorrect Format');
                                }
                            }
                        },
                        function ($attribute, $value, $fail) {
                            $contactArr = array_column($value, 'contactTelephone');
                            if (count($contactArr) !== count(array_unique($contactArr))) {
                                return $fail('Emergency contacts cannot be the same');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            $contactArr = array_column($value, 'contactTelephone');
                            $compareArr = array_column(request()->input('emergency_contact'), 'contactTelephone');
                            if (count(array_intersect($contactArr, $compareArr)) > 0) {
                                return $fail('Emergency contacts already exists');
                            }
                        },
                    ],
                ],
                $this->callSubmit()
            ),

            static::SENARIO_APPROVE_LOG => [
                'order_id' => 'required|integer',
                'order_type' => 'required|integer'
            ],

            static::SENARIO_START_CHECK => [
                'id' => 'required|integer',
            ],

            static::SENARIO_ADDRESS_INFO => [
                'pincode' => 'required|regex:/\d{6}/',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function attributes()
    {
        return [
            'type' => '审批类型',
            'order_id' => '订单Id',
            'order_type' => '订单类型',
        ];
    }

    /**
     * @return array|mixed
     */
    public function firstSubmit()
    {
//        Validator::extendImplicit('suspected_fraud_other_remark_extend', function ($attribute, $value, $parameters, $validator) {
//            $status = request()->input('suspected_fraud_status', []);
//            // 10是other选项,具体参考Params::firstApprove('base_detail_suspected_fraud_list')
//            if (in_array(10, $status)) {
//                return !empty($value);
//            }
//
//            return true;
//        });
        // 判断是否是初审
        $status = request()->input('base_detail_status');
        if ($this->scenario != static::SENARIO_FIRST_SUBMIT || $status != FirstApproveService::PASS) {
            return [];
        }

        $this->extend();

        return [
//            'page' => 'bail|sometimes|string',
//            'first_name' => 'bail|required',
//            'father_name' => 'bail',
//            'birthday' => 'bail|required|integer',
//            'gender' => 'bail|required|in:Male,Female,male,female',
//            'current_address' => 'bail|required|string',
//            'current_region' => 'bail|current_region_extend',
//            'current_pin_code' => 'bail|required|string',
//            'permanent_address' => 'bail|required|string',
//            'permanent_region' => 'bail|permanent_region_extend',
//            'permanent_pin_code' => 'bail|required|string',
//            'pan_card_no' => 'bail|pan_card_extend|string',
//            'passport_no' => 'bail|passport_no_extend|string',
//            'passport_status' => 'bail|passport_no_extend|integer',
//            'aadhaar_card_no' => 'bail|aadhaar_card_no_extend|string',
//            'aadhaar_card_status' => 'bail|aadhaar_card_no_extend|integer',
//            'driving_license_no' => 'bail|driving_license_no_extend|string',
//            'driving_license_status' => 'bail|driving_license_no_extend|integer',
//            'voter_id_card_no' => 'bail|voter_id_card_no_extend|string',
//            'environment_status' => 'bail|sometimes|integer',
//            'appearance_status' => 'bail|sometimes|array',
//            'base_detail_status' => 'bail|required|' . $this->submitStatus(ApprovePool::ORDER_FIRST_GROUP),
//            'approval_failed_status' => 'bail|sometimes|array',
//            'suspected_fraud_status' => 'bail|sometimes|array',
        ];

    }

    /**
     * @return array
     */
    public function callSubmit()
    {
        // 判断是否是电审
        if ($this->scenario != static::SENARIO_CALL_SUBMIT) {
            return [];
        }

        $this->init();

        $status = request()->input('status');

        $emergencyContacthandler = function ($value, $key, $validator, $attribute, $type = 'phone') use ($status) {
            // 2是不正确,可能会纠正用户紧急联系人.具体查看 Params::callApprove('normal_status_list');
            if ($value == 2) {
                if ($type == 'phone') {
                    if (($phone = request()->input($key, false)) && preg_match('/^[98]\d{9}$/', $phone['phone'] ?? '')) {
                        return true;
                    }
                }

                if ($type == 'name') {
                    //if (($name = request()->input($key, false)) && preg_match('/^(?![\. ])[a-zA-Z\. ]+(?<! )$/', $name['name'] ?? '')) {
                    return true;
                    //}
                }

                $message = '';
                if ($type == 'phone') {
                    $message = 'Emergency phone is an invalid phone number';
                }
                if ($type == 'name') {
                    $message = 'Invalid emergency contact name';
                }
                $validator->setCustomMessages([$attribute => $message]);

                return false;
            }

            return true;

        };

        Validator::extendImplicit('call_need_verify', function ($attribute, $value, $parameters, $validator) use ($status) {
            if ($status == CallApproveService::PASS) {
                return $value ? true : false;
            }

            return true;
        });

//        Validator::extendImplicit('emergency_contact1_extend', function ($attribute, $value, $parameters, $validator) use ($emergencyContacthandler) {
//            $emergencyContact1 = request()->input('emergency_contact1');
//            return $emergencyContacthandler($emergencyContact1, 'emergency_contact1_phone', $validator, 'emergency_contact1_extend');
//        });
//
//        Validator::extendImplicit('emergency_contact1_extend', function ($attribute, $value, $parameters, $validator) use ($emergencyContacthandler) {
//            $emergencyContact1 = request()->input('emergency_contact1');
//            return $emergencyContacthandler($emergencyContact1, 'emergency_contact1_name', $validator, 'emergency_contact1_extend', 'name');
//        });
//
//        Validator::extendImplicit('emergency_contact2_extend', function ($attribute, $value, $parameters, $validator) use ($emergencyContacthandler) {
//            $emergencyContact2 = request()->input('emergency_contact2');
//            return $emergencyContacthandler($emergencyContact2, 'emergency_contact2_phone', $validator, 'emergency_contact2_extend');
//        });
//
//        Validator::extendImplicit('emergency_contact2_extend', function ($attribute, $value, $parameters, $validator) use ($emergencyContacthandler) {
//            $emergencyContact2 = request()->input('emergency_contact2');
//            return $emergencyContacthandler($emergencyContact2, 'emergency_contact2_name', $validator, 'emergency_contact2_extend', 'name');
//        });

        $list = [
//            'emergency_contact1' => 'call_need_verify',
//            'emergency_contact1_phone' => 'emergency_contact1_extend|array',
//            'emergency_contact1_name' => 'emergency_contact1_extend|array',
//            'emergency_contact2' => 'call_need_verify',
//            'emergency_contact2_phone' => 'emergency_contact2_extend|array',
//            'emergency_contact2_name' => 'emergency_contact2_extend|array',
//            'marital_status' => 'call_need_verify',
//            'income' => 'call_need_verify',
//            'english_level' => 'call_need_verify',
//            'status' => 'required|' . $this->submitStatus(ApprovePool::ORDER_CALL_GROUP),
        ];

        return $list;
    }

    /**
     * @param int $group
     * @return string
     */
    public function submitStatus($group)
    {
        $list = [
            // 初审
            ApprovePool::ORDER_FIRST_GROUP => [
                FirstApproveService::SUPPLEMENTARY,
                FirstApproveService::PASS,
                FirstApproveService::FRAUD,
            ],
            ApprovePool::ORDER_CALL_GROUP => [
                CallApproveService::PASS,
                CallApproveService::NO_ANSWER,
                CallApproveService::FAILURE_CHECK,
                CallApproveService::GAVE_UP,
            ],
        ];

        return '|in:' . implode(',', $list[$group]);
    }

    /**
     * @throws CustomException
     */
    protected function extend()
    {
        $this->init();

        $certifications = [];
        $verfiy = function ($columns, $value) use (&$certifications) {
            $status = $certifications[$columns];
            if ($status['show'] && $status['edit']) {
                return empty($value) ? false : true;
            }

            return true;
        };

        /** TODO不判断Pincode */
        $pincodeVerify = function ($data, $pincode, $validator, $attribute) {
//            $pincode = Pincode::wherePincode($pincode)->first();
//
//            if (!$pincode && !$data) {
//                $validator->setCustomMessages([$attribute => 'State/District cannot be empty']);
//                return false;
//            }
//
//            if (($pincode && $pincode->type == Pincode::TYPE_MANUAL && $data) || (!$pincode && $data)) {
//                $region = explode('/', $data);
//                if (count($region) != 2) {
//                    $validator->setCustomMessages([$attribute => 'Wrong format, please make sure the format `State/District`']);
//                    return false;
//                }
//            }

            return true;
        };

        FirstApproveService::optionalCertification($this->order, $certifications);

        Validator::extendImplicit('pan_card_extend', function ($attribute, $value, $parameters, $validator) use ($certifications, $verfiy) {
            return $verfiy('pan_card_status', $value);
        });

        Validator::extendImplicit('voter_id_card_no_extend', function ($attribute, $value, $parameters, $validator) use ($certifications, $verfiy) {
            return $verfiy('voter_id_status', $value);
        });

        Validator::extendImplicit('driving_license_no_extend', function ($attribute, $value, $parameters, $validator) use ($certifications, $verfiy) {
            return $verfiy('driving_license_status', $value);
        });

        Validator::extendImplicit('passport_no_extend', function ($attribute, $value, $parameters, $validator) use ($certifications, $verfiy) {
            return $verfiy('passport_status', $value);
        });

        Validator::extendImplicit('aadhaar_card_no_extend', function ($attribute, $value, $parameters, $validator) use ($certifications, $verfiy) {
            return $verfiy('aadhaar_status', $value);
        });

        Validator::extendImplicit('current_region_extend', function ($attribute, $value, $parameters, $validator) use ($pincodeVerify) {
            return $pincodeVerify($value, request()->input('current_pin_code'), $validator, 'current_region_extend');
        });

        Validator::extendImplicit('permanent_region_extend', function ($attribute, $value, $parameters, $validator) use ($pincodeVerify) {
            return $pincodeVerify($value, request()->input('permanent_pin_code'), $validator, 'permanent_region_extend');
        });

        Validator::extendImplicit('validate_card_num', function ($attribute, $value, $parameters, Validation $validator) {
            $data = $validator->getData();
            switch ($data['id_card_type']) {
                case 'Driver’s License':
                    if (!ValidatorHelper::validPhDriverNum($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'SSS card':
                    if (!ValidatorHelper::validPhSSSCard($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'PRC ID':
                    if (!ValidatorHelper::validPhPrcID($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'UMID':
                    if (!ValidatorHelper::validPhUMID($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'TIN':
                    if (!ValidatorHelper::validPhTIN($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case "PASSPORT":
                    if (!ValidatorHelper::validPhPPT($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'PAG-IBIG':
                    if (!ValidatorHelper::validPhPAG($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'GSIS ID':
                    if (!ValidatorHelper::validPhGSIS($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'Voter\'s ID':
                    if (!ValidatorHelper::validPhVoter($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'PIC':
                    if (!ValidatorHelper::validPhPIC($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
                case 'NBI':
                    if (!ValidatorHelper::validPhNBI($value)) {
                        $validator->setCustomMessages(['id_card_no' => "Incorrect document format"]);
                        return false;
                    }
                    break;
            }
            return true;
        });
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            static::SENARIO_FIRST_SUBMIT => [
                'pan_card_extend' => 'The :attribute field is required',
                'voter_id_card_no_extend' => 'The :attribute field is required',
                'driving_license_no_extend' => 'The :attribute field is required',
                'passport_no_extend' => 'The :attribute field is required',
                'aadhaar_card_no_extend' => 'The :attribute field is required',
                'bank_statement_extend' => 'bank statement is not last recent 3 months / bank statement months: [0,3)',
                'suspected_fraud_other_remark_extend' => 'suspected fraud other reason is required',
                'id_card_no.validate_card_num' => 'The ID No. is incorrect',
            ],
            static::SENARIO_CALL_SUBMIT => [
                'call_need_verify' => 'The :attribute field is required',
            ],
            static::SENARIO_ADDRESS_INFO => [
                'pincode.regex' => 'Pincode can only be 6 digits',
            ],
        ];
    }
}
