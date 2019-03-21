<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Api\Controller;
use Enterprise\Service\EnterpriseService;
use Think\Controller;
use Wechat\Model\WechatAddressModel;
use Wechat\Model\WechatDeviceModel;
use Wechat\Model\WechatFacilitatorModel;
use Wechat\Model\WechatInspectionModel;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatInspectionStoreModel;
use Wechat\Model\WechatKaStoresModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderCrontabModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;

class ApiController extends Controller
{

    private $WechatOrderModel;
    private $WechatOrderItemModel;
    private $WechatPublicOrderService;
    private $WechatService;
    private $WechatMemberModel;
    private $WechatInspectionModel;
    private $WechatInspectionStoreModel;
    private $WechatInspectionStoreChildModel;
    private $WechatKaStoresModel;

    function _initialize()
    {

        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatOrderItemModel = new WechatOrderItemModel();
        $this->WechatPublicOrderService = new WechatPublicOrderService();
        $this->WechatService = new WechatService();
        $this->WechatMemberModel = new WechatMemberModel();
        $this->WechatInspectionModel = new WechatInspectionModel();
        $this->WechatInspectionStoreModel = new WechatInspectionStoreModel();
        $this->WechatInspectionStoreChildModel = new WechatInspectionStoreChildModel();
        $this->WechatKaStoresModel = new WechatKaStoresModel();
    }
    public function test(){
        $update_time = "2018-01-19 17:01:29";
        echo $update_time."<br/>";
        echo date("Y-m-d H:i:s",strtotime("$update_time  +40 minute"))."<br/>";

        echo  date("Y-m-d 22:00:00")."<br/>";
        echo  date("Y-m-d 09:00:00",strtotime("+1 day"))."<br/>";
    }
    //给客服发消息
    private function sendServerMsg($orderId,$msgType){
        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
        $param = $enterpriseService->getCustomerSendMsg($orderId,$msgType);//新工单提醒
        $enterpriseService->sendMessage($param);//新工单提醒
    }
    //给主管发消息
    private function sendMasterMsg($orderId,$msgType){
        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
        $param = $enterpriseService->getDistributeSendMsg($orderId,$msgType);//新工单提醒
        $enterpriseService->sendMessage($param);//新工单提醒
    }
    //给师傅发消息
    private function sendWorkerMsg($orderId,$msgType,$orderType){
        if($orderType == 1){
            $codescrect = COMPANY_MAINTAIN_APPSECRET;
        }elseif($orderType == 2){
            $codescrect = COMPANY_CLEANKILL_APPSECRET;
        }else{
            $codescrect = COMPANY_CLEANING_APPSECRET;
        }
        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,$codescrect);
        $param = $enterpriseService->getMasterSendMsg($orderId,$msgType);//新工单提醒
        $enterpriseService->sendMessage($param);//新工单提醒
    }
    //给客服发消息
    private function sendServerInsMsg($orderId,$msgType,$isMain=true){
        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
        $param = $enterpriseService->getCustomerInsSendMsg($orderId,$msgType,$isMain);//新工单提醒
        $enterpriseService->sendMessage($param);//新工单提醒
    }
    //给主管发消息
    private function sendMasterInsMsg($orderId,$msgType,$isMain=true){
        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
        $param = $enterpriseService->getDistributeInsSendMsg($orderId,$msgType,$isMain);//新工单提醒
        $enterpriseService->sendMessage($param);//新工单提醒
    }
    //给师傅发消息
    private function sendWorkerInsMsg($orderId,$msgType,$day = 0){
        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_MAINTAIN_APPSECRET);
        $param = $enterpriseService->getMasterInsSendMsg($orderId,$msgType,$day);//新工单提醒
        $enterpriseService->sendMessage($param);//新工单提醒
    }
    /**
     * 给客服端和主管端发送定时消息
     * @author pangyongfu
     */
    public function sendMsgToServerMaster(){

        //获取所有派单中的订单
        $map['order_state'] = 0;//派单中
        $map['workers_id'] = 0;
        $map['facilitator_id'] = ["neq",0];
        $map['supervisor_id'] = 0;
        $orderData = $this->WechatOrderModel->where($map)->select();

        //判断订单的下单时间
//        $tenTime = date("Y-m-d 22:00:00");
//        $nextDataTime = date("Y-m-d 09:00:00",strtotime("+1 day"));
        foreach($orderData as $key=>$value){
//            //下单时间在今天22:00之后，第二天9：00之间则跳过
//            if(strtotime($value['create_time']) >= strtotime($tenTime) && strtotime($value['create_time']) <= strtotime($nextDataTime)){
//                continue;
//            }
            //根据订单类型判断对应【更新时间】是否超时，超时则发送消息
            //1设备维修（1小时），2门店消杀（1小时40分钟），3设备清洗（1小时40分钟）清洗和消杀走一个消息
            $update_time = $value['update_time'];
            $server_type = 2;//客服发消息
            $mater_type = 3;//主管发消息
            if($value['order_type'] == 1){
                $afterMasterTIme = date("Y-m-d H:i:s",strtotime("$update_time +30 minute"));//主管时间
                $afterServerTIme = date("Y-m-d H:i:s",strtotime("$update_time + 1 hour"));//客服时间
            }else{
                $afterMasterTIme = date("Y-m-d H:i:s",strtotime("$update_time +40 minute"));//主管时间
                $afterServerTIme = date("Y-m-d H:i:s",strtotime("$update_time +50 minute"));//客服时间
            }
            //超时发消息
            $now = date("Y-m-d H:i:s");
            if($value['master_facilitator_msg'] == 0){//未发过消息
                //如果时间大于给客服发消息的时间，则给主管和客服都发消息
                if(strtotime($now) > strtotime($afterServerTIme)){
                    //给客服端发消息提醒
                    $this->sendServerMsg($value['id'],$server_type);
                    //给分配主管发消息提醒
                    $this->sendMasterMsg($value['id'],$mater_type);
                    //更新订单客服主管发消息状态
                    $saveData['id'] = $value['id'];
                    $saveData['master_facilitator_msg'] = 2;
                    $this->WechatOrderModel->save($saveData);
                }elseif(strtotime($now) >= strtotime($afterMasterTIme) && strtotime($now) <= strtotime($afterServerTIme)){
                    //给分配主管发消息提醒
                    $this->sendMasterMsg($value['id'],$mater_type);
                    //更新订单客服主管发消息状态
                    $saveData['id'] = $value['id'];
                    $saveData['master_facilitator_msg'] = 1;
                    $this->WechatOrderModel->save($saveData);
                }
            }elseif($value['master_facilitator_msg'] == 1) {//给主管发过消息
                if(strtotime($now) > strtotime($afterServerTIme)){
                    //给客服端发消息提醒
                    $this->sendServerMsg($value['id'],$server_type);
                    //更新订单客服主管发消息状态
                    $saveData['id'] = $value['id'];
                    $saveData['master_facilitator_msg'] = 2;
                    $this->WechatOrderModel->save($saveData);
                }
            }
        }
    }

    /**
     * 给主管端和师傅端发消息
     * @author pangyongfu
     */
    public function sendMsgToMasterWorker(){
        //获取所有未接单，主管分配给师傅后师傅未接单的数据
        $map['order_state'] = 10;//未接单
        $map['workers_id'] = ["neq",0];
        $map['facilitator_id'] = ["neq",0];
        $map['supervisor_id'] = ["neq",0];
        $orderData = $this->WechatOrderModel->where($map)->select();

        //判断订单的下单时间
//        $tenTime = date("Y-m-d 22:00:00");
//        $nextDataTime = date("Y-m-d 09:00:00",strtotime("+1 day"));
        foreach($orderData as $okey=>$ovalue) {
            //下单时间在今天22:00之后，第二天9：00之间则跳过
//            if (strtotime($ovalue['create_time']) >= strtotime($tenTime) && strtotime($ovalue['create_time']) <= strtotime($nextDataTime)) {
//                continue;
//            }
            //根据订单类型判断对应【更新时间】是否超时，超时则发送消息
            //1设备维修（1小时），2门店消杀（1小时40分钟），3设备清洗（1小时40分钟）清洗和消杀走一个消息
            $update_time = $ovalue['update_time'];
            if($ovalue['order_type'] == 1){
                $worker_type = 2;//师傅发消息
                $mater_type = 4;//主管发消息
                $afterWorkerTIme = date("Y-m-d H:i:s",strtotime("$update_time +50 minute"));//主管时间
                $afterMasterTIme = date("Y-m-d H:i:s",strtotime("$update_time +55 minute"));//客服时间
            }else{
                $worker_type = 2;//师傅发消息
                $mater_type = 4;//主管发消息
                $afterWorkerTIme = date("Y-m-d H:i:s",strtotime("$update_time +1 hour +20 minute"));//主管时间
                $afterMasterTIme = date("Y-m-d H:i:s",strtotime("$update_time +1 hour +30 minute"));//客服时间
            }
            //超时发消息
            $now = date("Y-m-d H:i:s");
            if($ovalue['facilitator_workers_msg'] == 0){//未发过消息
                //如果时间大于给主管发消息的时间，则给主管和师傅都发消息
                if(strtotime($now) > strtotime($afterMasterTIme)){
                    //给师傅端发消息提醒
                    $this->sendWorkerMsg($ovalue['id'],$worker_type,$ovalue['order_type']);
                    //给分配主管发消息提醒
                    $this->sendMasterMsg($ovalue['id'],$mater_type);
                    //更新订单主管师傅发消息状态
                    $saveData['id'] = $ovalue['id'];
                    $saveData['facilitator_workers_msg'] = 2;
                    $this->WechatOrderModel->save($saveData);
                }elseif(strtotime($now) >= strtotime($afterWorkerTIme) && strtotime($now) <= strtotime($afterMasterTIme)){
                    //给师傅发消息提醒
                    $this->sendWorkerMsg($ovalue['id'],$worker_type,$ovalue['order_type']);
                    //更新订单主管师傅发消息状态
                    $saveData['id'] = $ovalue['id'];
                    $saveData['facilitator_workers_msg'] = 1;
                    $this->WechatOrderModel->save($saveData);
                }
            }elseif($ovalue['facilitator_workers_msg'] == 1) {//给主管发过消息
                if(strtotime($now) > strtotime($afterMasterTIme)){
                    //给主管端发消息提醒
                    $this->sendMasterMsg($ovalue['id'],$mater_type);
                    //更新订单主管师傅发消息状态
                    $saveData['id'] = $ovalue['id'];
                    $saveData['facilitator_workers_msg'] = 2;
                    $this->WechatOrderModel->save($saveData);
                }
            }
        }
    }

    /**
     * 维修和清洗超过24小时未支付发消息
     * @author pangyongfu
     */
    public function sendMsgAfter24Hour(){
        //获取所有维修和清洗的待支付订单
        $map['order_state'] = 2;//待支付
        $map['order_type'] = ["in",[1,3]];
        $map['no_pay_msg'] = 0;//未发送过消息
        $map['workers_id'] = ['neq',0];//师傅已接单
        $orderData = $this->WechatOrderModel->where($map)->select();
        
        //比较更新时间到现在是否超过24小时，超过24小时则自动发消息
        $now = date("Y-m-d H:i:s");
        foreach($orderData as $ork=>$orv){
            $create_time = $orv['create_time'];
            $after24Hour = date("Y-m-d H:i:s",strtotime("$create_time +1 day"));//用户时间
            if($now >= $after24Hour){

                //todo 发消息给用户
                if($orv['order_type'] == '1'){
                    (new WechatService())->sendUserOrderOutMsgNew($orv['id'],'待支付',1);
                    //给师傅端发消息提醒
                    $this->sendWorkerMsg($orv['id'],5,$orv['order_type']);
                }elseif($orv['order_type'] == '3'){
                    (new WechatService())->sendUserOrderOutMsgNew($orv['id'],'待支付',3);
                    //给师傅端发消息提醒
                    $this->sendWorkerMsg($orv['id'],5,$orv['order_type']);
                }

                //no_pay_msg状态为已发送
                $saveData['id'] = $orv['id'];
                $saveData['no_pay_msg'] = 1;
                $this->WechatOrderModel->save($saveData);

            }
        }

    }

    /**
     * KA订单超过30分钟未确认自动修改为已完成并给用户发消息
     */
    public function after30MinCompleteKaOrder(){
        //获取所有未接单，主管分配给师傅后师傅未接单的数据
        $map['orde.order_state'] = 9;//待确认
        $map['orde.is_ka'] = 1;//ka订单
        $orderData = $this->WechatOrderModel->alias("orde")
            ->join("jpgk_wechat_order_item item on item.order_id = orde.id")
            ->where($map)
            ->field("orde.*")
            ->select();
        foreach($orderData as $kk=>$vv){

            //判断时间【超过30分钟取消并发消息，25~30之间只发消息，小于25不操作】
            $update_time = $vv['update_time'];
            $after25TIme = date("Y-m-d H:i:s",strtotime("$update_time +25 minute"));//用户时间
            $after30TIme = date("Y-m-d H:i:s",strtotime("$update_time +30 minute"));//用户时间
            $now = date("Y-m-d H:i:s");

            if(strtotime($now) >= strtotime($after30TIme)){
                //todo 给用户发消息，订单已完成
                (new WechatService())->sendUserOrderOverMsgNew($vv['id']);

                //todo 修改订单状态为已取消
                $saveData['id'] = $vv['id'];
                $saveData['order_state'] = 3;
                $this->WechatOrderModel->save($saveData);
            }elseif(strtotime($now) >= strtotime($after25TIme) && strtotime($now) <= strtotime($after30TIme) && $vv['no_pay_msg'] == 0){
                //todo 给用户发消息,通知用户确认订单
                (new WechatService())->sendUserMakeSureOrderOverMsg($vv['id']);
            }
        }
    }

    /**
     * 清洗和消杀订单超过清洗或消杀周期发消息提醒
     */
    public function sendCycleMsg(){
        //获取所有消杀和清洗的已完成订单
        $map['order_state'] = ["in",[7,3]];//已完成
        $map['order_type'] = ["in",[2,3]];
        $map['is_year'] = 0;//不是年订单
        $map['member_cycle_msg'] = ['neq',2];//未给用户发消息或只发过一条
        $map['customer_cycle_msg'] = ['neq',2];//未给客服发消息或只发过一条
        $orderData = $this->WechatOrderModel->where($map)->select();
        $now = date("Y-m-d H:i:s");
        foreach($orderData as $kk=>$vv){

            //判断时间【超过最大天数直接该状态为2并发消息，54~57之间发消息并该状态为1，小于54不操作】
            //判断时间【超过最大天数直接该状态为2并发消息，24~27之间发消息并该状态为1，小于24不操作】
            $update_time = $vv['update_time'];
            $washAfter54Time = date("Y-m-d H:i:s",strtotime("$update_time +54 day"));//用户时间
            $washAfter57Time = date("Y-m-d H:i:s",strtotime("$update_time +57 day"));//用户时间
            $washAfter60Time = date("Y-m-d H:i:s",strtotime("$update_time +60 day"));//用户时间
            $cleanAfter24Time = date("Y-m-d H:i:s",strtotime("$update_time +24 day"));//用户时间
            $cleanAfter27Time = date("Y-m-d H:i:s",strtotime("$update_time +27 day"));//用户时间
            $cleanAfter30Time = date("Y-m-d H:i:s",strtotime("$update_time +30 day"));//用户时间

            if($vv['order_type'] == 3 && strtotime($now) >= strtotime($washAfter57Time)){
                //todo 给用户发清洗完成57天的消息
                (new WechatService())->sendServiceExpiration($vv['id'],$vv['order_type'],57,$washAfter60Time);

                //todo 修改订单消息状态为2
                $saveData['id'] = $vv['id'];
                $saveData['member_cycle_msg'] = 2;
                $this->WechatOrderModel->save($saveData);
            }elseif($vv['order_type'] == 3 && strtotime($now) >= strtotime($washAfter54Time) && strtotime($now) <= strtotime($washAfter57Time) && $vv['member_cycle_msg'] == 0){
                //todo 给用户发清洗完成54天的消息
                (new WechatService())->sendServiceExpiration($vv['id'],$vv['order_type'],54,$washAfter60Time);
                //todo 修改订单消息状态为2
                $saveData['id'] = $vv['id'];
                $saveData['member_cycle_msg'] = 1;
                $this->WechatOrderModel->save($saveData);
            }elseif($vv['order_type'] == 2 && strtotime($now) >= strtotime($cleanAfter27Time)){
                //todo 给用户发消杀完成27天的消息
                (new WechatService())->sendServiceExpiration($vv['id'],$vv['order_type'],27,$cleanAfter30Time);

                //todo 修改订单消息状态为2
                $saveData['id'] = $vv['id'];
                $saveData['member_cycle_msg'] = 2;
                $this->WechatOrderModel->save($saveData);
            }elseif($vv['order_type'] == 2 && strtotime($now) >= strtotime($cleanAfter24Time) && strtotime($now) <= strtotime($cleanAfter27Time) && $vv['member_cycle_msg'] == 0){
                //todo 给用户发消杀完成24天的消息
                (new WechatService())->sendServiceExpiration($vv['id'],$vv['order_type'],24,$cleanAfter30Time);
                //todo 修改订单消息状态为2
                $saveData['id'] = $vv['id'];
                $saveData['member_cycle_msg'] = 1;
                $this->WechatOrderModel->save($saveData);
            }

            $washAfter55Time = date("Y-m-d H:i:s",strtotime("$update_time +55 day"));//客服时间
            $washAfter58Time = date("Y-m-d H:i:s",strtotime("$update_time +58 day"));//客服时间
            $cleanAfter25Time = date("Y-m-d H:i:s",strtotime("$update_time +25 day"));//客服时间
            $cleanAfter28Time = date("Y-m-d H:i:s",strtotime("$update_time +28 day"));//客服时间
            if($vv['order_type'] == 3 && strtotime($now) >= strtotime($washAfter58Time)){
                //todo 给客服发清洗完成58天的消息
                $this->sendServerMsg($vv['id'],6);
                //todo 修改订单消息状态为2
                $saveData['id'] = $vv['id'];
                $saveData['customer_cycle_msg'] = 2;
                $this->WechatOrderModel->save($saveData);
            }elseif($vv['order_type'] == 3 && strtotime($now) >= strtotime($washAfter55Time) && strtotime($now) <= strtotime($washAfter58Time) && $vv['customer_cycle_msg'] == 0){
                //todo 给客服发清洗完成55天的消息
                $this->sendServerMsg($vv['id'],5);
                //todo 修改订单消息状态为2
                $saveData['id'] = $vv['id'];
                $saveData['customer_cycle_msg'] = 1;
                $this->WechatOrderModel->save($saveData);
            }elseif($vv['order_type'] == 2 && strtotime($now) >= strtotime($cleanAfter28Time)){
                //todo 给客服发清洗完成28天的消息
                $this->sendServerMsg($vv['id'],8);

                //todo 修改订单消息状态为2
                $saveData['id'] = $vv['id'];
                $saveData['customer_cycle_msg'] = 2;
                $this->WechatOrderModel->save($saveData);
            }elseif($vv['order_type'] == 2 && strtotime($now) >= strtotime($cleanAfter25Time) && strtotime($now) <= strtotime($cleanAfter28Time) && $vv['customer_cycle_msg'] == 0){
                //todo 给客服发清洗完成25天的消息
                $this->sendServerMsg($vv['id'],7);
                //todo 修改订单消息状态为2
                $saveData['id'] = $vv['id'];
                $saveData['customer_cycle_msg'] = 1;
                $this->WechatOrderModel->save($saveData);
            }
        }
    }
    /**
     * 所有订单超过3天未确认自动修改为已完成并给用户发消息
     */
    public function after3DaysCompleteWashOrder(){
        //获取所有未接单，主管分配给师傅后师傅未接单的数据
        $map['orde.order_state'] = 9;//待确认
        $orderData = $this->WechatOrderModel->alias("orde")
            ->join("jpgk_wechat_order_item item on item.order_id = orde.id")
            ->where($map)
            ->field("orde.*")
            ->select();
        foreach($orderData as $kk=>$vv){

            //判断时间【超过3天的未验收订单自动改为已完成】
            $update_time = $vv['update_time'];
            $after30TIme = date("Y-m-d H:i:s",strtotime("$update_time +3 day"));//用户时间
            $now = date("Y-m-d H:i:s");

            if(strtotime($now) >= strtotime($after30TIme)){

                //todo 修改订单状态为已完成
                $saveData['id'] = $vv['id'];
                $saveData['order_state'] = 3;
                $saveData['update_time'] = $now;
                $this->WechatOrderModel->save($saveData);

                if($vv['order_type'] == 2 && $vv['is_year'] == 1 && $vv['is_main'] == 0){
                    //如果，是子订单，将年服务次数减去1
                    $serviceSql = "UPDATE jpgk_wechat_year_service SET service_num_remain = service_num_remain - 1 WHERE id = ".$vv['year_service_id'];
                    M('')->query($serviceSql);
                    //如果，该子订单是最后一次服务，将年服务主订单改为已完成
                    $serviceTimeData = M('wechat_year_service_time')->where(['year_service_id'=>$vv['year_service_id'],'order_id'=>0])->find();
                    if(empty($serviceTimeData)){
                        $motherData['order_state'] = 3;
                        $this->WechatOrderModel->where(['year_service_id'=>$vv['year_service_id'],'is_year'=>1,'is_main'=>1])->save($motherData);
                    }
                }
                //todo 给用户发消息，订单已完成
                (new WechatService())->sendUserOrderOverMsgNew($vv['id']);
            }
        }
    }

    /**
     * 年服务订单 提前5天给用户提醒
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function advance5DayYearServiceCaution(){

        //1.查询消杀年服务订单
        $where['status'] = 1;
        $where['order_id'] = ['eq',0];
        $where['is_send_caution'] = ['eq',0];
//        $where['service.'] = ['eq',0];

        $timeData = M('WechatYearServiceTime')->where($where)->group('year_service_id,id')->select();
//        $timeData = (new WechatPublicOrderService())->getYearServiceData($where);

        $time = time();

        $orderId = '';
        $yearServiceId = [];
        $yearServiceTimeId = [];
        $yearServiceTimeData = [];
        //2.循环数据
        foreach($timeData as $value){

            //3.判断筛选出当前时间是否小于等于5天的订单
            if((strtotime($value['service_time'].' 00:00:00') - $time) <= 432000 && (strtotime($value['service_time'].' 00:00:00') - $time) >=0){

                $yearServiceId[] = $value['year_service_id'];
                $yearServiceTimeId[] = $value['id'];
                $yearServiceTimeData[] = $value;
            }
        }

        //4.通过符合条件的年服务id查询出用户微信标识
        $userData = [];
        if($yearServiceId){
            $userData = (new WechatPublicOrderService())->getOrderMemberData(['year_service_id'=>['in',$yearServiceId],'is_year'=>1,'is_main'=>1],'orde.id,orde.store_name,orde.order_type,orde.facilitator_id,orde.supervisor_id,orde.year_service_id,mem.open_id');
        }

        //5.给筛选出的用户发送提醒
        if($userData){

            foreach($userData as $k => $val){
                foreach($yearServiceTimeData as $k2 => $val2){

                    if($val['year_service_id'] == $val2['year_service_id']){
                        //TODO 给主管发送消息提醒
                        (new WechatService())->sendYearServiceMsgForFacilitator($val['id'],$val['supervisor_id'],$val2['service_time'],$val['order_type'],$val['store_name']);

                        //TODO 给用户发送消息提醒
                        (new WechatService())->sendYearServiceMsgForUser($val['id'],$val['open_id'],$val2['service_num'],$val2['service_time'],$val['order_type']);
                    }
                }
            }

            //6.修改年服务数据
            M('WechatYearServiceTime')->where(['id'=>['IN',$yearServiceTimeId]])->save(['is_send_caution'=>1]);
        }
    }

    /**
     * 年服务订单 提前一天给用户创建订单
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function advance1DayYearServiceCreatOrder(){

        //1.查询消杀年服务订单
        $where['serviceTime.status'] = 1;
        $where['serviceTime.order_id'] = ['eq',0];
        $timeData = (new WechatOrderModel())->getYearDataByMap($where,'serviceTime.id as serviceTimeID,serviceTime.service_time,service.id as serviceId,serviceTime.service_num');

        $time = time();
        $orderId = [];
        $yearServiceId = [];
        $yearServiceTimeId = [];
        $tempData = [];
        //2.循环数据
        foreach($timeData as $value){

            //3.判断筛选出当前时间是否小于等于1天的订单
            if((strtotime($value['service_time'].' 00:00:00') - $time) <= 86400 && (strtotime($value['service_time'].' 23:59:59') - $time) >=0){

                $userData = (new WechatPublicOrderService())->getOrderMemberData(['year_service_id'=>$value['serviceId'],'is_year'=>1,'is_main'=>1],'orde.id,orde.order_state,orde.store_name,orde.order_type,orde.facilitator_id,orde.supervisor_id,orde.year_service_id,mem.open_id');
                if($userData[0]['order_state'] != 11){//当前订单状态不是服务中
                    continue;
                }
//                $yearServiceId[] = $value['year_service_id'];
//                $yearServiceTimeId[] = $value['serviceTimeID'];
//                $orderId[] = $value['orderId'];

                //4.生成年服务子订单

                (new WechatPublicOrderService())->creatWechatOrder($value,$userData[0]);
            }
        }
    }
    /**
     * 年服务订单 根据年服务ID和日期直接生成订单
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function advance1DayCreatOrderByDate($year_id,$date)
    {

        //1.查询消杀年服务订单
        $where['serviceTime.status'] = 1;
        $where['serviceTime.order_id'] = ['eq', 0];
        $where['serviceTime.year_service_id'] = $year_id;
        $where['serviceTime.service_time'] = $date;
        $timeData = (new WechatOrderModel())->getYearDataByMap($where, 'serviceTime.id as serviceTimeID,serviceTime.service_time,service.id as serviceId,serviceTime.service_num');

        //2.循环数据
        foreach ($timeData as $value) {

            //3.判断筛选出当前时间是否小于等于1天的订单

            $userData = (new WechatPublicOrderService())->getOrderMemberData(['year_service_id' => $value['serviceId'], 'is_year' => 1, 'is_main' => 1], 'orde.id,orde.store_name,orde.order_type,orde.facilitator_id,orde.supervisor_id,orde.year_service_id,mem.open_id');

//                $yearServiceId[] = $value['year_service_id'];
//                $yearServiceTimeId[] = $value['serviceTimeID'];
//                $orderId[] = $value['orderId'];

            //4.生成年服务子订单

            (new WechatPublicOrderService())->creatWechatOrder($value, $userData[0]);
        }
    }
    /**
     * 生成巡检子订单（一天执行一次）
     */
    public function createInspectionChildOrder(){
        //获取巡检主订单
        set_time_limit(0);
        $now = date("Y-m-d");
        $mainMap['inspection_status'] = 2;//主订单状态（1：新工单(服务商未接单)；2：服务中(服务商已接单)；3：已取消；4：已完成）
        $inspectorOrder = $this->WechatInspectionModel->where($mainMap)->select();
        foreach($inspectorOrder as $okey=>$order){
            //如果服务起始时间小于等于当前时间则继续创建子订单
            if(strtotime($order['service_start_time']) <= strtotime($now)){
                //获取该主订单对应的门店关联数据
                $storeMap['inspection_id'] = $order['inspection_id'];
                $storeMap['status'] = 1;//1--启用；2--禁用；-1--删除
                $inspectionStoreInfo = $this->WechatInspectionStoreModel->where($storeMap)->select();
                foreach($inspectionStoreInfo as $skey=>$store){
                    //获取门店信息，查看门店是否被禁用
                    $storeInfo = $this->WechatKaStoresModel->where(['id'=>$store['store_id']])->field("id,name,status")->find();
                    if($storeInfo['status'] != 1){
                        continue;
                    }
                    //获取该门店最近一次的子订单
                    $storeChildMap['inspection_store_id'] = $store['inspection_store_id'];
                    $storeChildOrder = "service_num desc";
                    $storeChildInsOrder = $this->WechatInspectionStoreChildModel->where($storeChildMap)->order($storeChildOrder)->find();

                    if(empty($storeChildInsOrder)){
                        //如果子订单为空则该门店未生成过子订单，所以生成第一次子订单
                        $childOrderTmp['inspection_id'] = $store['inspection_id'];
                        $childOrderTmp['inspection_store_id'] = $store['inspection_store_id'];
                        $childOrderTmp['facilitator_id'] = $order['facilitator_id'];
                        $childOrderTmp['inspection_supervisor_id'] = $order['facilitator_uid'];
                        $childOrderTmp['service_num'] = 1;
                        $childOrderTmp['enterprise_id'] = $order['enterprise_id'];
                        $childOrderTmp['store_id'] = $store['store_id'];
                        $childOrderTmp['child_order_code'] = getInspectionOrderCode("C");
                        $childOrderTmp['status'] = 1;//1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成
                        $childOrderTmp['create_time'] = $childOrderTmp['update_time'] = date("Y-m-d H:i:s");
                        //创建子订单
                        $res = $this->WechatInspectionStoreChildModel->add($childOrderTmp);
                        //给分配主管发消息提醒
                        $this->sendMasterInsMsg($res,3,false);
                        //给店长发送消息
                        (new WechatService())->sendInsCreateOrderMsg($res);

                    }elseif($store['type'] == 2){//巡检类型（1：单次巡检；2：周期性巡检）
                        //子订单不为空则判断该门店的服务类型是否是周期，是的话再生成下一次子订单
                        //订单状态（1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成）
                        if($storeChildInsOrder['status'] == 6){
                            //如果上一次的子订单已完成则生成下一次的子订单
                            //上一次子订单完成时间加上周期是否等于今天
                            $cycle = ceil($store['cycle'] * 2 / 3);
                            $finishTime = $storeChildInsOrder['finish_time'];
                            $createOrderDate = date("Y-m-d",strtotime("$finishTime + $cycle day"));
                            if(strtotime($createOrderDate) <= strtotime($now)){
                                //生成子订单
                                $childOrderTmp['inspection_id'] = $store['inspection_id'];
                                $childOrderTmp['inspection_store_id'] = $store['inspection_store_id'];
                                $childOrderTmp['service_num'] = $storeChildInsOrder['service_num'] + 1;
                                $childOrderTmp['enterprise_id'] = $order['enterprise_id'];

                                //如果未修改服务商则绑定师傅
                                if($storeChildInsOrder['facilitator_id'] == $order['facilitator_id']){
                                    $childOrderTmp['facilitator_id'] = $storeChildInsOrder['facilitator_id'];
                                    $childOrderTmp['inspector_id'] = $storeChildInsOrder['inspector_id'];
                                    $childOrderTmp['inspection_supervisor_id'] = $storeChildInsOrder['inspection_supervisor_id'];
                                    $childOrderTmp['status'] = 2;//1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成
                                }else{
                                    //如果修改服务商则不绑定师傅
                                    $childOrderTmp['facilitator_id'] = $order['facilitator_id'];
                                    $childOrderTmp['inspection_supervisor_id'] = $order['facilitator_uid'];
                                    $childOrderTmp['status'] = 1;//1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成
                                }
                                $childOrderTmp['store_id'] = $store['store_id'];
                                $childOrderTmp['child_order_code'] = getInspectionOrderCode("C");
                                $childOrderTmp['create_time'] = $childOrderTmp['update_time'] = date("Y-m-d H:i:s");
                                //TODO 创建子订单
                                $res = $this->WechatInspectionStoreChildModel->add($childOrderTmp);
                                //TODO 发送消息
                                //给分配主管发消息提醒
                                $this->sendMasterInsMsg($res,3,false);
                                //给店长发送消息
                                (new WechatService())->sendInsCreateOrderMsg($res);
                                //为更换服务商给巡检员发消息提醒
                                if($storeChildInsOrder['facilitator_id'] == $order['facilitator_id']){

                                    $this->sendWorkerInsMsg($res,3);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 接单时生成子订单接口
     * @param $inspection_id
     */
    public function createInsChildOrderWhenAcceptOrder($inspection_id){
        set_time_limit(0);
        $mainMap['inspection_id'] = $inspection_id;
        $inspectorOrder = $this->WechatInspectionModel->where($mainMap)->find();
        //主订单状态（1：新工单(服务商未接单)；2：服务中(服务商已接单)；3：已取消；4：已完成）
        if($inspectorOrder['inspection_status'] == 2){

            $now = date("Y-m-d");
            //如果服务起始时间小于等于当前时间则生成子订单
            if(strtotime($inspectorOrder['service_start_time']) <= strtotime($now)){
                //获取该主订单对应的门店关联数据
                $storeMap['inspection_id'] = $inspection_id;
                $storeMap['status'] = 1;//1--启用；2--禁用；-1--删除
                $inspectionStoreInfo = $this->WechatInspectionStoreModel->where($storeMap)->select();
                foreach($inspectionStoreInfo as $skey=>$store){
                    //获取门店信息，查看门店是否被禁用
                    $storeInfo = $this->WechatKaStoresModel->where(['id'=>$store['store_id']])->field("id,name,status")->find();
                    if($storeInfo['status'] != 1){
                        continue;
                    }
                    //获取该门店最近一次的子订单
                    $storeChildMap['inspection_store_id'] = $store['inspection_store_id'];
                    $storeChildOrder = "service_num desc";
                    $storeChildInsOrder = $this->WechatInspectionStoreChildModel->where($storeChildMap)->order($storeChildOrder)->find();

                    //如果子订单为空则该门店未生成过子订单，所以生成第一次子订单
                    if(empty($storeChildInsOrder)){
                        $childOrderTmp['inspection_id'] = $store['inspection_id'];
                        $childOrderTmp['inspection_store_id'] = $store['inspection_store_id'];
                        $childOrderTmp['inspection_supervisor_id'] = $inspectorOrder['facilitator_uid'];
                        $childOrderTmp['facilitator_id'] = $inspectorOrder['facilitator_id'];
                        $childOrderTmp['service_num'] = 1;
                        $childOrderTmp['enterprise_id'] = $inspectorOrder['enterprise_id'];
                        $childOrderTmp['store_id'] = $store['store_id'];
                        $childOrderTmp['child_order_code'] = getInspectionOrderCode("C");
                        $childOrderTmp['status'] = 1;//1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成
                        $childOrderTmp['create_time'] = $childOrderTmp['update_time'] = date("Y-m-d H:i:s");
                        //创建子订单
                        $res = $this->WechatInspectionStoreChildModel->add($childOrderTmp);
                        //给分配主管发消息提醒
                        $this->sendMasterInsMsg($res,3,false);
                        //给店长发送消息
                        (new WechatService())->sendInsCreateOrderMsg($res);
                    }
                }
            }
        }
    }

    /**
     * 判断付费方式--季付和半年付的发送消息
     */
    public function judgePayTypeToSendMsg(){
        //获取服务中的不是年付的巡检主订单
        $mainMap['inspection_status'] = 2;//主订单状态（1：新工单(服务商未接单)；2：服务中(服务商已接单)；3：已取消；4：已完成）

        $inspectorOrder = $this->WechatInspectionModel->where($mainMap)->select();
        foreach($inspectorOrder as $insValue){
            //1：季付；2：半年付；3：年付
            //服务起始时间加上一个季度与当前时间比较，如果大于当前时间，然后再与服务截止时间比较
            $month = $insValue['payment'] == 1 ? 3 : ($insValue['payment'] == 2 ? 6 : 12);
            $isSend = $this->compaireInsData($month,$insValue['service_start_time'],$insValue['service_end_time']);
            if($isSend){
                //TODO 发送付费消息提醒
            }
        }
    }

    /**
     * 获取是否需要发送付费消息提醒
     * @param $month +3 month +6 month
     * @param $startTime string 服务起始时间
     * @param $endTime string 服务截止时间
     * @param string $oldTime string 已经加过的时间
     * @return bool
     */
    private function compaireInsData($month,$startTime,$endTime,$oldTime = ""){
        $now = date("Y-m-d");
        if($oldTime){
            $oldTime = date("Y-m-d",strtotime("$oldTime +$month month"));//加上周期后的时间
        }else{
            $oldTime = date("Y-m-d",strtotime("$startTime +$month month"));//加上周期后的时间
        }
        //加上周期后的时间需小于截止时间
        if(strtotime($oldTime) <= strtotime($endTime)){
            if(strtotime($oldTime) < strtotime($now)){
                $this->compaireInsData($month,$startTime,$endTime,$oldTime);
            }elseif(strtotime($oldTime) == strtotime($now)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 客服发起后服务商一小时未接单则发送提醒(每小时执行一次)
     */
    public function facilicatorNoAcceptOrderForOneHour(){
        //获取主订单（未接单）
        $map['inspection_status'] = 1;//1：新工单(服务商未接单)；2：服务中(服务商已接单)；3：已取消；4：已完成
        $field = "inspection_id,update_time";
        $insOrder = $this->WechatInspectionModel->where($map)->field($field)->select();
        $now = strtotime("now");
        foreach($insOrder as $insValue){
            //根据更新时间判断是否超过一个小时未接单
            $updateTime = $insValue['update_time'];
            $onHourLater = strtotime("$updateTime +1 hour");

            if($onHourLater <= $now){
                //给客服发送超时提醒
                $this->sendServerInsMsg($insValue['inspection_id'],2);
                //给巡检主管发送超时提醒
                $this->sendMasterInsMsg($insValue['inspection_id'],2);
            }
        }
    }
    /**
     * 服务商派单给师傅后一小时未接单则发送提醒(每小时执行一次)
     */
    public function workerNoAcceptOrderForOneHour(){
        //订单状态（1：服务商未派单；2：服务商已派单（巡检员未接单）；3：巡检员已接单；4：开始巡检（服务商不可重复派单）；5：完成巡检（门店待确认）；6：门店点击完成）
        $map['status'] = 2;//获取未接单的子订单
        $field = "inspection_store_child_id,update_time";
        $insOrder = $this->WechatInspectionStoreChildModel->where($map)->field($field)->select();
        $now = strtotime("now");
        foreach($insOrder as $insValue){
            //根据更新时间判断是否超过一个小时未接单
            $updateTime = $insValue['update_time'];
            $onHourLater = strtotime("$updateTime +1 hour");

            if($onHourLater <= $now){
                //给巡检员发送超时提醒
                $this->sendWorkerInsMsg($insValue['inspection_id'],2);
            }
        }
    }
    /**
     * 距离服务日期还有3天（2天，1天）时发消息(每天执行一次)
     */
    public function advanceSendInspectionMsg(){
        //获取主订单门店列表

        $storeMap["wi.inspection_status"] = 2;//订单状态（1：新工单(服务商未接单)；2：服务中(服务商已接单)；3：已取消；4：已完成）
        $storeMap["wis.status"] = 1;//1--启用；2--禁用；-1--删除
        $storeMap["wis.type"] = 2;//1：单次巡检；2：周期性巡检
        $storeField = "wis.inspection_store_id,wis.inspection_id,wis.type,wis.cycle";
        $storeData = $this->WechatInspectionModel->getInspectionStoreData($storeMap,$storeField);
        //根据门店服务周期减去3或2或1得到要发消息的时间，判断当前时间是否等于这些时间，等于则发消息通知
        $now = strtotime(date("Y-m-d"));
        foreach($storeData as $store){
            //获取门店子订单(最近一次完成的订单)
            $childField = "inspection_store_child_id,store_id,inspection_id,service_num,finish_time";
//            $childMap["status"] = 6;
            $childMap["inspection_store_id"] = $store['inspection_store_id'];
            $order = "service_num desc";
            $childData = $this->WechatInspectionStoreChildModel->where($childMap)->field($childField)->order($order)->find();
            //如果已有完成的子订单
            if(!empty($childData)){
                $finishTime = $childData['finish_time'];
                $cycle = $store['cycle'];
                $nextServiceTime = strtotime("$finishTime +$cycle days");
                $remainDays = ($nextServiceTime - $now)/86400;
                if($remainDays == '3'){
                    //给巡检员发送消息提醒
                    $this->sendWorkerInsMsg($childData['inspection_store_child_id'],4,3);
                }elseif($remainDays == '2'){
                    //给巡检员发送消息提醒
                    $this->sendWorkerInsMsg($childData['inspection_store_child_id'],4,2);
                    //给客服发送消息提醒
                    $this->sendServerInsMsg($childData['inspection_store_child_id'],4,false);
                }elseif($remainDays == '1'){
                    //给巡检员发送消息提醒
                    $this->sendWorkerInsMsg($childData['inspection_store_child_id'],4,1);
                }
            }
        }
    }

    /**
     * 紧急订单10分钟内维修主管未接单
     */
    public function emergencyOrderSendMsg(){
        $field = "orde.id,orde.facilitator_id,orde.supervisor_id,item.urgent_level,orde.order_code,orde.update_time,orde.member_id";
        $orderInfo = (new WechatOrderModel())->getOrderAndItemData(['item.urgent_level'=>2,'orde.supervisor_id'=>0,'orde.facilitator_id'=>array('neq','0')],$field);
        if($orderInfo){
            foreach ($orderInfo as $key=>$val) {
                //查看是否推送过
                $orderCrontab = (new WechatOrderCrontabModel())->getDataInfo(['order_id'=>$val['id'],'is_urgent_msg_facilitator'=>1]);
                if(empty($orderCrontab)){
                    //超过十分钟后推送消息
                    if(time() >= date(strtotime($val['update_time'])+60*10)){
                        //获取服务商
                        $facilitatorInfo = (new WechatFacilitatorModel())->getOnceInfo(['id'=>$val['facilitator_id']],'');
                        //根据服务商id获取所有的服务商
                        $facilitatorMemberInfo = (new WechatMemberModel())->getInfoByMap(['facilitator_id'=>$facilitatorInfo['id'],'status'=>1,'isadmin'=>1]);
                        //===============================================服务商推送================================================
                        foreach($facilitatorMemberInfo as $a=>$b){
                            //给服务商推送消息
                            $facilitatorEnterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
                            $param = $facilitatorEnterpriseService->sendFacilitatorMsg($b['wx_code'],$val['id'],$val['order_code'],$facilitatorInfo['id']);
                            $facilitatorEnterpriseService->sendMessage($param);

                        }
                        //===============================================客服端推送================================================
                        //获取所有客服 推送消息
                        $memberInfo = (new WechatMemberModel())->getInfoByMap(['isadmin'=>2,'status'=>1]);
                        foreach($memberInfo as $k=>$v){
                            //给客服推送消息
                            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
                            $param = $enterpriseService->emergencyOrderSendMsg($v['wx_code'],$val['id'],$val['order_code'],$facilitatorInfo['name']);
                            $enterpriseService->sendMessage($param);
                        }

                        //===============================================用户端推送================================================
                        //获取用户信息
                        $memInfo = (new WechatMemberModel())->getOnceInfo(['status'=>1,'uid'=>$val['member_id']]);
                        (new WechatService())->sendUserEmergencyOrderMsg($memInfo['open_id'],$val['id']);

                        //添加推送记录
                        $addOrderCrontab = (new WechatOrderCrontabModel())->addInfo(['order_id'=>$val['id'],'is_urgent_msg_facilitator'=>1]);
                    }
                }
            }
        }
    }

    /**
     * 主管分 十分钟后师傅未接单
     */
    public function sendSingleToWorkers(){
        $field = "orde.id,orde.facilitator_id,orde.supervisor_id,item.urgent_level,orde.order_code,orde.update_time,orde.member_id,orde.order_state";
        $orderInfo = (new WechatOrderModel())->getOrderAndItemData(['item.urgent_level'=>2,'order_state'=>10,'orde.workers_id'=>array('neq','0'),'orde.facilitator_id'=>array('neq','0')],$field);
        if($orderInfo){
            foreach ($orderInfo as $key=>$val) {
                //查看是否推送过
                $orderCrontab = (new WechatOrderCrontabModel())->getDataInfo(['order_id'=>$val['id'],'is_urgent_msg_facilitator'=>2]);
                if(empty($orderCrontab)) {
                    //超过十分钟后推送消息
                    if (time() >= date(strtotime($val['update_time']) + 60 * 10)) {
                        //获取服务商
                        $facilitatorInfo = (new WechatFacilitatorModel())->getOnceInfo(['id' => $val['facilitator_id']], '');
                        //根据服务商id获取所有的服务商
                        $facilitatorMemberInfo = (new WechatMemberModel())->getInfoByMap(['facilitator_id' => $facilitatorInfo['id'], 'status' => 1, 'isadmin' => 1]);
                        //===============================================服务商推送================================================
                        foreach ($facilitatorMemberInfo as $a => $b) {
                            //给服务商推送消息
                            $facilitatorEnterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY, COMPANY_TOKEN, COMPANY_CORPID, COMPANY_DISTRIBUTE_APPSECRET);
                            $param = $facilitatorEnterpriseService->sendFacilitatorWorkerMsg($b['wx_code'], $val['id'], $val['order_code'], $facilitatorInfo['name']);
                            $facilitatorEnterpriseService->sendMessage($param);

                        }
                        //===============================================客服端推送================================================
                        //获取所有客服 推送消息
                        $memberInfo = (new WechatMemberModel())->getInfoByMap(['isadmin' => 2, 'status' => 1]);
                        foreach ($memberInfo as $k => $v) {
                            //给客服推送消息
                            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY, COMPANY_TOKEN, COMPANY_CORPID, COMPANY_CUSTOMER_APPSECRET);
                            $param = $enterpriseService->emergencyOrderWorkerSendMsg($v['wx_code'], $val['id'], $val['order_code'], $facilitatorInfo['name']);
                            $this->sendMessage($param);
                        }

                        //===============================================用户端推送================================================
                        //获取用户信息
                        $memInfo = (new WechatMemberModel())->getOnceInfo(['status' => 1, 'uid' => $val['member_id']]);
                        (new WechatService())->sendUserEmergencyOrderWorkerMsg($memInfo['open_id'], $val['id']);
                        //===============================================师傅端推送================================================
                        $workerEnterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY, COMPANY_TOKEN, COMPANY_CORPID, COMPANY_MAINTAIN_APPSECRET);
                        $param = $workerEnterpriseService->workerTenMinutesNoOrder($v['wx_code'], $val['id'], $val['order_code']);//新工单提醒
                        $workerEnterpriseService->sendMessage($param);

                        //添加推送记录
                        $addOrderCrontab = (new WechatOrderCrontabModel())->addInfo(['order_id'=>$val['id'],'is_urgent_msg_facilitator'=>2]);
                    }
                }
            }
        }
    }

    /**
     * 巡检完成24小时后门店未确认
     */
    public function acceptanceOrder(){

        $WechatService = (new WechatService());
        $WechatMemberModel = (new WechatMemberModel());
        $WechatKaStoresModel = (new WechatKaStoresModel());
        $WechatInspectionModel = (new WechatInspectionModel());
        $WechatInspectionStoreChildModel = (new WechatInspectionStoreChildModel());

        $field = "wisc.inspection_store_child_id,wisc.update_time,wisc.status,store.name,store.id as store_id,wisc.inspection_store_id,wisc.inspection_id";
        //获取巡检完成 门店未确认订单
        $orderInfo = $WechatInspectionStoreChildModel->getChildList(['wisc.status'=>5],$field,'');

        if($orderInfo){
            foreach($orderInfo as $key=>$val) {
                //当前时间大于24小时后自动将修改订单状态
                if(time() >= date(strtotime($val['update_time'])+604800)){
                    $res = $WechatInspectionStoreChildModel->saveInfo(['inspection_store_child_id'=>$val['inspection_store_child_id'],'status'=>6]);
                    //修改订单次数
                    $res1 = $WechatInspectionModel->changeOrderInfo($val['inspection_store_id']);
                    if($res && $res1){
                        //给用户推送消息
                        $memInfo = $WechatMemberModel->getOnceInfo(['isadmin'=>7,'status'=>1,'stores_id'=>$val['store_id']]);
                        //获取门店
                        $storeInfo = $WechatKaStoresModel->getOnceInfo(['status'=>1,'id'=>$val['store_id']]);
                        $WechatService->confirmedOrder($memInfo['open_id'],$val['inspection_store_child_id'],$storeInfo['name'],$val['service_num'],$val['child_order_code']);
                    }
                }
            }
        }
    }

    /**
     * 给巡检主管发送延迟一小时未派单的提醒
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionSupervisorDelayOneHourMsg(){

        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);

        //1.查询巡检子订单状态为 服务商未派单
        $wechatInspectionStoreChildModel = (new WechatInspectionStoreChildModel());

        $yesterday = date("Y-m-d 00:00:00",strtotime("-1 day"));
        $today = date('Y-m-d 23:59:59');
        $where['wisc.status'] = 1;
        $where['wisc.update_time'] = ['between',[$yesterday,$today]];
        $field = 'wisc.inspection_store_child_id,wisc.service_num,wisc.child_order_code,wisc.update_time,store.name storeName,wms.wx_code';

        $inspectionData = $wechatInspectionStoreChildModel->getInspectionChildStoreMemberData($where,$field);

        if(!$inspectionData){
            return;
        }

        //2.循环将超过1小时未派单的订单，巡检主管标识查出
        $now = strtotime("now");

        //获取当前小时
        $nowHour = date("h");
        foreach($inspectionData as $val){
            $updateTime = $val['update_time'];

            //判断如果是昨天晚上9点到今天早上9点的时间段内生成的订单，就在今天早上10点钟统一发送
            if($nowHour == 10){

                $onHourLater = strtotime($updateTime);

                //筛选出从昨天9点到今天9点的单子
                if(($now - $onHourLater) <=46800){
                    //给巡检主管发送消息
                    $enterpriseService->sendInspectionSupervisorDelayOneHourMsg($val);
                }
            }else{
                $oneHourLater = strtotime("$updateTime -1hour");

                 //筛选出从昨天9点到今天9点的单子
                if(($now - $oneHourLater) <=3600){
                    //给巡检主管发送消息
                    $enterpriseService->sendInspectionSupervisorDelayOneHourMsg($val);
                }
            }
        }
    }

    /**
     * 给巡检员发送延迟一小时未接单的提醒
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionWorkerDelayOneHourMsg(){

        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_MAINTAIN_APPSECRET);

        //1.查询巡检子订单状态为 服务商已派单（巡检员未接单）
        $wechatInspectionStoreChildModel = (new WechatInspectionStoreChildModel());

        $yesterday = date("Y-m-d 00:00:00",strtotime("-1 day"));
        $today = date('Y-m-d 23:59:59');
        $where['wisc.status'] = 2;
        $where['wisc.update_time'] = ['between',[$yesterday,$today]];

        $field = 'wisc.inspection_store_child_id,wisc.service_num,wisc.child_order_code,wisc.update_time,store.name storeName,wms.wx_code';

        $inspectionData = $wechatInspectionStoreChildModel->getInspectionChildStoreMemberData($where,$field);

        if(!$inspectionData){
            return;
        }

        //2.循环将超过1小时未派单的订单，巡检主管标识查出
        $now = strtotime("now");

        //获取当前小时
        $nowHour = date("h");
        foreach($inspectionData as $val){
            $updateTime = $val['update_time'];

            //判断如果是昨天晚上9点到今天早上9点的时间段内生成的订单，就在今天早上10点钟统一发送
            if($nowHour == 10){

                $onHourLater = strtotime($updateTime);

                //筛选出从昨天9点到今天9点的单子
                if(($now - $onHourLater) <=46800){
                    //给巡检员发送消息
                    $enterpriseService->sendInspectionWorkerDelayOneHourMsg($val);
                }else{
                    $oneHourLater = strtotime("$updateTime -1hour");

                    //筛选出从昨天9点到今天9点的单子
                    if(($now - $oneHourLater) <=3600){
                        //给巡检员发送消息
                        $enterpriseService->sendInspectionWorkerDelayOneHourMsg($val);
                    }
                }
            }
        }
    }

    /**
     * 给客服发送 巡检员添加设备提醒
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendNewDeviceCustomerServicesMsg(){

        $wechatDeviceModel = (new WechatDeviceModel());
        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);

        //上个小时
        $lastTime = Date('Y-m-d H:i:s',strtotime('-1 hour'));

        //当前时间
        $nowTime = Date('Y-m-d H:i:s');

        //1.获取符合规则的数据
        $where['d.status'] = 3;
        $where['d.inspection_store_child_id'] = ['neq',0];
        $where['d.create_time'] = ['between',[$lastTime,$nowTime]];

        $fields = 'd.inspection_store_child_id,wm.wx_code,wks.`name` storeName,wisc.service_num,wisc.child_order_code';
        $deviceData = $wechatDeviceModel -> getDeviceInspectionMemData($where,$fields);

        if(!$deviceData){
            return;
        }

        //2.循环 处理数据 发送消息
        foreach($deviceData as $val){
            $enterpriseService -> sendInspectionNewDeviceMsg($val);
        }

    }

    /**
     * 地址移动
     */
    public function addressMove(){
        //查询所有地址信息数据（经纬度不为空）
        $memberData = (new WechatMemberModel())->where(['latng'=>['neq','']])->select();
//        $memberData = (new WechatMemberModel())->where(['uid'=>4810])->select();
        $provinceId = 110000;
        $cityId = 0;
        $city = "";
        foreach($memberData as $key=>$value){
            $str1 = mb_substr($value['location_name'],2,1);
            $str2 = mb_substr($value['location_name'],3,1);
            $str3 = mb_substr($value['location_name'],5,1);
            $str4 = mb_substr($value['location_name'],6,1);
            if($str1 == "区"){
                $city = mb_substr($value['location_name'],0,3);
                $cityId = M('district')->where(['name'=>$city])->field('id')->find();
            }elseif($str2 == "区"){
                $city = mb_substr($value['location_name'],0,4);
                $cityId = M('district')->where(['name'=>$city])->field('id')->find();
            }elseif($str3 == "区"){
                $city = mb_substr($value['location_name'],3,3);
                $cityId = M('district')->where(['name'=>$city])->field('id')->find();
            }elseif($str4 == "区"){
                $city = mb_substr($value['location_name'],3,4);
                $cityId = M('district')->where(['name'=>$city])->field('id')->find();
            }
//            echo $value['location_name']."----------".$city."<br/>";
            //插入地址
            $data['store_name'] = $value['store_name'];
            $data['latng'] = $value['latng'];
            $data['location_address'] = $value['location_name'];
            $data['city_id'] = $cityId['id'];
            $data['province_id'] = $provinceId;
            $data['detail_address'] = $value['address_name'] ? $value['address_name'] : $value['location_name'];
            $data['link_person'] = $value['link_name'] ? $value['link_name'] : $value['name'];
            $data['link_phone'] = $value['phone'];
            $data['is_default'] = 1;
            $data['member_id'] = $value['uid'];
            $data['create_time'] = $data['update_time'] = date("Y-m-d H:i:s");
            $res = M('wechatAddress')->add($data);
            echo $res."---".$key."---".$city."---".$cityId['id']."----".$value['uid']."---"."<br/>";
        }
    }

    /**
     * 给用户、主管 发送后台自己创建的订单消息
     * 调用接口是在新后台 backstagetest.canxunnet.com 测试地址
     * @param $_POST {
                "orderType": "storeCleanKill",
                "yearServiceType": "year",
                "allocationSupervisorWXcode": "huangqing",
                "orderArr": [{
                "orderId": "1180",
                "orderCode": "1812191714559341",
                "storeUserOpenId": "o3-pW0223YEu_MA0pt6agnqgzn2g"
                }, {
                "orderId": "1181",
                "orderCode": "1812191714554097",
                "storeUserOpenId": "o3-pW0223YEu_MA0pt6agnqgzn2g"
                }, {
                "orderId": "1182",
                "orderCode": "1812191714558295",
                "storeUserOpenId": "o3-pW0223YEu_MA0pt6agnqgzn2g"
                }]
                }
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendBackStageCreateInstantMsg(){
    	set_time_limit(0);
        //@file_put_contents("huangqing.log", "获取到的数据 ：".json_encode($_POST)."\t\n",FILE_APPEND);

        $data = json_decode(json_encode($_POST),true);
        //获取传递来的数据
        if(empty($data) || empty($data['orderType'])  || empty($data['yearServiceType'])  || empty($data['allocationSupervisorWXcode'])  || empty($data['orderArr'])){
            return false;
        }

        //1.判断订单类型 orderType：storeCleanKill(门店消杀)，设备维修、设备清洗、巡检（暂无添加）
        $orderType = '';
        switch($data['orderType']){
            case 'storeCleanKill': $orderType = 2;
                break;
            default:
                return false;
        }

        //2.判断订单服务类型 yearServiceType  one：单次服务 year：年服务
//        switch($data['yearServiceType']){
//            case 'one' : ;break;
//            case 'year' : ;break;
//            default :
//                return false;
//        }

        $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);

        if($data['yearServiceType'] == 'one'){
            //循环处理
            foreach($data['orderArr'] as $val){

                //判断 线上 线下单
                if($data['payment'] == 'online') {
                    //3.给用户发送消息
                    $url = host_url.'/index.php?s=/wechat/index/showCleankillOrder/order_id/'.$val['orderId'];
                    $remark = '您在餐讯网购买的门店消杀服务订单已生成，订单号：'.$val['orderCode'].'，请您及时付款！餐讯网客服电话：18810250371（上班时间9：00-22：00）';
                    $this->WechatService->sendUserCreateOrderMsg($val['storeUserOpenId'],'订单状态提醒',"订单生成通知",$val['orderCode'],date("Y-m-d H:i:s"),$remark,$url);

                }else{
                    //3.给用户发送消息
                    $url = host_url . '/index.php?s=/wechat/index/showCleankillOrder/order_id/' . $val['orderId'];
                    $remark = '您在餐讯网购买的门店消杀服务订单已生成，订单号：' . $val['orderCode'] . '，服务商将在平台规定时间内与您联系，请保持电话畅通，您可以在订单信息中查看订单状态！餐讯网客服电话：18810250371（工作时间9：00-22：00）';
                    $this->WechatService->sendUserCreateOrderMsg($val['storeUserOpenId'],'订单状态提醒',"订单生成通知",$val['orderCode'],date("Y-m-d H:i:s"),$remark,$url);
                }

                //4.给分配主管发送消息
                $distributeUrl = host_url . "/Enterprise/DistributionSupervisor/orderDetail?type=".$orderType."&order_id=".$val['orderId']."&fid=".$data['allocationSupervisorFid'];
//                $distributeUrl = host_url . "/Enterprise/DistributionSupervisor/showCleanKillYearOrder?order_id=".$val['orderId'];
                $description = '您好，餐讯网给您分派了【门店消杀】的新订单，订单号：'.$val['orderCode'].'，请查看并及时操作订单分配【点击查看详情】';
                $distributeService->sendPicTextMsg($data['allocationSupervisorWXcode'],'','订单状态提醒',$description,$distributeUrl);
            }
        }else{
            //循环处理
            foreach($data['orderArr'] as $val){

                //判断 线上 线下单
                if($data['payment'] == 'online') {
                    //3.给用户发送消息
                    $url = host_url.'/index.php?s=/wechat/index/showCleankillOrder/order_id/'.$val['orderId'];
                    $remark = '您在餐讯网购买的门店消杀服务订单已生成，订单号：'.$val['orderCode'].'，请您及时付款！餐讯网客服电话：18810250371（上班时间9：00-22：00）';
                    $this->WechatService->sendUserCreateOrderMsg($val['storeUserOpenId'],'订单状态提醒',"订单生成通知",$val['orderCode'],date("Y-m-d H:i:s"),$remark,$url);
                }else{
                    //3.给用户发送消息
                    $url = host_url . '/index.php?s=/wechat/index/showCleanKillYearOrder/order_id/' . $val['orderId'];
                    $remark = '您在餐讯网购买的门店消杀服务订单已生成，订单号：' . $val['orderCode'] . '，服务商将在平台规定时间内与您联系，请保持电话畅通，您可以在订单信息中查看订单状态！餐讯网客服电话：18810250371（工作时间9：00-22：00）';
                    $this->WechatService->sendUserCreateOrderMsg($val['storeUserOpenId'],'订单状态提醒',"订单生成通知",$val['orderCode'],date("Y-m-d H:i:s"),$remark,$url);
                }

                //4.给分配主管发送消息
                $distributeUrl = host_url . "/Enterprise/DistributionSupervisor/showCleanKillYearOrder?order_id=".$val['orderId'];
                $description = '您好，餐讯网给您分派了【门店消杀】的新订单，订单号：'.$val['orderCode'].'，请查看并及时操作订单分配【点击查看详情】';
                $distributeService->sendPicTextMsg($data['allocationSupervisorWXcode'],'','订单状态提醒',$description,$distributeUrl);
            }
        }

        //5.返回
        return 'OK';
    }

    /**
     * 发送修改订单服务商消息
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendChangeFacFromSuperviseMsg(){

        $postData = json_decode(json_encode($_POST),true);

        if(!$postData['orderId'] && !is_int($postData['orderId'])){
            return 'NOT OK';
        }

        //派单后给分配主管发消息提醒
        $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
        $param = $distributeService->getDistributeSendMsg($postData['orderId'],1);//新工单提醒
        $distributeService->sendMessage($param);//新工单提醒

        //给用户发送更换服务商消息
        $this -> WechatService ->sendMsgChangeOrderFac($postData['orderId']);

        return 'OK';
    }
}