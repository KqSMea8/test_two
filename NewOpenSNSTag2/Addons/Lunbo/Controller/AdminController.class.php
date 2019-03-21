<?php

namespace Addons\Lunbo\Controller;

use Admin\Controller\AddonsController;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminTreeListBuilder;


class AdminController extends AddonsController
{

    public function buildList($page = 1, $r = 20)
    {

        $list = M('imageslider')->page($page, $r)->order('paixu desc')->select();
        $reportCount = M('imageslider')->count();
        int_to_string($list);

        $builder = new AdminListBuilder();
        $builder->title("轮播图管理");

        $builder->buttonModalPopup(addons_url('Lunbo://Admin/handleEject'), '', '批量处理', array('target-form' => 'ids'))
            ->buttonModalPopup(addons_url('Lunbo://Admin/addSilider'), '', '添加轮播图')
            ->buttonDelete(addons_url('Lunbo://Admin/deleteSilider'), '删除轮播图')
            ->keyId()
            ->keyText('paixu', "排序")
            ->keyText('link', "链接")
            ->keyText('url', "路径")
            ->keyCreateTime('create_time', "创建时间")
            ->keyUpdateTime('update_time', "更新时间")
			->key('status', '状态', 'status', array('0' => '禁用', '1' => '启用'))
            ->keyDoActionModalPopup('Lunbo://Admin/editSilider?ids=###|addons_url', '编辑','操作')
            ->keyDoAction('Lunbo://Admin/deleteSilider?ids=###|addons_url', '删除','操作');

        $builder->data($list);
        $builder->pagination($reportCount, $r);
        $builder->display();
    }

    /**
     * 修改轮播图
     */
    public function editSilider(){
        $ids = I('ids',0,'int');                        //获取点击的ids
        if (!is_integer($ids) || $ids == 0) {
            echo '<p>请提交单个需要编辑的对象'.$ids.'</p>';
            return ;
        }
        if(IS_POST){
            $data = array();
            $data['url'] = I('post.url');
            $data['link'] = I('post.link');
            $data['paixu'] = I('post.paixu',0,'int');
            $data['update_time']=time();
            $result = D('Addons://Lunbo/Lunbo')->where(array('id'=>$ids))->save($data);
            if ($result) {
                $this->success('处理成功', 0);
            } else {
                $this->error('处理失败');
            }
            return;
        }
        $imageSilider= M('imageslider')->field('paixu,url,link')->where(array('id'=>$ids))->find();
        $this->assign('ids', $ids);
        $this->assign('imageSilider', $imageSilider);
        $this->display(T('Addons://Lunbo@Lunbo/editSilider'));

    }

    /**
     * 修改轮播图
     */
    public function addSilider(){

        if(IS_POST){
            $data = array();
            $data['url'] = I('post.url');
            $data['link'] = I('post.link');
            $data['paixu'] = I('post.paixu',0,'int');
            $data['update_time']=time();
            $data['create_time']=time();
            $result = D('Addons://Lunbo/Lunbo')->where(array('id'=>$ids))->addData($data);
            if ($result) {
                $this->success('处理成功', 0);
            } else {
                $this->error('处理失败');
            }
            return;
        }
        $this->display(T('Addons://Lunbo@Lunbo/addSilider'));

    }
    /**
     * 删除轮播图操作
     */
    public function deleteSilider()
    {
        $ids = I('ids', array());
        $map['id'] = array('in', $ids);
        $result = D('Addons://Lunbo/Lunbo')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', 0);
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 处理轮播图页面的弹窗实现
     */
    public function handleEject()
    {
        $ids = I('ids');                        //获取点击的ids
        if (is_array($ids)) {
            $ids = implode(',', $ids);              //数组转换成字符串，传到页面
        }
        $this->assign('ids', $ids);
        $this->display(T('Addons://Lunbo@Lunbo/handleeject'));
    }


    /**
     * 管理员处理后进行的操作
     */

    public function handleSilider()
    {
        $ids = I('ids');                        //接收页面传出的ids
        $ids = explode(',', $ids);              //字符串转换成数组
        $map['id'] = array('in', $ids);

        $time = time();                       //获取当前时间时间戳
        $data['update_time'] = $time;
        $handlestatus = I('post.user_choose', '', 'op_t');      //接收处理状态
        $data['status'] = $handlestatus;
        $result = D('Addons://Lunbo/Lunbo')->where($map)->save($data);
        if ($result) {
            $this->success('处理成功', 0);
        } else {
            $this->error('处理失败');
        }
    }

    /**
     * 管理员禁用轮播图操作
     */

    public function ignoreSilider()
    {
        $ids = I('ids');                        //获取点击的ids
        $map['id'] = array('in', $ids);
        $time = time();                       //获取当前时间
        $data['update_time'] = $time;
        $data['status'] = '0';
        $result = D('Addons://Lunbo/Lunbo')->where($map)->save($data);

        if ($result) {
            $this->success('处理成功', 0);
        } else {
            $this->error('处理失败');
        }
    }


}
