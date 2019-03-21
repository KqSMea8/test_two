<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Enterprise\Controller;
use Enterprise\Service\EnterpriseService;
use Goods\Model\SomethingPicModel;
use Home\Model\PictureModel;
use Think\Controller;
use think\Cache;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatServiceGuidelinesModel;
use Think\Log;
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;

class StoreCleaningController extends Controller{

    private $corpID;
    private $appSecret;
    private $token;
    private $encodingAesKey;
    private $svr;
    private $WechatServiceGuidelinesModel;
    private $WechatOrderModel;
    private $WechatOrderItemModel;
    private $WechatPublicOrderService;
    private $WechatService;
    private $PictureModel;
    private $SomethingPicModel;
    private $WechatMemberModel;


    function _initialize()
    {
        $this->corpID = COMPANY_CORPID;
        $this->appSecret = COMPANY_CLEANING_APPSECRET;
        $this->token = COMPANY_TOKEN;
        $this->encodingAesKey = COMPANY_EMCODINGASEKEY; //暂未设置 TODO
        $this->svr = (new EnterpriseService($this->encodingAesKey,$this->token,$this->corpID,$this->appSecret));
//        parent::__construct();

        $this->WechatServiceGuidelinesModel = new WechatServiceGuidelinesModel();
        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatOrderItemModel = new WechatOrderItemModel();
        $this->WechatPublicOrderService = new WechatPublicOrderService();
        $this->WechatService = new WechatService();
        $this->PictureModel = new PictureModel();
        $this->SomethingPicModel = new SomethingPicModel();
        $this->WechatMemberModel = new WechatMemberModel();
    }

///////////////////////////////////////////////////////////////////////////清洗管理开始////////////////////////////////////////////////////////////////////////////////////////////////
    private function getWxUserId(){
        //判断是否存在缓存userId
        $wxUserId = session('wxUserId');

        //$wxUserId = "weigo521";//测试使用

        if(!$wxUserId){

            if(isset($_REQUEST['code'])){

                $data = $this->svr->getUserId($_REQUEST['code']);
                session('wxUserId',$data['UserId']);
                return $data['UserId'];
            }else{

                $url = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                $redirectUrl = $this->svr->getUserCode($url);
                header("Location:".$redirectUrl);
                exit;
            }
        }else{

            return $wxUserId;
        }
    }
    private function getUserInfo($type_user){
        //获取企业号用户标识wx_code，然后通过标识获取该用户对应的 服务商ID
        $wxUserId = $this->getWxUserId();
//        $wxUserId = 'pangshifu';
        $facidata = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"wx_code" => $wxUserId,'type_user'=>4])->find();
        return $facidata;
    }
    /**
     * 订单列表
     */
    public function orderShowList(){
        //TODO 三个页面订单数据接口 可封装成一个方法
        //获取派单中的订单列表
        $map['orde.order_state'] = 10;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收)
        $map['orde.order_type'] = 3;//1设备维修，2门店消杀，3设备清洗
        //        $map['orde.workers_id'] = session("member.memid");//获取该师傅的工单
        $map['orde.workers_id'] = $this->getUserInfo()['uid'];//获取该师傅的工单
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,orde.order_type,member.uid,member.name uname";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,3);
        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/storeCleaning/orderShowList');
    }
    /**
     * 根据订单状态获取对应数据
     * @author pangyongfu
     */
    public function getOrderDataByStatus(){
        $status = I('status');
        $map['orde.order_state'] = $status;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        $map['orde.order_type'] = 3;//1设备维修，2门店消杀，3设备清洗
        //        $map['orde.workers_id'] = session("member.memid");//获取该师傅的工单
        $map['orde.workers_id'] = $this->getUserInfo()['uid'];//获取该师傅的工单
        if($status == 2){
            $map['orde.order_state'] = ['in',[2,9]];
        }
        if($status == 3){
            $map['orde.order_state'] = ['in',[3,5,7]];
        }
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,orde.order_type,member.uid,member.name uname";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,3);

        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
            $this->ajaxReturn(["status" => 1,"data" => $orderData]);
        }
        $this->ajaxReturn(["status" => 0,"msg" => "该状态下无订单数据"]);
    }
    /**
     * 订单详情
     * @param $order_id
     * @author pangyongfu
     */
    public function orderDetail($order_id,$is_enterprise = 0){
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        $map['orde.workers_id'] = $this->getUserInfo()['uid'];//获取该师傅的工单
//        $map['orde.workers_id'] = 77;//获取该师傅的工单
        //订单数据
        $field = "orde.*,item.store_area,item.store_scene,item.clean_type,item.last_clean_time,item.after_sale_text,item.petticoat_pipe,item.upright_flue_length,
        item.across_flue_length,item.flue_round_num,item.purifier_slice_num,item.draught_fan_clean_num,item.fireproof_board_length,
        item.draught_fan_chaixi_num,item.entirety_greasy_dirt,item.cancel_text,member.uid,member.name,member.phone";

        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        if(empty($orderData)){
            $this->display('WechatPublic/storeCleaning/orderError');
            exit;
        }
        $dirty = $orderData['entirety_greasy_dirt'];
        switch($dirty){
            case 1:
                $orderData['entirety_greasy_dirt'] = "轻度";
                break;
            case 2:
                $orderData['entirety_greasy_dirt'] = "中度";
                break;
            case 3:
                $orderData['entirety_greasy_dirt'] = "重度";
                break;
        }
        $len = mb_strlen($orderData['detailed_address'],"utf-8");
        if($len > 30){
            $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,30,"utf-8")."...";
        }
        //检查生成订单支付数据
        $renturnData = (new WechatService())->checkOrderNoPay($orderData);

        $this -> getWechatJsSignPackage();

        //清洗前图片数据
        $this->beforeImgData = $this->WechatPublicOrderService->getOrderImg($order_id);
        //清洗后图片数据
        $this->afterImgData = $this->WechatPublicOrderService->getOrderImg($order_id,1);
        $this->orderData = $orderData;

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
        $this->is_enterprise = $is_enterprise;

        $this->display('WechatPublic/storeCleaning/orderDetail');
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
     * 清洗完成--上传清洗图片
     * @author pangyongfu
     */
    public function cleaningSuccess(){
        $data['id'] = I('id');
        $data['code'] = I('order_code');
        $data['is_ka'] = I('is_ka');

        $beforeImageFile = I('beforeimgdata');
        $afterImageFile = I('afterimgdata');
        //添加清洗前图片数据
        if(!empty($beforeImageFile)) {
            foreach ($beforeImageFile as $k => $v) {
                if (empty($v)) {
                    continue;
                }
                $tmp = [
                    'path' => $v,
                    'type' => "wechat",
                    'create_time' => time(),
                    'status' => 1,
                ];
                $resultId = $this->PictureModel->add($tmp);
                //图片预订单绑定
                $tmp_pic = [
                    'app' => "WECHAT",
                    'row_id' => $data['id'],
                    'pic_id' => $resultId,
                    'type' => 0,//0 之前 1 之后
                    'create_time' => time(),
                    'update_time' => date("Y-m-d H:i:s"),
                ];
                $this->SomethingPicModel->add($tmp_pic);
            }
        }
        //添加清洗后图片数据
        if(!empty($afterImageFile)) {
            foreach ($afterImageFile as $k => $v) {
                if (empty($v)) {
                    continue;
                }
                $tmp = [
                    'path' => $v,
                    'type' => "wechat",
                    'create_time' => time(),
                    'status' => 1,
                ];
                $resultId = $this->PictureModel->add($tmp);
                //图片预订单绑定
                $tmp_pic = [
                    'app' => "WECHAT",
                    'row_id' => $data['id'],
                    'pic_id' => $resultId,
                    'type' => 1,//0 之前 1 之后
                    'create_time' => time(),
                    'update_time' => date("Y-m-d H:i:s"),
                ];
                $this->SomethingPicModel->add($tmp_pic);
            }
        }
        $this->ajaxReturn(["status" => 1,"data" => $data]);
    }

    /**
     * 输入清洗数据
     * @author pangyongfu
     */
    public function inputCleaningPrice(){
        $this->order_id = I('order_id');
        $this->order_code = I('order_code');
        $this->is_ka = I('is_ka');
        $this->is_change_price = I('is_change_price') ? I('is_change_price') : 0;
        $this->display('WechatPublic/storeCleaning/inputCleaningPrice');
    }

    /**
     * 更新清洗订单--师傅输入的清洗数据与金额
     * @author pangyongfu
     */
    public function updateCleaningOrder(){
        //拼凑更新数据

        $money = I('money_total');
        $is_ka = I('is_ka');
        $orderData['id'] = I('id');
        $orderData['order_state'] = I('order_state');
        if(empty($money) || $is_ka){
            $orderData['order_state'] = 9;//金额为0或者是ka订单状态是 待验收
        }
        $orderData['money_total'] = $money;
        $orderData['update_time'] = date("Y-m-d H:i:s");

        $condition['order_id'] = ['eq',I('id')];
        $orderItemData['petticoat_pipe'] = I("petticoat_pipe");
        $orderItemData['upright_flue_length'] = I("upright_flue_length");
        $orderItemData['across_flue_length'] = I("across_flue_length");
        $orderItemData['flue_round_num'] = I("flue_round_num");
        $orderItemData['purifier_slice_num'] = I("purifier_slice_num");
        $orderItemData['draught_fan_clean_num'] = I("draught_fan_clean_num");
        $orderItemData['draught_fan_chaixi_num'] = I("draught_fan_chaixi_num");
        $orderItemData['fireproof_board_length'] = I("fireproof_board_length");
        $orderItemData['entirety_greasy_dirt'] = I("entirety_greasy_dirt");
        $orderItemData['update_time'] = date("Y-m-d H:i:s");

        //判断如果是第一次修改价格则将money_total_old字段也添加上 , 以后变更money_total_old字段是在客户端 查看订单详情页面后端代码
        if(I('id')){
            $orderInfo = $this->WechatOrderModel->where(['id'=>I('id')])->find();
            if(empty($orderInfo['money_total_old'])){
                $orderData['money_total_old'] = $money;
            }
        }

        //更新订单
        $res_order = $this->WechatOrderModel->save($orderData);
        $item_res = false;
        if($res_order){
            $item_res = $this->WechatOrderItemModel->where($condition)->save($orderItemData);
        }
        $is_change_price = I('is_change_price');
        if($item_res){
            Log::write('修改订单状态成功,订单ID为：'.$orderData['id'].'订单状态为：'.$orderData['order_state']);
            $res = array("status" => 1,"data" => ["id" => $orderData['id']]);

            if(empty($money) || $is_ka){
                //发送用户订单确认完成微信模板消息
                $this->WechatService->sendUserMakeSureOrderOverMsg(I('id'),$is_change_price,3);
            }else{
                //发送用户微信模板消息
                $this->WechatService->sendMasterOrederFinishPayMoneyNew(I('id'),'storeCleaning',$is_change_price);
            }
        }else{
            Log::write('修改订单状态失败,订单ID为：'.$orderData['id'].'订单状态为：'.$orderData['order_state']);
            $res = array("status" => 0,"data" => ["id" => $orderData['id']]);
        }
        $this->ajaxReturn($res);
    }
    /**
     * 导出我的报表数据
     */
    public function myReport(){

        if(isset($_REQUEST['echostr'])){
            $this->svr->verifyURL();
        }else{
            $this->svr->parseMsg();

            $content = $this->svr->getRev('all');
            if($content['MsgType'] == 'event' && $content['Event'] == 'click' ){
                $this->toExcel($content);
            }
        }
    }
    private function toExcel($content){
        //type : week = 周 | mouth = 月 | all 所有 可参考维养报表 TODO
//        "wx_code" => $content['FromUserName']
        if($content['EventKey'] == 'week'){
            $condition['start_time'] = date("Y-m-d H:i:s",mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y")));
            $condition['end_time'] = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y")));
            $fileNameItem = "本周工单";
        }elseif($content['EventKey'] == 'mouth'){
            $condition['start_time'] = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y")));
            $condition['end_time'] = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y")));
            $fileNameItem = "本月工单";
        }elseif($content['EventKey'] == 'all'){
            $fileNameItem = "所有工单";
        }
        //找出该用户信息（uid）
        $uid = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"wx_code" => $content['FromUserName'],'type_user'=>4])->find()['uid'];
        //获取该用户清洗且已完成的订单数据
        $trueData = [];
        $map['orde.order_state'] = ['in',[3,4,5,6,7]];
        $map['orde.order_type'] = ['eq',3];//设备清洗
        $map['orde.workers_id'] = $uid;//师傅ID
        $field = "orde.*";
        $orderData = $this->WechatOrderModel->getOrderAndItemData($map,$field);
        if(!empty($orderData)){
            foreach($orderData as $key=>$value){
                $trueData[$key]['id'] = $value['id'];
                $trueData[$key]['single_time'] = $value['single_time'];
                $trueData[$key]['order_code'] = $value['order_code']." ";
                $trueData[$key]['link_person'] = filterEmoji($value['link_person']);
                $trueData[$key]['link_phone'] = $value['link_phone'];
                $trueData[$key]['create_time'] = $value['create_time'];
                $trueData[$key]['money_total'] = $value['money_total'];
                switch($value['order_state']){
                    case 3:
                        $trueData[$key]['order_state'] = "已完成";
                        break;
                    case 4:
                        $trueData[$key]['order_state'] = "已取消";
                        break;
                    case 5:
                        $trueData[$key]['order_state'] = "无需上门";
                        break;
                    case 6:
                        $trueData[$key]['order_state'] = "已退款";
                        break;
                    case 7:
                        $trueData[$key]['order_state'] = "已评价";
                        break;
                }
                $trueData[$key]['update_time'] = $value['update_time'];
            }
        }

        $title = array (
            "row" => 1,
            "count" => 13,
            "title" => array (
                '序号',
                '接单时间',
                '订单号',
                "用户名",
                '用户电话',
                "下单时间",
                "订单金额",
                '订单状态',
                '完成时间'
            )
        );

        $style = array (
            "colwidth" => array (
                'A' => "9",
                'B' => "19",
                'C' => "23",
                'D' => "23",
                'E' => '22',
                "F" => '13',
                'G' => '20',
                "H" => '20',
                "I" => '20',
            )
        );

        $info = array (
            "table_name_one" => '报表',
            'table_name_two' => '师傅清洗报表',
            'lister' => '餐讯',
            'tabletime' => date ( "Y-m-d H:i:s" )
        );

        $fileName = $fileNameItem.date("YmdHis").".xls";
        $fileName = iconv("UTF-8","GBK",$fileName);

        if(!file_exists($_SERVER['DOCUMENT_ROOT']."/excel")){
            mkdir($_SERVER['DOCUMENT_ROOT']."/excel");
        }

        $dir = $_SERVER['DOCUMENT_ROOT']."/excel/".$fileName;
        saveExcel ( $dir,$info, $title, $trueData, $style );
        $result = $this->svr->mediaUpload('file',['media'=>'@'.$dir]);

        $param = [
            'touser'=>$content['FromUserName'],
            'msgtype'=>'file',
            'agentid'=>$content['AgentID'],
            'file'=>[
                'media_id'=>$result['media_id']
            ],
            'safe'=>0
        ];
        $this->svr->sendMessage(json_encode($param));
    }
///////////////////////////////////////////////////////////////////////////清洗管理结束////////////////////////////////////////////////////////////////////////////////////////////////

}