<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author HQ
 */

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Model\PictureModel;
use CircleVideo\Model\CircleVideoModel;
use CircleVideo\Model\CircleVideoDetailModel;
use User\Model\UcenterMemberModel;

class CircleVideoController extends AdminController{

    protected $CircleVideoModel;

    function _initialize()
    {
        parent::_initialize();
        $this->CircleVideoModel = new CircleVideoModel();
    }

    //*************************************************视频列表开始**************************************************

    //视频列表start
    public function videoList($page=1,$r=20)
    {
        $map = [];
        $map['status']=['neq',0];

        list($list,$totalCount)=(new CircleVideoModel())->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){
            $val['statusText'] = $val['status'] == 1 ? '已上线' : ($val['status'] == 2 ? '已下线' : '');
        }

        $builder=new AdminListBuilder();
        $builder->title('视频列表')
            ->data($list)
            ->buttonNew(U('CircleVideo/editVideo'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('CircleVideo/delLive'), 'target-form' => 'ids'])
            ->button('上线', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要上线吗?', 'url' => U('CircleVideo/startVideo'), 'target-form' => 'ids'])
            ->button('下线', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要下线吗?', 'url' => U('CircleVideo/endVideo'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('title','视频标题')
            ->keyText('source','视频来源')
            ->keyText('statusText','状态')
            ->keyText('view_count','观看量')
            ->keyText('sort','排序')
            ->keyCreateTime()
            ->keyUpdateTime()
            ->keyDoActionEdit('CircleVideo/editVideo?id=###');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**直播添加
     * @param int $id
     */
    public function editVideo($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["title"])){

                $error .= "视频标题不可为空！";
            }

            if(empty($_POST["image_id"])){

                $error .= "封面不可为空！";
            }

            if(!empty($error)){

                $this->error($error);
            }
            $data = I('post.');
            $data['template']="detailVideoH5";
            $pictureData = (new PictureModel())->where(['id'=>$data['image_id']])->find();
            $data['image_url'] = $pictureData['path'];

            if ($cate = (new CircleVideoModel())->editData($data)) {

                $this->success($title.L('_SUCCESS_'), U('CircleVideo/videoList'));
            } else {
                $this->error($title.L('_FAIL_').(new CircleVideoModel())->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = (new CircleVideoModel())->find($id);
                $videoDetail = (new CircleVideoDetailModel())->where(['video_id'=>$id])->find();
                $data['content'] = $videoDetail['content'];
            }

            $builder->title($title.L('视频'))
                ->data($data)
                ->keyId()
                ->keyText('title','视频标题')
                ->keyText('source','视频来源')
//                ->keyText('source','视频地址')
                ->keyMultiImage('image_id','直播封面')
                ->keyText('sort','排序')
                ->keyEditor('content',L('_CONTENT_'),'','all',array('width' => '700px', 'height' => '400px'))
//                ->keyText('view_count','观看量')
                ->buttonSubmit(U('CircleVideo/editVideo'))->buttonBack()
                ->display();
        }
    }

    //删除直播数据
    public function delLive(){

        $Ids = I('post.ids');
        if($Ids){
            (new CircleVideoModel())->where(['id'=>['in',$Ids]])->save(['status' => '0']);
        }

        $this->success('删除成功');
    }

    //启用视频数据
    public function startVideo(){
        $Ids = I('post.ids');
        if($Ids){

            (new CircleVideoModel())->where(['id'=>['IN',$Ids]])->save(['status'=>1,'update_time'=>time()]);
        }

        $this->success('上线成功');
    }

    //禁用视频数据
    public function endVideo(){
        $Ids = I('post.ids');
        if($Ids){

            (new CircleVideoModel())->where(['id'=>['IN',$Ids]])->save(['status'=>2,'update_time'=>time()]);
        }

        $this->success('下线成功');
    }

}