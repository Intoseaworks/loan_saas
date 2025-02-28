ALTER TABLE `collection_detail` 
MODIFY COLUMN `reduction_setting` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '减免设置' AFTER `contact_num`;