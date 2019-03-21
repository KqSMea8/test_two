<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * UCenter客户端配置文件
 * 注意：该配置文件请使用常量方式定义
 */

define('UC_APP_ID', 1); //应用ID
define('UC_API_TYPE', 'Model'); //可选值 Model / Service
define('UC_AUTH_KEY', '3FwDNrOHE4B2LfoWRydU5up7IC90lKnVcgaX8AYe'); //加密KEY

if(ENVIRONMENTSTATUS == 'Reality'){
    define('UC_DB_DSN', 'mysql://catering:lz517_catering@jpgktest.mysql.rds.aliyuncs.com:3306/catering'); // 数据库连接，使用Model方式调用API必须配置此项
}else{
    define('UC_DB_DSN', 'mysql://root:mysqlOne@192.168.1.50:3306/canxun_ceshi'); // 数据库连接，使用Model方式调用API必须配置此项
}
define('UC_TABLE_PREFIX', 'jpgk_'); // 数据表前缀，使用Model方式调用API必须配置此项
