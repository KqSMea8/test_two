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
use Enterprise\Service\InspectionService;
use Goods\Model\SomethingPicModel;
use Think\Controller;
use think\Cache;
use Wechat\Model\WechatDeviceModel;
use Wechat\Model\WechatEquipmentCategoryModel;
use Wechat\Model\WechatFacilitatorModel;
use Wechat\Model\WechatInspectionModel;
use Wechat\Model\WechatInspectionStoreChildDeviceModel;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatInspectionStoreModel;
use Wechat\Model\WechatKaEnterpriseModel;
use Wechat\Model\WechatKaStoresModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatMemberStoreModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatServiceGuidelinesModel;
use Think\Log;
use Wechat\Model\WechatYearServiceTimeModel;
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;

class CustomerServicesController extends Controller{

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
    private $WechatFacilitatorModel;
    private $WechatKaEnterpriseModel;
    private $WechatKaStoresModel;
    private $WechatInspectionModel;
    private $WechatInspectionStoreModel;
    private $WechatMemberStoreModel;
    private $inspectionService;
    private $WechatInspectionStoreChildModel;
    private $WechatInspectionStoreChildDeviceModel;
    private $WechatDeviceModel;


    function _initialize()
    {
        $this->corpID = COMPANY_CORPID;
        $this->appSecret = COMPANY_CUSTOMER_APPSECRET;
        $this->token = COMPANY_TOKEN;
        $this->encodingAesKey = COMPANY_EMCODINGASEKEY; //暂未设置 TODO
        $this->svr = (new EnterpriseService($this->encodingAesKey,$this->token,$this->corpID,$this->appSecret));
        $this->inspectionService = (new InspectionService($this->encodingAesKey,$this->token,$this->corpID,$this->appSecret));

//        parent::__construct();

        $this->WechatServiceGuidelinesModel = new WechatServiceGuidelinesModel();
        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatOrderItemModel = new WechatOrderItemModel();
        $this->WechatPublicOrderService = new WechatPublicOrderService();
        $this->WechatService = new WechatService();
        $this->WechatMemberModel = new WechatMemberModel();
        $this->WechatFacilitatorModel = new WechatFacilitatorModel();
        $this->WechatKaEnterpriseModel = new WechatKaEnterpriseModel();
        $this->WechatKaStoresModel = new WechatKaStoresModel();
        $this->WechatInspectionModel = new WechatInspectionModel();
        $this->WechatInspectionStoreModel = new WechatInspectionStoreModel();
        $this->WechatMemberStoreModel = new WechatMemberStoreModel();
        $this->WechatInspectionStoreChildModel = new WechatInspectionStoreChildModel();
        $this->WechatInspectionStoreChildDeviceModel = new WechatInspectionStoreChildDeviceModel();
        $this->WechatDeviceModel = new WechatDeviceModel();
    }

///////////////////////////////////////////////////////////////////////////客服管理开始////////////////////////////////////////////////////////////////////////////////////////////////
    private function getWxUserId(){
        //判断是否存在缓存userId
        $wxUserId = session('wxUserId');
//        $wxUserId = 'huangqing';

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
//        $wxUserId = 'PangYongFu';
        $facidata = $this->WechatMemberModel->where(['isadmin'=>2,"status" => 1,"wx_code" => $wxUserId])->find();
        return $facidata;
    }
    /**
     * 获取企业主管
     * @return mixed
     */
    private function getBusinessUserInfo(){
        //获取企业号用户标识wx_code，然后通过标识获取该用户对应的 服务商ID
        // 2：门店消杀 3： 设备维修 4：设备清洗
        $wxUserId = $this->getWxUserId();
//        $wxUserId = '13041037986';
        $facidata = $this->WechatMemberModel->where(['isadmin'=>6,"status" => 1,"wx_code" => $wxUserId])->find();
        return $facidata;
    }
    /**
     * 未接单列表
     */
    public function noReceiveOrder(){
        //获取派单中的订单列表
        $map['orde.order_state'] = 0;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收)
        $map['orde.is_year'] = 0;
        $map['orde.facilitator_id'] = 0;
        $field = "orde.id,orde.order_code,orde.create_time,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $this->orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field);
        $this->display('WechatPublic/customerServices/noReceiveOrder');
    }

    /**
     * 订单详情页
     * @author pangyongfu
     */
    public function orderDetail(){
        $type = I('type');
        $order_id = I('order_id');
        $is_enterprise = I('is_enterprise');
        switch($type){
            case 1:
                //拼凑条件
                $map['orde.id'] = ['eq',$order_id];
                //订单数据
                $field = "orde.*,item.equipment_name,item.brands_text,item.after_sale_text,item.malfunction_text,item.phone_solve_text,item.cancel_text,item.change_parts_text,item.parts_price,member.uid,member.name,member.phone,faci.id fid,faci.name fname,faci.phone fphone,item.mr_id,item.urgent_level";
                $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
                $len = mb_strlen($orderData['detailed_address'],"utf-8");
                if($len > 45){
                    $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,45,"utf-8")."...";
                }
                $this->orderData = $orderData;
                //获取图片数据
                $this->imgData = $this->WechatPublicOrderService->getOrderImg($order_id);
                //维修部位图片
                $this->afterImgData = $this->WechatPublicOrderService->getOrderImg($order_id,1);
                $this->is_enterprise = $is_enterprise;
                $this->display('WechatPublic/customerServices/orderMaintainDetail');
                break;
            case 2:
                //拼凑条件
                $map['orde.id'] = ['eq',$order_id];
                $field = "orde.*,item.worker_over_time,item.malfunction_text,item.store_area,item.store_scene,item.insect_species,item.old_order_id,item.old_order_code,item.after_sale_text,item.phone_solve_text,item.insect_time,item.difference_price,item.difference_text,item.difference_status,item.cancel_text,item.difference_status,item.difference_price,member.uid,member.name,member.phone,faci.id fid,faci.name fname,faci.phone fphone";
                $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
                //如果是年订单的主订单则直接跳转到年订单页面
                if(($orderData['order_state'] == 11 || $orderData['order_state'] == 3) && $orderData['is_year'] == 1 && $orderData['is_main'] == 1){
                    $this->showCleanKillYearOrder($order_id);
                    return true;
                }
                if($orderData['order_state'] == '0' && !empty($orderData['renew_order_id'])){
                    $orderData['order_state_text'] = '续签中';
                }
                $len = mb_strlen($orderData['detailed_address'],"utf-8");
                if($len > 45){
                    $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,45,"utf-8")."...";
                }
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
                $this->display('WechatPublic/customerServices/orderCleanKillDetail');
                break;
            case 3:
                $map['orde.id'] = ['eq',$order_id];
                $field = "orde.*,item.store_area,item.store_scene,item.clean_type,item.after_sale_text,item.phone_solve_text,item.last_clean_time,item.petticoat_pipe,item.upright_flue_length,
                        item.across_flue_length,item.flue_round_num,item.purifier_slice_num,item.draught_fan_clean_num,item.fireproof_board_length,
                        item.draught_fan_chaixi_num,item.entirety_greasy_dirt,item.cancel_text,member.uid,member.name,member.phone,faci.id fid,faci.name fname,faci.phone fphone";
                $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
                $len = mb_strlen($orderData['detailed_address'],"utf-8");
                if($len > 45){
                    $orderData['detailed_address'] = mb_substr($orderData['detailed_address'],0,20,"utf-8")."...";
                }
                $this->orderData = $orderData;
                //获取图片数据
                $this->imgBefore = $this->WechatPublicOrderService->getOrderImg($order_id);
                $this->imgAfter = $this->WechatPublicOrderService->getOrderImg($order_id,1);
                $this->is_enterprise = $is_enterprise;
                $this->display('WechatPublic/customerServices/orderCleaningDetail');
                break;
        }
    }

    /**
     * 分配任务页
     * @author pangyongfu
     */
    public function distributeTask(){
        $this->order_id = I('order_id');
        $order_type = I('order_type');//1设备维修，2门店消杀，3设备清洗
        $this->is_back = I('is_back') ? I('is_back') : 0;
        $this->fac_id = I('fac_id') ? I('fac_id') : 0;
        $this->fac_name = I('fac_name') ? I('fac_name') : '';
        $this->reason = I('reason') ? I('reason') : '';
        //根据服务类型获取对应服务类型的服务商列表，并统计该服务商现有的任务数 1：门店消杀，2：设备维修，3：设备清洗
        if($order_type == 1){
            $type_user = 2;
        }elseif($order_type == 2){
            $type_user = 1;
            $back_url = '/index.php?s=/Enterprise/CustomerServices/showCleanKillYearOrder/order_id/'.$this->order_id;
        }else{
            $type_user = $order_type;
            $back_url = host_url."";//todo 以后跳转清洗年订单
        }
        $map['type'] = $type_user;
        $map['`status`'] = 1;//1--启用
        $map['order_type'] = $order_type;////1设备维修，2门店消杀，3设备清洗

        $this->masterData = $this->WechatFacilitatorModel->getFacilitatorData($map);
        $this->back_url = $back_url;
        $this->display('WechatPublic/customerServices/distributeTask');
    }

    /**
     * 分配给服务商订单
     * @author pangyongfu
     */
    public function updateCustomerOrder(){
        $data['id'] = I('id');
        $data['facilitator_id'] = I('fid');
        $is_back = I('is_back') ?I('is_back') : 0;

        $data['update_time'] = date("Y-m-d H:i:s");
        //验证改任务是否已经待支付，已完成 TODO
        //更新订单，绑定服务商
        if($is_back){
            //如果是更换服务商，就获取一个服务商的主管
            $supervisor = $this->WechatMemberModel->where(['facilitator_id'=>$data['facilitator_id'],'status'=>1,'isadmin'=>1])->order("uid desc")->find();
            $data['supervisor_id'] = $supervisor['uid'];
            //记录更换服务商日志
            $changeLogData['order_id'] = $data['id'];
            $changeLogData['old_fac_id'] = I('fac_id');
            $changeLogData['old_fac_name'] = I('fac_name');
            $changeLogData['new_fac_id'] = I('fid');
            $changeLogData['new_fac_name'] = I('fname');
            $changeLogData['reason'] = I('reason');
            $changeLogData['change_time'] = date("Y-m-d");
            M('wechat_changefac_log')->add($changeLogData);
        }
        $res = $this->WechatOrderModel->save($data);
        //派单后给分配主管发消息提醒
        $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
        $param = $distributeService->getDistributeSendMsg($data['id'],1);//新工单提醒
        $distributeService->sendMessage($param);//新工单提醒
        if($is_back){
            //给用户发送更换服务商消息
            $this -> WechatService ->sendMsgChangeOrderFac($data['id']);
        }
        //给用户发送模板通知消息
//        $orderData = $this->WechatOrderModel->where(['id'=>$data['id']])->find();
//        $userData = $this->WechatMemberModel->where(['uid'=>$orderData['member_id']])->find();
//        if($userData && $orderData){
//            switch ($orderData['order_type']){
//                case 1 :
//                    $orderTypeText = '设备维修';
//                    $urlNew = 'http://opensns.lz517.com/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$orderData['id'];
//                    break;
//                case 2 :
//                    $orderTypeText = '门店消杀';
//                    $urlNew = 'http://opensns.lz517.com/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.$orderData['id'];
//                    break;
//                case 3 :
//                    $orderTypeText = '设备清洗';
//                    $urlNew = 'http://opensns.lz517.com/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$orderData['id'];
//                    break;
//                default : $orderTypeText = '上门服务'; break;
//            }
//
//            $remark = "尊敬的用户您好，您的订单 ".$orderData['order_code']." 正在配单中，请您耐心等待。";
//            $this -> WechatService -> sendPaiOrderMsg($userData['open_id'],'派单通知',$orderData['create_time'],$orderTypeText,$remark,$urlNew);
//        }

        $this->ajaxReturn(["status" => 1,"data" => $res]);

    }
    /**
     * 已接单列表
     */
    public function yesReceiveOrder(){

        //首次访问该页面默认显示未接单数据
        $map['orde.order_state'] = ['in',[0,10]];//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        $map['orde.facilitator_id'] = ['neq',0];
        $map['orde.is_main'] = 1;
        $field = "orde.id,orde.order_code,orde.is_year,orde.order_state,orde.is_main,orde.store_name,orde.create_time,orde.order_type,faci.id fid,faci.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field);
        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/customerServices/yesReceiveOrder');
    }

    /**
     * 根据订单状态获取对应数据
     * @author pangyongfu
     */
    public function getOrderDataByStatus(){
        $status = I('status');
        $map['orde.is_main'] = 1;
        $map['orde.order_state'] = $status;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        if($status == 2){
            $map['orde.order_state'] = ['in',[2,8,9,11]];
        }elseif($status == 3){
            $map['orde.order_state'] = ['in',[3,5,7,12]];
        }elseif($status == 10){
            $map['orde.order_state'] = ['in',[0,10]];//派单中和未接单的数据
            $map['orde.facilitator_id'] = ['neq',0];//已分配服务商
        }
        $field = "orde.id,orde.order_code,orde.is_year,orde.order_state,orde.is_main,orde.store_name,orde.create_time,orde.order_type,faci.id fid,faci.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getOrderDataByStatus($map,$field);

        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
            $this->ajaxReturn(["status" => 1,"data" => $orderData]);
        }
        $this->ajaxReturn(["status" => 0,"msg" => "该状态下无订单数据"]);
    }
    /**
     * 企业主管-根据订单状态获取对应数据
     * @author muqi
     */
    public function getEnterpriseOrderDataByStatus(){
        $userInfo = $this->getBusinessUserInfo();
        $enterpriseInfo = $this->WechatKaEnterpriseModel->getOnceData(['id'=>$userInfo['enterprise_id']]);
        $status = I('status');
        $map['orde.is_main'] = 1;
        $map['orde.order_state'] = $status;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        $map['orde.province'] = $enterpriseInfo['code'];
        if($status == 2){
            $map['orde.order_state'] = ['in',[2,8,9,11]];
        }elseif($status == 3){
            $map['orde.order_state'] = ['in',[3,5,7,12]];
        }elseif($status == 10){
            $map['orde.order_state'] = ['in',[0,10]];//派单中和未接单的数据
            $map['orde.facilitator_id'] = ['neq',0];//已分配服务商
        }
        $field = "orde.id,orde.order_code,orde.is_year,orde.order_state,orde.is_main,orde.store_name,orde.create_time,orde.order_type,faci.id fid,faci.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getEnterpriseOrderDataByStatus($map,$field);
        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
            $this->ajaxReturn(["status" => 1,"data" => $orderData]);
        }
        $this->ajaxReturn(["status" => 0,"msg" => "该状态下无订单数据"]);
    }

    /**
     * 客服取消订单
     * @author pangyongfu
     */
    public function cancelOrder(){
//拼凑更新数据
        $orderData['id'] = I('id');
        $orderData['order_state'] = I('order_state');
        $orderData['update_time'] = date("Y-m-d H:i:s");

        $condition['order_id'] = ['eq',I('id')];
        $orderItemData['cancel_text'] = I("cancel_text");
        $orderItemData['update_time'] = date("Y-m-d H:i:s");

        //更新订单
        $res_order = $this->WechatOrderModel->save($orderData);
        $item_res = false;
        if($res_order){
            $item_res = $this->WechatOrderItemModel->where($condition)->save($orderItemData);
        }
        if($item_res){

//            //客服取消订单给分配主管发消息提醒
//            $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
//            $param = $distributeService->getDistributeSendMsg($orderData['id'],2);//客服取消订单
//            $distributeService->sendMessage($param);//客服取消订单
//            //客服取消订单给师傅发消息提醒
//            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CLEANKILL_APPSECRET);
//            $param = $enterpriseService->getMasterSendMsg($orderData['id'],5);//新工单提醒
//            $enterpriseService->sendMessage($param);//新工单提醒
            //TODO 如果是领值订单，则通知领值系统
            $orderData = $this->WechatPublicOrderService->getOrderData($orderData['id']);
            if(!empty($orderData['mr_id'])){
                $this->WechatService->curlCancel($orderData['mr_id'],'cancel',$orderItemData['cancel_text']);
            }

            $this->ajaxReturn(["status" => 1,"data" => ["id" => $orderData['id']]]);
        }
        $this->ajaxReturn(["status" => 0,"data" => ["id" => $orderData['id']]]);
    }
