<?php
namespace Wechat\Service;
use Enterprise\Service\EnterpriseService;
use Goods\Model\SomethingPicModel;
use Home\Model\PictureModel;
use think\Cache;
use think\Exception;
use think\Log;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatTicketModel;
use Wechat\Model\WechatTicketOrderModel;
use Wechat\Model\WechatYearServiceModel;
use Wechat\Model\WechatYearServiceTimeModel;

class WechatPublicOrderService{

    private $WechatOrderModel;
    private $WechatOrderItemModel;
    private $PictureModel;
    private $SomethingPicModel;
    private $WechatTicketOrderModel;
    private $WechatTicketModel;
    private $WechatMemberModel;
    private $WechatYearServiceModel;
    private $WechatService;
    public function __construct()
    {
        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatOrderItemModel = new WechatOrderItemModel();
        $this->PictureModel = new PictureModel();
        $this->SomethingPicModel = new SomethingPicModel();
        $this->WechatTicketOrderModel = new WechatTicketOrderModel();
        $this->WechatTicketModel = new WechatTicketModel();
        $this->WechatMemberModel = new WechatMemberModel();
        $this->WechatYearServiceModel = new WechatYearServiceModel();
        $this->WechatService = new WechatService();
    }

    /**
     * 生成订单
     * @param $orderType 订单来源 1：门店消杀 2：烟道清洗 3：设备维修
     */
    public function creatOrder($orderType){

        //验证期望上门时间
        $doortime = "";
        if(I("door_time")){
            $doortime = date("Y-m-d H:i:s",strtotime(I("door_time")));
            if($doortime < date("Y-m-d H:i:s")){
                return array('status'=>0,'msg'=>"期望服务时间需大于当前时间！");
            }
        }

        //先验证当前用户是否有待支付订单，有的话就提示相应信息
        $map['member_id'] = session("memId");//测试阶段先写死
//        $map['member_id'] = 2;//测试阶段先写死
        $map['order_state'] = 2;//待支付
        $waitPayOrder = $this->WechatOrderModel->where($map)->find();
        if(!empty($waitPayOrder)){
            return array('status'=>0,'msg'=>"您有待支付订单，请先支付后再预约新的服务！");
        }

        $orderCode = $this->getOrderCode();
        $memberId = session("memId");
//        $memberId = 18;
//        var_dump(I(''));die;
        $orderData = [
            "order_code" => $orderCode,
            "ka_store_id" => I("ka_store_id") ? I("ka_store_id") : 0,
            "order_state" => I("order_state"),//0:派单中;1:已接单;2:待支付;3:已支付;4:已取消;5:已电话解决;6:已退款
            "order_type" => I("order_type"),
            "member_id" => $memberId,//用户ID
//            "member_id" => 2,
            "province" => I("province"),
            "city" => I("city"),
            "latng" => I("latng"),
            "location_name" => I("location"),
            "store_name" => I("store_name"),
            "detailed_address" => I("detailed_address") ? I("detailed_address") : I("location"),
            "link_person" => I("link_person"),
            "link_phone" => I("link_phone"),
            "fixed_line" => I("fixed_line"),
            "door_price" => 0,//上门费默认20
            "service_price" => 0,//服务费
            "money_total" => I("money_total")+0,
            "create_time" => date("Y-m-d H:i:s"),
            "door_time" => $doortime,
            "update_time" => date("Y-m-d H:i:s"),
            "enterprise_name" => I('enterprise_name'),
            "is_ka" => I('is_ka')
        ];
        if(I("equipment_id") && $orderType == 1){
            $orderData['equipment_id'] = I("equipment_id");
        }
        $orderId = $this->WechatOrderModel->add($orderData);
//        //更新个人中心信息
//        if(!empty($memberId)){
//            $memberInfo = $this->WechatMemberModel->where(array('uid'=>$memberId))->find();
//            if(empty($memberInfo['location_name']) && empty($memberInfo['latng'])){
//                $memberData['uid'] = $memberId;
//                $memberData['store_name'] = I("store_name");
//                $memberData['address_name'] = I("detailed_address");
//                $memberData['location_name'] = I("location");
//                $memberData['latng'] = I("latng");
//                $memberData['name'] = I("link_person");
//                $this->WechatMemberModel->save($memberData);
//            }
//        }
        $res = false;
        //1设备维修，2门店消杀，3烟道清洗
        if($orderType==1 && $orderId){
            //添加图片数据
            $imageFile = I('imageData');
            if(!empty($imageFile)){
                foreach ($imageFile as $k => $v) {
                    if(empty($v)){
                        continue;
                    }
                    $tmp = [
                        'path'=>$v,
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

            //添加订单扩展数据
            $orderItemData = [
                "order_id" => $orderId,
                "equipment_name" => I("equipment_name"),
                "brands_text" => I("brands_text"),
                "malfunction_text" => I("malfunction_text"),
                "urgent_level" => I("urgent_level"),
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
            $res = $this->WechatOrderItemModel->add($orderItemData);

            }elseif($orderType==2 && $orderId){
            $orderItemData = [
                "order_id" => $orderId,
                "store_area" => I("store_area"),
                "store_scene" => I("store_scene"),
                "insect_species" => I("insect_species"),
                "insect_time" => I("insect_time"),
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
            $res = $this->WechatOrderItemModel->add($orderItemData);

        }elseif($orderId){
            $orderItemData = [
                "order_id" => $orderId,
                "store_area" => I("store_area"),
                "store_scene" => I("store_scene"),
                "clean_type" => I("clean_type"),
                "last_clean_time" => I("last_clean_time"),
                "style_of_cooking" => I("style_of_cooking"),
                "petticoat_pipe_length" => I("petticoat_pipe_length"),
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
            $res = $this->WechatOrderItemModel->add($orderItemData);
        }
        if($res){
            Log::write('创建订单成功,订单号为：'.$orderCode);
            //给客服端发消息提醒
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
            $param = $enterpriseService->getCustomerSendMsg($orderId,1);//新工单提醒
            $enterpriseService->sendMessage($param);//新工单提醒

            //给用户发送下单提醒
            (new WechatService())->sendCreateOrderMsg($orderId,$orderType);

            $res = array("status" => 1,"data" => ["id" => $orderId]);
        }else{
            Log::write('创建订单失败,订单号为：'.$orderCode);
            $res = array("status" => 0,"data" => ["id" => $orderId]);
        }
        return $res;
    }
    /**
     * 创建售后订单
     */
    public function createSHOrder($oldOrderData,$shOrder,$memberId,$masterSend = true,$distSend = true){
        $orderCode = $this->getOrderCode();
        $orderData = [
            "order_code" => $orderCode,
            "order_state" => $shOrder['order_state'],//0:派单中;1:已接单;2:待支付;3:已支付;4:已取消;5:已电话解决;6:已退款
            "order_type" => $oldOrderData['order_type'],
            "latng" => $oldOrderData['latng'],
            "location_name" => $oldOrderData['location_name'],
            "member_id" => $memberId,//用户ID
            "workers_id" => $shOrder['workers_id'],//师傅ID
            "facilitator_id" => $shOrder['facilitator_id'],//服务商ID
            "supervisor_id" => $shOrder['supervisor_id'],//分配主管ID
            "province" => $oldOrderData['province'],
            "city" => $oldOrderData['city'],
            "store_name" => $oldOrderData['store_name'],
            "detailed_address" => $oldOrderData['detailed_address'],
            "link_person" => $oldOrderData['link_person'],
            "link_phone" => $oldOrderData['link_phone'],
            "fixed_line" => $oldOrderData['fixed_line'],
            "door_price" => 0,//上门费默认20
            "service_price" => 0,//服务费
            "money_total" => 0,
            "create_time" => date("Y-m-d H:i:s"),
            "update_time" => date("Y-m-d H:i:s"),
            "enterprise_name" => $oldOrderData['enterprise_name'],
            "is_ka" => $oldOrderData['is_ka'],
            "is_fast" => $oldOrderData['is_fast'],
            "is_sh" => 1,//售后订单
        ];
        if($oldOrderData['order_type'] == 1){
            $orderData['equipment_id'] = $oldOrderData['equipment_id'];
        }
        $orderId = $this->WechatOrderModel->add($orderData);

        //1设备维修，2门店消杀，3烟道清洗
        if($oldOrderData['order_type']==1 && $orderId){

            //添加订单扩展数据
            $orderItemData = [
                "order_id" => $orderId,
                "equipment_name" => $oldOrderData['equipment_name'],
                "brands_text" => $oldOrderData['brands_text'],
                "malfunction_text" => $oldOrderData['malfunction_text'],
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];

        }elseif($oldOrderData['order_type']==2 && $orderId){
            $orderItemData = [
                "order_id" => $orderId,
                "store_area" => $oldOrderData['store_area'],
                "store_scene" => $oldOrderData['store_scene'],
                "insect_species" => $oldOrderData['insect_species'],
                "insect_time" => $oldOrderData['insect_time'],
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];

        }elseif($orderId){
            $orderItemData = [
                "order_id" => $orderId,
                "store_area" => $oldOrderData['store_area'],
                "store_scene" => $oldOrderData['store_scene'],
                "clean_type" => $oldOrderData['clean_type'],
                "last_clean_time" => $oldOrderData['last_clean_time'],
                "style_of_cooking" => $oldOrderData['style_of_cooking'],
                "petticoat_pipe_length" => $oldOrderData['petticoat_pipe_length'],
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
        }
        $orderItemData['after_sale_text'] = I('after_sale_text');
        $orderItemData['old_order_id'] = $oldOrderData['order_id_old'];
        $orderItemData['old_order_code'] = $oldOrderData['order_code'];
        $res = $this->WechatOrderItemModel->add($orderItemData);

        if($res){
            //给客服端发消息提醒
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
            $param = $enterpriseService->getCustomerSendMsg($orderId,4);//售后工单提醒
            $enterpriseService->sendMessage($param);//售后工单提醒
            //给主管端发消息提醒
            if($distSend){
                $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
                $param = $distributeService->getDistributeSendMsg($orderId,5);//售后工单提醒
                $distributeService->sendMessage($param);//售后工单提醒
            }
            //给师傅端发消息提醒
            if($masterSend){
                if($oldOrderData['order_type'] == 1){
                    $masterSecret = COMPANY_MAINTAIN_APPSECRET;
                }elseif($oldOrderData['order_type'] == 2){
                    $masterSecret = COMPANY_CLEANKILL_APPSECRET;
                }else{
                    $masterSecret = COMPANY_CLEANING_APPSECRET;
                }
                $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,$masterSecret);
                $param = $enterpriseService->getMasterSendMsg($orderId,8);//售后工单提醒
                $enterpriseService->sendMessage($param);//售后工单提醒
            }
            //给用户发消息
            $shRemark = "您申请的售后订单已生成，服务商将在平台规定时间内与您联系，请保持电话畅通，如有问题请联系餐讯网客服：18810250371。";
            (new WechatService())->sendCreateOrderMsg($orderId,$oldOrderData['order_type'],$shRemark);
            $res = array("status" => 1,"data" => ["id" => $orderId]);
        }else{
            Log::write('创建售后订单失败,订单号为：'.$orderCode);
            $res = array("status" => 0,"msg" => "售后订单创建失败，请重试");
        }
        return $res;
    }
    /**
     * 顾客再次下单
     */
    public function customerOrderAgain(){

        //验证期望上门时间
        $doortime = "";
        if(I("door_time")){
            $doortime = date("Y-m-d H:i:s",strtotime(I("door_time")));
            if($doortime < date("Y-m-d H:i:s")){
                return array('status'=>0,'msg'=>"期望服务时间需大于当前时间！");
            }
        }
        $order_type = I("order_type");
        //先验证当前用户是否有待支付订单，有的话就提示相应信息
        $map['member_id'] = session("memId");//测试阶段先写死
//        $map['member_id'] = 2;//测试阶段先写死
        $map['order_state'] = 2;//待支付
        $waitPayOrder = $this->WechatOrderModel->where($map)->find();
        if(!empty($waitPayOrder)){
            return array('status'=>0,'msg'=>"您有待支付订单，请先支付后再预约新的服务！");
        }
        //获取用户之前订单的师傅ID，服务商ID信息
        $condition['id'] = I('order_id');
        $oldOrderData = $this->WechatOrderModel->where($condition)->find();
        $orderCode = $this->getOrderCode();
        $memberId = session("memId");
        $orderData = [
            "order_code" => $orderCode,
            "order_state" => I("order_state"),//0:派单中;1:已接单;2:待支付;3:已支付;4:已取消;5:已电话解决;6:已退款
            "order_type" => $order_type,
            "member_id" => $memberId,//用户ID
            "workers_id" => $oldOrderData['workers_id'],//用户ID
            "facilitator_id" => $oldOrderData['facilitator_id'],//用户ID
            "supervisor_id" => $oldOrderData['supervisor_id'],//用户ID
            "province" => I("province"),
            "city" => I("city"),
            "latng" => I("latng"),
            "location_name" => I("location"),
            "store_name" => I("store_name"),
            "ka_store_id" => I("ka_store_id") ? I("ka_store_id") : 0,
            "detailed_address" => I("detailed_address"),
            "link_person" => I("link_person"),
            "link_phone" => I("link_phone"),
            "fixed_line" => I("fixed_line"),
            "door_price" => 0,//上门费默认20
            "service_price" => 0,//服务费
            "money_total" => I("money_total")+0,
            "create_time" => date("Y-m-d H:i:s"),
            "door_time" => $doortime,
            "update_time" => date("Y-m-d H:i:s"),
            "enterprise_name" => I('enterprise_name'),
            "is_ka" => I('is_ka'),
            "is_fast" => 1,//快捷订单
        ];
        if(I("equipment_id")){
            $orderData['equipment_id'] = I("equipment_id");
        }
        $orderId = $this->WechatOrderModel->add($orderData);

        $res = false;
        if($orderId){
            //添加图片数据
            $imageFile = I('imageData');
            if(!empty($imageFile)){
                foreach ($imageFile as $k => $v) {
                    if(empty($v)){
                        continue;
                    }
                    $tmp = [
                        'path'=>$v,
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

            //添加订单扩展数据
            $orderItemData = [
                "order_id" => $orderId,
                "equipment_name" => I("equipment_name"),
                "brands_text" => I("brands_text"),
                "malfunction_text" => I("malfunction_text"),
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
            $res = $this->WechatOrderItemModel->add($orderItemData);

            }
        if($res){
            Log::write('创建订单成功,订单号为：'.$orderCode);
            //给客服端发消息提醒
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
            $param = $enterpriseService->getCustomerSendMsg($orderId,1);//新工单提醒
            $enterpriseService->sendMessage($param);//新工单提醒
            //给主管端发消息提醒
            $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
            $param = $distributeService->getDistributeSendMsg($orderId,1);//新工单提醒
            $distributeService->sendMessage($param);//新工单提醒
            //给师傅端发消息提醒
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_MAINTAIN_APPSECRET);
            $param = $enterpriseService->getMasterSendMsg($orderId,1);//新工单提醒
            $enterpriseService->sendMessage($param);//新工单提醒
            $res = array("status" => 1,"data" => ["id" => $orderId]);
            //给用户发送下单提醒
            (new WechatService())->sendCreateOrderMsg($orderId,$order_type);
        }else{
            Log::write('创建订单失败,订单号为：'.$orderCode);
            $res = array("status" => 0,"data" => ["id" => $orderId]);
        }
        return $res;
    }

    /**
     * 续签年服务生成新订单
     * @param $order_id
     */
    public function renewCleanKillOrder($order_id){
        //获取用户之前订单的服务商ID等信息
        $condition['orde.id'] = $order_id;
        $oldOrderData = $this->WechatOrderModel->getOrderAndItemData($condition,"orde.*,item.*");
        $oldOrderData = $oldOrderData[0];
        $orderCode = $this->getOrderCode();
        $memberId = session("memId");
        $orderData = [
            "order_code" => $orderCode,
            "order_state" => I("order_state"),//0:派单中;1:已接单;2:待支付;3:已支付;4:已取消;5:已电话解决;6:已退款
            "order_type" => $oldOrderData['order_type'],
            "member_id" => $memberId,//用户ID
            "facilitator_id" => $oldOrderData['facilitator_id'],//用户ID
            "province" => $oldOrderData['province'],
            "city" => $oldOrderData['city'],
            "latng" => $oldOrderData['latng'],
            "location_name" => $oldOrderData['location_name'],
            "store_name" => $oldOrderData['store_name'],
            "detailed_address" => $oldOrderData['detailed_address'],
            "link_person" => $oldOrderData['link_person'],
            "link_phone" => $oldOrderData['link_phone'],
            "fixed_line" => $oldOrderData['fixed_line'],
            "door_price" => 0,//上门费默认20
            "service_price" => 0,//服务费
            "money_total" => $oldOrderData['money_total'],
            "create_time" => date("Y-m-d H:i:s"),
            "door_time" => date("Y-m-d H:i:s"),
            "update_time" => date("Y-m-d H:i:s"),
            "enterprise_name" => $oldOrderData['enterprise_name'],
            "renew_order_id" => $order_id,
            "is_ka" => $oldOrderData['is_ka']
        ];
        $orderId = $this->WechatOrderModel->add($orderData);
        $res = false;
        if($orderId){
            $orderItemData = [
                "order_id" => $orderId,
                "store_area" => $oldOrderData['store_area'],
                "store_scene" => $oldOrderData['store_scene'],
                "insect_species" => $oldOrderData['insect_species'],
                "insect_time" => $oldOrderData['insect_time'],
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
            $res = $this->WechatOrderItemModel->add($orderItemData);
        }
        if($res){
            Log::write('创建订单成功,订单号为：'.$orderCode);
            //给客服端发消息提醒
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
            $param = $enterpriseService->getCustomerSendMsg($orderId,1);//新工单提醒
            $enterpriseService->sendMessage($param);//新工单提醒
            //给主管端发消息提醒
            $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
            $param = $distributeService->getDistributeSendMsg($orderId,1);//新工单提醒
            $distributeService->sendMessage($param);//新工单提醒
            $res = array("status" => 1,"data" => ["id" => $orderId]);
            //给用户发送下单提醒
            (new WechatService())->sendCreateOrderMsg($orderId,$oldOrderData['order_type']);
        }else{
            Log::write('创建订单失败,订单号为：'.$orderCode);
            $res = array("status" => 0,"data" => ["id" => $orderId],'msg'=>'续签创建订单失败');
        }
        return $res;
    }

    /**
     * 更新订单
     * @param $order_state
     * @return array
     * @author pangyongfu
     */
    public function updateOrder($order_state){
        //0:派单中;1:已接单;2:待支付;3:已支付;4:已取消;5:已电话解决;6:已退款;7:已评价

        $item_res = false;
        if($order_state == 1){
            //拼凑更新数据
            $orderData['id'] = I('id');
            $orderData['order_state'] = I('order_state');
            $orderData['single_time'] = date("Y-m-d H:i:s");//接单时间
            $orderData['update_time'] = date("Y-m-d H:i:s");
            $order_type = I('order_type');
            //获取该订单是否为重新分配的单子
            $orderOne = $this->WechatOrderModel->where(['id'=>I('id')])->find();

            //更新订单
            $item_res = $this->WechatOrderModel->save($orderData);

            //判断如果是首次接单 则给用户发送消息提醒
            if(empty($orderOne['single_time']) && $order_type != 2){

                //给用户发送模板通知消息
                (new WechatService)->sendMasterOrderMsgNew(I('id'),$orderOne['workers_id']);
                //TODO 如果是领值订单，则通知领值系统
                $orderData = $this->getOrderData(I('id'));
                if(!empty($orderData['mr_id'])){
                    $this->WechatService->curlPatch($orderData['mr_id'],3,$orderData['money_total'],$orderData['door_price'],$orderData['service_price'],$orderData['parts_price']);
                }
            }

            //消杀师傅接单后，发消息提醒  1设备维修，2门店消杀，3烟道清洗
//            $order_type = I('order_type');
//            if(isset($order_type) && !empty($order_type) && $order_type == 2){
//
//                $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CLEANKILL_APPSECRET);
//                $param = $enterpriseService->getMasterSendMsg($orderData['id'],4);//新工单提醒
//                $enterpriseService->sendMessage($param);//新工单提醒
//            }

        }elseif($order_state == 2){
            //拼凑更新数据
            $is_ka = I('is_ka');
            $is_year = I('is_year');
            $is_sh = I('is_sh');
            $orderData['id'] = I('id');

            $is_change_price = I('is_change_price');
            if($is_change_price){
                $orderData['money_total'] = I('money_total');
            }

            if($is_ka==1 || $is_year==1 || $is_sh==1){
                $orderData['equipment_id'] = 2;//消杀
            }else{
                $orderData['order_state'] = $order_state;
            }
            $orderData['update_time'] = date("Y-m-d H:i:s");

//            $condition['order_id'] = ['eq',I('id')];
//            $orderItemData['difference_text'] = I("difference_text");
//            $orderItemData['difference_price'] = I("difference_price");
//            $orderItemData['difference_status'] = 0;//0：无需差价支付1：客户未支付2：客服已支付
//            $orderItemData['update_time'] = date("Y-m-d H:i:s");

            //更新订单
            $item_res = $this->WechatOrderModel->save($orderData);
            if($is_ka!=1 && $is_year!=1 && $is_sh!=1){
                (new WechatService())->sendMasterOrederFinishPayMoneyNew($orderData['id'],'storeCleanKill',$is_change_price);
            }
//            if($res_order){
//                $item_res = $this->WechatOrderItemModel->where($condition)->save($orderItemData);
//            }
        }elseif($order_state == 3){
            //拼凑更新数据
            $orderData['id'] = I('id');
            $orderData['order_state'] = I('order_state');
            $orderData['update_time'] = date("Y-m-d H:i:s");
            //如果订单是消杀订单，并且是否是年订单的子订单
            $childOrder = $this->WechatOrderModel->where(['id'=>$orderData['id']])->find();
            if($childOrder['order_type'] == 2 && $childOrder['is_year'] == 1 && $childOrder['is_main'] == 0){
                //如果，是子订单，将年服务次数减去1
                $serviceSql = "UPDATE jpgk_wechat_year_service SET service_num_remain = service_num_remain - 1 WHERE id = ".$childOrder['year_service_id'];
                M('')->query($serviceSql);
                //如果，该子订单是最后一次服务，将年服务主订单改为已完成
                $serviceTimeData = M('wechat_year_service_time')->where(['year_service_id'=>$childOrder['year_service_id'],'order_id'=>0])->find();
                if(empty($serviceTimeData)){
                    $motherData['order_state'] = 3;
                    $this->WechatOrderModel->where(['year_service_id'=>$childOrder['year_service_id'],'is_year'=>1,'is_main'=>1])->save($motherData);
                }
            }
            //更新订单
            $item_res = $this->WechatOrderModel->save($orderData);
            //TODO 如果是领值订单，则通知领值系统
            $orderData = $this->getOrderData(I('id'));
            if(!empty($orderData['mr_id'])){
                $this->WechatService->curlCancel($orderData['mr_id'],'archive');
            }
        }elseif($order_state == 4){

            //拼凑更新数据
            $orderData['id'] = I('id');
            $orderData['order_state'] = I('order_state');
            $orderData['update_time'] = date("Y-m-d H:i:s");

            $condition['order_id'] = ['eq',I('id')];
            $orderItemData['cancel_text'] = I("cancel_text");
            $orderItemData['update_time'] = date("Y-m-d H:i:s");

            //更新订单
            $res_order = $this->WechatOrderModel->save($orderData);
            if($res_order){
                $item_res = $this->WechatOrderItemModel->where($condition)->save($orderItemData);
                //TODO 如果是领值订单，则通知领值系统
                $orderData = $this->getOrderData(I('id'));
                if(!empty($orderData['mr_id'])){
                    $this->WechatService->curlCancel($orderData['mr_id'],'cancel',$orderItemData['cancel_text']);
                }
            }
            $is_distribute = I('is_distribute') ? I('is_distribute') : false;
            //给客服发送取消订单提醒
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
            $param = $enterpriseService->getCustomerSendMsg($orderData['id'],9);//取消订单提醒
            if(!empty($is_distribute)){
                $param = $enterpriseService->getCustomerSendMsg($orderData['id'],10);//取消订单提醒
            }
            $enterpriseService->sendMessage($param);//取消订单提醒
            if(empty($is_distribute)){//不是主管取消的就给主管发消息
                //给分配主管发取消订单消息提醒
                $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
                $param = $distributeService->getDistributeSendMsg($orderData['id'],2);//取消订单提醒
                $distributeService->sendMessage($param);//取消订单提醒
            }

        }elseif($order_state == 5){
            //拼凑更新数据
            $orderData['id'] = I('id');
            $orderData['order_state'] = I('order_state');
            $orderData['update_time'] = date("Y-m-d H:i:s");

            $condition['order_id'] = ['eq',I('id')];
            $orderItemData['phone_solve_text'] = I("phone_solve_text");
            $orderItemData['update_time'] = date("Y-m-d H:i:s");

            //更新订单
            $res_order = $this->WechatOrderModel->save($orderData);
            if($res_order){
                $item_res = $this->WechatOrderItemModel->where($condition)->save($orderItemData);
                //TODO 如果是领值订单，则通知领值系统
                $orderData = $this->getOrderData(I('id'));
                if(!empty($orderData['mr_id'])){
                    $this->WechatService->curlCancel($orderData['mr_id'],'archive',$orderItemData['phone_solve_text']);
                }
            }
        }elseif($order_state == 9){
            //查看订单是否有差价并已支付
            $orderId = I('id');
            $malfunction_text = I('malfunction_text');
//            $orderItemData = $this->WechatOrderItemModel->where(array('order_id' => $orderId))->find();
//            if($orderItemData['difference_status'] == 1){
//                $res = array("status" => 0,"msg" => "差价还未支付，请确认！");
//                return $res;
//            }
            //拼凑扩展订单数据  更新师傅完成时间
            if(!empty($malfunction_text)){
                $orderItemData['malfunction_text'] = $malfunction_text;
                $orderItemData['worker_over_time'] = date("Y-m-d H:i:s");
                $this->WechatOrderItemModel->where(['order_id' => $orderId])->save($orderItemData);
            }else{
                $orderItemData['worker_over_time'] = date("Y-m-d H:i:s");
                $this->WechatOrderItemModel->where(['order_id' => $orderId])->save($orderItemData);
            }
            //拼凑订单更新数据
            $orderData['id'] = $orderId;
            $orderData['order_state'] = I('order_state');
            $orderData['update_time'] = date("Y-m-d H:i:s");
            //删除已上传图片

            $beforeimgdata = I('beforeimgdata') ? I('beforeimgdata') : [];
            $afterimgdata = I('afterimgdata');
            $imageFiles = array_merge($beforeimgdata,$afterimgdata);
            $this->delPicData($imageFiles,$orderId,'wechat');
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
            //更新订单
            $item_res = $this->WechatOrderModel->save($orderData);
        }elseif($order_state == 10){
            $orderData['id'] = I('id');
            $orderData['order_state'] = I('order_state');
            $orderData['workers_id'] = I('uid');
            $orderData['update_time'] = date("Y-m-d H:i:s");
            $item_res = $this->WechatOrderModel->save($orderData);
        }elseif($order_state == 13){//待确认
            $orderData['id'] = I('id');
            $is_ka = I('is_ka');
            $is_year = I('is_year');
            if($is_ka == 1 && $is_year == 1){
                $order_state = 11;
                //如果是ka并且是年订单，则判断该订单是否是续签的订单
                $where['id'] = $orderData['id'];
                $OriginalData = $this->WechatOrderModel->where($where)->find();
                //是续签订单则给第一次服务绑定续签时单次订单的ID，并将该续签的单次订单改为该年服务订单的子订单
                if(!empty($OriginalData['renew_order_id'])){
                    //将续签之前的单次订单改为年订单的子订单
                    $renewData['id'] = $OriginalData['renew_order_id'];
                    $renewData['is_year'] = 1;//年订单
                    $renewData['is_main'] = 0;//子订单
                    $renewData['year_service_id'] = $OriginalData['year_service_id'];//年订单的服务ID
                    $this->WechatOrderModel->save($renewData);
                    //给该年订单绑定第一次服务的订单ID
                    $sql = "UPDATE jpgk_wechat_year_service_time SET order_id = ".$OriginalData['renew_order_id']." WHERE year_service_id = ".$OriginalData['year_service_id']." ORDER BY service_time ASC LIMIT 1";
                    M('')->query($sql);
                }
            }else{
                $order_state = 10;
            }
            $orderData['order_state'] = $order_state;
            $orderData['update_time'] = date("Y-m-d H:i:s");
            $item_res = $this->WechatOrderModel->save($orderData);
        }
        if($item_res){
            Log::write('修改订单状态成功,订单ID为：'.$orderData['id'].'订单状态为：'.$order_state);
            $res = array("status" => 1,"data" => ["id" => $orderData['id']]);
        }else{
            Log::write('修改订单状态失败,订单ID为：'.$orderData['id'].'订单状态为：'.$order_state);
            $res = array("status" => 0,"data" => ["id" => $orderData['id']]);
        }
        return $res;
    }

    /**
     * 获取订单数据
     * @param $order_id
     * @return mixed
     */
    public function getOrderData($order_id){
        $map['orde.id'] = $order_id;
        $fields = "orde.id,orde.order_code,orde.order_state,orde.door_price,orde.service_price,orde.money_total,item.parts_price,item.mr_id";
        $orderData = $this->WechatOrderModel->getOrderAndItemData($map,$fields);
        return $orderData[0];
    }
    //生成订单号
    private function getOrderCode(){

        $orderCode = date('ymdHis').rand(1000,9999);
        return $orderCode;
    }

    /**
     * 根据条件获取一条订单数据
     * @param $map
     * @param $field
     * @author pangyongfu
     */
    public function getOneOrderData($map,$field){
        //获取订单数据
        $orderData = $this->WechatOrderModel->getDataByOrderId($map,$field);

        if(empty($orderData)){
            return [];
        }
        //处理数据
        $orderData = $orderData[0];
        //如果该订单是年订单则拼凑年订单数据
        if($orderData['is_year'] == 1 && $orderData['is_main'] == 1){
            $orderData = $this->getYearOrderData($map,$orderData);
        }elseif($orderData['is_year'] == 1 && $orderData['is_main'] == 0){
            //如果是子订单则只获取对应子订单的详情
            $orderData = $this->getYearOrderDetailByOrderId($map,$orderData);
        }
        $orderData['clean_type'] = $orderData['clean_type'] == 1 ? "烟道系统清洗" : "中央空调清洗";
        $dirty = $orderData['entirety_greasy_dirt'];
        $orderData['entirety_greasy_dirt'] = $dirty==1?"轻度":($dirty==2?"中度":"重度");
        switch($orderData['store_area']){
            case 0:
                $orderData['store_area'] = "未选择";
                break;
            case 1:
                $orderData['store_area'] = "0-100";
                break;
            case 2:
                $orderData['store_area'] = "101-200";
                break;
            case 3:
                $orderData['store_area'] = "201-300";
                break;
            case 4:
                $orderData['store_area'] = "301-400";
                break;
            case 5:
                $orderData['store_area'] = "401-500";
                break;
            case 6:
                $orderData['store_area'] = "501-600";
                break;
            case 7:
                $orderData['store_area'] = "600以上";
                break;
        }
        switch($orderData['order_state']){
            case 0:
                $orderData['order_state_text'] = "派单中";
                break;
            case 1:
                $orderData['order_state_text'] = "已接单";
                break;
            case 2:
                $orderData['order_state_text'] = "待支付";
                break;
            case 3:
                $orderData['order_state_text'] = "已完成";
                break;
            case 4:
                $orderData['order_state_text'] = "已取消";
                break;
            case 5:
                $orderData['order_state_text'] = "已完成";
                break;
            case 6:
                $orderData['order_state_text'] = "已退款";
                break;
            case 7:
                $orderData['order_state_text'] = "已完成";
                break;
            case 8:
                $orderData['order_state_text'] = "已支付";
                break;
            case 9:
                $orderData['order_state_text'] = "待验收";
                break;
            case 10:
                $orderData['order_state_text'] = "派单中";
                break;
            case 11:
                $orderData['order_state_text'] = "服务中";
                break;
            case 12:
                $orderData['order_state_text'] = "已过期";
                break;
            case 13:
                $orderData['order_state_text'] = "待确认";
                break;
            default:
                $orderData['order_state_text'] = "已完成";
                break;
        }
        switch($orderData['store_scene']){
            case 1:
                $orderData['store_scene'] = "商场";
                break;
            case 2:
                $orderData['store_scene'] = "写字楼";
                break;
            case 3:
                $orderData['store_scene'] = "美食城";
                break;
            case 4:
                $orderData['store_scene'] = "底商";
                break;
            case 5:
                $orderData['store_scene'] = "其他";
                break;
        }
//            0：未清洗过 1：15天以内  2：15天~30天 3：30天以前 4：60天以前 5：90天以前
        switch($orderData['last_clean_time']){
            case -1:
                $orderData['last_clean_time'] = "无";
                break;
            case 0:
                $orderData['last_clean_time'] = "未清洗过";
                break;
            case 1:
                $orderData['last_clean_time'] = "15天以内";
                break;
            case 2:
                $orderData['last_clean_time'] = "15天~30天";
                break;
            case 3:
                $orderData['last_clean_time'] = "30天以前";
                break;
            case 4:
                $orderData['last_clean_time'] = "60天以前";
                break;
            case 5:
                $orderData['last_clean_time'] = "90天以前";
                break;
        }
        //1：老鼠 2：蟑螂 3：蚊蝇
        $species = "";
        $speciesData = explode(",",$orderData['insect_species']);
        foreach($speciesData as $sv){
            switch($sv){
                case 1:
                    $species .= "老鼠,";
                    break;
                case 2:
                    $species .= "蟑螂,";
                    break;
                case 3:
                    $species .= "蚊蝇,";
                    break;
            }
        }
        $orderData['insect_species'] = rtrim($species,",");
        //1：一周 2：二周 3：三周 4：一个月之上
        switch($orderData['insect_time']){
            case 0:
                $orderData['insect_time'] = "无";
                break;
            case 1:
                $orderData['insect_time'] = "一周";
                break;
            case 2:
                $orderData['insect_time'] = "二周";
                break;
            case 3:
                $orderData['insect_time'] = "三周";
                break;
            case 4:
                $orderData['insect_time'] = "一个月之上";
                break;
        }
        switch($orderData['urgent_level']){
            case 2:
                $orderData['urgent_level_text'] = "<span class='cor_red'>紧急订单</span>";
                break;
            case 3:
                $orderData['urgent_level_text'] = "普通订单";
                break;
            case 4:
                $orderData['urgent_level_text'] = "普通订单";
                break;
            default:
                $orderData['urgent_level_text'] = "普通订单";
                break;
        }
        if(empty($orderData['location_name'])){
            $orderData['location_name'] = "未填写";
        }
        return $orderData;
    }
    /**
     * 根据条件获取一条年订单数据
     * @param $map
     * @param $field
     * @author pangyongfu
     */
    public function getYearOrderData($map,$orderData){
        //获取年订单数据（年服务时间列表）
        $field = "orde.id main_order_id,orde.year_service_id,service.type,service.start_time,service.end_time,service.service_num_total,service.service_num_remain,service.service_cycle,serviceTime.id time_id,serviceTime.order_id,serviceTime.service_time,serviceTime.service_num";
        $map['service.status'] = 1;
        $yearOrderData = $this->WechatOrderModel->getYearDataByOrderId($map,$field);

        Log::write("获取年订单数据sql|||".$this->WechatOrderModel->getLastSql());
        if(empty($yearOrderData)){
            return $orderData;
        }
        //处理数据
        $yearServiceData = $yearOrderData[0];
        $orderData['service_num_total'] = $yearServiceData['service_num_total'];
        $orderData['service_num_remain'] = $yearServiceData['service_num_remain'];
        $orderData['start_time'] = $yearServiceData['start_time'];
        $orderData['end_time'] = $yearServiceData['end_time'];
        $orderData['service_cycle'] = $yearServiceData['service_cycle'] == 0 ? "不固定周期" : $yearServiceData['service_cycle']."天";
        switch($yearServiceData['type']){//1-季服务，2-半年服务，3-年服务
            case 1:
                $orderData['year_service_type'] = "季服务";
                break;
            case 2:
                $orderData['year_service_type'] = "半年服务";
                break;
            case 3:
                $orderData['year_service_type'] = "年服务";
                break;
        }
        //年服务数据拼凑到订单详情数据中
        $orderData['year_service_recode'] = [];//服务记录
        $orderData['year_service_plan'] = [];//服务计划
        foreach($yearOrderData as $yearkey=> $yearData){
            //如果order_id不为空代表该次服务已生成订单，放入服务记录中
            if(!empty($yearData['order_id'])){
                //获取子订单服务商和订单状态
                $tmpOrder = $this->WechatOrderModel->where("id=".$yearData['order_id'])->field("order_state,facilitator_id")->find();
                //拼凑服务记录数据
                $orderData['year_service_recode'][$yearkey]['year_service_id'] = $yearData['year_service_id'];
                $orderData['year_service_recode'][$yearkey]['main_order_id'] = $yearData['main_order_id'];
                $orderData['year_service_recode'][$yearkey]['order_id'] = $yearData['order_id'];
                $orderData['year_service_recode'][$yearkey]['service_time'] = $yearData['service_time'];
                $orderData['year_service_recode'][$yearkey]['service_num'] = $yearData['service_num'];
                $orderData['year_service_recode'][$yearkey]['time_id'] = $yearData['time_id'];
                $orderData['year_service_recode'][$yearkey]['order_state'] = $tmpOrder['order_state'];
                $orderData['year_service_recode'][$yearkey]['facilitator_id'] = $tmpOrder['facilitator_id'];
            }else{
                //拼凑服务计划数据
                $orderData['year_service_plan'][$yearkey]['year_service_id'] = $yearData['year_service_id'];
                $orderData['year_service_plan'][$yearkey]['main_order_id'] = $yearData['main_order_id'];
                $orderData['year_service_plan'][$yearkey]['service_time'] = $yearData['service_time'];
                $orderData['year_service_plan'][$yearkey]['service_num'] = $yearData['service_num'];
                $orderData['year_service_plan'][$yearkey]['time_id'] = $yearData['time_id'];
                break;
            }
        }
        return $orderData;
    }

    /**
     * 获取年服务订单的子订单详情（根据子订单的ID）
     * @param $map
     * @param $orderData
     * @return array
     */
    public function getYearOrderDetail($map){
        $field = "service.id as serviceId,service.money_total,service.type,service.start_time,service.end_time,service.service_num_total,service.service_num_remain,service.service_cycle,serviceTime.id time_id,serviceTime.order_id,serviceTime.service_time,serviceTime.service_num";
        $map['service.status'] = 1;
        $map['serviceTime.order_id'] = 0;
        unset($map['orde.id']);
        unset($map['orde.facilitator_id']);
        unset($map['orde.workers_id']);
        $yearOrderData = $this->WechatYearServiceModel->getYearDetailDataByOrderId($map,$field);
        Log::write("获取年订单详情数据sql|||".$this->WechatYearServiceModel->getLastSql());

        $orderData = [];
        //处理数据
        $yearServiceData = $yearOrderData[0];

        $orderData['serviceId'] = $yearServiceData['serviceId'];
        $orderData['money_total'] = $yearServiceData['money_total'];
        $orderData['service_num_total'] = $yearServiceData['service_num_total'];
        $orderData['service_num_remain'] = $yearServiceData['service_num_remain'];
        $orderData['start_time'] = $yearServiceData['start_time'];
        $orderData['end_time'] = $yearServiceData['end_time'];
        $orderData['service_cycle'] = $yearServiceData['service_cycle'] == 0 ? "不固定周期" : $yearServiceData['service_cycle']."天";
        $orderData['cycle'] = $yearServiceData['service_cycle'];
        $orderData['type'] = $yearServiceData['type'];
        switch($yearServiceData['type']){//1-季服务，2-半年服务，3-年服务
            case 1:
                $orderData['year_service_type'] = "季服务";
                break;
            case 2:
                $orderData['year_service_type'] = "半年服务";
                break;
            case 3:
                $orderData['year_service_type'] = "年服务";
                break;
        }
        foreach($yearOrderData as $value){
            $orderData['serviceTime'][] = [
                'time_id'=>$value['time_id'],
                'service_time'=>$value['service_time'],
                'service_num'=>$value['service_num'],
            ];
        }

        return $orderData;
    }
    /**
     * 获取年服务订单的子订单详情（根据子订单的ID）
     * @param $map
     * @param $orderData
     * @return array
     */
    public function getYearOrderDetailByOrderId($map,$orderData){
        $field = "service.id as serviceId,service.money_total,service.type,service.start_time,service.end_time,service.service_num_total,service.service_num_remain,service.service_cycle,serviceTime.id time_id,serviceTime.order_id,serviceTime.service_time,serviceTime.service_num";
        $map['service.status'] = 1;
        $map['serviceTime.order_id'] = $orderData['id'];
        unset($map['orde.id']);
        unset($map['orde.facilitator_id']);
        unset($map['orde.workers_id']);
        $yearOrderData = $this->WechatYearServiceModel->getYearDetailDataByOrderId($map,$field);
        Log::write("获取年订单详情数据sql|||".$this->WechatYearServiceModel->getLastSql());

        if(empty($orderData)){
            return [];
        }
        //处理数据
        $yearServiceData = $yearOrderData[0];

        $orderData['serviceId'] = $yearServiceData['serviceId'];
        $orderData['money_total'] = $yearServiceData['money_total'];
        $orderData['service_num_total'] = $yearServiceData['service_num_total'];
        $orderData['service_num_remain'] = $yearServiceData['service_num_remain'];
        $orderData['start_time'] = $yearServiceData['start_time'];
        $orderData['end_time'] = $yearServiceData['end_time'];
        $orderData['service_cycle'] = $yearServiceData['service_cycle'] == 0 ? "不固定周期" : $yearServiceData['service_cycle']."天";
        $orderData['cycle'] = $yearServiceData['service_cycle'];
        $orderData['type'] = $yearServiceData['type'];
        switch($yearServiceData['type']){//1-季服务，2-半年服务，3-年服务
            case 1:
                $orderData['year_service_type'] = "季服务";
                break;
            case 2:
                $orderData['year_service_type'] = "半年服务";
                break;
            case 3:
                $orderData['year_service_type'] = "年服务";
                break;
        }
        $orderData['service_time'] = $yearServiceData['service_time'];//计划服务时间
        $orderData['service_num'] = $yearServiceData['service_num'];//第几次服务
        return $orderData;
    }
    //获取订单中的地址信息
    public function getDistrictData($provinceId,$cityId){
        $District = M('District');
        $provinceData = $District->where(['id'=>$provinceId])->find();
        $cityData = $District->where(['id'=>$cityId])->find();

        return [$provinceData['name'],$cityData['name']];
    }

    /**
     * 获取订单图片数据
     * @param $orderId
     * @param int $type
     * @author pangyongfu
     */
    public function getOrderImg($orderId,$type = 0){
        $map['sm.row_id'] = ['eq',$orderId];
        $map['sm.type'] = ['eq',$type];
        $map['pic.type'] = ['eq',"wechat"];
        $map['sm.app'] = ['eq',"WECHAT"];
        $field = "pic.path";
        $data = $this->SomethingPicModel->getImgData($map,$field);
        return $data;
    }

    /**
     * 申请售后
     * @param $order_id
     * @param $data
     * @return array
     * @author pangyongfu
     */
    public function applyAfterSale($order_id,$data){
        $map['order_id'] = ['eq',$order_id];
        $map['update_time'] = date("Y-m-d H:i:s");
        $item_res = $this->WechatOrderItemModel->where($map)->save($data);
        if($item_res){
            $res = array("status" => 1);
        }else{
            $res = array("status" => 0);
        }
        return $res;
    }

    /**
     * 获取未开发票的订单列表
     * @return mixed
     * @author pangyongfu
     */
    public function getInvoiceOrder(){
        //获取已开发票的订单的ID
        $order_ids = $this->WechatTicketOrderModel->field("order_id")->select();
        $order_ids = array_column($order_ids,"order_id");
        $order_ids = implode(",",$order_ids);
        //查该用户出未申请开票的订单
        $condition['orde.member_id'] = session("memId");//测试阶段先写死
//        $condition['orde.member_id'] = 2;//获取用户的MemberID
        $condition['orde.order_state'] = ['in',[3,7]];//订单状态为已完成
        if(!empty($order_ids)){
            $condition['orde.id'] = ['not in',$order_ids];//订单状态为已完成
        }
        $field = "orde.*,item.difference_price,item.difference_status";
        $orderData = $this->WechatOrderModel->getDataByOrderId($condition,$field);
//        如果订单有差价，且差价为已支付则将订单金额加上差价金额
        foreach($orderData  as &$orval){
            if($orval['difference_status'] != 0 && $orval['difference_status'] == 2){
                $orval['money_total'] += $orval['difference_price'];
            }
            $orval['order_type_text'] = $orval['order_type']==1 ? "门店维修" : ($orval['order_type']==2 ? "门店消杀" : "烟道清洗");
        }
//        var_dump("<pre>",$orderData);die;
        return $orderData;
    }

    /**
     * 创建发票
     * @return array
     * @author pangyongfu
     */
    public function createInvoice(){
        $data['head_type'] = I("head_type");
        $data['member_id'] =session("memId");//测试阶段先写死
//        $data['member_id'] = 2;
        $data['head_ticket'] = I("head_ticket");
        $data['tax_number'] = I("tax_number");
        $data['ticket_type'] = I("ticket_type");
        $data['money'] = I("money");
        $data['receive_person'] = I("receive_person");
        $data['receive_phone'] = I("receive_phone");
        $data['receive_address'] = I("receive_address");
        $data['city_id'] = I("city_id");
        $data['province_id'] = I("province_id");
        $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
        $tax_ticket_type = I("tax_ticket_type");
        if($tax_ticket_type == 2){
            $data['account_number'] = I("account_number");
            $data['open_bank'] = I("open_bank");
            $data['ticket_phone'] = I("ticket_phone");
            $data['ticket_address'] = I("ticket_address");
            $data['tax_ticket_type'] = I("tax_ticket_type");
        }
        $order_id = I("order_id");
        //创建发票申请记录
        $res = $this->WechatTicketModel->add($data);
        if($res){
            //创建发票订单关联关系记录
            $orderId = explode(',',$order_id);
            $ticketData = [];
            foreach($orderId as $oval){
                $tmp = [];
                $tmp['order_id'] = $oval;
                $tmp['ticket_id'] = $res;
                $tmp['create_time'] = date('Y-m-d H:i:s');
                array_push($ticketData,$tmp);
            }
            $final = $this->WechatTicketOrderModel->addAll($ticketData);
            if($final){
                return array('status'=>1);
            }
        }
        return array('status'=>0);
    }

    /**
     * 获取指定状态的订单数据 1 全部 2 未完成 3 已完成 4 已取消
     * @param $status
     * @author pangyongfu
     */
    public function getOrderInfoByStatus($status){
        $condition['orde.member_id'] = session("memId");//查询登录用户的订单
//        $condition['member_id'] = 2;
        $condition['orde.order_state'] = ["neq",4];
        $condition['orde.is_main'] = 1;//是否为服务订单主单（1：是 0：否）
        switch($status){
            case 2:
                $condition['orde.order_state'] = ["not in","3,4,5,7"];
                break;
            case 3:
                $condition['orde.order_state'] = ["in","3,5,7"];
                break;
            case 4:
                $condition['orde.order_state'] = ["eq","4,12"];
                break;
        }
        $orderData = $this->WechatOrderModel->alias("orde")
            ->join("jpgk_wechat_order_item as item on orde.id = item.order_id")
            ->where($condition)
            ->order('item.urgent_level asc,orde.create_time desc')
            ->field("orde.*,item.urgent_level")
            ->select();
//        var_dump($orderData);die;
        if(empty($orderData)){
            return array('status'=>0,'msg'=>"暂无数据");
        }
        foreach($orderData as &$order){
            switch($order['order_state']){
                case 0:
                    $order['order_state_text'] = "派单中";
                    break;
                case 1:
                    $order['order_state_text'] = "已接单";
                    break;
                case 2:
                    $order['order_state_text'] = "待支付";
                    break;
                case 3:
                    $order['order_state_text'] = "已完成";
                    break;
                case 4:
                    $order['order_state_text'] = "已取消";
                    break;
                case 5:
                    $order['order_state_text'] = "无需上门";
                    break;
                case 6:
                    $order['order_state_text'] = "已退款";
                    break;
                case 7:
                    $order['order_state_text'] = "已完成";
                    break;
                case 8:
                    $order['order_state_text'] = "已支付";
                    break;
                case 9:
                    $order['order_state_text'] = "待验收";
                    break;
                case 10:
                    $order['order_state_text'] = "派单中";
                    break;
                case 11:
                    $order['order_state_text'] = "服务中";
                    break;
                case 12:
                    $order['order_state_text'] = "已过期";
                    break;
                case 13:
                    $order['order_state_text'] = "待确认";
                    break;
                default:
                    $order['order_state_text'] = "已完成";
                    break;
            }
            switch($order['order_type']){
                case 1:
                    $order['order_type_text'] = "门店维修";
                    $order['url'] = "/index.php?s=/wechat/index/showstorerepaireorder/order_id/".$order['id'].".html";
                    break;
                case 2:
                    $order['order_type_text'] = "门店消杀";
                    if($order['is_year'] == 1){
                        $order['order_type_text'] = "消杀年服务";
                    }
                    $order['url'] = "/index.php?s=/wechat/index/showcleankillorder/order_id/".$order['id'].".html";
                    if($order['is_year'] == 1 && in_array($order['order_state'],[11,3]) && $order['is_main'] == 1){
                        $order['url'] = "/index.php?s=/wechat/index/showcleankillyearorder/order_id/".$order['id'].".html";
                    }
                    break;
                case 3:
                    $order['order_type_text'] = "烟道清洗";
                    $order['url'] = "/index.php?s=/wechat/index/showcleaningorder/order_id/".$order['id'].".html";
                    break;
            }
        }
        return array('status'=>1,'data'=>$orderData);
    }

    /**
     * 获取登录用户开具的发票列表
     * @return mixed
     * @author pangyongfu
     */
    public function getInvoiceByMemberId(){
      $map['member_id'] = session("memId");
//        $map['member_id'] = 2;
        $ticketData = $this->WechatTicketModel->where($map)->order("update_time")->select();
        return $ticketData;
    }
    public function getInvoiceDetail(){
        $map['ticket.id'] = I('id');
        $field = "ticket.*,GROUP_CONCAT(torder.order_id) order_id";
        //获取发票数据
        $ticketData = $this->WechatTicketModel->getInvoiceDetail($map,$field);
        $ticketData['ticket_type'] = $ticketData['ticket_type']==1?"食品":($ticketData['ticket_type']==2?"服务费":($ticketData['ticket_type']==3?"食品":"门店维修"));
        $ticketData['head_type'] = $ticketData['head_type']==1?"企业":"个人/非企业单位";
        $ticketData['status'] = $ticketData['status']==1?"未开具":"已开具";
        $ticketData['tax_ticket_type_text'] = $ticketData['tax_ticket_type']==1?"普通发票":"专用发票";
        //获取订单数据
        $condition['orde.id'] = ['in',$ticketData['order_id']];
        $fields = "orde.id,orde.order_code,orde.order_type,orde.money_total,item.difference_status,item.difference_price";
        $orderData = $this->WechatOrderModel->getOrderAndItemData($condition,$fields);
        foreach($orderData as &$value){
            if($value['difference_status'] == 2 && $value['difference_price']){
                $value['money_total'] += $value['difference_price'];
            }
            $value['order_code'] = substr($value['order_code'],0,6)."...";
            switch($value['order_type']){
                case 1:
                    $value['order_type_text'] = "门店维修";
                    $value['url'] = "/index.php?s=/wechat/index/showstorerepaireorder/order_id/".$value['id'].".html";
                    break;
                case 2:
                    $value['order_type_text'] = "门店消杀";
                    $value['url'] = "/index.php?s=/wechat/index/showcleankillorder/order_id/".$value['id'].".html";
                    break;
                case 3:
                    $value['order_type_text'] = "烟道清洗";
                    $value['url'] = "/index.php?s=/wechat/index/showcleaningorder/order_id/".$value['id'].".html";
                    break;
            }
        }
        return array($ticketData,$orderData);
    }

    /**
     * 年服务订单设置
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function orderYearServiceSet($data){

        if(!$data){
            return [];
        }

        $WechatYearServiceModel = new WechatYearServiceModel();
        $WechatYearServiceTimeModel = new WechatYearServiceTimeModel();

        //1.年服务 首先提交添加年服务主表
        $yearService['type'] = $data['type'] == '季服务' ? 1 : ($data['type'] == '半年服务' ? 2 :( $data['type'] == '一年服务' ? 3 : ''));
        $yearService['category'] = $data['category'];
        $yearService['money_total'] = $data['price'];
        $yearService['service_cycle'] = $data['cycle'];
        $yearService['service_num_total'] = $data['service_num'];
        $yearService['service_num_remain'] = $data['service_remain'];
        $yearService['start_time'] = date('Y-m-d',strtotime($data['start_time']));
        $yearService['end_time'] = date('Y-m-d',strtotime($data['end_time']));

        $return = $WechatYearServiceModel->editData($yearService);

        //2.批量添加年服务时间
        $yearServiceTime = [];
        $timeReturn = '';
        $nowtime = Date('Y-m-d H:i:s');
        if($return){
            $time = $data['time'];
            if($time){
                foreach($time as $key => $val){

                    $yearServiceTimeOld['year_service_id'] = $return;
                    $yearServiceTimeOld['service_time'] = date('Y-m-d',strtotime($val));
                    $yearServiceTimeOld['service_num'] = $key+1;
                    $yearServiceTimeOld['status'] = 1;
                    $yearServiceTimeOld['create_time'] = $nowtime;
                    $yearServiceTimeOld['update_time'] = $nowtime;

                    $yearServiceTime[] = $yearServiceTimeOld;
                }
            }

//            print_r($yearServiceTime);die;
            $timeReturn = $WechatYearServiceTimeModel->addall($yearServiceTime);
        }
        return $return;
    }

    /**
     * 修改
     * @param $userInfo
     * @param $data
     * @param $state
     * $type 1=单次订单 2=年服务订单
     * $yearId 年服务订单标识
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function updateYearServiceOrder($userInfo,$data,$state,$type=1,$yearId = ''){


        $data['order_state'] = $state;
        //验证改任务是否已经待支付，已完成 TODO
        $order = $this->WechatOrderModel->where(['id'=>$data['id']])->find();
//        if(in_array($order['order_state'],[2,3,4,5,6,7,8,9])){
//            $this->ajaxReturn(["status" => 0,"msg" => "该订单已被分配，请分配其他订单！"]);
//        }
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
        Log::write("主管数据信息||".json_encode($userInfo));
        //判断如果是年服务的订单
        if($type == '2'){

            // TODO 是否为KA
            $data['money_total'] = $data['price'];
            $data['year_service_id'] = $yearId;
            $data['is_year'] = 1;
        }
        //更新订单，绑定服务商
        $res = $this->WechatOrderModel->save($data);
        Log::write("更新订单sql语句||".$this->WechatOrderModel->getLastSql());
        return $res;
    }

    /**
     * 通过年服务订单标识获取数据
     * @param $data
     * @return array|mixed|string
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getOrderYearServiceDataById($yearServiceId){
        //获取年订单数据（年服务时间列表）
        $field = "orde.id main_order_id,orde.year_service_id,service.type,service.start_time,service.end_time,service.service_num_total,service.service_num_remain,service.service_cycle,serviceTime.id time_id,serviceTime.order_id,serviceTime.service_time,serviceTime.service_num";
        $map['service.status'] = 1;
        $yearOrderData = $this->WechatOrderModel->getYearDataByOrderId($map,$field);

        //处理数据
        $yearServiceData = $yearOrderData[0];
        $orderData['service_num_total'] = $yearServiceData['service_num_total'];
        $orderData['service_num_remain'] = $yearServiceData['service_num_remain'];
        $orderData['start_time'] = $yearServiceData['start_time'];
        $orderData['end_time'] = $yearServiceData['end_time'];
        $orderData['service_cycle'] = $yearServiceData['service_cycle'] == 0 ? "不固定周期" : $yearServiceData['service_cycle']."天";
        switch($yearServiceData['type']){//1-季服务，2-半年服务，3-年服务
            case 1:
                $orderData['year_service_type'] = "季服务";
                break;
            case 2:
                $orderData['year_service_type'] = "半年服务";
                break;
            case 3:
                $orderData['year_service_type'] = "年服务";
                break;
        }
        //年服务数据拼凑到订单详情数据中
        $orderData['year_service'] = [];//服务记录
        foreach($yearOrderData as $yearkey=> $yearData){
            //如果order_id不为空代表该次服务已生成订单，放入服务记录中
                //拼凑服务记录数据
                $orderData['year_service'][$yearkey]['year_service_id'] = $yearData['year_service_id'];
                $orderData['year_service'][$yearkey]['order_id'] = $yearData['order_id'];
                $orderData['year_service'][$yearkey]['service_time'] = $yearData['service_time'];
                $orderData['year_service'][$yearkey]['service_num'] = $yearData['service_num'];
                $orderData['year_service'][$yearkey]['time_id'] = $yearData['time_id'];
        }
        return $orderData;
    }

    /**
     * 通过条件查询订单 用户数据
     * @param array $map
     * @param string $field
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getOrderMemberData($map=[],$field='*'){

//        $map['orde.status'] = 1;

        $returnData = $this->WechatOrderModel->getOrderMemberList($map,$field);

        return $returnData;
    }

    /**
     * 通过条件查询年服务数据
     * @param array $map
     * @param string $field
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getYearServiceData($map=[],$field='*'){

//        $map['orde.status'] = 1;

        $returnData = $this->WechatOrderModel->getYearServiceTime($map,$field);

        return $returnData;
    }

    /**
     * 生成订单  定时任务专用
     * @param $orderData 订单来源 1：门店消杀 2：烟道清洗 3：设备维修
     */
    public function creatWechatOrder($serviceData,$userData){

        //TODO 获取年服务子订单的上一条，如果订单id为空则不作操作
        $WechatYearServiceTimeModel = new WechatYearServiceTimeModel();
        $serviceTimeData = $WechatYearServiceTimeModel->where(['year_service_id'=>$serviceData['serviceId'],'order_id'=>['neq',0]])->order("service_num desc")->find();
        //TODO 如果不为空则获取该订单信息，判断师傅id是否为空，不为空则给将要生成的子订单绑定师傅
        $oldChildOrder = [];
        if(!empty($serviceTimeData)){

            $oldChildOrder = $this->WechatOrderModel->where(['id'=>$serviceTimeData['order_id']])->field("id,workers_id,supervisor_id,facilitator_id")->find();
        }
        //获取年服务主订单的订单信息
        $map['orde.year_service_id'] = $serviceData['serviceId'];
        $map['orde.is_year'] = 1;//年订单
        $map['orde.is_main'] = 1;//主订单
        $orderData = $this->WechatOrderModel->getOrderAndItemData($map,'orde.*,item.*');
        $orderData = $orderData[0];
//        var_dump($orderData);die;
        $orderCode = $this->getOrderCode();
        $orderChildData = [
            "order_code" => $orderCode,
            "order_state" => 0,//0:派单中;1:已接单;2:待支付;3:已支付;4:已取消;5:已电话解决;6:已退款
            "order_type" => $orderData['order_type'],
            "member_id" => $orderData['member_id'],//用户ID
            "facilitator_id" => $orderData['facilitator_id'],
            "province" => $orderData["province"],
            "city" => $orderData["city"],
            "latng" => $orderData["latng"],
            "location_name" => $orderData["location_name"],
            "store_name" =>  $orderData["store_name"],
            "ka_store_id" =>  $orderData["ka_store_id"],
            "detailed_address" =>  $orderData["detailed_address"],
            "link_person" =>  $orderData["link_person"],
            "link_phone" =>  $orderData["link_phone"],
            "fixed_line" =>  $orderData["fixed_line"],
            "door_price" => 0,//上门费默认20
            "service_price" => 0,//服务费
            "money_total" => $orderData["money_total"],
            "door_time" => $serviceData['service_time'],
            "create_time" => date("Y-m-d H:i:s"),
            "update_time" => date("Y-m-d H:i:s"),
            "enterprise_name" => $orderData["enterprise_name"],
            "is_ka" => $orderData['is_ka'],
            "is_year" => 1,
            "is_main" => 0,
            "year_service_id" => $orderData['year_service_id'],
        ];
        //如果上一个子订单存在且上一个子订单分配了师傅
        if(!empty($oldChildOrder['workers_id']) && $oldChildOrder['facilitator_id'] == $orderData['facilitator_id']){
            $orderChildData["workers_id"] = $oldChildOrder['workers_id'];
            $orderChildData["supervisor_id"] = $oldChildOrder['supervisor_id'];
            $orderChildData["order_state"] = 10;//师傅未接单
        }
        $orderId = $this->WechatOrderModel->add($orderChildData);

        $res = false;
        //1设备维修，2门店消杀，3烟道清洗
        if($orderData['order_type']==1 && $orderId){
            //添加图片数据
            $imageFile = I('imageData');
            if(!empty($imageFile)){
                foreach ($imageFile as $k => $v) {
                    if(empty($v)){
                        continue;
                    }
                    $tmp = [
                        'path'=>$v,
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

            //添加订单扩展数据
            $orderItemData = [
                "order_id" => $orderId,
                "equipment_name" => $orderData["equipment_name"],
                "brands_text" => $orderData["brands_text"],
                "malfunction_text" => $orderData["malfunction_text"],
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
            $res = $this->WechatOrderItemModel->add($orderItemData);

        }elseif($orderData['order_type']==2 && $orderId){
            $orderItemData = [
                "order_id" => $orderId,
                "store_area" => $orderData["store_area"],
                "store_scene" => $orderData["store_scene"],
                "insect_species" => $orderData["insect_species"],
                "insect_time" => $orderData["insect_time"],
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
            $res = $this->WechatOrderItemModel->add($orderItemData);

        }elseif($orderId){
            $orderItemData = [
                "order_id" => $orderId,
                "store_area" => $orderData["store_area"],
                "store_scene" => $orderData["store_scene"],
                "clean_type" => $orderData["clean_type"],
                "last_clean_time" => $orderData["last_clean_time"],
                "style_of_cooking" => $orderData["style_of_cooking"],
                "petticoat_pipe_length" => $orderData["petticoat_pipe_length"],
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s"),
            ];
            $this->WechatOrderItemModel->add($orderItemData);
        }

        $timeData['order_id'] = $orderId;
        $time_res = M('wechat_year_service_time')->where(['id'=>$serviceData['serviceTimeID']])->save($timeData);
        //更新年服务时间表中的 orderid标识
        if($time_res){

            //给客服端发消息提醒
//            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
//            $param = $enterpriseService->getCustomerSendMsg($orderId,1);//新工单提醒
//            $enterpriseService->sendMessage($param);//新工单提醒
            //给分配主管发消息，提醒主管上门
            (new WechatService())->sendYearServiceCreatMsgForFacilitator($orderData['order_id'],$orderData['supervisor_id'],$serviceData['service_num'],$serviceData['service_time'],$orderData['order_type'],$orderData['store_name']);

            //如果之前的订单已分配师傅则给师傅发消息，提醒上门
            if(!empty($oldChildOrder['workers_id']) && $oldChildOrder['facilitator_id'] == $orderData['facilitator_id']){
                $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CLEANKILL_APPSECRET);
                $param = $enterpriseService->getMasterSendMsg($orderId,1);//新工单提醒
                $enterpriseService->sendMessage($param);//新工单提醒
            }
            //给用户发送下单提醒
            (new WechatService())->sendYearServiceCreatMsgForUser($orderData['order_id'],$userData['open_id'],$serviceData['service_num'],$serviceData['service_time'],$orderData['order_type'],$orderData['facilitator_id']);

            $res = array("status" => 1,"data" => ["id" => $orderId]);
        }else{
            Log::write('创建订单失败,订单号为：'.$orderCode);
            $res = array("status" => 0,"data" => ["id" => $orderId]);
        }
        return $res;
    }

    /**
     * 添加图片使用
     * @param $imageFile
     * @param $id
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function editPicData($imageFile,$id,$type,$smtype){

//        if(!empty($imageFile)){
            //查询是否以往是否添加过数据，如果有并且用户这一次没选择这个图片就删除数据，并将存储本地的图片删除
            $picData = $this->SomethingPicModel->getImgData([
                'sm.app'=>strtoupper($smtype),
                'sm.row_id'=>$id,
            ],'pic.id as picId,pic.path,sm.id as smId');

            //判断
            if(is_array($picData)){

                //循环删除
                $picIdArr = '';
                $smPicIdArr = '';
                foreach($picData as $key => $value){
                    $picIdArr[] = $value['picId'];
                    $smPicIdArr[] = $value['smId'];

                    $tempReturn = false;
                    //判断如果数据不存在传递来的图片数据中，就删除
                    foreach($imageFile as $key2 => $value2){

                        if(strstr($value2,$value['path'])){
                            $tempReturn = true;
                            break;
                        }
                    }

                    if($tempReturn)
                        continue;

                    //删除本地图片
                    @unlink($value['path']);
                }

                //删除 Pic数据库数据  重新添加数据
                $this->PictureModel->where([
                    'id'=>['IN',$picIdArr]
                ])->delete();

                //删除 something_pic数据
                $this->SomethingPicModel->where([
                    'id'=>['IN',$smPicIdArr]
                ])->delete();
            }

            //添加数据图片
            if($imageFile){
                foreach ($imageFile as $k => $v) {
                    if(empty($v)){
                        continue;
                    }
                    $tmp = [
                        'path'=>$v,
                        'type' =>strtolower($type),
                        'create_time' => time(),
                        'status' =>1,
                    ];
                    $resultId = $this->PictureModel->add($tmp);
                    //图片预订单绑定
                    $tmp_pic = [
                        'app'=>strtoupper($smtype),
                        'row_id'=>$id,
                        'pic_id'=>$resultId,
                        'type'=>0,//0 之前 1 之后
                        'create_time'=>time(),
                        'update_time'=>date("Y-m-d H:i:s"),
                    ];
                    $this->SomethingPicModel->add($tmp_pic);
                }
            }
            return true;
//        }
    }

    /**
     * 删除图片数据
     * @param $id
     * @param $smtype
     * @return bool
     */
    public function delPicData($imageFile,$id,$smtype){

        //查询是否以往是否添加过数据，如果有并且用户这一次没选择这个图片就删除数据，并将存储本地的图片删除
        $picData = $this->SomethingPicModel->getImgData([
            'sm.app'=>strtoupper($smtype),
            'sm.row_id'=>$id,
        ],'pic.id as picId,pic.path,sm.id as smId');
        if(is_array($picData)){

            //循环删除
            $picIdArr = '';
            $smPicIdArr = '';
            foreach($picData as $key => $value){
                $picIdArr[] = $value['picId'];
                $smPicIdArr[] = $value['smId'];
                $tempReturn = false;
                //判断如果数据不存在传递来的图片数据中，就删除
                foreach($imageFile as $key2 => $value2){

                    if(strstr($value2,$value['path'])){
                        $tempReturn = true;
                        break;
                    }
                }

                if($tempReturn)
                    continue;
                //删除本地图片
                @unlink($value['path']);
            }

            //删除 Pic数据库数据
            $this->PictureModel->where([
                'id'=>['IN',$picIdArr]
            ])->delete();

            //删除 something_pic数据
            $this->SomethingPicModel->where([
                'id'=>['IN',$smPicIdArr]
            ])->delete();
        }
        return true;
    }
    /**
     * 获取巡检备注图片
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getInspectionRequirementsImg($inspection_store_child_id){

        //查询巡检备注图片
        $imgData = (new SomethingPicModel())->getImgData([
            'sm.row_id' => $inspection_store_child_id,
            'sm.app' => 'WECHATPOLLINGREMARK',
        ]);

        return $imgData;
    }

    /**
     * 发送巡检消息
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionRequirementsMsg($inspectionChildId){

        //查询订单信息，如果存在巡检员就发送消息，如果不存在巡检员，就给巡检主管发送消息
        $WechatInspectionStoreChildModel = (new WechatInspectionStoreChildModel());
        $childOrderInfo = $WechatInspectionStoreChildModel
            ->field('inspection_store_child_id,inspection_supervisor_id,inspector_id,store_id')
            ->where([
                'inspection_store_child_id'=>$inspectionChildId
            ])
            ->find();

        //判断巡检员是否存在 反之判断是否存在巡检主管
        if(!empty($childOrderInfo['inspector_id'])){

            //发送消息
            $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_MAINTAIN_APPSECRET);
            $distributeService->sendInspectionRequirementsMsg($inspectionChildId,$childOrderInfo['inspector_id'],2,$childOrderInfo['store_id']);

        }elseif(!empty($childOrderInfo['inspection_supervisor_id'])){

            //发送消息
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
            $enterpriseService->sendInspectionRequirementsMsg($inspectionChildId,$childOrderInfo['inspection_supervisor_id'],1,$childOrderInfo['store_id']);
        }


    }
}