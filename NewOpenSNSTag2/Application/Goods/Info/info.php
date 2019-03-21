<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 上午10:12
 * @author 郑钟良<zzl@ourstu.com>
 */


return array(
    //模块名
    'name' => 'Goods',
    //别名
    'alias' => '二手市场',
    //版本号
    'version' => '2.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '商品以及商品分类管理',
    //开发者
    'developer' => '京品高科信息科技（北京）有限公司',
    //开发者网站
    'website' => 'http://www.lz517.com',
    //前台入口，可用U函数
    'entry' => 'Goods/index/index',

    'admin_entry' => 'Admin/Goods/index',

    'icon' => 'rss-sign',

    'can_uninstall' => 1

);