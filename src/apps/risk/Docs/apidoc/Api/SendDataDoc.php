<?php

namespace Risk\Docs\apidoc\Api;

/**
 * 风控接口 - 发送数据
 * @package Risk\Docs\apidoc\Api
 */
interface SendDataDoc
{
    /**
     * @api {post} /api/risk/send_data/all 上传用户风控数据
     * @apiGroup SendData
     * @apiVersion 1.0.0
     * @apiDescription
     * 上传用户风控数据 <br/>
     * 注意资料项的可选属性只表示接口单次上传可省略，支持多次上传，按每项资料项下的唯一记录ID去重。<br/>
     * 具体针对审批任务的必填数据项项需看[创建机审任务]处返回的必须数据项
     *
     * @apiParam {String} user_id 用户唯一标识
     * @apiParam {String} [task_no] 机审任务编号，非必填。若存在编号，则会完成机审任务对应的数据上传。若不存在编号，则为普通上传，不会关联机审任务
     * @apiParam {Array} [BANK_CARD] 用户银行卡数据列表
     * @apiParam {Array} [COLLECTION_RECORD] 用户催收记录列表
     * @apiParam {Array} [ORDER] 用户所有订单记录列表
     * @apiParam {Array} [ORDER_DETAIL] 用户所有订单详情数据列表
     * @apiParam {Array} [REPAYMENT_PLAN] 用户所有还款计划列表
     * @apiParam {Array} [USER] 用户信息单项
     * @apiParam {Array} [USER_CONTACT] 用户紧急联系人信息
     * @apiParam {Array} [USER_INFO] 用户详情
     * @apiParam {Array} [USER_WORK] 用户工作信息
     * @apiParam {Array} [USER_AUTH] 用户认证数据
     * @apiParam {Array} [USER_THIRD_DATA] 用户第三方数据
     * @apiParam {Array} [USER_APPLICATION] 用户app应用列表
     * @apiParam {Array} [USER_CONTACTS_TELEPHONE] 通讯录列表
     * @apiParam {Array} [USER_PHONE_HARDWARE] 手机硬件信息
     * @apiParam {Array} [USER_PHONE_PHOTO] 相册信息
     * @apiParam {Array} [USER_POSITION] 位置信息
     * @apiParam {Array} [USER_SMS] 短信列表
     *
     * @apiSuccess (返回值) {int} status 请求状态 18000:成功 13000:失败 **全局状态**
     * @apiSuccess (返回值) {String} msg 信息
     * @apiSuccess (返回值) {Array} data 返回数据
     * @apiSuccess (返回值) {String} data.-- *上传任务项每项的结果 成功：SUCCESS  失败：FAILED 。若关联了机审任务上传数据，失败的数据项需要重新上传否则不能成功发起机审执行
     *
     * @apiSampleRequest off
     *
     * @apiParamExample BANK_CARD
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 业务系统银行卡ID，如记录自增id
     * no           | 是     | string    | 银行卡号
     * name         | 是     | string    | 用户姓名
     * status       | 是     | string    | 状态。 1:正常  0:废弃   至少需要一条状态为 1 的银行卡记录
     * created_at   | 是     | date      | 创建时间 Y-m-d H:i:s
     * ifsc         | 是     | string    | ifsc
     * bank_name    | 是     | string    | 银行名
     * bank_branch_name | 否 | string    | 支行名称
     * city         | 否     | string    | 城市
     * province     | 否     | string    | 州
     * updated_at   | 否     | date      | 记录最后修改时间 Y-m-d H:i:s
     *
     * @apiParamExample BANK_CARD 示例
     * {"user_id":"123","BANK_CARD":[{"id":915,"no":"50100009665390","name":"Bhushan Dilip Savale","bank_name":"HDFC BANK","status":1,"created_at":"2020-01-23 10:42:44","ifsc":"HDFC0001293"},{"id":804,"no":"31657456705","name":"Bhushan Dilip Savale","bank_name":"STATE BANK OF INDIA","status":-2,"created_at":"2019-11-12 06:54:15","ifsc":"SBIN0000441"},{"id":490,"no":"024301079497","name":"SAURAV  RAJORE","bank_name":"ICICI BANK LIMITED","status":0,"created_at":"2019-08-22 08:02:15","ifsc":"ICIC0000243"}]}
     *
     * @apiParamExample COLLECTION_RECORD
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 业务系统催收记录ID，如记录自增id
     * order_id     | 是     | int       | 订单ID
     * fullname     | 是     | string    | 被催收人姓名
     * relation     | 是     | string    | 被催收人与借款人关系 父亲:FATHER 母亲:MOTHER  兄弟:BROTHERS  姐妹:SISTERS  儿子:SON  女儿:DAUGHTER  妻子:WIFE  丈夫:HUSBAND  其他:OTHER
     * contact      | 是     | string    | 被催收电话
     * dial         | 是     | string    | 联系情况  正常联系:normal_contact  无法联系:unable_contact
     * progress     | 是     | string    | 联系情况  承诺还款:committed_repayment  本人无意还款:self_inadvertently_repay  有意告知:intentional_notification  有意帮还:intentional_help  无意向:unintentional  过多承诺跳票:too_many_promises  停机空号:shut_down_no  关机:shutdown  拒绝接听:refusal_to_answer  忙线:busy_line  无效音:invalid_sound  来电提醒:call_reminder  暂时无人接通:temporary_nobody_connection  反复无人接听:repeatedly_unanswered  反复接听挂断:repeatedly_hang_up
     * created_at   | 是     | date      | 催收时间 Y-m-d H:i:s
     * overdue_days | 是     | int       | 催收时逾期天数
     * updated_at   | 否     | date      | 记录最后修改时间 Y-m-d H:i:s
     * promise_paid_time | 否     | date     | 承诺还款时间 合法时间格式。如：Y-m-d H:i:s 或 Y-m-d 等
     * reduction_fee| 否     | numeric   | 当前减免金额
     * receivable_amount | 否    | numeric   | 当前应还金额
     * receivable_amount | 否    | string    | 备注
     * admin_id     | 否    | string    | 备注
     *
     * @apiParamExample COLLECTION_RECORD 示例
     * {"user_id":"123","COLLECTION_RECORD":[{"id":104,"order_id":638,"fullname":"Bhushan Dilip Savale","relation":"oneself","contact":"9999988888","dial":"normal_contact","progress":"self_inadvertently_repay","created_at":"2019-12-11 09:28:24","overdue_days":75},{"id":106,"order_id":638,"fullname":"test","relation":"MOTHER","contact":"9999988887","dial":"normal_contact","progress":"intentional_notification","created_at":"2019-12-20 14:12:14","overdue_days":84}]}
     *
     * @apiParamExample ORDER
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 订单唯一ID,由数字组成
     * order_no     | 是     | string    | 订单业务编号
     * principal    | 是     | numeric   | 借款金额
     * loan_days    | 是     | int       | 借款天数
     * status       | 是     | string    | 订单当前状态  创建:create  待审批:wait_system_approve   审批中:system_approving   审批通过:system_pass  已签约:sign  放款中:paying  出款成功待还款:system_paid   出款失败:system_pay_fail   取消借款:manual_cancel   已逾期:overdue   正常结清:finish   逾期结清:overdue_finish   已坏账:collection_bad
     * created_at   | 是     | date      | 订单创建时间 Y-m-d H:i:s
     * quality      | 是     | int       | 新老用户类型 新用户:0  复借用户:1
     * daily_rate   | 是     | numeric   | 日利率 小数。如0.09% 需传 0.0009
     * overdue_rate | 是     | numeric   | 逾期日利率 小数。 如0.09% 需传 0.0009
     * overdue_rate | 是     | numeric   | 逾期日利率 小数。 如0.09% 需传 0.0009
     * app_client   | 否     | string    | 订单创建客户端  android  ios  h5
     * updated_at   | 否     | date      | 最后修改时间 Y-m-d H:i:s
     * signed_time  | 否     | date      | 签约时间 Y-m-d H:i:s
     * system_time  | 否     | date      | 审批时间 Y-m-d H:i:s
     * paid_time    | 否     | date      | 放款时间 Y-m-d H:i:s
     * paid_amount  | 否     | numeric   | 支付金额
     * pay_channel  | 否     | string    | 支付渠道
     * cancel_time  | 否     | date      | 取消时间 Y-m-d H:i:s
     * overdue_time | 否     | date      | 进入逾期的时间 Y-m-d H:i:s
     * bad_time     | 否     | date      | 进入坏账的时间 Y-m-d H:i:s
     * pass_time    | 否     | date      | 审批通过时间 Y-m-d H:i:s
     * reject_time  | 否     | date      | 审批拒绝时间 Y-m-d H:i:s
     * app_version  | 否     | string    | 版本号. 业务版本号
     *
     * @apiParamExample ORDER 示例
     * {"user_id":"123","ORDER":[{"id":1071,"order_no":"88539845E7C9373BC3","principal":"5000.00","loan_days":30,"status":"finish","created_at":"2020-03-26 17:05:15","updated_at":"2020-03-26 18:12:11","app_client":"android","app_version":"1.0.8","quality":0,"daily_rate":"0.00100","overdue_rate":"0.00500","signed_time":"2020-03-26 18:10:20","system_time":"2020-03-26 17:07:06","paid_time":"2020-03-26 18:10:55","paid_amount":"4410.00","pay_channel":"Manual Disburse","cancel_time":null,"pass_time":"2020-03-26 18:05:02","overdue_time":null,"bad_time":null,"reject_time":null},{"id":1072,"order_no":"88493335E7CA336099","principal":"5000.00","loan_days":30,"status":"sign","created_at":"2020-03-26 18:12:30","updated_at":"2020-03-28 12:40:06","app_client":"android","app_version":"1.0.8","quality":1,"daily_rate":"0.00100","overdue_rate":"0.00500","signed_time":"2020-03-28 12:40:06","system_time":"2020-03-26 18:15:09","paid_time":null,"paid_amount":null,"pay_channel":"","cancel_time":null,"pass_time":"2020-03-28 12:38:22","overdue_time":null,"bad_time":null,"reject_time":null}]}
     *
     * @apiParamExample ORDER_DETAIL
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 订单唯一ID,由数字组成
     * order_id     | 是     | int       | 订单ID
     * key          | 是     | int       | 订单详情key 需传的订单信息key： imei  loan_position  loan_ip   bank_card_no
     * value        | 是     | int       | 订单详情value  imei:订单创建时的设备imei  loan_position：订单提交时的经纬度,使用','分割，经度在前。如:113.949547,22.534047   loan_ip:提交订单时的ip   bank_card_no:绑定的放款银行卡号
     * created_at   | 是     | int       | 创建时间
     *
     * @apiParamExample ORDER_DETAIL 示例
     * {"user_id":"123","ORDER_DETAIL":[{"id":6460,"order_id":1071,"key":"loan_location","value":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u4eae\u7532\u5e97\u8def\u897f\u5317\u65fa","created_at":"2020-03-26 17:05:15"},{"id":6461,"order_id":1071,"key":"loan_position","value":"116.235094,40.047068","created_at":"2020-03-26 17:05:15"},{"id":6462,"order_id":1071,"key":"loan_ip","value":"221.218.141.61","created_at":"2020-03-26 17:05:15"},{"id":6466,"order_id":1071,"key":"imei","value":"dc63dbb4f7f4663d","created_at":"2020-03-26 17:05:15"},{"id":6470,"order_id":1071,"key":"bank_card_no","value":"50100009665390","created_at":"2020-03-26 18:10:20"},{"id":6472,"order_id":1072,"key":"loan_location","value":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u4eae\u7532\u5e97\u8def\u897f\u5317\u65fa","created_at":"2020-03-26 18:12:30"},{"id":6473,"order_id":1072,"key":"loan_position","value":"116.235297,40.047027","created_at":"2020-03-26 18:12:30"},{"id":6474,"order_id":1072,"key":"loan_ip","value":"123.51.194.13","created_at":"2020-03-26 18:12:30"},{"id":6478,"order_id":1072,"key":"imei","value":"dc63dbb4f7f4663d","created_at":"2020-03-26 18:12:30"},{"id":6585,"order_id":1072,"key":"bank_card_no","value":"50100009665390","created_at":"2020-03-28 12:40:06"}]}
     *
     * @apiParamExample REPAYMENT_PLAN
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录ID
     * order_id     | 是     | int       | 订单ID
     * status       | 是     | int       | 还款计划状态。未完成:0  已完成:1
     * overdue_days | 是     | int       | 当前逾期天数 大于等于0的整数。未逾期填0
     * appointment_paid_time| 是     | date      | 应还款时间 Y-m-d H:i:s
     * created_at   | 是     | date      | 还款计划创建时间 Y-m-d H:i:s
     * updated_at   | 是     | date      | 还款计划最后修改时间 Y-m-d H:i:s
     * installment_num      | 是     | int       | 还款计划期数。订单下唯一 1,2,3  只有一期填1即可
     * repay_proportion     | 是     | numeric   | 当期比例。当前还款计划占订单的百分比 0-100
     * repay_time   | 否     | date      | 实际还款时间 Y-m-d H:i:s
     * repay_amount | 否     | numeric   | 实际还款金额
     * repay_channel| 否     | string    | 还款渠道
     * reduction_fee| 否     | numeric   | 减免金额
     * overdue_fee  | 否     | numeric   | 逾期费用
     * no           | 否     | string    | 还款计划编号
     *
     * @apiParamExample REPAYMENT_PLAN 示例
     * {"user_id":"123","REPAYMENT_PLAN":[{"id":617,"order_id":1046,"status":2,"overdue_days":17,"appointment_paid_time":"2020-03-09 17:41:13","created_at":"2020-03-24 17:38:47","updated_at":"2020-03-26 12:29:09","installment_num":1,"repay_proportion":"99.99"},{"id":618,"order_id":1046,"status":3,"overdue_days":0,"appointment_paid_time":"2020-04-08 17:41:13","created_at":"2020-03-24 17:38:47","updated_at":"2020-03-26 12:30:06","installment_num":2,"repay_proportion":"0.01"},{"id":655,"order_id":1068,"status":1,"overdue_days":11,"appointment_paid_time":"2020-03-22 11:18:01","created_at":"2020-03-27 08:28:04","updated_at":"2020-04-02 00:05:04","installment_num":1,"repay_proportion":"99.99"},{"id":656,"order_id":1068,"status":1,"overdue_days":0,"appointment_paid_time":"2020-04-21 11:18:01","created_at":"2020-03-27 08:28:04","updated_at":"2020-03-27 11:18:01","installment_num":2,"repay_proportion":"0.01"}]}
     *
     * @apiParamExample USER
     * 一维数组列字段！
     * 字段         | 必填    | 类型      | 描述
     * telephone    | 是     | int       | 手机号
     * fullname     | 是     | string    | 用户姓名
     * id_card_no   | 是     | string    | 用户身份证号。pan card no
     * status       | 是     | int       | 用户状态 正常:1 禁用:2 删除:-1
     * created_at   | 是     | date      | 用户创建时间 Y-m-d H:i:s
     * updated_at   | 是     | date      | 最后修改时间 Y-m-d H:i:s
     * client_id    | 是     | string    | 用户注册设备类型 android  ios  h5
     * quality      | 是     | int       | 用户类型 0:新用户 1:复贷用户
     * quality_time | 否     | date      | 成为复贷用户时间
     *
     * @apiParamExample USER 示例
     * {"user_id":"123","USER":{"telephone":"6356896580","fullname":"DURGAKIRAN DUNABOINA","id_card_no":"APWPD5178H","status":1,"created_at":"2020-03-16 15:09:49","updated_at":"2020-03-27 08:12:44","client_id":"android","quality":1,"quality_time":"2020-03-26 12:57:25"}}
     *
     * @apiParamExample USER_CONTACT
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录ID
     * contact_fullname     | 是     | string    | 紧急联系人名字
     * contact_telephone    | 是     | string    | 紧急联系人电话
     * relation     | 是     | string    | 紧急联系人关系 FATHER MOTHER BROTHERS SISTERS SON DAUGHTER WIFE HUSBAND OTHER
     * status       | 是     | string    |  状态 正常:1  弃用:0
     * created_at   | 是     | date      |  记录创建时间
     * updated_at   | 是     | date      |  记录最后修改时间
     * times_contacted      | 否     | date      |  联系次数
     * last_time_contacted  | 否     | date      |  最近联系时间 毫秒级时间戳
     * last_time_contacted  | 否     | int       |  最近联系时间 毫秒级时间戳
     * starred      | 否     | int       |  是否收藏 是:1  否:0
     * contact_last_updated_timestamp   | 否     | int       |  联系人最后编辑时间 毫秒级时间戳
     * has_phone_number     | 否     | int       |  手机联系人下手机号的数量
     *
     * @apiParamExample USER_CONTACT 示例
     * {"user_id":"123","USER_CONTACT":[{"id":1963,"contact_fullname":"ndnjsjsjs","contact_telephone":"6364947869","relation":"FATHER","status":1,"created_at":"2020-03-16 17:02:58","updated_at":"2020-03-16 17:02:58","times_contacted":2,"last_time_contacted":1583936294400,"has_phone_number":"1","starred":1,"contact_last_updated_timestamp":1583936359999},{"id":1964,"contact_fullname":"dnnnsjsjs","contact_telephone":"8123456789","relation":"BROTHERS","status":1,"created_at":"2020-03-16 17:02:58","updated_at":"2020-03-16 17:02:58","times_contacted":2,"last_time_contacted":1583938454400,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1583978504513}]}
     *
     * @apiParamExample USER_INFO
     * 一维数组列字段!
     * 字段             | 必填    | 类型      | 描述
     * id               | 是     | int       | 记录ID
     * gender           | 是     | string    | 性别 男:Male  女:Female
     * province         | 是     | string    | 所在州(用户填写)
     * city             | 是     | string    | 所在城市(用户填写)
     * address          | 是     | string    | 地址(用户填写)
     * pincode          | 是     | string    | pincode(用户填写)
     * education_level      | 是     | string    | 教育程度 小学:Primary  初级中学学历:General Secondary  高级中学学历:Senior Secondary  本科:University Degree  研究生:Postgraduate  博士:Doctoral Degree  其他:other
     * marital_status       | 是     | string    | 婚姻状况  已婚:Married  未婚:Unmarried  离异:Divorce  丧偶:Widowhood
     * birthday         | 是     | date      | 生日 d/m/Y 格式
     * father_name      | 是     | string    | 父亲姓名
     * residence_type   | 是     | string    | 住宅类型  租住:RENTED  拥有:OWNED  跟家属住:WITH RELATIVES  公司宿舍:OFFICE PROVIDED
     * religion         | 是     | string    | 宗教  印度教:Hinduism  伊斯兰教:Islam  基督教:Christianity  锡克教:Sikhism  耆那教:Jainism  无宗教:No religion  其他:other
     * language         | 是     | string    | 语言  印度语:Hindi 英语:English  马拉地语:Marathi  孟加拉语:Bengali  泰卢固语:Telugu  泰米尔语:Tamil  古吉拉特语:Gujarati  卡纳达语:Kannada  乌尔都语:Urdu  马拉雅拉姆语:Malayalam  奥里亚:Odia  旁遮普语:Punjabi  阿萨姆语:Assamese
     * pan_card_no      | 是     | string    | pan card no
     * aadhaar_card_no  | 是     | string    | aadhaar card no
     * email            | 是     | email     | email
     * created_at       | 是     | date      | 记录创建时间
     * updated_at       | 是     | date      | 最后修改时间
     * input_name       | 否     | string    | 用户输入的姓名
     * input_birthday   | 否     | string    | 用户输入生日
     * chirdren_count   | 否     | numeric   | 子女数
     * passport_no      | 否     | string    | 护照编号
     * voter_id_card_no | 否     | string    | 选民身份证
     * driving_license_no| 否    | string    | 驾驶证
     * permanent_province| 否    | string    | 永久居住州(证件识别)
     * permanent_city   | 否     | string    | 永久居住城市(证件识别)
     * permanent_address| 否     | string    | 永久居住地址(证件识别)
     * permanent_pincode| 否     | string    | 永久居住地址pincode(证件识别)
     * period_of_resident   | 否     | string    | 常驻地居住时间  一年以下:less_than_a_year  一到两年:one_to_two_years   两到三年:two_to_three_years  三年以上:more_than_three_years
     *
     * @apiParamExample USER_INFO 示例
     * {"user_id":"123","USER_INFO":{"id":695,"gender":"Male","province":"TELANGANA","city":"Hyderabad","address":"bnnnbvc","pincode":"500028","permanent_province":"TELANGANA","permanent_city":"Hyderabad","permanent_address":"S\/O Dunaboina Adinarayana, HNO 12-2- 709\/C\/19\/3,1ST FLOOR,SAIRAM NILAYAM, KAROLBAGH SOCIETY, GUDIMALKAPUR, PADMANABHANAGAR,Asifnagac,P Dk, Telangana - 500028","permanent_pincode":"500028","expected_amount":"","education_level":"Primary","marital_status":"Married","birthday":"04\/04\/1973","father_name":"ADINARAYANA DUNABOINA","residence_type":"WITH RELATIVES","religion":"Hinduism","language":"Assamese","pan_card_no":"APWPD5178H","aadhaar_card_no":"261726665659","passport_no":"","voter_id_card_no":"ZUC0267203","driving_license_no":"","email":"jjjnjho@163.com","created_at":"2020-03-31 10:51:02","updated_at":"2020-03-31 10:51:27","input_name":"nskdkkdkd","contacts_count":2305,"input_birthday":"04\/04\/1973","chirdren_count":"1"}}
     *
     * @apiParamExample USER_WORK
     * 一维数组列字段!
     * 字段             | 必填    | 类型      | 描述
     * id               | 是     | int       | 记录ID
     * employment_type  | 是     | string    | 就业类型  全职:full-time salaried  兼职:part-time salaried  个体经营:self-employed  无业:no job  学生:student  其他:other
     * created_at       | 是     | string    | 记录创建时间
     * updated_at       | 否     | string    | 最后修改时间
     * profession       | 否     | string    | 职业
     * workplace_pincode| 否     | string    | 工作地点pincode
     * company          | 否     | string    | 公司名
     * work_address1    | 否     | string    | 工作地点1
     * work_address2    | 否     | string    | 工作地点2
     * work_positon     | 否     | string    | 工作位置
     * salary           | 否     | string    | 薪水范围  "below and ₹15,000", "₹15,000-₹25,000", "₹25,000-₹35,000", "₹35,000-₹45,000", "₹45,000-₹55,000", "₹55,000 and above"
     * work_email       | 否     | string    | 工作邮箱
     * work_start_date  | 否     | date    | 开始工作时间 格式 d/m/Y
     * work_phone       | 否     | string    | 工作电话
     *
     * @apiParamExample USER_WORK 示例
     * {"user_id":"123","USER_WORK":{"id":99,"profession":"Bars\/Pubs","workplace_pincode":"125001","company":"asd","work_address1":"1","work_address2":"","work_positon":"","salary":"\u20b935,000-\u20b945,000","work_email":"","work_start_date":"01\/01\/1981","work_experience_years":0,"work_experience_months":0,"work_phone":"","employment_type":"part-time salaried","created_at":"2019-08-19 16:42:42","updated_at":"2019-11-22 13:57:58"}}
     *
     * @apiParamExample USER_AUTH
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录列ID
     * type         | 是     | string    | 认证项类型 基础信息(user_info完善状态):base_info
     * time         | 是     | date      | 认证时间 Y-m-d H:i:s
     * status       | 是     | int       | 认证项状态 1:有效 0:失效
     * created_at   | 是     | date      | 记录创建时间 Y-m-d H:i:s
     * updated_at   | 是     | date      | 记录修改时间 Y-m-d H:i:s
     *
     * @apiParamExample USER_AUTH 示例
     * {"user_id":"123","USER_AUTH":[{"id":2316,"type":"base_info","time":"2019-11-22 19:59:28","status":1,"created_at":"2019-11-22 13:59:28","updated_at":"2019-11-22 13:59:28"}]}
     *
     * @apiParamExample USER_APPLICATION
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 应用记录列ID
     * app_name     | 是     | string    | app名称
     * pkg_name     | 是     | string    | 包名
     * installed_time| 是    | int       | 应用安装时间 毫秒级时间戳
     * updated_time | 是     | int       | 应用更新时间 毫秒级时间戳
     * created_at   | 是     | date      | 记录创建时间date
     *
     * @apiParamExample USER_APPLICATION 示例
     * {"user_id":"123","USER_APPLICATION":[{"id":906,"app_name":"Boss123","pkg_name":"com.hpbr.bosszhipin","installed_time":1550294204433,"updated_time":1568817430600,"created_at":"2019-10-30 13:50:32"},{"id":907,"app_name":"IndiaOxDemo","pkg_name":"com.cashfintech.cashnow","installed_time":1572423599472,"updated_time":1572423599472,"created_at":"2019-10-30 13:50:32"},{"id":908,"app_name":"test","pkg_name":"com.daimatest.gold","installed_time":1559104862162,"updated_time":1561262223326,"created_at":"2019-10-30 13:50:32"}]}
     *
     * @apiParamExample USER_CONTACTS_TELEPHONE
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录列ID
     * contact_fullname | 是     | int       | 联系人姓名
     * contact_telephone| 是     | int       | 联系人手机号
     * relation_level   | 是     | int       | 联系人等级。暂默认为 0
     * created_at       | 是     | date      | 记录创建时间
     * times_contacted  | 否     | int       | 联系次数
     * last_time_contacted  | 否     | int       | 最近联系时间 毫秒级时间戳
     * has_phone_number     | 否     | int       | 手机联系人下手机号的数量
     * starred              | 否     | int       | 是否收藏 是:1  否:0
     * contact_last_updated_timestamp   | 否     | int       | 联系人最后编辑时间 毫秒级时间戳
     *
     * @apiParamExample USER_CONTACTS_TELEPHONE 示例
     * {"user_id":"123","USER_CONTACTS_TELEPHONE":[{"id":3727589,"contact_fullname":"badsaa1765","contact_telephone":"8123458574","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254853},{"id":3727590,"contact_fullname":"badsaa1766","contact_telephone":"8123458575","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254857},{"id":3727591,"contact_fullname":"badsaa1767","contact_telephone":"8123458576","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254861},{"id":3727592,"contact_fullname":"badsaa1768","contact_telephone":"8123458577","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254865},{"id":3727593,"contact_fullname":"badsaa1769","contact_telephone":"8123458578","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254868},{"id":3727594,"contact_fullname":"badsaa1770","contact_telephone":"8123458579","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254872},{"id":3727595,"contact_fullname":"badsaa1771","contact_telephone":"8123458580","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254876},{"id":3727596,"contact_fullname":"badsaa1772","contact_telephone":"8123458581","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254879},{"id":3727597,"contact_fullname":"badsaa1773","contact_telephone":"8123458582","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254883},{"id":3727598,"contact_fullname":"badsaa1774","contact_telephone":"8123458583","relation_level":0,"created_at":"2020-03-16 16:52:44","times_contacted":0,"last_time_contacted":0,"has_phone_number":"1","starred":0,"contact_last_updated_timestamp":1578560254887}]}
     *
     * @apiParamExample USER_PHONE_HARDWARE
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录列ID
     * imei         | 是     | string    | Imei号
     * phone_type   | 是     | string    | 手机型号 COL-AL10、MI 8 Lite、MI 8、Redmi 4A 等
     * os_version| 否    | string    | 安卓版本 9、8.0.0、10 等
     * model  | 是     | string    | 设备型号
     * advertising_id         | 否     | string    | advertising_id号
     * phone_brand  | 否     | string    | 手机品牌 Xiaomi、HONOR
     * ext_info     | 否     | string    | 手机硬件扩展信息 json字符串。[uuid,mac地址,ip地址,androidId]  {"uuid":"2369ea50d4e719fdee088da3a2193f9e","addressMac":"02:00:00:00:00:00","addressIP":"","androidId":"dc63dbb4f7f4663d"}
     * created_at   | 是     | date      | 记录添加时间
     * total_rom   | 是     | string    | 内存空间总大小。例：'53859.38 MB'
     * used_rom    | 是     | string    | 已使用内存空间 20142.05 MB
     * free_rom    | 是     | string    | 未使用内存空间 33717.34 MB
     * is_agent  | 否     | int       | 是否使用vpn  0:未使用  1:使用
     * is_wifi_proxy| 否     | int       | 是否使用wifi 0:未使用  1:使用
     * pixels       | 否     | string    | 手机摄像头信息
     * native_phone| 否     | string    | SIM卡手机号
     * net_type     | 否     | string    | 网络类型，上传相关字段信息：如4G，3G，wifi等
     * net_type_original    | 否     | string        | 原始网络类型
     * is_double_sim| 否     | int       | 是否双卡双待 0:否 1:是
     * wifi_ip  | 否     | string    | wifi ip 地址
     *
     * @apiParamExample USER_PHONE_HARDWARE 示例
     * {"user_id":"123","USER_PHONE_HARDWARE":[{"id":2174,"imei":"e6edda542d8682e8","phone_type":"Redmi 4A","os_version":"6.0.1","model":"23","advertising_id":"404450986117732","phone_brand":"Xiaomi","ext_info":"{\"uuid\":\"a0f8c4451bf058c217c18bc9d7edc0f7\",\"addressMac\":\"02:00:00:00:00:00\",\"addressIP\":\"\",\"androidId\":\"e6edda542d8682e8\"}","created_at":"2020-03-16 15:10:03","total_rom":" 9822.28 MB","used_rom":" 8986.44 MB","free_rom":" 835.84 MB","is_agent":"0","is_wifi_proxy":"1","pixels":"[{\"camer_height\":\"3120\",\"camer_width\":\"4160\"},{\"camer_height\":\"1944\",\"camer_width\":\"2592\"}]","native_phone":"","net_type":"wifi","net_type_original":"wifi","is_double_sim":"0"},{"id":2179,"imei":"2c2227b6a25baba0","phone_type":"MI 8 Lite","os_version":"9","model":"28","advertising_id":"89860055011990017727","phone_brand":"Xiaomi","ext_info":"{\"uuid\":\"b9520be5d276e4030da6b4cfa81a9858\",\"addressMac\":\"02:00:00:00:00:00\",\"addressIP\":\"\",\"androidId\":\"2c2227b6a25baba0\"}","created_at":"2020-03-16 17:09:56","total_rom":" 50323.60 MB","used_rom":" 48380.35 MB","free_rom":" 1943.25 MB","is_agent":"0","is_wifi_proxy":"0","pixels":"[{\"camer_height\":\"3024\",\"camer_width\":\"4032\"},{\"camer_height\":\"4312\",\"camer_width\":\"5760\"}]","native_phone":"3552791945","net_type":"wifi","net_type_original":"wifi","is_double_sim":"0"},{"id":2220,"imei":"2c2227b6a25baba0","phone_type":"MI 8 Lite","os_version":"9","model":"28","advertising_id":"89860055011990017727","phone_brand":"Xiaomi","ext_info":"{\"uuid\":\"b9520be5d276e4030da6b4cfa81a9858\",\"addressMac\":\"02:00:00:00:00:00\",\"addressIP\":\"\",\"androidId\":\"2c2227b6a25baba0\"}","created_at":"2020-03-24 17:30:26","total_rom":" 50323.60 MB","used_rom":" 47484.80 MB","free_rom":" 2838.80 MB","is_agent":"0","is_wifi_proxy":"0","pixels":"[{\"camer_height\":\"3024\",\"camer_width\":\"4032\"},{\"camer_height\":\"4312\",\"camer_width\":\"5760\"}]","native_phone":"3552791945","net_type":"wifi","net_type_original":"wifi","is_double_sim":"0"}]}
     *
     * @apiParamExample USER_PHONE_PHOTO
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录列ID
     * storage      | 是     | string    | 存储位置  /storage/emulated/0/Pictures/Screenshots/20190220-101100.jpg
     * longitude    | 是     | numeric   | 经度 113.198768972222
     * latitude     | 是     | numeric   | 维度 23.416796000000
     * length       | 是     | string    | 照片长度 2280
     * width        | 否     | string    | 照片宽度 1080
     * province     | 否     | string    | 州
     * city         | 否     | string    | 城市
     * district     | 否     | string    | 县/区
     * street       | 否     | string    | 街道
     * address      | 否     | string    | 详细地址
     * photo_created_time   | 是     | date      | 拍照时间
     * all_data     | 是     | string       | 所有相册信息 {"date_time":"2018:02:04 22:33:55","image_length":"3456","latitude":"23.416796","name":"\/storage\/emulated\/0\/UCMobile\/Temp\/15177548191270.4994811770850389.jpg","image_width":"4608","longitude":"113.19876897222223"}
     * created_at   | 否     | date      | 记录列ID
     *
     * @apiParamExample USER_PHONE_PHOTO 示例
     * {"user_id":"123","USER_PHONE_PHOTO":[{"id":1967,"storage":"\/storage\/emulated\/0\/DCIM\/Camera\/IMG_20200102_105418.jpg","longitude":0,"latitude":0,"length":"4160","width":"3120","province":"","city":"","district":"","street":"","address":"","photo_created_time":"2020-01-02 10:54:18","all_data":"{\"latitude\":\"0.0\",\"name\":\"\\\/storage\\\/emulated\\\/0\\\/DCIM\\\/Camera\\\/IMG_20200102_105418.jpg\",\"image_length\":\"4160\",\"date_time\":\"2020:01:02 10:54:18\",\"longitude\":\"0.0\",\"image_width\":\"3120\"}","created_at":"2020-03-16 15:09:55"},{"id":1968,"storage":"\/storage\/emulated\/0\/DCIM\/Camera\/IMG_20200102_105424.jpg","longitude":121.476984,"latitude":31.232803,"length":"4160","width":"3120","province":"\u4e0a\u6d77\u5e02","city":"\u4e0a\u6d77\u5e02","district":"\u9ec4\u6d66\u533a","street":"\u5ef6\u5b89\u4e1c\u8def","address":"\u4e0a\u6d77\u5e02\u9ec4\u6d66\u533a\u5ef6\u5b89\u4e1c\u8def550\u53f726f\u5916\u6ee9,\u4eba\u6c11\u5e7f\u573a,\u5357\u4eac\u4e1c\u8def","photo_created_time":"2020-01-02 10:54:24","all_data":"{\"latitude\":\"31.232802999999997\",\"name\":\"\\\/storage\\\/emulated\\\/0\\\/DCIM\\\/Camera\\\/IMG_20200102_105424.jpg\",\"image_length\":\"4160\",\"date_time\":\"2020:01:02 10:54:24\",\"longitude\":\"121.476984\",\"image_width\":\"3120\"}","created_at":"2020-03-16 15:09:55"},{"id":1969,"storage":"\/storage\/emulated\/0\/DCIM\/Camera\/IMG_20200102_113707.jpg","longitude":121.477056,"latitude":31.232777972222,"length":"4160","width":"3120","province":"\u4e0a\u6d77\u5e02","city":"\u4e0a\u6d77\u5e02","district":"\u9ec4\u6d66\u533a","street":"\u5ef6\u5b89\u4e1c\u8def","address":"\u4e0a\u6d77\u5e02\u9ec4\u6d66\u533a\u5ef6\u5b89\u4e1c\u8def550\u53f7\u5916\u6ee9,\u4eba\u6c11\u5e7f\u573a,\u5357\u4eac\u4e1c\u8def","photo_created_time":"2020-01-02 11:37:06","all_data":"{\"latitude\":\"31.23277797222222\",\"name\":\"\\\/storage\\\/emulated\\\/0\\\/DCIM\\\/Camera\\\/IMG_20200102_113707.jpg\",\"image_length\":\"4160\",\"date_time\":\"2020:01:02 11:37:06\",\"longitude\":\"121.477056\",\"image_width\":\"3120\"}","created_at":"2020-03-16 15:09:55"}]}
     *
     * @apiParamExample USER_POSITION
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录列ID
     * longitude    | 是     | numeric   | 经度 116.235416000000
     * latitude     | 是     | numeric   | 纬度 40.047052000000
     * created_at   | 是     | date      | 记录创建时间 Y-m-d H:i:s
     * province     | 否     | string    | 所在州
     * city         | 否     | string    | 城市
     * district     | 否     | string    | 县/区
     * street       | 否     | string    | 街道
     * address      | 否     | string    | 详细地址
     *
     * @apiParamExample USER_POSITION 示例
     * {"user_id":"123","USER_POSITION":[{"id":4907,"longitude":116.235325,"latitude":40.047018,"province":"\u5317\u4eac\u5e02","city":"\u5317\u4eac\u5e02","district":"\u6d77\u6dc0\u533a","street":"\u4eae\u7532\u5e97\u8def","address":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u4eae\u7532\u5e97\u8def\u897f\u5317\u65fa","created_at":"2020-03-27 08:13:29"}]}
     *
     * @apiParamExample USER_THIRD_DATA
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录列ID
     * type         | 是     | string    | 类型 aadhaarCard号码第三方校验得到的手机号：aadhaarCardTelephoneCheck  aadhaarCard号码第三方校验得到的年龄范围：aadhaarCardAgeCheck  panCard号码第三方验证得到的名字：pancardNameCheck  panCardOCR识别得到的名字：pancardOcrVerfiyNameCheck   人脸识别分数：faceComparison
     * channel      | 否     | string    | 渠道
     * result       | 是     | string    | 原数据json
     * value        | 是     | string    | 具体值。不同类型值举例 aadhaarCardTelephoneCheck：xxxxxxx280  aadhaarCardAgeCheck：20-30  pancardNameCheck：YESHPAL SHARMA  pancardOcrVerfiyNameCheck：YESHPAL SHARMA   faceComparison(0-1的数字,识别度)：0.96
     * status       | 是     | int       | 记录状态 -1:失效 1:有效
     * created_at   | 是     | date      | 记录创建时间 Y-m-d H:i:s
     * updated_at   | 是     | date      | 最后修改时间 Y-m-d H:i:s
     *
     * @apiParamExample USER_THIRD_DATA 示例
     * {"user_id":"123","USER_THIRD_DATA":[{"id":2179544,"type":"pancardNameCheck","channel":"","result":"{\"auth_name\":\"SAROJ NAIK\",\"fullname\":\"Saroj Naik\",\"identical\":true}","created_at":"2020-03-17 15:54:20","updated_at":"2020-03-17 15:54:20","status":1,"value":"SAROJ NAIK"},{"id":2179545,"type":"pancardOcrVerfiyNameCheck","channel":"","result":"{\"verfiy_name\":\"SAROJ NAIK\",\"ocr_name\":\"SAROJ NAIK\",\"identical\":true}","created_at":"2020-03-17 15:54:20","updated_at":"2020-03-17 15:54:20","status":1,"value":"SAROJ NAIK"},{"id":2179549,"type":"faceComparison","channel":"Services","result":"{\"identical\":true,\"score\":0.957,\"aadhaar_upload_id\":683996,\"aadhaar_upload_path\":\"data\\\/saas\\\/prod\\\/aadhaar\\\/20200317\\\/210442_155305.jpeg.jepg\",\"face_upload_id\":683983,\"face_upload_path\":\"data\\\/saas\\\/prod\\\/face\\\/20200317\\\/210442_155131.jepg\"}","created_at":"2020-03-17 15:54:21","updated_at":"2020-03-17 15:54:21","status":1,"value":0.957},{"id":2525489,"type":"aadhaarCardTelephoneCheck","channel":"","result":"{\"user_telephone\":\"8239317231\",\"aadhaar_telephone\":\"xxxxxxx231\",\"identical\":true}","created_at":"2020-04-02 13:03:42","updated_at":"2020-04-02 13:03:42","status":1,"value":"xxxxxxx231"}]}
     *
     * @apiParamExample USER_SMS
     * 二维数组列字段
     * 字段         | 必填    | 类型      | 描述
     * id           | 是     | int       | 记录列ID
     * sms_telephone| 是     | string    | 短信【发送|接收】电话
     * sms_centent  | 否     | string    | 短信内容
     * sms_date     | 否     | string    | 短信【发送|接收】的时间
     * type         | 否     | string    | 类型 1:接收 2:发送
     * created_at   | 否     | date      | 记录添加时间 Y-m-d H:i:s
     *
     * @apiParamExample USER_SMS 示例
     * {"user_id":"123","task_no":"TASK_BCACAE4F2B13AC70","USER_SMS":[{"id":240,"sms_telephone":"51462","sms_centent":"<#>  039901  is your OTP for MiCredit login. It is valid for 5 minutes. Please don't share it with anyone.","sms_date":"2020-02-07 16:05:38","type":1,"created_at":"2020-03-31 08:07:01"},{"id":181,"sms_telephone":"51462","sms_centent":"xx code 655065","sms_date":"2020-02-19 16:22:13","type":1,"created_at":"2020-03-31 08:07:01"},{"id":187,"sms_telephone":"57575353","sms_centent":"G-896518 \u662f\u60a8\u7684 Google \u9a8c\u8bc1\u7801\u3002","sms_date":"2020-02-18 18:23:11","type":1,"created_at":"2020-03-31 08:07:01"}]}
     *
     * @apiSuccessExample {json} 成功返回:
     * {
     * "code":18000,
     * "msg":"ok",
     * "data":{
     * "BANK_CARD":"SUCCESS"
     * }
     * }
     */
}
