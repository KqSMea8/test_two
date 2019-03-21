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
use Enterprise\Service\InspectionService;
use Goods\Model\SomethingPicModel;
use Think\Controller;
use Home\Model\DistrictModel;
use think\Cache;
use Wechat\Model\WechatEquipmentCategoryModel;
use Wechat\Model\WechatFacilitatorModel;
use Wechat\Model\WechatKaEnterpriseModel;
use Wechat\Model\WechatKaStoresModel;
use Wechat\Model\WechatDeviceModel;
use Wechat\Model\WechatInspectionStoreChildDeviceModel;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatMemberStoreModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatServiceGuidelinesModel;
use Think\Log;
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;

class StoreMaintainController extends Controller{

    private $corpID;
    private $appSecret;
    private $token;
    private $encodingAesKey;
    private $svr;
    private $inspectionService;
    private $DistrictModel;
    private $WechatKaEnterpriseModel;
    private $WechatKaStoresModel;
    private $WechatServiceGuidelinesModel;
    private $WechatOrderModel;
    private $WechatOrderItemModel;
    private $WechatPublicOrderService;
    private $WechatService;
    private $WechatMemberModel;
    private $WechatDeviceModel;
    private $SomethingPicModel;
    private $PictureModel;


    function _initialize()
    {
        $this->corpID = COMPANY_CORPID;
        $this->appSecret = COMPANY_MAINTAIN_APPSECRET;
        $this->token = COMPANY_TOKEN;
        $this->encodingAesKey = COMPANY_EMCODINGASEKEY; //暂未设置 TODO
        $this->svr = (new EnterpriseService($this->encodingAesKey,$this->token,$this->corpID,$this->appSecret));
        $this->inspectionService = (new InspectionService($this->encodingAesKey,$this->token,$this->corpID,$this->appSecret));

        $this->DistrictModel = new DistrictModel();
        $this->WechatServiceGuidelinesModel = new WechatServiceGuidelinesModel();
        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatOrderItemModel = new WechatOrderItemModel();
        $this->WechatPublicOrderService = new WechatPublicOrderService();
        $this->WechatService = new WechatService();
        $this->WechatKaEnterpriseModel = new WechatKaEnterpriseModel();
        $this->WechatKaStoresModel = new WechatKaStoresModel();
        $this->WechatMemberModel = new WechatMemberModel();
        $this->WechatDeviceModel = new WechatDeviceModel();
        $this->SomethingPicModel = new SomethingPicModel();
        $this->PictureModel = new PictureModel();
    }

///////////////////////////////////////////////////////////////////////////维修管理开始////////////////////////////////////////////////////////////////////////////////////////////////
    private function getWxUserId(){
        //判断是否存在缓存userId
        $wxUserId = session('wxUserId');

        //$wxUserId = "huangqing";//测试使用

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
//        $wxUserId = 'huangqing';
        $facidata = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"wx_code" => $wxUserId,'type_user'=>3])->find();
        return $facidata;
    }
    /**
     * 订单列表
     */
    public function orderShowList(){
        //TODO 三个页面订单数据接口 可封装成一个方法
        //获取派单中的订单列表
        $map['orde.order_state'] = 10;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收)
        $map['orde.order_type'] = 1;//1设备维修，2门店消杀，3设备清洗
