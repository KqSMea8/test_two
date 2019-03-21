<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Wechat\Controller;
use Common\Api\SMSService;
use Common\Lib\WX\JSSDK;
use Enterprise\Service\EnterpriseService;
use GXPT\Model\supplyMarketingCompanyQualificationModel;
use Home\Model\DistrictModel;
use Home\Model\PictureModel;
use Think\Controller;
use think\Cache;
use Wechat\Model\WechatAddressModel;
use Wechat\Model\WechatDeviceModel;
use Wechat\Model\WechatEquipmentCategoryModel;
use Wechat\Model\WechatFacilitatorModel;
use Wechat\Model\WechatInspectionModel;
use Wechat\Model\WechatKaEnterpriseModel;
use Wechat\Model\WechatKaStoresModel;
use Wechat\Model\WechatMemberStoreModel;
use Wechat\Model\WechatOrderAppraiseModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatPayModel;
use Wechat\Model\WechatServiceGuidelinesModel;
use Think\Log;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatShareArticleModel;
use Wechat\Model\WechatVocationRadarModel;
use Wechat\Model\WechatYearServiceTimeModel;
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatInspectionStoreChildDeviceModel;
use Goods\Model\SomethingPicModel;
use Enterprise\Service\InspectionService;

require_once APP_PATH ."Pay/Library/WxpayAPI/lib/instance/WxPay.NativePay.php";
require_once APP_PATH ."Pay/Library/WxpayAPI/lib/WxPay.Notify.php";

class IndexController extends Controller{

    private $appId;
    private $appSecret;
    private $token;
    private $WechatServiceGuidelinesModel;
    private $DistrictModel;
    private $WechatOrderModel;
    private $WechatOrderItemModel;
    private $WechatPublicOrderService;
    private $WechatEquipmentCategoryModel;
    private $WechatService;
    private $WechatOrderAppraiseModel;
    private $WechatMemberModel;
    private $WechatKaEnterpriseModel;
    private $WechatKaStoresModel;


    function _initialize()
    {
        $this->appId = WECHAT_APPID;
        $this->appSecret = WECHAT_APPSECRET;
        $this->token = WECHAT_TOKEN;

        $this->WechatServiceGuidelinesModel = new WechatServiceGuidelinesModel();
        $this->DistrictModel = new DistrictModel();
        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatOrderItemModel = new WechatOrderItemModel();
        $this->WechatPublicOrderService = new WechatPublicOrderService();
        $this->WechatEquipmentCategoryModel = new WechatEquipmentCategoryModel();
        $this->WechatService = new WechatService();
        $this->WechatOrderAppraiseModel = new WechatOrderAppraiseModel();
        $this->WechatMemberModel = new WechatMemberModel();
        $this->WechatKaEnterpriseModel = new WechatKaEnterpriseModel();
        $this->WechatKaStoresModel = new WechatKaStoresModel();

    }

    /**
     * 使用openId作为用户登录的标记
     */
    private function wechatLogin(){
        $wechatService = new WechatService();
        if (empty($_REQUEST['code']))
        {
            $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $url = $wechatService->getSNSOauthUrl($url);
            header("Location:" . $url);
            die;
        }
        @file_put_contents("pangyongfu.log","获取Code的数据是：".$_GET["code"]."\r\n",FILE_APPEND);

        $accessTokenData = $wechatService->getSNSAccessToken($_GET["code"]);
        @file_put_contents("pangyongfu.log","获取accessTokenData的数据是：".$accessTokenData."\r\n",FILE_APPEND);
        $accessTokenData = json_decode($accessTokenData, true);

        //设置判断如果用户code重复则到redis中获取
        $redis = getRedisClient();
        if($accessTokenData['errcode']){
            $accessTokenData = $redis->get('canxun:wechat:'.$_GET["code"]);
            @file_put_contents("pangyongfu.log","获取Redis中的code对应的openID：".json_decode($accessTokenData,true)['openid']."\r\n",FILE_APPEND);
            $openId = json_decode($accessTokenData,true)['openid'];
            unset($accessTokenData);
        }

        if($accessTokenData['openid']){
            $redis->set('canxun:wechat:'.$_GET["code"],json_encode($accessTokenData));
            @file_put_contents("pangyongfu.log","存到Redis中的code对应的openID || ：".$accessTokenData['openid']."\r\n",FILE_APPEND);
            @file_put_contents("pangyongfu.log","accessTokenDat数据：".json_encode($accessTokenData)."\r\n",FILE_APPEND);
            $redis->EXPIRE('canxun:wechat:'.$_GET["code"], 1800);
            $openId = $accessTokenData['openid'];
        }
        @file_put_contents("pangyongfu.log","获取openid：".$openId."\r\n",FILE_APPEND);
        //1.通过openId查询是否已存在该用户
        $mem = (new WechatMemberModel())->where(['open_id'=>$openId])->find();
        @file_put_contents("pangyongfu.log","根据OpenId查询出的member是：".json_encode($mem)."\r\n",FILE_APPEND);

        //验证用户是否已注册 如果没有跳转注册页面
        if(empty($mem)){

            $member = (new WechatMemberModel());

            //微信用户信息
            $wechatService = new WechatService();
            $userInfo = $wechatService->getUserInfo($openId);
            $userData = [];
            if(isset($userInfo['headimgurl'])){
                $avatar = $userInfo['headimgurl'];
                $userData['pic'] = $avatar;
            }

            $userData['open_id'] = $openId;
            $userData['name'] =$userInfo['nickname'];
            @file_put_contents("pangyongfu.log","将用户数据存储到表：".json_encode($userData)."\r\n",FILE_APPEND);
            //4.将用户数据存储到表中
            $wechatUser = $member->editData($userData);

            session("memId",null);
            session("openId",null);
            session("memId",$wechatUser);
            session("openId",$openId);
        }elseif(!$mem['phone']){

            session("memId",null);
            session("openId",null);
            session("memId",$mem['uid']);
            session("openId",$mem['open_id']);

            log::write('用户session数据 | '.session("memId")." | ".$mem['uid']);

            //获取来源网址,即点击来到本页的上页网址
            //这一步处理是为了注册成功时内部调转会多创建一条数据的问题
            session('cxHttpReferer',explode('&',$_SERVER["REQUEST_URI"])[0]);

            log::write('跳转页面 | '.session('cxHttpReferer'));

            $this->redirect('registLogin');
        }else{

            log::write('记录测试');
            session("memId",null);
            session("openId",null);
            session("memId",$mem['uid']);
            session("openId",$mem['open_id']);
        }

    }

    /**
     * 使用openId作为用户登录的标记
     */
    private function wechatLoginNew(){

        $wechatService = new WechatService();
        if (empty($_REQUEST['code']))
        {
            $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $url = $wechatService->getSNSOauthUrl($url);
            header("Location:" . $url);
            die;
        }

        Log::write("获取Code的数据是：".$_GET["code"]);

        $accessTokenData = $wechatService->getSNSAccessToken($_GET["code"]);
        $accessTokenData = json_decode($accessTokenData, true);

        Log::write("获取accessTokenData的数据是：".json_encode($accessTokenData));

        $openId = $accessTokenData['openid'];
        if($accessTokenData['errcode'] && session("openId")){
            $openId = session("openId");
        }
        //1.通过openId查询是否已存在该用户
        $mem = (new WechatMemberModel())->where(['open_id'=>$openId])->find();
        Log::write('根据OpenId查询出的member是'.json_encode($mem));

        //验证用户是否已注册 如果没有跳转注册页面
        if(empty($mem)){

            $member = (new WechatMemberModel());

            //微信用户信息
            $wechatService = new WechatService();
            $userInfo = $wechatService->getUserInfo($openId);
            $userData = [];
            if(isset($userInfo['headimgurl'])){
                $avatar = $userInfo['headimgurl'];
                $userData['pic'] = $avatar;
            }

            $userData['open_id'] = $openId;
            $userData['name'] =$userInfo['nickname'];

            //4.将用户数据存储到表中
            $wechatUser = $member->editData($userData);
            log::write('记录测试用户数据memId New | '.$wechatUser);
            log::write('记录测试用户数据openId New | '.$openId);

            session("memId",null);
            session("openId",null);
            session("memId",$wechatUser);
            session("openId",$openId);
            session('cxHttpReferer','http://opensns.lz517.com/index.php?s=/wechat/index/myCenter');
        }else{

            log::write('记录测试用户数据New | '.json_encode($mem));
            session("memId",null);
            session("openId",null);
            session("memId",$mem['uid']);
            session("openId",$mem['open_id']);
            session('cxHttpReferer','http://opensns.lz517.com/index.php?s=/wechat/index/myCenter');
        }

    }

    //获取微信验证
    public function verifyWechat(){

        $token = $this->token;

        if(empty($_GET["echostr"])){
            $wechatService = new WechatService();

            //解析
            $wechatService->parseMsg();

            //处理微信事件(关注)
            if($wechatService->getRev('MsgType') == 'event'){

                $this -> wechatEvent($wechatService);
            }

            if($wechatService->getRev('MsgType') == 'text'){
                $toUser = $wechatService->getRev('ToUserName');
                $fromUser = $wechatService->getRev('FromUserName');
                $time = time();
                log::write('客服消息toUser | '.$toUser);
                log::write('客服消息fromUser | '.$fromUser);
                $itemTpl = '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[transfer_customer_service]]></MsgType></xml>';
                $return = sprintf($itemTpl,$fromUser,$toUser,$time);
                echo $return;

            }

        }else {

            (new WechatService())->verify($_GET["echostr"], $_GET["signature"], $_GET["timestamp"], $_GET["nonce"], $token);
            die;
        }
    }

    //清空模块缓存数据使用
    public function unsetSModuleData(){

        S('module_all',null);
    }

    /**
     * 获取微信js签名信息
     */
    private function getWechatJsSignPackage(){
        // 调用微信JS接口
        $wechatService = new WechatService() ;
        $package = $wechatService->getSignPackage ();

        $this->assign('appId',$package ['appId']);
        $this->assign('timestamp',$package ['timestamp']);
        $this->assign('nonceStr',$package ['nonceStr']);
        $this->assign('signature',$package ['signature']);
    }

    /**
     * 获取微信js签名信息
     */
    public function getWechatJsSignPackageCeshi(){
        // 调用微信JS接口
        $wechatService = new WechatService() ;
        $package = $wechatService->getSignPackage ();

        $this->assign('appId',$package ['appId']);
        $this->assign('timestamp',$package ['timestamp']);
        $this->assign('nonceStr',$package ['nonceStr']);
        $this->assign('signature',$package ['signature']);
    }

    /**
     * 操作用户在关注公众号时存储用户的openId
     * @param $wechatService
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    private function wechatEvent($wechatService){

        $event = $wechatService -> getRev('Event');
        $openId = $wechatService -> getRev('FromUserName');

        switch($event)
        {
            case 'unsubscribe': //取消关注
                break;
            case 'SCAN'://扫描
                break;
            case 'subscribe' : //关注

                $redis = getRedisClient();
            //    file_put_contents("huangqing.log",'关注微信获取到的微信分享文章标识为One：'.$redis->get('canxun:wechatSharePaper:shareArticleId_'.$wechatService->getRev('FromUserName').'')."\r\n",FILE_APPEND);

                log::write('用户渠道码New || '.$wechatService->getRev('EventKey'));

                //用户关注时存储用户的信息
                $this->addWechatUser($openId,$wechatService->getRev('EventKey'));

                //判断用户是否已经注册 给用户发送消息
                $userData = $this->WechatMemberModel->where(['open_id'=>$openId,'type_user'=>1])->find();
                session("memId",$userData['uid']);
                session("openId",$userData['open_id']);

                //如果已注册则让用户跳转至 个人中心页面  反之跳转注册
                if(!empty($userData['phone'])){
                    $wechatService -> senWechatAttentionMsg($openId);
                }else{
                    $wechatService -> senWechatAttentionMsg($openId);
                }

                //发送消息 文章阅读消息 图文消息
                $shareArticleId = $redis->get('canxun:wechatSharePaper:shareArticleId_'.$wechatService->getRev('FromUserName'));
                if($shareArticleId){
     //               file_put_contents("huangqing.log",'进入1:'."\r\n",FILE_APPEND);
                    $wechatService -> sendWechatShareArticleMsg($openId,$shareArticleId);
                    $redis->del('canxun:wechatSharePaper:shareArticleId_'.$wechatService->getRev('FromUserName'));
                }
                break;
        }

    }

    /**
     * 用户关注时添加微信用户
     *uthor  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    private function addWechatUser($openId,$userChannel = ''){

        //1.通过openId查询是否已存在该用户
        $memData = (new WechatMemberModel())->where(['open_id'=>$openId])->find();

        Log::write('验证是否存在该OpenId'.json_encode($memData));

        $data = [];
        //拼接添加用户的渠道码
        if(!empty($userChannel)){
            $data['channel_code'] = explode('_',$userChannel)[1];
        }

        //2.如果已存在则跳出
        if(!$memData['uid']){
            //3.如果不存在 获取用户头像
            $wechatUser = (new WechatService())->getUserInfo($openId);
            if($wechatUser){
                $data['pic'] = $wechatUser['headimgurl'];
                $data['name'] = $wechatUser['nickname'];
                $data['open_id'] = $wechatUser['openid'];
                //4.存储到表中
                (new WechatMemberModel())->editMemberData($data);
            }
        }
    }

    //用户协议页面
    public function userAgreement(){

        $this->display('WechatPublic/my/userAgreement');
    }

    //创建微信菜单
    public function createWxMenu(){

        $returnMsg = (new WechatService())->createClientMenu();
        print_r($returnMsg);die;
    }

    //删除微信菜单
    public function delWxMenu(){

        $returnMsg = (new WechatService())->deleteMenu();
        print_r($returnMsg);die;
    }

    //跳转微信过往文章
    public function skipPasiPaper(){

        Header("Location: http://mp.weixin.qq.com/mp/homepage?__biz=MzIyOTY3NTUzNw==&hid=1&sn=e4f67abf2fcb92b45f6d37dfc3589b68#wechat_redirect");
    }

    /**
     * 服务须知
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showServiceGuide(){

        $type = I('type'); //type = 1 门店维修 2 门店消杀 3 设备清洗

        //判断如果数据不存在则跳转到错误页面
        if(!$type){
            throw_exception('服务器出错，请稍后重试！');
        }

        $condition['type'] = ['eq',$type];//1-设备维修，2-门店消杀，3-设备清洗
        $condition['status'] = ['eq',1];//-1--删除；1--:启用；2--禁用；

        $guide = $this->WechatServiceGuidelinesModel->where($condition)->find();
        $this->guide = $guide;
        $this->type = $type;
        $this->url = "/Wechat/Index/selectEqipmentType";
        $this->display('WechatPublic/serviceguide');
    }

    /**
     * 创建订单
     * @author pangyongfu
     */
    public function createOrder(){
        $order_type = I('order_type');
        $res = $this->WechatPublicOrderService ->creatOrder($order_type);
        echo json_encode($res);
        exit;
    }

