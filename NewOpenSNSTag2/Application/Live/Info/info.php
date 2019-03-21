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
    'name' => 'Live',
    //别名
    'alias' => '直播',
    //版本号
    'version' => '2.3.1',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '直播模块',
    //开发者
    'developer' => '乐栈科技',
    //开发者网站
    'website' => 'http://www.lz517.com',
    //前台入口，可用U函数
    'entry' => 'Live/index/index',

    'admin_entry' => 'Admin/Live/liveList',

    'icon' => 'rss-sign',

    'can_uninstall' => 1

);