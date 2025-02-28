<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-25
 * Time: 17:01
 */

namespace Approve\Admin\Config;


use Api\Models\Order\Order;

class Params
{
    /**
     * @param $key
     * @return array
     */
    public static function firstApprove($key)
    {
        $list = [
            'refusal_code' => [
                'R03WD' => t('填写证件号错误', 'approve'),
                'R01OHPIV' => t('非有效头像', 'approve'),
                'R03OCPIV' => t('非有效证件', 'approve'),
                'R03OCPUQ' => t('证件不符合标准', 'approve'),
                'R03OHPUQ' => t('头像不符合标准', 'approve'),
                'R01OCPFF' => t('身份信息伪造', 'approve'),
                'R01OCPSP' => t('证件影像存在风险', 'approve'),
                'R01OHPDF' => t('头像与证照不一致', 'approve'),
                'R03DL' => t('非埃及公民', 'approve'),
                'R03OAGUQ' => t('年龄不符合规定', 'approve'),
                'R03OWCCD' => t('工作证已经过期', 'approve'),
                'R03OWCDF2' => t('客户证件姓名与在线申请不符', 'approve'),
                'R03OWCDF3' => t('头像和工作证显示不同的人', 'approve'),
                'R03OWCIV' => t('工作证无效', 'approve'),
                'R03OWCNI' => t('工作证的真实性不确定', 'approve'),
                'R03OWCUQ' => t('工作证不符合申请标准', 'approve'),
                'R03SG' => t('身份证过期', 'approve'),
                'R01FH' => t('行业或公司的负面信息', 'approve'),
            ],
            // 补件
            'suspected_fraud_status' => [
                Order::STATUS_API_REPLENISH_FACE => t('自拍', 'approve'),
                Order::STATUS_API_REPLENISH_ID => t('证件照', 'approve'),
                Order::STATUS_API_REPLENISH_ALL => t('自拍&证件照', 'approve'),
            ],
            // aadhaar file
            'aadhaar_file_list' => [
                1 => t('原始文件', 'approve'),
                2 => t('屏幕图片', 'approve'),
                3 => t('影印图片', 'approve'),
            ],

            // passport file
            'passport_file_list' => [
                1 => t('原始文件', 'approve'),
                2 => t('屏幕图片', 'approve'),
                3 => t('影印图片', 'approve'),
            ],

            // pan card status list
            'pan_card_status_list' => [
                1 => t('pan存在','approve'),
                2 => t('Pan Card的状态是不活跃的','approve'),
                3 => t('Pan Card的状态为null','approve'),
                4 => t('pan信息不匹配数据库信息','approve'),
                6 => t('不是印度公民','approve'),
                7 => t('pan是复印件','approve'),
                8 => t('pan是拍照的','approve'),
                5 => t('pan与aadhaar名字不一样','approve'),
            ],

            'passport_status_list' => [
                1 => t('发证地点与申请城市不一致','approve'),
                2 => t('名字与客户名字不一致','approve'),
                3 => t('姓与客户名字不一致','approve'),
                4 => t('证件为复印件','approve'),
                5 => t('证件使用屏幕图片拍摄','approve'),
            ],

            'driving_licence_file_list' => [
                1 => t('原始文件', 'approve'),
                2 => t('屏幕图片', 'approve'),
                3 => t('影印图片', 'approve'),
            ],

            'face_ambient_list' => [
                1 => t('家','approve'),
                2 => t('办公室','approve'),
                3 => t('户外','approve'),
                4 => t('在公共交通工具上','approve'),
                5 => t('摩托车上','approve'),
                6 => t('在小车上','approve'),
                7 => t('在咖啡厅','approve'),
                8 => t('其他地方','approve'),
            ],

            'appearance_list' => [
                1 => t('短发', 'approve'),
                2 => t('没有胡须', 'approve'),
                3 => t('戴眼镜','approve'),
                4 => t('戴面纱','approve'),
                5 => t('戴头巾', 'approve'),
            ],

            // 基础资料审批失败,增加项的index = 最大index + 1
            'base_detail_failed_list' => [
                11 => t('生活照片不清晰', 'approve'),
                1 => t('Pan卡不清晰', 'approve'),
                2 => t('选民身份证正面不清晰', 'approve'),
                //3 => t('选民身份证反面不清晰', 'approve'),
                4 => t('驾驶证正面不清晰', 'approve'),
                5 => t('驾驶证反面不清晰', 'approve'),
                12 => t('驾驶证已过期', 'approve'),
                6 => t('Aadhaar card正面不清晰', 'approve'),
                7 => t('Aadhaar card反面不清晰', 'approve'),
                8 => t('护照身份信息不清晰', 'approve'),
                //9 => t('护照资料页不清晰', 'approve'),
                10 => t('护照已过期', 'approve'),
            ],

            // 人脸比对结果下拉选项
            'face_compare_selector_list' => [
                '一致' => t('一致', 'approve'),
                '不一致' => t('不一致', 'approve'),
                '无人脸' => t('无人脸', 'approve'),
                '不清晰' => t('不清晰', 'approve'),
                '造假' => t('造假', 'approve'),
                '无需审核' => t('无需审核', 'approve'),
            ],

            // 基础资料审批疑似欺诈,增加项的index = 最大index + 1
            'base_detail_suspected_fraud_list' => [
                4 => t('Pan卡无效', 'approve'),
                5 => t('选民证无效', 'approve'),
                6 => t('护照无效', 'approve'),
                8 => t('驾照持有人不是申请人', 'approve'),
                7 => t('不是印度国籍', 'approve'),
                9 => t('Aadhaar卡持有人不是申请人', 'approve'),
                10 => t('其他例外', 'approve'),
            ],

            'voter_id_card_stauts_list' => [
                1 => t('未显示地址','approve'),
                2 => t('未显示面部照片','approve'),
                3 => t('名字与客户名字不一致','approve'),
                4 => t('姓与客户名字不一致','approve'),
                5 => t('证件为复印件','approve'),
                6 => t('证件使用屏幕图片拍摄','approve'),
                7 => t('未显示人名','approve'),
            ],

            // 银行账单审批失败
            'bank_state_status_failed_list' => [
                1 => t('没有显示银行徽标和银行名称','approve'),
                2 => t('ATM取款不是100的整数','approve'),
                3 => t('季度的最后一天或下个月的第一天没有给出季度利息','approve'),
                4 => t('银行对账单有修改痕迹','approve'),
                5 => t('字体或格式不一致y','approve'),
                6 => t('其他例外','approve'),
                7 => t('没有显示用户名','approve'),
                8 => t('名字与客户的名字不一致','approve'),
                9 => t('姓与客户的名字不一致','approve'),
            ],

            // 银行账单return
            'bank_state_status_return_list' => [
                1 => t('不接受银行存折','approve'),
                2 => t('不接受银行账户对账单','approve'),
                3 => t('不接受当前银行对账单','approve'),
                4 => t('上传的账单文件不支持PDF，请重新上传','approve'),
                5 => t('银行账单不足90天/结束日期不是当前日期','approve'),
                6 => t('银行对账单没有密码','approve'),
                7 => t('银行极度利息异常','approve'),
            ],

            // 工资单审核失败
            'payslip_status_failed_list' => [
                1 => t('工资单没有密码','approve'),
                2 => t('工资单或工资单不明确','approve'),
            ],

            // pass other exception
            'payslip_pass_other_exception_list' => [
                1 => t('没有公司名称','approve'),
                2 => t('没有公司徽标','approve'),
                3 => t('工资单或工资单上的名称与申请人的姓名不同','approve'),
                4 => t('季度的最后一天或下个月的第一天没有给出季度利息','approve'),
                5 => t('有修改的痕迹','approve'),
                6 => t('字体或格式不一致y','approve'),
                7 => t('工资单不是最新的月份','approve'),
                8 => t('其他例外','approve'),
            ],

            // 员工卡公共选项
            'employee_card_common_select_list' => [
                1 => t('Yes','approve'),
                2 => t('No','approve'),
            ],

            // 血型
            'blood_group_list' => [
                1 => t('A','approve'),
                2 => t('B','approve'),
                3 => t('O','approve'),
                4 => t('AB','approve'),
                5 => t('null','approve'),
            ],

            // 员工卡审批通过选项
            'employee_card_pass_list' => [
                1 => t('工作证上的照片不是申请人','approve'),
                2 => t('工作证上没有肖像照片','approve'),
                3 => t('公司名称与申请人提供的名称不同','approve'),
                4 => t('员工姓名与申请人不一致','approve'),
                5 => t('工牌上岗位与申请时的岗位一致','approve'),
            ],

            // 员工卡审批失败
            'employee_card_failed_list' => [
                1 => t('工作证不清楚','approve'),
            ],

            // employmentInfo
            'employment_info_company' => [
                'total_funding_amount' => [
                    1 => 'less than 1M',
                    2 => '(1,50] M',
                    3 => '(50,100] M',
                    4 => 'more than 100M',
                    5 => 'null'
                ],
                'cb_rank' => [
                    1 => 'less than 1000',
                    2 => '(1000,3000]',
                    3 => '(3000,5000]',
                    4 => 'more than 5000',
                    5 => 'null',
                ],
                'categories' => [
                    1 => 'Personal Finance/Lending/FinTech/ Financial Services',
                    2 => 'other',
                    3 => 'null',
                ],
                'founded_years' => [
                    1 => 'less than 3 years',
                    2 => '[3,5] years',
                    3 => '(5,10] years',
                    4 => 'more than 10 years',
                    5 => 'null',
                ],
                'founders_number' => [
                    1 => '1',
                    2 => '2-3',
                    3 => 'more than 3',
                    4 => 'null',
                ],
                'operating_status' => [
                    1 => 'Active',
                    2 => 'Dormant',
                    3 => 'Active in Progress',
                    4 => 'Under liquidation',
                    5 => 'Under process of Striking Off',
                    6 => 'Closed',
                    7 => 'Not available for e-filing',
                    8 => 'To be migrated',
                    9 => 'Captured',
                ],
                'employee_number' => [
                    1 => '1-10',
                    2 => '11-50',
                    3 => '51-100',
                    4 => '101-250',
                    5 => '251-500',
                    6 => '501-1000',
                    7 => '1001-5000',
                    8 => '5000+',
                ],
            ],

            'employment_info_linked_in' => [
                'highest_degree' => [
                    1 => 'junior high school or below',
                    2 => 'high school',
                    3 => 'college',
                    4 => 'university',
                    5 => 'postgraduate or above',
                    6 => 'null'
                ],
                'years_of_graduation' => [
                    1 => 'less than 3 years',
                    2 => '[3,5] years',
                    3 => '(5,10] years',
                    4 => 'more than 10 years',
                    5 => 'null'
                ],
                'current_company_working_months' => [
                    1 => 'less than 6 months',
                    2 => '[6,12]months',
                    3 => '(12,36]months',
                    4 => 'more than 36 months',
                    5 => 'null'
                ],
                'number_of_fulltime_work_companies' => [
                    1 => 'less than 3',
                    2 => '3-6',
                    3 => '7-9',
                    4 => '10and above',
                    5 => 'null'
                ],
                'cumulative_employment_duration' => [
                    1 => 'less than 3 years',
                    2 => '[3,5]years',
                    3 => '(5,10]years',
                    4 => 'more than 10years',
                    5 => 'null'
                ],
                'cumulative_number_of_working_cities' => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 'more than 3',
                    5 => 'null'
                ],
                'number_of_friends' => [
                    1 => 'less than 100',
                    2 => '[100, 500]',
                    3 => 'more than 500',
                    4 => 'null'
                ],
                'skill_recognition_number' => [
                    1 => 'less than 3',
                    2 => '3-4',
                    3 => '5-6',
                    4 => '6 and above',
                    5 => 'null'
                ],
                'number_of_recommended_letters' => [
                    1 => 'less than 3',
                    2 => '3-4',
                    3 => '5-6',
                    4 => '6 and above',
                    5 => 'null'
                ],
                'last_time_publishing_dynamics' => [
                    1 => 'within 1 day',
                    2 => 'within 1 week',
                    3 => 'within 1 month',
                    4 => 'more than 1 month',
                    5 => 'no dynamic'
                ],
                'articles_and_dynamic_followers' => [
                    1 => '<100',
                    2 => '(100, 1000]',
                    3 => '(1000, 10000]',
                    4 => '>10000',
                    5 => 'no followers'
                ],
            ],

            'normal_status_list' => [
                'Y' => t('正确', 'approve'),
                'N' => t('不正确', 'approve'),
            ],

            'gender_list' => [
                'Male' => 'Male',
                'Female' => 'Female',
            ],
            "failed" => [
                'R01SG' => 'Verify the work is fake',
                'R03DL' => 'Non-Vietnamese citizens',
                'R03SG' => 'ID card expired',
                'R03SW' => 'Identity card number abnormality',
                'R03ZF' => 'No zalo&fb number',
                'R01OCPSP' => 'Authenticity of ID card is uncertain',
                'R01OHPDF' => 'Head portrait and id card show different people',
                'R01OIDOL' => 'Risk applicant repeated application',
                'R01OMGNI' => 'Negative information of SMS',
                'R01OMPCD' => 'Applicant\'phone no. is closing down',
                'R01RMPCD' => 'Contacts\'phone no. is closing down',
                'R02OTCDF' => 'Not client’s application',
                'R01OHPIV' => 'Photo is not head portrait',
                'R03OAGUQ' => 'Age does not meet the regulations',
                'R03OCPIV' => 'ID card is invalid',
                'R03OCPUQ' => 'ID card does not meet the application standards',
                'R03OHPUQ' => 'Head portrait does not meet the application standards',
                'R03OIDOL' => 'Applicant repeated application',
                'R03OJIUQ' => 'Occupation does not meet the regulations',
                'R03OHPMI' => 'there is no head portrait',
                'R03OBADF' => 'customer\'s name is inconsistent with the bankcard_no',
            ],
            "fraud" => [
                'R01HZ' => 'Intermediary / packaging to apply',
                'R01OCPFF' => 'ID forgery',
            ]
        ];