    /**
     * 更新订单
     * @author pangyongfu
     */
    public function updateOrder(){
        $order_state = I('order_state');
        $res = $this->WechatPublicOrderService ->updateOrder($order_state);

        //师傅服务完成后（消杀）给用户发待验收微信模板消息
        if($order_state == 9 && $res['status'] == 1){

            //发送用户微信模板消息
            $this->WechatService->sendMasterCleanKillFinishNew(I('id'));
        }

        echo json_encode($res);
        exit;
    }
///////////////////////////////////////////////////////////////////////////设备维修开始////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 设备维修首页
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
//    public function storeMaintain(){
//        $condition['type'] = ['eq',1];//1-设备维修，2-门店消杀，3-设备清洗
//        $condition['status'] = ['eq',1];//-1--删除；1--:启用；2--禁用；
//
//        $guide = $this->WechatServiceGuidelinesModel->where($condition)->find();
//        $this->guide = $guide;
//        $this->type = 1;
//        $this->url = "/Wechat/Index/selectEqipmentType";
//        $this->display('WechatPublic/serviceguide');
//    }

    /**
     * 选择设备类型
     * @author pangyongfu
     */
    public function selectEqipmentType(){
        //取出所有有效的设备类型
//        $condition['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
//        $this->typeData = $this->WechatEquipmentCategoryModel->where($condition)->select();
        $this->order_id = I('order_id') ? I('order_id') : 0;
        $this->is_ka = I('is_ka') ? I('is_ka') : 0;
        $this->companycode = I('companycode') ? I('companycode') : "";
        $this->storecode = I('storecode') ? I('storecode') : "";
        $this->url = "/Wechat/Index/appointmentStoreRepaire";
        $this->display('WechatPublic/storeMaintain/selecteqipmenttype');
    }

    /**
     * 预约维修服务$
     * @author pangyongfu
     */
    public function appointmentStoreRepaire($equip_id,$is_ka=0,$companycode="",$storecode="",$add_id=""){
        $this->signpackge = $this->WechatService->getSignPackage();
//        $this->province = $this->DistrictModel->where(['id'=>['eq',110000]])->select();
//        $condition['upid'] = ['eq',110000];
//        // TODO 目前地址有限制，只开放海淀区 昌平区
//        $city = $this->DistrictModel->where($condition)->select();
//        $merge_city = [["id"=>0,"name"=>"请选择","level"=>"2","upid"=>"110000"]];
//        $this->city = array_merge($merge_city,$city);
        $this->equipment_id = $equip_id;
        $this->is_ka = $is_ka;
        $this->companycode = $companycode;
        $this->storecode = $storecode;
        if($is_ka){
            $this->companyStoreData = $this->getCompanyStoreData($companycode,$storecode);
        }
        //获取个人信息
        $memberId = session("memId");
        $memberInfo=[];
        if(!empty($memberId) && !$is_ka){
            $addressModel = new WechatAddressModel();
            if($add_id){
                $memberInfo = $addressModel->getAddressAreaInfo(array('addr.add_id'=>$add_id));
            }else{
                $memberInfo = $addressModel->getAddressAreaInfo(array('addr.member_id'=>$memberId,'addr.is_default'=>1,'addr.status'=>1));
            }
        }
        $len = mb_strlen($memberInfo['detail_address'],"utf-8");
//        echo $memberInfo['detailed_address'];die;
        if($len > 30){
            $memberInfo['detail_address'] = mb_substr($memberInfo['detail_address'],0,30,"utf-8")."...";
        }
        $this->memberInfo = $memberInfo;
        $this->display('WechatPublic/storeMaintain/appointmentstorerepaire');
    }
/////////////////////////////////////顾客再次下单 开始///////////////////////////////////////////////////////////////
    /**
     * 顾客再次预约下单页面
     * @author pangyongfu
     */
    public function againStoreRepaire($equip_id,$order_id,$add_id = ""){
        $this->signpackge = $this->WechatService->getSignPackage();
        $this->equipment_id = $equip_id;
        $this->order_id = $order_id;
        //获取订单数据
        $map['orde.id'] = ['eq',$order_id];
        //订单数据
        $field = "orde.store_name,orde.detailed_address,orde.link_person,orde.link_phone,orde.fixed_line,orde.province,orde.city,orde.enterprise_name,orde.is_ka,orde.location_name,orde.latng";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        //如果是ka订单就获取地址
//        if($orderData['is_ka'] && $add_id){
//            $addressModel = new WechatAddressModel();
//            $memberInfo = $addressModel->where(array('add_id'=>$add_id))->find();
//            $orderData['store_name'] = $memberInfo['store_name'];
//            $orderData['location_address'] = $memberInfo['location_address'];
//            $orderData['detail_address'] = $memberInfo['detail_address'];
//            $orderData['link_person'] = $memberInfo['link_person'];
//            $orderData['link_phone'] = $memberInfo['link_phone'];
//            $orderData['fixed_line'] = $memberInfo['fixed_line'];
//        }
        $is_ka = 0;
        if($orderData['is_ka'] == 1){
            $is_ka = $orderData['is_ka'];
            $companycode = $orderData['is_ka'];
            $storecode = $orderData['is_ka'];
            $this->companyStoragaineData = $this->getCompanyStoreData($companycode,$storecode);
            $this->is_ka = $is_ka;
            $this->companycode = $companycode;
            $this->storecode = $storecode;
        }
        //获取个人信息
        $memberId = session("memId");
        $memberInfo=[];

        if(!empty($memberId) && !$is_ka){
            $addressModel = new WechatAddressModel();
            if(!empty($add_id)){
                $memberInfo = $addressModel->getAddressAreaInfo(array('addr.add_id'=>$add_id));
            }else{
                $memberInfo = $addressModel->getAddressAreaInfo(array('addr.member_id'=>$memberId,'addr.is_default'=>1,'addr.status'=>1));
            }
        }
        $len = mb_strlen($memberInfo['detail_address'],"utf-8");
//        echo $memberInfo['detailed_address'];die;
        if($len > 30){
            $memberInfo['detail_address'] = mb_substr($memberInfo['detail_address'],0,30,"utf-8")."...";
        }
        $this->memberInfo = $memberInfo;
        $this->orderData = $orderData;

        $this->display('WechatPublic/storeMaintain/againstorerepaire');
    }

