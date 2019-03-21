<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author HQ
 */

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;
use Admin\Model\PictureModel;
use Common\Model\UserModel;
use Live\Model\LiveRoomModel;
use Think\Cache\Driver\Redis;
use User\Model\UcenterMemberModel;

class LiveController extends AdminController{

    protected $LiveRoomModel;

    function _initialize()
    {
        parent::_initialize();
        $this->LiveRoomModel = new LiveRoomModel();
    }

    //*************************************************直播列表开始**************************************************

    //直播列表start
    public function liveList($page=1,$r=20)
    {
        $map = [];
        $map['status']=['neq',-1];

        list($list,$totalCount)=(new LiveRoomModel())->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){
            $val['statusText'] = $val['status'] == 1 ? '启用' : ($val['status'] == 0 ? '禁用' : '');
            $val['liveStatusText'] = $val['live_status'] == 0 ? '已激活' : ($val['live_status'] == 1 ? '已激活' : ($val['live_status'] == 2 ? '已结束' : ''));

            //阿里直播鉴权
            $AppName = 'catering';
            $StreamName = $val['path'];
            $liveId = $val['id'];

            //时间戳，有效时间 10000小时
            $time=time()+3600000;

            //加密key，即直播后台鉴权里面自行设置
            $key = 'RXBBRQLC8tpB';
            $strpush = "/$AppName/$StreamName-$time-0-$liveId-$key";

            //里面的直播推流中心服务器域名、vhost域名可根据自身实际情况进行设置
            $pushurl = "rtmp://video-center.alivecdn.com/$AppName/$StreamName?vhost=live.lz517.net&auth_key=$time-0-$liveId-".md5($strpush);
            $val['path'] = $pushurl;
        }