///////////////////////////////////////////////////////////////////////////客服管理结束////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 获取年服务订单详情
     * @param $order_id
     */
    public function showCleanKillYearOrder($order_id,$is_enterprise = 0){
        //拼凑条件
        $map['orde.id'] = ['eq',$order_id];
        $field = "orde.*,item.store_area,faci.name facilitator_name,item.store_scene,item.insect_species,item.insect_time,item.after_sale_text,item.difference_price,item.difference_text,item.difference_status,item.old_order_id,item.old_order_code,item.cancel_text,item.difference_status,item.difference_price,member.uid,member.name,member.phone";
        $orderData = $this->WechatPublicOrderService->getOneOrderData($map,$field);
        if($orderData['order_state'] == '0' && !empty($orderData['renew_order_id'])){
            $orderData['order_state_text'] = '续签中';
        }
        //是否展示更换服务商按钮
        $showButton = false;
        //检验该年订单是否还有未生成的子订单
        $childWhere['order_id'] = 0;
        $childWhere['year_service_id'] = $orderData['year_service_id'];
        $childCount = (new WechatYearServiceTimeModel())->where($childWhere)->count();
        if($childCount > 0){
            $showButton = true;
        }
        $this->showButton = $showButton;
        $this->orderData = $orderData;
        $this->is_enterprise = $is_enterprise;
        $this->year_order_id = $order_id;
        $this->display('WechatPublic/customerServices/showcleankillyearorder');
    }
