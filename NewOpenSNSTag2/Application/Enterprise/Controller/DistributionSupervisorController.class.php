<?php
/**
 * Created by PhpStorm.
 * User: pangyongfu
 * Date: 2018/1/3
 * Time: 16:55
 */

namespace Enterprise\Controller;
use Api\Controller\ApiController;
use Enterprise\Service\EnterpriseService;
use Enterprise\Service\InspectionService;
use Goods\Model\SomethingPicModel;
use Think\Controller;
use think\Cache;
use Wechat\Model\WechatDeviceModel;
use Wechat\Model\WechatFacilitatorModel;
use Wechat\Model\WechatInspectionModel;
use Wechat\Model\WechatInspectionStoreChildDeviceModel;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatKaStoresModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatServiceGuidelinesModel;
use Think\Log;
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;

class DistributionSupervisorController extends Controller{

    private $corpID;
    private $appSecret;
    private $token;
    private $encodingAesKey;
    private $svr;
    private $inspectionService;
    private $WechatServiceGuidelinesModel;
    private $WechatOrderModel;
    private $WechatPublicOrderService;
    private $WechatService;
    private $WechatMemberModel;


    function _initialize()
    {
        $this->corpID = COMPANY_CORPID;
        $this->appSecret = COMPANY_DISTRIBUTE_APPSECRET;
        $this->token = COMPANY_TOKEN;
        $this->encodingAesKey = COMPANY_EMCODINGASEKEY; //暂未设置 TODO
        $this->svr = (new EnterpriseService($this->encodingAesKey,$this->token,$this->corpID,$this->appSecret));
        $this->inspectionService = (new InspectionService($this->encodingAesKey,$this->token,$this->corpID,$this->appSecret));
//        parent::__construct();

        $this->WechatServiceGuidelinesModel = new WechatServiceGuidelinesModel();
        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatPublicOrderService = new WechatPublicOrderService();
        $this->WechatService = new WechatService();
        $this->WechatMemberModel = new WechatMemberModel();
    }

///////////////////////////////////////////////////////////////////////////客服管理开始////////////////////////////////////////////////////////////////////////////////////////////////
    private function getWxUserId(){
        //判断是否存在缓存userId
        $wxUserId = session('wxUserId');
//        $wxUserId = "huangqing";//测试使用

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
        // 2：门店消杀 3： 设备维修 4：设备清洗
        $wxUserId = $this->getWxUserId();
//        $wxUserId = 'huangqing';
        Log::write("获取wxcode".$wxUserId);
        $facidata = $this->WechatMemberModel->where(['isadmin'=>1,"status" => 1,"wx_code" => $wxUserId])->select();
        Log::write("获取用户信息".json_encode($facidata));
        return $facidata;
    }

    private function getUserInfoByType($user_type){
        //获取企业号用户标识wx_code，然后通过标识获取该用户对应的 服务商ID
        // 2：门店消杀 3： 设备维修 4：设备清洗
        $wxUserId = $this->getWxUserId();
//        $wxUserId = 'PangYongFu';
        Log::write("获取wxcode".$wxUserId);
        $facidata = $this->WechatMemberModel->where(['isadmin'=>1,"status" => 1,"wx_code" => $wxUserId,'type_user'=>$user_type])->find();
        Log::write("获取用户信息".json_encode($facidata));
        return $facidata;
    }

    /**
     * 获取维修员用户
     * @return mixed
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    private function getWorkerUserInfo(){
        //获取企业号用户标识wx_code，然后通过标识获取该用户对应的 服务商ID
        // 2：门店消杀 3： 设备维修 4：设备清洗
        $wxUserId = $this->getWxUserId();
//        $wxUserId = 'huangqing';
        $facidata = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"type_user"=>3,"wx_code" => $wxUserId])->find();
        return $facidata;
    }

    /**
     * 获取维修主管用户数据
     * @return mixed
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    private function getRepairSupervisorUserInfo(){
        //获取企业号用户标识wx_code，然后通过标识获取该用户对应的 服务商ID
        // 2：门店消杀 3： 设备维修 4：设备清洗
        $wxUserId = $this->getWxUserId();
//        $wxUserId = 'huangqing';
//        Log::write("获取wxcode".$wxUserId);
        $facidata = $this->WechatMemberModel->where(['isadmin'=>1,"status" => 1,"type_user"=>3,"wx_code" => $wxUserId])->select();

        Log::write("获取用户信息".json_encode($facidata));
        return $facidata;
    }
    /**
     * 未接单列表
     */
    public function noReceiveOrder(){

        $faciinfo= $this->getUserInfo();
        $faci_id = [];
        foreach($faciinfo as $fk => $fvalue){
            $faci_id[$fk] = $fvalue['facilitator_id'];
        }
        //获取派单中的订单列表
        $map['orde.order_state'] = 0;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收)
        $map['orde.facilitator_id'] = ['in',$faci_id];
        $field = "orde.id,orde.order_code,orde.facilitator_id,orde.create_time,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $this->orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,4);
        $this->display('WechatPublic/DistributionSupervisor/noReceiveOrder');
    }

    /**
     * 订单详情页
     * @author pangyongfu
     */
    public function orderDetail(){

        $this->getWxUserId();
        $type = I('type');
        $order_id = I('order_id');
        $is_enterprise = I('is_enterprise');
        $this->fid = I('fid');

        $wechatService = (new WechatService());
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        $map['orde.facilitator_id'] = ['eq',$this->fid];
        switch($type){
            case 1:
                //订单数据
                $field = "orde.*,item.equipment_name,item.brands_text,item.malfunction_text,item.after_sale_text,item.phone_solve_text,item.cancel_text,item.change_parts_text,item.parts_price,member.uid,member.name,member.phone,faci.id fid,faci.name fname,faci.phone fphone,item.mr_id,item.urgent_level";

                $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);

                if(empty($orderData)){
                    $this->display('WechatPublic/DistributionSupervisor/orderError');
                    exit;
                }
                if(empty($orderData['change_parts_text'])){
                    $orderData['change_parts_text'] = "无";
                }
                $len = mb_strlen($orderData['detailed_address'],"utf-8");
                if($len > 45){
                    $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,45,"utf-8")."...";
                }
                //获取图片数据
                $this->imgData = $this->WechatPublicOrderService->getOrderImg($order_id);
                //维修部位图片
                $this->afterImgData = $this->WechatPublicOrderService->getOrderImg($order_id,1);
                $this->orderData = $orderData;
                $this->is_enterprise = $is_enterprise;
                $this->display('WechatPublic/DistributionSupervisor/orderMaintainDetail');
                break;
            case 2:
                $field = "orde.*,item.worker_over_time,item.malfunction_text,item.store_area,item.store_scene,item.insect_species,item.old_order_id,item.old_order_code,item.after_sale_text,item.phone_solve_text,item.insect_time,item.difference_price,item.difference_text,item.difference_status,item.cancel_text,item.difference_status,item.difference_price,item.old_order_id,item.old_order_code,member.uid,member.name,member.phone,faci.id fid,faci.name fname,faci.phone fphone";
                $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
                //如果是年订单的主订单则直接跳转到年订单页面
                if(($orderData['order_state'] == 11 || $orderData['order_state'] == 3) && $orderData['is_year'] == 1 && $orderData['is_main'] == 1){
                    $this->showCleanKillYearOrder($order_id);
                    return true;
                }
                //检查生成订单支付数据
                $renturnData = $wechatService->checkOrderNoPay($orderData);

                $this -> getWechatJsSignPackage();

                if(empty($orderData)){
                    $this->display('WechatPublic/DistributionSupervisor/orderError');
                    exit;
                }
                if($orderData['order_state'] == '0' && !empty($orderData['renew_order_id'])){
                    $orderData['order_state_text'] = '续签中';
                }
                $len = mb_strlen($orderData['detailed_address'],"utf-8");
                if($len > 45){
                    $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,45,"utf-8")."...";
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
                $this->orderData = $orderData;
                $this->is_enterprise = $is_enterprise;
                $this->display('WechatPublic/DistributionSupervisor/orderCleanKillDetail');
                break;
            case 3:
                $field = "orde.*,item.store_area,item.store_scene,item.clean_type,item.last_clean_time,item.after_sale_text,item.petticoat_pipe,item.upright_flue_length,
                        item.across_flue_length,item.flue_round_num,item.purifier_slice_num,item.draught_fan_clean_num,item.fireproof_board_length,
                        item.draught_fan_chaixi_num,item.entirety_greasy_dirt,item.cancel_text,member.uid,member.name,member.phone,faci.id fid,faci.name fname,faci.phone fphone";
                $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
                if(empty($orderData)){
                    $this->display('WechatPublic/DistributionSupervisor/orderError');
                    exit;
                }
                $len = mb_strlen($orderData['detailed_address'],"utf-8");
                if($len > 45){
                    $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,45,"utf-8")."...";
                }
                $this->orderData = $orderData;
                //获取图片数据
                $this->imgBefore = $this->WechatPublicOrderService->getOrderImg($order_id);
                $this->imgAfter = $this->WechatPublicOrderService->getOrderImg($order_id,1);
                $this->is_enterprise = $is_enterprise;
                $this->display('WechatPublic/DistributionSupervisor/orderCleaningDetail');
                break;
        }
    }
    /**
     * 主管直接接单
     * @author pangyongfu
     */
    public function managerGetOrder(){

        $userInfo = $this->getUserInfo();
        $order_type = I('order_type');
        $data['id'] = I('id');
        $uid = NULL;
        foreach($userInfo as $k => $v){
            //1设备维修，2门店消杀，3设备清洗
            //1：门店用户 2：门店消杀 3： 设备维修 4：设备清洗 5：客服)
            if($order_type == 1 && $v['type_user'] == 3){
                $uid = $v['uid'];
                $dis_name = $v['name'];
                $dis_phone = $v['phone'];
                $dis_link_name = $v['link_name'];
                break;
            }elseif($order_type == 3 && $v['type_user'] == 4){
                $uid = $v['uid'];
                $dis_name = $v['name'];
                $dis_phone = $v['phone'];
                $dis_link_name = $v['link_name'];
                break;
            }elseif($order_type == 2 && $v['type_user'] == 2){
                $uid = $v['uid'];
                $dis_name = $v['name'];
                $dis_phone = $v['phone'];
                $dis_link_name = $v['link_name'];
            }
        }
//        主管接单
        $wxCode = $this->getWxUserId();
        //获取是否有该wxCode对应的该服务商下的师傅
        switch($order_type){
            case 1:
                $codescrect = COMPANY_MAINTAIN_APPSECRET;
                $type_user = 3;
                break;
            case 3:
                $codescrect = COMPANY_CLEANING_APPSECRET;
                $type_user = 4;
                break;
            default:
                $codescrect = COMPANY_CLEANKILL_APPSECRET;
                $type_user = 2;
                break;
        }
        //不是消杀订单才需要更新订单师傅信息或创建师傅
        if($order_type != 2){
            $masterData = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"wx_code" => $wxCode,'type_user'=>$type_user])->find();
