/*
Navicat MySQL Data Transfer

Source Server         : 192.168.1.50
Source Server Version : 50619
Source Host           : 192.168.1.50:3306
Source Database       : sns

Target Server Type    : MYSQL
Target Server Version : 50619
File Encoding         : 65001

Date: 2016-11-10 10:17:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for jpgk_pay_record
-- ----------------------------
DROP TABLE IF EXISTS `jpgk_pay_record`;
CREATE TABLE `jpgk_pay_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status_pay` tinyint(4) NOT NULL COMMENT '支付状态：0->发起支付 5->支付成功 10->支付超时关闭（此状态设置需要定时任务） 15->支付失败',
  `service_id` int(11) NOT NULL,
  `pay_type` tinyint(4) NOT NULL COMMENT '1--微信；2--支付宝',
  `money` decimal(10,0) NOT NULL COMMENT '支付金额',
  `trade_code` varchar(20) NOT NULL COMMENT '交易号',
  `data_request` varchar(3000) NOT NULL COMMENT '请求第三方的数据',
  `time_notify` int(11) DEFAULT NULL COMMENT '回调时间',
  `data_notify` varchar(3000) DEFAULT NULL COMMENT '回调数据',
  `time_out` int(11) DEFAULT NULL COMMENT '过期时间',
  `time_create` int(11) NOT NULL,
  `time_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='餐讯网支付记录';

-- ----------------------------
-- Table structure for jpgk_pay_server
-- ----------------------------
DROP TABLE IF EXISTS `jpgk_pay_server`;
CREATE TABLE `jpgk_pay_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `app` varchar(50) NOT NULL,
  `row_id` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '0--不可用；1--可用',
  `ip` bigint(20) DEFAULT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='餐讯网服务支付记录';
