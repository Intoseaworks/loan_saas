CREATE TABLE `coupon` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) NOT NULL COMMENT '优惠券名称',
  `coupon_type` int(2) NOT NULL COMMENT '优惠券类型,1免息券，2抵扣券',
  `used_amount` int(10) unsigned DEFAULT '0' COMMENT '用券金额',
  `usage` int(2) DEFAULT NULL COMMENT '使用限制：1直减，2满减',
  `with_amount` int(10) unsigned DEFAULT '0' COMMENT '满多少金额可用',
  `overdue_use` int(2) NOT NULL DEFAULT '2' COMMENT '逾期是否可以使用,1可以，2不可以',
  `end_time` datetime NOT NULL COMMENT '优惠券有效期',
  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，0表示过期，1生效，2停止，优惠券关联的所有优惠券任务都会相关动作',
  `create_user` bigint(20) NOT NULL COMMENT '创建用户',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `update_user` bigint(20) NOT NULL COMMENT '修改用户',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `index_end_time` (`end_time`),
  KEY `index_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COMMENT='优惠券表';

CREATE TABLE `coupon_receive` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `coupon_id` bigint(20) NOT NULL COMMENT '关联优惠券id',
  `user_id` bigint(20) NOT NULL COMMENT '关联用户id',
  `order_id` bigint(20) DEFAULT NULL COMMENT '关联订单id，用户在此订单使用了优惠券',
  `coupon_task_id` bigint(20) NOT NULL COMMENT '优惠券是从哪个优惠券任务领取的',
  `created_at` datetime NOT NULL COMMENT '领取优惠券时间',
  `use_time` datetime NOT NULL COMMENT '优惠券使用时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `index_coupon_id` (`coupon_id`),
  KEY `index_user_id` (`user_id`),
  KEY `index_coupon_task_id` (`coupon_task_id`),
  KEY `index_use_time` (`use_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2104 DEFAULT CHARSET=utf8mb4 COMMENT='用户领取优惠券';