    /**
     * 顾客再次下单
     * @author pangyongfu
     */
    public function customerOrderAgain(){
        $res = $this->WechatPublicOrderService ->customerOrderAgain();
        echo json_encode($res);
        exit;
    }
///////////////////////////////////////////顾客再次下单 结束/////////////////////////////////////////////////////
    /**
     * 维修订单详情
     * @param $order_id
     * @author pangyongfu
     */
    public function showStoreRepaireOrder($order_id){
        $this->wechatLogin();
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        //订单数据
        $field = "orde.*,item.equipment_name,item.brands_text,item.malfunction_text,item.after_sale_text,item.cancel_text,item.change_parts_text,item.parts_price,item.is_maintain,item.is_change_parts,item.urgent_level,member.uid,member.name,member.phone";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        log::write('维修订单取出的数据为 | '.json_encode($orderData));
        if(empty($orderData['change_parts_text'])){
            $orderData['change_parts_text'] = "无";
        }
        //订单如果是待支付 拼接微信支付代码
        if($orderData['order_state'] == '2'){

            $wechatService = new WechatService();
            $orderCode = $orderData['order_code'];

            //获取订单数据 需支付的金额
            $money = $orderData['money_total'];

            //获取微信js签名信息
            $this->getWechatJsSignPackage();

            //获取用户的open_id
            $memId = session("memId");
            $member = (new WechatMemberModel())->where(['uid'=>$memId])->find();
            $openId = $member['open_id'];
//            $openId = 'o3-pW08WIJsLhAFtLNLATBfhrNzM'; //永福 微信标识

            //支付回调地址
            $notifyUrl = host_url.'/index.php/Wechat/Notify/wxPayNotify';
            require_once APP_PATH . "Pay/Library/WxpayAPI/lib/instance/WxPay.JsApiPay.php";


            //1.判断该订单师傅是否已经更改过价格 如果价格相等 取pay表最新的数据
            $orderNewData = $wechatService->getPayDataForOrderCode($orderCode);

            //循环取得第一个就是最新生成的订单号
            $orderGGCode = '';
            $orderNum = '';
            foreach($orderNewData as $val){
                if(strstr($val['trade_no'],"GG") && !strstr($val['trade_no'],"SM")){
                    $orderCode = $val['trade_no'];

                    //获取可编辑的更改价格的订单号
                    $orderGGCode = $val['trade_no'];

                    //获取订单号中前缀自增的数字
                    $orderNum = explode('GG',$val['trade_no'])[0];
                    break;
                }
            }

            //2.如果money_total 与 money_total_old 字段不一致的话,则价格就是已经变更
            if($orderData['money_total'] != $orderData['money_total_old']){

                //2.1 首先去查pay表是否已经有生成的变更价格的订单了,如果有取出在原有的基础上 将订单号头一位自增加一 ，避免重复
                if(!empty($orderGGCode)){
                    $orderNumNew = $orderNum+1;
                    $orderCode = $orderNumNew.'GG'.$orderData['order_code'];
                }else{
                    $orderCode = '1GG'.$orderData['order_code'];
                }

                //3.统一修改价格
                $this->WechatOrderModel->where(['id'=>$order_id])->save(['money_total_old'=>$orderData['money_total']]);
            }

            log::write('生成的全新订单号:'.$orderCode);
            log::write('生成的全新价格:'.$money);

            //调用统一下单，获取支付相关数据
            $UnifiedOrderResult = $wechatService->unifiedOrder($orderCode,$money,$openId,$notifyUrl,'1');
            $jsApiParameters = (new \JsApiPay())->GetJsApiParameters($UnifiedOrderResult);
            $jsApiParameters = json_decode ( $jsApiParameters, true );

            //增加payLog数据
            $wechatService->addPayLog($orderCode,$money,1,0,$orderCode);

            //扫码支付
            $payUrl = $wechatService->scanCodePay('SM'.$orderCode,$notifyUrl,$orderData['id'],$money);

            //增加payLog数据
            $wechatService->addPayLog('SM'.$orderCode,$money,1,0,'SM'.$orderCode);

            $this->assign('p_timeStamp',$jsApiParameters['timeStamp']);
            $this->assign('p_nonceStr',$jsApiParameters['nonceStr']);
            $this->assign('p_package',$jsApiParameters['package']);
            $this->assign('p_paySign',$jsApiParameters['paySign']);
            $this->assign('p_signType',$jsApiParameters['signType']);
            $this->assign('payUrl',$payUrl);

            $this->assign("orderId",$order_id);
            $this->assign("orderCode",$orderCode);
            $this->assign("moneyPay",$orderData['money_pay']);
            $this->assign("orderState",$orderData['order_state']);
        }

        //图片数据
        $imgData = $this->WechatPublicOrderService->getOrderImg($order_id);
        //维修部位图片
        $this->afterImgData = $this->WechatPublicOrderService->getOrderImg($order_id,1);
        $len = mb_strlen($orderData['detailed_address'],"utf-8");
        if($len > 45){
            $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,45,"utf-8")."...";
        }
        $this->orderData = $orderData;
        $this->imgData = $imgData;
        $this->display('WechatPublic/storeMaintain/showstorerepaireorder');
    }

    /**
     * 维修订单详情 生成支付二维码专用
    * @param $order_id
    * @author pangyongfu
    */
    public function showStoreRepaireOrderTest($order_id){
//        $this->wechatLogin();
            //拼凑条件
            $map['orde.id'] = ['eq',$order_id];
            //订单数据
            $field = "orde.*,item.equipment_name,item.brands_text,item.malfunction_text,item.after_sale_text,item.cancel_text,item.change_parts_text,item.parts_price,item.is_maintain,item.is_change_parts,member.uid,member.name,member.phone";
            $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
            log::write('维修订单取出的数据为 | '.json_encode($orderData));
            if(empty($orderData['change_parts_text'])){
                $orderData['change_parts_text'] = "无";
            }
            //订单如果是待支付 拼接微信支付代码
            if($orderData['order_state'] == '2'){

                //检查生成订单支付数据
                $wechatService = new WechatService();
                $renturnData = $wechatService->checkOrderNoPay($orderData);

                $this->p_timeStamp = $renturnData['p_timeStamp'];
                $this->p_nonceStr = $renturnData['p_nonceStr'];
                $this->p_package = $renturnData['p_package'];
                $this->p_paySign = $renturnData['p_paySign'];
                $this->p_signType = $renturnData['p_signType'];
                $this->payUrl = $renturnData['payUrl'];

                $this->moneyPay = $renturnData['moneyPay'];
                $this->orderId = $order_id;
                $this->orderCode = $orderData['order_code'];
                $this->orderState = $orderData['order_state'];

        }

        //图片数据
        $imgData = $this->WechatPublicOrderService->getOrderImg($order_id);
        $this->orderData = $orderData;
        $this->imgData = $imgData;
        $this->display('WechatPublic/storeMaintain/showstorerepaireorder');
    }

    /**
     * 上传图片
     * @author pangyongfu
     */
    public function downLoadImage(){

        set_time_limit(0);

        $dirname = "./Public/wechatLogImg/repaire/".date("Ymd")."/";

        if ($_FILES["fileVal"]["error"] > 0) {
            echo "Return Code: " . $_FILES["fileVal"]["error"] . "<br />";//获取文件返回错误
        } else {
            //自定义文件名称
            $array=$_FILES["fileVal"]["type"];
            $array=explode("/",$array);
            $filename= time().rand(100, 999).".".$array[1];//自定义文件名（测试的时候中文名会操作失败）

            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }


            move_uploaded_file($_FILES["fileVal"]["tmp_name"],$dirname.$filename);

            $this->ajaxReturn(['data'=>$dirname . $filename]);
        }
    }
    /**
     * 上传图片
     * @author pangyongfu
     */
    public function downLoadImageForClean(){

        set_time_limit(0);

        $dirname = "./Public/wechatLogImg/cleaning/".date("Ymd")."/";

        if ($_FILES["fileVal"]["error"] > 0) {
            echo "Return Code: " . $_FILES["fileVal"]["error"] . "<br />";//获取文件返回错误
        } else {
            //自定义文件名称
            $array=$_FILES["fileVal"]["type"];
            $array=explode("/",$array);
            $filename= time().rand(100, 999).".".$array[1];//自定义文件名（测试的时候中文名会操作失败）

            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }


            move_uploaded_file($_FILES["fileVal"]["tmp_name"],$dirname.$filename);

            $this->ajaxReturn(['data'=>$dirname . $filename]);
        }
    }
    /**
     * 上传消杀图片
     * @author pangyongfu
     */
    public function downLoadImageForCleanKill(){

        set_time_limit(0);

        $dirname = "./Public/wechatLogImg/cleankill/".date("Ymd")."/";

        if ($_FILES["fileVal"]["error"] > 0) {
            echo "Return Code: " . $_FILES["fileVal"]["error"] . "<br />";//获取文件返回错误
        } else {
            //自定义文件名称
            $array=$_FILES["fileVal"]["type"];
            $array=explode("/",$array);
            $filename= time().rand(100, 999).".".$array[1];//自定义文件名（测试的时候中文名会操作失败）

            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }


            move_uploaded_file($_FILES["fileVal"]["tmp_name"],$dirname.$filename);

            $this->ajaxReturn(['data'=>$dirname . $filename]);
        }
    }
    /**
     * 上传图片
     * @author pangyongfu
     */
    public function downLoadImageFoDevice(){

        set_time_limit(0);

        $dirname = "./Public/wechatLogImg/device/".date("Ymd")."/";

        if ($_FILES["fileVal"]["error"] > 0) {
            echo "Return Code: " . $_FILES["fileVal"]["error"] . "<br />";//获取文件返回错误
        } else {
            //自定义文件名称
            $array=$_FILES["fileVal"]["type"];
            $array=explode("/",$array);
            $filename= time().rand(100, 999).".".$array[1];//自定义文件名（测试的时候中文名会操作失败）

            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }


            move_uploaded_file($_FILES["fileVal"]["tmp_name"],$dirname.$filename);

            $this->ajaxReturn(['data'=>$dirname . $filename]);
        }
    }
    /**
     * 维修评价页面
     * @author pangyongfu
     */
    public function repaireAppraise(){
        $this->member_id = I('member_id');
        $this->workers_id = I('workers_id');
        $this->order_id = I('order_id');
        $this->display('WechatPublic/storeMaintain/repaireappraise');
    }
    /**
     * 清洗评价页面
     * @author pangyongfu
     */
    public function cleaningAppraise(){
        $this->member_id = I('member_id');
        $this->workers_id = I('workers_id');
        $this->order_id = I('order_id');
        $this->display('WechatPublic/storeCleaning/cleaningappraise');
    }
    /**
     * 消杀评价页面
     * @author pangyongfu
     */
    public function cleanKillAppraise(){
        $this->member_id = I('member_id');
        $this->workers_id = I('workers_id');
        $this->order_id = I('order_id');

        I('order_code');
        $this->display('WechatPublic/storeCleanKill/cleankillappraise');
    }
    /**
     * 创建评价记录并更新订单状态
     * @author pangyongfu
     */
    public function createOrderAppraise(){
        //评价数据
        $appData['member_id'] = I('member_id');
        $appData['workers_id'] = I('workers_id');
        $appData['order_id'] = I('order_id');
        $appData['delivery_score'] = I('delivery_score');
        $appData['content'] = I('content');
        //订单数据
        $orderData['id'] = I('order_id');
        $orderData['order_state'] = I('order_state');
        $map['order_id'] = I('order_id');
        $appPriseData = $this->WechatOrderAppraiseModel->where($map)->find();
        if(!empty($appPriseData)){
            echo json_encode(array('status'=>0,'msg'=>"您已评价该订单！"));
            exit;
        }
        $appRes = $this->WechatOrderAppraiseModel->editData($appData);
        $orderRes = $this->WechatOrderModel->save($orderData);
        if($appRes && $orderRes){
            echo json_encode(array('status'=>1,'data'=>$orderData));
            exit;
        }
        echo json_encode(array('status'=>0,'msg'=>"提交失败，请稍后重试！"));
        exit;
    }
    /**
     * 维修申请售后
     */
    public function applyRepaireService(){
        $this->order_id = I('order_id');
        $this->order_code = I('order_code');
        $this->create_time = I('create_time');
        $this->display('WechatPublic/storeMaintain/applyrepaireservice');
    }
    /**
     * 消杀申请售后
     */
    public function applyCleanKillService(){
        $this->order_id = I('order_id');
        $this->order_code = I('order_code');
        $this->create_time = I('create_time');
        $this->display('WechatPublic/storeCleanKill/applycleankillservice');
    }
    /**
     * 清洗申请售后
     */
    public function applyCleaningService(){
        $this->order_id = I('order_id');
        $this->order_code = I('order_code');
        $this->create_time = I('create_time');
        $this->display('WechatPublic/storeCleaning/applycleaningservice');
    }

    /**
     * 申请售后（维修·清洗·消杀）
     */
    public function applyAfterSale(){
        $memberId = session("memId");
        $order_id = I('order_id');
        if(empty($memberId)){
            $this->wechatLogin();
        }
        //检查该用户的该订单是否有未完成的售后订单
        $where['orde.order_state'] = ['in',[0,1,2,8,9,10]];
        $where['orde.member_id'] = $memberId;
        $where['orde.is_sh'] = 1;//售后订单
        $where['item.old_order_id'] = $order_id;//旧订单
        $orderDataSH = $this->WechatOrderModel->alias("orde")->join("jpgk_wechat_order_item as item on orde.id = item.order_id")->where($where)->find();
        if(!empty($orderDataSH)){
            $this->ajaxReturn(["status" => 0,"msg" => "您还有售后订单未完成"]);
        }
        $shOrder = [];
        //获取订单数据
        $condition['orde.id'] = $order_id;
        $orderData = $this->WechatOrderModel->getOrderAndItemData($condition,"orde.*,orde.id order_id_old,item.*");
        $orderData = $orderData[0];

        //检查师傅是否有效
        $masterData = $this->WechatMemberModel->where(['uid'=>$orderData['workers_id'],'status'=>1])->find();
        $masterSend = false;
        $distSend = false;
        if(!empty($masterData)){
            $shOrder['workers_id'] = $orderData['workers_id'];
            $shOrder['facilitator_id'] = $orderData['facilitator_id'];
            $shOrder['supervisor_id'] = $orderData['supervisor_id'];
            $shOrder['order_state'] = 10;//未接单
            $masterSend = true;
            $distSend = true;
        }else{
            //检验服务商是否有效
            $facilitatorData = (new WechatFacilitatorModel())->where(['id'=>$orderData['facilitator_id'],'status'=>1])->find();
            if(!empty($facilitatorData)){
                $shOrder['workers_id'] = 0;
                $shOrder['supervisor_id'] = 0;
                $shOrder['facilitator_id'] = $orderData['facilitator_id'];
                $shOrder['order_state'] = 0;//未接单
                $distSend = true;
            }
        }
        //创建售后订单
        $res = $this->WechatPublicOrderService->createSHOrder($orderData,$shOrder,$memberId,$masterSend,$distSend);
        $this->ajaxReturn($res);
    }

///////////////////////////////////////////////////////////////////////////设备维修结束////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////门店消杀开始////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 门店消杀首页
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
//    public function storeCleanKill(){
//        $condition['type'] = ['eq',2];//1-设备维修，2-门店消杀，3-设备清洗
//        $condition['status'] = ['eq',1];//-1--删除；1--:启用；2--禁用；
//
//        $guide = $this->WechatServiceGuidelinesModel->where($condition)->find();
//        $this->guide = $guide;
//        $this->type = 2;
//        $this->url = "/Wechat/Index/appointmentCleanKill";
//        $this->display('WechatPublic/serviceguide');
//    }

    /**
     * 预约消杀服务
     * @author pangyongfu
     */
    public function appointmentCleanKill($is_ka=0,$companycode="",$storecode="",$add_id=""){
//        $this->wechatLogin();
//        $this->province = $this->DistrictModel->where(['id'=>['eq',110000]])->select();
//        $condition['upid'] = ['eq',110000];
//        // TODO 目前地址有限制，只开放海淀区 昌平区
//        $city = $this->DistrictModel->where($condition)->select();
//        $merge_city = [["id"=>0,"name"=>"请选择","level"=>"2","upid"=>"110000"]];
//        $this->city = array_merge($merge_city,$city);
        //获取个人信息
        //获取ka信息
        $this->is_ka = $is_ka;
        $this->companycode = $companycode;
        $this->storecode = $storecode;
        if($is_ka){
            $this->companyStoreData = $this->getCompanyStoreData($companycode,$storecode);
        }
        $memberId = session("memId");
        $memberInfo = [];
        if(!empty($memberId) && !$is_ka){
            $addressModel = new WechatAddressModel();
            if($add_id){
                $memberInfo = $addressModel->getAddressAreaInfo(array('addr.add_id'=>$add_id));
            }else{
                $memberInfo = $addressModel->getAddressAreaInfo(array('addr.member_id'=>$memberId,'addr.is_default'=>1,'addr.status'=>1));
            }
            //如果下过单就默认门店面积和门店场景
            $isHaveOrder = $this->WechatOrderModel->where(array('member_id'=>$memberId))->field('id')->order('id desc')->find();
            if(!empty($isHaveOrder)){
                $orderItemData = $this->WechatOrderItemModel->where(array('order_id'=>$isHaveOrder['id']))->field('store_area,store_scene')->find();
                $this->store_area = $orderItemData['store_area'];
                $this->store_scene = $orderItemData['store_scene'];
            }
        }
        $len = mb_strlen($memberInfo['detail_address'],"utf-8");
        if($len > 30){
            $memberInfo['detail_address'] = mb_substr($memberInfo['detail_address'],0,30,"utf-8")."...";
        }
        $this->memberInfo = $memberInfo;
        $this->display('WechatPublic/storeCleanKill/appointmentcleankill');
    }

    /**
     * 订单详情
     * @param $order_id
     * @author pangyongfu
     */
    public function showCleanKillOrder($order_id){

        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        $field = "orde.*,item.store_area,item.store_scene,item.insect_species,item.insect_time,item.malfunction_text,item.after_sale_text,item.difference_price,item.difference_text,item.difference_status,item.old_order_id,item.old_order_code,item.cancel_text,item.difference_status,item.difference_price,member.uid,member.name,member.phone";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        //如果是年订单的主订单则直接跳转到年订单页面
        if(($orderData['order_state'] == 11 || $orderData['order_state'] == 3) && $orderData['is_year'] == 1 && $orderData['is_main'] == 1){
            $this->showCleanKillYearOrder($order_id);
            return true;
        }
        if($orderData['order_state'] == 0 && !empty($orderData['renew_order_id'])){
            $orderData['order_state_text'] = '续签中';
        }elseif($orderData['order_state'] == 0 && !empty($orderData['facilitator_id']) && $orderData['is_year'] && !$orderData['is_main']){
            $orderData['order_state_text'] = "待上门";
        }
        if(($orderData['order_state'] == 10 || $orderData['order_state'] == 1) && !empty($orderData['uid'])){
            $orderData['order_state_text'] = "待上门";
        }
        if($orderData['order_state'] == 1 && !empty($orderData['equipment_id'])){
            $orderData['order_state_text'] = '服务中';
        }
        //订单如果是待支付 拼接微信支付代码
        if($orderData['order_state'] == '2'){

            //检查生成订单支付数据
            $wechatService = new WechatService();
            $renturnData = $wechatService->checkOrderNoPay($orderData);

            $this->p_timeStamp = $renturnData['p_timeStamp'];
            $this->p_nonceStr = $renturnData['p_nonceStr'];
            $this->p_package = $renturnData['p_package'];
            $this->p_paySign = $renturnData['p_paySign'];
            $this->p_signType = $renturnData['p_signType'];
            $this->payUrl = $renturnData['payUrl'];

            $this->moneyPay = $renturnData['moneyPay'];
            $this->orderId = $order_id;
            $this->orderCode = $orderData['order_code'];
            $this->orderState = $orderData['order_state'];
            $this -> getWechatJsSignPackage();
        }
        //防治建议图片数据
        $this->beforeImgData = $this->WechatPublicOrderService->getOrderImg($order_id);
        //消杀报告图片数据
        $this->afterImgData = $this->WechatPublicOrderService->getOrderImg($order_id,1);
        $len = mb_strlen($orderData['detailed_address'],"utf-8");
        if($len > 45){
            $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,45,"utf-8")."...";
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/storeCleanKill/showcleankillorder');
    }

    /**
     * 获取年服务订单详情
     * @param $order_id
     */
    public function showCleanKillYearOrder($order_id){
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        $field = "orde.*,item.store_area,item.malfunction_text,item.store_scene,item.insect_species,item.old_order_id,item.old_order_code,item.after_sale_text,item.phone_solve_text,item.insect_time,item.difference_price,item.difference_text,item.difference_status,item.cancel_text,item.difference_status,item.difference_price,member.uid,member.name,member.phone,faci.id fid,faci.name fname,faci.phone fphone";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        if($orderData['order_state'] == '0' && !empty($orderData['renew_order_id'])){
            $orderData['order_state_text'] = '续签中';
        }
        $this->orderData = $orderData;
        $this->year_order_id = $order_id;
        $this->display('WechatPublic/storeCleanKill/showcleankillyearorder');
    }
