<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 上午10:02
 * @author 郑钟良<zzl@ourstu.com>
 */

//TODO 客服电话 公司电话
define('COMPANY_PHONE',18810250371);
//wechat_order表 status_state
//订单状态: (0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价;8:已支付;10:未接单)
define('PAY_STATUS_SENDORDERING',0);
define('PAY_STATUS_NO_PAY',2);
define('PAY_STATUS_PAY_COMPLETION',3);
define('PAY_STATUS_PAY_SUCCESS',8);
define('PAY_STATUS_PAY_FAIL',4);
define('PAY_STATUS_PAY_WAITACCEPT',9);//待验收
define('PAY_STATUS_NO_ORDER_RECEIVING',10);
define('PAY_STATUS_PAY_INSERVICE',11);//服务中
define('PAY_STATUS_OUTDATE',12);//已过期
define('PAY_STATUS_WAIT_MAKESURE',13);//待确认
//define('PAY_STATUS_REFUND',10);
//define('PAY_STATUS_CANCELED',15);

////wechat_pay表 status_state
//支付状态(0:待支付; 1:支付成功; 2:支付失败; 3:超时未支付; 4:退款中; 5:已成功退款)
define('PAY_LOG_STATUS_NO_PAY',0);
define('PAY_LOG_STATUS_PAY_SUCCESS',1);
define('PAY_LOG_STATUS_PAY_FAIL',2);
//define('PAY_STATUS_REFUND',10);
//define('PAY_STATUS_CANCELED',15);

//0：无需差价支付1：客户未支付2：客服已支付)
define('CJ_PAY_STATUS_NO_PAY',0);
define('CJ_PAY_STATUS_PAY_FAIL',1);
define('CJ_PAY_STATUS_PAY_SUCCESS',2);

//-----------------------------------------------------------------上门巡检相关表 jpgk_wechat_inspection---------------------------------------------------------------------------------------------------------------
/**
 * 字段：payment
 * 付费方式（1：季付；2：半年付；3：年付）
 */
//季付
defined ( 'INSPECTION_PAYMENT_SEASON' ) or define ( 'INSPECTION_PAYMENT_SEASON', 1 );
//半年付
defined ( 'INSPECTION_PAYMENT_SEMESTER' ) or define ( 'INSPECTION_PAYMENT_SEMESTER', 2 );
//年付
defined ( 'INSPECTION_PAYMENT_YEAR' ) or define ( 'INSPECTION_PAYMENT_YEAR', 3 );

$INSPECTION_PAYMEN = [
    INSPECTION_PAYMENT_SEASON => '季付',
    INSPECTION_PAYMENT_SEMESTER => '半年付',
    INSPECTION_PAYMENT_YEAR => '年付',
];

/**
 * 字段：inspection_status
 * 订单状态（1：新工单(服务商未接单)；2：服务中(服务商已接单)；3：已取消；4：已完成）
 */
//新工单
defined ( 'INSPECTION_MAINSTATUS_NEW' ) or define ( 'INSPECTION_MAINSTATUS_NEW', 1 );
//服务中
defined ( 'INSPECTION_MAINSTATUS_INSERVICE' ) or define ( 'INSPECTION_MAINSTATUS_INSERVICE', 2 );
//已取消
defined ( 'INSPECTION_MAINSTATUS_CANCEL' ) or define ( 'INSPECTION_MAINSTATUS_CANCEL', 3 );
//已完成
defined ( 'INSPECTION_MAINSTATUS_FINISH' ) or define ( 'INSPECTION_MAINSTATUS_FINISH', 4 );

$INSPECTION_MAINSTATUS = [
    INSPECTION_MAINSTATUS_NEW => '未接单',
    INSPECTION_MAINSTATUS_INSERVICE => '服务中',
    INSPECTION_MAINSTATUS_CANCEL => '已取消',
    INSPECTION_MAINSTATUS_FINISH => '已完成',
];

//-----------------------------------------------------------------上门巡检相关表 jpgk_wechat_inspection---------------------------------------------------------------------------------------------------------------

//-----------------------------------------------------------------上门巡检相关表 jpgk_wechat_inspection_store_child---------------------------------------------------------------------------------------------------------------
/**
 * 字段：status
 * 订单状态（1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成）
 */