CREATE TABLE `coupon_task` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `coupon_id` bigint(20) NOT NULL COMMENT '关联优惠券id',
  `title` varchar(200) NOT NULL COMMENT '任务名称',
  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，0表示未开始，1生效，2停止',
  `customer_group` int(2) NOT NULL COMMENT '客户群体，0全体客户，1首贷普通名单，2首贷白名单，3首贷一般客户，4首贷客户',
  `clm_level` varchar(20) DEFAULT NULL COMMENT 'CLM等级，可多选',
  `batch` varchar(20) DEFAULT NULL COMMENT '批次号',
  `is_blacklist` int(2) NOT NULL COMMENT '是否排除黑名单0不排除，1排除',
  `is_graylist` int(2) NOT NULL COMMENT '是否排除灰名单0不排除，1排除',
  `cust_status` varchar(50) DEFAULT NULL COMMENT '客户当前状态1.未注册，2注册未申请，3审批中，4审批拒绝，5放款处理中，6待还款，7逾期，8结清;存储规则1,2,3逗号分隔符分开',
  `max_overdue_day` int(4) DEFAULT NULL COMMENT '全品牌最大逾期天数限制',
  `last_login_limit` int(2) DEFAULT NULL COMMENT '最后登录时间距今限定0-未注册，1已注册',
  `last_login_limit_scope` varchar(25) DEFAULT NULL COMMENT '最后登录时间距今限定已注册 1-2，1到2天',
  `grant_type` int(2) NOT NULL COMMENT '发放类型：1.注册时，2停留超过天数，3.生日当天，4特定时间',
  `grant_type_scope` varchar(50) DEFAULT NULL COMMENT '发放类型范围：用于存放停留超过天数，特定时间',
  `get_way` int(2) NOT NULL COMMENT '领取方式1无需领取，2APP内弹窗领取，3APP活动页领取',
  `notice_type` int(2) NOT NULL DEFAULT '0' COMMENT '通知类型1无，2站内信，3.短信',
  `notice_type_scope` int(20) DEFAULT NULL COMMENT '站内信id或短信模板id',
  `notice_begin_time` time DEFAULT NULL COMMENT '\r\n通知发送的开始时间',
  `create_user` bigint(20) NOT NULL COMMENT '创建用户',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `update_user` bigint(20) NOT NULL COMMENT '更新用户',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `issue_count` int(10) unsigned DEFAULT '0' COMMENT '任务发放优惠券数量',
  `received_count` int(10) unsigned DEFAULT '0' COMMENT '任务发放优惠券用户领取数量',
  `used_count` int(10) unsigned DEFAULT '0' COMMENT '任务发放优惠券用户使用数量',
  PRIMARY KEY (`id`),
  KEY `index_get_way` (`get_way`),
  KEY `coupon_task_index_status` (`status`),
  KEY `coupon_task_index_grant_type` (`grant_type`),
  KEY `coupon_task_index_grant_type_scope` (`grant_type_scope`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COMMENT='优惠券任务表';

CREATE TABLE `crm_customer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `main_user_id` bigint(20) DEFAULT '0' COMMENT '主用户ID',
  `clm_level` int(5) DEFAULT NULL COMMENT 'clm等级',
  `telephone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `telephone_status` tinyint(4) DEFAULT '0' COMMENT '手机状态(-1:未检测;1=正常;2=无效;3=停机)',
  `telephone_check_time` datetime DEFAULT NULL COMMENT '手机检测时间',
  `type` tinyint(4) DEFAULT '1' COMMENT '客户类型(1=首贷普通名单;2=首贷白名单;3=首贷一般客户;4=复贷客户)',
  `email` varchar(255) DEFAULT NULL COMMENT 'email',
  `fullname` varchar(255) DEFAULT NULL COMMENT '全名',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `id_type` varchar(255) DEFAULT NULL COMMENT '证件类型',
  `id_number` varchar(50) DEFAULT NULL COMMENT '证件号码',
  `gender` varchar(10) NOT NULL DEFAULT 'Male' COMMENT '性别:男,女',
  `status` tinyint(4) DEFAULT '1' COMMENT '用户状态(\r\n1:未注册;\r\n2:注册未申请;\r\n3:审批中;\r\n4:审批拒绝;\r\n5:放款处理中\r\n6:待还款\r\n7:逾期\r\n8:结清)',
  `batch_id` int(11) DEFAULT '0' COMMENT '导入批次号(白名单|营销)',
  `suggest_time` varchar(50) DEFAULT NULL COMMENT '建议致电时间',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `max_overdue_days` int(11) DEFAULT '0' COMMENT '最大逾期天数',
  `status_stop_days` int(11) DEFAULT '0' COMMENT '当前状态停留时间',
  `settle_times` int(11) DEFAULT NULL COMMENT '结清次数',
  `last_settle_time` datetime DEFAULT NULL COMMENT '最后结清时间',
  `status_updated_time` datetime DEFAULT NULL COMMENT '状态变更时间',
  `remark` text COMMENT '上传时备注',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33703 DEFAULT CHARSET=utf8mb4 COMMENT='客户管理-客户主表';

CREATE TABLE `crm_customer_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL COMMENT '客户ID',
  `user_id` bigint(20) DEFAULT '0' COMMENT '用户ID',
  `email` varchar(50) DEFAULT NULL COMMENT 'Email',
  `admin_id` int(11) DEFAULT '0' COMMENT '管理员',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23497 DEFAULT CHARSET=utf8mb4 COMMENT='CRM客户-email';

CREATE TABLE `crm_customer_fb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL COMMENT '客户ID',
  `reg_telephone` varchar(20) DEFAULT '0' COMMENT 'fb注册手机号',
  `fb_id` varchar(200) DEFAULT NULL COMMENT 'fbID',
  `admin_id` int(11) DEFAULT '0' COMMENT '管理员',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COMMENT='CRM客户-fb信息';

CREATE TABLE `crm_customer_paper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL COMMENT '客户ID',
  `id_type` varchar(20) DEFAULT '0' COMMENT '证件类型',
  `id_number` varchar(20) DEFAULT NULL COMMENT '证件编号',
  `user_id` bigint(20) DEFAULT '0',
  `admin_id` int(11) DEFAULT '0' COMMENT '管理员',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=268 DEFAULT CHARSET=utf8mb4 COMMENT='客户-证件表';

CREATE TABLE `crm_customer_telephone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) DEFAULT NULL COMMENT 'crm客户ID',
  `telephone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `telephone_status` tinyint(4) DEFAULT '0' COMMENT '手机状态(-1:未检测;1=正常;2=无效;3=停机)',
  `telephone_test_time` datetime DEFAULT NULL COMMENT '手机号检测时间',
  `user_id` bigint(13) DEFAULT NULL COMMENT '用户ID',
  `status` tinyint(4) DEFAULT '1' COMMENT '是否有效(1=有效;0=失效)',
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33689 DEFAULT CHARSET=utf8mb4 COMMENT='客户-手机号';

CREATE TABLE `crm_customer_user_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL COMMENT 'crm_customer.id',
  `user_id` int(11) DEFAULT NULL COMMENT 'user.id',
  `similarity` int(11) DEFAULT '1' COMMENT '相似度',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_c_u` (`customer_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33675 DEFAULT CHARSET=utf8mb4 COMMENT='crm->user映射';