//
//    public function getMoney(){
//        $species = I('species');//消杀种类
//        $store_area = I('store_area');//门店面积
//        $servicetype = I('servicetype');//消杀类型（默认为单次）
//        if($servicetype == 1){
//            $servicetype = "Single";
//        }
//        $species = implode(",",$species);
//        switch($species){
//            case "1":
//                $species = "Mouse";
//                break;
//            case "2":
//                $species = "Cockroach";
//                break;
//            case "3":
//                $species = "Insect";
//                break;
//            case "1,2":
//                $species = "CockroachRat";
//                break;
//            default:
//                $species = "Four";
//                break;
//        }
//        $rules = C($servicetype);
//        echo json_encode(array('status'=>1,'money'=>$rules[$species][$store_area]));
//        exit;
//    }
    public function addAdvice($id,$advice){
        $where['order_id'] = $id;
        $data['malfunction_text'] = $advice;
        $this->WechatOrderItemModel->where($where)->save($data);
        $this->ajaxReturn(['status'=>1]);
    }
///////////////////////////////////////////////////////////////////////////门店消杀结束////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////设备清洗开始////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 设备清洗首页（服务须知页）
     * @author  yongFu.
     */
//    public function storeCleaning(){
//
//        $condition['type'] = ['eq',3];//1-设备维修，2-门店消杀，3-设备清洗
//        $condition['status'] = ['eq',1];//-1--删除；1--:启用；2--禁用；
//
//        $guide = $this->WechatServiceGuidelinesModel->where($condition)->find();
//        $this->guide = $guide;
//        $this->type = 3;
//        $this->url = "/Wechat/Index/appointmentCleaning";
//        $this->display('WechatPublic/serviceguide');
//    }

    /**
     * 获取省下面的城市
     * @param string $upid
     * @author pangyongfu
     */
    public function getArea($upid = "110000"){

        $condition['upid'] = ['eq',$upid];
        $city = $this->DistrictModel->where($condition)->select();
        echo json_encode($city);
    }

    /**
     * 预约清洗服务
     * @author pangyongfu
     */
    public function appointmentCleaning($is_ka=0,$companycode="",$storecode="",$add_id=""){
//        $this->province = $this->DistrictModel->where(['id'=>['eq',110000]])->select();
//        $condition['upid'] = ['eq',110000];
//        // TODO 目前地址有限制，只开放海淀区 昌平区
//        $city = $this->DistrictModel->where($condition)->select();
//        $merge_city = [["id"=>0,"name"=>"请选择","level"=>"2","upid"=>"110000"]];
//        $this->city = array_merge($merge_city,$city);
        //获取ka信息
        $this->is_ka = $is_ka;
        $this->companycode = $companycode;
        $this->storecode = $storecode;
        if($is_ka){
            $this->companyStoreData = $this->getCompanyStoreData($companycode,$storecode);
        }
        //获取个人信息
        $memberId = session("memId");
        $memberInfo = [];
        if(!empty($memberId) && !$is_ka){
            $addressModel = new WechatAddressModel();
            if($add_id){
                $memberInfo = $addressModel->getAddressAreaInfo(array('addr.add_id'=>$add_id));
            }else{
                $memberInfo = $addressModel->getAddressAreaInfo(array('addr.member_id'=>$memberId,'addr.is_default'=>1,'addr.status'=>1));
            }
            //如果下过单就默认门店面积和门店场景
            $isHaveOrder = $this->WechatOrderModel->where(array('member_id'=>$memberId))->field('id')->order('id desc')->find();
            if(!empty($isHaveOrder)){
                $orderItemData = $this->WechatOrderItemModel->where(array('order_id'=>$isHaveOrder['id']))->field('store_area,store_scene')->find();
                $this->store_area = $orderItemData['store_area'];
                $this->store_scene = $orderItemData['store_scene'];
            }
        }

        $len = mb_strlen($memberInfo['detail_address'],"utf-8");
        if($len > 30){
            $memberInfo['detail_address'] = mb_substr($memberInfo['detail_address'],0,30,"utf-8")."...";
        }
        $this->memberInfo = $memberInfo;
        $this->display('WechatPublic/storeCleaning/appointmentcleaning');
    }
    /**
     * 展示清洗订单
     * @param $order_id
     * @author pangyongfu
     */
    public function showCleaningOrder($order_id){

        $this->wechatLogin();
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        $field = "orde.*,item.store_area,item.store_scene,item.clean_type,item.last_clean_time,item.cancel_text,item.after_sale_text,item.difference_price,item.difference_text,member.uid,member.name,member.phone";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        //清洗前图片数据
        $this->beforeImgData = $this->WechatPublicOrderService->getOrderImg($order_id);
        //清洗后图片数据
        $this->afterImgData = $this->WechatPublicOrderService->getOrderImg($order_id,1);
        //订单如果是待支付 拼接微信支付代码
        if($orderData['order_state'] == '2'){

            $wechatService = new WechatService();
            $orderCode = $orderData['order_code'];

            $money = $orderData['money_total'];

            //获取微信js签名信息
            $this->getWechatJsSignPackage();

            //获取用户的open_id
            $memId = session("memId");
            $member = (new WechatMemberModel())->where(['uid'=>$memId])->find();
            $openId = $member['open_id'];

            //支付回调地址
            $notifyUrl = host_url.'/index.php/Wechat/Notify/wxPayNotify';

            require_once APP_PATH . "Pay/Library/WxpayAPI/lib/instance/WxPay.JsApiPay.php";

            //1.判断该订单师傅是否已经更改过价格 如果价格相等 取pay表最新的数据
            $orderNewData = $wechatService->getPayDataForOrderCode($orderCode);

            //循环取得第一个就是最新生成的订单号
            $orderGGCode = '';
            $orderNum = '';
            foreach($orderNewData as $val){
                if(strstr($val['trade_no'],"GG") && !strstr($val['trade_no'],"SM")){
                    $orderCode = $val['trade_no'];

                    //获取可编辑的更改价格的订单号
                    $orderGGCode = $val['trade_no'];

                    //获取订单号中前缀自增的数字
                    $orderNum = explode('GG',$val['trade_no'])[0];
                    break;
                }
            }

            //2.如果money_total 与 money_total_old 字段不一致的话,则价格就是已经变更
            if($orderData['money_total'] != $orderData['money_total_old']){

                //2.1 首先去查pay表是否已经有生成的变更价格的订单了,如果有取出在原有的基础上 将订单号头一位自增加一 ，避免重复
                if(!empty($orderGGCode)){
                    $orderNumNew = $orderNum+1;
                    $orderCode = $orderNumNew.'GG'.$orderData['order_code'];
                }else{
                    $orderCode = '1GG'.$orderData['order_code'];
                }

                //3.统一修改价格
                $this->WechatOrderModel->where(['id'=>$order_id])->save(['money_total_old'=>$orderData['money_total']]);
            }

            //调用统一下单，获取支付相关数据
//            $UnifiedOrderResult = $wechatService->unifiedOrder($orderCode,$money,$openId,$notifyUrl);
            $UnifiedOrderResult = $wechatService->unifiedOrder($orderCode,$money,$openId,$notifyUrl,'2');
            $jsApiParameters = (new \JsApiPay())->GetJsApiParameters($UnifiedOrderResult);
            $jsApiParameters = json_decode ( $jsApiParameters, true );

            //扫码支付
            $payUrl = $wechatService->scanCodePay('SM'.$orderCode,$notifyUrl,$orderData['id'],$money);

            //pay数据
            $payModel = new WechatPayModel();
            $pay = $payModel->where(['trade_no'=>$orderCode])->find();
            if(empty($pay)){
                $payData = [
                    'trade_no'=>$orderCode,
                    'type'=>1,//todo 微信支付
                    'money_pay'=>$money,
                    'status'=>1,
                    'status_pay'=>0//待支付
                ];
                $payModel->data($payData)->add();
            }

            $this->assign('p_timeStamp',$jsApiParameters['timeStamp']);
            $this->assign('p_nonceStr',$jsApiParameters['nonceStr']);
            $this->assign('p_package',$jsApiParameters['package']);
            $this->assign('p_paySign',$jsApiParameters['paySign']);
            $this->assign('p_signType',$jsApiParameters['signType']);
            $this->assign('payUrl',$payUrl);

            $this->assign("orderId",$order_id);
            $this->assign("orderCode",$orderCode);
            $this->assign("moneyPay",$orderData['money_pay']);
            $this->assign("orderState",$orderData['order_state']);

        }
        $len = mb_strlen($orderData['detailed_address'],"utf-8");
        if($len > 45){
            $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,45,"utf-8")."...";
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/storeCleaning/showcleaningorder');
    }
