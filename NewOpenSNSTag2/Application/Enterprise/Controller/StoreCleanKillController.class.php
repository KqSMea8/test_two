<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Enterprise\Controller;
use Admin\Model\PictureModel;
use Enterprise\Service\EnterpriseService;
use Goods\Model\SomethingPicModel;
use Think\Controller;
use think\Cache;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatServiceGuidelinesModel;
use Think\Log;
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;

class StoreCleanKillController extends Controller{

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
    private $WechatMemberModel;
    private $PictureModel;
    private $SomethingPicModel;


    function _initialize()
    {
        $this->corpID = COMPANY_CORPID;
        $this->appSecret = COMPANY_CLEANKILL_APPSECRET;
        $this->token = COMPANY_TOKEN;
        $this->encodingAesKey = COMPANY_EMCODINGASEKEY; //暂未设置 TODO
        $this->svr = (new EnterpriseService($this->encodingAesKey,$this->token,$this->corpID,$this->appSecret));
//        parent::__construct();

        $this->WechatServiceGuidelinesModel = new WechatServiceGuidelinesModel();
        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatPublicOrderService = new WechatPublicOrderService();
        $this->WechatService = new WechatService();
        $this->WechatMemberModel = new WechatMemberModel();
        $this->WechatOrderItemModel = new WechatOrderItemModel();
        $this->PictureModel = new PictureModel();
        $this->SomethingPicModel = new SomethingPicModel();
    }

///////////////////////////////////////////////////////////////////////////消杀管理开始////////////////////////////////////////////////////////////////////////////////////////////////
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
    private function getUserInfo(){
        //获取企业号用户标识wx_code，然后通过标识获取该用户对应的 服务商ID
        $wxUserId = $this->getWxUserId();
//        $wxUserId = 'pangshifu';
        $facidata = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"wx_code" => $wxUserId,'type_user'=>2])->find();
        return $facidata;
    }
    /**
     * 订单列表
     */
    public function orderShowList(){
        //获取派单中的订单列表
        $map['orde.order_state'] = 10;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收)
        $map['orde.order_type'] = 2;//1设备维修，2门店消杀，3设备清洗
