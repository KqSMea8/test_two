/*
Navicat MySQL Data Transfer

Source Server         : opensns
Source Server Version : 50619
Source Host           : 192.168.1.50:3306
Source Database       : sns

Target Server Type    : MYSQL
Target Server Version : 50619
File Encoding         : 65001

Date: 2016-11-17 15:05:00
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for jpgk_something_pic
-- ----------------------------
DROP TABLE IF EXISTS `jpgk_something_pic`;
CREATE TABLE `jpgk_something_pic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `app` varchar(50) NOT NULL COMMENT '模块',
  `row_id` int(11) NOT NULL COMMENT '模块数据的主键',
  `pic_id` int(11) NOT NULL COMMENT '图片主键',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='二手商品信息和图片的关系表';

-- ----------------------------
-- Table structure for jpgk_used_market_category
-- ----------------------------
DROP TABLE IF EXISTS `jpgk_used_market_category`;
CREATE TABLE `jpgk_used_market_category` (
  `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '二手商品分类id',
  `title` varchar(50) NOT NULL COMMENT '分类名称',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级分类',
  `sort` smallint(6) NOT NULL COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '分类状态(1--可用，2--删除 , 3--禁用)',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='分类';

-- ----------------------------
-- Table structure for jpgk_used_market_goods
-- ----------------------------
DROP TABLE IF EXISTS `jpgk_used_market_goods`;
CREATE TABLE `jpgk_used_market_goods` (
  `goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品主键',
  `uid` int(11) NOT NULL,
  `title` varchar(100) NOT NULL COMMENT '标题',
  `description` varchar(1000) DEFAULT NULL COMMENT '描述',
  `category_id` int(11) NOT NULL COMMENT '分类id',
  `sale_price` decimal(10,2) NOT NULL COMMENT '想卖多少',
  `bought_price` decimal(10,2) NOT NULL COMMENT '购入价格',
  `link_phone` varchar(11) NOT NULL COMMENT '联系电话',
  `link_name` varchar(50) NOT NULL COMMENT '联系人',
  `district_id` int(11) NOT NULL COMMENT '所在位置',
  `district_text` varchar(50) DEFAULT NULL COMMENT '所在位置文字描述',
  `start_time` int(11) NOT NULL COMMENT '发布有效期开始时间',
  `end_time` int(11) NOT NULL COMMENT '发布有效期结束时间',
  `check_status` tinyint(4) NOT NULL COMMENT '审核状态（1--新二手商品信息；2--审核通过；3--禁用）',
  `check_uid` int(11) DEFAULT NULL COMMENT '审核人',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  PRIMARY KEY (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='二手商品信息';