        $builder=new AdminListBuilder();
        $builder->title('直播列表')
            ->data($list)
            ->buttonNew(U('Live/editLive'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Live/delLive?id=###'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用数据吗?', 'url' => U('Live/disableLive?id=###'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用数据吗?', 'url' => U('Live/disableLive?id=###'), 'target-form' => 'ids'])
            ->button('激活直播', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要激活直播吗?', 'url' => U('Live/startLive?id=###'), 'target-form' => 'ids'])
            ->button('结束直播', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要结束直播吗?', 'url' => U('Live/endLive?id=###'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('title','直播标题')
            ->keyText('speaker','主讲人')
            ->keyText('start_time','直播开始时间')
            ->keyText('statusText','状态') //状态(-1:删除;0:未启用;1:已启用)
            ->keyText('liveStatusText','直播状态') //直播状态(0:已激活;1:直播中;2:已结束)
            ->keyText('sort','排序')
            ->keyHtml('path','直播地址')
            ->keyCreateTime()
            ->keyUpdateTime()
            ->keyDoActionEdit('Live/editLive?id=###');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**直播添加
     * @param int $id
     */
    public function editLive($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["title"])){

                $error .= "直播标题不可为空！";
            }
            if(empty($_POST["speaker"])){

                $error .= "主讲人不可为空！";
            }
            if(empty($_POST["cover_id"])){

                $error .= "封面不可为空！";
            }
            if(empty($_POST["phone"])){

                $error .= "直播间所属人(用户手机号)不可为空！";
            }
            if(empty($_POST['path'])){
                $error.="直播标识不可为空！";
            }elseif (preg_match("/[\x7f-\xff]/", $_POST['path'])) {
                $error.="直播标识不可含有中文！";
            }
            if(empty($_POST['description'])){
                $error.="直播描述不可为空！";
            }
            $pictureData = (new PictureModel())->where(['id'=>$_POST["cover_id"]])->find();
            $_POST['cover_url'] = $pictureData['path'];

            $liveOneData = (new LiveRoomModel())->where(['path'=>$_POST['path']])->select();
            if($liveOneData[1] && $id){
                $error.="直播标识重复,请重新输入！";
            }elseif($liveOneData[0] && !$id){
                $error.="直播标识重复,请重新输入！";
            }

            $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\。|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
            if( preg_match($regex,$_POST['path'])){
                $error.="直播标识只能填写字母,数字,下划线。不可填入其他非法字符！";
            }

            if($_POST["phone"]){
                $userData = (new UcenterMemberModel())->where(['mobile'=>$_POST["phone"]])->find();
                if(empty($userData)){
                    $error .= "直播间所属人(用户手机号)用户不存在！";
                }elseif($userData['status']!=1){
                    $error .= "直播间所属人(用户手机号)用户被禁用！";
                }else{
                    $_POST["uid"] = $userData['id'];
                }
            }

            if(!empty($error)){

                $this->error($error);
            }
            $data = I('post.');
            //直播时间格式转换
            $data['start_time'] = Date('Y-m-d H:i',$data['start_time']);

            if ($cate = (new LiveRoomModel())->editData($data)) {
//                print_r($cate);

                //环信 创建群组
//                $HXReturn = CreatHuanXinChatRooms();
                $HXReturn = CreatHuanXinGroup($data['path'],$data['phone']);

                //获取群组ID
                $HXReturn['data']['groupid'];

                //将群组ID添加入库
                (new LiveRoomModel())->where(['id'=>$cate])->save(['chat_room_id'=>$HXReturn['data']['groupid']]);

                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('Live/liveList'));
            } else {
                $this->error($title.L('_FAIL_').(new LiveRoomModel())->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = (new LiveRoomModel())->find($id);
                $data['start_time'] = strtotime($data['start_time']);
                $userData = (new UcenterMemberModel())->where(['id'=>$data["uid"]])->find();
                $data['phone'] = $userData['mobile'];
            }

            $builder->title($title.L('直播'))
                ->data($data)
                ->keyId()
                ->keyText('title','直播标题')
                ->keyText('speaker','主讲人')
                ->keyText('phone','直播间所属人(用户手机号,已注册)')
                ->keyText('path','直播标识(可为字母数字，唯一不可重复)')
                ->keyTime('start_time','直播开始时间')
                ->keyText('sort','排序')
                ->keySingleImage('cover_id','直播封面')
                ->keyTextArea('description','描述')
                ->buttonSubmit(U('Live/editLive'))->buttonBack()
                ->display();
        }
    }

    //删除直播数据
    public function delLive(){

        $Ids = I('post.ids');
        if($Ids){
            (new LiveRoomModel())->where(['id'=>['in',$Ids]])->save(['status' => '-1']);
        }

        $this->success('删除成功');
    }

    //启用禁用直播数据
    public function disableLive(){
        $Ids = I('post.ids');

        if($Ids){
            $jobData = (new LiveRoomModel())->where(['id'=>['IN',$Ids]])->find();

            $status = '';
            $jobData['status'] == '1' ? $status = '0' : ($jobData['status'] == '0' ? $status = '1':'');

            (new LiveRoomModel())->where(['id'=>['IN',$Ids]])->save(['status'=>$status,'update_time'=>time()]);
        }

        $this->success('操作成功');
    }

    //激活直播
    public function startLive(){

        $Ids = I('post.ids');
        if($Ids) {
            //参数验证
            if($Ids[1]){
                $this->error('不可一起激活多个直播！');
            }

            $liveData = (new LiveRoomModel())->where(['id' => $Ids[0]])->find();
            //(0:已激活;1:直播中;2:已结束)
            $error = "";
            if($liveData['live_status']==0){
                $error .= "直播已激活,无需重新激活！";
            }
            if($liveData['live_status']==1){
                $error .= "直播已激活,无需重新激活！";
            }
//            if($liveData['live_status']==2){
//                $error .= "直播已结束,不可开始！";
//            }
            if($liveData['status']!=1){
                $error .= "数据状态为禁用,请启用之后在进行激活！";
            }

            if(!empty($error)){

                $this->error($error);
            }

//            //删除原有的聊天室Id 获取一个新的聊天室 替换数据
//            DelHuanXinChatRooms($liveData['chat_room_id']);
//
//            //环信 创建聊天室
//            $HXReturn = CreatHuanXinChatRooms();
//
//            //环信 创建群组
//            $HXReturn = CreatHuanXinGroup($liveData['path'],$liveData['phone']);
//
//            //获取群组ID
//            $HXReturn['data']['groupid'];

            //将添加入库
//            (new LiveRoomModel())->where(['id'=>$liveData['id']])->save(['live_status'=>0,'chat_room_id'=>$HXReturn['data']['id']]);
            (new LiveRoomModel())->where(['id'=>$liveData['id']])->save(['live_status'=>0]);

            $this->success('操作成功');
        }
    }

    public function endLive(){
        $Ids = I('post.ids');
        if($Ids){
            (new LiveRoomModel())->where(['id'=>['in',$Ids]])->save(['live_status' => '2']);
        }

        $this->success('结束成功');
    }

}