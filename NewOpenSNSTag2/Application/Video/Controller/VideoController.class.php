<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-11
 * Time: PM5:41
 */

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;


class VideoController extends AdminController
{
    protected $videoModel;

    function _initialize()
    {
        $this->videoModel = D('Video/Video');
        parent::_initialize();
    }

    public function config()
    {
        $admin_config = new AdminConfigBuilder();
        $data = $admin_config->handleConfig();
        $data['NEED_VERIFY'] = $data['NEED_VERIFY'] ? $data['NEED_VERIFY'] : 0;
        $data['DISPLAY_TYPE'] = $data['DISPLAY_TYPE'] ? $data['DISPLAY_TYPE'] : 'list';
        $data['VIDEO_SHOW_TITLE'] = $data['VIDEO_SHOW_TITLE'] ? $data['VIDEO_SHOW_TITLE'] : '最热视频';
        $data['VIDEO_SHOW_COUNT'] = $data['VIDEO_SHOW_COUNT'] ? $data['VIDEO_SHOW_COUNT'] : 4;
        $data['VIDEO_SHOW_ORDER_FIELD'] = $data['VIDEO_SHOW_ORDER_FIELD'] ? $data['VIDEO_SHOW_ORDER_FIELD'] : 'view_count';
        $data['VIDEO_SHOW_ORDER_TYPE'] = $data['VIDEO_SHOW_ORDER_TYPE'] ? $data['VIDEO_SHOW_ORDER_TYPE'] : 'desc';
        $data['VIDEO_SHOW_CACHE_TIME'] = $data['VIDEO_SHOW_CACHE_TIME'] ? $data['VIDEO_SHOW_CACHE_TIME'] : '600';
        $admin_config->title('视频基本设置')
            ->keyBool('NEED_VERIFY', '投稿是否需要审核', '默认无需审核')
            ->keyRadio('DISPLAY_TYPE', '默认展示形式', '前台列表默认以该形式展示',array('list'=>'列表','masonry'=>'瀑布流'))
            ->buttonSubmit('', '保存')->data($data);
        $admin_config->keyText('VIDEO_SHOW_TITLE', '标题名称', '在首页展示块的标题');
        $admin_config->keyText('VIDEO_SHOW_COUNT', '显示视频的个数', '只有在网站首页模块中启用了视频块之后才会显示');
        $admin_config->keyRadio('VIDEO_SHOW_ORDER_FIELD', '排序值', '展示模块的数据排序方式', array('view_count' => '阅读数', 'reply_count' => '回复数', 'create_time' => '发表时间', 'update_time' => '更新时间'));
        $admin_config->keyRadio('VIDEO_SHOW_ORDER_TYPE', '排序方式', '展示模块的数据排序方式', array('desc' => '倒序，从大到小', 'asc' => '正序，从小到大'));
        $admin_config->keyText('VIDEO_SHOW_CACHE_TIME', '缓存时间', '默认600秒，以秒为单位');
        $admin_config->group('基本配置', 'NEED_VERIFY,DISPLAY_TYPE')->group('首页展示配置', 'VIDEO_SHOW_COUNT,VIDEO_SHOW_TITLE,VIDEO_SHOW_ORDER_TYPE,VIDEO_SHOW_ORDER_FIELD,VIDEO_SHOW_CACHE_TIME');

        $admin_config->groupLocalComment('本地评论配置','videoContent');



        $admin_config->display();
    }

    public function video()
    {
        //显示页面
        $builder = new AdminTreeListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';
        $attr1 = $attr;
        $attr1['url'] = $builder->addUrlParam(U('setWeiboTop'), array('top' => 1));
        $attr0 = $attr;
        $attr0['url'] = $builder->addUrlParam(U('setWeiboTop'), array('top' => 0));
        $tree = D('Video/Video')->getTree(0, 'id,title,sort,pid,status');
        $builder->title('视频管理')
            ->buttonNew(U('Video/add'))
            ->data($tree)
            ->display();
    }

    public function add($id = 0, $pid = 0)
    {
        if (IS_POST) {
            if ($id != 0) {
                $video = $this->videoModel->create();
                if ($this->videoModel->save($video)) {
                    $this->success('编辑成功。');
                } else {
                    $this->error('编辑失败。');
                }
            } else {
                $video = $this->videoModel->create();
                if ($this->videoModel->add($video)) {

                    $this->success('新增成功。');
                } else {
                    $this->error('新增失败。');
                }
            }


        } else {
            $builder = new AdminConfigBuilder();
            $videos = $this->videoModel->select();
            $opt = array();
            foreach ($videos as $video) {
                $opt[$video['id']] = $video['title'];
            }
            if ($id != 0) {
                $video = $this->videoModel->find($id);
            } else {
                $video = array('pid' => $pid, 'status' => 1);
            }


            $builder->title('新增分类')->keyId()->keyText('title', '标题')->keySelect('pid', '父分类', '选择父级分类', array('0' => '顶级分类') + $opt)
                ->keyStatus()->keyCreateTime()->keyUpdateTime()
                ->data($video)
                ->buttonSubmit(U('Video/add'))->buttonBack()->display();
        }

    }

