<?php


namespace Video\Controller;

use Think\Controller;


class IndexController extends Controller
{
    /**
     * 业务逻辑都放在 WeiboApi 中
     * @var
     */
    public function _initialize()
    {
        $tree = D('Video')->getTree();
        $this->assign('tree', $tree);


        $sub_menu =
            array(
                'left' =>
                    array(
                        array('tab' => 'home', 'title' => '首页', 'href' => U('Video/index/index')),
                    ),
            );
        if (check_auth('addVideoContent')) {
            $sub_menu['right'] = array(
                array('tab' => 'post', 'title' => '发布', 'href' => '#frm-post-popup','a_class'=>'open-popup-link')
            );
        }
        foreach ($tree as $cat) {
            if ($cat['_']) {
                $children = array();
                $children[] = array('tab' => 'cat_' . $cat['id'], 'title' => '全部', 'href' => U('Video/index/index', array('video_id' => $cat['id'])));
                foreach ($cat['_'] as $child) {
                    $children[] = array('tab' => 'cat_' . $cat['id'], 'title' => $child['title'], 'href' => U('Video/index/index', array('video_id' => $child['id'])));
                }

            }
            $menu_item = array('children' => $children, 'tab' => 'cat_' . $cat['id'], 'title' => $cat['title'], 'href' => U('blog/article/lists', array('category' => $cat['id'])));
            $sub_menu['left'][] = $menu_item;
            unset($children);
        }
        $this->assign('sub_menu', $sub_menu);

    }

    public function index($page = 1, $video_id = 0)
    {
        //设置展示方式 列表；瀑布流
        $aDisplay_type=I('display_type','','text');
        $cookie_type=cookie('video_display_type');
        if($aDisplay_type==''){
            if($cookie_type){
                $aDisplay_type=$cookie_type;
            }else{
                $aDisplay_type=modC('DISPLAY_TYPE','list','Video');
                cookie('video_display_type',$aDisplay_type);
            }
        }else{
            if($cookie_type!=$aDisplay_type){
                cookie('video_display_type',$aDisplay_type);
            }
        }
        $this->assign('display_type',$aDisplay_type);
        //设置展示方式 列表；瀑布流 end

        $video_id = intval($video_id);
        $video = D('Video')->find($video_id);
        if (!$video_id == 0) {
            $video_id = intval($video_id);
            $videos = D('Video')->where("id=%d OR pid=%d", array($video_id, $video_id))->limit(999)->select();
            $ids = array();
            foreach ($videos as $v) {
                $ids[] = $v['id'];
            }
            $map['video_id'] = array('in', implode(',', $ids));
        }
        $map['status'] = 1;
        $content = D('VideoContent')->where($map)->order('create_time desc')->page($page, 16)->select();
        $totalCount = D('VideoContent')->where($map)->count();
        foreach ($content as &$v) {
            $v['user'] = query_user(array('id', 'nickname', 'space_url', 'space_link', 'avatar128', 'rank_html'), $v['uid']);
            $v['video'] = D('Video')->field('id,title')->find($v['video_id']);
            if($aDisplay_type=='masonry'){
                $cover = M('Picture')->where(array('status' => 1))->getById($v['cover_id']);
                $imageinfo = getimagesize('.'.$cover['path']);
                $v['cover_height']=$imageinfo[1]*255/$imageinfo[0];
                $v['cover_height']=$v['cover_height']?$v['cover_height']:253;
            }
        }
        unset($v);
        $this->assign('contents', $content);
        $this->assign('totalPageCount', $totalCount);
        $this->assign('top_video', $video['pid'] == 0 ? $video['id'] : $video['pid']);

        $this->assign('video_id', $video_id);
        $this->setTitle('视频');
        $this->display();
    }