        return (array)array_get($list, $key);
    }

    /**
     * @param $key
     * @return array
     */
    public static function callApprove($key)
    {
        $list = [
            'refusal_code' => [
                'R03OIDOL' => t('填写证件号错误', 'approve'),
                'R03OJIUQ' => t('职业不符合规定', 'approve'),
                'R01OIDOL' => t('风险申请人重复申请', 'approve'),
                'R02QB' => t('取消', 'approve'),
                'R02OHBNI' => t('负面信息', 'approve'),
                'R01OMGNI' => t('负面信息-短信异常', 'approve'),
                'R02OJIIV' => t('客户无收入来源', 'approve'),
                'R02OMPNA' => t('申请人电话无人接听', 'approve'),
                'R02OTCNV' => t('申请人拒绝核实信息', 'approve'),
                'R02RMPNA' => t('联系人电话无法接通', 'approve'),
                'R02RTCDF' => t('联系人核实异常', 'approve'),
                'R02OTCNI' => t('申请人拒绝补充联系人', 'approve'),
                'R02RTCNV' => t('联系人拒绝核实信息', 'approve'),
                'R01OMPCD' => t('申请人号码异常', 'approve'),
                'R01RMPCD' => t('联系人号码异常', 'approve'),
                'R02OTCDF' => t('申请人身份存疑', 'approve'),
                'R01FS' => t('非本人设备申请', 'approve'),
                'R02OHADF' => t('申请人的家庭地址是假的', 'approve'),
                'R02OHANV' => t('地址不详细、不完整', 'approve'),
                'R02OJIDF' => t('工作信息造假', 'approve'),
                'R02OSAIV' => t('申请人没有有效的社交网络账号', 'approve'),
                'R02OTCNC' => t('客户对金额的减少不满意', 'approve'),
            ],
            'contact_options' => [
                'no_call' => t('未致电', 'approve'),
                'no_answer' => t('不接听', 'approve'),
                'no_cooperate' => t('不配合核实', 'approve'),
                'check_diff' => t('核实不一致', 'approve'),
                'check_same' => t('核实一致', 'approve'),
            ],
            'give_up_loan_list' => [
                1 => t('额度低', 'approve'),
                2 => t('利息高', 'approve'),
                3 => t('审批效率低', 'approve'),
                4 => t('贷款期限太短', 'approve'),
                5 => t('暂无贷款需求', 'approve'),
                6 => t('其他', 'approve'),
            ],
            'normal_status_list' => [
                'Y' => t('正确', 'approve'),
                'N' => t('不正确', 'approve'),
            ],
            'english_level_list' => [
                1 => t('良', 'approve'),
                2 => t('一般', 'approve'),
                3 => t('差', 'approve'),
            ],
            "failed" => [
                'R01FS' => 'Non personal equipment application',
                'R02QB' => 'Cancel',
                'R02SH' => 'Unable to verify client\'s work',
                'R02OHANV' => 'The address is not detailed and complete',
                'R02OHADF' => 'The home address of the  applicant is fake',
                'R02OHBNI' => 'Applicant have a mess life',
                'R02OJIDF' => 'The job information of the  applicant is fake',
                'R02OJIIV' => 'Applicant don\'t have a job',
                'R02OMPNA' => 'Applicant\'phone  is no answer',
                'R02OTCNV' => 'Applicant refuser to verify the information',
                'R02RMPNA' => 'Contacts\'phone  is no answer',
                'R02RTCDF' => 'Contact is false',
                'R02RTCNV' => 'Contacts refuser to verify the information',
                'R02OTCNI' => 'Applicant refuse to add contacts ',
            ]
        ];

        return (array)array_get($list, $key);
    }

    /**
     * @param $key
     * @return array
     */
    public static function allRefusalCode()
    {
        return ['R03WD' => t('填写证件号错误', 'approve'),
                'R01OHPIV' => t('非有效头像', 'approve'),
                'R03OCPIV' => t('非有效证件', 'approve'),
                'R03OCPUQ' => t('证件不符合标准', 'approve'),
                'R03OHPUQ' => t('头像不符合标准', 'approve'),
                'R01OCPFF' => t('身份信息伪造', 'approve'),
                'R01OCPSP' => t('证件影像存在风险', 'approve'),
                'R01OHPDF' => t('头像与证照不一致', 'approve'),
                'R03DL' => t('非埃及公民', 'approve'),
                'R03OAGUQ' => t('年龄不符合规定', 'approve'),
                'R03OWCCD' => t('工作证已经过期', 'approve'),
                'R03OWCDF2' => t('客户证件姓名与在线申请不符', 'approve'),
                'R03OWCDF3' => t('头像和工作证显示不同的人', 'approve'),
                'R03OWCIV' => t('工作证无效', 'approve'),
                'R03OWCNI' => t('工作证的真实性不确定', 'approve'),
                'R03OWCUQ' => t('工作证不符合申请标准', 'approve'),
                'R03SG' => t('身份证过期', 'approve'),
                'R01FH' => t('行业或公司的负面信息', 'approve'),

				 'R01SG' => 'Verify the work is fake',
                'R03SW' => 'Identity card number abnormality',
                'R03ZF' => 'No zalo&fb number',
                'R03OHPMI' => 'there is no head portrait',
                'R03OBADF' => 'customer\'s name is inconsistent with the bankcard_no',
				 'R01HZ' => 'Intermediary / packaging to apply',
				  'R03OIDOL' => t('填写证件号错误', 'approve'),
                'R03OJIUQ' => t('职业不符合规定', 'approve'),
                'R01OIDOL' => t('风险申请人重复申请', 'approve'),
                'R02QB' => t('取消', 'approve'),
                'R02OHBNI' => t('负面信息', 'approve'),
                'R01OMGNI' => t('负面信息-短信异常', 'approve'),
                'R02OJIIV' => t('客户无收入来源', 'approve'),
                'R02OMPNA' => t('申请人电话无人接听', 'approve'),
                'R02OTCNV' => t('申请人拒绝核实信息', 'approve'),
                'R02RMPNA' => t('联系人电话无法接通', 'approve'),
                'R02RTCDF' => t('联系人核实异常', 'approve'),
                'R02OTCNI' => t('申请人拒绝补充联系人', 'approve'),
                'R02RTCNV' => t('联系人拒绝核实信息', 'approve'),
                'R01OMPCD' => t('申请人号码异常', 'approve'),
                'R01RMPCD' => t('联系人号码异常', 'approve'),
                'R02OTCDF' => t('申请人身份存疑', 'approve'),
                'R01FS' => t('非本人设备申请', 'approve'),
                'R02OHADF' => t('申请人的家庭地址是假的', 'approve'),
                'R02OHANV' => t('地址不详细、不完整', 'approve'),
                'R02OJIDF' => t('工作信息造假', 'approve'),
                'R02OSAIV' => t('申请人没有有效的社交网络账号', 'approve'),
                'R02OTCNC' => t('客户对金额的减少不满意', 'approve'),

                'R02SH' => 'Unable to verify client\'s work',
            'R03OIDOB' => t('人脸搜索', 'approve'),
            'R03OIDFF' => t('人脸搜索', 'approve'),
            'R04OIDIB' => t('本人证件号码命中内部黑名单', 'approve'),
            'R04OPNIB' => t('本人电话号码命中内部黑名单', 'approve'),
            'R04OEMIB' => t('本人Email命中内部黑名单', 'approve'),
            'R04OMPIB' => t('本人deviceid、cookieid、advertisingid、persistentDeviceId任一命中内部黑名单', 'approve'),
            'R04OBAIB' => t('本人银行卡号命中内部黑名单', 'approve'),
            'R04ONMIB' => t('本人姓名|出生日期组合命中内部黑名单', 'approve'),
            'R04RPNIB' => t('联系人电话号码或单位电话命中内部黑名单', 'approve'),
            'R04OIPIB' => t('ip命中内部黑名单', 'approve'),
            'R04OBBIB' => t('银行卡号段命中内部黑名单', 'approve'),
            'R04OIDOB' => t('本人证件号码命中跨品牌黑名单', 'approve'),
            'R04OPNOB' => t('本人电话号码命中跨品牌黑名单', 'approve'),
            'R04OEMOB' => t('本人Email命中跨品牌黑名单', 'approve'),
            'R04OMPOB' => t('本人deviceid、cookieid、advertisingid、persistentDeviceId任一命中跨品牌黑名单', 'approve'),
            'R04OBAOB' => t('本人银行卡号命中跨品牌黑名单', 'approve'),
            'R04ONMOB' => t('本人姓名|出生日期组合命中跨品牌黑名单', 'approve'),
            'R04RPNOB' => t('联系人电话号码或单位电话命中跨品牌黑名单', 'approve'),
            'R04OIPOB' => t('ip命中跨品牌黑名单', 'approve'),
            'R04OBBOB' => t('银行卡号段命中跨品牌黑名单', 'approve')
            ];
    }
}
