<?php


namespace OnlineVote\Controller;

use Think\Controller;
use Common\Lib\Lz517\IceProxyHelper;
use News\Model\NewsModel;
use Event\Model\EventModel;

class IndexController extends Controller
{
    //活动详情页H5
    public function detailH5($id = 50, $uid = 0)
    {
        //是否为分享打开
        $isApp = false;
        if (!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1) {

            $isApp = true;
            $this->assign("isApp", "yes");
        } else {
            $this->assign("isApp", "no");
        }

        //获取code 随机码
        $code = $_SERVER["HTTP_CODE"];

        //获取线上投票的数据
        $service = IceProxyHelper::getEventDetail();

        $eventData = [];

        //在App中打开
        if($isApp){

            //已登录
            if(!empty($uid)){

                $eventData = $service->getOfflineEventDetailWithVote(intval($id), intval($uid), 1);

                //未登录 新版本
            }elseif(!empty($code) && empty($uid)){

                $eventData = $service->getOfflineEventDetailWithVoteByUidOrCode(intval($id), intval($uid), $code, 1);
            }else{

                //未登录 旧版本
                $eventData = $service->getOfflineEventDetailWithVote(intval($id), 0, 1);
            }
        }else{
            $eventData = $service->getOfflineEventDetailWithVote(intval($id), 0, 1);
        }
        $eventData = json_decode(json_encode($eventData), true);
        $eventData['explain'] = attr_imgjs($eventData['explain']);

        //拼接投票数据开始结束时间
        if(!empty($eventData['eventId'])){
            $eventDataNew = (new EventModel())->where(['id'=>$eventData['eventId']])->find();
            $eventData['sTime'] = $eventDataNew['sTime'].'000';
            $eventData['eTime'] = $eventDataNew['eTime'].'000';
        }

        $eventData['deadLine'] = Date('Y-m-d H:i:s', $eventData['deadLine']);

        //查询顶部的轮播banner
        list($bannerData,$bannerNewData) = getTopBanner('',true);
        $this->assign("topBanners",$bannerData);
        $this->assign("topBannersNew",json_encode($bannerNewData));

        //将候选id拆分出前端使用
        $itemsIdArr = [];
        foreach($eventData['eventVoteDetail']['eventVoteItems'] as $val){
            $itemsIdArr[]['id'] = $val['id'];
        }

        $this->assign('info', $eventData);
        $this->assign('uid', $uid);
        $this->assign('code', !empty($code) ? $code : 0);
        $this->assign('eventId', $eventData['eventId']);
        $this->assign('eventVoteId', $eventData['eventVoteDetail']['id']);
        $this->assign('userNum', $eventData['eventVoteDetail']['userNum']);
        $this->assign('deadLine', strtotime($eventData['deadLine'])."000");
        $this->assign('stime', $eventData['sTime']);
        $this->assign('random', time());
        $this->assign('voteItems', json_encode($eventData['eventVoteDetail']['eventVoteItems']));
        $this->assign('voteItemsId', json_encode($itemsIdArr));

        //code有值调用新模板 反之使用旧模板
        if($code){
            $this->display('Index/tmpl/detailH5New');
        }else{
            $this->display('Index/tmpl/detailH5');
        }

    }