//服务商未派单
defined ( 'INSPECTION_CHILDSTATUS_SUPERVISOR_NO_ORDER' ) or define ( 'INSPECTION_CHILDSTATUS_SUPERVISOR_NO_ORDER', 1 );
//服务商已派单
defined ( 'INSPECTION_CHILDSTATUS_SUPERVISOR_YES_ORDER' ) or define ( 'INSPECTION_CHILDSTATUS_SUPERVISOR_YES_ORDER', 2 );
//巡检员已接单
defined ( 'INSPECTION_CHILDSTATUS_MASTER_TAKEORDERS' ) or define ( 'INSPECTION_CHILDSTATUS_MASTER_TAKEORDERS', 3 );
//巡检员开始巡检
defined ( 'INSPECTION_CHILDSTATUS_MASTER_STARTORDER' ) or define ( 'INSPECTION_CHILDSTATUS_MASTER_STARTORDER', 4 );
//巡检员完成巡检
defined ( 'INSPECTION_CHILDSTATUS_MASTER_FINISHORDER' ) or define ( 'INSPECTION_CHILDSTATUS_MASTER_FINISHORDER', 5 );
//门店点击完成
defined ( 'INSPECTION_CHILDSTATUS_STORE_OVERORDER' ) or define ( 'INSPECTION_CHILDSTATUS_STORE_OVERORDER', 6 );

$INSPECTION_CHILDSTATUS = [
    INSPECTION_CHILDSTATUS_SUPERVISOR_NO_ORDER => '未派单',
    INSPECTION_CHILDSTATUS_SUPERVISOR_YES_ORDER => '未接单',
    INSPECTION_CHILDSTATUS_MASTER_TAKEORDERS => '已接单',
    INSPECTION_CHILDSTATUS_MASTER_STARTORDER => '巡检中',
    INSPECTION_CHILDSTATUS_MASTER_FINISHORDER => '待确认',
    INSPECTION_CHILDSTATUS_STORE_OVERORDER => '已完成',
];
return array(
    // 预先加载的标签库
    'TAGLIB_PRE_LOAD' => 'OT\\TagLib\\Article,OT\\TagLib\\Think',

    /* 主题设置 */
    'DEFAULT_THEME' => 'default', // 默认模板主题名称

    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
        '__IMG__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/images',
        '__CSS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/css',
        '__JS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/js',
        '__ZUI__' => __ROOT__ . '/Public/zui',
        '__CORE_IMAGE__'=>__ROOT__.'/Application/Core/Static/images',
        '__CORE_CSS__'=>__ROOT__.'/Application/Core/Static/css',
        '__CORE_JS__'=>__ROOT__.'/Application/Core/Static/js',
        '__APPLICATION__'=>__ROOT__.'/Application/'
    ),

    'NEED_VERIFY'=>0,//此处控制默认是否需要审核，该配置项为了便于部署起见，暂时通过在此修改来设定。
    'NEWS_APP_NAME' => 'News',
    'Single'=>array(        //单次
        'Cockroach'=>array( //蟑螂
            '1'=>399,         //0~100
            '2'=>479,         //101~200
            '3'=>559,         //201~300
            '4'=>639,         //301~400
            '5'=>719,         //401~500
            '6'=>799,         //501~600
            '7'=>959,         //600以上
        ),
        'Mouse'=>array(       //老鼠
            '1'=>399,         //0~100
            '2'=>479,         //101~200
            '3'=>559,         //201~300
            '4'=>639,         //301~400
            '5'=>719,         //401~500
            '6'=>799,         //501~600
            '7'=>959,         //600以上
        ),
        'CockroachRat'=>array( //蟑鼠
            '1'=>479,          //0~100
            '2'=>559,          //101~200
            '3'=>639,          //201~300
            '4'=>719,          //301~400
            '5'=>799,          //401~500
            '6'=>879,          //501~600
            '7'=>1039,          //600以上
        ),
        'Insect'=>array(      //蚊蝇（昆虫）
            '1'=>319,         //0~100
            '2'=>351,         //101~200
            '3'=>383,         //201~300
            '4'=>415,         //301~400
            '5'=>447,         //401~500
            '6'=>479,         //501~600
            '7'=>559,         //600以上
        ),
        'Four'=>array(        //四害
            '1'=>559,         //0~100
            '2'=>639,         //101~200
            '3'=>719,         //201~300
            '4'=>799,         //301~400
            '5'=>879,         //401~500
            '6'=>959,         //501~600
            '7'=>1119,         //600以上
        ),
    ),
    //巡检员操作使用
    'INSPECTION_OPERATE'=>[
        '1'=>'进行了除霜',
        '2'=>'疏通了出风口',
        '3'=>'进行了内部清洗',
        '4'=>'查看了整个运行流程',
    ],
    'INSPECTION_PAYMEN' => $INSPECTION_PAYMEN,
    'INSPECTION_MAINSTATUS' => $INSPECTION_MAINSTATUS,
    'INSPECTION_CHILDSTATUS' => $INSPECTION_CHILDSTATUS
);