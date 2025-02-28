/*
 Navicat Premium Data Transfer

 Source Server         : P_SAAS_URUPEE
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : rm-a2d1333s9gp40460i.mysql.ap-south-1.rds.aliyuncs.com:3306
 Source Schema         : loan_saas_db

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 24/03/2020 10:52:31
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for access_token
-- ----------------------------
DROP TABLE IF EXISTS `access_token`;
CREATE TABLE `access_token` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户id',
  `token` varchar(64) NOT NULL COMMENT '身份令牌',
  `client_id` varchar(32) NOT NULL COMMENT '终端',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `expired_time` datetime NOT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `access_token` (`token`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for action_log
-- ----------------------------
DROP TABLE IF EXISTS `action_log`;
CREATE TABLE `action_log` (
  `action_log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '事件名称',
  `content` varchar(128) CHARACTER SET utf8 DEFAULT '' COMMENT '事件附加信息',
  `ip` varchar(15) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户访问IP',
  `quality` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户质量',
  `device_uuid` varchar(128) CHARACTER SET utf8 DEFAULT '' COMMENT '设备ID',
  `app_version` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'App版本',
  `client_id` varchar(128) NOT NULL COMMENT '设备类型',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `unit_type` varchar(50) DEFAULT '' COMMENT '设备型号',
  `brand` varchar(50) DEFAULT '' COMMENT '设备品牌',
  PRIMARY KEY (`action_log_id`) USING BTREE,
  KEY `device_uuid` (`device_uuid`) USING BTREE,
  KEY `name` (`name`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `created_time` (`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='事件日志';

-- ----------------------------
-- Table structure for action_name
-- ----------------------------
DROP TABLE IF EXISTS `action_name`;
CREATE TABLE `action_name` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 NOT NULL COMMENT '动作名称[英文]',
  `nickname` varchar(50) CHARACTER SET utf8mb4 NOT NULL COMMENT '动作别名[中文]',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0禁用 1启用',
  `stat_type` char(6) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '统计方式 pv统计 uv统计 uid统计',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for admin_trade_account
-- ----------------------------
DROP TABLE IF EXISTS `admin_trade_account`;
CREATE TABLE `admin_trade_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `type` tinyint(4) NOT NULL COMMENT '类型 1:放款  2:还款',
  `payment_method` tinyint(4) NOT NULL COMMENT '类型 1:银行卡转账  2:支付宝',
  `account_no` varchar(32) CHARACTER SET utf8mb4 NOT NULL COMMENT '银行卡号',
  `account_name` varchar(100) CHARACTER SET utf8mb4 NOT NULL COMMENT '银行卡户名',
  `bank_name` varchar(16) NOT NULL DEFAULT '' COMMENT '银行名称',
  `status` tinyint(4) NOT NULL COMMENT '状态  1:正常  -1:已删除  -2:禁用',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `is_default` tinyint(2) NOT NULL DEFAULT '0' COMMENT '默认方式',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台账户表';

-- ----------------------------
-- Table structure for app
-- ----------------------------
DROP TABLE IF EXISTS `app`;
CREATE TABLE `app` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL COMMENT '商户id',
  `app_key` varchar(255) NOT NULL COMMENT 'app唯一key',
  `app_name` varchar(64) NOT NULL COMMENT 'app名称',
  `page_name` varchar(100) DEFAULT NULL COMMENT '包名',
  `status` tinyint(4) NOT NULL COMMENT '状态 1：正常  2：禁用  -1：删除',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `google_server_key` varchar(255) NOT NULL COMMENT 'google服务key',
  `app_url` text COMMENT 'app下载地址',
  `send_id` varchar(20) DEFAULT NULL COMMENT '短信发信ID',
  `putaway_time` datetime DEFAULT NULL COMMENT '上架时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `app_key` (`app_key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for app_version
-- ----------------------------
DROP TABLE IF EXISTS `app_version`;
CREATE TABLE `app_version` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `platform` tinyint(4) NOT NULL DEFAULT '1' COMMENT '平台（1：android；2：ios）',
  `version` varchar(16) NOT NULL COMMENT '版本号',
  `channel` varchar(64) NOT NULL,
  `forcible` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否强制更新（1：true 0：false）',
  `title` varchar(64) DEFAULT '' COMMENT '版本名称',
  `content` varchar(255) DEFAULT '' COMMENT '更新提示语',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '更新地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1：正常；0：已删除）',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='APP版本更新数据表';

-- ----------------------------
-- Table structure for approve
-- ----------------------------
DROP TABLE IF EXISTS `approve`;
CREATE TABLE `approve` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '审批记录id',
  `merchant_id` bigint(20) unsigned DEFAULT NULL COMMENT 'merchant_id',
  `type` bigint(4) NOT NULL COMMENT '类型',
  `order_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '订单id',
  `admin_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '审批操作人id',
  `admin_username` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '审批人 名称',
  `result` varchar(64) NOT NULL DEFAULT '' COMMENT '审批结果',
  `approve_select` text NOT NULL COMMENT '审批选项',
  `approve_content` text NOT NULL COMMENT '审批选项内容',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态  1：正常 -1：被覆盖',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `approve_order_id_index` (`order_id`) USING BTREE,
  KEY `merchant_id_index` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='审批表';

-- ----------------------------
-- Table structure for approve_assign_log
-- ----------------------------
DROP TABLE IF EXISTS `approve_assign_log`;
CREATE TABLE `approve_assign_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL COMMENT 'merchant_id',
  `approve_pool_id` int(11) NOT NULL,
  `order_id` bigint(13) NOT NULL COMMENT '订单id',
  `admin_id` bigint(13) NOT NULL COMMENT '分单人员id',
  `fullname` varchar(25) DEFAULT NULL,
  `telephone` varchar(16) DEFAULT NULL,
  `from_order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单修改前状态',
  `to_order_status` tinyint(1) NOT NULL COMMENT '订单修改后状态',
  `approve_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审批状态默认0审批中1审批完成2分单取消 用户的审批状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `approve_pool_id` (`approve_pool_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='分单日志';

-- ----------------------------
-- Table structure for approve_fail_record
-- ----------------------------
DROP TABLE IF EXISTS `approve_fail_record`;
CREATE TABLE `approve_fail_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1 初审 2电审',
  `option_type` tinyint(1) unsigned NOT NULL COMMENT '初审 1 approve filed , 2 Suspected fraud/disqualified',
  `option_value` tinyint(1) unsigned NOT NULL COMMENT '选择项值',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `option_value` (`option_value`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='cashnow 回退项统计';

-- ----------------------------
-- Table structure for approve_page_statistic
-- ----------------------------
DROP TABLE IF EXISTS `approve_page_statistic`;
CREATE TABLE `approve_page_statistic` (
  `id` bigint(13) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `approve_user_pool_id` int(11) unsigned NOT NULL COMMENT 'approve_user_pool 表主键',
  `page` tinyint(1) unsigned NOT NULL COMMENT '审批页',
  `cost` smallint(2) unsigned DEFAULT NULL COMMENT '花费 单位秒',
  `type` tinyint(1) NOT NULL COMMENT '审批类型 1初审 2电审',
  `admin_id` bigint(13) NOT NULL COMMENT '审批人员',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '统计是否有效 1未生效 2有效 审批人员最终提交审批单统计才有效',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `approve_user_pool_id` (`approve_user_pool_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for approve_pool
-- ----------------------------
DROP TABLE IF EXISTS `approve_pool`;
CREATE TABLE `approve_pool` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `order_id` bigint(13) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '审批类型 1初审 2电审',
  `grade` tinyint(1) NOT NULL COMMENT '订单等级 1初审 2补充资料 3电审 4电二审',
  `order_no` varchar(36) NOT NULL COMMENT '订单编号',
  `user_id` bigint(13) DEFAULT NULL COMMENT '用户id',
  `telephone` varchar(15) DEFAULT NULL COMMENT '用户手机号码',
  `order_type` varchar(30) DEFAULT NULL COMMENT '订单类型',
  `order_status` tinyint(1) DEFAULT NULL COMMENT '订单状态 1待初审 2待补充资料 3待电审 4电二审 5机审拒绝 6人工取消 这个订单状态会根据审批单的审批结果变化',
  `order_created_time` int(11) DEFAULT NULL COMMENT '订单生成时间',
  `manual_time` int(11) DEFAULT NULL COMMENT '人工审批时间',
  `risk_pass_time` int(11) DEFAULT NULL COMMENT '机审通过时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审批中状态 0.未审批 1.审批中 2.审批完成 默认0',
  `sort` int(10) DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `telephone` (`telephone`) USING BTREE,
  KEY `order_created_time` (`order_created_time`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT=' 审批池';

-- ----------------------------
-- Table structure for approve_pool_log
-- ----------------------------
DROP TABLE IF EXISTS `approve_pool_log`;
CREATE TABLE `approve_pool_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `approve_pool_id` int(11) NOT NULL,
  `approve_user_pool_id` int(11) DEFAULT NULL,
  `type` tinyint(1) NOT NULL COMMENT '审批类型 1初审 2电审',
  `grade` tinyint(1) NOT NULL,
  `wait_time` int(11) DEFAULT NULL COMMENT '订单审批等待时间',
  `order_status` tinyint(1) DEFAULT NULL,
  `result` tinyint(1) DEFAULT '0' COMMENT '审批结果  0未审批',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `approve_pool_id` (`approve_pool_id`) USING BTREE,
  KEY `approve_user_pool_id` (`approve_user_pool_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for approve_quality
-- ----------------------------
DROP TABLE IF EXISTS `approve_quality`;
CREATE TABLE `approve_quality` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `approve_user_pool_id` int(11) NOT NULL COMMENT 'approve_user_pool 主键',
  `quality_status` tinyint(1) NOT NULL COMMENT '质检状态 1 未质检 2已质检 默认 1',
  `quality_result` tinyint(1) DEFAULT NULL COMMENT '质检结果 1通过误判为拒绝 2拒绝误判为通过 3正确审核',
  `admin_id` bigint(13) DEFAULT NULL COMMENT '质检人 id',
  `quality_time` int(11) DEFAULT NULL COMMENT '质检时间',
  `remark` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `change_order` varchar(20) DEFAULT NULL COMMENT '修改订单状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `approve_user_pool_id` (`approve_user_pool_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for approve_result_snapshot
-- ----------------------------
DROP TABLE IF EXISTS `approve_result_snapshot`;
CREATE TABLE `approve_result_snapshot` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) DEFAULT NULL,
  `approve_user_pool_id` int(11) NOT NULL COMMENT 'approve_user_pool表主键',
  `approve_pool_id` int(11) NOT NULL COMMENT 'approve_pool表主键',
  `result` text NOT NULL COMMENT '审批结果',
  `approve_type` tinyint(1) NOT NULL COMMENT '审批类型 1初审 2电审',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `approve_user_pool_id` (`approve_user_pool_id`) USING BTREE,
  KEY `approve_pool_id` (`approve_pool_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for approve_user_pool
-- ----------------------------
DROP TABLE IF EXISTS `approve_user_pool`;
CREATE TABLE `approve_user_pool` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `admin_id` bigint(20) NOT NULL COMMENT '审批人员',
  `approve_pool_id` int(11) NOT NULL COMMENT 'approve_pool 主键',
  `order_id` bigint(13) NOT NULL COMMENT '订单id',
  `status` tinyint(1) NOT NULL COMMENT '是否完成 0.审批中 1.已审批 2.已过期 默认 0',
  `start_at` int(11) DEFAULT NULL COMMENT '审批时间开始时间',
  `finish_at` int(11) DEFAULT NULL COMMENT '审批结束时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `approve_pool_id` (`approve_pool_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户审批池';

-- ----------------------------
-- Table structure for bank_card
-- ----------------------------
DROP TABLE IF EXISTS `bank_card`;
CREATE TABLE `bank_card` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '银行卡id',
  `merchant_id` bigint(20) unsigned DEFAULT NULL COMMENT 'merchant_id',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `no` varchar(32) NOT NULL COMMENT '银行卡号',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '银行卡户主姓名',
  `bank` varchar(32) DEFAULT NULL COMMENT '银行缩写',
  `bank_name` varchar(64) NOT NULL COMMENT '开户行',
  `reserved_telephone` varchar(11) NOT NULL DEFAULT '' COMMENT '银行预留手机号',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:1正常 0废弃 -1待鉴权绑卡 -2系统清理(放款失败解绑银行卡)',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `bank_branch_name` varchar(64) DEFAULT '' COMMENT '所属支行',
  `province_code` varchar(32) DEFAULT '' COMMENT '省级code',
  `city_code` varchar(32) DEFAULT '',
  `ifsc` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `reject_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `reject_day` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `reserved_telephone` (`reserved_telephone`,`status`) USING BTREE,
  KEY `no` (`no`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `app_id` (`app_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for bank_info
-- ----------------------------
DROP TABLE IF EXISTS `bank_info`;
CREATE TABLE `bank_info` (
  `bank_info_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank` varchar(100) DEFAULT NULL COMMENT '银行名称',
  `ifsc` varchar(100) DEFAULT NULL COMMENT '银行编号',
  `micr_code` varchar(100) DEFAULT NULL COMMENT '编号',
  `branch` varchar(200) DEFAULT NULL COMMENT '分支',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `contact` varchar(100) DEFAULT NULL COMMENT '联系方式',
  `city` varchar(100) DEFAULT NULL COMMENT '所属城市',
  `district` varchar(100) DEFAULT NULL COMMENT '所属区',
  `state` varchar(100) DEFAULT NULL COMMENT '状态',
  `created_time` bigint(13) DEFAULT NULL,
  `updated_time` bigint(13) DEFAULT NULL,
  PRIMARY KEY (`bank_info_id`) USING BTREE,
  UNIQUE KEY `ifsc` (`ifsc`) USING BTREE,
  KEY `state` (`state`) USING BTREE,
  KEY `city` (`city`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for channel
-- ----------------------------
DROP TABLE IF EXISTS `channel`;
CREATE TABLE `channel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增长id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `channel_code` varchar(64) DEFAULT '' COMMENT '渠道编码',
  `channel_name` varchar(64) DEFAULT '' COMMENT '渠道名称',
  `company` varchar(64) DEFAULT '' COMMENT '公司',
  `cpa` varchar(16) DEFAULT '',
  `cps` varchar(16) DEFAULT '',
  `pre_cost` varchar(16) DEFAULT '' COMMENT '预设成本',
  `percent` varchar(16) DEFAULT '' COMMENT '折扣比例',
  `settlement_way` tinyint(3) DEFAULT '1' COMMENT '结算方式',
  `email` varchar(64) NOT NULL DEFAULT '' COMMENT '渠道对接人邮箱',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `check_email` varchar(64) NOT NULL DEFAULT '' COMMENT '内部审核邮箱',
  `partner_name` varchar(64) DEFAULT '' COMMENT '合作方',
  `partner_account` varchar(64) DEFAULT '' COMMENT '合作方帐号',
  `partner_config_dimension` varchar(255) DEFAULT '' COMMENT '合作方维度配置',
  `partner_user_id` int(10) DEFAULT '0' COMMENT '合作方用户id',
  `is_auth` tinyint(2) DEFAULT '0' COMMENT '是否授权 1授权 0未授权',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `cooperation_time` datetime DEFAULT NULL COMMENT '合作时间',
  `url` text NOT NULL COMMENT '推广地址',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `is_top` tinyint(4) DEFAULT '0' COMMENT '是否置顶',
  `top_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '置顶时间',
  `page_name` varchar(100) DEFAULT NULL COMMENT '包名',
  `short_url` varchar(100) DEFAULT '' COMMENT '短链',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `channel_code` (`channel_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='渠道管理表';

-- ----------------------------
-- Table structure for channel_count
-- ----------------------------
DROP TABLE IF EXISTS `channel_count`;
CREATE TABLE `channel_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `channel_id` int(11) NOT NULL COMMENT '渠道ID',
  `register_pv` int(11) NOT NULL COMMENT '注册pv',
  `register_uv` int(11) NOT NULL COMMENT '注册uv',
  `download_pv` int(11) NOT NULL COMMENT '下载pv',
  `download_uv` int(11) NOT NULL COMMENT '下载uv',
  `count_at` date DEFAULT NULL COMMENT '监控时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for collection
-- ----------------------------
DROP TABLE IF EXISTS `collection`;
CREATE TABLE `collection` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `admin_id` bigint(20) unsigned NOT NULL COMMENT '催收人员id',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户id',
  `order_id` bigint(20) unsigned NOT NULL COMMENT '订单id',
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT '催收状态',
  `level` varchar(32) NOT NULL DEFAULT '' COMMENT '催收等级',
  `assign_time` datetime DEFAULT NULL COMMENT '分配时间',
  `finish_time` datetime DEFAULT NULL COMMENT '完结时间',
  `bad_time` datetime DEFAULT NULL COMMENT '坏账时间',
  `collection_time` datetime DEFAULT NULL COMMENT '首催时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `level` (`level`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收案件表';

-- ----------------------------
-- Table structure for collection_assign
-- ----------------------------
DROP TABLE IF EXISTS `collection_assign`;
CREATE TABLE `collection_assign` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT COMMENT '完结ID',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `collection_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收记录ID',
  `order_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收记录ID',
  `admin_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '案件催收员ID',
  `from_admin_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '来源催收员ID',
  `from_status` varchar(50) NOT NULL DEFAULT '' COMMENT '分配前案子状态',
  `to_status` varchar(50) NOT NULL DEFAULT '' COMMENT '分配后案子状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `setting_id` int(11) NOT NULL DEFAULT '0' COMMENT '分单配置id',
  `parent_id` bigint(16) DEFAULT NULL COMMENT '手动分单人id',
  `from_level` varchar(20) NOT NULL DEFAULT '' COMMENT '分配前案子等级',
  `level` varchar(20) NOT NULL DEFAULT '' COMMENT '分配后案子等级',
  `overdue_days` int(10) DEFAULT NULL COMMENT '当前逾期天数',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `collection_id` (`collection_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收分单表';

-- ----------------------------
-- Table structure for collection_contact
-- ----------------------------
DROP TABLE IF EXISTS `collection_contact`;
CREATE TABLE `collection_contact` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `order_id` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `user_id` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `collection_id` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `type` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '联系来源类型',
  `fullname` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '姓名',
  `contact` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '联系值（手机号）',
  `relation` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '关系',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `content` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `contact` (`contact`) USING BTREE,
  KEY `collection_id` (`collection_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收联系人表';

-- ----------------------------
-- Table structure for collection_deduction
-- ----------------------------
DROP TABLE IF EXISTS `collection_deduction`;
CREATE TABLE `collection_deduction` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `user_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '用户id',
  `repayment_plan_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '还款计划表id',
  `collection_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '用户id',
  `from_admin_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '当前案件归属人',
  `deduction_admin_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '操作人',
  `overdue_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '减免时逾期息费',
  `deduction` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '减免金额',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `collection_assign_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收分单ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `collection_id` (`collection_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收减免记录表';

-- ----------------------------
-- Table structure for collection_detail
-- ----------------------------
DROP TABLE IF EXISTS `collection_detail`;
CREATE TABLE `collection_detail` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `order_id` bigint(20) unsigned NOT NULL COMMENT '订单id',
  `collection_id` bigint(16) unsigned NOT NULL COMMENT '催收id',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `promise_paid_time` datetime DEFAULT NULL COMMENT '承诺还款时间',
  `record_time` datetime DEFAULT NULL COMMENT '催记时间',
  `dial` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '联系结果 （正常联系，无法联系...）',
  `progress` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '催收进度 （承诺还款，无意向...）',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `contact_num` int(11) NOT NULL DEFAULT '0' COMMENT '显示联系人数',
  `reduction_setting` varchar(20) NOT NULL DEFAULT '' COMMENT '减免设置',
  `contact` varchar(16) DEFAULT '' COMMENT '联系值（手机号）',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `collection_id` (`collection_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收案件表';

-- ----------------------------
-- Table structure for collection_finish
-- ----------------------------
DROP TABLE IF EXISTS `collection_finish`;
CREATE TABLE `collection_finish` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT COMMENT '完结ID',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `collection_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收记录ID',
  `order_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收记录ID',
  `admin_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '案件催收员ID',
  `from_status` varchar(50) NOT NULL DEFAULT '' COMMENT '完结前案子状态',
  `to_status` varchar(50) NOT NULL DEFAULT '' COMMENT '完结后案子状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `collection_assign_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收分单ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `collection_id` (`collection_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `assign_id` (`collection_assign_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='案件完结表';

-- ----------------------------
-- Table structure for collection_log
-- ----------------------------
DROP TABLE IF EXISTS `collection_log`;
CREATE TABLE `collection_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '催收日志id',
  `merchant_id` bigint(20) unsigned DEFAULT NULL COMMENT 'merchant_id',
  `user_id` bigint(20) unsigned DEFAULT '0' COMMENT '0表示系统，其他为用户的user_id',
  `order_id` bigint(20) unsigned DEFAULT '0' COMMENT '订单id',
  `collection_id` bigint(20) unsigned DEFAULT '0' COMMENT '催收id',
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员id',
  `from_status` varchar(50) CHARACTER SET utf8 DEFAULT '' COMMENT '更新前状态',
  `to_status` varchar(50) CHARACTER SET utf8 DEFAULT '' COMMENT '更新后状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `collection_assign_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收分单ID',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `collection_id` (`collection_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for collection_record
-- ----------------------------
DROP TABLE IF EXISTS `collection_record`;
CREATE TABLE `collection_record` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL COMMENT 'merchant_id',
  `collection_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收记录ID',
  `order_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `user_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `admin_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `fullname` varchar(64) CHARACTER SET utf8mb4 NOT NULL COMMENT '姓名',
  `relation` varchar(16) DEFAULT '' COMMENT '亲戚关系',
  `contact` varchar(16) DEFAULT '' COMMENT '联系值（手机号）',
  `promise_paid_time` datetime DEFAULT NULL COMMENT '承诺还款时间',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `dial` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '联系结果 （正常联系，无法联系...）',
  `progress` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '催收进度 （承诺还款，无意向...）',
  `from_status` varchar(50) NOT NULL DEFAULT '' COMMENT '催记前案子状态',
  `to_status` varchar(50) NOT NULL DEFAULT '' COMMENT '催记后案子状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `overdue_days` int(10) NOT NULL DEFAULT '0' COMMENT '当前逾期天数',
  `reduction_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当前减免金额',
  `level` varchar(32) NOT NULL DEFAULT '' COMMENT '当前催收等级',
  `receivable_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '当前应还金额',
  `collection_assign_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '催收分单ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `collection_id` (`collection_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催记表';

-- ----------------------------
-- Table structure for collection_setting
-- ----------------------------
DROP TABLE IF EXISTS `collection_setting`;
CREATE TABLE `collection_setting` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `admin_id` bigint(20) unsigned DEFAULT NULL COMMENT '操作人员id',
  `key` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `value` text NOT NULL COMMENT '值',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '配置状态',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `remark` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `key` (`key`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统配置表';

-- ----------------------------
-- Table structure for config_log
-- ----------------------------
DROP TABLE IF EXISTS `config_log`;
CREATE TABLE `config_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `staff_id` int(11) NOT NULL COMMENT '员工id',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作ip',
  `key` varchar(56) DEFAULT NULL COMMENT '配置key',
  `old_config` text COMMENT '旧配置',
  `new_config` text NOT NULL COMMENT '新配置',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for contract_agreement
-- ----------------------------
DROP TABLE IF EXISTS `contract_agreement`;
CREATE TABLE `contract_agreement` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT '0' COMMENT '合同id',
  `name` varchar(64) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `status` tinyint(2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='合同协议表';

-- ----------------------------
-- Table structure for date
-- ----------------------------
DROP TABLE IF EXISTS `date`;
CREATE TABLE `date` (
  `date` date NOT NULL COMMENT '日期',
  PRIMARY KEY (`date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for feedback
-- ----------------------------
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '反馈ID',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'feedback',
  `user_id` bigint(20) DEFAULT NULL COMMENT '用户ID',
  `type` varchar(50) DEFAULT '' COMMENT '类型',
  `content` varchar(512) NOT NULL COMMENT '内容',
  `telephone` varchar(13) DEFAULT NULL COMMENT '联系电话',
  `status` tinyint(2) DEFAULT NULL COMMENT '状态 1正常 0废弃',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `app_id` (`app_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户反馈记录';

-- ----------------------------
-- Table structure for feedback_faq
-- ----------------------------
DROP TABLE IF EXISTS `feedback_faq`;
CREATE TABLE `feedback_faq` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '反馈ID',
  `type` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '类型',
  `title` varchar(256) CHARACTER SET utf8mb4 NOT NULL COMMENT '标题',
  `content` varchar(512) CHARACTER SET utf8mb4 NOT NULL COMMENT '内容',
  `status` tinyint(2) DEFAULT NULL COMMENT '状态 1正常 0废弃',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for inbox
-- ----------------------------
DROP TABLE IF EXISTS `inbox`;
CREATE TABLE `inbox` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '私信id',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `title` varchar(256) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `read_time` datetime DEFAULT NULL COMMENT '读取时间',
  `status` tinyint(2) NOT NULL COMMENT '状态（未读、已读）',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE,
  KEY `app_id` (`app_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `jobs_queue_index` (`queue`(191)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for loan_multiple_config
-- ----------------------------
DROP TABLE IF EXISTS `loan_multiple_config`;
CREATE TABLE `loan_multiple_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `parent_id` bigint(20) NOT NULL COMMENT '父id',
  `number_start` int(10) NOT NULL COMMENT '起始复借次数',
  `number_end` int(10) NOT NULL COMMENT '结束复借次数',
  `loan_amount_range` text NOT NULL COMMENT '借款金额选项范围',
  `loan_days_range` text NOT NULL COMMENT '借款期限选项范围',
  `loan_daily_loan_rate` decimal(10,4) unsigned NOT NULL COMMENT '正常借款日息',
  `penalty_rate` decimal(10,4) unsigned NOT NULL COMMENT '滞纳金日息',
  `processing_rate` decimal(10,4) unsigned NOT NULL COMMENT '手续费费率',
  `loan_again_days` int(10) unsigned NOT NULL COMMENT '可重借天数(天)',
  `status` tinyint(4) NOT NULL COMMENT '状态 1:正常 -1:删除',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent_id` (`merchant_id`,`parent_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for loan_multiple_config_log
-- ----------------------------
DROP TABLE IF EXISTS `loan_multiple_config_log`;
CREATE TABLE `loan_multiple_config_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL COMMENT 'merchant_id',
  `staff_id` bigint(20) NOT NULL COMMENT 'staff_id',
  `ip` varchar(45) NOT NULL COMMENT '操作ip',
  `old_config` text COMMENT '旧配置集合json',
  `new_config` text COMMENT '新配置集合json',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for login_log
-- ----------------------------
DROP TABLE IF EXISTS `login_log`;
CREATE TABLE `login_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `client_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'client url',
  `staff_id` int(11) NOT NULL COMMENT '员工id',
  `login_type` tinyint(4) NOT NULL COMMENT '登录方式',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '登录ip',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for manual_approve_log
-- ----------------------------
DROP TABLE IF EXISTS `manual_approve_log`;
CREATE TABLE `manual_approve_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `admin_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `approve_type` tinyint(1) NOT NULL COMMENT '审批类型判断1初审2电审',
  `name` varchar(100) DEFAULT NULL,
  `from_order_status` varchar(30) DEFAULT NULL,
  `to_order_status` varchar(30) DEFAULT NULL,
  `result` text,
  `remark` varchar(200) DEFAULT NULL COMMENT '备注',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for merchant
-- ----------------------------
DROP TABLE IF EXISTS `merchant`;
CREATE TABLE `merchant` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_no` varchar(50) NOT NULL COMMENT '商户唯一编号',
  `merchant_name` varchar(255) DEFAULT '' COMMENT '商户名，公司名',
  `product_name` varchar(255) DEFAULT NULL COMMENT '产品名',
  `contact` varchar(100) DEFAULT NULL COMMENT '联系人姓名',
  `contact_telephone` varchar(16) DEFAULT NULL COMMENT '联系人手机号',
  `contact_email` varchar(20) DEFAULT NULL COMMENT '联系人邮箱',
  `contract_sign_date` date DEFAULT NULL COMMENT '合同签署日期',
  `contract_expiry_date` date DEFAULT NULL COMMENT '合同到期日',
  `status` tinyint(4) DEFAULT '1' COMMENT '商户状态 1：正常 2：禁用  -1：删除',
  `admin_nick` varchar(128) DEFAULT NULL COMMENT '关联商务(创建商户的管理员账号)',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `app_key` varchar(255) DEFAULT '' COMMENT '微服务app key',
  `app_secret_key` varchar(255) DEFAULT '' COMMENT '微服务app秘钥',
  `lender` varchar(150) DEFAULT NULL COMMENT '商户NBFC名称',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_no` (`merchant_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商户表';

-- ----------------------------
-- Table structure for message
-- ----------------------------
DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '信息类型(根据前端展示样式区分) 0:普通 1:succes 2:info 3:warning 4:error ',
  `send_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发送类型 1:系统',
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='管理后台站内信表';

-- ----------------------------
-- Table structure for message_user
-- ----------------------------
DROP TABLE IF EXISTS `message_user`;
CREATE TABLE `message_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `send_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `message_id` (`message_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='管理后台站内信-staff关联表';

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for nbfc_report_config
-- ----------------------------
DROP TABLE IF EXISTS `nbfc_report_config`;
CREATE TABLE `nbfc_report_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `platform` varchar(255) NOT NULL COMMENT '平台',
  `config` text NOT NULL COMMENT '配置 json',
  `status` tinyint(4) NOT NULL COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for nbfc_report_log
-- ----------------------------
DROP TABLE IF EXISTS `nbfc_report_log`;
CREATE TABLE `nbfc_report_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `type` varchar(20) NOT NULL COMMENT '类型 create:创建  remit:放款',
  `platform` varchar(100) NOT NULL COMMENT '平台',
  `step` varchar(100) NOT NULL COMMENT '步骤',
  `request` mediumtext NOT NULL COMMENT '请求内容',
  `response` mediumtext NOT NULL COMMENT '响应内容',
  `created_at` datetime NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for notice
-- ----------------------------
DROP TABLE IF EXISTS `notice`;
CREATE TABLE `notice` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '通知id',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `platform` varchar(8) NOT NULL COMMENT '平台：all、ios、android',
  `title` varchar(256) NOT NULL COMMENT '标题',
  `tags` varchar(255) DEFAULT '' COMMENT '标签（urgent 紧急公告 normal一般公告）',
  `content` text NOT NULL COMMENT '内容',
  `read_total` int(11) NOT NULL COMMENT '阅读量',
  `status` tinyint(4) NOT NULL COMMENT '状态(1待发送 2已发送 3已删除)',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '创建时间',
  `pushed_at` datetime NOT NULL COMMENT '推送时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `platform` (`platform`) USING BTREE,
  KEY `app_id` (`app_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for order
-- ----------------------------
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `merchant_id` bigint(20) unsigned DEFAULT NULL COMMENT 'merchant_id',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `order_no` varchar(64) DEFAULT '' COMMENT '订单编号',
  `user_id` bigint(20) unsigned DEFAULT '0' COMMENT '用户id',
  `principal` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '贷款金额',
  `loan_days` int(10) unsigned DEFAULT '0' COMMENT '贷款天数',
  `status` varchar(50) DEFAULT '' COMMENT '订单状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `app_client` varchar(16) DEFAULT '' COMMENT 'APP终端',
  `app_version` varchar(16) DEFAULT '' COMMENT 'APP版本号',
  `quality` tinyint(2) unsigned DEFAULT '0' COMMENT '新老用户 0新用户 1老用户',
  `daily_rate` decimal(11,5) unsigned NOT NULL DEFAULT '0.00000' COMMENT '日利率',
  `overdue_rate` decimal(11,5) unsigned NOT NULL DEFAULT '0.00000' COMMENT '逾期费率',
  `signed_time` datetime DEFAULT NULL COMMENT '签约时间',
  `system_time` datetime DEFAULT NULL COMMENT '机审结束时间',
  `manual_time` datetime DEFAULT NULL COMMENT '人审结束时间',
  `paid_time` datetime DEFAULT NULL COMMENT '放款时间',
  `paid_amount` decimal(10,2) DEFAULT NULL COMMENT '实际放款金额',
  `pay_channel` varchar(64) DEFAULT '' COMMENT '放款渠道',
  `cancel_time` datetime DEFAULT NULL COMMENT '取消时间',
  `pass_time` datetime DEFAULT NULL COMMENT '审批通过时间',
  `overdue_time` datetime DEFAULT NULL COMMENT '转逾期时间',
  `bad_time` datetime DEFAULT NULL COMMENT '转坏账时间',
  `approve_push_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审批推送状态 0 不需要推送 1 未推送 2 已推送',
  `manual_check` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要初审, 1需要, 2不需要',
  `call_check` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要电审,1需要，2不需要',
  `reject_time` datetime DEFAULT NULL COMMENT '被拒时间',
  `manual_result` tinyint(4) DEFAULT '0' COMMENT '人审通过结果',
  `call_result` tinyint(4) DEFAULT '0' COMMENT '电审通过结果',
  `auth_process` varchar(20) DEFAULT NULL COMMENT '认证过程，所属分流',
  `nbfc_report_status` tinyint(4) DEFAULT '0' COMMENT 'NBFC上报状态 0:无需上报 1:未上报 2:已上报',
  `flag` varchar(64) DEFAULT '' COMMENT '特殊订单标记',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_no` (`order_no`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `app_id` (`app_id`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for order_detail
-- ----------------------------
DROP TABLE IF EXISTS `order_detail`;
CREATE TABLE `order_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `key` varchar(64) DEFAULT '' COMMENT '键',
  `value` varchar(256) DEFAULT '' COMMENT '值',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `key` (`key`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `order_id, key` (`order_id`,`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for order_log
-- ----------------------------
DROP TABLE IF EXISTS `order_log`;
CREATE TABLE `order_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单日志id',
  `merchant_id` bigint(20) unsigned DEFAULT NULL COMMENT 'merchant_id',
  `user_id` bigint(20) unsigned DEFAULT '0' COMMENT '0表示系统，其他为用户的user_id',
  `order_id` bigint(20) unsigned DEFAULT '0' COMMENT '订单id',
  `name` varchar(16) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '比如：创建create，提交submit，取消cancel，删除delete，系统拒绝system_reject',
  `content` varchar(128) CHARACTER SET utf8mb4 DEFAULT '',
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员id',
  `from_status` varchar(50) DEFAULT '' COMMENT '更新前状态',
  `to_status` varchar(50) DEFAULT '' COMMENT '更新后状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for order_sign_doc
-- ----------------------------
DROP TABLE IF EXISTS `order_sign_doc`;
CREATE TABLE `order_sign_doc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'digio的docId',
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '订单id',
  `type` varchar(10) COLLATE utf8_bin NOT NULL COMMENT '签约渠道类型',
  `doc_id` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `doc_url` text COLLATE utf8_bin NOT NULL COMMENT '文档地址',
  `contract_agreement_id` bigint(20) NOT NULL COMMENT '文档下载后关联contract_agreement表',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1:正常 2:已失效',
  `created_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `doc_id` (`doc_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for pincode
-- ----------------------------
DROP TABLE IF EXISTS `pincode`;
CREATE TABLE `pincode` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pincode` varchar(10) DEFAULT NULL,
  `districtname` varchar(100) DEFAULT NULL,
  `statename` varchar(100) DEFAULT NULL COMMENT '邦 | 直辖区',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 从官方提供数据录入 1 手工录入(补充)',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pincode` (`pincode`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for profession
-- ----------------------------
DROP TABLE IF EXISTS `profession`;
CREATE TABLE `profession` (
  `occupation` varchar(100) DEFAULT NULL,
  `profession` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for rbac_configs
-- ----------------------------
DROP TABLE IF EXISTS `rbac_configs`;
CREATE TABLE `rbac_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `project` varchar(255) NOT NULL COMMENT '项目名称',
  `guard_name` varchar(255) NOT NULL COMMENT '模块',
  `config` varchar(1000) NOT NULL COMMENT '配置信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_menus
-- ----------------------------
DROP TABLE IF EXISTS `rbac_menus`;
CREATE TABLE `rbac_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'path guard + 所有父节点path',
  `route` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'route 对应前端路由',
  `parent_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '父Id',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '菜单名称',
  `is_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否显示菜单 1显示 0不显示 默认显示',
  `type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '菜单类型 1项目菜单 2子项目微服务菜单',
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '守卫,区分模块.比如:前端(web),后端(admin).',
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `sort` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`path`) USING BTREE,
  UNIQUE KEY `rbac_menus_guard_name_path_unique` (`guard_name`,`path`) USING BTREE,
  KEY `rbac_menus_name_index` (`name`) USING BTREE,
  KEY `index` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for rbac_model_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `rbac_model_has_permissions`;
CREATE TABLE `rbac_model_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`) USING BTREE,
  KEY `rbac_model_has_permissions_model_type_model_id_index` (`model_type`,`model_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_model_has_roles
-- ----------------------------
DROP TABLE IF EXISTS `rbac_model_has_roles`;
CREATE TABLE `rbac_model_has_roles` (
  `role_id` int(10) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`) USING BTREE,
  KEY `rbac_model_has_roles_model_type_model_id_index` (`model_type`,`model_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_operation_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `rbac_operation_has_permissions`;
CREATE TABLE `rbac_operation_has_permissions` (
  `operation_id` int(10) unsigned NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  PRIMARY KEY (`operation_id`,`permission_key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_operations
-- ----------------------------
DROP TABLE IF EXISTS `rbac_operations`;
CREATE TABLE `rbac_operations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `menu_key` varchar(255) DEFAULT NULL COMMENT '关联菜单path',
  `guard_name` varchar(50) NOT NULL,
  `remark` varchar(255) DEFAULT '' COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_permissions
-- ----------------------------
DROP TABLE IF EXISTS `rbac_permissions`;
CREATE TABLE `rbac_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `remark` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '请求方式+路由',
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '守卫,区分模块.比如:前端(web),后端(admin).',
  `menu_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '菜单对应key',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`name`,`guard_name`) USING BTREE,
  KEY `rbac_permissions_guard_name_index` (`guard_name`) USING BTREE,
  KEY `rbac_permission_id_index` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_role_has_menus
-- ----------------------------
DROP TABLE IF EXISTS `rbac_role_has_menus`;
CREATE TABLE `rbac_role_has_menus` (
  `menu_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`menu_key`,`role_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_role_has_operations
-- ----------------------------
DROP TABLE IF EXISTS `rbac_role_has_operations`;
CREATE TABLE `rbac_role_has_operations` (
  `role_id` int(11) NOT NULL,
  `operation_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`operation_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_role_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `rbac_role_has_permissions`;
CREATE TABLE `rbac_role_has_permissions` (
  `permission_key` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_key`,`role_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_roles
-- ----------------------------
DROP TABLE IF EXISTS `rbac_roles`;
CREATE TABLE `rbac_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `remark` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '描述',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '角色名称',
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '守卫,区分模块.比如:前端(web),后端(admin).',
  `is_super` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否超管权限',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rbac_roles_guard_name_index` (`guard_name`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for rbac_users
-- ----------------------------
DROP TABLE IF EXISTS `rbac_users`;
CREATE TABLE `rbac_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL,
  `sso_uid` int(11) NOT NULL COMMENT 'sso 登录 用户id',
  `company_id` int(11) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `nick_name` varchar(50) DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for refresh_token
-- ----------------------------
DROP TABLE IF EXISTS `refresh_token`;
CREATE TABLE `refresh_token` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `user_id` bigint(20) DEFAULT NULL COMMENT '用户id',
  `token` varchar(40) NOT NULL COMMENT '刷新令牌',
  `client_id` varchar(80) NOT NULL COMMENT '客户端id',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `expired_time` datetime NOT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `refresh_token` (`token`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for repayment_plan
-- ----------------------------
DROP TABLE IF EXISTS `repayment_plan`;
CREATE TABLE `repayment_plan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `user_id` bigint(20) unsigned DEFAULT '0' COMMENT '用户id',
  `order_id` bigint(20) unsigned DEFAULT '0' COMMENT '订单id',
  `no` varchar(128) DEFAULT '' COMMENT '还款编号',
  `status` int(10) DEFAULT NULL COMMENT '还款状态',
  `overdue_days` int(10) unsigned DEFAULT '0' COMMENT '逾期天数',
  `appointment_paid_time` datetime DEFAULT NULL COMMENT '应还款时间',
  `repay_time` datetime DEFAULT NULL COMMENT '还款时间',
  `repay_amount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '实际还款金额',
  `repay_channel` varchar(64) DEFAULT '' COMMENT '还款渠道',
  `reduction_fee` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '减免金额',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `reduction_valid_date` varchar(100) DEFAULT '' COMMENT '减免有效期',
  `principal` decimal(10,2) DEFAULT '0.00' COMMENT '实还本金',
  `interest_fee` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '实还综合费用',
  `overdue_fee` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '实还罚息',
  `gst_processing` decimal(10,2) unsigned DEFAULT '0.00' COMMENT 'GST手续费',
  `gst_penalty` decimal(10,2) unsigned DEFAULT '0.00' COMMENT 'GST逾期费',
  `installment_num` int(4) NOT NULL DEFAULT '1' COMMENT '当前期数，默认为1',
  `repay_proportion` decimal(10,2) unsigned DEFAULT '100.00' COMMENT '当期还款比例',
  `repay_days` int(8) DEFAULT '0' COMMENT '当期还款天数',
  `loan_days` int(8) DEFAULT NULL COMMENT '当期借款天数',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `no` (`no`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `appointment_paid_time` (`appointment_paid_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for repayment_plan_renewal
-- ----------------------------
DROP TABLE IF EXISTS `repayment_plan_renewal`;
CREATE TABLE `repayment_plan_renewal` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `order_id` bigint(20) unsigned NOT NULL COMMENT '订单id',
  `repayment_plan_id` bigint(20) unsigned NOT NULL COMMENT '还款计划表id',
  `renewal_days` int(10) unsigned NOT NULL COMMENT '续期天数',
  `issue` int(10) unsigned NOT NULL COMMENT '续期期数(次数)',
  `renewal_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '续期费用(续期时收取总费用) = 续期息费+当期逾期息费 = (借款本金*续期天数*续期费率(x%)) + 逾期息费',
  `renewal_interest` decimal(10,2) unsigned NOT NULL COMMENT '续期息费 = 借款本金*续期天数*续期费率(x%)',
  `rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '续期费率',
  `overdue_days` tinyint(10) unsigned NOT NULL COMMENT '逾期天数  续期时的逾期天数',
  `overdue_interest` decimal(10,2) NOT NULL COMMENT '逾期息费',
  `extends_appointment_paid_time` datetime NOT NULL COMMENT '应还款时间 放款日期+合同借款期限+逾期天数+续期天数',
  `appointment_paid_time_log` datetime NOT NULL COMMENT '续期前应还款日期log记录',
  `status` tinyint(4) NOT NULL COMMENT '状态  1:创建  2:续期成功',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `repayment_plan_id_index` (`repayment_plan_id`) USING BTREE,
  KEY `order_id_status_index` (`order_id`,`status`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='续期表';

-- ----------------------------
-- Table structure for request_packet
-- ----------------------------
DROP TABLE IF EXISTS `request_packet`;
CREATE TABLE `request_packet` (
  `id` bigint(13) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `relate_id` bigint(13) NOT NULL COMMENT '关联ID',
  `relate_type` varchar(128) NOT NULL COMMENT '关联类型',
  `type` tinyint(4) NOT NULL COMMENT '报文类型',
  `url` text COMMENT '请求url',
  `request_info` text COMMENT '请求报文',
  `response_info` mediumtext COMMENT '响应报文',
  `http_code` varchar(10) DEFAULT NULL COMMENT 'http状态码',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `relate_id_type` (`relate_id`,`relate_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='请求报文记录表';

-- ----------------------------
-- Table structure for staff
-- ----------------------------
DROP TABLE IF EXISTS `staff`;
CREATE TABLE `staff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '昵称',
  `password_hash` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `ding_openid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '钉钉openid',
  `wechat_openid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '微信openid',
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'email',
  `last_login_time_old` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '最后登录IP',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  `created_at_old` bigint(20) unsigned NOT NULL,
  `updated_at_old` bigint(20) unsigned NOT NULL,
  `last_update_pwd_time_old` bigint(16) NOT NULL DEFAULT '0' COMMENT '上次更新密码',
  `is_need_update_pwd` tinyint(4) NOT NULL DEFAULT '0' COMMENT '需要更新密码',
  `is_need_ding_login` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否需要钉钉登录，1:需要，0:不需要',
  `ding_unionid` varchar(100) NOT NULL DEFAULT '' COMMENT '钉钉unionid',
  `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '密码',
  `is_super` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否超级管理员账号',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_update_pwd_time` datetime DEFAULT NULL COMMENT '上次更新密码',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for statistics_collection
-- ----------------------------
DROP TABLE IF EXISTS `statistics_collection`;
CREATE TABLE `statistics_collection` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `date` date NOT NULL COMMENT '统计日期',
  `overdue_count` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '累计订单数  历史进入过逾期状态总订单数（逾期订单累计统计）',
  `overdue_finish_count` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '累计成功数  历史进入过“逾期结清”状态订单数累计统计',
  `collection_bad_count` int(13) NOT NULL DEFAULT '0' COMMENT '累计坏账数  历史进入过“已坏账”状态订单数累计统计',
  `collection_success_count_rate` decimal(6,2) NOT NULL DEFAULT '0.00' COMMENT '累计回款率  [累计成功数]/[累计订单数]',
  `today_overdue` int(13) NOT NULL DEFAULT '0' COMMENT '今日新增订单数  今天“已逾期”状态订单数累计统计',
  `today_promise_paid` int(13) NOT NULL DEFAULT '0' COMMENT '今日承诺还款订单数  今天“承诺还款”催收状态订单数累计统计',
  `today_overdue_finish` int(13) NOT NULL DEFAULT '0' COMMENT '今日成功订单数  今天进入“逾期结清”状态订单数累计统计',
  `today_collection_bad` int(13) NOT NULL DEFAULT '0' COMMENT '今日坏账订单数  今天进入“已坏账”状态订单数累计统计',
  `real_overdue_finish` int(13) NOT NULL DEFAULT '0' COMMENT '截止当前回款成功数  当前处于“逾期结清”状态订单数累计统计',
  `collection_success_rate` decimal(6,2) NOT NULL DEFAULT '0.00' COMMENT '今日催回率  [今日成功订单数]/[今日新增订单数]',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `statistics_collection_date_index` (`date`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收订单统计表';

-- ----------------------------
-- Table structure for statistics_collection_rate
-- ----------------------------
DROP TABLE IF EXISTS `statistics_collection_rate`;
CREATE TABLE `statistics_collection_rate` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `date` date NOT NULL COMMENT '统计日期',
  `history_overdue_count` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '逾期总订单数(包含今天) 历史逾期状态总订单数',
  `present_overdue_count` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '当前逾期订单数 当前“已逾期”状态订单数累计统计',
  `today_overdue_finish` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '当日回款成功数  今天“逾期结清”状态订单数累计统计',
  `present_overdue_finish_count` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '当前回款成功数  当前“逾期结清”状态订单数累计统计',
  `today_collection_bad_count` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '坏账数  今日坏账订单累计统计',
  `history_collection_bad_count` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '历史坏账数(包含今天)  历史坏账订单累计统计',
  `today_collection_rate` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当日回款率  [当日回款成功数]/([当前逾期订单数]+[当日回款成功数])',
  `history_collection_rate` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '截止当前回款率  [截止当前回款成功数]/[逾期总订单数]',
  `collection_count` int(13) unsigned NOT NULL DEFAULT '0' COMMENT '今天催收记录总次数累计统计',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间 ',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `statistics_collection_rate_date_index` (`date`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催回率统计表';

-- ----------------------------
-- Table structure for statistics_collection_staff
-- ----------------------------
DROP TABLE IF EXISTS `statistics_collection_staff`;
CREATE TABLE `statistics_collection_staff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `date` date NOT NULL COMMENT '统计日期',
  `staff_id` bigint(20) unsigned NOT NULL COMMENT '催收人员 id',
  `staff_name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '催收员姓名  ',
  `allot_order` int(13) NOT NULL DEFAULT '0' COMMENT '今日新增订单数   今天已分配催收单数累计统计',
  `promise_paid` int(13) NOT NULL DEFAULT '0' COMMENT '今日承诺还款订单数   今天“承诺还款”催收状态订单数累计统计',
  `overdue_finish` int(13) NOT NULL DEFAULT '0' COMMENT '今日成功订单数   今天“逾期结清”状态订单数累计统计',
  `collection_bad` int(13) NOT NULL DEFAULT '0' COMMENT '今日坏账订单数   今天“已坏账”状态订单数累计统计',
  `collection_success_rate` decimal(6,2) NOT NULL DEFAULT '0.00' COMMENT '今日催回率   [今日成功订单数]/[今日新增订单数]',
  `collection_count` int(13) NOT NULL DEFAULT '0' COMMENT '今日催收次数  今天催收记录总次数累计统计',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收员每日统计表';

-- ----------------------------
-- Table structure for statistics_data
-- ----------------------------
DROP TABLE IF EXISTS `statistics_data`;
CREATE TABLE `statistics_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `date` date NOT NULL COMMENT '统计日期',
  `statistics` varchar(30) NOT NULL DEFAULT '' COMMENT '统计',
  `count` int(10) NOT NULL DEFAULT '0',
  `quota` decimal(14,4) NOT NULL COMMENT '数值',
  `client_id` varchar(16) NOT NULL DEFAULT '' COMMENT '注册终端 Android/ios/h5',
  `quality` char(4) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '用户质量 1为老用户 0为新用户 2为当天注册用户',
  `channel_id` varchar(16) NOT NULL DEFAULT '' COMMENT '注册渠道',
  `platform` varchar(16) NOT NULL DEFAULT '' COMMENT '下载平台',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '统计记录状态 1:启用 -1:删除',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `statistics_collection_date_index` (`date`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收订单统计表';

-- ----------------------------
-- Table structure for statistics_log
-- ----------------------------
DROP TABLE IF EXISTS `statistics_log`;
CREATE TABLE `statistics_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `user_id` bigint(20) unsigned DEFAULT '0' COMMENT '用户id',
  `statistics` varchar(30) NOT NULL DEFAULT '' COMMENT '统计',
  `quota` decimal(14,4) NOT NULL COMMENT '数值',
  `client_id` varchar(16) NOT NULL DEFAULT '' COMMENT '注册终端 Android/ios/h5',
  `quality` char(4) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '用户质量 1为老用户 0为新用户 2为当天注册用户',
  `channel_id` varchar(16) NOT NULL DEFAULT '' COMMENT '注册渠道',
  `platform` varchar(16) NOT NULL DEFAULT '' COMMENT '下载平台',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '统计记录状态 1:启用 -1:删除',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_time` datetime DEFAULT NULL COMMENT '统计日期',
  `created_date` date DEFAULT NULL COMMENT '统计日期',
  `created_hour` tinyint(2) NOT NULL DEFAULT '0' COMMENT '创建时间(小时)',
  `created_day` tinyint(4) NOT NULL DEFAULT '0' COMMENT '创建时间(天)',
  `created_week` tinyint(4) NOT NULL DEFAULT '0' COMMENT '创建时间(周)',
  `created_month` tinyint(4) NOT NULL DEFAULT '0' COMMENT '创建时间(月)',
  `created_year` int(4) NOT NULL DEFAULT '0' COMMENT '创建时间(年)',
  `user_register_time` datetime DEFAULT NULL,
  `user_register_date` date DEFAULT NULL,
  `user_register_hour` tinyint(2) NOT NULL DEFAULT '0' COMMENT '创建时间(小时)',
  `user_register_day` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户注册日期',
  `user_register_week` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户注册日期(周)',
  `user_register_month` tinyint(2) NOT NULL DEFAULT '0' COMMENT '用户注册日期(月份)',
  `user_register_year` int(4) NOT NULL DEFAULT '0' COMMENT '用户注册日期(年)',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `statistics_collection_date_index` (`created_time`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='催收订单统计表';

-- ----------------------------
-- Table structure for system_approve_task
-- ----------------------------
DROP TABLE IF EXISTS `system_approve_task`;
CREATE TABLE `system_approve_task` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL COMMENT '商户id',
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `order_id` bigint(20) NOT NULL COMMENT '订单id',
  `user_type` tinyint(4) NOT NULL COMMENT '用户类型 0:新用户  1:老用户',
  `task_no` varchar(255) DEFAULT NULL COMMENT '任务编号',
  `status` varchar(255) NOT NULL COMMENT '状态',
  `result` varchar(16) NOT NULL DEFAULT '' COMMENT '结果 ',
  `hit_rule_cnt` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'hit_rule命中数',
  `hit_rule` text COMMENT '命中拒绝规则code',
  `extra_code` varchar(64) DEFAULT NULL COMMENT '额外标识',
  `description` varchar(255) NOT NULL COMMENT '注释说明',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for test_user
-- ----------------------------
DROP TABLE IF EXISTS `test_user`;
CREATE TABLE `test_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '测试账号ID',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '用户ID',
  `user_type` tinyint(2) DEFAULT '0' COMMENT '账号类型 0虚拟账号 1真实账号',
  `status` tinyint(2) unsigned DEFAULT '1' COMMENT '账号状态 1正常 0禁用',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for third_experian
-- ----------------------------
DROP TABLE IF EXISTS `third_experian`;
CREATE TABLE `third_experian` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(255) NOT NULL COMMENT '批次编号',
  `type` varchar(128) DEFAULT NULL COMMENT '类型',
  `telephone` varchar(16) DEFAULT NULL COMMENT '手机号',
  `request_info` text COMMENT '请求信息',
  `report_body` mediumtext COMMENT '报告主体',
  `response_info` mediumtext COMMENT '响应原文',
  `status` varchar(255) DEFAULT NULL COMMENT '响应状态',
  `extra_info` text,
  `remark` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `telephone` (`telephone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='征信跑批数据存储';

-- ----------------------------
-- Table structure for third_party_log
-- ----------------------------
DROP TABLE IF EXISTS `third_party_log`;
CREATE TABLE `third_party_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `system_send_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '对应系统认证记录唯一标识',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `name` varchar(50) DEFAULT NULL,
  `channel` varchar(50) DEFAULT NULL COMMENT '调用渠道',
  `user_id` bigint(20) DEFAULT NULL,
  `report_id` varchar(60) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `request` text,
  `request_url` varchar(255) DEFAULT NULL,
  `request_status` tinyint(4) DEFAULT NULL,
  `response` text,
  `response_time` datetime DEFAULT NULL,
  `callback` text,
  `callback_url` varchar(255) DEFAULT NULL,
  `callback_time` datetime DEFAULT NULL,
  `callback_status` tinyint(4) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `name` (`name`) USING BTREE,
  KEY `report_id` (`report_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for third_party_verify
-- ----------------------------
DROP TABLE IF EXISTS `third_party_verify`;
CREATE TABLE `third_party_verify` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `result` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `report_id` varchar(100) DEFAULT NULL,
  `source_id` varchar(20) DEFAULT NULL,
  `source_val` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `report_id` (`report_id`) USING BTREE,
  KEY `source_id` (`source_id`) USING BTREE,
  KEY `source_val` (`source_val`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for third_report
-- ----------------------------
DROP TABLE IF EXISTS `third_report`;
CREATE TABLE `third_report` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) NOT NULL COMMENT '商户ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `type` tinyint(4) NOT NULL COMMENT '类型 1:征信报告 ',
  `channel` varchar(50) NOT NULL COMMENT '调用渠道  HighMark',
  `status` tinyint(4) DEFAULT NULL COMMENT '状态 1:正常',
  `request_info` mediumtext COMMENT '请求信息',
  `report_body` mediumtext COMMENT '报告主体',
  `report_id` varchar(100) DEFAULT NULL COMMENT '报告id',
  `unique_no` varchar(100) DEFAULT NULL COMMENT '唯一标识号',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `report_id` (`report_id`) USING BTREE,
  KEY `unique_no` (`unique_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='第三方报告表';

-- ----------------------------
-- Table structure for trade_log
-- ----------------------------
DROP TABLE IF EXISTS `trade_log`;
CREATE TABLE `trade_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '交易记录自增长id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `admin_trade_account_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '关联 admin_pay_account',
  `trade_platform` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '交易平台',
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `bank_card_id` bigint(20) NOT NULL COMMENT '银行卡id',
  `master_related_id` bigint(20) NOT NULL COMMENT '业务主关联id (订单id)',
  `master_business_no` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '主业务编号(订单编号)',
  `batch_no` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '交易批次号',
  `transaction_no` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '交易编号(生成唯一编号)',
  `request_no` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '支付系统定义支付请求编号',
  `trade_platform_no` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '第三方交易平台交易编号',
  `business_type` varchar(100) CHARACTER SET utf8mb4 NOT NULL COMMENT '业务类型 manual_remit:人工出款  repay:还款',
  `trade_type` tinyint(4) NOT NULL COMMENT '交易类型( 1:出款, 2:回款, 3:退款)',
  `request_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发起类型 1:系统  2:管理后台 3:用户 ',
  `business_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '业务金额',
  `trade_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '交易金额',
  `bank_name` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '银行名称',
  `trade_account_telephone` varchar(13) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '交易账户手机号(银行卡手机号、支付宝手机号)',
  `trade_account_name` varchar(255) CHARACTER SET utf8mb4 NOT NULL COMMENT '交易账户名(银行卡账户、支付宝姓名)',
  `trade_account_no` varchar(60) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '交易账户号(用户银行卡、支付宝账户)',
  `trade_desc` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '交易描述',
  `trade_result_time` datetime DEFAULT NULL COMMENT '交易时间',
  `trade_notice_time` datetime DEFAULT NULL COMMENT '交易通知时间',
  `trade_settlement_time` datetime DEFAULT NULL COMMENT '清算时间',
  `trade_request_time` datetime DEFAULT NULL COMMENT '请求时间',
  `p_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '父id,退款用',
  `admin_id` bigint(20) NOT NULL COMMENT '后台管理员id',
  `handle_name` varchar(100) CHARACTER SET utf8mb4 NOT NULL COMMENT '操作者名称',
  `trade_evolve_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '交易进展状态',
  `trade_result` tinyint(4) NOT NULL DEFAULT '0' COMMENT '交易结果(1成功,2失败)',
  `trade_result_code` varchar(100) DEFAULT NULL COMMENT '交易结果CODE',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `transaction_no` (`transaction_no`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `master_related_id` (`master_related_id`) USING BTREE,
  KEY `business_flag` (`business_type`) USING BTREE,
  KEY `trade_type` (`trade_type`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC COMMENT='交易记录表';

-- ----------------------------
-- Table structure for trade_log_detail
-- ----------------------------
DROP TABLE IF EXISTS `trade_log_detail`;
CREATE TABLE `trade_log_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '交易详情记录id',
  `trade_log_id` bigint(20) NOT NULL COMMENT '关联主交易记录id',
  `business_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '业务id(合同id,还款计划id)',
  `business_no` varchar(100) NOT NULL DEFAULT '' COMMENT '业务编号(会员编号,还款计划编号,,,)',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `trade_log_id` (`trade_log_id`) USING BTREE,
  KEY `business_id` (`business_id`) USING BTREE,
  KEY `business_no` (`business_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for upload
-- ----------------------------
DROP TABLE IF EXISTS `upload`;
CREATE TABLE `upload` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '用途类型',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态-1:已删除 1:正常',
  `source_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
  `filename` varchar(128) NOT NULL DEFAULT '' COMMENT '文件名称',
  `path` varchar(128) NOT NULL DEFAULT '' COMMENT '文件地址',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `ext_info` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展信息',
  `user_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '来源类型 1:APP 2:管理后台',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `telephone` varchar(16) DEFAULT '' COMMENT '手机号码',
  `fullname` varchar(100) DEFAULT '' COMMENT '姓名',
  `id_card_no` varchar(19) DEFAULT '' COMMENT '身份证号',
  `status` tinyint(2) unsigned DEFAULT '1' COMMENT '状态 1正常 2禁用',
  `created_at` datetime DEFAULT NULL COMMENT '注册时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `platform` varchar(16) DEFAULT '' COMMENT '下载平台',
  `client_id` varchar(16) DEFAULT '' COMMENT '注册终端 Android/ios/h5',
  `channel_id` varchar(16) DEFAULT '' COMMENT '注册渠道',
  `quality` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '新老用户 0新用户 1老用户',
  `quality_time` datetime DEFAULT NULL COMMENT '转老用户时间',
  `app_version` varchar(10) NOT NULL DEFAULT '' COMMENT '注册版本',
  `api_token` varchar(500) DEFAULT '' COMMENT '令牌token',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `app_id,telephone` (`app_id`,`telephone`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `app_version` (`app_version`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户表';

-- ----------------------------
-- Table structure for user_application
-- ----------------------------
DROP TABLE IF EXISTS `user_application`;
CREATE TABLE `user_application` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `app_name` varchar(255) DEFAULT NULL COMMENT '应用名',
  `pkg_name` varchar(255) DEFAULT NULL COMMENT '包名',
  `installed_time` bigint(13) DEFAULT NULL COMMENT '应用安装时间',
  `updated_time` bigint(13) DEFAULT NULL COMMENT '应用的最后更新时间',
  `version` int(10) NOT NULL DEFAULT '0' COMMENT '版本号，用于区分批次',
  `created_at` datetime DEFAULT NULL COMMENT '记录添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id_app_id` (`user_id`) USING BTREE,
  KEY `user_id` (`user_id`,`pkg_name`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户安装应用表';

-- ----------------------------
-- Table structure for user_auth
-- ----------------------------
DROP TABLE IF EXISTS `user_auth`;
CREATE TABLE `user_auth` (
  `id` bigint(13) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户信息扩展自增长ID',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `user_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '用户id',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '用户数据类型',
  `time` datetime DEFAULT NULL COMMENT '用户数据时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `auth_status` tinyint(4) NOT NULL DEFAULT '0',
  `quality` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '新老用户 0新用户 1老用户',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `user_id_type` (`user_id`,`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `auth_status` (`auth_status`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for user_auth_black
-- ----------------------------
DROP TABLE IF EXISTS `user_auth_black`;
CREATE TABLE `user_auth_black` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT '商户ID',
  `user_id` bigint(16) NOT NULL DEFAULT '0' COMMENT '用户id',
  `type` varchar(20) NOT NULL DEFAULT '类型',
  `receive` varchar(20) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `black_time` datetime DEFAULT NULL COMMENT '黑名单生效时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for user_black
-- ----------------------------
DROP TABLE IF EXISTS `user_black`;
CREATE TABLE `user_black` (
  `id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT '商户ID',
  `telephone` varchar(20) NOT NULL DEFAULT '',
  `hit_value` varchar(128) NOT NULL COMMENT '命中黑名单值',
  `hit_type` varchar(32) DEFAULT NULL COMMENT '命中黑名单类型',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '禁用类型',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `black_time` datetime DEFAULT NULL COMMENT '黑名单生效时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `telephone` (`telephone`) USING BTREE,
  KEY `hit_value` (`hit_value`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for user_contact
-- ----------------------------
DROP TABLE IF EXISTS `user_contact`;
CREATE TABLE `user_contact` (
  `id` bigint(13) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户紧急联系人自增长ID',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `user_id` bigint(13) NOT NULL COMMENT '用户ID',
  `contact_fullname` varchar(30) NOT NULL COMMENT '紧急联系人名字',
  `contact_telephone` varchar(32) NOT NULL COMMENT '紧急联系人电话',
  `relation` varchar(64) DEFAULT NULL COMMENT '紧急联系人关系(直系亲属,朋友,兄弟姐妹)',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `check_status` varchar(16) DEFAULT '' COMMENT '空号检测结果',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '修改时间',
  `times_contacted` int(10) DEFAULT NULL COMMENT '联系人联系次数',
  `last_time_contacted` bigint(13) DEFAULT NULL COMMENT '最近联系时间',
  `has_phone_number` varchar(16) DEFAULT NULL COMMENT '是否有号码',
  `starred` tinyint(2) DEFAULT NULL COMMENT '是否收藏',
  `contact_last_updated_timestamp` bigint(13) DEFAULT NULL COMMENT '联系人最后编辑时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户紧急联系人表';

-- ----------------------------
-- Table structure for user_contacts_telephone
-- ----------------------------
DROP TABLE IF EXISTS `user_contacts_telephone`;
CREATE TABLE `user_contacts_telephone` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(13) NOT NULL,
  `contact_fullname` varchar(64) DEFAULT NULL,
  `contact_telephone` varchar(32) DEFAULT NULL,
  `relation_level` tinyint(2) NOT NULL DEFAULT '0' COMMENT '亲戚关系(0无亲戚关系,1近亲,2近亲模糊匹配,3远亲)',
  `created_at` datetime DEFAULT NULL COMMENT '添加时间',
  `times_contacted` int(10) DEFAULT NULL COMMENT '联系人联系次数',
  `last_time_contacted` bigint(13) DEFAULT NULL COMMENT '最近联系时间',
  `has_phone_number` varchar(16) DEFAULT NULL COMMENT '是否有号码',
  `starred` tinyint(2) DEFAULT NULL COMMENT '是否收藏',
  `contact_last_updated_timestamp` bigint(13) DEFAULT NULL COMMENT '联系人最后编辑时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`,`contact_telephone`) USING BTREE,
  KEY `user_id_app_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户通讯录信息';

-- ----------------------------
-- Table structure for user_info
-- ----------------------------
DROP TABLE IF EXISTS `user_info`;
CREATE TABLE `user_info` (
  `id` bigint(13) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `user_id` bigint(13) NOT NULL COMMENT '用户id',
  `gender` varchar(10) CHARACTER SET utf8mb4 NOT NULL COMMENT '性别:男,女',
  `province` varchar(50) NOT NULL DEFAULT '' COMMENT '省',
  `city` varchar(50) NOT NULL DEFAULT '' COMMENT '市',
  `address` varchar(500) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '户籍地址',
  `pincode` varchar(10) NOT NULL DEFAULT '' COMMENT '邮编',
  `permanent_province` varchar(50) NOT NULL DEFAULT '' COMMENT '永久居住州',
  `permanent_city` varchar(50) NOT NULL DEFAULT '' COMMENT '永久居住城市',
  `permanent_address` varchar(500) NOT NULL DEFAULT '' COMMENT '永久居住地址',
  `permanent_pincode` varchar(10) NOT NULL DEFAULT '' COMMENT '永久居住地址pincode',
  `expected_amount` varchar(10) NOT NULL COMMENT '期望额度',
  `salary` varchar(20) NOT NULL COMMENT '工资',
  `education_level` varchar(20) NOT NULL DEFAULT '' COMMENT '教育程度',
  `marital_status` varchar(20) NOT NULL DEFAULT '' COMMENT '婚姻状况 0 未婚 1 已婚没有子女 2 已婚没有子女 3离婚 4丧偶',
  `id_card_valid_date` varchar(30) NOT NULL COMMENT '身份证有效期限',
  `id_card_issued_by` varchar(50) NOT NULL COMMENT '身份证签发机关',
  `birthday` varchar(20) NOT NULL DEFAULT '',
  `father_name` varchar(50) NOT NULL DEFAULT '',
  `residence_type` varchar(20) NOT NULL DEFAULT '' COMMENT '住宅类型: RENTED租住;OWNED拥有;WITH RELATIVES跟家属住;OFFICE PROVIDED公司宿舍',
  `religion` varchar(20) NOT NULL DEFAULT '' COMMENT '宗教',
  `language` varchar(200) NOT NULL DEFAULT '' COMMENT '语言',
  `pan_card_no` varchar(20) NOT NULL DEFAULT '' COMMENT 'pancard No',
  `aadhaar_card_no` varchar(20) NOT NULL DEFAULT '' COMMENT 'aadhaar No',
  `company_telephone` varchar(20) NOT NULL COMMENT '公司电话',
  `company_name` varchar(50) NOT NULL COMMENT '公司名称',
  `passport_no` varchar(20) NOT NULL DEFAULT '' COMMENT '护照',
  `voter_id_card_no` varchar(20) NOT NULL DEFAULT '' COMMENT '选民身份证',
  `driving_license_no` varchar(20) NOT NULL COMMENT '驾驶证',
  `address_aadhaar_no` varchar(20) NOT NULL DEFAULT '' COMMENT '地址aadhaar No',
  `email` varchar(128) NOT NULL DEFAULT '',
  `google_token` text CHARACTER SET utf8mb4 COMMENT 'google_token',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `input_name` varchar(50) DEFAULT NULL COMMENT '用户输入姓名',
  `contacts_count` int(10) DEFAULT NULL COMMENT '通讯录总数',
  `input_birthday` varchar(20) DEFAULT NULL COMMENT '用户输入生日',
  `chirdren_count` varchar(20) DEFAULT NULL COMMENT '子女数',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `pan_card_no` (`pan_card_no`) USING BTREE,
  KEY `aadhaar_card_no` (`aadhaar_card_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for user_login_log
-- ----------------------------
DROP TABLE IF EXISTS `user_login_log`;
CREATE TABLE `user_login_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` bigint(20) unsigned NOT NULL COMMENT 'app_id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录ip',
  `address` varchar(128) DEFAULT NULL,
  `device` varchar(32) DEFAULT NULL COMMENT '设备名称',
  `browser` varchar(32) DEFAULT NULL COMMENT '浏览器',
  `platform` varchar(32) DEFAULT NULL COMMENT '操作系统',
  `language` varchar(32) DEFAULT NULL COMMENT '语言',
  `device_type` varchar(32) DEFAULT NULL COMMENT '设备类型 tablet平板 mobile便捷设备 robot爬虫机器人 desktop桌面设备',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `app_id` (`app_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户登录日志表';

-- ----------------------------
-- Table structure for user_phone_hardware
-- ----------------------------
DROP TABLE IF EXISTS `user_phone_hardware`;
CREATE TABLE `user_phone_hardware` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '未使用内存空间',
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `imei` varchar(100) DEFAULT NULL COMMENT 'Imei号',
  `phone_type` varchar(32) DEFAULT NULL COMMENT '手机型号',
  `system_version` varchar(100) DEFAULT '' COMMENT '安卓版本',
  `system_version_code` varchar(20) DEFAULT NULL COMMENT '版本号',
  `imsi` varchar(64) DEFAULT NULL COMMENT 'Imsi号',
  `phone_brand` varchar(100) DEFAULT NULL COMMENT '手机品牌',
  `ext_info` varchar(500) DEFAULT NULL COMMENT '手机硬件扩展信息',
  `created_at` datetime DEFAULT NULL COMMENT '添加时间',
  `total_disk` varchar(64) NOT NULL COMMENT '内存空间总大小',
  `used_disk` varchar(64) NOT NULL COMMENT '已使用内存空间',
  `free_disk` varchar(64) NOT NULL COMMENT '未使用内存空间',
  `is_vpn_used` varchar(10) NOT NULL DEFAULT '' COMMENT '是否使用vpn',
  `is_wifi_proxy` varchar(10) NOT NULL DEFAULT '' COMMENT '是否使用wifi',
  `pixels` varchar(500) DEFAULT NULL COMMENT '手机摄像头',
  `sim_phone_num` varchar(20) DEFAULT NULL COMMENT 'SIM卡手机号',
  `net_type` varchar(20) DEFAULT NULL COMMENT '网络类型，上传相关字段信息：如4G，3G，wifi等',
  `net_type_original` varchar(128) DEFAULT NULL COMMENT '原始网络类型',
  `is_double_sim` varchar(10) DEFAULT NULL COMMENT '是否双卡双待',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id_2` (`user_id`) USING BTREE,
  KEY `imei` (`imei`) USING BTREE,
  KEY `user_id` (`user_id`,`imei`,`imsi`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户手机硬件信息';

-- ----------------------------
-- Table structure for user_phone_photo
-- ----------------------------
DROP TABLE IF EXISTS `user_phone_photo`;
CREATE TABLE `user_phone_photo` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `storage` text COMMENT '存储位置',
  `longitude` double(30,12) NOT NULL COMMENT '经度',
  `latitude` double(30,12) NOT NULL COMMENT '维度',
  `length` varchar(50) DEFAULT NULL COMMENT '照片长度',
  `width` varchar(50) DEFAULT NULL COMMENT '照片宽度',
  `province` varchar(60) DEFAULT NULL COMMENT '省份',
  `city` varchar(60) DEFAULT NULL COMMENT '市',
  `district` varchar(60) DEFAULT NULL COMMENT '县/区',
  `street` varchar(255) DEFAULT NULL COMMENT '街道',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `photo_created_time` datetime DEFAULT NULL COMMENT '拍照时间',
  `all_data` text COMMENT '存储位置',
  `created_at` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `province_city` (`province`,`city`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user_position
-- ----------------------------
DROP TABLE IF EXISTS `user_position`;
CREATE TABLE `user_position` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `longitude` double(30,12) NOT NULL COMMENT '经度',
  `latitude` double(30,12) NOT NULL COMMENT '维度',
  `province` varchar(60) DEFAULT NULL COMMENT '省份',
  `city` varchar(60) DEFAULT NULL,
  `district` varchar(60) DEFAULT NULL COMMENT '县/区',
  `street` varchar(255) DEFAULT NULL COMMENT '街道',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `created_at` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `province_city` (`province`,`city`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户地理位置轨迹表';

-- ----------------------------
-- Table structure for user_third_data
-- ----------------------------
DROP TABLE IF EXISTS `user_third_data`;
CREATE TABLE `user_third_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `channel` varchar(50) DEFAULT NULL,
  `result` text,
  `report_id` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `res_status` tinyint(4) DEFAULT '0' COMMENT '-1没有 1匹配 2不匹配',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 1正常 -1失效',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for user_work
-- ----------------------------
DROP TABLE IF EXISTS `user_work`;
CREATE TABLE `user_work` (
  `id` bigint(13) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户工作信息自增长ID',
  `merchant_id` bigint(20) unsigned NOT NULL COMMENT 'merchant_id',
  `user_id` bigint(13) NOT NULL COMMENT '用户id',
  `profession` varchar(100) NOT NULL DEFAULT '' COMMENT '职业',
  `workplace_pincode` varchar(50) NOT NULL DEFAULT '' COMMENT '工作地点邮编',
  `company` varchar(100) NOT NULL DEFAULT '' COMMENT '公司',
  `work_address1` varchar(200) NOT NULL DEFAULT '' COMMENT '工作地点1',
  `work_address2` varchar(200) NOT NULL DEFAULT '' COMMENT '工作地点2',
  `work_positon` varchar(200) NOT NULL DEFAULT '' COMMENT '工作位置',
  `salary` varchar(100) NOT NULL DEFAULT '' COMMENT '薪水',
  `work_email` varchar(50) NOT NULL DEFAULT '' COMMENT '工作邮箱',
  `work_start_date` varchar(10) NOT NULL DEFAULT '' COMMENT '开始工作时间',
  `work_experience_years` tinyint(4) NOT NULL DEFAULT '0' COMMENT '工作经验，单位/年',
  `work_experience_months` tinyint(4) NOT NULL DEFAULT '0' COMMENT '工作经验，单位/月',
  `work_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '工作电话',
  `work_card_front_file_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '工作证正面文件id',
  `work_card_back_file_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '工作证反面文件id',
  `employment_type` varchar(50) NOT NULL DEFAULT '' COMMENT '就业类型',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `occupation` varchar(100) DEFAULT NULL COMMENT '一级职业',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