    //活动详情页H5
    public function detailH5Ceshi($id = 50, $uid = 0)
    {
        //是否为分享打开
        $isApp = false;
        if (!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1) {

            $isApp = true;
            $this->assign("isApp", "yes");
        } else {
            $this->assign("isApp", "no");
        }

        //获取code 随机码
        $code = $_SERVER["HTTP_CODE"];

        //获取线上投票的数据
        $service = IceProxyHelper::getEventDetail();

        $eventData = [];

        //在App中打开
        if($isApp){

            //已登录
            if(!empty($uid)){

                $eventData = $service->getOfflineEventDetailWithVote(intval($id), intval($uid), 1);

                //未登录 新版本
            }elseif(!empty($code) && empty($uid)){

                $eventData = $service->getOfflineEventDetailWithVoteByUidOrCode(intval($id), intval($uid), $code, 1);
            }else{

                //未登录 旧版本
                $eventData = $service->getOfflineEventDetailWithVote(intval($id), 0, 1);
            }
        }else{
            $eventData = $service->getOfflineEventDetailWithVote(intval($id), 0, 1);
        }
        $eventData = json_decode(json_encode($eventData), true);
        $eventData['explain'] = attr_imgjs($eventData['explain']);

        //拼接投票数据开始结束时间
        if(!empty($eventData['eventId'])){
            $eventDataNew = (new EventModel())->where(['id'=>$eventData['eventId']])->find();
            $eventData['sTime'] = $eventDataNew['sTime'].'000';
            $eventData['eTime'] = $eventDataNew['eTime'].'000';
        }

        $eventData['deadLine'] = Date('Y-m-d H:i:s', $eventData['deadLine']);

        //查询顶部的轮播banner
        $bannerNew = [];
        $banners = (new NewsModel())->field("id,title,cover,description")->where(["is_show_banner" => 0, "category" => 32, "status" => 1, "dead_line" => ["gt", time()], "publish_time" => ["lt", time()]])->order("sort asc,publish_time desc")->limit("0,3")->select();

        if (!empty($banners)) {
            $bannerCoverIdArr = array_column($banners, "cover");
            $bcovers = M('picture')->where(["id" => ["in", $bannerCoverIdArr]])->select();
            $bcovers = useFieldAsArrayKey($bcovers, "id");
            $bannerOrder = 1;
            foreach ($banners as &$banner) {
                $banner['banOrder'] = $bannerOrder;
                if (empty($bcovers[$banner["cover"]])) {
                    $banner["coverimg"] = "http://{$_SERVER['HTTP_HOST']}/Public/images/news_content_list_item_bg.png";
                } else {
                    $banner["coverimg"] = "http://image.lz517.com/catering" . $bcovers[$banner["cover"]]["path"];
                }
                $bannerOrder++;

                $banner["title"] = mb_strlen($banner["title"],'utf8') > 30 ? mb_substr($banner["title"], 0, 30,'utf-8') . "……" : $banner["title"];

                //拼接banner数据
                $bannerNew[] = [
                    'id' => $banner['id'],
                    'title' => str_replace("\"","",$banner['title']),
                    'description' => str_replace("\"","",mb_strlen($banner["description"],'utf8') > 30 ? mb_substr($banner["description"], 0, 30,'utf-8') . "……" : $banner["description"]),
                    'banOrder' => $banner['banOrder']
                ];
            }
        }

        //将候选id拆分出前端使用
        $itemsIdArr = [];
        foreach($eventData['eventVoteDetail']['eventVoteItems'] as $val){
            $itemsIdArr[]['id'] = $val['id'];
        }

        $this->assign("topBanners", $banners);
        $this->assign("topBannersNew", json_encode($bannerNew));
        $this->assign('info', $eventData);
        $this->assign('uid', $uid);
        $this->assign('code', !empty($code) ? $code : 0);
        $this->assign('eventId', $eventData['eventId']);
        $this->assign('eventVoteId', $eventData['eventVoteDetail']['id']);
        $this->assign('userNum', $eventData['eventVoteDetail']['userNum']);
        $this->assign('deadLine', strtotime($eventData['deadLine'])."000");
        $this->assign('stime', $eventData['sTime']);
        $this->assign('random', time());
        $this->assign('voteItems', json_encode($eventData['eventVoteDetail']['eventVoteItems']));
        $this->assign('voteItemsId', json_encode($itemsIdArr));

        //code有值调用新模板 反之使用旧模板
        if($code){
            $this->display('Index/tmpl/detailH5Ceshi');
        }else{
            $this->display('Index/tmpl/detailH5Ceshi');
        }

    }

    public function updateEventdesc()
    {
        $newData = (new EventModel())->where(['id' => 164])->select();

        foreach ($newData as &$val) {
            strip_tags($val['explain']);

        }
    }

    //投票操作
    public function voteForItem()
    {
        $itemId = I('post.itemId');
        $eventVoteId = I('post.eventVoteId');
        $uid = I('post.uid');
        $code = I('post.code');
        $eventId = I('post.eventId');

        $eventData = [];
        $service = IceProxyHelper::getEventDetail();

        //已登录
        if(!empty($uid)){
            //获取线上投票的数据
            $eventData = $service->vote(intval($eventVoteId), intval($itemId), intval($uid));
            $eventData = json_decode(json_encode($eventData), true);
            //未登录
        }elseif(!empty($code) && empty($uid)){
            //获取线上投票的数据
            $eventData = $service->voteByCode(intval($eventVoteId), intval($itemId), $code);
            $eventData = json_decode(json_encode($eventData), true);
        }

        //投票成功后 拼接新的数据
        if($eventData['success'] == 1){
            $itemData = $this -> getVoteData($eventVoteId,$uid,$code);

            $eventData['itemData'] = $itemData['eventVoteDetail']['eventVoteItems'];
            $eventData['userNum'] = $itemData['eventVoteDetail']['userNum'];
        }
        echo json_encode($eventData);
    }

    //获取线上投票详情
    private function getVoteData($voteId,$uid,$code){

        //获取线上投票的数据
        $service = IceProxyHelper::getEventDetail();

        //已登录
        if(!empty($uid)){

            $eventData = $service->getOfflineEventDetailWithVote(intval($voteId), intval($uid), 1);
            //未登录
        }elseif(!empty($code) && empty($uid)){

            $eventData = $service->getOfflineEventDetailWithVoteByUidOrCode(intval($voteId), intval($uid), $code, 1);
        }

        $eventData = json_decode(json_encode($eventData), true);
        return $eventData;
    }
}