CREATE TABLE `crm_marketing_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_number` varchar(100) DEFAULT NULL COMMENT '批次号',
  `indate` datetime DEFAULT NULL COMMENT '过期时间',
  `match_rule` json DEFAULT NULL COMMENT '匹配规则',
  `total_count` int(10) DEFAULT NULL COMMENT '导入总数',
  `success_count` int(10) DEFAULT '0' COMMENT '成功总数',
  `status` tinyint(4) DEFAULT '1' COMMENT '是否有效(1=有效;0=失效)',
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `reg_count` int(11) DEFAULT '0' COMMENT '注册总数',
  `finish_count` int(11) DEFAULT '0' COMMENT '完件总数',
  `telephone_count` int(11) DEFAULT '0' COMMENT '电话营销总数',
  `sms_count` int(11) DEFAULT '0' COMMENT '短信营销总数',
  `indate_count` int(11) DEFAULT '0' COMMENT '有效期内数量',
  `last_marketing_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '最后营销时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COMMENT='营销批次表';

CREATE TABLE `crm_marketing_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) DEFAULT NULL COMMENT '批次号',
  `telephone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `type` varchar(50) DEFAULT NULL COMMENT '白名单类型',
  `email` varchar(255) DEFAULT NULL COMMENT 'email',
  `fullname` varchar(255) DEFAULT NULL COMMENT '全名',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `id_type` varchar(255) DEFAULT NULL COMMENT '证件类型',
  `id_number` varchar(50) DEFAULT NULL COMMENT '证件号码',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态1=正常2=失败',
  `indate` datetime DEFAULT NULL COMMENT '有效期null=不限期限',
  `admin_id` int(11) DEFAULT NULL COMMENT '操作人ID（staff.id）',
  `customer_id` bigint(20) DEFAULT '0' COMMENT 'crmCustomerID',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i-batch-number` (`batch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COMMENT='CRM白名单';

CREATE TABLE `crm_marketing_phone_assign` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) DEFAULT NULL COMMENT '客户IDcrm_customer.id',
  `task_id` int(11) DEFAULT NULL COMMENT '任务ID crm_marketing_task.id',
  `saler_id` bigint(20) DEFAULT '0' COMMENT '电话销售ID,staff.id (0未分配',
  `assign_time` datetime DEFAULT NULL COMMENT '任务分配时间',
  `suggest_time` varchar(50) DEFAULT NULL COMMENT '建议致电时间',
  `last_call_time` datetime DEFAULT NULL COMMENT '最后通话时间',
  `admin_id` int(11) DEFAULT '0' COMMENT '分配人ID(staff.id)',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态(1=待电销;2=完成电销)',
  `stop_reason` varchar(255) DEFAULT NULL COMMENT '停止原因',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COMMENT='电话营销分配表';

CREATE TABLE `crm_marketing_phone_assign_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `assign_id` bigint(20) DEFAULT NULL COMMENT '分配ID',
  `customer_id` bigint(20) DEFAULT NULL COMMENT '客户IDcrm_customer.id',
  `task_id` int(11) DEFAULT NULL COMMENT '任务ID crm_marketing_task.id',
  `saler_id` bigint(20) DEFAULT '0' COMMENT '电话销售ID,staff.id (0未分配)',
  `from_saler_id` bigint(20) DEFAULT '0' COMMENT '上一个销售员 (0未分配)',
  `assign_time` datetime DEFAULT NULL COMMENT '任务分配时间',
  `admin_id` int(11) DEFAULT '0' COMMENT '分配人ID(staff.id)',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='电话营销分配表';

CREATE TABLE `crm_marketing_phone_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) DEFAULT NULL COMMENT '任务Id(crm_marketing_task.id)',
  `customer_type` tinyint(4) DEFAULT '0' COMMENT '客户状态',
  `total` int(11) DEFAULT NULL COMMENT '待分配总量',
  `per_capita_allocation` int(11) DEFAULT NULL COMMENT '人均分配量',
  `saler_ids` text COMMENT '分配账号例1,2,3,4',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作员(staff.id)',
  `remark` text COMMENT '备注',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `crm_marketing_phone_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) DEFAULT NULL COMMENT '营销任务ID(crm_marketing_task.id)',
  `customer_id` bigint(20) DEFAULT NULL COMMENT '客户ID(crm_customer.id',
  `assign_id` bigint(20) DEFAULT NULL COMMENT '分配ID(crm_marketing_telephone.id)',
  `operator_id` int(11) DEFAULT NULL COMMENT '操作员(staff.id)',
  `call_status` tinyint(4) DEFAULT NULL COMMENT '呼叫情况\r\n1.NO ANSWER\r\n2.NO SERVICE\r\n3.INVALID NUMBER\r\n4.WRONG NUMBER\r\n5.ANSWER',
  `call_result` tinyint(4) DEFAULT NULL COMMENT '跟进结果\r\n1.QUIT MARKETING\r\n2.HANG UP\r\n3.THINK ABOUT IT\r\n4.WILL APPLY\r\n5.NO INTENTION TO APPLY\r\n6.OTHER PERSON USE PHONE AND HAVE NO INTENTION TO APPLY\r\n7.OTHER PERSON USE PHONE AND WILL APPLY\r\n8.CALL LATER',
  `call_refusal` tinyint(4) DEFAULT NULL COMMENT '拒绝原因\r\n1.DON''T NEED\r\n2.DOESN''T SAY ANYTHING\r\n3.HAVE NO WORK\r\n4.HIGH INTEREST\r\n5.LOST ID\r\n6.NO BANK CARD\r\n7.SHORT PERIOD\r\n8.SLOW DISBURSEMENT',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `customer_status` tinyint(255) DEFAULT NULL COMMENT '当前用户状态',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `crm_marketing_phone_report` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL COMMENT '报告日期',
  `saler_id` int(11) DEFAULT NULL COMMENT '销售员(staff.id)',
  `total` int(11) DEFAULT '0' COMMENT '总数',
  `agree_number` int(11) DEFAULT '0' COMMENT '同意数',
  `call_reject_number` int(11) DEFAULT '0' COMMENT '呼叫拒绝数',
  `miss_call_number` int(11) DEFAULT '0' COMMENT '未接听数',
  `wrong_number` int(11) DEFAULT NULL COMMENT '号码错误数',
  `apply_number` int(11) DEFAULT NULL COMMENT '进件数',
  `pass_number` int(11) DEFAULT NULL COMMENT '审核通过数',
  `reject_number` int(11) DEFAULT NULL COMMENT '拒绝数',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `crm_marketing_sms_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) DEFAULT NULL COMMENT '任务ID(crm_marketing_task.id',
  `customer_id` bigint(20) DEFAULT NULL COMMENT '客户ID(crm_customer.id',
  `sms_template_id` int(11) DEFAULT NULL COMMENT '短信模板ID(crm_sms_template.id)',
  `telephone` varchar(20) DEFAULT NULL COMMENT '电话',
  `content` varchar(255) DEFAULT NULL COMMENT '短信内容',
  `status` tinyint(5) DEFAULT '0' COMMENT '0=待发送1=已发;2=发送成功',
  `customer_status` tinyint(4) DEFAULT NULL COMMENT '当前用户状态',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `crm_marketing_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '任务ID标识',
  `task_name` varchar(200) DEFAULT NULL COMMENT '任务名',
  `task_type` varbinary(20) DEFAULT NULL COMMENT '任务类型(SMS=短信;PHONE=短语）',
  `customer_type` tinyint(4) DEFAULT NULL COMMENT '用户群体customer.type',
  `customer_status` varchar(300) DEFAULT NULL COMMENT '用户状态',
  `clm_level` varchar(300) DEFAULT NULL COMMENT 'clm等级要求',
  `batch_id` varchar(50) DEFAULT NULL COMMENT '批次号',
  `check_blacklist` tinyint(1) DEFAULT '0' COMMENT '排除黑名单(0=不排除;1=排除)',
  `check_greylist` tinyint(1) DEFAULT '0' COMMENT '排除灰名单(0=不排除;1=排除)',
  `max_overdue_days` int(5) DEFAULT NULL COMMENT '最大预期天数',
  `telephone_status` tinyint(4) DEFAULT NULL COMMENT '手机状态(0:未检测;1=正常;2=无效;3=停机)',
  `last_login` varchar(255) DEFAULT NULL COMMENT '最后登陆时间（-1:未注册;已注册:{"start":"1","end":"10"}]',
  `send_time` text COMMENT '发送时间数组',
  `frequency` varchar(20) DEFAULT NULL COMMENT '发送频次',
  `frequency_detail` text COMMENT '发送频次详情',
  `sms_template_id` int(11) DEFAULT NULL COMMENT '短信模板',
  `phone_time_interval` varchar(100) DEFAULT '1' COMMENT '电话营销距上次时间间隔(天)',
  `phone_stop_term` varchar(255) DEFAULT NULL COMMENT '电话&短信营销停止条件',
  `phone_stop_term_detail` varchar(255) DEFAULT NULL COMMENT '停止限定 进入电销N天',
  `phone_status_stop_days` int(11) DEFAULT '0' COMMENT '当前状态停留时间0不计算',
  `sms_run_times` int(11) DEFAULT '0' COMMENT '执行次数',
  `send_total` int(11) DEFAULT '0' COMMENT '下发总数',
  `success_total` int(11) DEFAULT '0' COMMENT '成功数',
  `apply_total` int(11) DEFAULT '0' COMMENT '进件数',
  `paid_total` int(11) DEFAULT '0' COMMENT '放款数',
  `agree_total` int(11) DEFAULT '0' COMMENT '已同意数',
  `status` tinyint(1) DEFAULT '1' COMMENT '1=正常;0=失效',
  `admin_id` int(11) DEFAULT NULL COMMENT '创建ID',
  `phone_total` int(255) DEFAULT '0' COMMENT '名单总数',
  `phone_assign_total` int(255) DEFAULT NULL COMMENT '分配总数',
  `phone_finish_total` int(255) DEFAULT '0' COMMENT '已完成总数',
  `remark` text COMMENT '备注',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COMMENT='营销任务';

