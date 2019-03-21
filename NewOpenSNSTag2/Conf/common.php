<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com>
//<http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
* 系统配文件
* 所有系统级别的配置
*/

if(ENVIRONMENTSTATUS == 'Ceshi'){
    /*企业号配置信息*/
    define('COMPANY_CORPID','wx99e8470701c8d586');
    define('COMPANY_TOKEN','weixin');
    define('COMPANY_EMCODINGASEKEY','nkvaT4YGCVLJJyGhpEk59OW422lznhFgt9DBc8Az4yi');
    define('COMPANY_CUSTOMER_APPSECRET','Ctp14jLvxeZd88lij9KhfPkcPvPC5SaI2lmxaKOdkBw');//客服应用
    define('COMPANY_DISTRIBUTE_APPSECRET','cfTOHyYnbyIRnVoSGPilOxOI-7M4JruiR4LxeZgd0Gw');//分配主管应用
    define('COMPANY_CLEANING_APPSECRET','sPH5cSccU6bVTlOCmD_RRVlg9dzP9FvwiX4Wa6XYKuc');//清洗师傅应用
    define('COMPANY_CLEANKILL_APPSECRET','BO8b2AaM6NYmZGibjJLxOBFb5WHsA7xqq9VEWjeiyFY');//消杀师傅应用
    define('COMPANY_MAINTAIN_APPSECRET','bkhgx6JKR8VUAueSP7wJpSvbLdqGsFbyaoqmQqvRMnA');//维修师傅应用
    define('COMPANY_ENTERPRISE_APPSECRET','qs-CKAaSNjMsA2O5RHpTYmD64WEceMtaPcWexuLmj38');//企业主管应用
    define('COMPANY_CUSTOMER_SERVICE',1000006);//客服应用
    define('COMPANY_REPAIRE_MASTER',1000008);//维修师傅应用
    define('COMPANY_CLEANING_MASTER',1000009);//清洗师傅应用
    define('COMPANY_CLEANKILL_MASTER',1000010);//消杀师傅应用
    define('COMPANY_DISTRIBUTE_MANAGER',1000007);//分配主管应用
    define('COMPANY_ENTERPRISE_MASTER',1000013);//企业主管应用
    /*公众号配置信息*/
    define('WECHAT_APPID','wx1993b2f1110479ea');
    define('WECHAT_TOKEN','weixin');
    define('WECHAT_APPSECRET','850fcf341e897d4a8196e1e820900585');

    //微信公众号模板消息ID
    define('WECHAT_MSG_PAY_SUCCESS','XY5RhGj39jBHlTHMsxVn0lMyMwvi03wPKPsZ_0tnihc'); //支付成功通知
    define('WECHAT_MSG_ORDER_OUT','qiLG8IR4yYZTJGqk6zemDdN3z5xx5SFwoU9acquHA4k'); //订单超时提醒
    define('WECHAT_MSG_ORDER_PAY','MFMp2XuOdW7qrupQy4VUSafDK0IYZicVNkmH-PFRHtc'); //订单支付通知
    define('WECHAT_MSG_PAI_ORDER_SUCCESS','X5qyB7Ge8QSW8VmW2xevCrrI6-nmBSsTtpzEWuvL2Zs');//派单成功提醒
    define('WECHAT_MSG_ORDER_NO_PAY','YUg-axFHiyg_HqBkThkGWyJb24UJy8IiaGOlYniaouM');//订单未支付通知
    define('WECHAT_MSG_ORDER_OVER','9bEGHQVok1DBVRa93DYGxDQRvJAs9RAAXo-auHoG_io');//订单完成通知
    define('WECHAT_MSG_ORDER_CREATE','PhbOWr3Cb2MVa3TZGuzhSTy5MPRUbhotYnLs52Uh-IQ');//订单创建成功通知
    define('WECHAT_MSG_REGIST_SUCCESS','9-ayajvdfsAw7kLyds0VhHhHFyhkmhfmeteJcKMsvX8');//注册成功提醒
    define('WECHAT_MSG_ORDER_YS','iJDEcSlouLvszvKXN42iyrBcWXgplKrBanB7oyNjFi4');//订单验收提醒
    define('WECHAT_MSG_RECEICE_ORDER','iS6axFT0yt__N5KSnUCTH62GW6ICDjqeNhlZ9gKo_h8');//接单成功提醒
    define('WECHAT_MSG_DELIVERY_ORDER','l-Lw2lmyygz4pg83-4i4iKwWmZzkGMPvoXxoh1eoEms');//派单通知
    define('WECHAT_MSG_AGAIN_ORDER','ivd0PGSvZvZ67KMKdf_8ZXmsiL8K12YiSLf79xljnuk');//新预约订单通知（顾客再次下单）
    define('WECHAT_MSG_SERVICE_EXPIRATION','EpXhfxt7oH4UDewZMOpZbGkwT8kbtSskGM-sy5ki8j8');//服务到期提醒
    define('WECHAT_MSG_ORDER_CHANGE','m7Mj851LNDk9El28Ra9E_vvJJ8bDIEi4W4KOcnM_ovE');//订单修改通知
    define('WECHAT_MSG_BIND_STORE','RIJmuLxY8aqoRFTF612ICpt-GX07zUG0vHzAG0V_Uqc');//绑定结果通知
    define('WECHAT_MSG_DOOR_SERVICE','lMOPzRhbeVXasuHKRrcO_SEOQTK95aqnIQrRCxvNliQ');//上门服务通知

    //支付配置
    define('WECHAT_KEY','b33764c01a2d5b3336e0d578819acd12');
    define('WECHAT_MCHID','1499646552');
    return array(
        /* 模块相关配置 */
        'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
        'DEFAULT_MODULE'     => 'Home',
        'MODULE_DENY_LIST'   => array('Common', 'User'),
//'MODULE_ALLOW_LIST'  => array('Home','Admin'),

        /* 系统数据加密设置 */
        'DATA_AUTH_KEY' => '3FwDNrOHE4B2LfoWRydU5up7IC90lKnVcgaX8AYe', //默认数据加密KEY

        /* 调试配置 */
        'SHOW_PAGE_TRACE' => false,

        /* 用户相关设置 */
        'USER_MAX_CACHE'     => 1000, //最大缓存用户数
        'USER_ADMINISTRATOR' => 1, //管理员用户ID

        /* URL配置 */
        'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
        'URL_MODEL'            => 3, //URL模式  默认关闭伪静态
        'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
        'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符
        'URL_HTML_SUFFIX'       => 'html',  // URL伪静态后缀设置

        /* 全局过滤配置 */
        'DEFAULT_FILTER' => '', //全局过滤函数


        /* 数据库配置 */
//        'DB_TYPE'   => 'mysqli', // 数据库类型
//        'DB_HOST'   => 'localhost', // 服务器地址
//        'DB_NAME'   => 'canxuntest', // 数据库名
//        'DB_USER'   => 'root', // 用户名
//        'DB_PWD'    => '',  // 密码
//        'DB_PORT'   => '3306', // 端口
//        'DB_PREFIX' => 'jpgk_', // 数据库表前缀

        /* 数据库配置 */
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '192.168.1.50', // 服务器地址
        'DB_NAME'   => 'canxun_ceshi', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'mysqlOne',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'jpgk_', // 数据库表前缀

        /**
         * Ice 文件配置
         */
        'RPC_ICE_CONFIG' => [
            'Ice.Communicator.Key'     => 'Ice.Communicator.Key',
            'Ice.Default.Locator'     => 'TestIceRegistry/Locator:default -h 192.168.1.25 -p 4062',
//    'Ice.Default.Locator'     => 'WanIceRegistry/Locator:default -h rpc.lz517.net -p 4061',
            'Ice.Warn.Connections'     => '1',
            'Ice.Communicator.Expires' => '5'
            #ice注册中心地址
//    --Ice.Default.Locator=TestIceRegistry/Locator:default -h 192.168.1.25 -p 4062
//    --Ice.Default.Locator=WanIceRegistry/Locator:default -h rpc.lz517.net -p 4061
        ],

// AOF持久化redis配置

        'REDIS_SERVER'       => [
            'host'     => '192.168.1.30',
            'port'     => 6379,
            'database' => 0
        ],

        'REDIS_SERVERREALITY'       => [
            'host'     => 'r-m5e879f730faee74.redis.rds.aliyuncs.com',
            'port'     => 6379,
            'database' => 1,
        ],

        'REDIS_PWD' => '1234zxcA',

        /* 文档模型配置 (文档模型核心配置，请勿更改) */
        'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),
        'LOAD_EXT_CONFIG' => 'router',
        /* 文件上传相关配置 */
        'DOWNLOAD_UPLOAD' => array(
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Uploads/Download/', //保存根路径
            'savePath' => '', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => false, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ), //下载模型上传配置（文件上传类配置）


        /* 图片上传相关配置 */
        'PICTURE_UPLOAD' => array(
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Uploads/Picture/', //保存根路径
            'savePath' => '', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => true, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ), //图片上传相关配置（文件上传类配置）

        'PICTURE_UPLOAD_DRIVER'=>'local',
        'DOWNLOAD_UPLOAD_DRIVER'=>'local',
//本地上传文件驱动配置
        'UPLOAD_LOCAL_CONFIG'=>array(),

        /* 编辑器图片上传相关配置 */
        'EDITOR_UPLOAD' => array(
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Uploads/Editor/', //保存根路径
            'savePath' => '', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => false, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ),
        'DEFAULT_THEME' => 'default', // 默认模板主题名称
        /* 模板相关配置 */
        'TMPL_PARSE_STRING' => array(
            '__STATIC__' => __ROOT__ . '/Public/static',
            '__ZUI__'=>__ROOT__.'/Public/zui'

        ),
    );
}else{
    /*企业号配置信息*/
    define('COMPANY_CORPID','ww59b92d5a3626de14');
    define('COMPANY_TOKEN','weixin');
    define('COMPANY_EMCODINGASEKEY','nkvaT4YGCVLJJyGhpEk59OW422lznhFgt9DBc8Az4yi');
    define('COMPANY_CUSTOMER_APPSECRET','MMhMJHEUN7RE1_lXbNyVpUhqZeT6eEX9dqb1Mck_Bks');
    define('COMPANY_DISTRIBUTE_APPSECRET','n36670d8m9Z179KywZd9fuT5eoMFYZS6rzRV9EMRdEU');
    define('COMPANY_CLEANING_APPSECRET','w2x_VfN1hvGu7FxuAISHiwviFSIIosNxAJhGURzVbZw');
    define('COMPANY_CLEANKILL_APPSECRET','x_CI3YU8bPjI4Cs0YJmWvb3QWF_QgWdk96n-q0UKEKU');
    define('COMPANY_MAINTAIN_APPSECRET','BZx55P_kDQkgHhQQh_A3v9k1KwhCUUmpgNfug--hAnM');
    define('COMPANY_ENTERPRISE_APPSECRET','YOeLvMWMF327IyJjCSyoKebgUOJ3zP19cQT68W7HPEI');//企业主管应用
    define('COMPANY_CUSTOMER_SERVICE',1000003);
    define('COMPANY_REPAIRE_MASTER',1000004);
    define('COMPANY_CLEANING_MASTER',1000005);
    define('COMPANY_CLEANKILL_MASTER',1000006);
    define('COMPANY_DISTRIBUTE_MANAGER',1000007);
    define('COMPANY_ENTERPRISE_MASTER',1000008);//企业主管应用

    /*公众号配置信息*/
    define('WECHAT_APPID','wx2e61f3fb77e56057');
    define('WECHAT_TOKEN','weixin');
    define('WECHAT_APPSECRET','2ffa990f6dd785a26489f98a5218547b');

    //微信公众号模板消息ID
    define('WECHAT_MSG_PAY_SUCCESS','1rHkrNYQSCcO3KmTgTEHd5HNO94uoflelr_C-mt020c'); //支付成功通知
    define('WECHAT_MSG_ORDER_OUT','Fg8JM_E_0L5hX2x5Thj4ifK-y_Ih1qltlEb849-jz1s'); //订单超时提醒
    define('WECHAT_MSG_ORDER_PAY','Hk67NC5ch_F40uokOT9-oadl0BzzRNATFU3a4Bs9rUM'); //订单支付通知
    define('WECHAT_MSG_PAI_ORDER_SUCCESS','HqhdFQAcqP2923ulXEuM2RuQa36kFcJF6frRk6ZXhIM');//派单成功提醒
    define('WECHAT_MSG_ORDER_NO_PAY','O-7wLNL-1x9jIbRt9yHYGT32oGIR4LNQ1fFL_-8GcJk');//订单未支付通知
    define('WECHAT_MSG_ORDER_OVER','V6VkfqL5vaaMpzCKWo4vTQxSLN1mCCWNj-C9QT9O2t4');//订单完成通知
    define('WECHAT_MSG_ORDER_CREATE','WKSxFnxlQjuamxmWyXl2VW3SuiobdTb0u3WzfjAAGNw');//订单创建成功通知
    define('WECHAT_MSG_REGIST_SUCCESS','fAkLVQg1PatAT_qrsXUqCRrQ8g9DbTNiQ_5ko2fqUyo');//注册成功提醒
    define('WECHAT_MSG_ORDER_YS','jMKDz9WVmo85KIBiz_oVEMoklk9jKy-LTVk-Rj5l35E');//订单验收提醒
    define('WECHAT_MSG_RECEICE_ORDER','yJ6SS8dDEa4Xqok6cxDmBw9OHdHt9PycacKO94AH4zY');//接单成功提醒
    define('WECHAT_MSG_DELIVERY_ORDER','zwEj82DBns5Yikq0dNigs4GkQrkktIAzC5d3ah-_DR4');//派单通知
    define('WECHAT_MSG_AGAIN_ORDER','KuV14JSkzO-yiTEELBnxtOBgY0pqNYvwJ9XCvRuGrns');//新预约订单通知（顾客再次下单）
    define('WECHAT_MSG_SERVICE_EXPIRATION','TGPZ5g9hTqZb5YTQ_hPKWqXfx27GG4BAaaE8YFnXSlQ');//服务到期提醒
    define('WECHAT_MSG_ORDER_CHANGE','eIU8sqU_P_NOegO3wDSzVy74k38YerVbxQN3jcAqN7w');//订单修改通知
    define('WECHAT_MSG_BIND_STORE','rhnae42boJSoHXi3JlBtSzuTopXR9D63e8lf7SPqiCU');//绑定结果通知
    define('WECHAT_MSG_DOOR_SERVICE','AyZmJjmBD04MoZd3qJL0Nzst-ExP49t_ZrLnIYDgRHY');//上门服务通知

    //支付配置
    define('WECHAT_KEY','b33764c01a2d5b3336e0d578819acd56');
    define('WECHAT_MCHID','1410451602');
    return array(
        /* 模块相关配置 */
        'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
        'DEFAULT_MODULE'     => 'Home',
        'MODULE_DENY_LIST'   => array('Common', 'User'),
//'MODULE_ALLOW_LIST'  => array('Home','Admin'),

        /* 系统数据加密设置 */
        'DATA_AUTH_KEY' => '3FwDNrOHE4B2LfoWRydU5up7IC90lKnVcgaX8AYe', //默认数据加密KEY

        /* 调试配置 */
        'SHOW_PAGE_TRACE' => false,

        /* 用户相关设置 */
        'USER_MAX_CACHE'     => 1000, //最大缓存用户数
        'USER_ADMINISTRATOR' => 1, //管理员用户ID

        /* URL配置 */
        'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
        'URL_MODEL'            => 3, //URL模式  默认关闭伪静态
        'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
        'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符
        'URL_HTML_SUFFIX'       => 'html',  // URL伪静态后缀设置

        /* 全局过滤配置 */
        'DEFAULT_FILTER' => '', //全局过滤函数

        /* 数据库配置 */
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => 'rm-8vb717zw1js6do36tjo.mysql.zhangbei.rds.aliyuncs.com', // 服务器地址
        'DB_NAME'   => 'cx_service_real', // 数据库名
        'DB_USER'   => 'xmjl', // 用户名
        'DB_PWD'    => 'Jxmjl0802',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'jpgk_', // 数据库表前缀

// AOF持久化redis配置

        'REDIS_SERVER'       => [
            'host'     => '192.168.1.30',
            'port'     => 6379,
            'database' => 0
        ],

        'REDIS_SERVERREALITY'       => [
            'host'     => 'r-m5e879f730faee74.redis.rds.aliyuncs.com',
            'port'     => 6379,
            'database' => 7,
        ],

        'REDIS_PWD' => '1234zxcA',


        'RPC_ICE_CONFIG' => [
            'Ice.Communicator.Key'     => 'Ice.Communicator.Key',
//        'Ice.Default.Locator'     => 'TestIceRegistry/Locator:default -h 192.168.1.25 -p 4062',
            'Ice.Default.Locator'     => 'WanIceRegistry/Locator:default -h rpc.lz517.net -p 4061',
            'Ice.Warn.Connections'     => '1',
            'Ice.Communicator.Expires' => '5'
            #ice注册中心地址
//    --Ice.Default.Locator=TestIceRegistry/Locator:default -h 192.168.1.25 -p 4062
//    --Ice.Default.Locator=WanIceRegistry/Locator:default -h rpc.lz517.net -p 4061
        ],
        /* 文档模型配置 (文档模型核心配置，请勿更改) */
        'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),
        'LOAD_EXT_CONFIG' => 'router',
        /* 文件上传相关配置 */
        'DOWNLOAD_UPLOAD' => array(
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Uploads/Download/', //保存根路径
            'savePath' => '', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => false, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ), //下载模型上传配置（文件上传类配置）


        /* 图片上传相关配置 */
        'PICTURE_UPLOAD' => array(
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Uploads/Picture/', //保存根路径
            'savePath' => '', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => true, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ), //图片上传相关配置（文件上传类配置）

        'PICTURE_UPLOAD_DRIVER'=>'local',
        'DOWNLOAD_UPLOAD_DRIVER'=>'local',
//本地上传文件驱动配置
        'UPLOAD_LOCAL_CONFIG'=>array(),

        /* 编辑器图片上传相关配置 */
        'EDITOR_UPLOAD' => array(
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Uploads/Editor/', //保存根路径
            'savePath' => '', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => false, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ),
        'DEFAULT_THEME' => 'default', // 默认模板主题名称
        /* 模板相关配置 */
        'TMPL_PARSE_STRING' => array(
            '__STATIC__' => __ROOT__ . '/Public/static',
            '__ZUI__'=>__ROOT__.'/Public/zui'

        ),
    );
}

