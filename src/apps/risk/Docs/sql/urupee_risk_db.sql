/*
 Navicat Premium Data Transfer

 Source Server         : 45.40.57.49-新开发环境
 Source Server Type    : MySQL
 Source Server Version : 50729
 Source Host           : localhost:3306
 Source Schema         : common_risk

 Target Server Type    : MySQL
 Target Server Version : 50729
 File Encoding         : 65001

 Date: 12/07/2020 20:51:30
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bank_info
-- ----------------------------
DROP TABLE IF EXISTS `bank_info`;
CREATE TABLE `bank_info`
(
    `bank_info_id` int(11) UNSIGNED                                        NOT NULL AUTO_INCREMENT,
    `bank`         varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '银行名称',
    `ifsc`         varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '银行编号',
    `micr_code`    varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '编号',
    `branch`       varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '分支',
    `address`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地址',
    `contact`      varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系方式',
    `city`         varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属城市',
    `district`     varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属区',
    `state`        varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '状态',
    `created_time` bigint(13)                                              NULL DEFAULT NULL,
    `updated_time` bigint(13)                                              NULL DEFAULT NULL,
    PRIMARY KEY (`bank_info_id`) USING BTREE,
    UNIQUE INDEX `ifsc` (`ifsc`) USING BTREE,
    INDEX `state` (`state`) USING BTREE,
    INDEX `city` (`city`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 273623
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for common_channel
-- ----------------------------
DROP TABLE IF EXISTS `common_channel`;
CREATE TABLE `common_channel`
(
    `id`                       int(11) UNSIGNED                                        NOT NULL COMMENT '自增长id',
    `app_id`                   bigint(20) UNSIGNED                                     NOT NULL,
    `business_app_id`          bigint(20) UNSIGNED                                     NOT NULL COMMENT 'app_id',
    `channel_code`             varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '渠道编码',
    `channel_name`             varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '渠道名称',
    `company`                  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '公司',
    `cpa`                      varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '',
    `cps`                      varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '',
    `pre_cost`                 varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '预设成本',
    `percent`                  varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '折扣比例',
    `settlement_way`           tinyint(3)                                              NULL     DEFAULT 1 COMMENT '结算方式',
    `email`                    varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT '' COMMENT '渠道对接人邮箱',
    `status`                   tinyint(3) UNSIGNED                                     NOT NULL DEFAULT 1 COMMENT '状态',
    `check_email`              varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT '' COMMENT '内部审核邮箱',
    `partner_name`             varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '合作方',
    `partner_account`          varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '合作方帐号',
    `partner_config_dimension` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '合作方维度配置',
    `partner_user_id`          int(10)                                                 NULL     DEFAULT 0 COMMENT '合作方用户id',
    `is_auth`                  tinyint(2)                                              NULL     DEFAULT 0 COMMENT '是否授权 1授权 0未授权',
    `created_at`               datetime(0)                                             NULL     DEFAULT NULL COMMENT '创建时间',
    `updated_at`               datetime(0)                                             NULL     DEFAULT NULL COMMENT '更新时间',
    `cooperation_time`         datetime(0)                                             NULL     DEFAULT NULL COMMENT '合作时间',
    `url`                      text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL COMMENT '推广地址',
    `sort`                     int(11)                                                 NOT NULL DEFAULT 1 COMMENT '排序',
    `is_top`                   tinyint(4)                                              NULL     DEFAULT 0 COMMENT '是否置顶',
    `top_time`                 datetime(0)                                             NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '置顶时间',
    `page_name`                varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '包名',
    `short_url`                varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '短链',
    `sync_time`                datetime(0)                                             NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`) USING BTREE,
    INDEX `status` (`status`) USING BTREE,
    INDEX `channel_code` (`channel_code`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '渠道管理表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config`
(
    `id`         int(10) UNSIGNED                                        NOT NULL AUTO_INCREMENT,
    `app_id`     bigint(20) UNSIGNED                                     NOT NULL COMMENT 'app_id',
    `key`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `value`      text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL,
    `remark`     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `status`     tinyint(2)                                              NOT NULL,
    `created_at` datetime(0)                                             NULL     DEFAULT NULL,
    `updated_at` datetime(0)                                             NULL     DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `key` (`key`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 2841
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '系统配置表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_bank_card
-- ----------------------------
DROP TABLE IF EXISTS `data_bank_card`;
CREATE TABLE `data_bank_card`
(
    `id`                 bigint(20) UNSIGNED                                           NOT NULL COMMENT '银行卡id',
    `app_id`             bigint(20) UNSIGNED                                           NULL     DEFAULT NULL COMMENT 'merchant_id',
    `business_app_id`    bigint(20) UNSIGNED                                           NOT NULL COMMENT 'app_id',
    `user_id`            bigint(20)                                                    NOT NULL COMMENT '用户id',
    `no`                 varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL COMMENT '银行卡号',
    `name`               varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '银行卡户主姓名',
    `bank`               varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT NULL COMMENT '银行缩写',
    `bank_name`          varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL COMMENT '开户行',
    `reserved_telephone` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '银行预留手机号',
    `status`             tinyint(4)                                                    NOT NULL DEFAULT 1 COMMENT '状态:1正常 0废弃 -1待鉴权绑卡 -2系统清理(放款失败解绑银行卡)',
    `created_at`         datetime(0)                                                   NULL     DEFAULT NULL COMMENT '创建时间',
    `updated_at`         datetime(0)                                                   NULL     DEFAULT NULL COMMENT '更新时间',
    `bank_branch_name`   varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT '' COMMENT '所属支行',
    `province_code`      varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT '' COMMENT '省级code',
    `city_code`          varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT '',
    `ifsc`               varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT NULL,
    `city`               varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL     DEFAULT NULL,
    `province`           varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL     DEFAULT NULL,
    `reject_time`        datetime(0)                                                   NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
    `reject_day`         tinyint(4)                                                    NULL     DEFAULT NULL,
    `sync_time`          datetime(0)                                                   NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `business_app_id`, `user_id`, `no`) USING BTREE,
    INDEX `reserved_telephone` (`reserved_telephone`, `status`) USING BTREE,
    INDEX `no` (`no`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `app_id` (`business_app_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_collection_record
-- ----------------------------
DROP TABLE IF EXISTS `data_collection_record`;
CREATE TABLE `data_collection_record`
(
    `id`                   bigint(16) UNSIGNED                                          NOT NULL,
    `app_id`               bigint(20)                                                   NOT NULL COMMENT 'merchant_id',
    `collection_id`        bigint(16)                                                   NULL     DEFAULT 0 COMMENT '催收记录ID',
    `order_id`             bigint(16)                                                   NOT NULL DEFAULT 0 COMMENT '管理员ID',
    `user_id`              bigint(16)                                                   NOT NULL DEFAULT 0 COMMENT '管理员ID',
    `admin_id`             varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci       NULL     DEFAULT '0' COMMENT '管理员ID',
    `fullname`             varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '姓名',
    `relation`             varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci       NULL     DEFAULT '' COMMENT '亲戚关系',
    `contact`              varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci       NULL     DEFAULT '' COMMENT '联系值（手机号）',
    `promise_paid_time`    datetime(0)                                                  NULL     DEFAULT NULL COMMENT '承诺还款时间',
    `remark`               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci      NULL     DEFAULT '' COMMENT '备注',
    `dial`                 varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '联系结果 （正常联系，无法联系...）',
    `progress`             varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '催收进度 （承诺还款，无意向...）',
    `from_status`          varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci       NULL     DEFAULT '' COMMENT '催记前案子状态',
    `to_status`            varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci       NULL     DEFAULT '' COMMENT '催记后案子状态',
    `created_at`           datetime(0)                                                  NULL     DEFAULT NULL COMMENT '创建时间',
    `updated_at`           datetime(0)                                                  NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
    `overdue_days`         int(10)                                                      NOT NULL DEFAULT 0 COMMENT '当前逾期天数',
    `reduction_fee`        decimal(10, 2) UNSIGNED                                      NOT NULL DEFAULT 0.00 COMMENT '当前减免金额',
    `level`                varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci       NULL     DEFAULT '' COMMENT '当前催收等级',
    `receivable_amount`    decimal(10, 2)                                               NULL     DEFAULT 0.00 COMMENT '当前应还金额',
    `collection_assign_id` bigint(16)                                                   NULL     DEFAULT 0 COMMENT '催收分单ID',
    `sync_time`            datetime(0)                                                  NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `order_id`, `user_id`) USING BTREE,
    INDEX `collection_id` (`collection_id`) USING BTREE,
    INDEX `order_id` (`order_id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `admin_id` (`admin_id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '催记表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_manual_approve_log
-- ----------------------------
DROP TABLE IF EXISTS `data_manual_approve_log`;
CREATE TABLE `data_manual_approve_log`
(
    `id`                bigint(20) UNSIGNED                                     NOT NULL,
    `app_id`            bigint(20)                                              NOT NULL,
    `admin_id`          bigint(20) UNSIGNED                                     NULL DEFAULT NULL,
    `user_id`           bigint(20)                                              NULL DEFAULT NULL,
    `order_id`          bigint(20)                                              NULL DEFAULT NULL,
    `approve_type`      tinyint(1)                                              NULL DEFAULT NULL COMMENT '审批类型判断1初审2电审',
    `name`              varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `from_order_status` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `to_order_status`   varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `result`            text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL,
    `remark`            varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
    `created_at`        datetime(0)                                             NULL DEFAULT NULL,
    `sync_time`         datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`, `order_id`) USING BTREE,
    INDEX `order_id` (`order_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '人审记录表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_order
-- ----------------------------
DROP TABLE IF EXISTS `data_order`;
CREATE TABLE `data_order`
(
    `id`                  bigint(20) UNSIGNED                                    NOT NULL COMMENT '订单id',
    `app_id`              bigint(20) UNSIGNED                                    NULL     DEFAULT NULL COMMENT 'merchant_id',
    `business_app_id`     bigint(20) UNSIGNED                                    NULL     DEFAULT NULL COMMENT 'app_id',
    `order_no`            varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '订单编号',
    `user_id`             bigint(20) UNSIGNED                                    NOT NULL DEFAULT 0 COMMENT '用户id',
    `principal`           decimal(10, 2) UNSIGNED                                NULL     DEFAULT 0.00 COMMENT '贷款金额',
    `loan_days`           int(10) UNSIGNED                                       NULL     DEFAULT 0 COMMENT '贷款天数',
    `status`              varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '订单状态',
    `created_at`          datetime(0)                                            NULL     DEFAULT NULL COMMENT '创建时间',
    `updated_at`          datetime(0)                                            NULL     DEFAULT NULL COMMENT '更新时间',
    `app_client`          varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT 'APP终端',
    `app_version`         varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT 'APP版本号',
    `quality`             tinyint(2) UNSIGNED                                    NULL     DEFAULT 0 COMMENT '新老用户 0新用户 1老用户',
    `daily_rate`          decimal(11, 5) UNSIGNED                                NOT NULL DEFAULT 0.00000 COMMENT '日利率',
    `overdue_rate`        decimal(11, 5) UNSIGNED                                NOT NULL DEFAULT 0.00000 COMMENT '逾期费率',
    `signed_time`         datetime(0)                                            NULL     DEFAULT NULL COMMENT '签约时间',
    `system_time`         datetime(0)                                            NULL     DEFAULT NULL COMMENT '机审结束时间',
    `manual_time`         datetime(0)                                            NULL     DEFAULT NULL COMMENT '人审结束时间',
    `paid_time`           datetime(0)                                            NULL     DEFAULT NULL COMMENT '放款时间',
    `paid_amount`         decimal(10, 2)                                         NULL     DEFAULT NULL COMMENT '实际放款金额',
    `pay_channel`         varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '放款渠道',
    `cancel_time`         datetime(0)                                            NULL     DEFAULT NULL COMMENT '取消时间',
    `pass_time`           datetime(0)                                            NULL     DEFAULT NULL COMMENT '审批通过时间',
    `overdue_time`        datetime(0)                                            NULL     DEFAULT NULL COMMENT '转逾期时间',
    `bad_time`            datetime(0)                                            NULL     DEFAULT NULL COMMENT '转坏账时间',
    `approve_push_status` tinyint(1)                                             NULL     DEFAULT 0 COMMENT '审批推送状态 0 不需要推送 1 未推送 2 已推送',
    `manual_check`        tinyint(1)                                             NULL     DEFAULT 0 COMMENT '是否需要初审, 1需要, 2不需要',
    `call_check`          tinyint(1)                                             NULL     DEFAULT 0 COMMENT '是否需要电审,1需要，2不需要',
    `reject_time`         datetime(0)                                            NULL     DEFAULT NULL COMMENT '被拒时间',
    `manual_result`       tinyint(4)                                             NULL     DEFAULT 0 COMMENT '人审通过结果',
    `call_result`         tinyint(4)                                             NULL     DEFAULT 0 COMMENT '电审通过结果',
    `auth_process`        varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '认证过程，所属分流',
    `nbfc_report_status`  tinyint(4)                                             NULL     DEFAULT 0 COMMENT 'NBFC上报状态 0:无需上报 1:未上报 2:已上报',
    `flag`                varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '特殊订单标记',
    `sync_time`           datetime(0)                                            NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `order_no`, `user_id`) USING BTREE,
    INDEX `order_no` (`order_no`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE,
    INDEX `app_id` (`business_app_id`) USING BTREE,
    INDEX `created_at` (`created_at`) USING BTREE,
    INDEX `status` (`status`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_order_detail
-- ----------------------------
DROP TABLE IF EXISTS `data_order_detail`;
CREATE TABLE `data_order_detail`
(
    `id`         bigint(20) UNSIGNED                                     NOT NULL,
    `order_id`   bigint(20) UNSIGNED                                     NOT NULL,
    `app_id`     bigint(20) UNSIGNED                                     NOT NULL COMMENT 'merchant_id',
    `key`        varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT '' COMMENT '键',
    `value`      varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '值',
    `created_at` datetime(0)                                             NULL DEFAULT NULL,
    `updated_at` datetime(0)                                             NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
    `sync_time`  datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `order_id`, `app_id`, `key`) USING BTREE,
    INDEX `order_id` (`order_id`) USING BTREE,
    INDEX `key` (`key`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE,
    INDEX `order_id, key` (`order_id`, `key`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_repayment_plan
-- ----------------------------
DROP TABLE IF EXISTS `data_repayment_plan`;
CREATE TABLE `data_repayment_plan`
(
    `id`                    bigint(20) UNSIGNED                                     NOT NULL,
    `app_id`                bigint(20) UNSIGNED                                     NOT NULL COMMENT 'merchant_id',
    `user_id`               bigint(20) UNSIGNED                                     NULL     DEFAULT 0 COMMENT '用户id',
    `order_id`              bigint(20) UNSIGNED                                     NULL     DEFAULT 0 COMMENT '订单id',
    `no`                    varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '还款编号',
    `status`                int(10)                                                 NULL     DEFAULT NULL COMMENT '还款状态',
    `overdue_days`          int(10) UNSIGNED                                        NULL     DEFAULT 0 COMMENT '逾期天数',
    `appointment_paid_time` datetime(0)                                             NULL     DEFAULT NULL COMMENT '应还款时间',
    `repay_time`            datetime(0)                                             NULL     DEFAULT NULL COMMENT '还款时间',
    `repay_amount`          decimal(10, 2) UNSIGNED                                 NULL     DEFAULT 0.00 COMMENT '实际还款金额',
    `repay_channel`         varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '还款渠道',
    `reduction_fee`         decimal(10, 2) UNSIGNED                                 NULL     DEFAULT 0.00 COMMENT '减免金额',
    `created_at`            datetime(0)                                             NULL     DEFAULT NULL COMMENT '创建时间',
    `updated_at`            datetime(0)                                             NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
    `reduction_valid_date`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '减免有效期',
    `principal`             decimal(10, 2)                                          NULL     DEFAULT 0.00 COMMENT '实还本金',
    `interest_fee`          decimal(10, 2) UNSIGNED                                 NULL     DEFAULT 0.00 COMMENT '实还综合费用',
    `overdue_fee`           decimal(10, 2) UNSIGNED                                 NULL     DEFAULT 0.00 COMMENT '实还罚息',
    `gst_processing`        decimal(10, 2) UNSIGNED                                 NULL     DEFAULT 0.00 COMMENT 'GST手续费',
    `gst_penalty`           decimal(10, 2) UNSIGNED                                 NULL     DEFAULT 0.00 COMMENT 'GST逾期费',
    `installment_num`       int(4)                                                  NOT NULL DEFAULT 1 COMMENT '当前期数，默认为1',
    `repay_proportion`      decimal(10, 2) UNSIGNED                                 NULL     DEFAULT 100.00 COMMENT '当期还款比例',
    `repay_days`            int(8)                                                  NULL     DEFAULT 0 COMMENT '当期还款天数',
    `loan_days`             int(8)                                                  NULL     DEFAULT NULL COMMENT '当期借款天数',
    `part_repay_amount`     decimal(10, 2)                                          NULL     DEFAULT NULL COMMENT '部分还款金额',
    `can_part_repay`        tinyint(4)                                              NULL     DEFAULT NULL COMMENT '催收配置可部分还款',
    `ost_prncp`             decimal(10, 2)                                          NULL     DEFAULT NULL COMMENT 'min(应还金额，应还本金)\r\n即\r\nmin(sum(应还本金，应还罚息，应还利息)-repay_amt，应还本金）',
    `sync_time`             datetime(0)                                             NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`, `order_id`, `no`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `order_id` (`order_id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE,
    INDEX `no` (`no`) USING BTREE,
    INDEX `status` (`status`) USING BTREE,
    INDEX `appointment_paid_time` (`appointment_paid_time`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user
-- ----------------------------
DROP TABLE IF EXISTS `data_user`;
CREATE TABLE `data_user`
(
    `id`              bigint(20) UNSIGNED                                     NOT NULL COMMENT '用户id',
    `app_id`          bigint(20) UNSIGNED                                     NOT NULL COMMENT 'merchant_id',
    `business_app_id` bigint(20) UNSIGNED                                     NULL     DEFAULT NULL COMMENT 'app_id',
    `telephone`       varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '手机号码',
    `fullname`        varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '姓名',
    `id_card_no`      varchar(19) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '身份证号',
    `status`          tinyint(2) UNSIGNED                                     NULL     DEFAULT 1 COMMENT '状态 1正常 2禁用',
    `created_at`      datetime(0)                                             NULL     DEFAULT NULL COMMENT '注册时间',
    `updated_at`      datetime(0)                                             NULL     DEFAULT NULL COMMENT '更新时间',
    `platform`        varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '下载平台',
    `client_id`       varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '注册终端 Android/ios/h5',
    `channel_id`      varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '注册渠道',
    `quality`         tinyint(2) UNSIGNED                                     NOT NULL DEFAULT 0 COMMENT '新老用户 0新用户 1老用户',
    `quality_time`    datetime(0)                                             NULL     DEFAULT NULL COMMENT '转老用户时间',
    `app_version`     varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '注册版本',
    `api_token`       varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '令牌token',
    `sync_time`       datetime(0)                                             NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `telephone`, `business_app_id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE,
    INDEX `app_id,telephone` (`business_app_id`, `telephone`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '用户表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_application
-- ----------------------------
DROP TABLE IF EXISTS `data_user_application`;
CREATE TABLE `data_user_application`
(
    `id`             bigint(20) UNSIGNED                                     NOT NULL,
    `app_id`         bigint(20)                                              NOT NULL,
    `user_id`        bigint(20)                                              NOT NULL COMMENT '用户id',
    `app_name`       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '应用名',
    `pkg_name`       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '包名',
    `installed_time` bigint(13)                                              NULL     DEFAULT NULL COMMENT '应用安装时间',
    `updated_time`   bigint(13)                                              NULL     DEFAULT NULL COMMENT '应用的最后更新时间',
    `version`        int(10)                                                 NOT NULL DEFAULT 0 COMMENT '版本号，用于区分批次',
    `created_at`     datetime(0)                                             NULL     DEFAULT NULL COMMENT '记录添加时间',
    `sync_time`      datetime(0)                                             NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `user_id_app_id` (`user_id`) USING BTREE,
    INDEX `user_id` (`user_id`, `pkg_name`) USING BTREE,
    INDEX `created_at` (`created_at`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '用户安装应用表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_auth
-- ----------------------------
DROP TABLE IF EXISTS `data_user_auth`;
CREATE TABLE `data_user_auth`
(
    `id`          bigint(13) UNSIGNED                                     NOT NULL COMMENT '用户信息扩展自增长ID',
    `app_id`      bigint(20) UNSIGNED                                     NOT NULL COMMENT 'merchant_id',
    `user_id`     bigint(16)                                              NOT NULL DEFAULT 0 COMMENT '用户id',
    `type`        varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户数据类型',
    `time`        datetime(0)                                             NULL     DEFAULT NULL COMMENT '用户数据时间',
    `status`      tinyint(4)                                              NOT NULL DEFAULT 0 COMMENT '状态',
    `created_at`  datetime(0)                                             NULL     DEFAULT NULL COMMENT '创建时间',
    `updated_at`  datetime(0)                                             NULL     DEFAULT NULL COMMENT '更新时间',
    `auth_status` tinyint(4)                                              NOT NULL DEFAULT 0,
    `quality`     tinyint(2) UNSIGNED                                     NOT NULL DEFAULT 0 COMMENT '新老用户 0新用户 1老用户',
    `sync_time`   datetime(0)                                             NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE,
    INDEX `user_id_type` (`user_id`, `type`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_contact
-- ----------------------------
DROP TABLE IF EXISTS `data_user_contact`;
CREATE TABLE `data_user_contact`
(
    `id`                             bigint(13) UNSIGNED                                    NOT NULL COMMENT '用户紧急联系人自增长ID',
    `app_id`                         bigint(20) UNSIGNED                                    NOT NULL COMMENT 'merchant_id',
    `user_id`                        bigint(13)                                             NOT NULL COMMENT '用户ID',
    `contact_fullname`               varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '紧急联系人名字',
    `contact_telephone`              varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '紧急联系人电话',
    `relation`                       varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '紧急联系人关系(直系亲属,朋友,兄弟姐妹)',
    `status`                         tinyint(4)                                             NOT NULL DEFAULT 1,
    `check_status`                   varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '空号检测结果',
    `created_at`                     datetime(0)                                            NULL     DEFAULT NULL COMMENT '创建时间',
    `updated_at`                     datetime(0)                                            NULL     DEFAULT NULL COMMENT '修改时间',
    `times_contacted`                int(10)                                                NULL     DEFAULT NULL COMMENT '联系人联系次数',
    `last_time_contacted`            bigint(13)                                             NULL     DEFAULT NULL COMMENT '最近联系时间',
    `has_phone_number`               varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '是否有号码',
    `starred`                        tinyint(2)                                             NULL     DEFAULT NULL COMMENT '是否收藏',
    `contact_last_updated_timestamp` bigint(13)                                             NULL     DEFAULT NULL COMMENT '联系人最后编辑时间',
    `sync_time`                      datetime(0)                                            NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `status` (`status`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '用户紧急联系人表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_contacts_telephone
-- ----------------------------
DROP TABLE IF EXISTS `data_user_contacts_telephone`;
CREATE TABLE `data_user_contacts_telephone`
(
    `id`                             bigint(20) UNSIGNED                                    NOT NULL,
    `app_id`                         bigint(20)                                             NOT NULL,
    `user_id`                        bigint(13)                                             NOT NULL,
    `contact_fullname`               varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL,
    `contact_telephone`              varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL,
    `relation_level`                 tinyint(2)                                             NOT NULL DEFAULT 0 COMMENT '亲戚关系(0无亲戚关系,1近亲,2近亲模糊匹配,3远亲)',
    `created_at`                     datetime(0)                                            NULL     DEFAULT NULL COMMENT '添加时间',
    `times_contacted`                int(10)                                                NULL     DEFAULT NULL COMMENT '联系人联系次数',
    `last_time_contacted`            bigint(13)                                             NULL     DEFAULT NULL COMMENT '最近联系时间',
    `has_phone_number`               varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '是否有号码',
    `starred`                        tinyint(2)                                             NULL     DEFAULT NULL COMMENT '是否收藏',
    `contact_last_updated_timestamp` bigint(13)                                             NULL     DEFAULT NULL COMMENT '联系人最后编辑时间',
    `sync_time`                      datetime(0)                                            NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `user_id` (`user_id`, `contact_telephone`) USING BTREE,
    INDEX `user_id_app_id` (`user_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '用户通讯录信息'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_info
-- ----------------------------
DROP TABLE IF EXISTS `data_user_info`;
CREATE TABLE `data_user_info`
(
    `id`                 bigint(13) UNSIGNED                                           NOT NULL,
    `app_id`             bigint(20) UNSIGNED                                           NOT NULL COMMENT 'merchant_id',
    `user_id`            bigint(13)                                                    NOT NULL COMMENT '用户id',
    `gender`             varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT NULL COMMENT '性别:男,女',
    `province`           varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '省',
    `city`               varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '市',
    `address`            varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL     DEFAULT '' COMMENT '户籍地址',
    `pincode`            varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '邮编',
    `permanent_province` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '永久居住州',
    `permanent_city`     varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '永久居住城市',
    `permanent_address`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci       NULL     DEFAULT '' COMMENT '永久居住地址',
    `permanent_pincode`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '永久居住地址pincode',
    `expected_amount`    varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '期望额度',
    `salary`             varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '工资',
    `education_level`    varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '教育程度',
    `marital_status`     varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '婚姻状况 0 未婚 1 已婚没有子女 2 已婚没有子女 3离婚 4丧偶',
    `id_card_valid_date` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '身份证有效期限',
    `id_card_issued_by`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '身份证签发机关',
    `birthday`           varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NOT NULL DEFAULT '',
    `father_name`        varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '',
    `residence_type`     varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '住宅类型: RENTED租住;OWNED拥有;WITH RELATIVES跟家属住;OFFICE PROVIDED公司宿舍',
    `religion`           varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '宗教',
    `language`           varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci       NOT NULL DEFAULT '' COMMENT '语言',
    `pan_card_no`        varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NOT NULL DEFAULT '' COMMENT 'pancard No',
    `aadhaar_card_no`    varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NOT NULL DEFAULT '' COMMENT 'aadhaar No',
    `company_telephone`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '公司电话',
    `company_name`       varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '公司名称',
    `passport_no`        varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '护照',
    `voter_id_card_no`   varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '选民身份证',
    `driving_license_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '驾驶证',
    `address_aadhaar_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT '' COMMENT '地址aadhaar No',
    `email`              varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci       NULL     DEFAULT '',
    `google_token`       text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci         NULL COMMENT 'google_token',
    `created_at`         datetime(0)                                                   NULL     DEFAULT NULL,
    `updated_at`         datetime(0)                                                   NULL     DEFAULT NULL COMMENT '更新时间',
    `input_name`         varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '用户输入姓名',
    `contacts_count`     int(10)                                                       NULL     DEFAULT NULL COMMENT '通讯录总数',
    `input_birthday`     varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '用户输入生日',
    `chirdren_count`     varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci        NULL     DEFAULT NULL COMMENT '子女数',
    `sync_time`          datetime(0)                                                   NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE,
    INDEX `pan_card_no` (`pan_card_no`) USING BTREE,
    INDEX `aadhaar_card_no` (`aadhaar_card_no`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_phone_hardware
-- ----------------------------
DROP TABLE IF EXISTS `data_user_phone_hardware`;
CREATE TABLE `data_user_phone_hardware`
(
    `id`                  bigint(20) UNSIGNED                                     NOT NULL COMMENT '未使用内存空间',
    `app_id`              bigint(20)                                              NOT NULL,
    `user_id`             bigint(20)                                              NOT NULL COMMENT '用户id',
    `imei`                varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'Imei号',
    `phone_type`          varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT '手机型号',
    `system_version`      varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '安卓版本',
    `system_version_code` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT '版本号',
    `imsi`                varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'Imsi号',
    `phone_brand`         varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '手机品牌',
    `ext_info`            varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '手机硬件扩展信息',
    `created_at`          datetime(0)                                             NULL     DEFAULT NULL COMMENT '添加时间',
    `total_disk`          varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '内存空间总大小',
    `used_disk`           varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '已使用内存空间',
    `free_disk`           varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '未使用内存空间',
    `is_vpn_used`         varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT '' COMMENT '是否使用vpn',
    `is_wifi_proxy`       varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT '' COMMENT '是否使用wifi',
    `pixels`              varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '手机摄像头',
    `sim_phone_num`       varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'SIM卡手机号',
    `net_type`            varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT '网络类型，上传相关字段信息：如4G，3G，wifi等',
    `net_type_original`   varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '原始网络类型',
    `is_double_sim`       varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT '是否双卡双待',
    `wifi_ip_address`     varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'wifiIP',
    `sync_time`           datetime(0)                                             NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `user_id_2` (`user_id`) USING BTREE,
    INDEX `imei` (`imei`) USING BTREE,
    INDEX `user_id` (`user_id`, `imei`, `imsi`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '用户手机硬件信息'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_phone_photo
-- ----------------------------
DROP TABLE IF EXISTS `data_user_phone_photo`;
CREATE TABLE `data_user_phone_photo`
(
    `id`                 bigint(20) UNSIGNED                                     NOT NULL,
    `app_id`             bigint(20)                                              NOT NULL,
    `user_id`            bigint(20)                                              NOT NULL COMMENT '用户id',
    `storage`            text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '存储位置',
    `longitude`          double(30, 12)                                          NOT NULL COMMENT '经度',
    `latitude`           double(30, 12)                                          NOT NULL COMMENT '维度',
    `length`             varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '照片长度',
    `width`              varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '照片宽度',
    `province`           varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '省份',
    `city`               varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '市',
    `district`           varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '县/区',
    `street`             varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '街道',
    `address`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '详细地址',
    `photo_created_time` datetime(0)                                             NULL DEFAULT NULL COMMENT '拍照时间',
    `all_data`           text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '存储位置',
    `created_at`         datetime(0)                                             NULL DEFAULT NULL COMMENT '添加时间',
    `sync_time`          datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `province_city` (`province`, `city`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_position
-- ----------------------------
DROP TABLE IF EXISTS `data_user_position`;
CREATE TABLE `data_user_position`
(
    `id`         bigint(20) UNSIGNED                                     NOT NULL,
    `app_id`     bigint(20)                                              NOT NULL,
    `user_id`    bigint(20)                                              NOT NULL COMMENT '用户id',
    `longitude`  double(30, 12)                                          NOT NULL COMMENT '经度',
    `latitude`   double(30, 12)                                          NOT NULL COMMENT '维度',
    `province`   varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '省份',
    `city`       varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `district`   varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '县/区',
    `street`     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '街道',
    `address`    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '详细地址',
    `created_at` datetime(0)                                             NULL DEFAULT NULL COMMENT '添加时间',
    `sync_time`  datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `province_city` (`province`, `city`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '用户地理位置轨迹表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_sms
-- ----------------------------
DROP TABLE IF EXISTS `data_user_sms`;
CREATE TABLE `data_user_sms`
(
    `id`            bigint(20) UNSIGNED                                      NOT NULL COMMENT '用户短信自增长id',
    `app_id`        bigint(20)                                               NOT NULL,
    `user_id`       bigint(20)                                               NOT NULL COMMENT '用户id',
    `sms_telephone` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci   NULL DEFAULT NULL COMMENT '短信【发送|接收】电话',
    `sms_centent`   varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '短信内容',
    `sms_date`      varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci   NULL DEFAULT NULL COMMENT '短信【发送|接收】的时间',
    `type`          tinyint(4)                                               NULL DEFAULT 1 COMMENT '1 接收 2发送',
    `created_at`    datetime(0)                                              NULL DEFAULT NULL COMMENT '添加时间',
    `sync_time`     datetime(0)                                              NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '记录同步时间',
    UNIQUE INDEX `unique_index` (`user_id`, `sms_telephone`, `sms_date`, `app_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '用户短信表 '
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_third_data
-- ----------------------------
DROP TABLE IF EXISTS `data_user_third_data`;
CREATE TABLE `data_user_third_data`
(
    `id`         bigint(20) UNSIGNED                                     NOT NULL,
    `app_id`     bigint(20)                                              NOT NULL,
    `user_id`    bigint(20)                                              NULL DEFAULT NULL,
    `type`       varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `channel`    varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `result`     text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL,
    `report_id`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `created_at` datetime(0)                                             NULL DEFAULT NULL COMMENT '创建时间',
    `updated_at` datetime(0)                                             NULL DEFAULT NULL COMMENT '更新时间',
    `res_status` tinyint(4)                                              NULL DEFAULT 0 COMMENT '-1没有 1匹配 2不匹配',
    `status`     tinyint(4)                                              NULL DEFAULT 1 COMMENT '状态 1正常 -1失效',
    `value`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `sync_time`  datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for data_user_work
-- ----------------------------
DROP TABLE IF EXISTS `data_user_work`;
CREATE TABLE `data_user_work`
(
    `id`                      bigint(13) UNSIGNED                                     NOT NULL COMMENT '用户工作信息自增长ID',
    `app_id`                  bigint(20) UNSIGNED                                     NOT NULL COMMENT 'merchant_id',
    `user_id`                 bigint(13)                                              NOT NULL COMMENT '用户id',
    `profession`              varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '职业',
    `workplace_pincode`       varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '工作地点邮编',
    `company`                 varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '公司',
    `work_address1`           varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '工作地点1',
    `work_address2`           varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '工作地点2',
    `work_positon`            varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '工作位置',
    `salary`                  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '薪水',
    `work_email`              varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '工作邮箱',
    `work_start_date`         varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '开始工作时间',
    `work_experience_years`   tinyint(4)                                              NULL     DEFAULT 0 COMMENT '工作经验，单位/年',
    `work_experience_months`  tinyint(4)                                              NULL     DEFAULT 0 COMMENT '工作经验，单位/月',
    `work_phone`              varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT '' COMMENT '工作电话',
    `work_card_front_file_id` bigint(20)                                              NULL     DEFAULT 0 COMMENT '工作证正面文件id',
    `work_card_back_file_id`  bigint(20)                                              NULL     DEFAULT 0 COMMENT '工作证反面文件id',
    `employment_type`         varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT '' COMMENT '就业类型',
    `created_at`              datetime(0)                                             NULL     DEFAULT NULL,
    `updated_at`              datetime(0)                                             NULL     DEFAULT NULL,
    `occupation`              varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '一级职业',
    `sync_time`               datetime(0)                                             NULL     DEFAULT CURRENT_TIMESTAMP(0),
    UNIQUE INDEX `unique_index` (`id`, `app_id`, `user_id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for experian_account_detail
-- ----------------------------
DROP TABLE IF EXISTS `experian_account_detail`;
CREATE TABLE `experian_account_detail`
(
    `id`                                            bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`                                        bigint(20)                                              NOT NULL,
    `user_id`                                       bigint(20)                                              NOT NULL,
    `relate_id`                                     bigint(20)                                              NOT NULL,
    `relate_type`                                   varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '关联类型  third_experian ',
    `detail_no`                                     varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '一份报告下有多份detail',
    `identification_number`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IdentificationNumber',
    `subscriber_name`                               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SubscriberName',
    `account_number`                                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AccountNumber',
    `portfolio_type`                                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PortfolioType',
    `account_type`                                  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AccountType',
    `open_date`                                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'OpenDate',
    `credit_limit_amount`                           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CreditLimitAmount',
    `highest_credit_or_original_loan_amount`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'HighestCreditOrOriginalLoanAmount',
    `account_status`                                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AccountStatus',
    `payment_rating`                                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PaymentRating',
    `payment_history_profile`                       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PaymentHistoryProfile',
    `special_comment`                               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SpecialComment',
    `current_balance`                               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CurrentBalance',
    `amount_past_due`                               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AmountPastDue',
    `original_charge_off_amount`                    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'OriginalChargeOffAmount',
    `date_reported`                                 varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DateReported',
    `date_of_first_delinquency`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DateOfFirstDelinquency',
    `date_closed`                                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DateClosed',
    `date_of_last_payment`                          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DateOfLastPayment',
    `suit_filed_willful_default_written_off_status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SuitFiledWillfulDefaultWrittenOffStatus',
    `suit_filed_wilful_default`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SuitFiledWilfulDefault',
    `written_off_settled_status`                    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'WrittenOffSettledStatus',
    `value_of_credits_las_month`                    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ValueOfCreditsLasMonth',
    `type_of_collateral`                            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TypeOfCollateral',
    `written_off_amt_total`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'WrittenOffAmtTotal',
    `written_off_amt_principal`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'WrittenOffAmtPrincipal',
    `rate_of_interest`                              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'RateOfInterest',
    `repayment_tenure`                              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'RepaymentTenure',
    `promotional_rate_flag`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PromotionalRateFlag',
    `income`                                        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Income',
    `income_indicator`                              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IncomeIndicator',
    `income_frequency_indicator`                    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IncomeFrequencyIndicator',
    `default_status_date`                           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DefaultStatusDate',
    `litigation_status_date`                        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'LitigationStatusDate',
    `write_off_status_date`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'WriteOffStatusDate',
    `date_of_addition`                              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DateOfAddition',
    `currency_code`                                 varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CurrencyCode',
    `subscriber_comments`                           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SubscriberComments',
    `account_holder_type_code`                      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AccountHolderTypeCode',
    `cais_account_history_year`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Year',
    `cais_account_history_month`                    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Month',
    `days_past_due`                                 varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DaysPastDue',
    `asset_classification`                          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AssetClassification',
    `advanced_account_history_year`                 varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Year',
    `advanced_account_history_month`                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Month',
    `advanced_account_history_cash_limit`           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CashLimit',
    `advanced_account_history_credit_limit_amount`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CreditLimitAmount',
    `advanced_account_history_emi_amount`           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'EMIAmount',
    `advanced_account_history_current_balance`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CurrentBalance',
    `advanced_account_history_amount_past_due`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AmountPastDue',
    `surname_non_normalized`                        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SurnameNonNormalized',
    `first_name_non_normalized`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'FirstNameNonNormalized',
    `middle_name1_non_normalized`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'MiddleName1NonNormalized',
    `middle_name2_non_normalized`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'MiddleName2NonNormalized',
    `middle_name3_non_normalized`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'MiddleName3NonNormalized',
    `alias`                                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Alias',
    `gender_code`                                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'GenderCode',
    `income_taxpan`                                 varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IncomeTAXPAN',
    `date_of_birth`                                 varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DateOfBirth',
    `first_line_of_address_non_normalized`          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'FirstLineOfAddressNonNormalized',
    `second_line_of_address_non_normalized`         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SecondLineOfAddressNonNormalized',
    `third_lin_of_address_non_normalized`           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ThirdLinOfAddressNonNormalized',
    `city_non_normalized`                           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CityNonNormalized',
    `fifth_line_of_address_non_normalized`          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'FifthLineOfAddressNonNormalized',
    `state_non_normalized`                          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'StateNonNormalized',
    `zip_postal_code_non_normalized`                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ZipPostalCodeNonNormalized',
    `country_code_non_normalized`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CountryCodeNonNormalized',
    `address_indicator_non_normalized`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AddressIndicatorNonNormalized',
    `residence_code_non_normalized`                 varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ResidenceCodeNonNormalized',
    `telephone_number`                              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TelephoneNumber',
    `telephone_type`                                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TelephoneType',
    `income_tax_pan`                                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IncomeTaxPan',
    `pan_issue_date`                                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PanIssueDate',
    `pan_expiration_date`                           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PanExpirationDate',
    `driver_license_number`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DriverLicenseNumber',
    `driver_license_issue_date`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DriverLicenseIssueDate',
    `driver_license_expiration_date`                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DriverLicenseExpirationDate',
    `email_id`                                      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'EmailId',
    `created_at`                                    datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_type_detail_no_id` (`relate_id`, `relate_type`, `detail_no`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 32
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'Experian 征信报告解析 - 账户详情'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for experian_account_summary
-- ----------------------------
DROP TABLE IF EXISTS `experian_account_summary`;
CREATE TABLE `experian_account_summary`
(
    `id`                                        bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`                                    bigint(20)                                              NOT NULL,
    `user_id`                                   bigint(20)                                              NOT NULL,
    `relate_id`                                 bigint(20)                                              NOT NULL,
    `relate_type`                               varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '关联类型  third_experian ',
    `bureau_score`                              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'BureauScore',
    `bureau_score_confid_level`                 varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'BureauScoreConfidLevel',
    `caps_last_180_days`                        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CAPSLast180Days',
    `caps_last_30_days`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CAPSLast30Days',
    `caps_last_7_days`                          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CAPSLast7Days',
    `caps_last_90_days`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CAPSLast90Days',
    `cad_suit_filed_current_balance`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CADSuitFiledCurrentBalance',
    `credit_account_active`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CreditAccountActive',
    `credit_account_closed`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CreditAccountClosed',
    `credit_account_default`                    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CreditAccountDefault',
    `credit_account_total`                      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CreditAccountTotal',
    `outstanding_balance_all`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'OutstandingBalanceAll',
    `outstanding_balance_secured`               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'OutstandingBalanceSecured',
    `outstanding_balance_secured_percentage`    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'OutstandingBalanceSecuredPercentage',
    `outstanding_balance_un_secured`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'OutstandingBalanceUnSecured',
    `outstanding_balance_un_secured_percentage` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'OutstandingBalanceUnSecuredPercentage',
    `non_credit_caps_last_180_days`             varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'NonCreditCAPSLast180Days',
    `non_credit_caps_last_30_days`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'NonCreditCAPSLast30Days',
    `non_credit_caps_last_7_days`               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'NonCreditCAPSLast7Days',
    `non_credit_caps_last_90_days`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'NonCreditCAPSLast90Days',
    `total_caps_last_180_days`                  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TotalCAPSLast180Days',
    `total_caps_last_30_days`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TotalCAPSLast30Days',
    `total_caps_last_7_days`                    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TotalCAPSLast7Days',
    `total_caps_last_90_days`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TotalCAPSLast90Days',
    `exact_match`                               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ExactMatch',
    `user_message_text`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'UserMessageText',
    `created_at`                                datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_type_id` (`relate_id`, `relate_type`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 7
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'Experian 征信报告解析 - 账户概要'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for experian_individual_info
-- ----------------------------
DROP TABLE IF EXISTS `experian_individual_info`;
CREATE TABLE `experian_individual_info`
(
    `id`                               bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`                           bigint(20)                                              NOT NULL,
    `user_id`                          bigint(20)                                              NOT NULL,
    `relate_id`                        bigint(20)                                              NOT NULL,
    `relate_type`                      varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '关联类型  third_experian ',
    `username`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Username',
    `report_date`                      datetime(0)                                             NULL DEFAULT NULL COMMENT 'ReportDate',
    `version`                          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Version',
    `report_number`                    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ReportNumber',
    `subscriber_name`                  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SubscriberName',
    `date_of_birth_applicant`          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DateOfBirthApplicant',
    `driver_license_expiration_date`   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DriverLicenseExpirationDate',
    `driver_license_issue_date`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DriverLicenseIssueDate',
    `driver_license_number`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'DriverLicenseNumber',
    `email_id`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'EmailId',
    `first_name`                       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'FirstName',
    `gender_code`                      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'GenderCode',
    `income_tax_pan`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IncomeTaxPan',
    `last_name`                        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'LastName',
    `mobile_phone_number`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'MobilePhoneNumber',
    `pan_expiration_date`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PanExpirationDate',
    `pan_issue_date`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PanIssueDate',
    `passport_expiration_date`         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PassportExpirationDate',
    `passport_issue_date`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PassportIssueDate',
    `passport_number`                  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PassportNumber',
    `ration_card_expiration_date`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'RationCardExpirationDate',
    `ration_card_issue_date`           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'RationCardIssueDate',
    `ration_card_number`               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'RationCardNumber',
    `telephone_type`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TelephoneType',
    `universal_id_expiration_date`     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'UniversalIdExpirationDate',
    `universal_id_issue_date`          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'UniversalIdIssueDate',
    `universal_id_number`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'UniversalIdNumber',
    `voter_id_expiration_date`         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'VoterIdExpirationDate',
    `voter_id_issue_date`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'VoterIdIssueDate',
    `voters_identity_card`             varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'VotersIdentityCard',
    `employment_status`                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'EmploymentStatus',
    `income`                           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Income',
    `marital_status`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'MaritalStatus',
    `number_of_major_credit_card_held` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'NumberOfMajorCreditCardHeld',
    `time_with_employer`               varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'TimeWithEmployer',
    `city`                             varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'City',
    `state`                            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'State',
    `pin_code`                         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PinCode',
    `country_code`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'CountryCode',
    `bldg_no_society_name`             varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'BldgNoSocietyName',
    `flat_no_plot_no_house_no`         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'FlatNoPlotNoHouseNo',
    `road_no_name_area_locality`       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'RoadNoNameAreaLocality',
    `created_at`                       datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_type_id` (`relate_id`, `relate_type`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 7
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'Experian 征信报告解析 - 个人信息'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for experian_request_Info
-- ----------------------------
DROP TABLE IF EXISTS `experian_request_Info`;
CREATE TABLE `experian_request_Info`
(
    `id`            bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`        bigint(20)                                              NOT NULL,
    `user_id`       bigint(20)                                              NOT NULL,
    `relate_id`     bigint(20)                                              NOT NULL,
    `relate_type`   varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '关联类型  third_experian ',
    `pan`           varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL,
    `func_type`     varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL,
    `first_name`    varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL,
    `last_name`     varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL,
    `mobile`        varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL,
    `gender`        varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL,
    `date_of_birth` date                                                    NULL     DEFAULT NULL,
    `city`          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL,
    `state`         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL,
    `pin_code`      varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL,
    `address`       text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL,
    `office_name`   varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL,
    `request_time`  int(16)                                                 NULL     DEFAULT NULL,
    `report_id`     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL,
    `created_at`    datetime(0)                                             NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_type_id` (`relate_id`, `relate_type`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 8
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'Experian 征信报告解析 - 查询信息'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for high_risk_phonenumber
-- ----------------------------
DROP TABLE IF EXISTS `high_risk_phonenumber`;
CREATE TABLE `high_risk_phonenumber`
(
    `id`          bigint(20)                                              NOT NULL AUTO_INCREMENT,
    `type`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '类别 loanapp：用户通讯录存储名击中app名',
    `name`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '名称',
    `phonenumber` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT '号码',
    `init_dt`     datetime(0)                                             NULL     DEFAULT NULL COMMENT '首次击中时间',
    `last_dt`     datetime(0)                                             NULL     DEFAULT NULL COMMENT '最后一次击中时间',
    `times_cnt`   int(11)                                                 NOT NULL COMMENT '累计击中次数',
    `created_at`  datetime(0)                                             NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    `result_json` text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '结果',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `phonenumber` (`phonenumber`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 16
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hm_account_summary
-- ----------------------------
DROP TABLE IF EXISTS `hm_account_summary`;
CREATE TABLE `hm_account_summary`
(
    `id`                                     bigint(20) UNSIGNED                                    NOT NULL AUTO_INCREMENT,
    `app_id`                                 bigint(20)                                             NOT NULL,
    `user_id`                                bigint(20) UNSIGNED                                    NOT NULL,
    `relate_id`                              bigint(20) UNSIGNED                                    NOT NULL,
    `report_id`                              varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'REPORT-ID ',
    `inquries_in_last_six_months`            varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'INQURIES-IN-LAST-SIX-MONTHS',
    `length_of_credit_history_year`          varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'LENGTH-OF-CREDIT-HISTORY-YEAR',
    `length_of_credit_history_month`         varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'LENGTH-OF-CREDIT-HISTORY-MONTH',
    `average_account_age_year`               varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'AVERAGE-ACCOUNT-AGE-YEAR',
    `average_account_age_month`              varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'AVERAGE-ACCOUNT-AGE-MONTH',
    `new_accounts_in_last_six_months`        varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'NEW-ACCOUNTS-IN-LAST-SIX-MONTHS',
    `new_delinq_account_in_last_six_months`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'NEW-DELINQ-ACCOUNT-IN-LAST-SIX-MONTHS',
    `primary_number_of_accounts`             varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'PRIMARY-NUMBER-OF-ACCOUNTS',
    `rimary_active_number_of_accounts`       varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'RIMARY-ACTIVE-NUMBER-OF-ACCOUNTS',
    `primary_overdue_number_of_accounts`     varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'PRIMARY-OVERDUE-NUMBER-OF-ACCOUNTS',
    `primary_secured_number_of_accounts`     varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'PRIMARY-SECURED-NUMBER-OF-ACCOUNTS',
    `primary_unsecured_number_of_accounts`   varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'PRIMARY-UNSECURED-NUMBER-OF-ACCOUNTS',
    `primary_untagged_number_of_accounts`    varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'PRIMARY-UNTAGGED-NUMBER-OF-ACCOUNTS',
    `primary_current_balance`                varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'PRIMARY-CURRENT-BALANCE',
    `primary_sanctioned_amount`              varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'PRIMARY-SANCTIONED-AMOUNT',
    `rimary_disbursed_amount`                varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'RIMARY-DISBURSED-AMOUNT',
    `secondary_number_of_accounts`           varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-NUMBER-OF-ACCOUNTS',
    `secondary_active_number_of_accounts`    varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-ACTIVE-NUMBER-OF-ACCOUNTS',
    `secondary_overdue_number_of_accounts`   varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-OVERDUE-NUMBER-OF-ACCOUNTS',
    `secondary_secured_number_of_accounts`   varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-SECURED-NUMBER-OF-ACCOUNTS',
    `secondary_unsecured_number_of_accounts` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-UNSECURED-NUMBER-OF-ACCOUNTS',
    `secondary_untagged_number_of_accounts`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-UNTAGGED-NUMBER-OF-ACCOUNTS',
    `secondary_current_balance`              varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-CURRENT-BALANCE',
    `secondary_sanctioned_amount`            varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-SANCTIONED-AMOUNT',
    `secondary_disbursed_amount`             varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'SECONDARY-DISBURSED-AMOUNT',
    `created_at`                             datetime(0)                                            NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_id` (`relate_id`) USING BTREE,
    INDEX `report_id` (`report_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 39
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'HighMark征信报告解析-账户信息 ACCOUNTS-SUMMARY'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hm_employment_detail
-- ----------------------------
DROP TABLE IF EXISTS `hm_employment_detail`;
CREATE TABLE `hm_employment_detail`
(
    `id`               bigint(20) UNSIGNED                                    NOT NULL AUTO_INCREMENT,
    `app_id`           bigint(20)                                             NOT NULL,
    `user_id`          bigint(20) UNSIGNED                                    NOT NULL,
    `relate_id`        bigint(20) UNSIGNED                                    NOT NULL,
    `report_id`        varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'REPORT-ID',
    `acct_type`        varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'ACCT-TYPE',
    `date_reported`    date                                                   NULL     DEFAULT NULL COMMENT 'DATE-REPORTED',
    `occupation`       varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'OCCUPATION',
    `income`           varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'INCOME',
    `income_frequency` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'INCOME-FREQUENCY',
    `income_indicator` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'INCOME-INDICATOR',
    `created_at`       datetime(0)                                            NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_id` (`relate_id`) USING BTREE,
    INDEX `report_id` (`report_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 33
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'HighMark征信报告解析-职业信息 EMPLOYMENT-DETAILS'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hm_individual_info
-- ----------------------------
DROP TABLE IF EXISTS `hm_individual_info`;
CREATE TABLE `hm_individual_info`
(
    `id`               bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`           bigint(20)                                              NOT NULL,
    `user_id`          bigint(20) UNSIGNED                                     NOT NULL COMMENT '用户ID',
    `relate_id`        bigint(20) UNSIGNED                                     NOT NULL,
    `report_id`        varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'REPORT-ID',
    `date_of_request`  date                                                    NULL     DEFAULT NULL COMMENT 'DATE-OF-REQUEST',
    `date_of_issue`    date                                                    NULL     DEFAULT NULL COMMENT 'DATE-OF-ISSUE',
    `batch_id`         varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'BATCH-ID',
    `status`           varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'STATUS',
    `name`             varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'NAME',
    `father`           varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'FATHER',
    `dob`              date                                                    NULL     DEFAULT NULL COMMENT 'DOB',
    `pan`              varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'PAN',
    `email_1`          varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'EMAIL-1',
    `address_1`        varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'ADDRESS-1',
    `address_2`        varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'ADDRESS-2',
    `address_3`        varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'ADDRESS-3',
    `phone_1`          varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'PHONE-1',
    `phone_2`          varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'PHONE-2',
    `phone_3`          varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'PHONE-3',
    `occupation`       varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'OCCUPATION',
    `income_frequency` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'INCOME-FREQUENCY',
    `income_indicator` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'INCOME-INDICATOR',
    `score_type`       varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'SCORE-TYPE',
    `score_value`      varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'SCORE-VALUE',
    `score_comments`   varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'CORE-COMMENTS',
    `created_at`       datetime(0)                                             NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_id` (`relate_id`) USING BTREE,
    INDEX `report_id` (`report_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 16
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'HighMark征信报告解析-个人信息'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hm_info_variation
-- ----------------------------
DROP TABLE IF EXISTS `hm_info_variation`;
CREATE TABLE `hm_info_variation`
(
    `id`            bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`        bigint(20)                                              NOT NULL,
    `user_id`       bigint(20) UNSIGNED                                     NOT NULL,
    `relate_id`     bigint(20) UNSIGNED                                     NOT NULL,
    `report_id`     varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'REPORT-ID',
    `key`           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL,
    `value`         text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL,
    `reported_date` date                                                    NULL     DEFAULT NULL COMMENT 'REPORTED DATE',
    `created_at`    datetime(0)                                             NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_id` (`relate_id`) USING BTREE,
    INDEX `report_id` (`report_id`) USING BTREE,
    INDEX `key` (`key`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 776
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'HighMark征信报告解析-个人信息变化记录 PERSONAL-INFO-VARIATION'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hm_inquriy_hist_detail
-- ----------------------------
DROP TABLE IF EXISTS `hm_inquriy_hist_detail`;
CREATE TABLE `hm_inquriy_hist_detail`
(
    `id`             bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`         bigint(20)                                              NOT NULL,
    `user_id`        bigint(20) UNSIGNED                                     NOT NULL,
    `relate_id`      bigint(20) UNSIGNED                                     NOT NULL,
    `report_id`      varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'REPORT-ID',
    `inqury_no`      varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'inqury_no 用于区分同分报告下的记录',
    `member_name`    varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'MEMBER-NAME',
    `inquiry_date`   date                                                    NULL     DEFAULT NULL COMMENT 'INQUIRY-DATE',
    `purpose`        varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'PURPOSE',
    `ownership_type` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'OWNERSHIP-TYPE',
    `amount`         varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'AMOUNT',
    `remark`         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'REMARK',
    `created_at`     datetime(0)                                             NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_id` (`relate_id`) USING BTREE,
    INDEX `report_id` (`report_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 79
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'HighMark征信报告解析-被查询历史 INQUIRY-HISTORY'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hm_loan_hist_detail
-- ----------------------------
DROP TABLE IF EXISTS `hm_loan_hist_detail`;
CREATE TABLE `hm_loan_hist_detail`
(
    `id`                       bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`                   bigint(20)                                              NOT NULL,
    `user_id`                  bigint(20) UNSIGNED                                     NOT NULL,
    `relate_id`                bigint(20) UNSIGNED                                     NOT NULL,
    `report_id`                varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'REPORT-ID',
    `loan_no`                  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'loan_no',
    `acct_number`              varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'ACCT-NUMBER',
    `credit_guarantor`         varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'CREDIT-GUARANTOR',
    `acct_type`                varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'ACCT-TYPE',
    `date_reported`            date                                                    NULL     DEFAULT NULL COMMENT 'DATE-REPORTED',
    `ownership_ind`            varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'OWNERSHIP-IND',
    `account_status`           varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'ACCOUNT-STATUS',
    `disbursed_amt`            varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'DISBURSED-AMT',
    `disbursed_dt`             date                                                    NULL     DEFAULT NULL COMMENT 'DISBURSED-DT',
    `last_payment_date`        date                                                    NULL     DEFAULT NULL COMMENT 'LAST-PAYMENT-DATE',
    `closed_date`              date                                                    NULL     DEFAULT NULL COMMENT 'CLOSED-DATE',
    `installment_amt`          varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'INSTALLMENT-AMT',
    `installment_freq`         varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'INSTALLMENT-freq',
    `overdue_amt`              varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'OVERDUE-AMT',
    `write_off_amt`            varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'WRITE-OFF-AMT',
    `current_bal`              varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'CURRENT-BAL',
    `combined_payment_history` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT 'COMBINED-PAYMENT-HISTORY',
    `matched_type`             varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'MATCHED-TYPE',
    `linked_accounts`          text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT 'LINKED-ACCOUNTS',
    `security_details`         text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT 'SECURITY-DETAILS',
    `interest_rate`            varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'INTEREST-RATE',
    `actual_payment`           varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'ACTUAL-PAYMENT',
    `principal_write_off_amt`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT 'PRINCIPAL-WRITE-OFF-AMT',
    `created_at`               datetime(0)                                             NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `relate_id` (`relate_id`) USING BTREE,
    INDEX `report_id` (`report_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 358
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = 'HighMark征信报告解析-贷款历史 LOAN-DETAILS'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pincode
-- ----------------------------
DROP TABLE IF EXISTS `pincode`;
CREATE TABLE `pincode`
(
    `id`           int(11) UNSIGNED                                            NOT NULL AUTO_INCREMENT,
    `pincode`      varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci  NULL     DEFAULT NULL,
    `districtname` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL     DEFAULT NULL,
    `statename`    varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL     DEFAULT NULL COMMENT '邦 | 直辖区',
    `type`         tinyint(1)                                                  NOT NULL DEFAULT 0 COMMENT '0 从官方提供数据录入 1 手工录入(补充)',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `pincode` (`pincode`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 155557
  CHARACTER SET = latin1
  COLLATE = latin1_swedish_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for profession
-- ----------------------------
DROP TABLE IF EXISTS `profession`;
CREATE TABLE `profession`
(
    `occupation` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
    `profession` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL
) ENGINE = InnoDB
  CHARACTER SET = latin1
  COLLATE = latin1_swedish_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for request_packet
-- ----------------------------
DROP TABLE IF EXISTS `request_packet`;
CREATE TABLE `request_packet`
(
    `id`            bigint(13) UNSIGNED                                     NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `app_id`        bigint(13)                                              NOT NULL,
    `relate_id`     bigint(13)                                              NOT NULL COMMENT '关联ID',
    `relate_type`   varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '关联类型',
    `type`          tinyint(4)                                              NOT NULL COMMENT '报文类型',
    `url`           text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '请求url',
    `request_info`  text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '请求报文',
    `response_info` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci   NULL COMMENT '响应报文',
    `http_code`     varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT 'http状态码',
    `remark`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '备注',
    `updated_at`    datetime(0)                                             NOT NULL COMMENT '更新时间',
    `created_at`    datetime(0)                                             NOT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `relate_id_type` (`relate_id`, `relate_type`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1483
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '请求报文记录表'
  ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for risk_associated_record
-- ----------------------------
DROP TABLE IF EXISTS `risk_associated_record`;
CREATE TABLE `risk_associated_record`
(
    `id`                bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `account_id`        varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
    `aadhaar`           varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `pancard`           varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `telephone`         varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `bankcard`          varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `voteid`            varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL,
    `passport`          varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `imei`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `email`             varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `apply_cnt`         int(10)                                                 NULL DEFAULT NULL,
    `issued_cnt`        int(10)                                                 NULL DEFAULT NULL,
    `max_overdue_days`  int(10)                                                 NULL DEFAULT NULL,
    `max_issued_amount` decimal(10, 0)                                          NULL DEFAULT NULL,
    `merchant_cnt`      int(10)                                                 NULL DEFAULT NULL,
    `created_at`        datetime(0)                                             NULL DEFAULT NULL,
    `updated_at`        datetime(0)                                             NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `account_id` (`account_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 13
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for risk_associated_record_var
-- ----------------------------
DROP TABLE IF EXISTS `risk_associated_record_var`;
CREATE TABLE `risk_associated_record_var`
(
    `id`                        bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `risk_associated_record_id` bigint(20) UNSIGNED                                     NOT NULL COMMENT '关联ID',
    `type`                      varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '类型',
    `value`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '值',
    `cnt`                       int(10)                                                 NULL DEFAULT NULL COMMENT '出现次数',
    `created_at`                datetime(0)                                             NULL DEFAULT NULL,
    `updated_at`                datetime(0)                                             NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `type_value_index` (`type`, `value`) USING BTREE,
    INDEX `risk_associated_record_id` (`risk_associated_record_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 539
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for risk_system_approve_collection_phonenum
-- ----------------------------
DROP TABLE IF EXISTS `risk_system_approve_collection_phonenum`;
CREATE TABLE `risk_system_approve_collection_phonenum`
(
    `id`                  bigint(20)                                              NOT NULL AUTO_INCREMENT,
    `collection_phonenum` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '号码',
    `app_name`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'app名称',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 930
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for system_approve_record
-- ----------------------------
DROP TABLE IF EXISTS `system_approve_record`;
CREATE TABLE `system_approve_record`
(
    `id`                 bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`             bigint(20)                                              NOT NULL COMMENT '商户id',
    `task_id`            bigint(20)                                              NOT NULL COMMENT 'task id',
    `module`             varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '所属模块 basic:基础 credit:征信',
    `user_id`            bigint(20)                                              NOT NULL COMMENT '用户id',
    `order_id`           bigint(20)                                              NOT NULL COMMENT '订单id',
    `user_type`          tinyint(4)                                              NOT NULL COMMENT '用户类型 0:新用户  1:老用户',
    `result`             tinyint(4)                                              NOT NULL COMMENT '结果 1:通过  2:拒绝',
    `hit_rule_cnt`       smallint(5) UNSIGNED                                    NOT NULL DEFAULT 0 COMMENT 'hit_rule命中数',
    `hit_rule`           text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '命中拒绝规则及对应命中值，json串',
    `exe_rule`           text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '规则执行记录',
    `extra_code`         varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT '额外标识',
    `description`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '注释说明',
    `relate_id`          bigint(20)                                              NULL     DEFAULT NULL COMMENT '关联ID',
    `experian_relate_id` bigint(20)                                              NULL     DEFAULT NULL COMMENT 'experian关联ID',
    `created_at`         datetime(0)                                             NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `order_id` (`order_id`) USING BTREE,
    INDEX `task_id` (`task_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1042
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for system_approve_rule
-- ----------------------------
DROP TABLE IF EXISTS `system_approve_rule`;
CREATE TABLE `system_approve_rule`
(
    `id`           bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT COMMENT 'id',
    `app_id`       bigint(20)                                              NOT NULL COMMENT '商户id',
    `module`       varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属模块 basic:基础 credit:征信',
    `user_quality` tinyint(4)                                              NOT NULL COMMENT '用户类型 0:新用户 1:老用户',
    `rule`         varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规则名',
    `type`         tinyint(4)                                              NOT NULL COMMENT '类型 1:字符串 2:范围 3:选项 4:多属性',
    `value`        text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '规则值',
    `status`       tinyint(4)                                              NOT NULL COMMENT '状态 -1:删除 1:正常 2:关闭-执行规则保存规则执行记录，但结果不影响机审结果  3:彻底关闭-彻底不执行规则(主要为防止某些规则执行占用时间)',
    `created_at`   datetime(0)                                             NOT NULL COMMENT '创建时间',
    `updated_at`   datetime(0)                                             NOT NULL COMMENT '修改时间',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `rule` (`rule`, `app_id`, `user_quality`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 5208
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for system_approve_sandbox_record
-- ----------------------------
DROP TABLE IF EXISTS `system_approve_sandbox_record`;
CREATE TABLE `system_approve_sandbox_record`
(
    `id`           bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`       bigint(20)                                              NOT NULL COMMENT '商户id',
    `module`       varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '所属模块 basic:基础 credit:征信',
    `user_id`      bigint(20)                                              NOT NULL COMMENT '用户id',
    `order_id`     bigint(20)                                              NOT NULL COMMENT '订单id',
    `user_type`    tinyint(4)                                              NOT NULL COMMENT '用户类型 0:新用户  1:老用户',
    `result`       tinyint(4)                                              NOT NULL COMMENT '结果 1:通过  2:拒绝',
    `hit_rule_cnt` smallint(5) UNSIGNED                                    NOT NULL DEFAULT 0 COMMENT 'hit_rule命中数',
    `hit_rule`     text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '命中拒绝规则及对应命中值，json串',
    `exe_rule`     text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '规则执行记录',
    `extra_code`   varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT '额外标识',
    `description`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '注释说明',
    `relate_id`    bigint(20)                                              NULL     DEFAULT NULL COMMENT '关联ID',
    `created_at`   datetime(0)                                             NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `order_id` (`order_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for task
-- ----------------------------
DROP TABLE IF EXISTS `task`;
CREATE TABLE `task`
(
    `id`            bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`        bigint(20) UNSIGNED                                     NOT NULL,
    `task_no`       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '机审任务编号',
    `user_id`       bigint(20) UNSIGNED                                     NOT NULL COMMENT '用户id',
    `order_no`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单no',
    `status`        varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '状态 ',
    `result`        varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT 'NA' COMMENT '结果',
    `hit_rule_code` text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL,
    `task_desc`     varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT '' COMMENT '描述',
    `notice_url`    text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '通知地址',
    `account_id`    bigint(20)                                              NULL     DEFAULT NULL COMMENT '关联 risk_associated_record id',
    `created_at`    datetime(0)                                             NOT NULL COMMENT '创建时间',
    `updated_at`    datetime(0)                                             NOT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `task_no` (`task_no`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `order_no` (`order_no`) USING BTREE,
    INDEX `app_id` (`app_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 255
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '机审任务表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for task_data
-- ----------------------------
DROP TABLE IF EXISTS `task_data`;
CREATE TABLE `task_data`
(
    `id`         bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `task_id`    bigint(20)                                              NOT NULL COMMENT '机审任务id',
    `type`       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '类型',
    `status`     tinyint(4)                                              NOT NULL COMMENT '状态 ',
    `is_inner`   tinyint(4)                                              NOT NULL DEFAULT 0 COMMENT '是否内部数据项',
    `expire_by`  datetime(0)                                             NULL     DEFAULT NULL COMMENT '数据项准备过期时间(不为null的情况下，若过了过期时间还未完成当前项，则忽略)',
    `created_at` datetime(0)                                             NOT NULL COMMENT '创建时间',
    `updated_at` datetime(0)                                             NOT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `task_id` (`task_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 4357
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for task_notice
-- ----------------------------
DROP TABLE IF EXISTS `task_notice`;
CREATE TABLE `task_notice`
(
    `id`            bigint(20)                                              NOT NULL AUTO_INCREMENT COMMENT '机审通知表主键',
    `task_id`       bigint(20)                                              NOT NULL,
    `notice_url`    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '通知地址',
    `notice_info`   mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci   NULL COMMENT '通知信息',
    `response_info` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL     DEFAULT NULL COMMENT '响应信息',
    `status`        tinyint(4)                                              NOT NULL COMMENT '通知状态',
    `remark`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
    `created_at`    datetime(0)                                             NOT NULL COMMENT '更新时间',
    `updated_at`    datetime(0)                                             NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `task_id` (`task_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 269
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '机审通知记录'
  ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for test_user
-- ----------------------------
DROP TABLE IF EXISTS `test_user`;
CREATE TABLE `test_user`
(
    `id`              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '测试账号ID',
    `app_id`          bigint(20) UNSIGNED NOT NULL COMMENT 'merchant_id',
    `business_app_id` bigint(20) UNSIGNED NOT NULL COMMENT 'app_id',
    `user_id`         bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '用户ID',
    `user_type`       tinyint(2)          NULL DEFAULT 0 COMMENT '账号类型 0虚拟账号 1真实账号',
    `status`          tinyint(2) UNSIGNED NULL DEFAULT 1 COMMENT '账号状态 1正常 0禁用',
    `updated_at`      datetime(0)         NOT NULL COMMENT '更新时间',
    `created_at`      datetime(0)         NOT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 4
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for third_experian
-- ----------------------------
DROP TABLE IF EXISTS `third_experian`;
CREATE TABLE `third_experian`
(
    `id`            bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `batch_no`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '批次编号',
    `type`          varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '类型',
    `telephone`     varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL DEFAULT NULL COMMENT '手机号',
    `request_info`  text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL COMMENT '请求信息',
    `report_body`   mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci   NULL COMMENT '报告主体',
    `response_info` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci   NULL COMMENT '响应原文',
    `status`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '响应状态',
    `extra_info`    text CHARACTER SET utf8 COLLATE utf8_general_ci         NULL,
    `remark`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `created_at`    datetime(0)                                             NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `telephone` (`telephone`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1002
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '征信跑批数据存储'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for third_report
-- ----------------------------
DROP TABLE IF EXISTS `third_report`;
CREATE TABLE `third_report`
(
    `id`           bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`       bigint(20)                                              NOT NULL COMMENT '商户ID',
    `user_id`      bigint(20) UNSIGNED                                     NOT NULL COMMENT '用户ID',
    `type`         tinyint(4)                                              NOT NULL COMMENT '类型 1:征信报告 ',
    `channel`      varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL COMMENT '调用渠道  HighMark',
    `status`       tinyint(4)                                              NULL DEFAULT NULL COMMENT '状态 1:正常',
    `request_info` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci   NULL COMMENT '请求信息',
    `report_body`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci   NULL COMMENT '报告主体',
    `report_id`    varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '报告id',
    `unique_no`    varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '唯一标识号',
    `created_at`   datetime(0)                                             NULL DEFAULT NULL COMMENT '创建时间',
    `updated_at`   datetime(0)                                             NULL DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `user_id` (`user_id`) USING BTREE,
    INDEX `report_id` (`report_id`) USING BTREE,
    INDEX `unique_no` (`unique_no`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 144
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '第三方报告表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user_associated_info
-- ----------------------------
DROP TABLE IF EXISTS `user_associated_info`;
CREATE TABLE `user_associated_info`
(
    `id`         bigint(20) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`     bigint(20)                                              NOT NULL,
    `user_id`    bigint(20)                                              NOT NULL,
    `type`       varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT '' COMMENT '特征值类型',
    `value`      varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '特征值',
    `created_at` datetime(0)                                             NOT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `value` (`value`) USING BTREE,
    INDEX `user_id_type` (`user_id`, `type`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 16396
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci COMMENT = '用户关联信息表，用来获取关联账户'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user_black
-- ----------------------------
DROP TABLE IF EXISTS `user_black`;
CREATE TABLE `user_black`
(
    `id`         bigint(16) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
    `app_id`     bigint(20) UNSIGNED                                     NOT NULL COMMENT '商户ID',
    `telephone`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT '',
    `hit_value`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '命中黑名单值',
    `hit_type`   varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL     DEFAULT NULL COMMENT '命中黑名单类型',
    `remark`     varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `status`     tinyint(4)                                              NOT NULL DEFAULT 0 COMMENT '状态',
    `type`       varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT '' COMMENT '禁用类型',
    `created_at` timestamp(0)                                            NULL     DEFAULT NULL,
    `updated_at` timestamp(0)                                            NULL     DEFAULT NULL,
    `black_time` datetime(0)                                             NULL     DEFAULT NULL COMMENT '黑名单生效时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `merchant_id` (`app_id`) USING BTREE,
    INDEX `telephone` (`telephone`) USING BTREE,
    INDEX `hit_value` (`hit_value`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 5120
  CHARACTER SET = utf8
  COLLATE = utf8_general_ci
  ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