//        $map['orde.workers_id'] = session("member.memid");//获取该师傅的工单
        $map['orde.workers_id'] = $this->getUserInfo()['uid'];//获取该师傅的工单
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,1);
        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/storeMaintain/orderShowList');
    }
    /**
     * 根据订单状态获取对应数据
     * @author pangyongfu
     */
    public function getOrderDataByStatus(){
        $status = I('status');
        $map['orde.order_state'] = $status;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        $map['orde.order_type'] = 1;
        //        $map['orde.workers_id'] = session("member.memid");//获取该师傅的工单
        $map['orde.workers_id'] = $this->getUserInfo()['uid'];//获取该师傅的工单
        if($status == 2){
            $map['orde.order_state'] = ['in',[2,9]];
        }
        if($status == 3){
            $map['orde.order_state'] = ['in',[3,5,7]];
        }
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,1);
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

        $wechatService = (new WechatService());
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        $map['orde.workers_id'] = $this->getUserInfo()['uid'];//获取该师傅的工单
//        $map['orde.workers_id'] = 33;//获取该师傅的工单

        //订单数据
        $field = "orde.*,item.equipment_name,item.brands_text,item.after_sale_text,item.phone_solve_text,item.malfunction_text,item.cancel_text,item.change_parts_text,item.old_order_id,item.parts_price,member.uid,member.name,member.phone,item.mr_id,item.urgent_level";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        if(empty($orderData)){
            $this->display('WechatPublic/storeMaintain/orderError');
            exit;
        }

        //检查生成订单支付数据
        $renturnData = $wechatService->checkOrderNoPay($orderData);

        $this -> getWechatJsSignPackage();

        if(empty($orderData['change_parts_text'])){
            $orderData['change_parts_text'] = "无";
        }
        $new_order_id = $order_id;
        //如果订单为售后订单则则展示原订单图片
        if($orderData['is_sh'] == 1){
            $new_order_id = $orderData['old_order_id'];
        }
        $len = mb_strlen($orderData['detailed_address'],"utf-8");
        if($len > 30){
            $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,30,"utf-8")."...";
        }
        //图片数据
        $imgData = $this->WechatPublicOrderService->getOrderImg($new_order_id);
        //维修部位图片
        $this->afterImgData = $this->WechatPublicOrderService->getOrderImg($order_id,1);
        $this->orderData = $orderData;
        $this->imgData = $imgData;

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

        $this->display('WechatPublic/storeMaintain/orderDetail');
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
    public function repaireSuccess(){
        $this->order_id = I('order_id');
        $this->order_code = I('order_code');
        $this->is_ka = I('is_ka');
        $this->is_change_price = I('is_change_price') ? I('is_change_price') : 0;

        //保留显示 默认上价格的功能 TODO
        if(I('is_change_price')){

        }

        $this->display('WechatPublic/storeMaintain/repaireSuccess');
    }

    /**
     * 更新维修订单--师傅输入的维修数据与金额
     * @author pangyongfu
     */
    public function updateMaintainOrder(){
        //拼凑更新数据
        $is_ka = I('is_ka');
        $orderData['id'] = I('id');
        $orderData['order_state'] = I('order_state');
        $parts_price = I('parts_price');
        $door_price = I('door_price');
        $service_price = I('service_price');
        $total = $parts_price + $door_price + $service_price;
        $orderData['door_price'] = $door_price;
        $orderData['service_price'] = $service_price;
        $orderData['money_total'] = $total;
        $orderData['update_time'] = date("Y-m-d H:i:s");

        $condition['order_id'] = ['eq',I('id')];
        $orderItemData['is_maintain'] = I("is_maintain");
        $orderItemData['is_change_parts'] = I("is_change_parts");
        $orderItemData['change_parts_text'] = I("change_parts_text");
        $orderItemData['parts_price'] = $parts_price;
        $orderItemData['update_time'] = date("Y-m-d H:i:s");

        //判断如果是第一次修改价格则将money_total_old字段也添加上 , 以后变更money_total_old字段是在客户端 查看订单详情页面后端代码
        if(I('id')){
            $orderInfo = $this->WechatOrderModel->where(['id'=>I('id')])->find();
            if(empty($orderInfo['money_total_old'])){
                $orderData['money_total_old'] = $total;
            }
        }

        if(empty($total) || $is_ka){
            $orderData['order_state'] = 9;//金额为0或者是ka订单状态是 待验收
        }
        $is_change_price = I('is_change_price');
        //更新订单
        $res_order = $this->WechatOrderModel->save($orderData);
        $item_res = false;
        if($res_order){
            $item_res = $this->WechatOrderItemModel->where($condition)->save($orderItemData);
        }
        //先清空之前订单中的维修部位图片
        $imgAfter = $this->SomethingPicModel->where(['row_id'=>I('id'),'type'=>1])->group('row_id')->field("GROUP_CONCAT(pic_id) pic_id")->find();
        if(!empty($imgAfter)){
            $picData = $this->PictureModel->where(['id'=>['in',$imgAfter['pic_id']]])->field("path")->select();
            foreach($picData as $picVal){
                @unlink($picVal['path']);
            }
            $this->SomethingPicModel->where(['row_id'=>I('id'),'type'=>1])->delete();
            $this->PictureModel->where(['id'=>['in',$imgAfter['pic_id']]])->delete();
        }
        //上传维修部位图片
        $afterimgdata = I('afterimgdata');
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
                    'row_id'=>I('id'),
                    'pic_id'=>$resultId,
                    'type'=>1,//0 之前 1 之后
                    'create_time'=>time(),
                    'update_time'=>date("Y-m-d H:i:s"),
                ];
                $this->SomethingPicModel->add($tmp_pic);
            }
        }
        if($item_res){
            Log::write('修改订单状态成功,订单ID为：'.$orderData['id'].'订单状态为：'.$orderData['order_state']);
            $res = array("status" => 1,"data" => ["id" => $orderData['id']]);

            //判断订单如果没有产生任何费用 就直接发送已完成的消息
            if(empty($total) || $is_ka){
                //发送用户订单确认完成微信模板消息
                $this->WechatService->sendUserMakeSureOrderOverMsg(I('id'),$is_change_price);
                //TODO 如果是领值订单，则通知领值系统
                $orderData = $this->WechatPublicOrderService->getOrderData(I('id'));
                if(!empty($orderData['mr_id'])){
                    $this->WechatService->curlPatch($orderData['mr_id'],6,$orderData['money_total'],$orderData['door_price'],$orderData['service_price'],$orderData['parts_price']);
                }
            }else{
                //发送用户微信模板消息
                $this->WechatService->sendMasterOrederFinishPayMoneyNew(I('id'),'storeMaintain',$is_change_price);
            }

        }else{
            Log::write('修改订单状态失败,订单ID为：'.$orderData['id'].'订单状态为：'.$orderData['order_state']."itemres:".$item_res);
            $res = array("status" => 0,"data" => ["id" => $orderData['id']]);
        }
        $this->ajaxReturn($res);
    }
    /**
     * 导出我的报表数据
     */
    public function myReport(){
        log::write('服务端接收数据 | '.json_encode($_REQUEST));

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
        $uid = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"wx_code" => $content['FromUserName'],'type_user'=>3])->find()['uid'];
        //获取该用户清洗且已完成的订单数据
        $trueData = [];
        $map['orde.order_state'] = ['in',[3,4,5,6,7]];
        $map['orde.order_type'] = ['eq',1];//设备维修
        $map['orde.workers_id'] = $uid;//师傅ID
        $field = "orde.*";
        $orderData = $this->WechatOrderModel->getOrderAndItemData($map,$field);
        if(!empty($orderData)) {
            foreach ($orderData as $key => $value) {
                $trueData[$key]['id'] = $value['id'];
                $trueData[$key]['single_time'] = $value['single_time'];
                $trueData[$key]['order_code'] = $value['order_code']." ";
                $trueData[$key]['link_person'] = filterEmoji($value['link_person']);
                $trueData[$key]['link_phone'] = $value['link_phone'];
                $trueData[$key]['create_time'] = $value['create_time'];
                $trueData[$key]['money_total'] = $value['money_total'];
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
            'table_name_two' => '师傅维修报表',
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
///////////////////////////////////////////////////////////////////////////维修管理结束////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 维修师傅点击顾客下单按钮
     * @author pangyongfu
     */
    public function customerOrder(){
        $order_id = I('order_id');
        $member_id = I('member_id');
        //TODO 给用户发下单的消息
        $this->WechatService->sendOrderAgainMsg($member_id,$order_id);
        $this->ajaxReturn(['status'=>1,'msg'=>'已向顾客发送消息，请让顾客查收！']);
    }



///////////////////////////////////////////////////////////////////////////巡检师傅开始////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 展示巡检子订单详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionOrderDetail(){

        $inspectionStoreChildId = I('inspectionStoreChildId');

        //获取当前师傅数据
        $userInfo = $this -> getUserInfo();

        if(!$userInfo || !$inspectionStoreChildId){
            throw_exception('您没有权限，不可访问！');//需要优化 TODO
        }

        $childInfo = $this->inspectionService->getChildOrderInfoForChildId($inspectionStoreChildId);

        //判断如果该订单不是目前登录师傅的单子，给出提示，抛出异常
        if($childInfo['inspector_id'] != $userInfo['uid']){
            throw_exception('您没有权限，不可访问！');//需要优化 TODO
        }

        $this->requirementsStatus = $childInfo['requirements_text'] ? 1 : 0;
        //查询是否存在巡检备注图片数据
        if($childInfo['requirements_text']){

            //查询巡检备注图片
            $imgData = (new WechatPublicOrderService())->getInspectionRequirementsImg($inspectionStoreChildId);
            if($imgData){
                //截取拼接图片链接
                foreach($imgData as &$value){
                    $value['path'] = host_url.strstr($value['path'],'/');
                }
            }
            $this->pic = $imgData;
            $this->picNum = count($imgData) ? count($imgData) : 0;
        }

        //判断 如果当前订单状态大于等于 巡检员完成巡检  门店点击完成，就展示另外一个详情页面
        if($childInfo['status'] >= INSPECTION_CHILDSTATUS_MASTER_FINISHORDER){
            $this->showChildOrderDetailNew($inspectionStoreChildId);
        }else{
            $childInfo['status_text'] = C('INSPECTION_CHILDSTATUS')[$childInfo['status']];
            $childInfo['isNoMaster'] = (empty($childInfo['storePerson']) && empty($childInfo['mentorPhone'])) ? 0 : 1;

            //提示页面数据来源
            $this -> assign('isSource','master');

            $this -> assign('childInfo',$childInfo);

            $this -> assign('uid',$userInfo['uid']);

            $this->display('WechatPublic/storeMaintain/inspection/inspectionChildOrderDetail');
        }
    }


    /**
     * 展示子订单详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showChildOrderDetailNew($inspectionStoreChildId){
        //TODO 获取子订单设备详情
        $map = ['inspection_store_child_id'=>$inspectionStoreChildId];
        $field = "*";
        $list = (new WechatInspectionStoreChildModel())->serviceOneDetailList($map, '', $field);
        $memberInfo = $this->WechatMemberModel->getOnceInfo(['uid'=>$list['inspector_id']],'');
        $storeInfo = $this->WechatKaStoresModel->getOnceInfo(['id'=>$list['store_id']],'');
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
        $wechatDeviceInfo = (new WechatDeviceModel())->where(['inspection_store_child_id'=>$inspectionStoreChildId])->find();

        $list['status_text'] = C('INSPECTION_CHILDSTATUS')[$list['status']];
        $list['store_name'] = $storeInfo['name'];
        $list['facilitator_name'] = $facilitatorInfo['name'];
        $list['user_name'] = $memberInfo['name'];
        $list['user_phone'] = $memberInfo['phone'];
        $list['id'] = $list['inspection_store_child_id'];
        $this->assign("wechatDeviceInfo",$wechatDeviceInfo ? 1:0);
        $this->assign("list",$list);
        $this->assign("imgInfo",$imgInfo);
        $this->assign("imgCount",count($imgInfo));
        $this->assign("store_id",json_encode($list['store_id']));
        $this->assign("inspection_store_child_id",json_encode($list['inspection_store_child_id']));
        $this->display('WechatPublic/customerServices/inspection/storeInspection');
    }

    /**
     * 设置子订单状态 巡检员接单，巡检员开始巡检
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function setChildInspectorStatus(){
        $inspectionChildId = I('inspectionChildId');
        $uid = I('uid');
        $type = I('type');

        if(!$inspectionChildId || !$uid || !$type){
            $this->ajaxReturn(['status'=>0,'msg'=>'服务器错误，请稍后重试！']);
        }

        //修改订单状态 巡检员接单、巡检员开始巡检
        if($type == 'TAKEORDERS'){
            $saveData = [
                'inspector_id'=>$uid,
                'status'=>INSPECTION_CHILDSTATUS_MASTER_TAKEORDERS,
            ];
        }elseif($type == 'STARTORDER'){
            $saveData = [
                'inspector_id'=>$uid,
                'status'=>INSPECTION_CHILDSTATUS_MASTER_STARTORDER,
            ];
        }

        //巡检开始时间
        $saveData['inspection_start_time'] = date("Y-m-d H:i:s");

        $return = (new WechatInspectionStoreChildModel())->where(['inspection_store_child_id'=>$inspectionChildId,])->save($saveData);

        if($return !== false){

            if($type=='TAKEORDERS'){
                //给门店店长 发送师傅接单消息 TODO
                (new WechatService())->sendInsAcceptOrderMsg($inspectionChildId);
            }

            $this->ajaxReturn(['status'=>1,'msg'=>'操作成功！']);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'操作失败，请稍后重试！']);
        }

    }

    /**
     * 显示巡检员操作详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showInspectionOperationDetail(){

        $inspectionStoreChildId = I('inspection_store_child_id');
        $reportStatus = I('reportStatus');

        //查询子订单数据
        $inspectionChildData = (new WechatInspectionStoreChildModel())->getDataInfo(['inspection_store_child_id'=>$inspectionStoreChildId]);

//        if(!$inspectionStoreChildId || !$inspectionChildData){
//            throw_exception('您没有权限，不可访问！');//需要优化 TODO
//        }

        if($inspectionChildData['status'] == INSPECTION_CHILDSTATUS_MASTER_FINISHORDER || $inspectionChildData['status'] == INSPECTION_CHILDSTATUS_STORE_OVERORDER){

        }

        //判断该门店是否绑定了设备数据
        if($inspectionChildData){

            $wechatDeviceData = (new WechatDeviceModel())->where(['store_id'=>$inspectionChildData['store_id'],'status'=>1])->find();

            //如果没有绑定就展示图片添加页面
            if(!$wechatDeviceData){

                //设备数据
                //判断是否展示设备
                $this->isDevice = 1;
            }else{
            //如果绑定了就展示对应的设备列表

                //设备数据
                //判断是否展示设备
                $this->isDevice = 2;
            }
        }

        //获取门店名称
        $storeInfo = (new WechatKaStoresModel())->where(['id'=>$inspectionChildData['store_id']])->find();

        //往session中存储 企业、门店、门店地址信息、联系人、联系电话、报修设备、设备品牌
//        $this->WechatKaStoresModel->where(['store_id'=>$inspectionChildData['sotre_id']]);
//        $inspectionChildData['enterprise_id'];

        $this->storeName = $storeInfo['name'];
        $this->inspectionChildData = $inspectionChildData;
        $this->inspectionStoreChildId = $inspectionStoreChildId;

        $this->reportStatus = ($reportStatus == 1) ? 1 : 2;

        $this->display('WechatPublic/storeMaintain/inspection/inspectionOperationDetail');
    }

    /**
     * 显示设备操作详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showinspectionoperationPage(){

        $inspectionStoreChildId = I('inspectionStoreChildId');
        $inspectionChildDeviceId = I('inspectionChildDeviceId');
        $deviceId = I('deviceId');

        if(IS_POST){
            $inspectionOperate = I('inspectionOperate');
            $remark = I('remark');
            $uploadImageListFront = I('uploadImageListFront');
            $uploadImageListBack = I('uploadImageListBack');

            //参数判断
            if(!$inspectionOperate || !$uploadImageListFront || !$uploadImageListBack){

                $this->ajaxReturn(['status'=>0,'msg'=>'参数不全,提交失败']);
            }

            $WechatInspectionStoreChildDeviceModel = (new WechatInspectionStoreChildDeviceModel());

            //修改数据 操作数据
            $editData = [
                'inspection_child_device_id' => $inspectionChildDeviceId,
                'inspection_store_child_id' => $inspectionStoreChildId,
                'inspection_operate' => $inspectionOperate,
                'device_id' => $deviceId,
                'remark' => $remark,
            ];
            $id = $WechatInspectionStoreChildDeviceModel->editData($editData);

            if(!empty($id)){
                $WechatPublicOrderService = (new WechatPublicOrderService());

                //处理图片 巡检前
                $WechatPublicOrderService->editPicData($uploadImageListFront,$id,'inspectionoperationfront','INSPECTIONOPERATIONFRONT');

                //处理图片 巡检后
                $WechatPublicOrderService->editPicData($uploadImageListBack,$id,'inspectionoperationback','INSPECTIONOPERATIONBACK');
            }

            $this->ajaxReturn(['status'=>1,'msg'=>'提交成功']);
        }else{

            //查询设备操作功能
            $WechatDeviceModel = (new WechatDeviceModel());

            //查询设备操作数据
            $where = ['wd.id'=>$deviceId];
            $deviceOperationInfo = $WechatDeviceModel->getDeviceOperation($where,'weco.*');

            //查询图片
            $imgFrontDataNew = [];
            $imgBackDataNew = [];
            if(!empty($inspectionChildDeviceId) && $inspectionChildDeviceId != 'null'){

                //查询操作巡检前图片
                $SomethingPicModel = (new SomethingPicModel());
                $frontWhere = [
                    'sm.app'=>'INSPECTIONOPERATIONFRONT',
                    'sm.row_id'=>$inspectionChildDeviceId,
                ];
                $imgFrontData = $SomethingPicModel->getImgData($frontWhere,'pic.path');

                //查询操作巡检后图片
                $backWhere = [
                    'sm.app'=>'INSPECTIONOPERATIONBACK',
                    'sm.row_id'=>$inspectionChildDeviceId,
                ];
                $imgBackData = $SomethingPicModel->getImgData($backWhere,'pic.path');

                foreach($imgFrontData as $k => $v){

                    $imgFrontDataNew[$k+10] = $v['path'];
                }

                foreach($imgBackData as $k => $v){

                    $imgBackDataNew[$k+10] = $v['path'];
                }

            }

            //查询巡检备注
            $WechatInspectionStoreChildDeviceModel = (new WechatInspectionStoreChildDeviceModel());
            $inspectionChildDeviceInfo = $WechatInspectionStoreChildDeviceModel->getInfoByMap(['inspection_child_device_id'=>$inspectionChildDeviceId],'inspection_operate,remark');

            //巡检订单数据
            if(!empty($inspectionChildDeviceInfo['inspection_operate']))
                $inspectionChildDeviceInfo['inspection_operate'] = explode(',',$inspectionChildDeviceInfo['inspection_operate']);

            //循环处理数组
            foreach($deviceOperationInfo as &$value){

                if(in_array($value['id'],$inspectionChildDeviceInfo['inspection_operate'])){
                    $value['selected'] = 'yes';
                }else{
                    $value['selected'] = 'no';
                }
            }

            $this->inspectionChildDeviceInfo = $inspectionChildDeviceInfo;

            //巡检前后图片
            $this->imgFrontData = $imgFrontDataNew;
            $this->imgFrontDataNum = count($imgFrontDataNew);
            $this->imgFrontDataJson = json_encode($imgFrontDataNew);

            $this->imgBackData = $imgBackDataNew;
            $this->imgBackDataNum = count($imgBackDataNew);
            $this->imgBackDataJson = json_encode($imgBackDataNew);

            //设备操作数据
            $this->deviceOperationInfo = $deviceOperationInfo;

            $this->inspectionStoreChildId = $inspectionStoreChildId;

            $this->deviceId = $deviceId;

            $this->inspectionChildDeviceId = $inspectionChildDeviceId;

            $this->display('WechatPublic/storeMaintain/inspection/inspectionOperationPage');
        }
    }

    /**
     * 获取设备列表数据
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getDeviceList(){
        $inspectionStoreChildId = I('inspectionStoreChildId');
        $searchText = I('searchText');

        //查询子订单数据
        $inspectionChildData = (new WechatInspectionStoreChildModel())->getDataInfo(['inspection_store_child_id'=>$inspectionStoreChildId]);

        if($inspectionChildData['store_id']){

            $where = [
                'wd.status'=>1,
                'wd.device_name'=>['like','%'.$searchText.'%'],
                'wisc.inspection_store_child_id' => $inspectionStoreChildId
            ];

           $returnData = $this->inspectionService->getChildOrderDeviceList($where);
           if($returnData){
               $this->ajaxReturn(['status'=>1,'data'=>$returnData]);
           }else{
               $this->ajaxReturn(['status'=>0]);
           }
        }

        $this->ajaxReturn(['status'=>0]);
    }

    /**
     * 巡检员完成页面 添加图片数据（第一次）
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function finishChildOrderForImg(){

        $inspectionStoreChildId = I('inspectionStoreChildId');
        $uploadImageList = I('uploadImageList');

        //添加图片数据
        (new WechatPublicOrderService())->editPicData($uploadImageList,$inspectionStoreChildId,"wechat","INSPECTION");

        //修改 订单状态
        $data['status'] = INSPECTION_CHILDSTATUS_MASTER_FINISHORDER;

        //巡检完成时间
        $data['inspection_end_time'] = date("Y-m-d H:i:s");

        $return = (new WechatInspectionStoreChildModel())->where(['inspection_store_child_id'=>$inspectionStoreChildId])->save($data);

        if($return !== false){

            //todo 发送订单完成消息消息
            (new WechatService())->sendInsOrderStatusChangeMsg($inspectionStoreChildId);

            $this->ajaxReturn(["status" => 1,'msg'=>'提交成功！']);
        }else{
            $this->ajaxReturn(["status" => 0,'msg'=>'提交失败，请刷新重试！']);
        }
    }

    /**
     * 巡检报告页面
     * @param string $inspectionChildId
     * @param string $type type如果有值则是巡检员操作，反之没值则是 只展示图片,没有操作功能
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionReportPage($inspectionChildId = '',$type = ''){

        if(IS_POST){
            $inspectionChildId = I('post.inspectionChildId');
            $uploadImageList = I('post.uploadImageList');

            if(!$inspectionChildId || !$uploadImageList){
                $this->ajaxReturn(["status" => 0,'msg'=>'提交失败，请刷新重试！']);
            }

            //添加图片数据
            (new WechatPublicOrderService())->editPicData($uploadImageList,$inspectionChildId,"wechat","INSPECTION");

            $this->ajaxReturn(["status" => 1,'msg'=>'提交成功！']);

        }else{

            $picData = [];
            if(!empty($inspectionChildId)){

                $SomethingPicModel = (new SomethingPicModel());
                $picDataTemp = $SomethingPicModel->alias("sm")
                    ->field('pic.path')
                    ->join("jpgk_picture as pic on sm.pic_id = pic.id")
                    ->where([
                        'sm.app'=>"INSPECTION",
                        'sm.row_id'=>$inspectionChildId,
                    ])
                    ->select();

                foreach($picDataTemp as $k => $v){

//                    $picData[$k+10] = host_url.'/'.explode('./',$v['path'])[1];
                    $picData[$k+10] = '/'.explode('./',$v['path'])[1];
                }
            }

            $this->type = !empty($type) ? true : false;
            $this->PicData = $picData;
            $this->PicDataJs = json_encode($picData);
            $this->PicDataNum = count($picData);
            $this->inspectionChildId = !empty($inspectionChildId) ? $inspectionChildId : 0;
            $this->display('WechatPublic/storeMaintain/inspection/inspectionReportDetail');
        }
    }

    /**
     * 师傅操作完成子订单
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function masterFinishChildOrder(){
        $inspectionStoreChildId = I('inspectionStoreChildId');

        if(!$inspectionStoreChildId){
            $this->ajaxReturn(["status" => 0,'msg'=>'提交失败，请刷新重试！']);
        }

        //修改 订单状态 为已完成
        $data['status'] = INSPECTION_CHILDSTATUS_MASTER_FINISHORDER;
        $data['is_showPic'] = 2;

        //巡检完成时间
        $data['inspection_end_time'] = date('Y-m-d H:i:s');

        $return = (new WechatInspectionStoreChildModel())->where(['inspection_store_child_id'=>$inspectionStoreChildId])->save($data);

        //查询该子订单是否有新添加的设备
        $deviceInfo = (new WechatDeviceModel())->where(['inspection_store_child_id'=>$inspectionStoreChildId,'status'=>3])->find();

        if($return !== false){

            //todo 发送订单完成消息消息
            (new WechatService())->sendInsOrderStatusChangeMsg($inspectionStoreChildId);

//            $childInfo = (new WechatInspectionStoreChildModel())->where(['inspection_store_child_id'=>$inspectionStoreChildId])->find();
//            //给用户推送消息
//            $memInfo = (new WechatMemberModel())->getOnceInfo(['isadmin'=>7,'status'=>1,'stores_id'=>$childInfo['store_id']]);
//            //获取门店
//            $storeInfo = (new WechatKaStoresModel())->getOnceInfo(['status'=>1,'id'=>$childInfo['store_id']]);
//            (new WechatService())->acceptanceOrder($memInfo['open_id'],$childInfo['inspection_store_child_id'],$storeInfo['name'],$childInfo['service_num'],$childInfo['child_order_code']);


            //如果有新设备，则给客服发送新设备提醒的消息
            if($deviceInfo){

                $where['d.status'] = 3;
                $where['d.inspection_store_child_id'] = ['eq',$inspectionStoreChildId];

                $fields = 'd.inspection_store_child_id,wm.wx_code,wks.`name` storeName,wisc.service_num,wisc.child_order_code';
                $returnData = (new WechatDeviceModel()) -> getDeviceInspectionMemInfo($where,$fields);

                (new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET))->sendInspectionNewDeviceMsg($returnData);
            }

            $this->ajaxReturn(["status" => 1,'msg'=>'提交成功！']);
        }else{
            $this->ajaxReturn(["status" => 0,'msg'=>'提交失败，请刷新重试！']);
        }
    }

    /**
     * 更改到店时间
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function changeArriveTimeForId(){

        $inspectionStoreChildId = I('inspectionStoreChildId');
        $arriveTime = I('arriveTime');

        $where['inspection_store_child_id']=$inspectionStoreChildId;
        $save['arrive_time']=$arriveTime;

        $return = (new WechatInspectionStoreChildModel())->where($where)->save($save);

        if($return !== false){

            $this->ajaxReturn(['status'=>1,'arriveTime'=>$arriveTime]);
        }else{

            $this->ajaxReturn(['status'=>0]);
        }
    }

    /**
     * 提交巡检子订单 修改操作中的内容
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function changeChildOrderOperate(){

        //获取到子订单标识、子订单设备标识、设备标识、选中的设备操作、操作备注
        $inspectionStoreChildId = I('inspectionStoreChildId');
        $inspectionChildDeviceId = I('inspectionChildDeviceId');
        $deviceId = I('deviceId');
        $inspectionOperate = I('inspectionOperate');
        $remark = I('remark');

        //整理数据
        $nowTime = date('Y-m-d H:i:s');
        $data['inspection_store_child_id'] = $inspectionStoreChildId;
        $data['device_id'] = $deviceId;
        $data['inspection_operate'] = $inspectionOperate;
        $data['remark'] = $remark;
        $data['update_time'] = $nowTime;

        $Model = (new WechatInspectionStoreChildDeviceModel());

        $returnData = false;
        //判断如果子订单设备标识存在的话就是 修改 编辑
        if(!empty($inspectionChildDeviceId) && $inspectionChildDeviceId != 0){

            $returnData = $Model->where(['inspection_child_device_id'=>$inspectionChildDeviceId])->save($data);
        }else{
            //反之如果不存在就是添加

            $data['create_time'] = $nowTime;
            $returnData = $Model->add($data);
        }

        //返回数据
        if($returnData !== false){
            $this->ajaxReturn(['status'=>1,'msg'=>'提交成功！']);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'提交失败，请稍后重试！']);
        }
    }


    /**
     * 显示已修订单页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showInspectionYetRepairPage(){

        $inspectionStoreChildId = I('inspectionStoreChildId');
        $inspectionChildDeviceId = I('inspectionChildDeviceId');
        $companyId = I('companyId');
        $storeId = I('storeId');
        $deviceId = I('deviceId');

        //来源 目前有两个地方巡检设备操作  巡检设备其他操作
        $source = I('source');

        //查询子订单相关数据
        $field = 'wisc.child_order_code,wks.name storeName,wm.name masterName';
        $where['wisc.inspection_store_child_id'] = $inspectionStoreChildId;
        $info = (new WechatInspectionStoreChildModel())->getChildOrderInfo($where,$field);
        $this->orderInfo = $info;

        //查询设备相关信息
        $deviceInfo = (new WechatDeviceModel())->info($deviceId,'id,device_name,device_code,brand');
        $this->deviceInfo = $deviceInfo;
        $this->deviceInfoJs = json_encode($deviceInfo);

        $this->inspectionStoreChildId = $inspectionStoreChildId;
        $this->inspectionChildDeviceId = $inspectionChildDeviceId;
        $this->companyId = $companyId;
        $this->storeId = $storeId;
        $this->source = $source ? $source : '';

        $this->display('WechatPublic/storeMaintain/inspection/inspectionYetRepair');
    }

    /**
     * 更新维修订单--师傅输入的维修数据与金额
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function updateMaterMaintainOrder(){

        if(empty(I('inspectionStoreChildId')) || empty(I('deviceId'))){
            $this->ajaxReturn(["status" => 0,"msg"=>'提交失败，请刷新后重试！']);
        }

        //提交创建订单数据
        $return = $this->inspectionService->createYetRepairInspectionMaster();
        if($return){
            $res = ["status" => 1,"msg"=>'提交成功'];

        }else{
            $res = ["status" => 0,"msg"=>'提交失败，请稍后重试！'];
        }
        $this->ajaxReturn($res);
    }



    /**
     * 巡检员给门店报修维修工单
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showInspectionRepairCreate(){

        $companyId = I('companyId');
        $storeId = I('storeId');
        $deviceId = I('deviceId');
        $inspectionChildDeviceId = I('inspectionChildDeviceId');
        $inspectionStoreChildId = I('inspectionStoreChildId');
        $is_ka = 1;
        $equip_id = 1;

        //来源 目前有两个地方巡检设备操作  巡检设备其他操作
        $source = I('source');

        $this->signpackge = $this->WechatService->getSignPackage();
//        $this->province = $this->DistrictModel->where(['id'=>['eq',110000]])->select();
//        $condition['upid'] = ['eq',110000];
//        // TODO 目前地址有限制，只开放海淀区 昌平区
//        $city = $this->DistrictModel->where($condition)->select();
//        $merge_city = [["id"=>0,"name"=>"请选择","level"=>"2","upid"=>"110000"]];
//        $this->city = array_merge($merge_city,$city);
        $this->equipment_id = $equip_id;
        $this->is_ka = $is_ka;
        $this->source = $source ? $source : '';

        $this->store_name = "";
        $this->address = "";
        $this->link_person = "";
        $this->link_phone = "";

        $companyStoreData = $this->getCompanyStoreData($companyId,$storeId);
        $this->companyStoreData = $companyStoreData;
        $this->companycode = $companyStoreData['companycode'];
        $this->storecode = $companyStoreData['storecode'];

        $memberStore = [];
        if($companyStoreData){
            $where['userStore.store_id'] = $companyStoreData[0]['storeId'];
            $where['member.status'] = 1;
            $field = 'member.*';

            //通过用户门店关联表查询门店绑定的用户
            $memberStore = (new WechatMemberStoreModel())->getMemberStoreInfoByWhere($where,$field);
            $this->link_person = $memberStore[0]['name'];
            $this->link_phone = $memberStore[0]['phone'];
//                $this->location = $memberInfo['location_name'];
//                $this->latng = $memberInfo['latng'];

            //获取设备名称
            $deviceInfo = (new WechatDeviceModel())->where(['id'=>$deviceId])->find();
            $this->deviceId = $deviceInfo['id'];
            $this->deviceName = $deviceInfo['device_name'];
        }

        $this->inspectionStoreChildId = !empty($inspectionStoreChildId) ? $inspectionStoreChildId : 0;
        $this->inspectionChildDeviceId = !empty($inspectionChildDeviceId) ? $inspectionChildDeviceId : 0;
        $this->location = I('addr') ? I('addr') : $memberStore[0]['location_name'];
        $this->latng = I('latng') ? I('latng') : $memberStore[0]['latng'];
        $this->memberId = $memberStore[0]['uid'] ? $memberStore[0]['uid'] : 0;

        $this->display('WechatPublic/storeMaintain/inspection/inspectionRepairCreate');
    }

    /**
     * 巡检员给门店报修维修工单
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function createOrder(){
        $order_type = I('order_type');
        $res = $this->inspectionService->inspectionMasterCreateOrder($order_type);
        echo json_encode($res);
        exit;
    }

    /**
     * 获取企业和门店信息
     * @param $company_code
     * @param $store_code
     * @return mixed
     */
    private function getCompanyStoreData($company_id,$store_id){
//        $data = session("company_".$company_id."_store_".$store_id);
        if(empty($data)){
            //根据企业编号获取企业信息
            $companyData = $this->WechatKaEnterpriseModel->getData(['id'=>$company_id]);
            //根据门店编号获取门店信息
            $fileds = "store.link_person,store.link_phone,store.id storeId,store.name storename,store.code storecode,store.province,store.city,store.stores_address,ent.code companycode,ent.name companyname";
            $data = $this->WechatKaStoresModel->getData(['store.id'=>$store_id,'store.enterprise_id'=>$companyData[0]['id']],$fileds);
//            session("company_".$company_id."_store_".$store_id,$data);
        }
        //获取地址信息
        $districtData = $this->WechatPublicOrderService->getDistrictData($data[0]['province'],$data[0]['city']);

        //拼接订单信息内容
        if($districtData){
            $orderData['stores_address'] = $districtData[0].' '.$districtData[1].' '.$data['stores_address'];
        }
        return $data;
    }

    /**
     * 上传图片 巡检使用
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function downLoadImageForInspection(){

        set_time_limit(0);

        $dirname = "./Public/wechatLogImg/inspection/".date("Ymd")."/";

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
     * 显示巡检详情页面
     */
    public function inspectionDevice(){
        $child_id = I('inspection_store_child_id');
        $where['dev.inspection_store_child_id'] = $child_id;
        //获取巡检工单其他设备巡检详情
        $inspectionDetail = (new WechatDeviceModel())->getInspectionDeviceDetail($where);

        foreach($inspectionDetail as &$inspection){
            //获取设备图片
            $picId = M('SomethingPic')->where(['row_id'=>$inspection['device_id'],'app'=>'INSPECTIONDEVICE'])
                ->field('GROUP_CONCAT(pic_id) pic_id')->find();
            $devicePic = M('Picture')->where(['id'=>['in',$picId['pic_id']],'type'=>'inspectiondevice'])->field('path')->select();
            $inspection['device_pic'] = array_column($devicePic,'path');
            //判断设备状态--拼凑维修详情
            if($inspection['repairs_id']){
                //报修订单详情
                $inspection['repaire_url'] = host_url."/Wechat/Index/showStoreRepaireOrderForInspection/id/".$inspection['repairs_id'];
            }elseif($inspection['yet_repairs_id']){
                $inspection['yet_repaire_url'] = host_url."/Wechat/Index/orderdetail/id/".$inspection['yet_repairs_id'];
            }
        }

        $this->inspectionDetail = $inspectionDetail;
        $this->display('WechatPublic/storeMaintain/inspection/inspectionDevice');
    }

    /**
     * 师傅端展示巡检操作 其他页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionAddDeviceNew(){

        $inspectionChildId = I('inspectionChildId');
        if(!$inspectionChildId){
            throw_exception('参数不全，不可访问！');
        }

        //TODO 增加判断如果当前操作的子订单状态已提交就抛出错误页面
//        $inspectionChildInfo = (new WechatInspectionStoreChildModel())->field('status')->where(['inspection_store_child_id'=>$inspectionChildId])->find();
//        if($inspectionChildInfo['status'] != INSPECTION_CHILDSTATUS_MASTER_STARTORDER){
//            throw_exception('你不可访问当前页面！');
//        }

        //查询巡检分类数据
        $deviceType = (new WechatEquipmentCategoryModel)->where(['status'=>1])->select();

        $this->inspectionStoreChildId = $inspectionChildId;
        $this->storeId = I('storeId');
        $this -> deviceType = $deviceType;
        $this -> deviceTypeJs = json_encode($deviceType);

        //获取详情订单关联的设备详情
        $deviceList = [];
        if($inspectionChildId){

            $deviceMap['ins.inspection_store_child_id'] = $inspectionChildId;
            $deviceMap['dev.status'] = 3;//设备状态 1 启用 2 禁用 -1 删除 3 新增未启用
//            $field = "d.id device_id,d.device_name,d.brand,ec.id category_id,`name`";
//            $deviceList = $this->WechatDeviceModel->getDeviceInfo($deviceMap,$field);

            $deviceList = (new WechatInspectionStoreChildDeviceModel())->getInspectionDeviceDetail($deviceMap);
        }

        $this->deviceLength = count($deviceList);
        $this->deviceList = json_encode($deviceList);

        if($inspectionChildId){

            //获取巡检子订单详情页面
            $inspectionChildData = (new WechatInspectionStoreChildModel())
                ->getDataInfo([
                    'inspection_store_child_id'=>$inspectionChildId
                ]);
        }

        $this->inspectionChildData = $inspectionChildData;
        $this->inspectionChildDataJs = json_encode($inspectionChildData);
        $this->display('WechatPublic/storeMaintain/inspection/inspectionAddDeviceNew');
    }

    /**
     * 添加|修改 巡检其他设备内容
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function addInspectionOtherDevice(){

        //获取到巡检设备的内容
        $deviceData = I('deviceData');
        $inspectionStoreChildId = I('inspectionStoreChildId');

        //参数判断
        if(!$deviceData && !$inspectionStoreChildId){
            $this->ajaxReturn(['status'=>0,'msg'=>'参数不全，添加失败！']);
        }

        $deviceAddArr = [];
        $WechatDeviceModel = (new WechatDeviceModel());
        $WechatPublicOrderService = (new WechatPublicOrderService());
        $WechatInspectionStoreChildDeviceModel = (new WechatInspectionStoreChildDeviceModel());
        //获取师傅和客服id
        $where['wisc.inspection_store_child_id'] = $inspectionStoreChildId;
        $fields = 'wi.customer_service_id,wisc.inspector_id,wisc.store_id';
        $inspectionInfo = (new WechatInspectionStoreChildModel())->getInspectionChildInfo($where,$fields);

        //1.循环传递来的设备数据，拼接数据
        foreach($deviceData as $val){

            $deviceAddArr['device_name'] = $val[0];//设备名称
            $deviceAddArr['device_code'] = getDeviceCode($val[0]);//设备编码
            $deviceAddArr['store_id'] = $inspectionInfo['store_id'];//门店标识
            $deviceAddArr['brand'] = $val[1];//设备品牌
            $deviceAddArr['category'] = $val[2];//设备类别
            $deviceAddArr['status'] = 3;
            $deviceAddArr['inspection_store_child_id'] = $inspectionStoreChildId;
            $deviceAddArr['inspector_id'] = $inspectionInfo['inspector_id'];//巡检员标识
            $deviceAddArr['customer_service_id'] = $inspectionInfo['customer_service_id'];//客服标识

            $imgArr = $val[4];//设备图片

            //已生成的设备标识 如果数据存在就走修改
            if(!empty($val[5]) && explode(":",$val[5])[1]){

                $deviceAddArr['id'] = explode(":",$val[5])[1];
            }

            //添加设备数据
            $returnDeviceId = $WechatDeviceModel->editDataNew($deviceAddArr);

            if(!empty($imgArr)){
                //添加或修改图片
                $WechatPublicOrderService->editPicData($imgArr,$returnDeviceId,'inspectiondevice','inspectiondevice');
            }

            //已生成的子订单设备数据标识，如果数据存在就走修改
            if(!empty($val[6]) && explode(":",$val[6])[1]){
                $childDeviceData['inspection_child_device_id'] = explode(":",$val[6])[1];
                $childDeviceData['remark'] = $val[3];//巡检备注
                $childDeviceData['source'] = 2;//巡检来源
                $childDeviceData['device_id'] = $returnDeviceId;
                $WechatInspectionStoreChildDeviceModel->editData($childDeviceData);

                unset($childDeviceData);
            }else{
                if($val[3]){
                    $childDeviceData['remark'] = $val[3];//巡检备注
                    $childDeviceData['inspection_store_child_id'] = $inspectionStoreChildId;
                    $childDeviceData['device_id'] = $returnDeviceId;
                    $WechatInspectionStoreChildDeviceModel->editData($childDeviceData);
                }
            }

            unset($deviceAddArr);
        }

        $this->ajaxReturn(['status'=>1,'msg'=>'添加成功！']);
    }

    /**
     * 添加设备功能
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function addDevice(){

        //获取到参数数据
        $device_name = I('device_name');
        $brand = I('brand');
        $select = I('select');
        $remark = I('remark');
        $inspectionStoreChildId = I('inspectionStoreChildId');
        $storeId = I('storeId');
        $deviceId = !empty(I('deviceId')) ? explode(':',I('deviceId'))[1] : '';

        if(!$device_name || !$brand || !$select || !$remark || !$inspectionStoreChildId || !$storeId){
            throw_exception('参数不全！');
        }

        //拼接操作数据
        if(!empty($deviceId))
            $data['id'] = $deviceId;

        $data['device_code'] = getDeviceCode($device_name);
        $data['store_id'] = $storeId;
        $data['device_name'] = $device_name;
        $data['brand'] = $brand;
        $data['category'] = $select;
        $data['status'] = 3;
        $data['inspection_store_child_id'] = $inspectionStoreChildId;

        //添加或修改数据
        $res = $this->WechatDeviceModel->editDataNew($data);

        if($res){

            //查询如果有对应的门店设备子订单数据，就不进行添加操作
            if($deviceId){
                $storeChildDeviceInfo = (new WechatInspectionStoreChildDeviceModel())
                    ->field('inspection_child_device_id')
                    ->where([
                        'device_id'=>$deviceId,
                        'inspection_store_child_id'=>$inspectionStoreChildId,
                    ])
                    ->find();

                if($storeChildDeviceInfo)
                    $inspectionChildDeviceData['inspection_child_device_id'] = $storeChildDeviceInfo['inspection_child_device_id'];
            }

            $inspectionChildDeviceData['inspection_store_child_id'] = $inspectionStoreChildId;
            $inspectionChildDeviceData['remark'] = $remark;
            $inspectionChildDeviceData['device_id'] = $res;
            $inspectionChildDeviceData['source'] = 2;
            $inspectionChildDeviceData['create_time'] = date('Y-m-d H:i:s');
            $inspectionChildDeviceData['update_time'] = date('Y-m-d H:i:s');
            $chileDeviceID = (new WechatInspectionStoreChildDeviceModel())->editData($inspectionChildDeviceData);
        }

        $returnData['inspectionChildDeviceId'] = $chileDeviceID;
        $returnData['inspectionStoreChildId'] = $inspectionStoreChildId;
        $returnData['deviceId'] = $res;
        $this->ajaxReturn(['status'=>1,'data'=>$returnData]);
    }

    /**
     * 删除设备 巡检中操作调用
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function delDeviceForInspectionIng(){

        $deviceId = explode(":",I('deviceId'))[1];
        $inspectionChildDeviceId = explode(":",I('inspectionChildDeviceId'))[1];

        //参数过滤
        if(empty($deviceId)){
            $this->ajaxReturn(['status'=>0,'msg'=>'参数错误！']);
        }

        $return = (new InspectionService())->delDeviceForInspectionIng($deviceId,$inspectionChildDeviceId);

        $this->ajaxReturn(['status'=>1,'msg'=>'成功！']);
    }

    /**
     * 显示添加新设备页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showInspectionAddDevicePage(){

        $deviceType = (new WechatEquipmentCategoryModel)->where(['status'=>1])->select();

        $this->inspectionStoreChildId = I('inspectionStoreChildId');
        $this -> deviceType = $deviceType;
        $this -> deviceTypeJs = json_encode($deviceType);

        //获取新增设备详情
        $deviceMap['d.inspection_store_child_id'] = I('inspectionStoreChildId');
        $deviceMap['d.status'] = 3;//设备状态 1 启用 2 禁用 -1 删除 3 新增未启用
        $field = "d.device_name,d.brand,ec.id category_id,`name`";
        $deviceList = $this->WechatDeviceModel->getDeviceInfo($deviceMap,$field);
        $this->deviceLength = count($deviceList);
        $this->deviceList = $deviceList;
        $this->display('WechatPublic/storeMaintain/inspection/inspectionAddDevice');
    }

    /**
     * 巡检员提交发现的新设备
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function masterAddDevice(){

        $deviceData = I('deviceData');
        $inspectionStoreChildId = I('inspectionStoreChildId');

        if(!$deviceData && !$inspectionStoreChildId){
            $this->ajaxReturn(['status'=>0,'msg'=>'提交失败！请刷新后重试！']);
        }

        $where['wisc.inspection_store_child_id'] = $inspectionStoreChildId;
        $fields = 'wi.customer_service_id,wisc.inspector_id,wisc.store_id';
        $inspectionInfo = (new WechatInspectionStoreChildModel())->getInspectionChildInfo($where,$fields);
        $data = [];
        foreach($deviceData as $val){
            $tmpData = [];
            $tmpData['device_code'] = getDeviceCode($val['deviceName']);
            $tmpData['store_id'] = $inspectionInfo['store_id'];
            $tmpData['inspector_id'] = $inspectionInfo['inspector_id'];
            $tmpData['customer_service_id'] = $inspectionInfo['customer_service_id'];
            $tmpData['inspection_store_child_id'] = $inspectionStoreChildId;
            $tmpData['device_name'] = $val['deviceName'];
            $tmpData['brand'] = $val['deviceBrand'];
            $tmpData['category'] = $val['deviceCate'];
            $tmpData['is_operate'] = 1;
            $tmpData['status'] = 3;
            $tmpData['create_time'] = date("Y-m-d H:i:s");
            $tmpData['update_time'] = date("Y-m-d H:i:s");
            $data[] = $tmpData;
        }

        //先删除之前新添加的设备
        $delMap['inspection_store_child_id'] = $inspectionStoreChildId;
        $delMap['status'] = 3;//1 启用 2 禁用 -1 删除 3 新增未启用
        (new WechatDeviceModel())->where($delMap)->delete();
        $return = (new WechatDeviceModel())->addAll($data);

        if($return){

            $this->ajaxReturn(['status'=>1,'msg'=>'提交成功！']);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'提交失败，请稍后重试！']);
        }
    }

    /**
     * 巡检列表页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionOrderList(){
        if(IS_POST){

            $status = I('status');
            $workerUid = I('workerUid');
            if($status == INSPECTION_CHILDSTATUS_MASTER_FINISHORDER){

                $where['wisc.status'] = [
                    ['EQ',INSPECTION_CHILDSTATUS_STORE_OVERORDER],
                    ['EQ',INSPECTION_CHILDSTATUS_MASTER_FINISHORDER],
                    'OR'
                ];
            }else{
                $where['wisc.status'] = $status;
            }

            $where['wisc.inspector_id'] = $workerUid;

            $inspectionData = $this->inspectionService->getChildOrderList($where,'wisc.create_time desc');

            if(!empty($inspectionData)){
                $this->ajaxReturn(["status" => 1,"data" => $inspectionData]);
            }else{
                $this->ajaxReturn(["status" => 0,"data" => []]);
            }

        }else{
            //获取当前用户
            $userinfo = $this->getUserInfo();
            if(!$userinfo){
                throw_exception('您没有权限，不可访问！'); //需要优化 TODO
            }

            $where['wisc.status'] = INSPECTION_CHILDSTATUS_SUPERVISOR_YES_ORDER;
            $where['wisc.inspector_id'] = $userinfo['uid'];

            $inspectionData = $this->inspectionService->getChildOrderList($where,'wisc.create_time desc');

            $this->inspectionData = !empty($inspectionData) ? $inspectionData : 0;
            $this->workerUid = !empty($userinfo['uid']) ? $userinfo['uid'] : 0;

            //已派单
            $this->isSendOrder = 1;
            $this->display('WechatPublic/storeMaintain/inspection/inspectionOrderReceiving');
        }
    }


}