///////////////////////////////////////////////////////////////////////////设备清洗结束////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////我的模块开始////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 我的中心
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function myCenter(){

        $this->wechatLogin();

        //获取用户的id
        $memId = session("memId");
        $member = (new WechatMemberModel())->where(['uid'=>$memId])->find();
        $this->memData = $member;

        $this->display('WechatPublic/my/myCenter');

    }

    /**
     * 我的中心
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function editMyCenter(){

        $this->wechatLogin();

        //获取用户的id
        $memId = session("memId");
        $member = (new WechatMemberModel())->where(['uid'=>$memId])->find();
        $this->memData = $member;

        $this->display('WechatPublic/my/editMyCenter');
    }

    /**
     * 添加编辑用户提交的数据
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function addMyData(){
        $userName = I('userName');
        $userPhone = I('userPhone');
        $userStore = I('userStore');
        $userAddress = I('userAddress');
        $userId = I('userId');

        if($userName == '' || $userPhone == '' || $userStore == '' || $userAddress == '' || $userId == ''){
            $this->ajaxReturn(['status' => 0,'msg'=>'添加数据出错,请联系客服！']);
        }

        $data = [
            'uid'=>$userId,
            'name'=>$userName,
            'phone'=>$userPhone,
            'store_name'=>$userStore,
            'address_name'=>$userAddress
        ];

        $return = (new WechatMemberModel())->editMemberData($data);
        if($return){
            $this->ajaxReturn(['status'=>1,'msg'=>'修改信息成功！']);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'修改信息失败，请稍后重试！']);
        }
    }

    /**
     * 我的订单
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function myOrder(){
        $this->wechatLogin();
        $data = $this->WechatPublicOrderService->getOrderInfoByStatus(1);//全部订单
        $this->orderData = $data['data'];
        $this->display('WechatPublic/my/myOrder');
    }

    /**
     * 获取该用户某个状态的订单数据---1：全部 2：未完成 3：已完成 4：已取消-----
     * @author pangyongfu
     */
    public function getOrderInfoByStatus($status){

        $orderData = $this->WechatPublicOrderService->getOrderInfoByStatus($status);
        echo json_encode($orderData);
    }
    /**
     * 客服
     * @param string $type
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function customerServices($type = ''){

        if(!$type){
            $title = '客服电话';
            $csCenter = '客服中心';
            $phone = '客服电话';
        }else{
            $title = '油烟净化器检测安装';
            $csCenter = '联系我们';
            $phone = '服务电话';
        }

        $this->title = $title;
        $this->csCenter = $csCenter;
        $this->phone = $phone;
        $this->display('WechatPublic/serviceTel');
    }

    /**
     * 我的发票列表
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function myInvoice(){
        $this->display('WechatPublic/my/myInvoice');
    }

    /**
     * 未开票的订单列表页
     * @author pangyongfu
     */
    public function askForInvoice(){
        //获取登录用户的未开票订单
        $this->wechatLogin();
        $this->orderData = $this->WechatPublicOrderService->getInvoiceOrder();

        $this->display('WechatPublic/my/askForInvoice');
    }

    /**
     * 申请发票，填写发票信息
     * @author pangyongfu
     */
    public function applyInvoice(){
        $this->total_money = I('total_money');
        $this->order_id = rtrim(I('order_id'),",");

        $this->signpackge = $this->WechatService->getSignPackage();
//        $this->province = $this->DistrictModel->where(['upid'=>['eq',0]])->select();
//        $condition['upid'] = ['eq',110000];
//        $this->city = $this->DistrictModel->where($condition)->select();
        $this->province = $this->DistrictModel->where(['id'=>['eq',110000]])->select();
        $condition['upid'] = ['eq',110000];
        $condition['id'] = ['not in',[110103,110104]];
        // TODO 目前地址有限制，不开放崇文宣武
        $this->city = $this->DistrictModel->where($condition)->select();
        $this->display('WechatPublic/my/applyInvoice');
    }

    /**
     * 创建发票申请
     * @author pangyongfu
     */
    public function createInvoice(){
        $res = $this->WechatPublicOrderService->createInvoice();
        echo json_encode($res);exit;
    }

    public function applyInvoiceSuccess(){
        $this->display('WechatPublic/my/applyInvoiceSuccess');
    }
    /**
     * 发票历史
     * @author pangyongfu
     */
    public function invoiceHistory(){
        $this->wechatLogin();
        $this->ticketData = $this->WechatPublicOrderService->getInvoiceByMemberId();
        $this->display('WechatPublic/my/invoiceHistory');
    }

    /**
     * 发票详情
     * @author pangyongfu
     */
    public function invoiceDetail(){
        list($this->ticketData,$this->orderData) = $this->WechatPublicOrderService->getInvoiceDetail();
        $this->display('WechatPublic/my/invoiceDetail');
    }
    /**
     * 用户注册页面
     */
    public function registLogin(){

        if(IS_POST){

            $userId = session("memId");
            $phone = I('post.phone');
            $codeVal = I('post.codeVal');

            //参数验证
            if(!$phone){
                $this->ajaxReturn(["status" => 0,"msg" => '用户输入手机号为空！']);
            }

            if(!$codeVal){
                $this->ajaxReturn(["status" => 0,"msg" => '用户输入验证码为空！']);
            }

            log::write('用户验证码 | 用户输入码'.session('verifyCode').' | '.$codeVal);
            if($codeVal != session('verifyCode')){
                $this->ajaxReturn(["status" => 0,"msg" => '用户输入验证码有误！']);
            }

            log::write('用户输入手机号 | '.$phone);
            log::write('用户输入ID | '.$userId);
            //将获取用户的手机号绑定到当前用户id绑定

            //存储到表中
            $return = (new WechatMemberModel())->where(['uid'=>$userId])->data(['phone'=>$phone])->save();

            if($return !== false){
                //获取用户来源地址
                $cxHttpReferer = session('cxHttpReferer');
                log::write('用户来源地址为 | '.$cxHttpReferer);

                //TODO 用户注册成功，给用户发送体验推广信息
                $this->WechatService->sendRegistMsg($userId);

                if(empty($cxHttpReferer)){
                    $this->ajaxReturn(["status" => 1,"msg" => '用户注册成功！','url' => host_url.'/index.php?s=/wechat/index/myCenter']);
                }else{
                    $this->ajaxReturn(["status" => 1,"msg" => '用户注册成功！','url' => $cxHttpReferer]);
                }

            }else{
                $this->ajaxReturn(["status" => 0,"msg" => '用户注册有误,请稍后重试！']);
            }
        }else{

            $this->wechatLoginNew();
            $this->display('WechatPublic/my/regist');
        }
    }

    /**
     * 发送用户短信验证码
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendCodeSms(){

        $phone = I('post.phone');

        if(!$phone){

            $this->ajaxReturn(["status" => 0,"msg" => '用户输入手机号为空！']);
        }

        //给用户发送验证码短信
        $re = (new SMSService())->sendBindPhoneSms($phone);

        //验证短信发送结果
        if($re){

            $this->ajaxReturn(["status" => 1,"msg" => "验证码已发送到您的手机，请注意查收！"]);
        }else{

            //短信发送失败异常处理
            $this->ajaxReturn(["status" => 0,"msg" => "验证码发送失败，请稍后重试"]);
        }

    }


///////////////////////////////////////////////////////////////////////////我的模块结束////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////行业雷达开始////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 供需平台显示页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function gxptIndex(){

        //查询行业雷达供需平台页面
        $data = (new WechatVocationRadarModel())->where(['type'=>1,'status'=>1])->find();

        //页面是否显示数据
        if($data){
            $this->assign('isData',true);

            $data['typeNewName'] = '供需平台';
            $this->assign('info',$data);
        }else{

            //跳转至错误页面 TODO
            $this->assign('isData',false);
        }


        $this->display('WechatPublic/my/radarIndex');
    }

    /**
     * 餐饮圈显示页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function canyinIndex(){
        //查询行业雷达供需平台页面
        $data = (new WechatVocationRadarModel())->where(['type'=>2,'status'=>1])->find();

        //页面是否显示数据
        if($data){
            $this->assign('isData',true);

            $data['typeNewName'] = '餐饮圈';
            $this->assign('info',$data);

        }else{

            //跳转至错误页面 TODO
            $this->assign('isData',false);
        }

        $this->display('WechatPublic/my/radarIndex');
    }

///////////////////////////////////////////////////////////////////////////行业雷达结束////////////////////////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////微信支付成功时 修改订单状态////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function wxPaySuccess(){
        //切割补差价的订单code
        $orderCodeOld = I('orderCode');
        $orderCode = $orderCodeOld;

        $orderCodeCj = explode('CJ',$orderCodeOld);
        if($orderCodeCj[1]){
            $orderCode = $orderCodeCj[1];
        };

        $orderCodeSm = explode('SM',$orderCodeOld);
        if($orderCodeSm[1]){
            $orderCode = $orderCodeSm[1];
        };

        $orderCodeGG = explode('GG',$orderCodeOld);
        if($orderCodeGG[1]){
            $orderCode = $orderCodeGG[1];
        };

        //业务逻辑处理
        try{

            //查询订单号对应的订单信息
            $orderModel = new WechatOrderModel();
            $orderData = $orderModel->where(['order_code'=>$orderCode])->find();
            if(empty($orderData)){
                Log::write("订单：".json_encode($orderData,true));
                $this->ajaxReturn(['status'=>0,'msg'=>'订单信息为空,请联系客服']);
            }

            if($orderData['order_state'] == PAY_STATUS_PAY_COMPLETION || $orderData['order_state'] == PAY_STATUS_PAY_SUCCESS){
                Log::write("订单：".json_encode($orderData,true));
                $this->ajaxReturn(['status'=>0,'msg'=>'订单已完成支付！']);
            }

            $orderSaveStatus = '';
            //判断订单分类 TODO order_type 1：设备维修，2：门店消杀，3：设备清洗
            if($orderData['order_type'] == 1){
                $codescrect = COMPANY_MAINTAIN_APPSECRET;
                $orderSaveStatus = PAY_STATUS_PAY_COMPLETION;
            }elseif($orderData['order_type'] == 2){

                //判断如果支付为差价的话 订单状态变更为待验收 反之派单中 TODO 消杀有差价时的逻辑 目前代码逻辑已变更 2018年5月23日16:51:19
//                if($orderCodeCj[1]){
//                    $orderSaveStatus = PAY_STATUS_PAY_WAITACCEPT;
//                    $itemModel = new WechatOrderItemModel();
//                    //修改差价状态为已完成
//                    $itemData['difference_status'] = CJ_PAY_STATUS_PAY_SUCCESS;
//                    $itemModel->where(array('order_id'=>$orderData['id']))->save($itemData);
//                    //差价支付后给消杀师傅发消息
//                    $masterService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CLEANKILL_APPSECRET);
//                    $param = $masterService->getMasterSendMsg($orderData['id'],6);//新工单提醒
//                    $masterService->sendMessage($param);//新工单提醒
//                }else{
//                    $orderSaveStatus = PAY_STATUS_PAY_INSERVICE;
//                }

                $codescrect = COMPANY_CLEANKILL_APPSECRET;
                //1.消杀判断是否为年服务订单
                if($orderData['is_year'] == 1){
                    $orderSaveStatus = PAY_STATUS_PAY_INSERVICE;

                    //判断如果是续签订单 则将续签前的单次订单变更为年订单的第一个子订单
                    if($orderData['renew_order_id'] && $orderData['year_service_id']){
                        (new WechatYearServiceTimeModel())->where(['year_service_id'=>$orderData['year_service_id'],'service_num'=>1])->save(['order_id'=>$orderData['renew_order_id']]);
                        (new WechatOrderModel())->where(['id'=>$orderData['renew_order_id']])->save(['year_service_id'=>$orderData['year_service_id'],'is_main'=>0,'is_year'=>1]);
                    }
                }else{
                    $orderSaveStatus = PAY_STATUS_PAY_SUCCESS;
                }

            }elseif($orderData['order_type'] == 3){
                $codescrect = COMPANY_CLEANING_APPSECRET;
                $orderSaveStatus = PAY_STATUS_PAY_COMPLETION;
            }

            //设置订单的支付状态
            if($orderData['order_state'] == PAY_STATUS_NO_PAY){

                $orderInfoData['id'] = $orderData['id'];
                $orderInfoData['order_state'] = $orderSaveStatus;
                $oResult = $orderModel->editData($orderInfoData);
                if($oResult === false){
                    Log::write("订单状态修改失败：".json_encode($orderInfoData,true));
                    Log::write("订单修改的状态为：".$orderSaveStatus);
                    $this->ajaxReturn(['status'=>0,'msg'=>'修改订单状态失败,请联系客服']);
                }
                //支付完成给师傅发消息提醒
                if($orderData['order_type'] == 1 || $orderData['order_type'] == 3){

                    $masterService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,$codescrect);
                    $param = $masterService->getMasterSendMsg($orderData['id'],7);//新工单提醒
                    $masterService->sendMessage($param);//新工单提醒
                }
                //消杀订单支付完成给客服和主管发消息提醒
                if($orderData['order_type'] == 2){

                    //给客服发送取消订单提醒
                    $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
                    $param = $enterpriseService->getCustomerSendMsg($orderData['id'],11);//订单支付提醒
                    $enterpriseService->sendMessage($param);//订单支付提醒
                    //给分配主管发取消订单消息提醒
                    $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
                    $param = $distributeService->getDistributeSendMsg($orderData['id'],6);//订单支付提醒
                    $distributeService->sendMessage($param);//订单支付提醒
                }
                //添加支付记录  TODO 放到异步回调
//                $payModel = new WechatPayModel();
//                $pay = $payModel->where(['trade_no'=>$orderCodeOld])->find();
//                if(!empty($pay)){
//                    $payData = [
//                        'trade_no'=>$orderCode,
//                        'status_pay'=>1//已支付
//                    ];
//
//                    $payModel->where(['trade_no'=>$orderCode])->data($payData)->save();
//                }

                //支付完成后给用户发送消息提醒
                $wechatService = new WechatService();
                //1：设备维修，2：门店消杀，3：设备清洗
                if($orderData['order_type'] == 1 || $orderData['order_type'] == 2 || $orderData['order_type'] == 3){

                    //判断消杀的订单支付前后顺序 $orderCodeCj不为空 则表示第二次支付
                    if($orderData['order_type'] == 2 && !empty($orderCodeCj[1])){
                        $wechatService->sendOrderPaySuccessMsg($orderData['id'],'storeCleanKillCj');
                    }elseif($orderData['order_type'] == 2 && empty($orderCodeCj[1])){
                        $wechatService->sendOrderPaySuccessMsg($orderData['id'],'storeCleanKill');
                    }

                    //设备维修
                    if($orderData['order_type'] == 1){
                        $wechatService->sendOrderPaySuccessMsg($orderData['id'],'storeMaintain');
                    }

                    //设备清洗
                    if($orderData['order_type'] == 3){
                        $wechatService->sendOrderPaySuccessMsg($orderData['id'],'storeCleaning');
                    }
                }

                $this->ajaxReturn(['status'=>1,'msg'=>'成功']);
            }

        }catch (\Exception $e){
            Log::write("错误信息：".$e->getMessage()."\r\n".$e);
            $this->ajaxReturn(['status'=>0,'msg'=>'订单出错,请联系客服']);

        }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 设置检查订单的支付状态
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function checkOrderStatus(){

        //1.接收订单号
        $orderCode = I('orderCode');

        //2.查询订单信息
        $orderData = (new WechatOrderModel())->where(['order_code'=>$orderCode])->find();

        //3.如果订单已完成 || 已支付 则ajax返回状态 1
        if($orderData['order_state'] == 3 || $orderData['order_state'] == 8 || $orderData['order_state'] == 11){

            $this->ajaxReturn(['status'=>1,'msg'=>'订单已支付！']);
        }

        //判断如果订单价格变更
        if($orderData['money_total'] != $orderData['money_total_old']){

            $this->ajaxReturn(['status'=>2,'msg'=>'订单价格已变更,即将刷新页面！']);
        }
    }

    /**
     * 体验服务
     * @author pangyongfu
     */
    public function activitiesPust(){

        $this->wechatLoginNew();
        $this->signpackge = $this->WechatService->getSignPackage();
        $this->province = $this->DistrictModel->where(['id'=>['eq',110000]])->select();
        $condition['upid'] = ['eq',110000];
        // TODO 目前地址有限制，只开放海淀区 昌平区
        $city = $this->DistrictModel->where($condition)->select();
        $merge_city = [["id"=>0,"name"=>"请选择","level"=>"2","upid"=>"110000"]];
        $this->city = array_merge($merge_city,$city);
        //获取个人信息
        $memberId = session("memId");
        $this->store_name = "";
        $this->address = "";
        $this->link_person = "";
        $this->link_phone = "";
        if(!empty($memberId)){
            $memberInfo = $this->WechatMemberModel->where(array('uid'=>$memberId))->find();
            $this->store_name = $memberInfo['store_name'];
            $this->address = $memberInfo['address_name'];
            $this->link_person = $memberInfo['name'];
            $this->link_phone = $memberInfo['phone'];
        }
        $this->display('WechatPublic/activitiesPust');
    }

    /**
     * 创建体验申请
     * @author pangyongfu
     */
    public function activitesOrder(){
        //验证是否已注册,没有注册过就注册
        $memberId = session("memId");
        $phone = I('post.tel_phone');
        $codeVal = I('post.codeVal');
        //验证验证码
        if($codeVal != session('verifyCode')){
            $this->ajaxReturn(["status" => 0,"msg" => '用户输入验证码有误！']);
        }
        $memberInfo = $this->WechatMemberModel->where(array('uid' => $memberId))->find();
        //验证是否已申请过
        $activeModel = M("wechat_activity");
        $activeInfo = $activeModel->where(array('uid'=>$memberId))->find();

        if(!empty($activeInfo)){
            $this->ajaxReturn(['status'=>0,'msg'=>'您已申请，不能再次申请！']);
        }
        //存储到表中
        if(empty($memberInfo['phone'])){
            $return = (new WechatMemberModel())->where(['uid'=>$memberId])->data(['phone'=>$phone])->save();
            if($return !== false){
                //TODO 用户注册成功，给用户发送体验推广信息
                $this->WechatService->sendRegistMsg($memberId);
            }else{
                $this->ajaxReturn(["status" => 0,"msg" => '用户注册信息有误,请稍后重试！']);
            }
        }
        //更新个人中心信息
        if(!empty($memberId)) {
            $memberInfo = $this->WechatMemberModel->where(array('uid' => $memberId))->find();
            if (empty($memberInfo['store_name']) && empty($memberInfo['address_name'])) {
                $memberData['uid'] = $memberId;
                $memberData['store_name'] = I("store_name");
                $memberData['address_name'] = I("store_address");
                $memberData['name'] = I("link_person");
                $this->WechatMemberModel->save($memberData);
            }
        }
        //创建体验申请
        $data['uid'] = $memberId;
        $data['store_name'] = I('store_name');
        $data['store_address'] = I('store_address');
        $data['province_id'] = I('province');
        $data['city_id'] = I('city');
        $data['link_person'] = I('link_person');
        $data['link_phone'] = I('link_phone')?I('link_phone'):I('tel_phone');
        $data['create_time'] = $data['update_time'] = date("Y-m-d H:i:s");
        $res = $activeModel->add($data);
        if($res){
            //获取用户来源地址
            $cxHttpReferer = session('cxHttpReferer');
            if(empty($cxHttpReferer)){
                $this->ajaxReturn(["status" => 1,"msg" => '申请成功！','url' => 'http://opensns.lz517.com/index.php?s=/wechat/index/myCenter']);
            }else{
                $this->ajaxReturn(["status" => 1,"msg" => '申请成功！','url' => $cxHttpReferer]);
            }
        }
	$this->ajaxReturn(['status'=>0,'msg'=>'体验申请提交失败，请稍后重试！']);
    }
