<?php


namespace Event\Controller;

use Think\Controller;
use Common\Lib\Lz517\IceProxyHelper;
use News\Model\NewsModel;
use Event\Model\EventModel;
use Common\Lib\WX\JSSDK;

class IndexController extends Controller
{
    //活动详情页H5
    public function detailH5($id = 203)
    {
        //是否为分享打开
        if(!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1){
            $this->assign("isApp","yes");
        }else{
            $this->assign("isApp","no");
        }

        $service = IceProxyHelper::getEventDetail();
        $eventData = $service->getOfflineEventDetailV0525(intval($id),0);
        $eventData = json_decode(json_encode($eventData),true);

        //给图片增加事件
        $eventData['explain'] = attr_imgjs($eventData['explain']);

        if($eventData['limitCount']<=0){
            $eventData['limitCountText'] = "不限制人数";
        }else{
            $eventData['limitCountText'] = "限".$eventData['limitCount']."人报名";
        }

        $eventData['signCountText'] = "已".$eventData['signCount'].'人报名';

        //微信分享的相关信息
        $wxjssdk = new JSSDK();
        $signPackage = $wxjssdk->getSignPackage();

        if(strpos($eventData['title'],'金玺奖') !== false){
            $this->assign("shareImg","http://opensns.lz517.com/JinXi.png");
            $this->assign("shareDesc",mb_substr(str_replace("&nbsp;","",strip_tags($eventData['explain'])), 0, 30, 'UTF-8'));
        }else{
            $this->assign("shareImg","http://opensns.lz517.com/share2.png");
            $this->assign("shareDesc",mb_substr(str_replace("&nbsp;","",strip_tags($eventData['explain'])), 0, 30, 'UTF-8'));
        }


        //查询顶部的轮播banner
        list($bannerData,$bannerNewData) = getTopBanner('',true);
        $this->assign("topBanners",$bannerData);
        $this->assign("topBannersNew",json_encode($bannerNewData));

        $this->assign("signPackage",$signPackage);
        $this->assign('info',$eventData);
        $this->display('Index/tmpl/detailH5');
    }

    public function updateEventdesc(){
        $newData = (new EventModel())->where(['id'=>164])->select();

        foreach($newData as &$val){
            strip_tags($val['explain']);

        }
    }
}