    public function doPost($id = 0, $cover_id = 0, $title = '', $content = '', $video_id = 0, $url = '')
    {
        if (!check_auth('addVideoContent')) {
            $this->error('抱歉，您不具备投稿权限。');
        }
        $video_id = intval($video_id);
        if (!is_login()) {
            $this->error('请登陆后再投稿。');
        }
        if (!$cover_id) {
            $this->error('请上传封面。');
        }
        if (trim(op_t($title)) == '') {
            $this->error('请输入标题。');
        }
        if (trim(op_h($content)) == '') {
            $this->error('请输入内容。');
        }
        if ($video_id == 0) {
            $this->error('请选择分类。');
        }
        if (trim(op_h($url)) == '') {
            $this->error('请输入视频地址。');
        }
        $content = D('VideoContent')->create();
        $content['content'] = op_h($content['content']);
        $content['title'] = op_t($content['title']);
        $content['url'] = op_t($content['url']); //新增链接框
        $content['video_id'] = $video_id;

        if ($id) {
            $content_temp = D('VideoContent')->find($id);
            if (!check_auth('editVideoContent')) { //不是管理员则进行检测
                if ($content_temp['uid'] != is_login()) {
                    $this->error('不可操作他人的内容。');
                }
            }
            $content['uid'] = $content_temp['uid']; //权限矫正，防止被改为管理员
            $rs = D('VideoContent')->save($content);
            if ($rs) {
                $this->success('编辑成功。', U('videoContentDetail', array('id' => $content['id'])));
            } else {
                $this->success('编辑失败。', '');
            }
        } else {
            if (modC('NEED_VERIFY', 0) && !is_administrator()) //需要审核且不是管理员
            {
                $content['status'] = 0;
                $tip = '但需管理员审核通过后才会显示在列表中，请耐心等待。';
                $user = query_user(array('nickname'), is_login());
                $admin_uids = explode(',', C('USER_ADMINISTRATOR'));
                foreach ($admin_uids as $admin_uid) {
                    D('Common/Message')->sendMessage($admin_uid, $title = '视频投稿提醒',"{$user['nickname']}向视频投了一份稿件，请到后台审核。",  'Admin/Video/verify', array(),is_login(), 2);
                }
            }
            $rs = D('VideoContent')->add($content);
            if ($rs) {
                $this->success('投稿成功。' . $tip, 'refresh');
            } else {
                $this->success('投稿失败。', '');
            }
        }


    }

    public function videoContentDetail($id = 0)
    {


        $video_content = D('VideoContent')->find($id);
        if (!$video_content) {
            $this->error('404 not found');
        }
        D('VideoContent')->where(array('id' => $id))->setInc('view_count');
        $video = D('Video')->find($video_content['video_id']);

        $this->assign('top_video', $video['pid'] == 0 ? $video['id'] : $video['pid']);
        $this->assign('video_id', $video['id']);
        $video_content['user'] = query_user(array('id', 'nickname', 'space_url', 'space_link', 'avatar64', 'rank_html', 'signature'), $video_content['uid']);
        $this->assign('content', $video_content);
        $this->setTitle('{$content.title|op_t}' . '——视频');
        $this->setKeywords($video_content['title']);
        $this->display();
    }

    public function selectDropdown($pid)
    {
        $videos = D('Video')->where(array('pid' => $pid, 'status' => 1))->limit(999)->select();
        exit(json_encode($videos));


    }

    public function edit($id)
    {
        if (!check_auth('addVideoContent') && !check_auth('editVideoContent')) {
            $this->error('抱歉，您不具备投稿权限。');
        }
        $video_content = D('VideoContent')->find($id);
        if (!$video_content) {
            $this->error('404 not found');
        }
        if (!check_auth('editVideoContent')) { //不是管理员则进行检测
            if ($video_content['uid'] != is_login()) {
                $this->error('404 not found');
            }
        }

        $video = D('Video')->find($video_content['video_id']);

        $this->assign('top_video', $video['pid'] == 0 ? $video['id'] : $video['pid']);
        $this->assign('video_id', $video['id']);
        $video_content['user'] = query_user(array('id', 'nickname', 'space_url', 'space_link', 'avatar64', 'rank_html', 'signature'), $video_content['uid']);
        $this->assign('content', $video_content);
        $this->display();
    }
}