//        $map['orde.is_year'] = 0;//只展示不是年订单的订单
//        $map['orde.workers_id'] =session("memId");//获取该师傅的工单
		$workersId = $this->getUserInfo()['uid'];
        $map['orde.workers_id'] = $workersId;//获取该师傅的工单
        //$map['orde.workers_id'] = 4848;//获取该师傅的工单
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,orde.order_type,member.uid,member.name uname";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,2);
        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/storeCleanKill/orderShowList');
    }
    /**
     * 根据订单状态获取对应数据
     * @author pangyongfu
     */
    public function getOrderDataByStatus(){
        $status = I('status');
        $workersId = $this->getUserInfo()['uid'];
        $map['orde.order_state'] = $status;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
//        $map['orde.is_year'] = 0;//只展示不是年订单的订单
        $map['orde.order_type'] = 2;//1设备维修，2门店消杀，3设备清洗
        //        $map['orde.workers_id'] = session("member.memid");//获取该师傅的工单
        $map['orde.workers_id'] = $workersId;//获取该师傅的工单
        if($status == 2){
            $map['orde.order_state'] = ['in',[PAY_STATUS_NO_PAY,PAY_STATUS_PAY_SUCCESS,PAY_STATUS_PAY_WAITACCEPT,PAY_STATUS_PAY_INSERVICE]];//服务中
        }
        if($status == 3){
            $map['orde.order_state'] = ['in',[PAY_STATUS_PAY_COMPLETION,PAY_STATUS_PAY_PHONE_OVER,PAY_STATUS_PAY_APPRAISE,PAY_STATUS_OUTDATE]];//已完成
        }
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,orde.order_type,member.uid,member.name uname";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,2);

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
    public function orderDetail($order_id,$is_enterprise = 0,$is_edit = 0){
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        $map['orde.workers_id'] = $this->getUserInfo()['uid'];//获取该师傅的工单
        //订单数据
        $field = "orde.*,item.worker_over_time,item.store_area,item.store_scene,item.insect_species,item.insect_time,item.after_sale_text,item.difference_price,
        item.difference_text,item.malfunction_text,item.difference_status,item.cancel_text,item.difference_status,item.difference_price,member.uid,member.name,
        member.phone";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        if(empty($orderData)){
            $this->display('WechatPublic/storeCleanKill/orderError');
            exit;
        }
        //如果是年订单且在接单之后开始消杀之前就获取主订单的消杀建议
        if($orderData['is_year'] == 1 && $orderData['order_state'] == 1 && empty($orderData['equipment_id'])){
            $yearMap['is_year'] = 1;
            $yearMap['is_main'] = 1;
            $yearMap['year_service_id'] = $orderData['year_service_id'];
            $field = "malfunction_text";
            $orderData['malfunction_text'] = $this->WechatOrderModel->getYearOrderDataByMap($yearMap,$field)['malfunction_text'];
        }
        $len = mb_strlen($orderData['detailed_address'],"utf-8");
        if($len > 30){
            $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,30,"utf-8")."...";
        }
        //增加扫码支付
        $wechatService = (new WechatService());

        //检查生成订单支付数据
        $renturnData = $wechatService->checkOrderNoPay($orderData);

        $this -> getWechatJsSignPackage();

        if($orderData['order_state'] == '0' && !empty($orderData['renew_order_id'])){
            $orderData['order_state_text'] = '续签中';
        }
        $this->p_timeStamp = $renturnData['p_timeStamp'];
        $this->p_nonceStr = $renturnData['p_nonceStr'];
        $this->p_package = $renturnData['p_package'];
        $this->p_paySign = $renturnData['p_paySign'];
        $this->p_signType = $renturnData['p_signType'];
        $this->payUrl = $renturnData['payUrl'];
        $this->moneyPay = $renturnData['moneyPay'];
        //如果消杀完成时间不为空则展示消杀完成时间
        if(!empty($orderData['worker_over_time'])){
            $orderData['update_time'] = $orderData['worker_over_time'];
        }
        //防治建议图片数据
        $this->beforeImgData = $this->WechatPublicOrderService->getOrderImg($order_id);
        //消杀报告图片数据
        $this->afterImgData = $this->WechatPublicOrderService->getOrderImg($order_id,1);
        //编辑和中途返回时数据丢失问题解决功能
        $this->beforeEditImgData = json_encode($this->beforeImgData);
        $this->afterEditImgData = json_encode($this->afterImgData);

        $this->orderData = $orderData;
        $this->is_enterprise = $is_enterprise;
        $this->isEdit = $is_edit;
        $this->display('WechatPublic/storeCleanKill/orderDetail');
    }
    public function setDiffPrice(){
        $this->order_id = I('order_id');
        $this->order_code = I('order_code');
        $this->display('WechatPublic/storeCleanKill/setDiffPrice');
    }

    /**
     * 实时保存师傅填写的信息数据（待验收时）
     */
    public function toSaveTmpData(){
        //获取参数
        $orderId = I('id');
        $malfunction_text = I('malfunction_text');
        //拼凑扩展订单数据
        if(!empty($malfunction_text)){
            $orderItemData['malfunction_text'] = $malfunction_text;
            $this->WechatOrderItemModel->where(['order_id' => $orderId])->save($orderItemData);
        }
        //删除已上传图片
        $beforeimgdata = I('beforeimgdata') ? I('beforeimgdata') : [];
        $afterimgdata = I('afterimgdata') ? I('afterimgdata') : [];
        $imageFiles = array_merge($beforeimgdata,$afterimgdata);
        $this->WechatPublicOrderService->delPicData($imageFiles,$orderId,'wechat');

        //上传防止简易图片
        if(!empty($beforeimgdata)){
            foreach ($beforeimgdata as $befork => $beforv) {
                if(empty($beforv)){
                    continue;
                }
                $tmp = [
                    'path'=>$beforv,
                    'type' => "wechat",
                    'create_time' => time(),
                    'status' =>1,
                ];
                $resultId = $this->PictureModel->add($tmp);
                //图片预订单绑定
                $tmp_pic = [
                    'app'=>"WECHAT",
                    'row_id'=>$orderId,
                    'pic_id'=>$resultId,
                    'type'=>0,//0 之前 1 之后
                    'create_time'=>time(),
                    'update_time'=>date("Y-m-d H:i:s"),
                ];
                $this->SomethingPicModel->add($tmp_pic);
            }
        }
        //上传消杀报告图片
        if(!empty($afterimgdata)){
            foreach ($afterimgdata as $afterk => $afterv) {
                if(empty($afterv)){
                    continue;
                }
                $tmp = [
                    'path'=>$afterv,
                    'type' => "wechat",
                    'create_time' => time(),
                    'status' =>1,
                ];
                $resultId = $this->PictureModel->add($tmp);
                //图片预订单绑定
                $tmp_pic = [
                    'app'=>"WECHAT",
                    'row_id'=>$orderId,
                    'pic_id'=>$resultId,
                    'type'=>1,//0 之前 1 之后
                    'create_time'=>time(),
                    'update_time'=>date("Y-m-d H:i:s"),
                ];
                $this->SomethingPicModel->add($tmp_pic);
            }
        }
        $this->ajaxReturn(['status'=>1]);
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
        $uid = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"wx_code" => $content['FromUserName'],'type_user'=>2])->find()['uid'];
        //获取该用户清洗且已完成的订单数据
        $trueData = [];
        $map['orde.order_state'] = ['in',[3,4,5,6,7]];
        $map['orde.order_type'] = ['eq',2];//设备消杀
        $map['orde.workers_id'] = $uid;//师傅ID
        $field = "orde.*，item.difference_price,item.difference_status";
        $orderData = $this->WechatOrderModel->getOrderAndItemData($map,$field);
        if(!empty($orderData)) {
            foreach ($orderData as $key => $value) {
                $trueData[$key]['id'] = $value['id'];
                $trueData[$key]['single_time'] = $value['single_time'];
                $trueData[$key]['order_code'] = $value['order_code']." ";
                $trueData[$key]['link_person'] = filterEmoji($value['link_person']);
                $trueData[$key]['link_phone'] = $value['link_phone'];
                $trueData[$key]['create_time'] = $value['create_time'];
                if ($value['difference_status'] == 2) {
                    $trueData[$key]['money_total'] = $value['money_total'] + $value['difference_price'];
                } else {
                    $trueData[$key]['money_total'] = $value['money_total'];
                }
                switch ($value['order_state']) {
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
        //格式化为Excel数据
        //
        //维修商	门店	报修内容	报修时间	修好时间	出车费	物料费	费用明细
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
            ),
            "fontcolor" => array (
                'A4' => "9",
                'B3' => "19",
                'C4' => "23",
                'D' => "23",
                'E' => '22',
                "F" => '13',
                'G' => '20',
                "H" => '20',
            )
        );

        $info = array (
            "table_name_one" => '报表',
            'table_name_two' => '师傅消杀报表',
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

///////////////////////////////////////////////////////////////////////////消杀管理结束////////////////////////////////////////////////////////////////////////////////////////////////

}