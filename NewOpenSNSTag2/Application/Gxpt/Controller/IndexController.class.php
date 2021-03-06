<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Gxpt\Controller;

use Common\Lib\WX\JSSDK;
use Think\Controller;
use Common\Lib\Lz517\IceProxyHelper;
use News\Model\NewsModel;
use Wechat\Model\WechatServiceGuidelinesModel;

class IndexController extends Controller{


    //供销平台详情
    public function detailH5($id = 82)
    {

        //是否为app打开
        if(!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1){
            $this->assign("isApp","yes");
        }else{
            $this->assign("isApp","no");
        }


        $service = IceProxyHelper::getGxptDetail();
        //获取产品详情
        $produtData = $service -> getProduct(intval($id),0);
        $produtData = json_decode(json_encode($produtData),true);

        if(utf8_strlen($produtData['description']) > 312){
            $produtData['descriptionNew'] = mb_substr($produtData['description'],0,312,'utf-8').".....";
        }

        //判断如果该产品没有成功案例 则在前端不显示
        if(empty($produtData['successCases'])){
            $this->assign("isSuccessCases","no");
        }else{
            $this->assign("isSuccessCases","yes");
        }

        $produtData['company']['regTimeNew'] = Date('Y-m-d',$produtData['company']['regTime']);
        $relatedData = $service -> getRelatedProductList(intval($id),intval($produtData['oneLevelCateg']['id']));
        $relatedData = json_decode(json_encode($relatedData),true);

        //查询顶部的轮播banner
        list($bannerData,$bannerNewData) = getTopBanner();
        $this->assign("topBanners",$bannerData);
        $this->assign("topBannersNew",json_encode($bannerData));

        $this->assign('info',$produtData);
        $this->assign('gxptImgUrlArr',json_encode(array_column($produtData['pictures'],'url')));
        $this->assign('relatedData',$relatedData);
        $this->display('Index/tmpl/detailH5');
    }

    //供销平台公司详情
    public function companyDetailH5($proId,$companyId,$isApp)
    {
        $service = IceProxyHelper::getGxptDetail();

        //获取公司详情
        $companyData = $service -> getCompany(intval($companyId));
        $companyData = json_decode(json_encode($companyData),true);

        if(utf8_strlen($companyData['description']) > 312){
            $companyData['descriptionNew'] = mb_substr($companyData['description'],0,312,'utf-8').".....";
        }

        //判断如果该产品 相对应的公司没有成功案例 则在前端不显示
        if(empty($companyData['successCases'])){
            $this->assign("isCompanySuccessCases","no");
        }else{
            $this->assign("isCompanySuccessCases","yes");
        }

        //获取公司其他产品列表
        $relatedData = $service -> getCompanyOrtherProductList(intval($proId),intval($companyId));
        $relatedData = json_decode(json_encode($relatedData),true);

        $this->assign('info',$companyData);
        $this->assign('isApp',$isApp);
        $this->assign('relatedData',$relatedData);
        $this->display('Index/tmpl/companyDetailH5');
    }

    /**
     * 维养H5详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showWeiyangH5(){
//        echo "<h3>这是维养</h3>";

        //是否为分享打开
        if(!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1){
            $this->assign("isApp","yes");
        }else{
            $this->assign("isApp","no");
        }

        $condition['type'] = ['eq',5];//4-餐讯上门，5-维养系统，6-微信点餐系统
        $condition['status'] = ['eq',1];//-1--删除；1--:启用；2--禁用；

        $title = '固定资产管理与维修保养系统';
        $shareDesc = '餐企标准化管理神器';
        $newData = (new WechatServiceGuidelinesModel())->where($condition)->find();

        $this->type = 5;

        //给图片增加事件
//        $newData['explain'] = attr_imgjs($newData['explain']);

        //微信分享的相关信息
        $wxjssdk = new JSSDK();
        $signPackage = $wxjssdk->getSignPackage();

        $this->assign("shareImg","http://opensns.lz517.com/share2.png");
        $this->assign("shareTitle",$title);
        $this->assign("shareDesc",$shareDesc);

        //查询顶部的轮播banner
//        list($bannerData,$bannerNewData) = getTopBanner('',true);
//        $this->assign("topBanners",$bannerData);
//        $this->assign("topBannersNew",json_encode($bannerNewData));

        $this->assign("title",$title);
        $this->assign("signPackage",$signPackage);
        $this->assign('info',$newData);
        $this->display('Index/tmpl/thirdDetailH5');
    }

    /**
     * 微信餐讯上门详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showCanXunH5(){
//        echo "<h3>这是餐讯上门</h3>";

        //是否为分享打开
        if(!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1){
            $this->assign("isApp","yes");
        }else{
            $this->assign("isApp","no");
        }

        $condition['type'] = ['eq',4];//4-餐讯上门，5-维养系统，6-微信点餐系统
        $condition['status'] = ['eq',1];//-1--删除；1--:启用；2--禁用；

        $title = '餐讯上门服务';
        $shareDesc = '后勤保障一键直达';

        $newData = (new WechatServiceGuidelinesModel())->where($condition)->find();

        $this->type = 4;

        //给图片增加事件
//        $newData['explain'] = attr_imgjs($newData['explain']);

        //微信分享的相关信息
        $wxjssdk = new JSSDK();
        $signPackage = $wxjssdk->getSignPackage();

        $this->assign("shareImg","http://opensns.lz517.com/share2.png");
        $this->assign("shareTitle",$title);
        $this->assign("shareDesc",$shareDesc);

        //查询顶部的轮播banner
//        list($bannerData,$bannerNewData) = getTopBanner('',true);
//        $this->assign("topBanners",$bannerData);
//        $this->assign("topBannersNew",json_encode($bannerNewData));

        $this->assign("title",$title);
        $this->assign("signPackage",$signPackage);
        $this->assign('info',$newData);
        $this->display('Index/tmpl/thirdDetailH5');
    }

    /**
     * 微信嘉禾点餐详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showJiaHeH5(){

        //是否为分享打开
        if(!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1){
            $this->assign("isApp","yes");
        }else{
            $this->assign("isApp","no");
        }

        $condition['type'] = ['eq',6];//4-餐讯上门，5-维养系统，6-微信点餐系统
        $condition['status'] = ['eq',1];//-1--删除；1--:启用；2--禁用；

        $title = '微信会员营销系统';
        $shareDesc = '微信引流 节约成本 会员营销';
        $newData = (new WechatServiceGuidelinesModel())->where($condition)->find();

        $this->type = 6;

        //给图片增加事件
//        $newData['explain'] = attr_imgjs($newData['explain']);

        //微信分享的相关信息
        $wxjssdk = new JSSDK();
        $signPackage = $wxjssdk->getSignPackage();

        $this->assign("shareImg","http://opensns.lz517.com/share2.png");
        $this->assign("shareTitle",$title);
        $this->assign("shareDesc",$shareDesc);

        //查询顶部的轮播banner
//        list($bannerData,$bannerNewData) = getTopBanner('',true);
//        $this->assign("topBanners",$bannerData);
//        $this->assign("topBannersNew",json_encode($bannerNewData));

        $this->assign("title",$title);
        $this->assign("signPackage",$signPackage);
        $this->assign('info',$newData);
        $this->display('Index/tmpl/thirdDetailH5');
    }
}