    public function videoTrash($page = 1, $r = 20, $model = '')
    {
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        //读取微博列表
        $map = array('status' => -1);
        $model = $this->videoModel;
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        //显示页面

        $builder->title('视频回收站')
            ->setStatusUrl(U('setStatus'))->buttonRestore()->buttonClear('Video/Video')
            ->keyId()->keyText('title', '标题')->keyStatus()->keyCreateTime()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function operate($type = 'move', $from = 0)
    {
        $builder = new AdminConfigBuilder();
        $from = D('Video')->find($from);

        $opt = array();
        $videos = $this->videoModel->select();
        foreach ($videos as $video) {
            $opt[$video['id']] = $video['title'];
        }
        if ($type === 'move') {

            $builder->title('移动分类')->keyId()->keySelect('pid', '父分类', '选择父分类', $opt)->buttonSubmit(U('Video/add'))->buttonBack()->data($from)->display();
        } else {

            $builder->title('合并分类')->keyId()->keySelect('toid', '合并至的分类', '选择合并至的分类', $opt)->buttonSubmit(U('Video/doMerge'))->buttonBack()->data($from)->display();
        }

    }

    public function doMerge($id, $toid)
    {
        $effect_count = D('VideoContent')->where(array('video_id' => $id))->setField('video_id', $toid);
        D('Video')->where(array('id' => $id))->setField('status', -1);
        $this->success('合并分类成功。共影响了' . $effect_count . '个内容。', U('video'));
        //TODO 实现合并功能 video
    }

    public function contents($page = 1, $r = 10)
    {
        //读取列表
        $map = array('status' => 1);
        $model = M('VideoContent');
        $list = $model->where($map)->page($page, $r)->select();
        unset($li);
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';


        $builder->title('内容管理')
            ->setStatusUrl(U('setVideoContentStatus'))->buttonDisable('', '审核不通过')->buttonDelete()
            ->keyId()->keyLink('title', '标题', 'Video/Index/videoContentDetail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function verify($page = 1, $r = 10)
    {
        //读取列表
        $map = array('status' => 0);
        $model = M('VideoContent');
        $list = $model->where($map)->page($page, $r)->select();
        unset($li);
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';


        $builder->title('审核内容')
            ->setStatusUrl(U('setVideoContentStatus'))->buttonEnable('', '审核通过')->buttonDelete()
            ->keyId()->keyLink('title', '标题', 'Video/Index/videoContentDetail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function setVideoContentStatus()
    {
        $ids = I('ids');
        $status = I('get.status', 0, 'intval');
        $builder = new AdminListBuilder();
        if ($status == 1) {
            foreach ($ids as $id) {
                $content = D('VideoContent')->find($id);
                D('Common/Message')->sendMessage($content['uid'],$title = '视频内容审核通知', "管理员审核通过了您发布的内容。现在可以在列表看到该内容了。",  'Video/Index/videoContentDetail', array('id' => $id), is_login(), 2);
                /*同步微博*/
                /*  $user = query_user(array('nickname', 'space_link'), $content['uid']);
                  $weibo_content = '管理员审核通过了@' . $user['nickname'] . ' 的内容：【' . $content['title'] . '】，快去看看吧：' ."http://$_SERVER[HTTP_HOST]" .U('Video/Index/videoContentDetail',array('id'=>$content['id']));
                  $model = D('Weibo/Weibo');
                  $model->addWeibo(is_login(), $weibo_content);*/
                /*同步微博end*/
            }

        }
        $builder->doSetStatus('VideoContent', $ids, $status);

    }

    public function contentTrash($page = 1, $r = 10, $model = '')
    {
        //读取微博列表
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        $map = array('status' => -1);
        $model = D('VideoContent');
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        //显示页面

        $builder->title('内容回收站')
            ->setStatusUrl(U('setVideoContentStatus'))->buttonRestore()->buttonClear('VideoContent')
            ->keyId()->keyLink('title', '标题', 'Video/Index/videoContentDetail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }
}