///////////////////////////////////////////////////巡检开始////////////////////////////////////////////////////////////
    /**
     * 巡检输入企业编码页面
     */
    public function inputCompanyCode(){
        $this->getWxUserId();
        $this->display("WechatPublic/customerServices/inspection/inputCompanyCode");
    }

    /**
     * 检验企业编码
     */
    public function checkCompanyCode(){
        $company_code = I('company_code');
        //根据企业编号获取企业信息
        $companyData = $this->WechatKaEnterpriseModel->getData(['code'=>$company_code]);
        if(empty($companyData)){
            $this->ajaxReturn(['status'=>0,'msg'=>'企业编号不存在']);
        }
        $this->ajaxReturn(['status'=>1,'msg'=>'验证成功']);
    }

    /**
     * 巡检订单信息填写页面
     * @param $company_code
     */
    public function inputInspectionInfo($company_code = "189133",$inspection_id = false){
        $this->getWxUserId();
        if($inspection_id){
            //获取巡检主工单信息
            $insMap['wi.inspection_id'] = $inspection_id;
            $insField = "wi.*,ent.id,ent.code,ent.name,ent.link_person,ent.link_phone";
            $this->companyData = $this->WechatInspectionModel->getInspectionOrderInfo($insMap,$insField);
            //获取主订单门店信息
            $storeMap['inspection_id'] = $inspection_id;
            $storeMap['status'] = 1;//1--启用；2--禁用；-1--删除
            $inspectionStoreInfo = $this->WechatInspectionStoreModel->where($storeMap)
                ->group("service_level")
                ->field("GROUP_CONCAT(store_id) store_id ,type service_type,cycle service_cycle,service_num_total service_num,service_money,service_level")
                ->order('service_level asc')
                ->select();
            $max_service_level = 1;
            foreach($inspectionStoreInfo as &$insStore){
                $length = count(explode(',',$insStore['store_id']));
                $insStore['store_desc'] = "已选".$length."家门店";
                $max_service_level = $insStore['service_level'];
            }
            $this->max_service_level = $max_service_level;
            $this->inspectionStoreInfo = json_encode($inspectionStoreInfo);
        }else{
            //根据企业编号获取企业信息
            $companyData = $this->WechatKaEnterpriseModel->getData(['code'=>$company_code]);
            $this->companyData = $companyData[0];
        }

        //获取服务商信息
        $failMap['type'] = 2;//1：门店消杀，2：设备维修，3：设备清洗
        $failMap['status'] = 1;
        $failField = "id,name";
        $this->failcatorData = $this->WechatFacilitatorModel->getData($failMap,$failField);
        $this->inspection_id = $inspection_id;
        $this->display("WechatPublic/customerServices/inspection/inputInspectionInfo");
    }
    /**
     * 获取门店列表
     */
    public function showStoreList($ent_code,$ent_id,$store_id = "",$checkd_store = "",$service_level = 1,$ins_id = "",$is_new = "new"){
        $store_name = trim(I("request.store_name"));
        if($store_name){
            $condition['name'] = ['like',"%{$store_name}%"];
        }
        $this->store_id = $store_id;
        $this->store_name = $store_name;
        $this->service_level = $service_level;
        $this->ins_id = $ins_id;
        $this->ent_code = $ent_code;
        $this->ent_id = $ent_id;
        $this->checkd_store = $checkd_store;
        $this->is_new = $is_new;

        $storeIdArr = explode(',',trim($store_id,","));
        if($ins_id){
            //如果是新模块则可以选择其余所有未选择门店
            if($is_new == "new"){
                if(!empty($checkd_store)){
                    $checkedStoreIdStr = trim($checkd_store,",");
                    $checkedStoreIdArr = explode(',',$checkedStoreIdStr);
                    $checkedStoreIdDiffArr = $checkedStoreIdArr;
                    if(!empty($storeIdArr)){
                        $checkedStoreIdDiffArr = array_diff($checkedStoreIdArr,$storeIdArr);
                    }
                    if(!empty($checkedStoreIdDiffArr)){
                        $condition['id'] = ['not in',$checkedStoreIdDiffArr];
                    }
                }
            }else{
                $condition['id'] = ['in',$storeIdArr];
            }
        }else{
            if(!empty($checkd_store)){
                $checkedStoreIdStr = trim($checkd_store,",");
                $checkedStoreIdArr = explode(',',$checkedStoreIdStr);
                $checkedStoreIdDiffArr = $checkedStoreIdArr;
                if(!empty($storeIdArr)){
                    $checkedStoreIdDiffArr = array_diff($checkedStoreIdArr,$storeIdArr);
                }
                if(!empty($checkedStoreIdDiffArr)){
                    $condition['id'] = ['not in',$checkedStoreIdDiffArr];
                }
            }
        }
        $condition['enterprise_id'] = $ent_id;
        $condition['status'] = 1;//1--启用；2--禁用；
        $this->storeList = $this->WechatKaStoresModel->where($condition)->select();
        $this->display("WechatPublic/customerServices/inspection/showStoreList");
    }
    /**
     * 校验门店是否绑定店长
     * @param $store_id
     */
    public function checkStoreBindShopOwnner($store_id){
        $userInfo = $this->checkStoreBind($store_id);
        if(!empty($userInfo)){
            $this->ajaxReturn(['status'=>1]);
        }
        $this->ajaxReturn(['status'=>0,'msg'=>"该门店未绑定店长！请先进行绑定"]);
    }
    /**
     * 校验门店是否绑定店长
     * @param $store_id
     */
    public function checkAllStoreBindShopOwnner($store_id){
        $store_id = trim($store_id,',');
        $storeIdArr = explode(',',$store_id);
        $store_name = "";
        foreach($storeIdArr as $store){
            $userInfo = $this->checkStoreBind($store);
            if(empty($userInfo)){
                $storeInfo = $this->WechatKaStoresModel->where(['id'=>$store])->field("id,name")->find();
                $store_name .= $storeInfo['name'] . ",";
            }
        }
        $store_name = trim($store_name,",");
        if(empty($store_name)){
            $this->ajaxReturn(['status'=>1]);
        }
        $this->ajaxReturn(['status'=>0,'msg'=>"门店【".$store_name."】未绑定店长！请先进行绑定"]);
    }

    /**
     * 校验门店是否绑定店长
     * @param $store_id
     * @return mixed
     */
    private function checkStoreBind($store_id){
        $where['store_id'] = $store_id;
        $userInfo = $this->WechatMemberStoreModel->where($where)->find();
        return $userInfo;
    }
    /**
     * 创建和更新巡检主订单
     */
    public function createInspectionOrder(){

        $data = I("post.");
        //接收主订单公共参数
        $uid = $this->getUserInfo()['uid'];
        $insData['service_start_time'] = $data['service_start_time'];
        $insData['service_end_time'] = $data['service_end_time'];
        $insData['service_price'] = $data['service_price'];
        $insData['payment'] = $data['payment'];//付费方式（1：季付；2：半年付；3：年付）
        $insData['facilitator_id'] = $data['facilitator_id'];
        //如果巡检主订单id存在则为更新，否则为添加
        if(isset($data['inspection_id']) && $data['inspection_id']){
            //获取主订单原始数据
            $oldInsData = $this->WechatInspectionModel->where(['inspection_id'=>$data['inspection_id']])->find();

            //更新巡检主订单
            $insData['inspection_id'] = $data['inspection_id'];
            $this->WechatInspectionModel->save($insData);

            //如果服务商已更换，就给新换的服务商发送消息
            if($data['facilitator_id'] != $oldInsData['facilitator_id']){
                $insData['inspection_status'] = 1;//1：新工单(服务商未接单)
                $insData['facilitator_uid'] = 0;//1：清空主管
                //给服务商发送巡检消息
                $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
                $param = $enterpriseService->getDistributeInsSendMsg($data['inspection_id'],1);//新工单提醒
                $enterpriseService->sendMessage($param);//新工单提醒
            }

            //获取主订单门店关联数据
            $storeMap['inspection_id'] = $data['inspection_id'];
            $storeMap['status'] = 1;//1--启用；2--禁用；-1--删除
            $insStoreData = $this->WechatInspectionStoreModel->where($storeMap)->group("service_level")
                ->field("GROUP_CONCAT(store_id) store_id,service_level")->select();
            //处理已有门店数据
            $insStoreNewData = [];
            foreach($insStoreData as $insStore){
                $insStoreNewData[$insStore['service_level']] = $insStore['store_id'];
            }
            //处理提交的要修改的门店数据
            $newInsStoreData = [];
            foreach($data['store_data'] as $storeVal){

                //更新主订单门店关联数据(原门店数据只能删除，可新增模块)
                if(isset($insStoreNewData[$storeVal['service_level']])){
                    //已有模块，则更新
                    $oldStoreArr = explode(',',$insStoreNewData[$storeVal['service_level']]);//已有模块绑定的门店
                    $newStoreArr = explode(',',$storeVal['store_id']);//提交的门店
                    //比较两个长度，等长则直接更新，不等长则获取差集(删除差集门店)，更新提交的门店数据
                    $oldLength = count($oldStoreArr);
                    $newLength = count($newStoreArr);
                    if($oldLength == $newLength){
                        //直接更新
                        $newStoreData['service_money'] = $storeVal['service_money'];
                        $newStoreMap['inspection_id'] = $data['inspection_id'];
                        $newStoreMap['service_level'] = $storeVal['service_level'];
                        $this->WechatInspectionStoreModel->where($newStoreMap)->save($newStoreData);
                    }else{
                        //取差集，删差集，更新新门店id
                        $diffStoreIdArr = array_diff($oldStoreArr,$newStoreArr);
                        //禁用门店
                        $newStoreData2['status'] = 2;
                        $newStoreMap2['inspection_id'] = $data['inspection_id'];
                        $newStoreMap2['service_level'] = $storeVal['service_level'];
                        $newStoreMap2['store_id'] = ['in',$diffStoreIdArr];
                        $this->WechatInspectionStoreModel->where($newStoreMap2)->save($newStoreData2);
                        //更新主订单门店关联表状态
                        $newStoreData3['service_money'] = $storeVal['service_money'];
                        $newStoreMap3['inspection_id'] = $data['inspection_id'];
                        $newStoreMap3['service_level'] = $storeVal['service_level'];
                        $newStoreMap2['store_id'] = ['in',$newStoreArr];
                        $this->WechatInspectionStoreModel->where($newStoreMap3)->save($newStoreData3);
                    }
                }else{
                    //无该模块，则添加
                    $storeIdArr = explode(",",$storeVal['store_id']);
                    foreach($storeIdArr as $storeId){
                        $storeTmp = [];
                        $storeTmp['inspection_id'] = $data['inspection_id'];
                        $storeTmp['store_id'] = $storeId;
                        $storeTmp['type'] = $storeVal['service_type'];//1：单次巡检；2：周期性巡检
                        $storeTmp['cycle'] = $storeVal['service_cycle'];
                        $storeTmp['service_num_total'] = $storeVal['service_num'];
                        $storeTmp['service_num_remain'] = $storeVal['service_num'];
                        $storeTmp['service_money'] = $storeVal['service_money'];
                        $storeTmp['service_level'] = $storeVal['service_level'];
                        $storeTmp['create_time'] = $storeTmp['update_time'] = date("Y-m-d H:i:s");
                        $newInsStoreData[] = $storeTmp;
                    }
                }
            }
            //批量添加主订单门店数据
            $this->WechatInspectionStoreModel->addAll($newInsStoreData);

            $this->ajaxReturn(['status'=>1,'info'=>"添加成功",'data'=>$data['inspection_id']]);
        }else{
            //添加巡检主订单
            $insData['inspection_code'] = getInspectionOrderCode("M");
            $insData['enterprise_id'] = $data['enterprise_id'];
            $insData['enterprise_name'] = $data['enterprise_name'];
            $insData['customer_service_id'] = $uid;
            $insData['create_time'] = $insData['update_time'] = date("Y-m-d H:i:s");;
            $res = $this->WechatInspectionModel->add($insData);
            //添加主订单门店关联数据
            $storeData = [];
            foreach($data['store_data'] as $storeVal){
                $storeIdArr = explode(",",$storeVal['store_id']);
                foreach($storeIdArr as $storeId){

                    $storeTmp = [];
                    $storeTmp['inspection_id'] = $res;
                    $storeTmp['store_id'] = $storeId;
                    $storeTmp['type'] = $storeVal['service_type'];//1：单次巡检；2：周期性巡检
                    $storeTmp['cycle'] = $storeVal['service_cycle'];
                    $storeTmp['service_num_total'] = $storeVal['service_num'];
                    $storeTmp['service_num_remain'] = $storeVal['service_num'];
                    $storeTmp['service_money'] = $storeVal['service_money'];
                    $storeTmp['service_level'] = $storeVal['service_level'];
                    $storeTmp['create_time'] = $storeTmp['update_time'] = date("Y-m-d H:i:s");
                    $storeData[] = $storeTmp;
                }
            }
            //批量添加主订单门店数据
            $this->WechatInspectionStoreModel->addAll($storeData);
            //给客服发送巡检消息
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
            $param = $enterpriseService->getCustomerInsSendMsg($res,1);//新工单提醒
            $enterpriseService->sendMessage($param);//新工单提醒
            //给服务商发送巡检消息
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
            $param = $enterpriseService->getDistributeInsSendMsg($res,1);//新工单提醒
            $enterpriseService->sendMessage($param);//新工单提醒

            $this->ajaxReturn(['status'=>1,'info'=>"修改成功",'data'=>$res]);
        }
    }
    /**
     * 巡检主订单详情页面
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function inspectionMainOrderDetail(){

        $inspectionId = I('inspection_id');
        $is_enterprise = I('is_enterprise');

        //获取主单数据
        $where['wi.inspection_id'] = $inspectionId;
        $inspectionMainInfo = $this->inspectionService->getMainInspectionOrderDetail($where);

        //获取主单中板绑定的门店数据
        $map['inspection_id'] = $inspectionId;
        $map['status'] = 1;//(1--启用；2--禁用；-1--删除)
        $inspectionMainStoreData = $this->inspectionService->getInspectionStoreOrderDetail($map);

        $inspectionMainInfo['storeData'] = $inspectionMainStoreData;

        //拼接订单状态值，支付方式
        $inspectionMainInfo['inspection_status_text'] = C('INSPECTION_MAINSTATUS')[$inspectionMainInfo['inspection_status']];
        $inspectionMainInfo['payment_text'] = C('INSPECTION_PAYMEN')[$inspectionMainInfo['payment']];

        $this->assign('inspectionMainInfo',$inspectionMainInfo);
        $this->assign('is_enterprise',$is_enterprise);
        $this->display('WechatPublic/customerServices/inspection/inspectionMainOrderDetail');

    }

    /**
     * 结束巡检
     */
    public function endInspectionOrder(){
        //接收参数，保存数据
        $data['inspection_id'] = I('inspection_id');
        $data['inspection_status'] = I('inspection_status');
        $this->WechatInspectionModel->save($data);
        $this->ajaxReturn(['status'=>1]);
    }

    /**
     * 巡检主订单列表
     * @param int $status 1：新工单(服务商未接单)；2：服务中(服务商已接单)；3：已取消；4：已完成
     */
    public function inspectionMainOrderList($status = 1){
        $map['wi.inspection_status'] = $status;
        $map['wis.status'] = 1;//1--启用；2--禁用；-1--删除
        $field = "count(*) store_num,wi.enterprise_name,wi.inspection_id,wi.inspection_code,wi.create_time";
        $this->inspectionList = $this->WechatInspectionModel->getInspectionOrderData($map,$field);
        $this->display('WechatPublic/customerServices/inspection/inspectionMainOrderList');
    }

    /**
     * 获取巡检主订单列表
     * @param int $status
     */
    public function getInspectionMainOrderList($status = 1){
        $map['wi.inspection_status'] = $status;
        $map['wis.status'] = 1;//1--启用；2--禁用；-1--删除
        $field = "count(*) store_num,wi.enterprise_name,wi.inspection_id,wi.inspection_code,wi.create_time";
        $inspectionList = $this->WechatInspectionModel->getInspectionOrderData($map,$field);
        if(empty($inspectionList)){
            $this->ajaxReturn(['status'=>0]);
        }
        $this->ajaxReturn(['status'=>1,'data'=>$inspectionList]);
    }

    /**
     * 展示主订单门店列表
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
        $this->display('WechatPublic/customerServices/inspection/showMainOrderStoreList');
    }

    /**
     * 展示子订单列表
     * @param $store_id
     * @param $ins_id
     */
    public function showChildOrderList($store_id,$ins_id,$start_time = "",$end_time = ""){
        $this->startTime = $start_time;
        $this->endTime = $end_time;
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
        $childList = $this->WechatInspectionStoreChildModel->getChildList($map,$field);
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
        $this->display('WechatPublic/customerServices/inspection/showChildOrderList');
    }

    /**
     * 子订单详情页
     * @param $inspection_store_child_id
     */
    public function showChildOrderDetail($inspection_store_child_id){
        //获取子订单详情
        $childInfo = $this->inspectionService->getChildOrderInfoForChildId($inspection_store_child_id);

        $childInfo['status_text'] = C('INSPECTION_CHILDSTATUS')[$childInfo['status']];
        $childInfo['isNoMaster'] = (empty($childInfo['storePerson']) && empty($childInfo['mentorPhone'])) ? 0 : 1;

        //处理巡检备注页面 图片数据
        $this->requirementsStatus = $childInfo['requirements_text'] ? 1 : 0;
        //查询是否存在巡检备注图片数据
        if($childInfo['requirements_text']){

            //查询巡检备注图片
            $imgData = (new WechatPublicOrderService())->getInspectionRequirementsImg($childInfo['inspection_store_child_id']);
            if($imgData){
                //截取拼接图片链接
                foreach($imgData as &$value){
                    $value['path'] = host_url.strstr($value['path'],'/');
                }
            }
            $this->pic = $imgData;
            $this->picNum = count($imgData) ? count($imgData) : 0;
        }

        //判断订单状态 1：服务商未派单；2：服务商已派单（巡检员未接单）3：巡检员已接单 4：开始巡检（服务商不可重复派单）
        //5：完成巡检（门店待确认）6：门店点击完成
        //订单状态为 待确认和已完成则展示订单设备详情
        if($childInfo['status'] == INSPECTION_CHILDSTATUS_MASTER_FINISHORDER || $childInfo['status'] == INSPECTION_CHILDSTATUS_STORE_OVERORDER){
            //TODO 获取子订单设备详情
            $map = ['inspection_store_child_id'=>$inspection_store_child_id];
            $field = "*";
            $list = $this->WechatInspectionStoreChildModel->serviceOneDetailList($map, '', $field);
            $memberInfo = $this->WechatMemberModel->getOnceInfo(['uid'=>$list['inspector_id']],'');
            $storeInfo = $this->WechatKaStoresModel->getOnceInfo(['id'=>$list['store_id']],'');
            $facilitatorInfo = $this->WechatFacilitatorModel->getOnceInfo(['id'=>$list['facilitator_id']],'');
            $childDeviceInfo = $this->WechatInspectionStoreChildDeviceModel->getInspectionStoreChildDeviceByInspectionStoreChildId(['inspection_store_child_id'=>$list['inspection_store_child_id']],'');
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
        }else{
            //提示页面数据来源
            $this -> assign('childInfo',$childInfo);
            $this->display('WechatPublic/customerServices/inspection/showChildOrderDetail');
        }
    }

    /**
     * 确认设备列表页面
     */
    public function confirmDeviceList($inspection_store_child_id){
        //获取子订单详情
        $childMap['wisc.inspection_store_child_id'] = $inspection_store_child_id;
        $field = "wke.name enterprise_name,wks.name store_name,wf.name fac_name,wm.name inspector_name,wm.phone inspector_phone";
        $this->childOrderDetail = $this->WechatInspectionStoreChildModel->getChildOrderDetailInfo($childMap,$field);

        //启用设备数据
        $isImport = true;
        $deviceMap['d.inspection_store_child_id'] = $inspection_store_child_id;
        $deviceMap['d.status'] = ['neq',3];//设备状态 1 启用 2 禁用 -1 删除 3 新增未启用
        $tmpDeviceData = $this->WechatDeviceModel->getDeviceInfo($deviceMap,'d.id');
        if(empty($tmpDeviceData)){
            //获取新增设备详情
            $deviceMap['d.status'] = 3;//设备状态 1 启用 2 禁用 -1 删除 3 新增未启用
            $field = "d.device_name,d.brand,ec.`name`,d.id,d.category,d.status";
            $this->deviceList = $this->WechatDeviceModel->getDeviceInfo($deviceMap,$field);
        }else{
            $isImport = false;//获取设备详情
            $deviceMap['d.status'] = ['in',[1,2,3]];//设备状态 1 启用 2 禁用 -1 删除 3 新增未启用
            $field = "d.device_name,d.brand,ec.`name`,d.id,d.category,d.status";
            $this->deviceList = $this->WechatDeviceModel->getDeviceInfo($deviceMap,$field);
        }
        $this->isImport = $isImport;
        $this->categoryList = (new WechatEquipmentCategoryModel())->where(['status'=>1])->select();
        $this->child_id = $inspection_store_child_id;
        $this->display('WechatPublic/customerServices/inspection/confirmDeviceList');
    }
    /**
     * 更新子订单新增设备状态
     * @param $inspection_store_child_id
     */
    public function updateDeviceStatus($child_id,$device_arr){
        //更新子订单新增设备信息
        foreach($device_arr as $device){
            $data = [];
            $data['id']          = $device[0];
            $data['device_name'] = $device[1];
            $data['brand']       = $device[2];
            $data['category']    = $device[3];
            $data['status']      = 1;//启用
            $this->WechatDeviceModel->save($data);
        }

        $this->ajaxReturn(['status'=>1]);
    }
}