//        1，判断是否有该主管对应的师傅，有就把该订单分配给师傅
            if(!empty($masterData)){
                //更新订单师傅ID和状态为已接单
                $masterId = $masterData['uid'];
            }else{
//        2，没有对应师傅就创建一条该主管对应的师傅，再分配给该师傅
                //拼凑师傅数据
                $orderInfo = $this->WechatOrderModel->find($data['id']);
                $master['wx_code'] = $wxCode;
                $master['type_user'] = $type_user;
                $master['name'] = $dis_name;
                $master['isadmin'] = 0;
                $master['link_name'] = $dis_link_name;
                $master['phone'] = $dis_phone;
                $master['facilitator_id'] = $orderInfo['facilitator_id'];
                $master['create_time'] = $master['update_time'] = date("Y-m-d H:i:s");
                $masterId = $this->WechatMemberModel->add($master);
            }
            //更新订单师傅ID和状态为已接单
            $data['workers_id'] = $masterId;
        }
        //更新订单师傅ID和状态为已接单
        $data['supervisor_id'] = $uid;
        $data['order_state'] = 1;//已接单
        $data['single_time'] = date("Y-m-d H:i:s");//接单时间
        $data['update_time'] = date("Y-m-d H:i:s");
        $res = $this->WechatOrderModel->save($data);
        if($res){
            if($order_type == 2){
                //主管接单则给主管发消息
//                $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
//                $param = $distributeService->getDistributeSendMsg($data['id'],7);//新工单提醒
//                $distributeService->sendMessage($param);//新工单提醒
                //给主管对应师傅发消息
//                $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,$codescrect);
//                $param = $enterpriseService->getMasterSendMsg($data['id'],1);//新工单提醒
//                $enterpriseService->sendMessage($param);//新工单提醒
            }else{
//                //主管接单则给主管发消息
//                $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
//                $param = $distributeService->getDistributeSendMsg($data['id'],8);//新工单提醒
//                $distributeService->sendMessage($param);//新工单提醒
                //给主管对应师傅发消息
                $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,$codescrect);
                $param = $enterpriseService->getMasterSendMsg($data['id'],1);//新工单提醒
                $enterpriseService->sendMessage($param);//新工单提醒
                //TODO 如果是领值订单，则通知领值系统
                $orderData = $this->WechatPublicOrderService->getOrderData($data['id']);
                Log::write("查询订单数据结果|||".json_encode($orderData));
                if(!empty($orderData['mr_id'])){
                    $this->WechatService->curlPatch($orderData['mr_id'],3,$orderData['money_total'],$orderData['door_price'],$orderData['service_price'],$orderData['parts_price']);
                }
            }
            //接单后发送给用户消息提醒
            $this->WechatService->sendMasterOrderMsgNew($data['id'],$data['workers_id']);

            $this->ajaxReturn(["status" => 1,"data" => $data['id']]);
        }
        $this->ajaxReturn(["status" => 0]);
    }

    /**
     * 分配给师傅
     * @author pangyongfu
     */
    public function updateCustomerOrder(){
        $data['id'] = I('id');
        $data['workers_id'] = I('uid');
        $userInfo = $this->getUserInfo();
        $data['order_state'] = 10;//未接单
        //验证改任务是否已经待支付，已完成 TODO
        $order = $this->WechatOrderModel->where(['id'=>$data['id']])->find();
        if(in_array($order['order_state'],[2,3,4,5,6,7,8,9])){
            $this->ajaxReturn(["status" => 0,"msg" => "该订单已被分配，请分配其他订单！"]);
        }
        $uid = NULL;
        foreach($userInfo as $uk => $uv){
            if($order['order_type'] == 1 && $uv['type_user'] == 3){
                $uid = $uv['uid'];
                break;
            }elseif($order['order_type'] == 3 && $uv['type_user'] == 4){
                $uid = $uv['uid'];
                break;
            }elseif($order['order_type'] == 2 && $uv['type_user'] == 2){
                $uid = $uv['uid'];
            }
        }
        $data['supervisor_id'] = $uid;
        //更新订单，绑定服务商
        $res = $this->WechatOrderModel->save($data);

        //主管分配给师傅，发消息提醒  1设备维修，2门店消杀，3设备清洗
        if($order['order_type'] == 1){
            $codescrect = COMPANY_MAINTAIN_APPSECRET;
        }elseif($order['order_type'] == 2){
            $codescrect = COMPANY_CLEANKILL_APPSECRET;
        }else{
            $codescrect = COMPANY_CLEANING_APPSECRET;
        }
        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,$codescrect);
        $param = $enterpriseService->getMasterSendMsg($data['id'],1);//新工单提醒
        $enterpriseService->sendMessage($param);//新工单提醒

        $this->ajaxReturn(["status" => 1,"data" => $res]);

    }
    /**
     * 分配任务页
     * @author pangyongfu
     */
    public function distributeTask(){
        $this->getWxUserId();
        $this->order_id = I('order_id');
        $order_type = I('order_type');//1设备维修，2门店消杀，3设备清洗
        $fid = I('fid');//服务商ID
        //根据服务类型获取对应服务类型的服务商列表，并统计该服务商现有的任务数
        if($order_type == 1){
            $type_user = 3;
        }elseif($order_type == 3){
            $type_user = 4;
        }else{
            $type_user = $order_type;
        }
        $map['type_user'] = $type_user;
        $map['isadmin'] = 0;//不是主管
        $map['status'] = 1;//1--启用
        $map['facilitator_id'] = $fid;//服务商
        $map['order_type'] = $order_type;

        $this->masterData = $this->WechatMemberModel->getMasterData($map);

        $this->display('WechatPublic/DistributionSupervisor/distributeTask');
    }
    /**
     * 已接单列表
     */
    public function yesReceiveOrder(){

        //首次访问该页面默认显示未接单数据
        $faciinfo = $this->getUserInfo();
        $faci_id = [];
        foreach($faciinfo as $fk => $fvalue){
            $faci_id[$fk] = $fvalue['facilitator_id'];
        }
        //获取派单中的订单列表
        $map['orde.facilitator_id'] = ['in',$faci_id];
        $map['orde.is_main'] = 1;
        $map['orde.order_state'] = 10;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        $field = "orde.id,orde.order_code,orde.is_year,orde.order_state,orde.is_main,orde.store_name,orde.facilitator_id,orde.create_time,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,4);
        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/DistributionSupervisor/yesReceiveOrder');
    }

    /**
     * 根据订单状态获取对应数据
     * @author pangyongfu
     */
    public function getOrderDataByStatus(){
        $status = I('status');
        $map['orde.order_state'] = $status;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        //首次访问该页面默认显示未接单数据
        $faciinfo = $this->getUserInfo();
        $faci_id = [];
        foreach($faciinfo as $fk => $fvalue){
            $faci_id[$fk] = $fvalue['facilitator_id'];
        }
        //当前服务商id（数组格式）
        $nowFacInfo = $faci_id;
        if($status == 2){
            $map['orde.order_state'] = ['in',[2,8,9,11,13]];
        }elseif($status == 3){
            $map['orde.order_state'] = ['in',[3,4,5,7,11,12]];
            //递归获取更换后的服务商
            @file_put_contents("huangqing.log","更换前的服务商：".json_encode($faci_id)."\r\n",FILE_APPEND);
            $faci_id = $this->getNewFacId($faci_id);
            @file_put_contents("huangqing.log","更换后的服务商：".json_encode($faci_id)."\r\n",FILE_APPEND);
        }
        //获取派单中的订单列表
        $map['orde.facilitator_id'] = ['in',$faci_id];
        $map['orde.is_main'] = 1;
        $field = "orde.id,orde.order_code,orde.is_year,orde.order_state,orde.is_main,orde.store_name,orde.facilitator_id,orde.create_time,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,4);

        if(!empty($orderData)){
            foreach($orderData as $key=>&$val){
                if($status == 3 && $val['order_type'] == 2 && in_array($val['order_state'],[3,11])){
                    //获取当前主管对应的消杀服务商id
                    $cleankillInfo = $this->getUserInfoByType(2)['facilitator_id'];
                    //获取年订单更换日志中的旧服务商
                    $oldFaciIdStr = M('wechat_changefac_log')->where(['order_id'=>$val['id']])
                        ->field("GROUP_CONCAT(DISTINCT old_fac_id) old_fac_id")
                        ->find()['old_fac_id'];
                    $oldFaciIdArr = explode(',',$oldFaciIdStr);
                    //当前年订单的服务商不是现在要查看列表的服务商
                    if(in_array($cleankillInfo,$oldFaciIdArr) && !in_array($val['facilitator_id'],$nowFacInfo)){
                        $val['order_state'] = 3;
                    }elseif($cleankillInfo == $val['facilitator_id'] && $val['order_state'] != 11){
                        $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
                        continue;
                    }else{
                        unset($orderData[$key]);
                    }
                }
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
            $orderData = array_values($orderData);
            @file_put_contents("huangqing.log","dingdanshuju：".json_encode($orderData)."\r\n",FILE_APPEND);
            $this->ajaxReturn(["status" => 1,"data" => $orderData]);
        }
        $this->ajaxReturn(["status" => 0,"msg" => "该状态下无订单数据"]);
    }
    /**
     * 获取
     */
    private function getNewFacId(&$fac_id){
        $newFacArr = M('wechat_changefac_log')->where(['old_fac_id'=>['in',$fac_id]])
            ->field("order_id,new_fac_id")
            ->select();
        //递归获取最终更换的服务商
        foreach($newFacArr as $newFac){
            //如果获取的新服务商不在已获取服务商里就放进去
            if(!in_array($newFac['new_fac_id'],$fac_id)){
                $fac_id[] = $newFac['new_fac_id'];
            }
            //根据新服务商获取是否还有已分配的服务商
            $fac_id_new = $this->getFinalFac($newFac['order_id'],$newFac['new_fac_id'],$fac_id);
            if(!in_array($fac_id_new,$fac_id)){
                $fac_id[] = $fac_id_new;
            }
        }
        return $fac_id;
    }
    //递归获取新服务商
    private function getFinalFac($orderId,$facId,$fac_id){
        //获取当前新服务商是否还有对应更换服务商记录
        $newFac = M('wechat_changefac_log')->where(['old_fac_id'=>$facId,'order_id'=>$orderId,'new_fac_id'=>['not in',$fac_id]])
            ->field("order_id,new_fac_id")
            ->find();
        if(!empty($newFac)){
            return $this->getFinalFac($newFac['order_id'],$newFac['new_fac_id']);
        }else{
            return $facId;
        }
    }
///////////////////////////////////////////////////////////////////////////主管端接单优化////////////////////////////////////////////////////////////////////////////////////////////////
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
     * 已接单列表
     */
    public function showOrderList(){

        //首次访问该页面默认显示未接单数据
        $faciinfo = $this->getUserInfo();
        $faci_id = [];
        foreach($faciinfo as $fk => $fvalue){
            $faci_id[$fk] = $fvalue['facilitator_id'];
        }
        $uidData = $this->getDistributeMasterAll($faci_id);
        //获取派单中的订单列表
        $map['orde.facilitator_id'] = ['in',$faci_id];
        $map['orde.workers_id'] = ['in',$uidData];
        $map['orde.order_state'] = 10;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        $map['orde.order_type'] = ['in',[1,3]];//1设备维修，2门店消杀，3设备清洗 TODO 主管端目前只能操作维修和清洗订单
        $map['orde.is_main'] = 1;//是主订单

        $field = "orde.id,orde.order_code,orde.order_state,orde.store_name,orde.facilitator_id,orde.create_time,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,5);
        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/DistributionSupervisor/showOrderList');
    }

    /**
     * 根据订单状态获取对应数据
     * @author pangyongfu
     */
    public function getDisOrderDataByStatus(){
        $status = I('status');
        $map['orde.order_state'] = $status;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        if($status == 2){
            $map['orde.order_state'] = ['in',[2,9]];
        }
        if($status == 3){
            $map['orde.order_state'] = ['in',[3,4,5,7]];
        }
        //首次访问该页面默认显示未接单数据
        $faciinfo = $this->getUserInfo();
        $faci_id = [];
        foreach($faciinfo as $fk => $fvalue){
            $faci_id[$fk] = $fvalue['facilitator_id'];
        }
        $uidData = $this->getDistributeMasterAll($faci_id);
        //获取派单中的订单列表
        $map['orde.facilitator_id'] = ['in',$faci_id];
        $map['orde.workers_id'] = ['in',$uidData];
        $map['orde.order_type'] = ['in',[1,3]];//1设备维修，2门店消杀，3设备清洗 TODO 主管端目前只能操作维修和清洗订单

        $field = "orde.id,orde.order_code,orde.order_state,orde.store_name,orde.facilitator_id,orde.create_time,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field,5);

        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
            $this->ajaxReturn(["status" => 1,"data" => $orderData]);
        }
        $this->ajaxReturn(["status" => 0,"msg" => "该状态下无订单数据"]);
    }
    /**
     * 获取该主管对应的相应类型的师傅
     */
    private function getDistributeMaster($master_type){
        $wxUserId = $this->getWxUserId();
//        $wxUserId = 'pangshifu';
        $facidata = $this->WechatMemberModel->where(['isadmin'=>0,"status" => 1,"wx_code" => $wxUserId,'type_user'=>$master_type])->find();
        return $facidata;

    }

    /**
     * 获取该主管对应的相应类型的师傅
     */
    private function getDistributeMasterAll($faci_id){
        $wxUserId = $this->getWxUserId();//2：门店消杀 3： 设备维修 4：设备清洗
//        $wxUserId = 'pangshifu';
        $uiddata = $this->WechatMemberModel->field('GROUP_CONCAT(uid) uid')->where(['isadmin'=>0,"status" => 1,"wx_code" => $wxUserId,'type_user'=>['in',[3,4]],'facilitator_id'=>['in',$faci_id]])->find();
        $uiddata = explode(',',$uiddata['uid']);
        return $uiddata;

    }
    /**
     * 主管操作师傅端维修订单详情页
     */
    public function maintainOrderDetail($order_id,$is_enterprise = 0){
        $wechatService = (new WechatService());
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        //获取主管对应的师傅ID
        $masterInfo = $this->getDistributeMaster(3); //2：门店消杀 3： 设备维修 4：设备清洗
        $map['orde.workers_id'] = $masterInfo['uid'];//获取该师傅的工单
//        $map['orde.workers_id'] = 81;//获取该师傅的工单

        //订单数据
        $field = "orde.*,item.equipment_name,item.brands_text,item.after_sale_text,item.phone_solve_text,item.malfunction_text,item.cancel_text,item.change_parts_text,item.parts_price,member.uid,member.name,member.phone,item.mr_id,item.urgent_level";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        if(empty($orderData)){
            $this->display('WechatPublic/DistributionSupervisor/orderError');
            exit;
        }

        //检查生成订单支付数据
        $renturnData = $wechatService->checkOrderNoPay($orderData);

        $this -> getWechatJsSignPackage();

        if(empty($orderData['change_parts_text'])){
            $orderData['change_parts_text'] = "无";
        }
        //图片数据
        $imgData = $this->WechatPublicOrderService->getOrderImg($order_id);
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

        $this->display('WechatPublic/DistributionSupervisor/maintainOrder/maintainOrderDetail');
    }

    /**
     * 维修完成页面
     */
    public function repaireSuccess(){
        $this->order_id = I('order_id');
        $this->order_code = I('order_code');
        $this->is_ka = I('is_ka');
        $this->is_change_price = I('is_change_price') ? I('is_change_price') : 0;

        //保留显示 默认上价格的功能 TODO
        if(I('is_change_price')){

        }

        $this->display('WechatPublic/DistributionSupervisor/maintainOrder/repaireSuccess');
    }
    public function cleaningOrderDetail($order_id,$is_enterprise = 0){
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        //获取主管对应的师傅ID
        $masterInfo = $this->getDistributeMaster(4); //2：门店消杀 3： 设备维修 4：设备清洗
        $map['orde.workers_id'] = $masterInfo['uid'];//获取该师傅的工单
//        $map['orde.workers_id'] = 77;//获取该师傅的工单
        //订单数据
        $field = "orde.*,item.store_area,item.store_scene,item.clean_type,item.last_clean_time,item.after_sale_text,item.petticoat_pipe,item.upright_flue_length,
        item.across_flue_length,item.flue_round_num,item.purifier_slice_num,item.draught_fan_clean_num,item.fireproof_board_length,
        item.draught_fan_chaixi_num,item.entirety_greasy_dirt,item.cancel_text,member.uid,member.name,member.phone";

        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        if(empty($orderData)){
            $this->display('WechatPublic/DistributionSupervisor/orderError');
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

        $this->display('WechatPublic/DistributionSupervisor/cleaningOrder/cleaningOrderDetail');
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
        $this->display('WechatPublic/DistributionSupervisor/cleaningOrder/inputCleaningPrice');
    }

    /**
     * 订单设置页面
     * @param $order_id
     * @param $old_order_code
     * @param $old_order_id
     * @param $fid 服务商ID
     * @param $order_type
     */
    public function cleanKillOrderSet($order_id='',$fid='',$order_type=2){

        $WechatPublicOrderService = new WechatPublicOrderService();

        //1.通过order_id查询服务商标识
        $orderData = (new WechatOrderModel())->info($order_id,'order_type,renew_order_id,workers_id,year_service_id,is_year,order_source,order_state,money_total');

        $order_type = $orderData['order_type'];//1设备维修，2门店消杀，3设备清洗

        //判断如果是续签订单，则将续签的第一次时间默认上
        if($orderData['renew_order_id']){
            $orderRenewData = (new WechatOrderModel())->info($orderData['renew_order_id'],'update_time');
        }

        //获取编辑时展示的内容
        $orderUpdateData = [];
        if($orderData['year_service_id']){

            $orderUpdateData = $WechatPublicOrderService->getYearOrderDetail(['service.id'=>$orderData['year_service_id']]);
            $this->orderUpdateData = json_encode($orderUpdateData);
        }

        //根据服务类型获取对应服务类型的服务商列表，并统计该服务商现有的任务数
        if($order_type == 1){
            $type_user = 3;
        }elseif($order_type == 3){
            $type_user = 4;
        }else{
            $type_user = $order_type;
        }
        $map['type_user'] = $type_user;
        $map['isadmin'] = 0;//不是主管
        $map['status'] = 1;//1--启用
        $map['facilitator_id'] = $fid;//服务商
        $map['order_type'] = $order_type;

        $this->masterData = $this->WechatMemberModel->getMasterData($map);
        $this->renewTime = $orderRenewData['update_time'] ? Date('Y-m-d',strtotime($orderRenewData['update_time'])) : "";
        $this->orderUpdateData = $orderUpdateData ? json_encode($orderUpdateData) : [];
        $this->orderId = $order_id;
        $this->order_source = $orderData['order_source'];
        $this->order_state = $orderData['order_state'];
        $this->money_total = $orderData['money_total'];
        $this->is_year = $orderData['is_year'];
        $this->fid = $fid;

        $this->display('WechatPublic/DistributionSupervisor/cleanKillOrderSet');
    }

    /**
     * 订单设置成功
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function orderSetSuccess(){

        $data = I('');
        Log::write("续签年服务时的数据|||".json_encode($data));

        $WechatPublicOrderService = new WechatPublicOrderService();

        $userInfo = $this->getUserInfo();
        //获取主订单信息
        $order = $this->WechatOrderModel->where(['id'=>$data['id']])->find();
        if($data['type'] == '单次服务'){
            $singleService['id'] = $data['id'];
            $singleService['appointed_service_time'] = date('Y-m-d',strtotime($data['one_time']));
            $singleService['workers_id'] = $data['people'];
            $singleService['money_total'] = $data['price'];
            //1.更新订单数据
            if($order['is_ka'] == 1){
                $state = PAY_STATUS_WAIT_MAKESURE;
                //给用户发送待确认消息
                (new WechatService())->sendDistributeCleanKillOrderSetMsg($data['id']);
            }else{
                $state = PAY_STATUS_NO_ORDER_RECEIVING;
            }
            $res = $WechatPublicOrderService -> updateYearServiceOrder($userInfo,$singleService,$state);

            //主管分配给师傅，发消息提醒  1设备维修，2门店消杀，3设备清洗
            if($order['order_type'] == 1){
                $codescrect = COMPANY_MAINTAIN_APPSECRET;
            }elseif($order['order_type'] == 2){
                $codescrect = COMPANY_CLEANKILL_APPSECRET;
            }else{
                $codescrect = COMPANY_CLEANING_APPSECRET;
            }
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,$codescrect);
            $param = $enterpriseService->getMasterSendMsg($data['id'],1);//新工单提醒
            $enterpriseService->sendMessage($param);//新工单提醒

            $this->ajaxReturn(["status" => 1,"data" => $data['id']]);
        }else{

            if(!empty($order['renew_order_id'])){
                $data['service_remain'] = $data['service_num'] - 1;
            }else{
                $data['service_remain'] = $data['service_num'];
            }

            //年服务的类型 目前只有消杀 2018年5月22日
            $data['category'] = 2;

            //年服务订单设置
            $returnId = $WechatPublicOrderService -> orderYearServiceSet($data);
            //修改年订单主单数据
            if($order['is_ka'] == 1){
                $state = PAY_STATUS_WAIT_MAKESURE;
                //给用户发送待确认消息
                (new WechatService())->sendDistributeCleanKillOrderSetMsg($data['id'],1);//是否是年服务
            }else{
                $state = PAY_STATUS_NO_PAY;
            }
            $WechatPublicOrderService -> updateYearServiceOrder($userInfo,$data,$state,'2',$returnId);
            if($order['is_ka'] != 1){
                //给用户发送待支付消息
                $is_change_price = 0;
                if($order['year_service_id']){
                    $is_change_price = 1;
                }
                (new WechatService())->sendMasterOrederFinishPayMoneyNew($data['id'],'storeCleanKill',$is_change_price);
            }
            //TODO 给用户发送支付消息

            if($returnId){
                $this->ajaxReturn(["status" => 1,"data" => $data['id']]);
            }
            $this->ajaxReturn(["status" => 0,"msg" => "设置失败,请稍后重试！"]);

        }
    }

    /**
     * 获取年服务订单详情
     * @param $order_id
     */
    public function showCleanKillYearOrder($order_id,$is_enterprise = 0){
        //拼凑条件
        $faciinfo= $this->getUserInfoByType(2)['facilitator_id'];
        //获取年订单的主订单信息
        $map['orde.id'] = ['eq',$order_id];
        $field = "orde.*,item.store_area,item.store_scene,item.insect_species,item.insect_time,item.after_sale_text,item.difference_price,item.difference_text,item.difference_status,item.old_order_id,item.old_order_code,item.cancel_text,item.difference_status,item.difference_price,member.uid,member.name,member.phone";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        //获取年订单的所有子订单的服务商
//        $childMap['is_year'] = 1;
//        $childMap['is_main'] = 0;
//        $childMap['year_service_id'] = $orderData['year_service_id'];
//        $faci_id = $this->WechatOrderModel->where($childMap)->field("GROUP_CONCAT(DISTINCT facilitator_id) facilitator_id")->find()['facilitator_id'];
//        $faci_id = explode(',',$faci_id);

        //获取年订单更换日志中的旧服务商
        $faci_id = M('wechat_changefac_log')->where(['order_id'=>$order_id])
            ->field("GROUP_CONCAT(DISTINCT old_fac_id) old_fac_id")
            ->find()['old_fac_id'];
        $faci_id = explode(',',$faci_id);
        
        if($orderData['facilitator_id'] != $faciinfo && !in_array($faciinfo,$faci_id) && !$is_enterprise){
            $this->display('WechatPublic/DistributionSupervisor/orderError');
            exit;
        }

        //如果当前服务商不是订单中的服务商 TODO 2019年3月4日19:51:16 HQ修改
        if($orderData['facilitator_id'] != $faciinfo){


            $orderData['order_state'] = 3;
            $orderData['order_state_text'] = "已完成";

            // 查询当前服务商是否还负责着年服务的 其中一个子订单
            if($orderData['year_service_id']){

                $WechatOrderModel = (new WechatOrderModel());
                $serviceTimeInfo = $WechatOrderModel->field('id,order_state')->where([
                    'year_service_id'=>$orderData['year_service_id'],
                    'is_main'=>0,
                    'facilitator_id'=>$faciinfo,
                    'order_state'=>['NOT IN',[PAY_STATUS_PAY_COMPLETION,PAY_STATUS_PAY_FAIL,PAY_STATUS_PAY_PHONE_OVER,PAY_STATUS_PAY_APPRAISE,PAY_STATUS_OUTDATE]]
                ])->find();

                //查询子订单的状态，如果是已完成，就标记订单状态为已完成，其他状态为进行中
                if($serviceTimeInfo){
                    $orderData['order_state'] = $serviceTimeInfo['order_state'];
                    $orderData['order_state_text'] = "服务中";
                }

            }

        }
        $this->orderData = $orderData;
        $this->is_enterprise = $is_enterprise;
        $this->fac_id = $faciinfo;
        $this->year_order_id = $order_id;
        $this->display('WechatPublic/DistributionSupervisor/showcleankillyearorder');
    }


    /**
     * 更改年服务某次的服务时间
     */
    public function changeServiceTime(){
        //接收参数
        $serviceTime = I('time');//要修改的时间
        $timeId = I('time_id');//某次的服务的ID
        $yearServiceId = I('year_id');//年服务ID
        $mainOrderId = I('main_id');//主订单ID
        $number = I('number');//主订单ID

        $serviceChangeTime = date('Y-m-d',strtotime($serviceTime));
        $time23 = date("H:i:s",strtotime("23:00:00"));
        if($serviceChangeTime  == date("Y-m-d") && date("H:i:s") >= $time23){
            $this->ajaxReturn(["status" => 0,"msg" => '当日23:00之后只能更改成次日订单']);
        }
        //判断时间（必须在服务期间内）
        $yearWhere['id'] = $yearServiceId;
        $serviceTimeDataYear = M('wechat_year_service')->where($yearWhere)->field('start_time,end_time')->find();
        $serviceStart = date('Y-m-d',strtotime($serviceTimeDataYear['start_time']));
        $serviceEnd = date('Y-m-d',strtotime($serviceTimeDataYear['end_time']));
        if($serviceChangeTime < $serviceStart || $serviceChangeTime > $serviceEnd){
            $this->ajaxReturn(["status" => 0,"msg" => '要修改的时间需在年服务时间内']);
        }
        //判断时间（不能大于下次服务时间并且不能小于上次服务时间）
        $minWhere['year_service_id'] = $yearServiceId;
        $minWhere['id'] = ['lt',$timeId];
        $serviceTimeDataMin = M('wechat_year_service_time')->where($minWhere)->field('service_time')->find();
        $serviceTimeDataMinTime = date('Y-m-d',strtotime($serviceTimeDataMin['service_time']));
        if($serviceChangeTime <= $serviceTimeDataMinTime){
            $this->ajaxReturn(["status" => 0,"msg" => '要修改的时间需大于上次服务的时间'.$serviceTimeDataMinTime]);
        }
        if($serviceChangeTime <= date("Y-m-d",strtotime("-1 day"))){
            $this->ajaxReturn(["status" => 0,"msg" => '要修改的时间需不小于当前的时间']);
        }
        $maxWhere['year_service_id'] = $yearServiceId;
        $maxWhere['id'] = ['gt',$timeId];
        $serviceTimeDataMax = M('wechat_year_service_time')->where($maxWhere)->field('service_time')->find();
        $serviceTimeDataMaxTime = date('Y-m-d',strtotime($serviceTimeDataMax['service_time']));
        if(!empty($serviceTimeDataMax) && $serviceChangeTime >= $serviceTimeDataMaxTime){
            $this->ajaxReturn(["status" => 0,"msg" => '要修改的时间需小于下次计划服务的时间'.$serviceTimeDataMaxTime]);
        }
        //修改时间
        $where['id'] = $timeId;
        $data['service_time'] = $serviceChangeTime;
        $data['is_send_caution'] = 0;//是否给用户发送过提醒消息（1：已发送 0：未发送）
        $data['update_time'] = date('Y-m-d H:i:s');
        $res = M('wechat_year_service_time')->where($where)->save($data);

        if($res){
            //todo 发送消息给用户
            $this->WechatService->sendMsgChangeOrderTime($mainOrderId,$serviceTime,$number);
            $this->ajaxReturn(["status" => 1,"data" => $mainOrderId]);
        }else{
            $this->ajaxReturn(["status" => 0,"msg" => '修改时间失败，请刷新重试！']);
        }
    }


    //--------------------------------------------------------巡检相关页面编写------------------------------------------------------------------------------------

    /**
     * 巡检主管主订单未派单列表页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionNoOrderReceivingList(){

        //获取当前用户
        $userinfo = $this->getRepairSupervisorUserInfo();
        if(!$userinfo){
            throw_exception('您没有权限，不可访问！');
        }

        $where['wi.facilitator_id'] = $userinfo[0]['facilitator_id'];
		$where['wi.inspection_status'] = 1;
        $inspectionData = $this->inspectionService->getMainInspectionOrderData($where,'');

        $this->inspectionData = !empty($inspectionData) ? $inspectionData : 0;

        //未派单
        $this->isSendOrder = 2;
        $this->display('WechatPublic/DistributionSupervisor/inspection/inspectionOrderReceiving');
    }

    /**
     * 巡检主管主订单已派单列表页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionYesOrderReceivingList(){

        //获取当前用户
        $userinfo = $this->getRepairSupervisorUserInfo();
        if(IS_POST){

            $where['wi.inspection_status'] = I('status');
            $where['wi.facilitator_id'] = $userinfo[0]['facilitator_id'];
            $where['wi.facilitator_uid'] = $userinfo[0]['uid'];
            $inspectionData = $this->inspectionService->getMainInspectionOrderData($where,'');

            if(!empty($inspectionData)){
                $this->ajaxReturn(["status" => 1,"data" => $inspectionData]);
            }else{
                $this->ajaxReturn(["status" => 0,"data" => []]);
            }

        }else{
            //获取当前用户
            $userinfo = $this->getRepairSupervisorUserInfo();
            if(!$userinfo){
                throw_exception('您没有权限，不可访问！'); //需要优化 TODO
            }

            $where['wi.inspection_status'] = 1;
            $where['wi.facilitator_id'] = $userinfo[0]['facilitator_id'];
            $where['wi.facilitator_uid'] = $userinfo[0]['uid'];

            $inspectionData = $this->inspectionService->getMainInspectionOrderData($where,'');

            $this->inspectionData = !empty($inspectionData) ? $inspectionData : 0;

            //已派单
            $this->isSendOrder = 1;
            $this->display('WechatPublic/DistributionSupervisor/inspection/inspectionOrderReceiving');
        }
    }

    /**
     * 巡检主订单详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionMainOrderDetail(){

        //获取当前用户
        $userinfo = $this->getRepairSupervisorUserInfo();
        if(!$userinfo){
            throw_exception('您没有权限，不可访问！');//需要优化 TODO
        }

        $inspectionId = I('inspection_id');

        //获取主单数据
        $where['wi.inspection_id'] = $inspectionId;
        $inspectionMainInfo = $this->inspectionService->getMainInspectionOrderDetail($where);

        if($userinfo[0]['facilitator_id'] != $inspectionMainInfo['facilitator_id']){
            throw_exception('服务商已更换，您无权限访问！');//需要优化 TODO
        }
        //获取主单中板绑定的门店数据
        $map['inspection_id'] = $inspectionId;
        $map['status'] = 1;//(1--启用；2--禁用；-1--删除)
        $inspectionMainStoreData = $this->inspectionService->getInspectionStoreOrderDetail($map);

        $inspectionMainInfo['storeData'] = $inspectionMainStoreData;

        //拼接订单状态值，支付方式
        $inspectionMainInfo['inspection_status_text'] = C('INSPECTION_MAINSTATUS')[$inspectionMainInfo['inspection_status']];
        $inspectionMainInfo['payment_text'] = C('INSPECTION_PAYMEN')[$inspectionMainInfo['payment']];

        $this->assign('userId',$userinfo[0]['uid']);
        $this->assign('inspectionMainInfo',$inspectionMainInfo);

        $this->display('WechatPublic/DistributionSupervisor/inspection/inspectionMainOrderDetail');
    }

    /**
     * 巡检主订单查看对应门店页面
     * @param $store_id
     * @param $ins_id
     */
    public function showMainOrderStoreList($store_id,$ins_id){

        //获取巡检订单门店列表
        $storeIdArr = explode(',',trim($store_id,","));
        $condition['id'] = ['in',$storeIdArr];
        $condition['status'] = 1;//1--启用；2--禁用；
        $storeList = (new WechatKaStoresModel())->where($condition)->select();
        $inspectionChildModel = new WechatInspectionStoreChildModel();
        //获取门店最近一次的巡检工单信息
        foreach($storeList as &$store){
            $map = [];
            $map['inspection_id'] = $ins_id;
            $map['store_id'] = $store['id'];
            $insData = $inspectionChildModel->where($map)->order("service_num desc")->field("service_num,status")->find();
            if(!empty($insData)){
                $store['service_num'] = $insData['service_num'];
                $store['service_status'] = C('INSPECTION_CHILDSTATUS')[$insData['status']];
            }
        }
        $this->storeList = $storeList;
        $this->storeNum = count($storeIdArr);
        $this->insId = $ins_id;
        $this->display('WechatPublic/DistributionSupervisor/inspection/showMainOrderStoreList');
    }

    /**
     * 巡检主管自己接受订单（子订单）
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionSupervisorOrderReceiving(){

        $inspectionChildId = I('inspectionChildId');
        $userWxCode = I('userWxCode');
        if(!$inspectionChildId || !$userWxCode){
            $this->ajaxReturn(['status'=>0,'msg'=>'接单失败，请稍后重试！']);
        }

        //1.通过获取的微信标识，查询是否有维修师傅数据
        $WechatMemberModel = (new WechatMemberModel());
        $memberData = $WechatMemberModel->where(['wx_code'=>$userWxCode,'type_user'=>3,'isadmin'=>0])->find();

        $masterId = '';
        $returnStatus = false;
        //2.如果没有师傅数据，则新增师傅数据
        if(!$memberData){

            //获取维修主管名称
            $supervisorData = $WechatMemberModel->where(['wx_code'=>$userWxCode,'status'=>1,'type_user'=>3,'isadmin'=>1])->field('link_name,phone,facilitator_id')->find();
            $addData['wx_code'] = $userWxCode;
            $addData['type_user'] = 3;
            $addData['isadmin'] = 0;
            $addData['name'] = $supervisorData['link_name'].'（维修员）';
            $addData['link_name'] = $supervisorData['link_name'].'（维修员）';
            $addData['status'] = 1;
            $addData['phone'] = $supervisorData['phone'];
            $addData['facilitator_id'] = $supervisorData['facilitator_id'];
            $addData['create_time'] = Date('Y-m-d H:i:s');
            $addData['update_time'] = Date('Y-m-d H:i:s');

            $masterId = $WechatMemberModel->add($addData);

            $returnStatus = $this->updateInspectionWorker($inspectionChildId,$masterId);

        }elseif($memberData['status'] != 1){

            //3.如果师傅存在，但状态为禁用或者删除，则返回页面提示主管
            $this->ajaxReturn(['status'=>0,'msg'=>'接单失败，请在后台启用您的师傅状态！']);

        }else{

            //4.如果师傅存在，则将单子分配的该师傅
            $returnStatus = $this->updateInspectionWorker($inspectionChildId,$memberData['uid']);
        }

        if($returnStatus == true){

            //给门店店长 发送师傅接单消息 TODO
            (new WechatService())->sendInsAcceptOrderMsg($inspectionChildId);

            //获取发送消息的数据
            $where['wisc.inspection_store_child_id'] = $inspectionChildId;
            $field = 'wisc.inspection_store_child_id,wisc.service_num,wisc.child_order_code,wisc.update_time,store.name storeName,wms.wx_code';
            $childOrder = (new WechatInspectionStoreChildModel())->getInspectionChildStoreMemberData($where,$field);

            //5.发送消息 可跳转至主管端的我的子订单
            $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
            $distributeService->sendInspectionSupervisorTakeOrdersMsg($childOrder[0]);//新工单提醒

            $this->ajaxReturn(['status'=>1,'msg'=>'接单成功！']);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'接单失败，请刷新后重试！']);
        }

    }

    /**
     * 修改巡检子订单师傅标识
     * @param $inspectionChildId
     * @param $workerId
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    private function updateInspectionWorker($inspectionChildId,$workerId){

        $saveData['inspector_id'] = $workerId;
        $saveData['update_time'] = Date('Y-m-d H:i:s');
        $saveData['status'] = INSPECTION_CHILDSTATUS_MASTER_TAKEORDERS;
        $returnData = (new WechatInspectionStoreChildModel())->where(['inspection_store_child_id'=>$inspectionChildId])->save($saveData);

        if($returnData !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 巡检主管接收巡检工单(主订单)
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionSupervisorReceptionOrder(){

        //获取当前用户

        $inspectionId = I('inspectionId');
        $userId = I('userId');
        $userinfo = $this->WechatMemberModel->where(['uid'=>$userId])->find();

        //过滤数据
        if(!$inspectionId || !$userinfo['uid']){
            $this->ajaxReturn(['status'=>0,'msg'=>'接单失败，请稍后重试！']);
        }

        //判断订单状态是否为服务中，过滤重复接单
        $orderInfo = (new WechatInspectionModel())->where(['inspection_id'=>$inspectionId])->field('inspection_id,inspection_status,facilitator_id,facilitator_uid')->find();

        if(!$orderInfo){
            $this->ajaxReturn(['status'=>0,'msg'=>'系统错误，请刷新重试！']);
        }

        //判断订单是否为自己公司的单子
        if($orderInfo['status'] == INSPECTION_MAINSTATUS_NEW){
            if($orderInfo['facilitator_id'] != $userinfo['facilitator_id']){
                $this->ajaxReturn(['status'=>0,'msg'=>'接单失败，您没有权限接收此单！']);
            }

        }elseif($orderInfo['status'] == INSPECTION_MAINSTATUS_INSERVICE){

            //判断单子是否被同公司的主管接收
            if(($orderInfo['facilitator_id'] == $userinfo['facilitator_id']) && ($orderInfo['facilitator_uid'] != $userinfo['uid'])){

                $memberInfo = (new WechatMemberModel())->where(['uid'=>$orderInfo['facilitator_uid']])->field('facilitator_id,link_name')->find();
                if($memberInfo['facilitator_id'] == $orderInfo['facilitator_id']){

                    $this->ajaxReturn(['status'=>0,'msg'=>'接单失败，订单已被【'.$memberInfo['link_name'].'】接收！']);
                }
            }
        }

        //修改数据
        $where['inspection_id'] = $inspectionId;
        $where['facilitator_uid'] = $userinfo['uid'];
        $where['inspection_status'] = INSPECTION_MAINSTATUS_INSERVICE;
        $return = (new WechatInspectionModel())->editData($where);

        if($return !== false){

            //发送接单成功消息 客服 TODO
            $enterpriseServiceOne = (new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET));
            $paramOne = $enterpriseServiceOne -> getCustomerInsSendMsg($inspectionId,3,true);
            $enterpriseServiceOne->sendMessage($paramOne);

            //发送接单成功消息 企业主管 TODO
            $enterpriseServiceTwo = (new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_ENTERPRISE_APPSECRET));
            $paramTwo = $enterpriseServiceTwo -> getEnterpriseInsSendMsg($inspectionId,1);
            $enterpriseServiceTwo->sendMessage($paramTwo);

            //调用生成子订单接口（异步）
            $returnThree = sendCreateInsChildOrder($inspectionId);

            $this->ajaxReturn(['status'=>1,'msg'=>'接单成功！']);
        }else{
            $this->ajaxReturn(['status'=>1,'msg'=>'接单失败，请稍后重试！']);
        }
    }

    /**
     * 巡检主订单查看某个门店的所有子订单
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function showChildOrderList(){
        $store_id = I('store_id');
        $ins_id = I('ins_id');;
        $start_time = !empty(I('start_time')) ? I('start_time') : '';
        $end_time = !empty(I('end_time')) ? I('end_time') : '';

        //获取巡检订单门店子订单列表
        $map["wisc.store_id"] = $store_id;
        $map["wisc.inspection_id"] = $ins_id;
        if($start_time && $end_time){
            $map["wisc.create_time"] = ["between",[$start_time." 00:00:00",$end_time." 23:59:59"]];
        }elseif($start_time){
            $map["wisc.create_time"] = ["egt",$start_time." 00:00:00"];
        }elseif($end_time){
            $map["wisc.create_time"] = ["elt",$end_time." 23:59:59"];
        }
        $field = "wisc.inspection_store_child_id,wisc.child_order_code,wisc.status,wisc.service_num,wisc.create_time,store.name store_name";
        $wechatInspectionStoreChildModel = (new WechatInspectionStoreChildModel());
        $childList = $wechatInspectionStoreChildModel->getChildList($map,$field);
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
        $this->store_id = $store_id;
        $this->ins_id = $ins_id;
        $this->startTime = I('start_time');
        $this->endTime = I('end_time');

        $this->display('WechatPublic/DistributionSupervisor/inspection/showChildOrderList');

    }


    /**
     * 巡检主管查看子订单详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionChildOrderDetail(){

        $inspection_store_child_id = I('inspection_store_child_id');

        //获取当前巡检主管数据
        $userinfo = $this->getRepairSupervisorUserInfo();

        if(!$userinfo || !$inspection_store_child_id){
            throw_exception('您没有权限，不可访问！');//需要优化 TODO
        }

        $childInfo = $this->inspectionService->getChildOrderInfoForChildId($inspection_store_child_id);

        $childInfo['status_text'] = C('INSPECTION_CHILDSTATUS')[$childInfo['status']];
        $childInfo['isNoMaster'] = (empty($childInfo['mentorName']) && empty($childInfo['mentorPhone'])) ? 2 : 1;

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

        //判断 如果当前订单状态大于等于 巡检员完成巡检  门店点击完成，就展示另外一个详情页面
        if($childInfo['status'] >= INSPECTION_CHILDSTATUS_MASTER_FINISHORDER){
            $this->showChildOrderDetailNew($inspection_store_child_id);
        }else {
            //提示页面数据来源
            $this->assign('isSource', 'supervisor');

            $this->userWxCode = $userinfo[0]['wx_code'];
            $this->assign('childInfo', $childInfo);
            $this->display('WechatPublic/DistributionSupervisor/inspection/inspectionChildOrderDetail');
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
     * 主管分配给师傅订单页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function distributeInspectionOrder(){

        //子订单标识
        $inspection_store_child_id = I('inspection_store_child_id');

        //获取当前巡检主管数据
        $userinfo = $this->getRepairSupervisorUserInfo();

        //判断这个巡检子单是否是当前巡检主管的单子
        if(!$userinfo || !$inspection_store_child_id){
            throw_exception('您没有权限，不可访问！');//需要优化 TODO
        }

        //获取子订单数据
        $childOrderInfo = (new WechatInspectionStoreChildModel())->where(['inspection_store_child_id'=>$inspection_store_child_id])->field('inspection_store_child_id,facilitator_id,inspection_supervisor_id,inspector_id')->find();

        //如果该订单没有被巡检主管接单，就提示错误
        if(!$childOrderInfo || !$childOrderInfo['inspection_supervisor_id']){
            throw_exception('您没有权限，不可访问！');//需要优化 TODO
        }

        //判断如果已经分配
//            if($childOrderInfo){
//
//            }

        //获取巡检主管下的师傅数据
        $wechatMemberModel = (new WechatMemberModel());
        $masterList = $wechatMemberModel->field('uid,facilitator_id,wx_code,name')->where(['isadmin'=>0,'facilitator_id'=>$childOrderInfo['facilitator_id'],'type_user'=>3,'status'=>1])->select();

        if(!$masterList){
            throw_exception('该服务商下没有绑定巡检员，请通知客服绑定！');//需要优化 TODO
        }


        $this->inspection_child_id = !empty($childOrderInfo['inspection_store_child_id']) ? $childOrderInfo['inspection_store_child_id'] : false;

        //是否已经选中了师傅
        $this->inspector_id = !empty($childOrderInfo['inspector_id']) ? $childOrderInfo['inspector_id'] : false;

        //师傅列表
        $this->masterList = !empty($masterList) ? json_encode($masterList,true) : false;

        $this->display('WechatPublic/DistributionSupervisor/inspection/distributeInspectionOrder');

    }

    /**
     * 设置子订单状态 主管派单师傅
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770+
     */
    public function setChildInspectorStatus(){
        $inspectionChildId = I('inspectionChildId');
        $uid = I('uid');

        if(!$inspectionChildId || !$uid){
            $this->ajaxReturn(['status'=>0,'msg'=>'服务器错误，请稍后重试！']);
        }

        //修改订单状态
        $saveData = [
            'inspector_id'=>$uid,
            'status'=>INSPECTION_CHILDSTATUS_SUPERVISOR_YES_ORDER,
        ];

        $return = (new WechatInspectionStoreChildModel())->where(['inspection_store_child_id'=>$inspectionChildId,])->save($saveData);

        if($return !== false){

            //发送消息给师傅 TODO
            $enterpriseService = (new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_MAINTAIN_APPSECRET));
            $param = $enterpriseService->getMasterInsSendMsg($inspectionChildId,1);
            $enterpriseService->sendMessage($param);

            $this->ajaxReturn(['status'=>1,'msg'=>'派单成功']);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'派单失败，请稍后重试！']);
        }

    }

    /**
     * 巡检主管子订单未派单列表页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionChildNoOrderReceivingList(){

        //获取当前用户
        $userinfo = $this->getRepairSupervisorUserInfo();
        if(!$userinfo){
            throw_exception('您没有权限，不可访问！');
        }

        //订单状态
        //1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；
        //4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成

        $where['wisc.inspection_supervisor_id'] = $userinfo[0]['uid'];
        $where['wisc.status'] = INSPECTION_CHILDSTATUS_SUPERVISOR_NO_ORDER;

        $field = "wisc.inspection_store_child_id,wisc.child_order_code,wisc.status,wisc.service_num,wisc.create_time,store.name store_name";
        $inspectionData = (new WechatInspectionStoreChildModel())->getChildList($where,$field);

        $this->inspectionData = !empty($inspectionData) ? $inspectionData : 0;

        //未派单
        $this->isSendOrder = 2;
        $this->display('WechatPublic/DistributionSupervisor/inspection/inspectionChildOrderReceiving');
    }

    /**
     * 巡检主管子订单已派单列表页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionChildYesOrderReceivingList(){

        //获取当前用户
        $userinfo = $this->getRepairSupervisorUserInfo();

        //订单状态
        //1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；
        //4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成
        if(IS_POST){

            if(!$userinfo){
                $this->ajaxReturn(["status" => 0,"data" => '您没有权限，不可访问！']);
            }

            //搜索已完成的订单  查询巡检员完成、门店确认完成
            if(I('status') == INSPECTION_CHILDSTATUS_MASTER_FINISHORDER){
                $where['wisc.inspection_supervisor_id'] = $userinfo[0]['uid'];
                $where['wisc.status'] = [INSPECTION_CHILDSTATUS_MASTER_FINISHORDER,INSPECTION_CHILDSTATUS_STORE_OVERORDER,'OR'];
            }else{
                $where['wisc.inspection_supervisor_id'] = $userinfo[0]['uid'];
                $where['wisc.status'] = I('status');
            }

            $inspectionData = $this->inspectionService->getChildOrderList($where,"wisc.create_time desc");

            if(!empty($inspectionData)){
                $this->ajaxReturn(["status" => 1,"data" => $inspectionData]);
            }else{
                $this->ajaxReturn(["status" => 0,"data" => []]);
            }

        }else{

            if(!$userinfo){
                throw_exception('您没有权限，不可访问！'); //需要优化 TODO
            }

            $where['wisc.inspection_supervisor_id'] = $userinfo[0]['uid'];
            $where['wisc.status'] = INSPECTION_CHILDSTATUS_SUPERVISOR_YES_ORDER;

            $inspectionData = $this->inspectionService->getChildOrderList($where,"wisc.create_time desc");

            $this->inspectionData = !empty($inspectionData) ? $inspectionData : 0;

            //已派单
            $this->isSendOrder = 1;
            $this->display('WechatPublic/DistributionSupervisor/inspection/inspectionChildOrderReceiving');
        }
    }

    /**
     * 我的巡检子订单列表
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function myInspectionChildOrderList(){
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
            $userinfo = $this->getWorkerUserInfo();

            if(!$userinfo){
                throw_exception('您没有权限，不可访问！'); //需要优化 TODO
            }

            $where['wisc.status'] = INSPECTION_CHILDSTATUS_MASTER_TAKEORDERS;
            $where['wisc.inspector_id'] = $userinfo['uid'];

            $inspectionData = $this->inspectionService->getChildOrderList($where,'wisc.create_time desc');

            $this->inspectionData = !empty($inspectionData) ? $inspectionData : 0;
            $this->workerUid = !empty($userinfo['uid']) ? $userinfo['uid'] : 0;

            //已派单
            $this->isSendOrder = 1;
            $this->display('WechatPublic/DistributionSupervisor/inspection/myInspectionChildOrderList');
        }
    }


}