CREATE TABLE `crm_sms_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tpl_name` varchar(255) DEFAULT NULL COMMENT '模板名称',
  `tpl_content` varchar(500) DEFAULT NULL COMMENT '短信内容',
  `status` tinyint(1) DEFAULT '1' COMMENT '模板状态(1=正常;0=停用)',
  `admin_id` int(11) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `crm_white_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_number` varchar(100) DEFAULT NULL COMMENT '批次号',
  `indate` datetime DEFAULT NULL COMMENT '过期时间',
  `match_rule` json DEFAULT NULL COMMENT '匹配规则',
  `match_blacklist` tinyint(4) DEFAULT NULL COMMENT '是否匹配黑名单',
  `match_greylist_type` tinyint(4) DEFAULT NULL COMMENT '命中灰名单',
  `total_count` int(10) DEFAULT '0' COMMENT '导入总数',
  `reg_count` int(11) DEFAULT '0' COMMENT '注册总数',
  `finish_count` int(11) DEFAULT NULL COMMENT '完件总数',
  `success_count` int(10) DEFAULT '0' COMMENT '成功总数',
  `telephone_count` int(11) DEFAULT '0' COMMENT '电话营销总数',
  `sms_count` int(11) DEFAULT '0' COMMENT '短信营销总数',
  `indate_count` int(11) DEFAULT '0' COMMENT '有效期内数量',
  `status` tinyint(4) DEFAULT '1' COMMENT '是否有效(1=有效;0=失效)',
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员',
  `last_marketing_time` datetime DEFAULT NULL COMMENT '最后营销时间',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COMMENT='CRM白名单批次表';