/////////////////////////////////////////////////个体用户和企业用户 开始///////////////////////////////////////////////////////////////
    /**
     *选择用户类型
     */
    public function selectUserType(){
        //验证用户是否注册
        $this->wechatLogin();
        $this->door_type = I('door_type') ? I('door_type') : 1;
        $this->display('WechatPublic/selectUserType');
    }
    /**
     *输入企业编码和门店编码
     */
    public function inputCompanyStoreCode(){
        $this->door_type = I('door_type') ? I('door_type') : 1;

        //从缓存中取出填写正确的 KA账号密码
        $openId = session("openId");
        $company_store_code_correct = S($openId."_company_store_code_correct");
        if($company_store_code_correct){
            $code = explode('_',$company_store_code_correct);
            $this->company_code = $code[0];
            $this->store_code = $code[1];
        }

        $this->display('WechatPublic/inputCompanyStoreCode');
    }
    /**
     * 检验企业编号和门店编号
     */
    public function checkCompanyAndStoreCode(){
        $company_code = I('company_code');
        $store_code = I('store_code');

        //根据企业编号获取企业信息
        $companyData = $this->WechatKaEnterpriseModel->getData(['code'=>$company_code]);
        if(empty($companyData)){
            $this->ajaxReturn(['status'=>0,'msg'=>'企业编号不存在']);
        }
        //根据门店编号获取门店信息
        $fileds = "store.name storename,store.code storecode,store.province,store.city,store.stores_address,ent.code companycode,ent.name companyname";
        $storeData = $this->WechatKaStoresModel->getData(['store.code'=>$store_code,'store.enterprise_id'=>$companyData[0]['id']],$fileds);
        if(empty($storeData)){
            $this->ajaxReturn(['status'=>0,'msg'=>'门店编号不存在']);
        }
        session("company_".$company_code."_store_".$store_code,$storeData);

        //往缓存中添加
        $openId = session("openId");
        S($openId."_company_store_code_correct",$company_code.'_'.$store_code);
		$this->ajaxReturn(['status'=>1,'msg'=>'验证成功']);
    }

    /**
     * 获取企业和门店信息
     * @param $company_code
     * @param $store_code
     * @return mixed
     */
    private function getCompanyStoreData($company_code,$store_code){
//        $data = session("company_".$company_code."_store_".$store_code);
        if(empty($data)){
            //根据企业编号获取企业信息
            $companyData = $this->WechatKaEnterpriseModel->getData(['code'=>$company_code]);
            //根据门店编号获取门店信息
            $fileds = "store.link_person,store.link_phone,store.id storeId,store.name storename,store.code storecode,store.province,store.city,store.stores_address,ent.code companycode,ent.name companyname";
            $data = $this->WechatKaStoresModel->getData(['store.code'=>$store_code,'store.enterprise_id'=>$companyData[0]['id']],$fileds);
//            session("company_".$company_code."_store_".$store_code,$data);
        }
        //获取地址信息
        $districtData = $this->WechatPublicOrderService->getDistrictData($data[0]['province'],$data[0]['city']);
        //拼接订单信息内容
        if($districtData){
            $orderData['stores_address'] = $districtData[0].' '.$districtData[1].' '.$data['stores_address'];
        }
        return $data;
    }
