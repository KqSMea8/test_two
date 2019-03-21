<?php
/**
 * 所属项目 OpenSNS开源免费版.
 * 开发者: 陈一枭
 * 创建日期: 2015-03-27
 * 创建时间: 15:48
 * 版权所有 想天软件工作室(www.ourstu.com)
 */
namespace Video\Widget;

use Think\Controller;

class HomeBlockWidget extends Controller
{
    public function render()
    {
        $this->assignVideo();
        $this->display(T('Application://Video@Widget/homeblock'));
    }

    public function assignVideo()
    {
        $num = modC('VIDEO_SHOW_COUNT', 4, 'Video');
        $field = modC('VIDEO_SHOW_ORDER_FIELD', 'view_count', 'Video');
        $order = modC('VIDEO_SHOW_ORDER_TYPE', 'desc', 'Video');
        $cache = modC('VIDEO_SHOW_CACHE_TIME', 600, 'Video');
        $data = S('video_home_data');
        if (empty($data)) {
            $map = array('status' => 1);
            $content = D('VideoContent')->where($map)->order($field . ' ' . $order)->limit($num)->select();
            foreach ($content as &$v) {
                $v['user'] = query_user(array('id', 'nickname', 'space_url', 'space_link', 'avatar128', 'rank_html'), $v['uid']);
                $v['video'] = D('Video')->field('id,title')->find($v['video_id']);
            }
            $data = $content;
            S('video_home_data', $data, $cache);
        }
        unset($v);
        $this->assign('VideoContents', $data);
    }
} 