CREATE TABLE `crm_white_import_failed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL COMMENT '导入类型（WHITE_LIST，MARKETING）',
  `batch_id` int(11) DEFAULT NULL COMMENT '批次号',
  `failed_data` text COMMENT '失败数据',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COMMENT='白名单导入失败';

CREATE TABLE `crm_white_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) DEFAULT NULL COMMENT '批次号',
  `telephone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `type` varchar(50) DEFAULT NULL COMMENT '白名单类型',
  `email` varchar(255) DEFAULT NULL COMMENT 'email',
  `fullname` varchar(255) DEFAULT NULL COMMENT '全名',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `id_type` varchar(255) DEFAULT NULL COMMENT '证件类型',
  `id_number` varchar(50) DEFAULT NULL COMMENT '证件号码',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态1=正常2=失败',
  `indate` datetime DEFAULT NULL COMMENT '有效期null=不限期限',
  `admin_id` int(11) DEFAULT NULL COMMENT '操作人ID（staff.id）',
  `customer_id` bigint(20) DEFAULT NULL COMMENT 'customer_id',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i-batch-number` (`batch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COMMENT='CRM白名单';

CREATE TABLE `sms_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '任务ID标识',
  `task_name` varchar(200) DEFAULT NULL COMMENT '任务名',
  `task_type` varbinary(20) DEFAULT NULL COMMENT '任务类型(SMS=短信;PHONE=短语）',
  `customer_type` tinyint(4) DEFAULT NULL COMMENT '用户群体customer.type',
  `clm_level` tinyint(6) DEFAULT NULL COMMENT 'clm等级要求',
  `batch_id` int(11) DEFAULT NULL COMMENT '批次号',
  `check_blacklist` tinyint(1) DEFAULT '0' COMMENT '排除黑名单(0=不排除;1=排除)',
  `check_greylist` tinyint(1) DEFAULT '0' COMMENT '排除灰名单(0=不排除;1=排除)',
  `max_overdue_days` int(5) DEFAULT NULL COMMENT '最大预期天数',
  `telephone_status` tinyint(4) DEFAULT NULL COMMENT '手机状态(0:未检测;1=正常;2=无效;3=停机)',
  `last_login` varchar(255) DEFAULT NULL COMMENT '最后登陆时间（-1:未注册;已注册:{"start":"1","end":"10"}]',
  `send_time` datetime DEFAULT NULL COMMENT '发送时间数组',
  `frequency` varchar(20) DEFAULT NULL COMMENT '发送频次',
  `sms_template_id` int(11) DEFAULT NULL COMMENT '短信模板',
  `phone_time_interval` int(5) DEFAULT '1' COMMENT '电话营销距上次时间间隔(天)',
  `phone_stop_term` varchar(255) DEFAULT NULL COMMENT '电话营销停止条件',
  `sms_run_times` int(11) DEFAULT '0' COMMENT '执行次数',
  `status` tinyint(1) DEFAULT '1' COMMENT '1=正常;0=失效',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `upload_id` int(10) unsigned DEFAULT NULL COMMENT '上传名单id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COMMENT='营销任务';

CREATE TABLE `sms_task_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户短信自增长id',
  `task_id` bigint(20) NOT NULL COMMENT '短信任务id',
  `sms_telephone` varchar(32) DEFAULT NULL COMMENT '短信【发送|接收】电话',
  `sms_centent` varchar(1024) DEFAULT NULL COMMENT '短信内容',
  `sms_date` varchar(50) DEFAULT NULL COMMENT '短信【发送|接收】的时间',
  `type` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1 接收 2发送',
  `created_at` datetime DEFAULT NULL COMMENT '添加时间',
  `user_id` bigint(20) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL COMMENT '真实姓名',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_id` (`task_id`,`sms_telephone`,`sms_date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户短信表 ';

CREATE TABLE `sms_telephone_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telephone` varchar(20) DEFAULT NULL COMMENT '号码',
  `created_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE `sms_tpl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tpl_type` varchar(50) DEFAULT NULL COMMENT '模板类型',
  `tpl_content` varchar(500) DEFAULT NULL COMMENT '模板内容',
  `admin_id` int(11) DEFAULT NULL COMMENT 'staff.id',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u-type` (`tpl_type`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `collection_detail` 
MODIFY COLUMN `reduction_setting` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '减免设置' AFTER `contact_num`;