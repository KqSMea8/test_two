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
use Think\Controller;
use think\Cache;
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
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;
use Goods\Model\SomethingPicModel;
use Wechat\Model\WechatDeviceModel;

class BusinessExecutiveController extends Controller{

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
    }

    private function getWxUserId(){
        //判断是否存在缓存userId
        $wxUserId = session('wxUserId');
        //$wxUserId = "weigo521";//测试使用

        if(!$wxUserId){

            if(isset($_REQUEST['code'])){
                file_put_contents("pangyongfu.log",json_encode($_REQUEST)."\r\n",FILE_APPEND);
                $data = $this->svr->getUserId($_REQUEST['code']);
                session('wxUserId',$data['UserId']);
                return $data['UserId'];
            }else{

                $url = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                $redirectUrl = $this->svr->getUserCode($url);

                file_put_contents("pangyongfu.log",$redirectUrl."\r\n",FILE_APPEND);
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
//        $wxUserId = '13041037986';

        $facidata = $this->WechatMemberModel->where(['isadmin'=>6,"status" => 1,"wx_code" => $wxUserId])->find();

        return $facidata;
    }


    /**
     * 巡检主订单列表
     * @param int $status 1：派单中；2：已接单；3：服务中；4：已完成
     */
    public function showMainOrderList($status = 1){
        $userInfo = $this->getUserInfo();

        session('userInfoEnterpriseId',$userInfo['enterprise_id']);

        $map['wi.inspection_status'] = $status;
        $map['wi.enterprise_id'] = $userInfo['enterprise_id'];
        $map['wis.status'] = 1;//1--启用；2--禁用；-1--删除
        $field = "count(*) store_num,wi.enterprise_name,wi.inspection_id,wi.inspection_code,wi.create_time";
        $this->inspectionList = $this->WechatInspectionModel->getInspectionOrderData($map,$field);
        $this->display('WechatPublic/BusinessExecutive/showMainOrderList');
    }


    /**
     * 订单详情
     */
    public function showMainOrderDetail(){
        $inspectionId = I('inspection_id');

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
        $this->display('WechatPublic/BusinessExecutive/showMainOrderDetail');

    }


    /**
     * @param int $status
     */
    public function getInspectionMainOrderList($status = 1){

        $map['wi.inspection_status'] = $status;
        $map['wis.status'] = 1;//1--启用；2--禁用；-1--删除
        $map['wi.enterprise_id'] = session('userInfoEnterpriseId');
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
        $this->display('WechatPublic/BusinessExecutive/showMainOrderStoreList');
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
        $this->display('WechatPublic/BusinessExecutive/showChildOrderList');
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

            $list['status_text'] = C('INSPECTION_CHILDSTATUS')[$list['status']];
            $list['store_name'] = $storeInfo['name'];
            $list['facilitator_name'] = $facilitatorInfo['name'];
            $list['user_name'] = $memberInfo['name'];
            $list['user_phone'] = $memberInfo['phone'];
            $list['id'] = $list['inspection_store_child_id'];
            $this->assign("list",$list);
            $this->assign("imgInfo",$imgInfo);
            $this->assign("imgCount",count($imgInfo));
            $this->assign("store_id",json_encode($list['store_id']));
            $this->assign("inspection_store_child_id",json_encode($list['inspection_store_child_id']));
            $this->display('WechatPublic/BusinessExecutive/storeInspection');

        }else{
            //提示页面数据来源
            $this -> assign('childInfo',$childInfo);
            $this->display('WechatPublic/BusinessExecutive/showChildOrderDetail');
        }
    }
    /**
     * 获取设备信息
     */
    public function getDevice(){
        $id = $_REQUEST['id'];
        $inspection_store_child_id = $_REQUEST['inspection_store_child_id'];
        $name = $_REQUEST['name'];
        $map = ['store_id'=>$id,'status'=>1,'device_name'=>array('like','%'.$name.'%')];
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
     * 未派单 - 企业主管
     */
    public function noReceiveOrder(){
        $userInfo = $this->getUserInfo();
        //获取派单中的订单列表
        $map['orde.order_state'] = 0;//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收)
        $map['orde.is_year'] = 0;
        $map['orde.facilitator_id'] = 0;
        $map['orde.ka_enterprise_id'] = $userInfo['enterprise_id'];
        $field = "orde.id,orde.order_code,orde.create_time,orde.order_type,member.uid,member.name uname,item.urgent_level";
        $this->orderData = $this->WechatOrderModel->getEnterpriseOrderDataByStatus($map,$field);
        $this->display('WechatPublic/BusinessExecutive/noReceiveOrder');
    }

    /**
     * 已派单 - 企业主管
     */
    public function yesReceiveOrder(){
        $userInfo = $this->getUserInfo();
        $enterpriseInfo = $this->WechatKaEnterpriseModel->getOnceData(['id'=>$userInfo['enterprise_id']]);
        //首次访问该页面默认显示未接单数据
        $map['orde.order_state'] = ['in',[0,10]];//(0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        $map['orde.facilitator_id'] = ['neq',0];
        $map['orde.is_main'] = 1;
        $map['orde.province'] = $enterpriseInfo['code'];
        $field = "orde.id,orde.province,orde.order_code,orde.is_year,orde.order_state,orde.is_main,orde.store_name,orde.create_time,orde.order_type,faci.id fid,faci.name uname,item.urgent_level";
        $orderData = $this->WechatOrderModel->getEnterpriseOrderDataByStatus($map,$field);
        if(!empty($orderData)){
            foreach($orderData as &$val){
                $val['order_state_text'] = $this->svr->getOrderStatusText($val['order_state']);
            }
        }
        $this->orderData = $orderData;
        $this->display('WechatPublic/BusinessExecutive/yesReceiveOrder');
    }
}