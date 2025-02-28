ALTER TABLE `crm_marketing_phone_assign_log` 
ADD COLUMN `status` tinyint(4) NULL COMMENT '跳转之前的状态' AFTER `assign_time`;
ALTER TABLE `loan_multiple_config` 
ADD COLUMN `penalty_start_days` text NOT NULL COMMENT '罚息开始天数' AFTER `loan_again_days`;
ALTER TABLE `loan_multiple_config` 
ADD COLUMN `penalty_end_days` text NOT NULL COMMENT '罚息结束天数' AFTER `penalty_start_days`;
ALTER TABLE `loan_multiple_config` 
ADD COLUMN `penalty_rates` text NOT NULL COMMENT '罚息率' AFTER `penalty_end_days`;