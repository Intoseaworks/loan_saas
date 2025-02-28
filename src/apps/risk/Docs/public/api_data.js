define({
    "api": [
        {
            "type": "post",
            "url": "/api/risk/task/exec_task",
            "title": "执行机审任务",
            "group": "Api",
            "version": "1.0.0",
            "description": "<p>执行机审任务。若还有机审任务对应的用户数据上传未完善，会返回status=CREATE的状态。成功发起执行则会返回status=WAITING状态，需等待机审结果回调。</p>",
            "parameter": {
                "fields": {
                    "Parameter": [
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "task_no",
                            "description": "<p>机审任务编号。注意上传数据的 [ORDER] 和 [USER] 数据必须包含创建任务时的ID对应的记录</p>"
                        }
                    ]
                }
            },
            "success": {
                "fields": {
                    "返回值": [
                        {
                            "group": "返回值",
                            "type": "int",
                            "optional": false,
                            "field": "status",
                            "description": "<p>请求状态 18000:成功 13000:失败 <strong>全局状态</strong></p>"
                        },
                        {
                            "group": "返回值",
                            "type": "String",
                            "optional": false,
                            "field": "msg",
                            "description": "<p>信息</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "Array",
                            "optional": false,
                            "field": "data",
                            "description": "<p>返回数据</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "Array",
                            "optional": false,
                            "field": "data.status",
                            "description": "<p>状态。若还有机审任务对应的用户数据上传未完善，会返回 CREATE 的状态。成功发起执行则会返回 WAITING 状态，需等待机审结果回调。</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "Array",
                            "optional": false,
                            "field": "data.required",
                            "description": "<p>机审所需数据项</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "Array",
                            "optional": false,
                            "field": "data.required.--",
                            "description": "<p>机审所需数据项</p>"
                        }
                    ]
                },
                "examples": [
                    {
                        "title": "数据上传未完善:",
                        "content": "{\n\"code\":18000,\n\"msg\":\"required data is incomplete\",\n\"data\":{\n\"status\":\"CREATE\",\n\"required\":[\n\"COLLECTION_RECORD\",\n\"ORDER\",\n\"ORDER_DETAIL\",\n\"REPAYMENT_PLAN\",\n\"USER\",\n\"USER_AUTH\",\n\"USER_CONTACT\",\n\"USER_INFO\",\n\"USER_THIRD_DATA\"\n]\n}\n}",
                        "type": "json"
                    },
                    {
                        "title": "成功发起执行:",
                        "content": "{\"code\":18000,\"msg\":\"ok\",\"data\":{\"status\":\"WAITING\"}}",
                        "type": "json"
                    }
                ]
            },
            "filename": "docs/apidoc/Api/ApiDoc.php",
            "groupTitle": "Api",
            "name": "PostApiRiskTaskExec_task",
            "sampleRequest": [
                {
                    "url": "http://services.dev.indiaox.in/api/risk/task/exec_task"
                }
            ]
        },
        {
            "type": "post",
            "url": "/api/risk/task/start_task",
            "title": "创建机审任务",
            "group": "Api",
            "version": "1.0.0",
            "description": "<p>创建机审任务</p>",
            "parameter": {
                "fields": {
                    "Parameter": [
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "user_id",
                            "description": "<p>用户唯一标识</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "order_id",
                            "description": "<p>申请订单ID</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "notice_url",
                            "description": "<p>机审结果回调通知地址</p>"
                        }
                    ]
                }
            },
            "success": {
                "fields": {
                    "返回值": [
                        {
                            "group": "返回值",
                            "type": "int",
                            "optional": false,
                            "field": "status",
                            "description": "<p>请求状态 18000:成功 13000:失败 <strong>全局状态</strong></p>"
                        },
                        {
                            "group": "返回值",
                            "type": "String",
                            "optional": false,
                            "field": "msg",
                            "description": "<p>信息</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "Array",
                            "optional": false,
                            "field": "data",
                            "description": "<p>返回数据</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "String",
                            "optional": false,
                            "field": "data.taskNo",
                            "description": "<p>机审任务编号</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "Array",
                            "optional": false,
                            "field": "data.required",
                            "description": "<p>机审所需数据项</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "String",
                            "optional": false,
                            "field": "data.required.--",
                            "description": "<p>机审所需数据项</p>"
                        }
                    ]
                },
                "examples": [
                    {
                        "title": "成功返回:",
                        "content": "{\n\"code\":18000,\n\"msg\":\"ok\",\n\"data\":{\n\"taskNo\":\"TASK_D523085A3FA5C2B7\",\n\"required\":[\n\"BANK_CARD\",\n\"COLLECTION_RECORD\",\n\"ORDER\",\n\"ORDER_DETAIL\",\n\"REPAYMENT_PLAN\",\n\"USER\",\n\"USER_AUTH\",\n\"USER_CONTACT\",\n\"USER_INFO\",\n\"USER_THIRD_DATA\",\n\"USER_WORK\",\n\"USER_APPLICATION\",\n\"USER_CONTACTS_TELEPHONE\",\n\"USER_PHONE_HARDWARE\",\n\"USER_PHONE_PHOTO\",\n\"USER_POSITION\"\n]\n}\n}",
                        "type": "json"
                    }
                ]
            },
            "filename": "docs/apidoc/Api/ApiDoc.php",
            "groupTitle": "Api",
            "name": "PostApiRiskTaskStart_task",
            "sampleRequest": [
                {
                    "url": "http://services.dev.indiaox.in/api/risk/task/start_task"
                }
            ]
        },
        {
            "type": "post",
            "url": "创建机审时上传的notice_url",
            "title": "机审结果回调",
            "group": "Callback",
            "version": "1.0.0",
            "description": "<p>机审结果回调。接口返回'SUCCESS'表示回调处理成功。若无接收到'SUCCESS'，系统会再次回调，回调6次后不再处理回调</p>",
            "parameter": {
                "fields": {
                    "Parameter": [
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "task_no",
                            "description": "<p>task_no</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "order_id",
                            "description": "<p>order_id</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "status",
                            "description": "<p>机审状态  完成:FINISH  异常:EXCEPTION</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "result",
                            "description": "<p>机审结果  通过:PASS  拒绝:REJECT  异常:NA</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "hit_rule_code",
                            "description": "<p>机审命中项，多项以','分隔  10009,10012,80002</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "task_desc",
                            "description": "<p>备注信息</p>"
                        }
                    ]
                }
            },
            "success": {
                "examples": [
                    {
                        "title": "返回值：",
                        "content": "SUCCESS",
                        "type": "json"
                    }
                ]
            },
            "error": {
                "examples": [
                    {
                        "title": "通过回调:",
                        "content": "{\"hit_rule_code\":\"100000\",\"order_id\":\"125915\",\"result\":\"REJECT\",\"status\":\"FINISH\",\"task_desc\":\"\\u89c4\\u5219\\u672a\\u901a\\u8fc7\",\"task_no\":\"TASK_F3C80626BFCB6AEE\"}",
                        "type": "json"
                    },
                    {
                        "title": "拒绝回调:",
                        "content": "{\"hit_rule_code\":\"\",\"order_id\":\"88995955\",\"result\":\"PASS\",\"status\":\"FINISH\",\"task_desc\":\"\\u89c4\\u5219\\u901a\\u8fc7\",\"task_no\":\"TASK_663EA8F14639F36D\"}",
                        "type": "json"
                    },
                    {
                        "title": "rule_code",
                        "content": "1XXXX 准入拒绝\n2XXXX 设备不符合\n3XXXX 申请异常\n4XXXX 通讯录不符合要求\n5XXXX 历史表现不良\n6XXXX 其他\n8XXXX|9XXXX 第三方数据不合格",
                        "type": "json"
                    }
                ]
            },
            "filename": "docs/apidoc/Api/CallbackDoc.php",
            "groupTitle": "Callback",
            "name": "PostNotice_url"
        },
        {
            "type": "post",
            "url": "/api/risk/send_data/all",
            "title": "上传用户风控数据",
            "group": "SendData",
            "version": "1.0.0",
            "description": "<p>上传用户风控数据 <br/> 注意资料项的可选属性只表示接口单次上传可省略，支持多次上传，按每项资料项下的唯一记录ID去重。<br/> 具体针对审批任务的必填数据项项需看[创建机审任务]处返回的必须数据项</p>",
            "parameter": {
                "fields": {
                    "Parameter": [
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": false,
                            "field": "user_id",
                            "description": "<p>用户唯一标识</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "String",
                            "optional": true,
                            "field": "task_no",
                            "description": "<p>机审任务编号，非必填。若存在编号，则会完成机审任务对应的数据上传。若不存在编号，则为普通上传，不会关联机审任务</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "BANK_CARD",
                            "description": "<p>用户银行卡数据列表</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "COLLECTION_RECORD",
                            "description": "<p>用户催收记录列表</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "ORDER",
                            "description": "<p>用户所有订单记录列表</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "ORDER_DETAIL",
                            "description": "<p>用户所有订单详情数据列表</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "REPAYMENT_PLAN",
                            "description": "<p>用户所有还款计划列表</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER",
                            "description": "<p>用户信息单项</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_CONTACT",
                            "description": "<p>用户紧急联系人信息</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_INFO",
                            "description": "<p>用户详情</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_WORK",
                            "description": "<p>用户工作信息</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_AUTH",
                            "description": "<p>用户认证数据</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_THIRD_DATA",
                            "description": "<p>用户第三方数据</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_APPLICATION",
                            "description": "<p>用户app应用列表</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_CONTACTS_TELEPHONE",
                            "description": "<p>通讯录列表</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_PHONE_HARDWARE",
                            "description": "<p>手机硬件信息</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_PHONE_PHOTO",
                            "description": "<p>相册信息</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_POSITION",
                            "description": "<p>位置信息</p>"
                        },
                        {
                            "group": "Parameter",
                            "type": "Array",
                            "optional": true,
                            "field": "USER_SMS",
                            "description": "<p>短信列表</p>"
                        }
                    ]
                },
                "examples": [
                    {
                        "title": "BANK_CARD",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 业务系统银行卡ID，如记录自增id\nno           | 是     | string    | 银行卡号\nname         | 是     | string    | 用户姓名\nstatus       | 是     | string    | 状态。 1:正常  0:废弃   至少需要一条状态为 1 的银行卡记录\ncreated_at   | 是     | date      | 创建时间 Y-m-d H:i:s\nifsc         | 是     | string    | ifsc\nbank_name    | 是     | string    | 银行名\nbank_branch_name | 否 | string    | 支行名称\ncity         | 否     | string    | 城市\nprovince     | 否     | string    | 州\nupdated_at   | 否     | date      | 记录最后修改时间 Y-m-d H:i:s",
                        "type": "json"
                    },
                    {
                        "title": "BANK_CARD 示例",
                        "content": "{\"user_id\":\"123\",\"BANK_CARD\":[{\"id\":915,\"no\":\"50100009665390\",\"name\":\"Bhushan Dilip Savale\",\"bank_name\":\"HDFC BANK\",\"status\":1,\"created_at\":\"2020-01-23 10:42:44\",\"ifsc\":\"HDFC0001293\"},{\"id\":804,\"no\":\"31657456705\",\"name\":\"Bhushan Dilip Savale\",\"bank_name\":\"STATE BANK OF INDIA\",\"status\":-2,\"created_at\":\"2019-11-12 06:54:15\",\"ifsc\":\"SBIN0000441\"},{\"id\":490,\"no\":\"024301079497\",\"name\":\"SAURAV  RAJORE\",\"bank_name\":\"ICICI BANK LIMITED\",\"status\":0,\"created_at\":\"2019-08-22 08:02:15\",\"ifsc\":\"ICIC0000243\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "COLLECTION_RECORD",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 业务系统催收记录ID，如记录自增id\norder_id     | 是     | int       | 订单ID\nfullname     | 是     | string    | 被催收人姓名\nrelation     | 是     | string    | 被催收人与借款人关系 父亲:FATHER 母亲:MOTHER  兄弟:BROTHERS  姐妹:SISTERS  儿子:SON  女儿:DAUGHTER  妻子:WIFE  丈夫:HUSBAND  其他:OTHER\ncontact      | 是     | string    | 被催收电话\ndial         | 是     | string    | 联系情况  正常联系:normal_contact  无法联系:unable_contact\nprogress     | 是     | string    | 联系情况  承诺还款:committed_repayment  本人无意还款:self_inadvertently_repay  有意告知:intentional_notification  有意帮还:intentional_help  无意向:unintentional  过多承诺跳票:too_many_promises  停机空号:shut_down_no  关机:shutdown  拒绝接听:refusal_to_answer  忙线:busy_line  无效音:invalid_sound  来电提醒:call_reminder  暂时无人接通:temporary_nobody_connection  反复无人接听:repeatedly_unanswered  反复接听挂断:repeatedly_hang_up\ncreated_at   | 是     | date      | 催收时间 Y-m-d H:i:s\noverdue_days | 是     | int       | 催收时逾期天数\nupdated_at   | 否     | date      | 记录最后修改时间 Y-m-d H:i:s\npromise_paid_time | 否     | date     | 承诺还款时间 合法时间格式。如：Y-m-d H:i:s 或 Y-m-d 等\nreduction_fee| 否     | numeric   | 当前减免金额\nreceivable_amount | 否    | numeric   | 当前应还金额\nreceivable_amount | 否    | string    | 备注\nadmin_id     | 否    | string    | 备注",
                        "type": "json"
                    },
                    {
                        "title": "COLLECTION_RECORD 示例",
                        "content": "{\"user_id\":\"123\",\"COLLECTION_RECORD\":[{\"id\":104,\"order_id\":638,\"fullname\":\"Bhushan Dilip Savale\",\"relation\":\"oneself\",\"contact\":\"9999988888\",\"dial\":\"normal_contact\",\"progress\":\"self_inadvertently_repay\",\"created_at\":\"2019-12-11 09:28:24\",\"overdue_days\":75},{\"id\":106,\"order_id\":638,\"fullname\":\"test\",\"relation\":\"MOTHER\",\"contact\":\"9999988887\",\"dial\":\"normal_contact\",\"progress\":\"intentional_notification\",\"created_at\":\"2019-12-20 14:12:14\",\"overdue_days\":84}]}",
                        "type": "json"
                    },
                    {
                        "title": "ORDER",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 订单唯一ID,由数字组成\norder_no     | 是     | string    | 订单业务编号\nprincipal    | 是     | numeric   | 借款金额\nloan_days    | 是     | int       | 借款天数\nstatus       | 是     | string    | 订单当前状态  创建:create  待审批:wait_system_approve   审批中:system_approving   审批通过:system_pass  已签约:sign  放款中:paying  出款成功待还款:system_paid   出款失败:system_pay_fail   取消借款:manual_cancel   已逾期:overdue   正常结清:finish   逾期结清:overdue_finish   已坏账:collection_bad\ncreated_at   | 是     | date      | 订单创建时间 Y-m-d H:i:s\nquality      | 是     | int       | 新老用户类型 新用户:0  复借用户:1\ndaily_rate   | 是     | numeric   | 日利率 小数。如0.09% 需传 0.0009\noverdue_rate | 是     | numeric   | 逾期日利率 小数。 如0.09% 需传 0.0009\noverdue_rate | 是     | numeric   | 逾期日利率 小数。 如0.09% 需传 0.0009\napp_client   | 否     | string    | 订单创建客户端  android  ios  h5\nupdated_at   | 否     | date      | 最后修改时间 Y-m-d H:i:s\nsigned_time  | 否     | date      | 签约时间 Y-m-d H:i:s\nsystem_time  | 否     | date      | 审批时间 Y-m-d H:i:s\npaid_time    | 否     | date      | 放款时间 Y-m-d H:i:s\npaid_amount  | 否     | numeric   | 支付金额\npay_channel  | 否     | string    | 支付渠道\ncancel_time  | 否     | date      | 取消时间 Y-m-d H:i:s\noverdue_time | 否     | date      | 进入逾期的时间 Y-m-d H:i:s\nbad_time     | 否     | date      | 进入坏账的时间 Y-m-d H:i:s\npass_time    | 否     | date      | 审批通过时间 Y-m-d H:i:s\nreject_time  | 否     | date      | 审批拒绝时间 Y-m-d H:i:s\napp_version  | 否     | string    | 版本号. 业务版本号",
                        "type": "json"
                    },
                    {
                        "title": "ORDER 示例",
                        "content": "{\"user_id\":\"123\",\"ORDER\":[{\"id\":1071,\"order_no\":\"88539845E7C9373BC3\",\"principal\":\"5000.00\",\"loan_days\":30,\"status\":\"finish\",\"created_at\":\"2020-03-26 17:05:15\",\"updated_at\":\"2020-03-26 18:12:11\",\"app_client\":\"android\",\"app_version\":\"1.0.8\",\"quality\":0,\"daily_rate\":\"0.00100\",\"overdue_rate\":\"0.00500\",\"signed_time\":\"2020-03-26 18:10:20\",\"system_time\":\"2020-03-26 17:07:06\",\"paid_time\":\"2020-03-26 18:10:55\",\"paid_amount\":\"4410.00\",\"pay_channel\":\"Manual Disburse\",\"cancel_time\":null,\"pass_time\":\"2020-03-26 18:05:02\",\"overdue_time\":null,\"bad_time\":null,\"reject_time\":null},{\"id\":1072,\"order_no\":\"88493335E7CA336099\",\"principal\":\"5000.00\",\"loan_days\":30,\"status\":\"sign\",\"created_at\":\"2020-03-26 18:12:30\",\"updated_at\":\"2020-03-28 12:40:06\",\"app_client\":\"android\",\"app_version\":\"1.0.8\",\"quality\":1,\"daily_rate\":\"0.00100\",\"overdue_rate\":\"0.00500\",\"signed_time\":\"2020-03-28 12:40:06\",\"system_time\":\"2020-03-26 18:15:09\",\"paid_time\":null,\"paid_amount\":null,\"pay_channel\":\"\",\"cancel_time\":null,\"pass_time\":\"2020-03-28 12:38:22\",\"overdue_time\":null,\"bad_time\":null,\"reject_time\":null}]}",
                        "type": "json"
                    },
                    {
                        "title": "ORDER_DETAIL",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 订单唯一ID,由数字组成\norder_id     | 是     | int       | 订单ID\nkey          | 是     | int       | 订单详情key 需传的订单信息key： imei  loan_position  loan_ip   bank_card_no\nvalue        | 是     | int       | 订单详情value  imei:订单创建时的设备imei  loan_position：订单提交时的经纬度,使用','分割，经度在前。如:113.949547,22.534047   loan_ip:提交订单时的ip   bank_card_no:绑定的放款银行卡号\ncreated_at   | 是     | int       | 创建时间",
                        "type": "json"
                    },
                    {
                        "title": "ORDER_DETAIL 示例",
                        "content": "{\"user_id\":\"123\",\"ORDER_DETAIL\":[{\"id\":6460,\"order_id\":1071,\"key\":\"loan_location\",\"value\":\"\\u5317\\u4eac\\u5e02\\u6d77\\u6dc0\\u533a\\u4eae\\u7532\\u5e97\\u8def\\u897f\\u5317\\u65fa\",\"created_at\":\"2020-03-26 17:05:15\"},{\"id\":6461,\"order_id\":1071,\"key\":\"loan_position\",\"value\":\"116.235094,40.047068\",\"created_at\":\"2020-03-26 17:05:15\"},{\"id\":6462,\"order_id\":1071,\"key\":\"loan_ip\",\"value\":\"221.218.141.61\",\"created_at\":\"2020-03-26 17:05:15\"},{\"id\":6466,\"order_id\":1071,\"key\":\"imei\",\"value\":\"dc63dbb4f7f4663d\",\"created_at\":\"2020-03-26 17:05:15\"},{\"id\":6470,\"order_id\":1071,\"key\":\"bank_card_no\",\"value\":\"50100009665390\",\"created_at\":\"2020-03-26 18:10:20\"},{\"id\":6472,\"order_id\":1072,\"key\":\"loan_location\",\"value\":\"\\u5317\\u4eac\\u5e02\\u6d77\\u6dc0\\u533a\\u4eae\\u7532\\u5e97\\u8def\\u897f\\u5317\\u65fa\",\"created_at\":\"2020-03-26 18:12:30\"},{\"id\":6473,\"order_id\":1072,\"key\":\"loan_position\",\"value\":\"116.235297,40.047027\",\"created_at\":\"2020-03-26 18:12:30\"},{\"id\":6474,\"order_id\":1072,\"key\":\"loan_ip\",\"value\":\"123.51.194.13\",\"created_at\":\"2020-03-26 18:12:30\"},{\"id\":6478,\"order_id\":1072,\"key\":\"imei\",\"value\":\"dc63dbb4f7f4663d\",\"created_at\":\"2020-03-26 18:12:30\"},{\"id\":6585,\"order_id\":1072,\"key\":\"bank_card_no\",\"value\":\"50100009665390\",\"created_at\":\"2020-03-28 12:40:06\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "REPAYMENT_PLAN",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录ID\norder_id     | 是     | int       | 订单ID\nstatus       | 是     | int       | 还款计划状态。未完成:0  已完成:1\noverdue_days | 是     | int       | 当前逾期天数 大于等于0的整数。未逾期填0\nappointment_paid_time| 是     | date      | 应还款时间 Y-m-d H:i:s\ncreated_at   | 是     | date      | 还款计划创建时间 Y-m-d H:i:s\nupdated_at   | 是     | date      | 还款计划最后修改时间 Y-m-d H:i:s\ninstallment_num      | 是     | int       | 还款计划期数。订单下唯一 1,2,3  只有一期填1即可\nrepay_proportion     | 是     | numeric   | 当期比例。当前还款计划占订单的百分比 0-100\nrepay_time   | 否     | date      | 实际还款时间 Y-m-d H:i:s\nrepay_amount | 否     | numeric   | 实际还款金额\nrepay_channel| 否     | string    | 还款渠道\nreduction_fee| 否     | numeric   | 减免金额\noverdue_fee  | 否     | numeric   | 逾期费用\nno           | 否     | string    | 还款计划编号",
                        "type": "json"
                    },
                    {
                        "title": "REPAYMENT_PLAN 示例",
                        "content": "{\"user_id\":\"123\",\"REPAYMENT_PLAN\":[{\"id\":617,\"order_id\":1046,\"status\":2,\"overdue_days\":17,\"appointment_paid_time\":\"2020-03-09 17:41:13\",\"created_at\":\"2020-03-24 17:38:47\",\"updated_at\":\"2020-03-26 12:29:09\",\"installment_num\":1,\"repay_proportion\":\"99.99\"},{\"id\":618,\"order_id\":1046,\"status\":3,\"overdue_days\":0,\"appointment_paid_time\":\"2020-04-08 17:41:13\",\"created_at\":\"2020-03-24 17:38:47\",\"updated_at\":\"2020-03-26 12:30:06\",\"installment_num\":2,\"repay_proportion\":\"0.01\"},{\"id\":655,\"order_id\":1068,\"status\":1,\"overdue_days\":11,\"appointment_paid_time\":\"2020-03-22 11:18:01\",\"created_at\":\"2020-03-27 08:28:04\",\"updated_at\":\"2020-04-02 00:05:04\",\"installment_num\":1,\"repay_proportion\":\"99.99\"},{\"id\":656,\"order_id\":1068,\"status\":1,\"overdue_days\":0,\"appointment_paid_time\":\"2020-04-21 11:18:01\",\"created_at\":\"2020-03-27 08:28:04\",\"updated_at\":\"2020-03-27 11:18:01\",\"installment_num\":2,\"repay_proportion\":\"0.01\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER",
                        "content": "一维数组列字段！\n字段         | 必填    | 类型      | 描述\ntelephone    | 是     | int       | 手机号\nfullname     | 是     | string    | 用户姓名\nid_card_no   | 是     | string    | 用户身份证号。pan card no\nstatus       | 是     | int       | 用户状态 正常:1 禁用:2 删除:-1\ncreated_at   | 是     | date      | 用户创建时间 Y-m-d H:i:s\nupdated_at   | 是     | date      | 最后修改时间 Y-m-d H:i:s\nclient_id    | 是     | string    | 用户注册设备类型 android  ios  h5\nquality      | 是     | int       | 用户类型 0:新用户 1:复贷用户\nquality_time | 否     | date      | 成为复贷用户时间",
                        "type": "json"
                    },
                    {
                        "title": "USER 示例",
                        "content": "{\"user_id\":\"123\",\"USER\":{\"telephone\":\"6356896580\",\"fullname\":\"DURGAKIRAN DUNABOINA\",\"id_card_no\":\"APWPD5178H\",\"status\":1,\"created_at\":\"2020-03-16 15:09:49\",\"updated_at\":\"2020-03-27 08:12:44\",\"client_id\":\"android\",\"quality\":1,\"quality_time\":\"2020-03-26 12:57:25\"}}",
                        "type": "json"
                    },
                    {
                        "title": "USER_CONTACT",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录ID\ncontact_fullname     | 是     | string    | 紧急联系人名字\ncontact_telephone    | 是     | string    | 紧急联系人电话\nrelation     | 是     | string    | 紧急联系人关系 FATHER MOTHER BROTHERS SISTERS SON DAUGHTER WIFE HUSBAND OTHER\nstatus       | 是     | string    |  状态 正常:1  弃用:0\ncreated_at   | 是     | date      |  记录创建时间\nupdated_at   | 是     | date      |  记录最后修改时间\ntimes_contacted      | 否     | date      |  联系次数\nlast_time_contacted  | 否     | date      |  最近联系时间 毫秒级时间戳\nlast_time_contacted  | 否     | int       |  最近联系时间 毫秒级时间戳\nstarred      | 否     | int       |  是否收藏 是:1  否:0\ncontact_last_updated_timestamp   | 否     | int       |  联系人最后编辑时间 毫秒级时间戳\nhas_phone_number     | 否     | int       |  手机联系人下手机号的数量",
                        "type": "json"
                    },
                    {
                        "title": "USER_CONTACT 示例",
                        "content": "{\"user_id\":\"123\",\"USER_CONTACT\":[{\"id\":1963,\"contact_fullname\":\"ndnjsjsjs\",\"contact_telephone\":\"6364947869\",\"relation\":\"FATHER\",\"status\":1,\"created_at\":\"2020-03-16 17:02:58\",\"updated_at\":\"2020-03-16 17:02:58\",\"times_contacted\":2,\"last_time_contacted\":1583936294400,\"has_phone_number\":\"1\",\"starred\":1,\"contact_last_updated_timestamp\":1583936359999},{\"id\":1964,\"contact_fullname\":\"dnnnsjsjs\",\"contact_telephone\":\"8123456789\",\"relation\":\"BROTHERS\",\"status\":1,\"created_at\":\"2020-03-16 17:02:58\",\"updated_at\":\"2020-03-16 17:02:58\",\"times_contacted\":2,\"last_time_contacted\":1583938454400,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1583978504513}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER_INFO",
                        "content": "一维数组列字段!\n字段             | 必填    | 类型      | 描述\nid               | 是     | int       | 记录ID\ngender           | 是     | string    | 性别 男:Male  女:Female\nprovince         | 是     | string    | 所在州(用户填写)\ncity             | 是     | string    | 所在城市(用户填写)\naddress          | 是     | string    | 地址(用户填写)\npincode          | 是     | string    | pincode(用户填写)\neducation_level      | 是     | string    | 教育程度 小学:Primary  初级中学学历:General Secondary  高级中学学历:Senior Secondary  本科:University Degree  研究生:Postgraduate  博士:Doctoral Degree  其他:other\nmarital_status       | 是     | string    | 婚姻状况  已婚:Married  未婚:Unmarried  离异:Divorce  丧偶:Widowhood\nbirthday         | 是     | date      | 生日 d/m/Y 格式\nfather_name      | 是     | string    | 父亲姓名\nresidence_type   | 是     | string    | 住宅类型  租住:RENTED  拥有:OWNED  跟家属住:WITH RELATIVES  公司宿舍:OFFICE PROVIDED\nreligion         | 是     | string    | 宗教  印度教:Hinduism  伊斯兰教:Islam  基督教:Christianity  锡克教:Sikhism  耆那教:Jainism  无宗教:No religion  其他:other\nlanguage         | 是     | string    | 语言  印度语:Hindi 英语:English  马拉地语:Marathi  孟加拉语:Bengali  泰卢固语:Telugu  泰米尔语:Tamil  古吉拉特语:Gujarati  卡纳达语:Kannada  乌尔都语:Urdu  马拉雅拉姆语:Malayalam  奥里亚:Odia  旁遮普语:Punjabi  阿萨姆语:Assamese\npan_card_no      | 是     | string    | pan card no\naadhaar_card_no  | 是     | string    | aadhaar card no\nemail            | 是     | email     | email\ncreated_at       | 是     | date      | 记录创建时间\nupdated_at       | 是     | date      | 最后修改时间\ninput_name       | 否     | string    | 用户输入的姓名\ninput_birthday   | 否     | string    | 用户输入生日\nchirdren_count   | 否     | numeric   | 子女数\npassport_no      | 否     | string    | 护照编号\nvoter_id_card_no | 否     | string    | 选民身份证\ndriving_license_no| 否    | string    | 驾驶证\npermanent_province| 否    | string    | 永久居住州(证件识别)\npermanent_city   | 否     | string    | 永久居住城市(证件识别)\npermanent_address| 否     | string    | 永久居住地址(证件识别)\npermanent_pincode| 否     | string    | 永久居住地址pincode(证件识别)\nperiod_of_resident   | 否     | string    | 常驻地居住时间  一年以下:less_than_a_year  一到两年:one_to_two_years   两到三年:two_to_three_years  三年以上:more_than_three_years",
                        "type": "json"
                    },
                    {
                        "title": "USER_INFO 示例",
                        "content": "{\"user_id\":\"123\",\"USER_INFO\":{\"id\":695,\"gender\":\"Male\",\"province\":\"TELANGANA\",\"city\":\"Hyderabad\",\"address\":\"bnnnbvc\",\"pincode\":\"500028\",\"permanent_province\":\"TELANGANA\",\"permanent_city\":\"Hyderabad\",\"permanent_address\":\"S\\/O Dunaboina Adinarayana, HNO 12-2- 709\\/C\\/19\\/3,1ST FLOOR,SAIRAM NILAYAM, KAROLBAGH SOCIETY, GUDIMALKAPUR, PADMANABHANAGAR,Asifnagac,P Dk, Telangana - 500028\",\"permanent_pincode\":\"500028\",\"expected_amount\":\"\",\"education_level\":\"Primary\",\"marital_status\":\"Married\",\"birthday\":\"04\\/04\\/1973\",\"father_name\":\"ADINARAYANA DUNABOINA\",\"residence_type\":\"WITH RELATIVES\",\"religion\":\"Hinduism\",\"language\":\"Assamese\",\"pan_card_no\":\"APWPD5178H\",\"aadhaar_card_no\":\"261726665659\",\"passport_no\":\"\",\"voter_id_card_no\":\"ZUC0267203\",\"driving_license_no\":\"\",\"email\":\"jjjnjho@163.com\",\"created_at\":\"2020-03-31 10:51:02\",\"updated_at\":\"2020-03-31 10:51:27\",\"input_name\":\"nskdkkdkd\",\"contacts_count\":2305,\"input_birthday\":\"04\\/04\\/1973\",\"chirdren_count\":\"1\"}}",
                        "type": "json"
                    },
                    {
                        "title": "USER_WORK",
                        "content": "一维数组列字段!\n字段             | 必填    | 类型      | 描述\nid               | 是     | int       | 记录ID\nemployment_type  | 是     | string    | 就业类型  全职:full-time salaried  兼职:part-time salaried  个体经营:self-employed  无业:no job  学生:student  其他:other\ncreated_at       | 是     | string    | 记录创建时间\nupdated_at       | 否     | string    | 最后修改时间\nprofession       | 否     | string    | 职业\nworkplace_pincode| 否     | string    | 工作地点pincode\ncompany          | 否     | string    | 公司名\nwork_address1    | 否     | string    | 工作地点1\nwork_address2    | 否     | string    | 工作地点2\nwork_positon     | 否     | string    | 工作位置\nsalary           | 否     | string    | 薪水范围  \"below and ₹15,000\", \"₹15,000-₹25,000\", \"₹25,000-₹35,000\", \"₹35,000-₹45,000\", \"₹45,000-₹55,000\", \"₹55,000 and above\"\nwork_email       | 否     | string    | 工作邮箱\nwork_start_date  | 否     | date    | 开始工作时间 格式 d/m/Y\nwork_phone       | 否     | string    | 工作电话",
                        "type": "json"
                    },
                    {
                        "title": "USER_WORK 示例",
                        "content": "{\"user_id\":\"123\",\"USER_WORK\":{\"id\":99,\"profession\":\"Bars\\/Pubs\",\"workplace_pincode\":\"125001\",\"company\":\"asd\",\"work_address1\":\"1\",\"work_address2\":\"\",\"work_positon\":\"\",\"salary\":\"\\u20b935,000-\\u20b945,000\",\"work_email\":\"\",\"work_start_date\":\"01\\/01\\/1981\",\"work_experience_years\":0,\"work_experience_months\":0,\"work_phone\":\"\",\"employment_type\":\"part-time salaried\",\"created_at\":\"2019-08-19 16:42:42\",\"updated_at\":\"2019-11-22 13:57:58\"}}",
                        "type": "json"
                    },
                    {
                        "title": "USER_AUTH",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录列ID\ntype         | 是     | string    | 认证项类型 基础信息(user_info完善状态):base_info\ntime         | 是     | date      | 认证时间 Y-m-d H:i:s\nstatus       | 是     | int       | 认证项状态 1:有效 0:失效\ncreated_at   | 是     | date      | 记录创建时间 Y-m-d H:i:s\nupdated_at   | 是     | date      | 记录修改时间 Y-m-d H:i:s",
                        "type": "json"
                    },
                    {
                        "title": "USER_AUTH 示例",
                        "content": "{\"user_id\":\"123\",\"USER_AUTH\":[{\"id\":2316,\"type\":\"base_info\",\"time\":\"2019-11-22 19:59:28\",\"status\":1,\"created_at\":\"2019-11-22 13:59:28\",\"updated_at\":\"2019-11-22 13:59:28\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER_APPLICATION",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 应用记录列ID\napp_name     | 是     | string    | app名称\npkg_name     | 是     | string    | 包名\ninstalled_time| 是    | int       | 应用安装时间 毫秒级时间戳\nupdated_time | 是     | int       | 应用更新时间 毫秒级时间戳\ncreated_at   | 是     | date      | 记录创建时间date",
                        "type": "json"
                    },
                    {
                        "title": "USER_APPLICATION 示例",
                        "content": "{\"user_id\":\"123\",\"USER_APPLICATION\":[{\"id\":906,\"app_name\":\"Boss123\",\"pkg_name\":\"com.hpbr.bosszhipin\",\"installed_time\":1550294204433,\"updated_time\":1568817430600,\"created_at\":\"2019-10-30 13:50:32\"},{\"id\":907,\"app_name\":\"IndiaOxDemo\",\"pkg_name\":\"com.cashfintech.cashnow\",\"installed_time\":1572423599472,\"updated_time\":1572423599472,\"created_at\":\"2019-10-30 13:50:32\"},{\"id\":908,\"app_name\":\"test\",\"pkg_name\":\"com.daimatest.gold\",\"installed_time\":1559104862162,\"updated_time\":1561262223326,\"created_at\":\"2019-10-30 13:50:32\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER_CONTACTS_TELEPHONE",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录列ID\ncontact_fullname | 是     | int       | 联系人姓名\ncontact_telephone| 是     | int       | 联系人手机号\nrelation_level   | 是     | int       | 联系人等级。暂默认为 0\ncreated_at       | 是     | date      | 记录创建时间\ntimes_contacted  | 否     | int       | 联系次数\nlast_time_contacted  | 否     | int       | 最近联系时间 毫秒级时间戳\nhas_phone_number     | 否     | int       | 手机联系人下手机号的数量\nstarred              | 否     | int       | 是否收藏 是:1  否:0\ncontact_last_updated_timestamp   | 否     | int       | 联系人最后编辑时间 毫秒级时间戳",
                        "type": "json"
                    },
                    {
                        "title": "USER_CONTACTS_TELEPHONE 示例",
                        "content": "{\"user_id\":\"123\",\"USER_CONTACTS_TELEPHONE\":[{\"id\":3727589,\"contact_fullname\":\"badsaa1765\",\"contact_telephone\":\"8123458574\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254853},{\"id\":3727590,\"contact_fullname\":\"badsaa1766\",\"contact_telephone\":\"8123458575\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254857},{\"id\":3727591,\"contact_fullname\":\"badsaa1767\",\"contact_telephone\":\"8123458576\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254861},{\"id\":3727592,\"contact_fullname\":\"badsaa1768\",\"contact_telephone\":\"8123458577\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254865},{\"id\":3727593,\"contact_fullname\":\"badsaa1769\",\"contact_telephone\":\"8123458578\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254868},{\"id\":3727594,\"contact_fullname\":\"badsaa1770\",\"contact_telephone\":\"8123458579\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254872},{\"id\":3727595,\"contact_fullname\":\"badsaa1771\",\"contact_telephone\":\"8123458580\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254876},{\"id\":3727596,\"contact_fullname\":\"badsaa1772\",\"contact_telephone\":\"8123458581\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254879},{\"id\":3727597,\"contact_fullname\":\"badsaa1773\",\"contact_telephone\":\"8123458582\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254883},{\"id\":3727598,\"contact_fullname\":\"badsaa1774\",\"contact_telephone\":\"8123458583\",\"relation_level\":0,\"created_at\":\"2020-03-16 16:52:44\",\"times_contacted\":0,\"last_time_contacted\":0,\"has_phone_number\":\"1\",\"starred\":0,\"contact_last_updated_timestamp\":1578560254887}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER_PHONE_HARDWARE",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录列ID\nimei         | 是     | string    | Imei号\nphone_type   | 是     | string    | 手机型号 COL-AL10、MI 8 Lite、MI 8、Redmi 4A 等\nsystem_version| 否    | string    | 安卓版本 9、8.0.0、10 等\nsystem_version_code  | 是     | string    | 版本号\nimsi         | 否     | string    | Imsi号\nphone_brand  | 否     | string    | 手机品牌 Xiaomi、HONOR\next_info     | 否     | string    | 手机硬件扩展信息 json字符串。[uuid,mac地址,ip地址,androidId]  {\"uuid\":\"2369ea50d4e719fdee088da3a2193f9e\",\"addressMac\":\"02:00:00:00:00:00\",\"addressIP\":\"\",\"androidId\":\"dc63dbb4f7f4663d\"}\ncreated_at   | 是     | date      | 记录添加时间\ntotal_disk   | 是     | string    | 内存空间总大小。例：'53859.38 MB'\nused_disk    | 是     | string    | 已使用内存空间 20142.05 MB\nfree_disk    | 是     | string    | 未使用内存空间 33717.34 MB\nis_vpn_used  | 否     | int       | 是否使用vpn  0:未使用  1:使用\nis_wifi_proxy| 否     | int       | 是否使用wifi 0:未使用  1:使用\npixels       | 否     | string    | 手机摄像头信息\nsim_phone_num| 否     | string    | SIM卡手机号\nnet_type     | 否     | string    | 网络类型，上传相关字段信息：如4G，3G，wifi等\nnet_type_original    | 否     | string        | 原始网络类型\nis_double_sim| 否     | int       | 是否双卡双待 0:否 1:是\nwifi_ip_address  | 否     | string    | wifi ip 地址",
                        "type": "json"
                    },
                    {
                        "title": "USER_PHONE_HARDWARE 示例",
                        "content": "{\"user_id\":\"123\",\"USER_PHONE_HARDWARE\":[{\"id\":2174,\"imei\":\"e6edda542d8682e8\",\"phone_type\":\"Redmi 4A\",\"system_version\":\"6.0.1\",\"system_version_code\":\"23\",\"imsi\":\"404450986117732\",\"phone_brand\":\"Xiaomi\",\"ext_info\":\"{\\\"uuid\\\":\\\"a0f8c4451bf058c217c18bc9d7edc0f7\\\",\\\"addressMac\\\":\\\"02:00:00:00:00:00\\\",\\\"addressIP\\\":\\\"\\\",\\\"androidId\\\":\\\"e6edda542d8682e8\\\"}\",\"created_at\":\"2020-03-16 15:10:03\",\"total_disk\":\" 9822.28 MB\",\"used_disk\":\" 8986.44 MB\",\"free_disk\":\" 835.84 MB\",\"is_vpn_used\":\"0\",\"is_wifi_proxy\":\"1\",\"pixels\":\"[{\\\"camer_height\\\":\\\"3120\\\",\\\"camer_width\\\":\\\"4160\\\"},{\\\"camer_height\\\":\\\"1944\\\",\\\"camer_width\\\":\\\"2592\\\"}]\",\"sim_phone_num\":\"\",\"net_type\":\"wifi\",\"net_type_original\":\"wifi\",\"is_double_sim\":\"0\"},{\"id\":2179,\"imei\":\"2c2227b6a25baba0\",\"phone_type\":\"MI 8 Lite\",\"system_version\":\"9\",\"system_version_code\":\"28\",\"imsi\":\"89860055011990017727\",\"phone_brand\":\"Xiaomi\",\"ext_info\":\"{\\\"uuid\\\":\\\"b9520be5d276e4030da6b4cfa81a9858\\\",\\\"addressMac\\\":\\\"02:00:00:00:00:00\\\",\\\"addressIP\\\":\\\"\\\",\\\"androidId\\\":\\\"2c2227b6a25baba0\\\"}\",\"created_at\":\"2020-03-16 17:09:56\",\"total_disk\":\" 50323.60 MB\",\"used_disk\":\" 48380.35 MB\",\"free_disk\":\" 1943.25 MB\",\"is_vpn_used\":\"0\",\"is_wifi_proxy\":\"0\",\"pixels\":\"[{\\\"camer_height\\\":\\\"3024\\\",\\\"camer_width\\\":\\\"4032\\\"},{\\\"camer_height\\\":\\\"4312\\\",\\\"camer_width\\\":\\\"5760\\\"}]\",\"sim_phone_num\":\"3552791945\",\"net_type\":\"wifi\",\"net_type_original\":\"wifi\",\"is_double_sim\":\"0\"},{\"id\":2220,\"imei\":\"2c2227b6a25baba0\",\"phone_type\":\"MI 8 Lite\",\"system_version\":\"9\",\"system_version_code\":\"28\",\"imsi\":\"89860055011990017727\",\"phone_brand\":\"Xiaomi\",\"ext_info\":\"{\\\"uuid\\\":\\\"b9520be5d276e4030da6b4cfa81a9858\\\",\\\"addressMac\\\":\\\"02:00:00:00:00:00\\\",\\\"addressIP\\\":\\\"\\\",\\\"androidId\\\":\\\"2c2227b6a25baba0\\\"}\",\"created_at\":\"2020-03-24 17:30:26\",\"total_disk\":\" 50323.60 MB\",\"used_disk\":\" 47484.80 MB\",\"free_disk\":\" 2838.80 MB\",\"is_vpn_used\":\"0\",\"is_wifi_proxy\":\"0\",\"pixels\":\"[{\\\"camer_height\\\":\\\"3024\\\",\\\"camer_width\\\":\\\"4032\\\"},{\\\"camer_height\\\":\\\"4312\\\",\\\"camer_width\\\":\\\"5760\\\"}]\",\"sim_phone_num\":\"3552791945\",\"net_type\":\"wifi\",\"net_type_original\":\"wifi\",\"is_double_sim\":\"0\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER_PHONE_PHOTO",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录列ID\nstorage      | 是     | string    | 存储位置  /storage/emulated/0/Pictures/Screenshots/20190220-101100.jpg\nlongitude    | 是     | numeric   | 经度 113.198768972222\nlatitude     | 是     | numeric   | 维度 23.416796000000\nlength       | 是     | string    | 照片长度 2280\nwidth        | 否     | string    | 照片宽度 1080\nprovince     | 否     | string    | 州\ncity         | 否     | string    | 城市\ndistrict     | 否     | string    | 县/区\nstreet       | 否     | string    | 街道\naddress      | 否     | string    | 详细地址\nphoto_created_time   | 是     | date      | 拍照时间\nall_data     | 是     | string       | 所有相册信息 {\"date_time\":\"2018:02:04 22:33:55\",\"image_length\":\"3456\",\"latitude\":\"23.416796\",\"name\":\"\\/storage\\/emulated\\/0\\/UCMobile\\/Temp\\/15177548191270.4994811770850389.jpg\",\"image_width\":\"4608\",\"longitude\":\"113.19876897222223\"}\ncreated_at   | 否     | date      | 记录列ID",
                        "type": "json"
                    },
                    {
                        "title": "USER_PHONE_PHOTO 示例",
                        "content": "{\"user_id\":\"123\",\"USER_PHONE_PHOTO\":[{\"id\":1967,\"storage\":\"\\/storage\\/emulated\\/0\\/DCIM\\/Camera\\/IMG_20200102_105418.jpg\",\"longitude\":0,\"latitude\":0,\"length\":\"4160\",\"width\":\"3120\",\"province\":\"\",\"city\":\"\",\"district\":\"\",\"street\":\"\",\"address\":\"\",\"photo_created_time\":\"2020-01-02 10:54:18\",\"all_data\":\"{\\\"latitude\\\":\\\"0.0\\\",\\\"name\\\":\\\"\\\\\\/storage\\\\\\/emulated\\\\\\/0\\\\\\/DCIM\\\\\\/Camera\\\\\\/IMG_20200102_105418.jpg\\\",\\\"image_length\\\":\\\"4160\\\",\\\"date_time\\\":\\\"2020:01:02 10:54:18\\\",\\\"longitude\\\":\\\"0.0\\\",\\\"image_width\\\":\\\"3120\\\"}\",\"created_at\":\"2020-03-16 15:09:55\"},{\"id\":1968,\"storage\":\"\\/storage\\/emulated\\/0\\/DCIM\\/Camera\\/IMG_20200102_105424.jpg\",\"longitude\":121.476984,\"latitude\":31.232803,\"length\":\"4160\",\"width\":\"3120\",\"province\":\"\\u4e0a\\u6d77\\u5e02\",\"city\":\"\\u4e0a\\u6d77\\u5e02\",\"district\":\"\\u9ec4\\u6d66\\u533a\",\"street\":\"\\u5ef6\\u5b89\\u4e1c\\u8def\",\"address\":\"\\u4e0a\\u6d77\\u5e02\\u9ec4\\u6d66\\u533a\\u5ef6\\u5b89\\u4e1c\\u8def550\\u53f726f\\u5916\\u6ee9,\\u4eba\\u6c11\\u5e7f\\u573a,\\u5357\\u4eac\\u4e1c\\u8def\",\"photo_created_time\":\"2020-01-02 10:54:24\",\"all_data\":\"{\\\"latitude\\\":\\\"31.232802999999997\\\",\\\"name\\\":\\\"\\\\\\/storage\\\\\\/emulated\\\\\\/0\\\\\\/DCIM\\\\\\/Camera\\\\\\/IMG_20200102_105424.jpg\\\",\\\"image_length\\\":\\\"4160\\\",\\\"date_time\\\":\\\"2020:01:02 10:54:24\\\",\\\"longitude\\\":\\\"121.476984\\\",\\\"image_width\\\":\\\"3120\\\"}\",\"created_at\":\"2020-03-16 15:09:55\"},{\"id\":1969,\"storage\":\"\\/storage\\/emulated\\/0\\/DCIM\\/Camera\\/IMG_20200102_113707.jpg\",\"longitude\":121.477056,\"latitude\":31.232777972222,\"length\":\"4160\",\"width\":\"3120\",\"province\":\"\\u4e0a\\u6d77\\u5e02\",\"city\":\"\\u4e0a\\u6d77\\u5e02\",\"district\":\"\\u9ec4\\u6d66\\u533a\",\"street\":\"\\u5ef6\\u5b89\\u4e1c\\u8def\",\"address\":\"\\u4e0a\\u6d77\\u5e02\\u9ec4\\u6d66\\u533a\\u5ef6\\u5b89\\u4e1c\\u8def550\\u53f7\\u5916\\u6ee9,\\u4eba\\u6c11\\u5e7f\\u573a,\\u5357\\u4eac\\u4e1c\\u8def\",\"photo_created_time\":\"2020-01-02 11:37:06\",\"all_data\":\"{\\\"latitude\\\":\\\"31.23277797222222\\\",\\\"name\\\":\\\"\\\\\\/storage\\\\\\/emulated\\\\\\/0\\\\\\/DCIM\\\\\\/Camera\\\\\\/IMG_20200102_113707.jpg\\\",\\\"image_length\\\":\\\"4160\\\",\\\"date_time\\\":\\\"2020:01:02 11:37:06\\\",\\\"longitude\\\":\\\"121.477056\\\",\\\"image_width\\\":\\\"3120\\\"}\",\"created_at\":\"2020-03-16 15:09:55\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER_POSITION",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录列ID\nlongitude    | 是     | numeric   | 经度 116.235416000000\nlatitude     | 是     | numeric   | 纬度 40.047052000000\ncreated_at   | 是     | date      | 记录创建时间 Y-m-d H:i:s\nprovince     | 否     | string    | 所在州\ncity         | 否     | string    | 城市\ndistrict     | 否     | string    | 县/区\nstreet       | 否     | string    | 街道\naddress      | 否     | string    | 详细地址",
                        "type": "json"
                    },
                    {
                        "title": "USER_POSITION 示例",
                        "content": "{\"user_id\":\"123\",\"USER_POSITION\":[{\"id\":4907,\"longitude\":116.235325,\"latitude\":40.047018,\"province\":\"\\u5317\\u4eac\\u5e02\",\"city\":\"\\u5317\\u4eac\\u5e02\",\"district\":\"\\u6d77\\u6dc0\\u533a\",\"street\":\"\\u4eae\\u7532\\u5e97\\u8def\",\"address\":\"\\u5317\\u4eac\\u5e02\\u6d77\\u6dc0\\u533a\\u4eae\\u7532\\u5e97\\u8def\\u897f\\u5317\\u65fa\",\"created_at\":\"2020-03-27 08:13:29\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER_THIRD_DATA",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录列ID\ntype         | 是     | string    | 类型 aadhaarCard号码第三方校验得到的手机号：aadhaarCardTelephoneCheck  aadhaarCard号码第三方校验得到的年龄范围：aadhaarCardAgeCheck  panCard号码第三方验证得到的名字：pancardNameCheck  panCardOCR识别得到的名字：pancardOcrVerfiyNameCheck   人脸识别分数：faceComparison\nchannel      | 否     | string    | 渠道\nresult       | 是     | string    | 原数据json\nvalue        | 是     | string    | 具体值。不同类型值举例 aadhaarCardTelephoneCheck：xxxxxxx280  aadhaarCardAgeCheck：20-30  pancardNameCheck：YESHPAL SHARMA  pancardOcrVerfiyNameCheck：YESHPAL SHARMA   faceComparison(0-1的数字,识别度)：0.96\nstatus       | 是     | int       | 记录状态 -1:失效 1:有效\ncreated_at   | 是     | date      | 记录创建时间 Y-m-d H:i:s\nupdated_at   | 是     | date      | 最后修改时间 Y-m-d H:i:s",
                        "type": "json"
                    },
                    {
                        "title": "USER_THIRD_DATA 示例",
                        "content": "{\"user_id\":\"123\",\"USER_THIRD_DATA\":[{\"id\":2179544,\"type\":\"pancardNameCheck\",\"channel\":\"\",\"result\":\"{\\\"auth_name\\\":\\\"SAROJ NAIK\\\",\\\"fullname\\\":\\\"Saroj Naik\\\",\\\"identical\\\":true}\",\"created_at\":\"2020-03-17 15:54:20\",\"updated_at\":\"2020-03-17 15:54:20\",\"status\":1,\"value\":\"SAROJ NAIK\"},{\"id\":2179545,\"type\":\"pancardOcrVerfiyNameCheck\",\"channel\":\"\",\"result\":\"{\\\"verfiy_name\\\":\\\"SAROJ NAIK\\\",\\\"ocr_name\\\":\\\"SAROJ NAIK\\\",\\\"identical\\\":true}\",\"created_at\":\"2020-03-17 15:54:20\",\"updated_at\":\"2020-03-17 15:54:20\",\"status\":1,\"value\":\"SAROJ NAIK\"},{\"id\":2179549,\"type\":\"faceComparison\",\"channel\":\"Services\",\"result\":\"{\\\"identical\\\":true,\\\"score\\\":0.957,\\\"aadhaar_upload_id\\\":683996,\\\"aadhaar_upload_path\\\":\\\"data\\\\\\/saas\\\\\\/prod\\\\\\/aadhaar\\\\\\/20200317\\\\\\/210442_155305.jpeg.jepg\\\",\\\"face_upload_id\\\":683983,\\\"face_upload_path\\\":\\\"data\\\\\\/saas\\\\\\/prod\\\\\\/face\\\\\\/20200317\\\\\\/210442_155131.jepg\\\"}\",\"created_at\":\"2020-03-17 15:54:21\",\"updated_at\":\"2020-03-17 15:54:21\",\"status\":1,\"value\":0.957},{\"id\":2525489,\"type\":\"aadhaarCardTelephoneCheck\",\"channel\":\"\",\"result\":\"{\\\"user_telephone\\\":\\\"8239317231\\\",\\\"aadhaar_telephone\\\":\\\"xxxxxxx231\\\",\\\"identical\\\":true}\",\"created_at\":\"2020-04-02 13:03:42\",\"updated_at\":\"2020-04-02 13:03:42\",\"status\":1,\"value\":\"xxxxxxx231\"}]}",
                        "type": "json"
                    },
                    {
                        "title": "USER_SMS",
                        "content": "二维数组列字段\n字段         | 必填    | 类型      | 描述\nid           | 是     | int       | 记录列ID\nsms_telephone| 是     | string    | 短信【发送|接收】电话\nsms_centent  | 否     | string    | 短信内容\nsms_date     | 否     | string    | 短信【发送|接收】的时间\ntype         | 否     | string    | 类型 1:接收 2:发送\ncreated_at   | 否     | date      | 记录添加时间 Y-m-d H:i:s",
                        "type": "json"
                    },
                    {
                        "title": "USER_SMS 示例",
                        "content": "{\"user_id\":\"123\",\"task_no\":\"TASK_BCACAE4F2B13AC70\",\"USER_SMS\":[{\"id\":240,\"sms_telephone\":\"51462\",\"sms_centent\":\"<#>  039901  is your OTP for MiCredit login. It is valid for 5 minutes. Please don't share it with anyone.\",\"sms_date\":\"2020-02-07 16:05:38\",\"type\":1,\"created_at\":\"2020-03-31 08:07:01\"},{\"id\":181,\"sms_telephone\":\"51462\",\"sms_centent\":\"xx code 655065\",\"sms_date\":\"2020-02-19 16:22:13\",\"type\":1,\"created_at\":\"2020-03-31 08:07:01\"},{\"id\":187,\"sms_telephone\":\"57575353\",\"sms_centent\":\"G-896518 \\u662f\\u60a8\\u7684 Google \\u9a8c\\u8bc1\\u7801\\u3002\",\"sms_date\":\"2020-02-18 18:23:11\",\"type\":1,\"created_at\":\"2020-03-31 08:07:01\"}]}",
                        "type": "json"
                    }
                ]
            },
            "success": {
                "fields": {
                    "返回值": [
                        {
                            "group": "返回值",
                            "type": "int",
                            "optional": false,
                            "field": "status",
                            "description": "<p>请求状态 18000:成功 13000:失败 <strong>全局状态</strong></p>"
                        },
                        {
                            "group": "返回值",
                            "type": "String",
                            "optional": false,
                            "field": "msg",
                            "description": "<p>信息</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "Array",
                            "optional": false,
                            "field": "data",
                            "description": "<p>返回数据</p>"
                        },
                        {
                            "group": "返回值",
                            "type": "String",
                            "optional": false,
                            "field": "data.--",
                            "description": "<p>*上传任务项每项的结果 成功：SUCCESS  失败：FAILED 。若关联了机审任务上传数据，失败的数据项需要重新上传否则不能成功发起机审执行</p>"
                        }
                    ]
                },
                "examples": [
                    {
                        "title": "成功返回:",
                        "content": "{\n\"code\":18000,\n\"msg\":\"ok\",\n\"data\":{\n\"BANK_CARD\":\"SUCCESS\"\n}\n}",
                        "type": "json"
                    }
                ]
            },
            "filename": "docs/apidoc/Api/SendDataDoc.php",
            "groupTitle": "SendData",
            "name": "PostApiRiskSend_dataAll"
        }
    ]
});