/////////////////////////////////////////////////个体用户和企业用户 结束///////////////////////////////////////////////////////////////
    /**
     * 用户类别说明
     */
    public function userClassDescription(){

        $this->display('WechatPublic/userClassDescription');
    }

    //显示微信分享的文章
    public function sharePaper(){

        //文章分享的标识
        $shareArticleId = I('shareArticleId');

        if(!$shareArticleId || !is_numeric($shareArticleId)){
            throw_exception("服务器出小差啦....");
        }

//        file_put_contents("huangqing.log",'进入方法One'."\r\n",FILE_APPEND);
        $wechatService = new WechatService();
        if (empty($_REQUEST['code']))
        {
            $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $url = $wechatService->getSNSOauthUrl($url);
            header("Location:" . $url);
            die;
        }

        $accessTokenData = $wechatService->getSNSAccessToken($_GET["code"]);
        $accessTokenData = json_decode($accessTokenData, true);

//        file_put_contents("huangqing.log",'获取到的AccessToken为：'.json_encode($accessTokenData)."\r\n",FILE_APPEND);

        $openId = $accessTokenData['openid'];
        if(!empty($openId)){
            session("openId",$openId);
        }
//        file_put_contents("huangqing.log",'获取到的OpenId为：'.$openId."\r\n",FILE_APPEND);

        if($accessTokenData['errcode'] && session("openId")){
            $openId = session("openId");
        }
//        file_put_contents("huangqing.log",'获取到的openId数据：'.$openId."\r\n",FILE_APPEND);

        //1.通过openId查询用户信息
        $mem = (new WechatService())->getUserInfo($openId);

        $isFocus = 0;
        //拼接用户是否没有注册
        if(isset($mem['subscribe'])){
            switch($mem['subscribe']){
                case 1:
                    $isFocus = 1;
                    break;
                case 0:
                    $isFocus = 2;
                    break;
            }
        }

        //查询分享文章数据
        $WechatShareArticleModel = (new WechatShareArticleModel());
        $shareArticleInfo = $WechatShareArticleModel->getOnceInfo(['id'=>$shareArticleId,'status'=>1]);

        if(!$shareArticleInfo){
            throw_exception("您访问的文章已禁用,感谢您的访问...");
        }

        $redis = getRedisClient();
        $redis->set('canxun:wechatSharePaper:shareArticleId_'.$openId.'',$shareArticleId);

        //file_put_contents("huangqing.log",'存放到redis中的微信文章标识：'.$redis->get('canxun:wechatSharePaper:shareArticleId_'.$openId.'')."\r\n",FILE_APPEND);

        //增加阅读量
        $WechatShareArticleModel->editData(['id'=>$shareArticleId,'PViews'=>$shareArticleInfo['PViews']+1]);

        //判断内容中是否包含了@从这里开始分割展示全文@
        $temp = '<p style="margin-top: 23px; margin-bottom: 20px; white-space: normal; padding: 0px; color: rgb(51, 51, 51); font-family: &quot;PingFang SC&quot;, &quot;Lantinghei SC&quot;, &quot;Microsoft YaHei&quot;, &quot;HanHei SC&quot;, &quot;Helvetica Neue&quot;, &quot;Open Sans&quot;, Arial, &quot;Hiragino Sans GB&quot;, 寰蒋闆呴粦, STHeiti, &quot;WenQuanYi Micro Hei&quot;, SimSun, sans-serif; font-size: 16px; text-align: justify; line-height: 1.5em;"><span style="font-family: 微软雅黑, &quot;Microsoft YaHei&quot;;color: blue;">@从这里开始分割展示全文@</span></p>';
        $number = strpos($shareArticleInfo['content'], $temp);
        $shareArticleHalfText = substr($shareArticleInfo['content'],0,$number);

        //第一种情况是运营人员填写了 设置了分割全文的的文字 @从这里开始分割展示全文@ 如果有的话就切割数据 添加按钮
        $shareArticleCompleteText = '';
        if(!empty($shareArticleHalfText)){

            //判断如果用户已关注了公众号就直接展示全文
            if($isFocus == 1){
                $shareArticleHalfText = str_replace($temp,' ',$shareArticleInfo['content']);
                $shareArticleCompleteText = '';
            }else{
                $shareArticleHalfText = $shareArticleHalfText.'<div class="readall_box"><div class="read_more_mask"></div><a class="seeMoreBtn" href="javascript:;" onclick="clik()" id="showAllBtn">查看全部</a></div>';
                $shareArticleCompleteText = str_replace($temp,' ',$shareArticleInfo['content']);
            }
        }else{

            //第二种情况是直接把所有内容展示出来，不包含分割全文的功能
            $shareArticleHalfText = $shareArticleInfo['content'];
        }

        //微信分享的相关信息
        $wxjssdk = new JSSDK();
        $signPackage = $wxjssdk->getSignPackageCeShi();

        //创建时间转换
        $shareArticleInfo['create_time'] = Date('Y-m-d',strtotime($shareArticleInfo['create_time']));

        //获取分享的封面图片
        $Picture = (new PictureModel())->field('path')->where(['id'=>$shareArticleInfo['cover_img_id']])->find();

        $this->assign("shareArticleInfo",$shareArticleInfo);
        $this->assign("shareArticleNewText",$shareArticleHalfText);
        $this->assign("shareArticleCompleteText",$shareArticleCompleteText);
        $this->assign("signPackage",$signPackage);
        $this->assign("shareImg",host_url.$Picture['path']);

        $this->display('Index/tmpl/wxSharePaper');
    }

    //显示微信分享的文章
    public function sharePaperTwo(){
        $wechatService = new WechatService();
        if (empty($_REQUEST['code']))
        {
            $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $url = $wechatService->getSNSOauthUrl($url);
            header("Location:" . $url);
            die;
        }

        Log::write("获取Code的数据是：".$_GET["code"]);

        $accessTokenData = $wechatService->getSNSAccessToken($_GET["code"]);
        $accessTokenData = json_decode($accessTokenData, true);

        Log::write("获取accessTokenData的数据是：".json_encode($accessTokenData));

        $openId = $accessTokenData['openid'];
        if($accessTokenData['errcode'] && session("openId")){
            $openId = session("openId");
        }
        //1.通过openId查询是否已存在该用户
        $mem = (new WechatMemberModel())->where(['open_id'=>$openId])->find();
        Log::write('根据OpenId查询出的member是'.json_encode($mem));

        //拼接用户是否没有注册
        $isFocus = 1;
        if(!$mem){
            $isFocus = 2;
        }

        //微信分享的相关信息
        $wxjssdk = new JSSDK();
        $signPackage = $wxjssdk->getSignPackageCeShi();

        Log::write('signPackage记录日志 | '.json_encode($signPackage));

        $this->assign("isFocus",$isFocus);
        $this->assign("openId",$openId);
        $this->assign("signPackage",$signPackage);
        $this->assign("shareImg","http://opensns.lz517.com/share2.png");

        $this->display('Index/tmpl/sharePaperTwo');
    }

    /**
     * 判断用户是否关注了公众号
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function isUserByOpenId(){
        $openId = session("openId");

        file_put_contents("huangqing.log",'获取到的openId数据：'.$openId."\r\n",FILE_APPEND);
        //1.通过openId查询用户信息
        $mem = (new WechatService())->getUserInfo($openId);

        $isFocus = 0;
        //拼接用户是否没有注册
        if(isset($mem['subscribe'])){
            switch($mem['subscribe']){
                case 1:
                    $isFocus = 1;
                    break;
                case 0:
                    $isFocus = 2;
                    break;
            }
        }
        //拼接用户是否已关注
        $this->ajaxReturn(['status'=>$isFocus]);
    }

    /**
     * 续签年服务
     * @param $order_id
     */
    public function renewCleanKillOrder($order_id){
        //验证改用户是否有未处理完毕的续签订单或年服务订单
        $map['member_id'] = session("memId");//测试阶段先写死
//        $map['member_id'] = 2;//测试阶段先写死
        $map['order_state'] = 2;//待支付
        $waitPayOrder = $this->WechatOrderModel->where($map)->find();
        if(!empty($waitPayOrder)){
            echo json_encode(['status'=>0,'msg'=>"您有待支付订单，请先支付后再预约新的服务！"]);
            exit;
        }
        $condition['member_id'] = session("memId");//测试阶段先写死
        $condition['order_type'] = 2;
        $condition['renew_order_id'] = ['neq',""];
        $condition['is_main'] = 1;
        $condition['order_state'] = ['not in',[3,4,5,7,12]];
        $notFinishOrder = $this->WechatOrderModel->where($condition)->find();
        if(!empty($notFinishOrder)){
            echo json_encode(['status'=>0,'msg'=>"您有未处理完毕的续签订单，请耐心等待！"]);
            exit;
        }
        //重新生成订单，把该订单的订单号信息绑定到续签订单中
        $res = $this->WechatPublicOrderService ->renewCleanKillOrder($order_id);
        echo json_encode($res);
        exit;
    }


    //=========================================企业订单确定页面BEGIN=======================================

    public function storeInspection(){

        $id = I('inspection_store_child_id'); //1;
        $map = ['inspection_store_child_id'=>$id];
        $field = "";
        $list = (new WechatInspectionStoreChildModel())->serviceOneDetailList($map, '', $field);
        $memberInfo = (new WechatMemberModel())->getOnceInfo(['uid'=>$list['inspector_id']],'');
        $storeInfo = (new WechatKaStoresModel())->getOnceInfo(['id'=>$list['store_id']],'');
        $facilitatorInfo = (new WechatFacilitatorModel())->getOnceInfo(['id'=>$list['facilitator_id']],'');
        $childDeviceInfo = (new WechatInspectionStoreChildDeviceModel())->getInspectionStoreChildDeviceByInspectionStoreChildId(['inspection_store_child_id'=>$list['inspection_store_child_id']],'');
        if($childDeviceInfo){
            foreach($childDeviceInfo as $k=>$v){
                $list['money'] += $v['moeny'];
            }
        }
        $img = (new SomethingPicModel())->getList(['app'=>'INSPECTION','row_id'=>$list['inspection_store_child_id']]);
        foreach($img as $k=>$v){
            $imgInfo[] = M("picture")->where(['id'=>$v['pic_id']])->find();
        }

        //判断是否添加了其他设备
        $wechatDeviceInfo = (new WechatDeviceModel())->where(['inspection_store_child_id'=>$id])->find();

        // （1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成）
        if($list['status'] == 1){
            $list['status_text'] = '服务商未派单';
        }elseif($list['status'] == 2){
            $list['status_text'] = '服务商已派单';
        }elseif($list['status'] == 3){
            $list['status_text'] = '巡检员已接单';
        }elseif($list['status'] == 4){
            $list['status_text'] = '开始巡检';
        }elseif($list['status'] == 5){
            $list['status_text'] = '完成巡检';
        }elseif($list['status'] == 6){
            $list['status_text'] = '门店确认完成';
        }
        $list['store_name'] = $storeInfo['name'];
        $list['facilitator_name'] = $facilitatorInfo['name'];
        $list['user_name'] = $memberInfo['name'];
        $list['user_phone'] = $memberInfo['phone'];
        $list['id'] = $list['inspection_store_child_id'];
        $this->assign("wechatDeviceInfo",$wechatDeviceInfo ? 1:0);
        $this->assign("list",$list);
        $this->assign("imgInfo",$imgInfo);
        $this->assign("store_id",json_encode($list['store_id']));
        $this->assign("inspection_store_child_id",json_encode($list['inspection_store_child_id']));
        $this->display('WechatPublic/storeInspection/storeInspection');
    }


    /**
     * 获取设备信息
     */
    public function getDevice(){
        $id = $_REQUEST['id'];
        $inspection_store_child_id = $_REQUEST['inspection_store_child_id'];
        $name = $_REQUEST['name'];

        //查看当前门店、过滤掉当前巡检标识（新提交的设备不显示出来）
        $map = ['store_id'=>$id,'inspection_store_child_id'=>['NEQ',$inspection_store_child_id],'status'=>1,'device_name'=>array('like','%'.$name.'%')];
        $field = "";
        $list = (new WechatDeviceModel())->getList($map, '', $field);

        if($list){
            foreach($list as $key=>$val) {
                $storeChildDeviceInfo = (new WechatInspectionStoreChildDeviceModel())->getInfoByMap(['inspection_store_child_id'=>$inspection_store_child_id,'device_id'=>$val['id']]);

                $list[$key]['yet_repairs_id'] = $storeChildDeviceInfo['yet_repairs_id'];
                $list[$key]['inspection_operate'] = empty($storeChildDeviceInfo['inspection_operate']) ? 0 : 1;
                $list[$key]['is_fix'] = empty($storeChildDeviceInfo['yet_repairs_id']) ? 0 : 1;
                $list[$key]['is_repairs'] = empty($storeChildDeviceInfo['repairs_id']) ? 0 : 1;
                $list[$key]['inspection_child_device_id'] = $storeChildDeviceInfo['inspection_child_device_id'];
                $list[$key]['repairs_id'] = $storeChildDeviceInfo['repairs_id'];

            }
        }
        $this->ajaxReturn(['status'=>1,'data'=>$list]);
    }

    /**
     * 显示订单详情
     * @return string
     */
    public function OrderDetail(){
        $id = $_REQUEST['id'];
        $data = [];
        $storeChildDeviceInfo = (new WechatInspectionStoreChildDeviceModel())->getInfoByMap(['yet_repairs_id'=>$id]);
        if($storeChildDeviceInfo){
            $orderInfo = (new WechatOrderModel())->getData($storeChildDeviceInfo['yet_repairs_id']);
            $orderItemInfo = (new WechatOrderItemModel())->getDataByOrderId($orderInfo['id']);
            $img = (new SomethingPicModel())->getList(['app'=>'WECHAT','row_id'=>$orderInfo['id']]);
            foreach($img as $k=>$v){
                $imgInfo[] = M("picture")->where(['id'=>$v['pic_id']])->find();

            }
            $map = ['inspection_store_child_id'=>$storeChildDeviceInfo['inspection_store_child_id']];
            $field = "";
            $list = (new WechatInspectionStoreChildModel())->serviceOneDetailList($map, '', $field);
            $memberInfo = (new WechatMemberModel())->getOnceInfo(['uid'=>$list['inspector_id']],'');
            $deviceInfo = (new WechatDeviceModel())->getData($storeChildDeviceInfo['device_id']);
            $data['order_code'] = $orderInfo['order_code'];
            $data['store_name'] = $orderInfo['store_name'];
            $data['name'] = $memberInfo['name'];
            $data['device_name'] = $deviceInfo['device_name'];
            $data['malfunction_text'] = $orderItemInfo['malfunction_text'];
            $data['is_maintain'] = empty($orderItemInfo['is_maintain']) ? '否' : '是';
            $data['is_change_parts'] = empty($orderItemInfo['is_change_parts']) ? '否' : '是';
            $data['change_parts_text'] = $orderItemInfo['change_parts_text'];
            $data['door_price'] = $orderInfo['door_price'];
            $data['parts_price'] = $orderItemInfo['parts_price'];
            $data['service_price'] = $orderInfo['service_price'];
        }

        $this->assign("data",empty($data) ? [] : $data);
        $this->assign("img",empty($imgInfo) ? [] : $imgInfo);
        $this->display('WechatPublic/storeInspection/orderDetail');
    }

    /**
     * 操作详情页面 （客服端 企业主管端 维修主管端 师傅端 客户端 公用的一个方法）
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function operation(){
        $id = I('id');
        $data = [];
        $operation = '';
        //获取巡检设备信息
        $storeChildDeviceInfo = (new WechatInspectionStoreChildDeviceModel())->getInfoByMap(['inspection_child_device_id'=>$id]);

        if($storeChildDeviceInfo){

            //获取巡检子订单详情
            $orderInfo = (new WechatInspectionStoreChildModel())
                ->serviceOneDetailList([
                    'inspection_store_child_id'=>$storeChildDeviceInfo['inspection_store_child_id']
                ],'','child_order_code');

            //获取设备信息
            $deviceInfo = (new WechatDeviceModel())->getData($storeChildDeviceInfo['device_id']);

            $data['order_code'] = $orderInfo['child_order_code'];
            $data['device_name'] = $deviceInfo['device_name'];
            $data['device_code'] = $deviceInfo['device_code'];
            $data['device_model'] = $deviceInfo['device_model'];
            $data['brand'] = $deviceInfo['brand'];
            $data['repairs_id'] = $storeChildDeviceInfo['repairs_id'];
            $data['yet_repairs_id'] = $storeChildDeviceInfo['yet_repairs_id'];
            $data['remark'] = $storeChildDeviceInfo['remark'];
//            $inspection_operate = explode(",", $storeChildDeviceInfo['inspection_operate']);

            $operateText = '';
            //通过巡检操作标识，查询操作名称
            if($storeChildDeviceInfo['inspection_operate']){

                $WechatEquipmentCategoryOperation = M('WechatEquipmentCategoryOperation');
                $operateInfo = $WechatEquipmentCategoryOperation
                    ->where([
                        'id'=>['IN',$storeChildDeviceInfo['inspection_operate'],
                        'status'=>1
                        ]])
                    ->field('name')
                    ->select();

                foreach($operateInfo as $key => $value){

                    $operateText .= $value['name'].'<br>';
                }
                $operateText = rtrim($operateText,'<br>');
            }

            //查询图片
            $imgFrontData = [];
            $imgBackData = [];
            if(!empty($id) && $id != 'null'){

                //查询操作巡检前图片
                $SomethingPicModel = (new SomethingPicModel());
                $frontWhere = [
                    'sm.app'=>'INSPECTIONOPERATIONFRONT',
                    'sm.row_id'=>$id,
                ];
                $imgFrontData = $SomethingPicModel->getImgData($frontWhere,'pic.path');

                //查询操作巡检后图片
                $backWhere = [
                    'sm.app'=>'INSPECTIONOPERATIONBACK',
                    'sm.row_id'=>$id,
                ];
                $imgBackData = $SomethingPicModel->getImgData($backWhere,'pic.path');
            }

            //订单报修 已修按钮
            if(!empty($storeChildDeviceInfo['repairs_id'])){

                $resultText = '故障已报修';
                $resultButtonText = '报修详情';
            }elseif(!empty($storeChildDeviceInfo['yet_repairs_id'])){

                $resultText = '故障已修复';
                $resultButtonText = '已修详情';
            }else{
                $resultText = '正常';
                $resultButtonText = NULL;
            }

            $this->assign('resultText',$resultText);
            $this->assign('resultButtonText',$resultButtonText);
            $this->assign("operateText",!empty($operateText) ? $operateText : '无');
            $this->assign("imgFrontData",$imgFrontData);
            $this->assign("imgBackData",$imgBackData);
            $data['operation'] = substr($operation,0,strlen($operation)-1);
        }
        $this->assign("data",empty($data) ? [] : $data);
        $this->assign("remark",empty($data['remark']) ? '无' : $data['remark']);
        $this->display('WechatPublic/storeInspection/operation');
    }
    /**
     * 确认完成修改订单状态
     */
    public function saveStatus(){
        $id = $_REQUEST['ids'];
        if(empty($id)){
            $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
        }
        $list = (new WechatInspectionStoreChildModel())->saveInfo(['inspection_store_child_id'=>$id,'status'=>6]);

        if($list){

            $info = (new WechatInspectionStoreChildModel())->getDataInfo(['inspection_store_child_id'=>$id]);

//            $storeInfo = (new WechatKaStoresModel())->getOnceInfo(['id'=>$info['store_id']],'');
//            $memberInfo = (new WechatMemberModel())->getInfoByMap(['uid'=>$list['inspection_supervisor_id']]);
            //修改剩余次数
            $res = (new WechatInspectionModel())->changeOrderInfo($info['inspection_store_id']);

            if($res && $list){
                $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
                $param = $enterpriseService->getDistributeInsSendMsg($id,6,false);//新工单提醒
                $enterpriseService->sendMessage($param);//新工单提醒
            }
            $this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
        }

    }

    /**
     * 查看明细
     */
    public function deviceDetail(){
        $data = [];
        $id = $_REQUEST['id'];
        $childDeviceInfo = (new WechatInspectionStoreChildDeviceModel())
            ->getInspectionStoreChildDeviceByInspectionStoreChildId([
                'inspection_store_child_id'=>$id,
                'yet_repairs_id'=>array('GT', 0)
            ],'');

        $totalMoney = 0;
        if($childDeviceInfo){
            foreach ($childDeviceInfo as $key => $val) {
                $deviceInfo = (new WechatDeviceModel())->info($val['device_id'],'device_code,device_name');
                $orderItemInfo = (new WechatOrderItemModel())->getDataByOrderId($val['yet_repairs_id']);
                $data[$key]['yet_repairs_id'] = $val['yet_repairs_id'];
                $data[$key]['device_name'] = $deviceInfo['device_name'];
                $data[$key]['device_code'] = $deviceInfo['device_code'];
                $data[$key]['change_parts_text'] = $orderItemInfo['change_parts_text'];
                $data[$key]['money'] = $val['moeny'];
                $totalMoney += $val['moeny'];
            }
        }
        $this->assign('list',$data);
        $this->assign('totalMoney',$totalMoney);
        $this->display("WechatPublic/storeInspection/deviceDetail");
    }


    /**
     * 显示巡检的保修详情页面
     * @param $order_id
     * @author pangyongfu
     */
    public function showStoreRepaireOrderForInspection($id){
        //拼凑条件
        $map['orde.id'] = ['eq',$id];
        //订单数据
        $field = "orde.*,item.equipment_name,item.brands_text,item.malfunction_text,item.after_sale_text,item.cancel_text,item.change_parts_text,item.parts_price,item.is_maintain,item.is_change_parts,item.urgent_level,member.uid,member.name,member.phone";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        log::write('维修订单取出的数据为 | '.json_encode($orderData));
        if(empty($orderData['change_parts_text'])){
            $orderData['change_parts_text'] = "无";
        }

        //图片数据
        $imgData = $this->WechatPublicOrderService->getOrderImg($id);

        $this->orderData = $orderData;
        $this->imgData = $imgData;
        $this->display('WechatPublic/storeInspection/repaireorderforinspection');
    }
    //=========================================企业订单确定页面END=======================================


    //=========================================门店端巡检列表BEGIN========================================

    public function showChildList(){
//        $uid = 8;
        $uid = session("memId");
        if(empty($uid)){
            $this->wechatLogin();
        }
        $start_time = !empty(I('start_time')) ? I('start_time') : '';
        $end_time = !empty(I('end_time')) ? I('end_time') : '';
        //获取巡检订单门店子订单列表
        if($start_time && $end_time){
            $map["wisc.create_time"] = ["between",[$start_time." 00:00:00",$end_time." 23:59:59"]];
        }elseif($start_time){
            $map["wisc.create_time"] = ["egt",$start_time." 00:00:00"];
        }elseif($end_time){
            $map["wisc.create_time"] = ["elt",$end_time." 23:59:59"];
        }
        //获取当前用户所有门店
        $newStoreId = '';
        $storeId = (new WechatMemberStoreModel())->getMemberStoreInfoByWhere(['member.uid'=>$uid,'status'=>1],'userStore.store_id');
        foreach($storeId as $k=>$v){
            $newStoreId .= $v['store_id'].',';
        }
        $map["wisc.store_id"] = ['IN',substr($newStoreId,0,strlen($newStoreId)-1)];
        $field = "wisc.inspection_store_child_id,wisc.child_order_code,wisc.status,wisc.service_num,wisc.create_time,store.name store_name";
        $wechatInspectionStoreChildModel = (new WechatInspectionStoreChildModel());
        $childList = $wechatInspectionStoreChildModel->getChildList($map,$field,"wisc.create_time desc");
        foreach($childList as &$child){
            //订单状态（1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成）
            switch($child["status"]){
                case 1:
                    $child["status_text"] = "未派单";
                    break;
                case 2:
                    $child["status_text"] = "未接单";
                    break;
                case 3:
                    $child["status_text"] = "已接单";
                    break;
                case 4:
                    $child["status_text"] = "巡检中";
                    break;
                case 5:
                    $child["status_text"] = "待确认";
                    break;
                case 6:
                    $child["status_text"] = "已完成";
                    break;
            }
            $child["create_time"] = substr($child["create_time"],0,10);
        }
        $this->childList = $childList;
        $this->startTime = I('start_time');
        $this->endTime = I('end_time');
        $this->display('WechatPublic/storeInspection/showMainOrderStoreList');
    }

    /**
     * 巡检主管查看子订单详情页面
     */
    public function inspectionChildOrderDetail(){

        $inspection_store_child_id = I('inspection_store_child_id');
        //获取当前巡检主管数据
        $childInfo = (new InspectionService())->getChildOrderInfoForChildId($inspection_store_child_id);
        $childInfo['status_text'] = C('INSPECTION_CHILDSTATUS')[$childInfo['status']];
//        $childInfo['isNoMaster'] = (empty($childInfo['storePerson']) && empty($childInfo['mentorPhone'])) ? 0 : 1;
        $childInfo['isNoMaster'] = empty($childInfo['mentorPhone']) ? 0 : 1;

        //处理巡检备注页面 图片数据
        $this->requirementsStatus = $childInfo['requirements_text'] ? 1 : 0;
        //查询是否存在巡检备注图片数据
        if($childInfo['requirements_text']){

            //查询巡检备注图片
            $imgData = (new WechatPublicOrderService())->getInspectionRequirementsImg($inspection_store_child_id);
            if($imgData){
                //截取拼接图片链接
                foreach($imgData as &$value){
                    $value['path'] = host_url.strstr($value['path'],'/');
                }
            }
            $this->pic = $imgData;
            $this->picNum = count($imgData) ? count($imgData) : 0;
        }

        //提示页面数据来源
        $this -> assign('isSource','supervisor');
        $this -> assign('childInfo',$childInfo);
        if($childInfo['status']<INSPECTION_CHILDSTATUS_MASTER_FINISHORDER){

            $this->display('WechatPublic/storeInspection/inspectionChildOrderDetail');
        }else{
            $this->storeInspection($inspection_store_child_id);
        }
    }

    /**
     * 门店添加巡检备注页面
     * @param $inspectionChildId
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function storeInspectionRequirements($inspectionChildId){

        $this->inspectionChildId = $inspectionChildId;

        //查询巡检备注信息
        $WechatInspectionStoreChildModel = (new WechatInspectionStoreChildModel());
        $info = $WechatInspectionStoreChildModel
            ->field('requirements_text')
            ->where([
            'inspection_store_child_id'=>$inspectionChildId,
        ])->find();

        //查询巡检备注图片
        $imgData = (new WechatPublicOrderService())->getInspectionRequirementsImg($inspectionChildId);

        $imgDataNew = [];
        foreach($imgData as $k=>$v){
//            $imgDataNew[$k+10] = substr($v['path'],1);
            $imgDataNew[$k+10] = $v['path'];
        }

        $this->infoData = $info;

        $this->pic = $imgDataNew;
        $this->picNum = count($imgDataNew);
        $this->picJson = json_encode($imgDataNew);

        $this->display('WechatPublic/storeInspection/storeInspectionRequirements');
    }

    /**
     * 提交门店巡检备注页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function addInspectionRequirements(){
        $inspectionChildId = I('inspectionChildId');
        $requirements_text = I('requirements_text');
        $imageData = I('imageData');

        //判断参数
        if(!is_numeric($inspectionChildId)){

            $this->ajaxReturn(['status'=>0,'msg'=>'提交失败，请稍后重试。']);
        }

        $data = [
            'inspection_store_child_id'=>$inspectionChildId,
            'requirements_text'=>$requirements_text,
        ];

        //存储数据
        $WechatInspectionStoreChildModel = (new WechatInspectionStoreChildModel());
        $return = $WechatInspectionStoreChildModel->editData($data);
        if($return === false){

            $this->ajaxReturn(['status'=>0,'msg'=>'提交失败，请稍后重试。']);
        }

        $WechatPublicOrderService = (new WechatPublicOrderService());

        //处理图片
//        if(is_array($imageData)){
        $WechatPublicOrderService->editPicData($imageData,$inspectionChildId,'wechatpollingremark','wechatpollingremark');
//        }

        //查询订单信息，如果存在巡检员就发送消息，如果不存在巡检员，就给巡检主管发送消息
        $WechatPublicOrderService->sendInspectionRequirementsMsg($inspectionChildId);

        $this->ajaxReturn(['status'=>1,'msg'=>'提交成功','data'=>['id'=>$inspectionChildId]]);
    }

    //=========================================门店端巡检列表END==========================================

    /**************************************地址管理功能 开始***********************************************/
    /**
     * 地址列表（微信菜单点击时）
     */
    public function addressList(){

        //获取用户地址列表
//        $memId = 4792;
        $memId = session("memId");
        if(empty($memId)){
            $this->wechatLogin();
            $memId = session("memId");
        }
        $condition['member_id'] = $memId;
        $condition['status'] = 1;//1 启用 2 禁用
        $fields = "add_id,store_name,latng,location_address,city_id,province_id,link_person,link_phone,fixed_line,
        is_default,status,member_id,store_id,IF(char_length(detail_address) > 25,CONCAT(LEFT (detail_address, 25),'...'),
	    detail_address) detail_address";
        $this->addressList = (new WechatAddressModel())->getAddressList($condition,$fields);
        $this->display('WechatPublic/address/addressList');
    }

    /**
     * 地址列表（下单时选择地址时）
     */
    public function selectAddressList(){
        //获取用户地址列表
//        $memId = 4792;
        $memId = session("memId");
        if(empty($memId)){
            $this->wechatLogin();
        }
        $condition['member_id'] = $memId;
        $condition['status'] = 1;//1 启用 2 禁用
        $fields = "add_id,store_name,latng,location_address,city_id,province_id,link_person,link_phone,fixed_line,
        is_default,status,member_id,store_id,IF(char_length(detail_address) > 25,CONCAT(LEFT (detail_address, 25),'...'),
	    detail_address) detail_address";
        $this->addressList = (new WechatAddressModel())->getAddressList($condition,$fields);
        $this->type = I('type');
        $this->equip_id = I('equip_id') ? I('equip_id') : "";
        $this->again = I('again') ? I('again') : "";
        $this->order_id = I('order_id') ? I('order_id') : "";
        $this->display('WechatPublic/address/selectAddressList');
    }

    /**
     * 添加地址
     * @param int $type
     * @param string $equip_id
     */
    public function addAddress($type = 0,$equip_id = "",$again = "",$order_id = ""){
        //获取省市区
        $this->province = $this->DistrictModel->where(['id'=>['eq',110000]])->select();
        $condition['upid'] = ['eq',110000];
        $this->city = $this->DistrictModel->where($condition)->select();
        $this->location_address = I('addr') ? I('addr') : "";
        $this->latng = I('latng') ? I('latng') : "";
        $this->type = $type;
        $this->equip_id = $equip_id;
        $this->display('WechatPublic/address/addAddress');
    }

    /**
     * 编辑地址
     * @param $add_id
     * @param int $type
     */
    public function editAddress($add_id,$type = 0,$equip_id = "",$again = "",$order_id = ""){
        //获取省市区
        $this->province = $this->DistrictModel->where(['id'=>['eq',110000]])->select();
        $condition['upid'] = ['eq',110000];
        $this->city = $this->DistrictModel->where($condition)->select();
        //获取编辑的地址信息
        $addressInfo = (new WechatAddressModel())->getAddressInfo(['add_id'=>$add_id]);
        $addressInfo['location_address'] = I('addr') ? I('addr') : $addressInfo['location_address'];
        $addressInfo['latng'] = I('latng') ? I('latng') : $addressInfo['latng'];
        $this->addressInfo = $addressInfo;
        $this->type = $type;
        $this->equip_id = $equip_id;
        $this->again = $again;
        $this->order_id = $order_id;
        $this->display('WechatPublic/address/editAddress');
    }
    /**
     * 新增编辑地址信息
     */
    public function createAddress(){
//        $memId = 4792;
        $memId = session("memId");
        $addressData = I('post.');
        $addressData['member_id'] = $memId;
        $addressModel = new WechatAddressModel();
        //如果是默认地址，则更新之前的默认地址
        if($addressData['is_default']){
            $defaultCon['member_id'] = $memId;
            $defaultCon['is_default'] = 1;
            //要修改的数据
            $changeData['is_default'] = 0;
            $addressModel->where($defaultCon)->save($changeData);
        }
        //如果地址id存在则为修改
        if(isset($addressData['add_id']) && !empty($addressData['add_id'])){
            $addressModel->save($addressData);
            $this->ajaxReturn(['status'=>1]);
        }
        $addressData['create_time'] = $addressData['update_time'] = date("Y-m-d H:i:s");
        $res = $addressModel->add($addressData);
        if($res){
            $this->ajaxReturn(['status'=>1,'add_id'=>$res]);
        }
        $this->ajaxReturn(['status'=>0,'msg'=>"新增失败，请稍候重试！"]);
    }
    /**
     * 删除地址
     * @param $add_id
     */
    public function delAddress($add_id){
        $addressModel = new WechatAddressModel();
        $addressModel->where(['add_id'=>['in',$add_id]])->save(['status'=>2]);
        $this->ajaxReturn(['status'=>1]);
    }
    /**************************************地址管理功能 结束***********************************************/
}
