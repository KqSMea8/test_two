<?php

return array(
    //模块名
    'name' => 'Topic',
    //别名
    'alias' => '话题',
    //版本号
    'version' => '0.2.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '专辑模块改版视频展示播放模块',
    //开发者
    'developer' => 'CnSeuTeam信息安全团队',
    //开发者网站
    'website' => 'http://www.cnseu.org',
    //前台入口，可用U函数
    'entry' => 'Topic/index/index',

    'admin_entry' => '/admin/topic/post/forum_id/7.html',

    'icon' => 'comments',

    'can_uninstall' => 1
);