<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author HQ
 */

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Model\PictureModel;
use Wechat\Model\WechatDeviceModel;
use Wechat\Model\WechatInspectionStoreChildDeviceModel;
use Wechat\Model\WechatMemberStoreModel;
use Wechat\Model\WechatShareArticleModel;
use Wechat\Service\AnalyzeExcelService;
use Enterprise\Service\EnterpriseService;
use Wechat\Model\WechatActivityModel;
use Wechat\Model\WechatEquipmentCategoryModel;
use Wechat\Model\WechatEquipmentCategoryOperationModel;
use Wechat\Model\WechatFacilitatorModel;
use Wechat\Model\WechatKaEnterpriseModel;
use Wechat\Model\WechatKaStoresModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderAppraiseModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatInspectionModel;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatInspectionStoreModel;
use Wechat\Model\WechatServiceGuidelinesModel;
use Wechat\Model\WechatTicketModel;
use Wechat\Model\WechatVocationRadarModel;
use Wechat\Service\WechatService;

class WechatController extends AdminController{

    private $mealTypeList = [
        1=>'中餐',
        2=>'西餐',
        3=>'韩国料理',
        4=>'日本料理',
        5=>'东南亚菜',
        6=>'烧烤',
        7=>'火锅',
        8=>'小吃快餐'
    ];

    function _initialize()
    {
        parent::_initialize();
        $this->WechatServiceGuidelinesModel = new WechatServiceGuidelinesModel();
        $this->WechatEquipmentCategoryModel = new WechatEquipmentCategoryModel();
        $this->WechatEquipmentCategoryOperationModel = new WechatEquipmentCategoryOperationModel();
        $this->WechatMemberModel = new WechatMemberModel();
        $this->WechatOrderModel = new WechatOrderModel();
        $this->WechatOrderItemModel = new WechatOrderItemModel();
        $this->WechatOrderAppraiseModel = new WechatOrderAppraiseModel();
        $this->WechatFacilitatorModel = new WechatFacilitatorModel();
        $this->WechatVocationRadarModel = new WechatVocationRadarModel();
        $this->WechatTicketModel = new WechatTicketModel();
        $this->WechatActivityModel = new WechatActivityModel();

        $this->WechatService = new WechatService();
        $this->WechatKaEnterpriseModel = new WechatKaEnterpriseModel();
        $this->WechatKaStoresModel = new WechatKaStoresModel();
        $this->WechatDeviceModel = new WechatDeviceModel();
    }

    /**************************************************空白页面展示************************************************************/
    public function blankPage(){
        $builder = new AdminConfigBuilder();
        $builder->title('微信模块')
            ->display();
    }
    /**************************************************空白页面结束************************************************************/

    //*************************************************服务须知列表开始**************************************************

    //服务须知列表start
    public function serviceGuidelinesList($page=1,$r=20)
    {
        $map = [];
        $map['status']=['neq',-1];
        $map['type']=['in',[1,2,3]];

        list($list,$totalCount)=$this->WechatServiceGuidelinesModel->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){

            switch($val['type']){
                case 1:
                    $val['typeText'] = '门店维修';
                    break;
                case 2:
                    $val['typeText'] = '门店消杀';
                    break;
                case 3:
                    $val['typeText'] = '烟道清洗';
                    break;
            }
        }

        $builder=new AdminListBuilder();
        $builder->title('服务须知列表')
            ->data($list)
            ->buttonNew(U('Wechat/editServiceGuidelines'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/delServiceGuidelines'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/startServiceGuidelines'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/endServiceGuidelines'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('typeText','服务须知对应入口')
            ->keyGxptStatus()
            ->keyText('create_time','创建时间')
            ->keyText('update_time','更新时间')
            ->keyDoActionEdit('Wechat/editServiceGuidelines?id=###');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 服务须知添加
     * @param int $id
     */
    public function editServiceGuidelines($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            $data = I('post.');
            if($_POST["guidelinesType"] == 0){

                $error .= "不可为空！";
            }else{
                $data['type'] = $data['guidelinesType'];
                unset($data['guidelinesType']);
            }

            if(empty($_POST["content"])){

                $error .= "内容不可为空！";
            }
            //如果为添加则验证是否已有该条记录
            if(empty($id)){
                $haveData = $this->WechatServiceGuidelinesModel->where(['type'=>['eq',$data['type']],'status'=>['neq',-1]])->select();
                if(!empty($haveData)){
                    $error .= $data['type']==1 ? "门店维修须知已存在":($data['type']==2 ? "门店消杀须知已存在":"烟道清洗须知已存在");
                }
            }
            if(!empty($error)){

                $this->error($error);
            }

            if ($cate = $this->WechatServiceGuidelinesModel->editData($data)) {

                $this->success($title.L('_SUCCESS_'), U('Wechat/serviceGuidelinesList'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatServiceGuidelinesModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatServiceGuidelinesModel->find($id);
            }

            $builder->title($title.L('服务须知'))
                ->data($data)
                ->keyId()
                ->keySelect('guidelinesType','服务须知对应的入口','',['0' => '请选择','1' => '门店维修','2' => '门店消杀','3' => '烟道清洗'])->keyDefault('guidelinesType',$data['type'])
                ->keyEditor('content',L('_CONTENT_'),'','all',['width' => '700px', 'height' => '400px'])
                ->buttonSubmit(U('Wechat/editServiceGuidelines'))->buttonBack()
                ->display();
        }
    }

    //删除服务须知数据
    public function delServiceGuidelines(){

        $Ids = I('post.ids');
        if($Ids){
            $this->WechatServiceGuidelinesModel->where(['id'=>['in',$Ids]])->save(['status' => '-1']);
        }

        $this->success('删除成功');
    }

    //启用服务须知数据
    public function startServiceGuidelines(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatServiceGuidelinesModel->where(['id'=>['IN',$Ids]])->save(['status'=>1]);
        }

        $this->success('启用成功');
    }

    //禁用服务须知数据
    public function endServiceGuidelines(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatServiceGuidelinesModel->where(['id'=>['IN',$Ids]])->save(['status'=>2]);
        }

        $this->success('禁用成功');
    }


    /////////////////////////////////////////////////////////////服务须知结束/////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////设备类别开始/////////////////////////////////////////////////////////////////////////////

    /**
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function equipmentCategoryList($page=1,$r=20){
        $map = [];
        $map['status']=['neq',-1];

        list($list,$totalCount)=$this->WechatEquipmentCategoryModel->getListByPage($map,$page,'update_time desc','*',$r);

        $builder=new AdminListBuilder();
        $builder->title('设备类别列表')
            ->data($list)
            ->buttonNew(U('Wechat/editEquipmentCategory'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/delEquipmentCategory'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/startEquipmentCategory'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/endEquipmentCategory'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('name','类别名称')
            ->keyGxptStatus()
            ->keyDoActionEdit('Wechat/editEquipmentCategory?id=###');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 编辑设备类别信息
     * @author  pangyongfu
     */
    public function editEquipmentCategory($id=0){
        $title=$id?L('_EDIT_'):L('_ADD_');

        $equipmentInfo = $this->WechatEquipmentCategoryModel->getData($id);
        $equipmentOperationInfo = $this->WechatEquipmentCategoryOperationModel->getListInfo(['equipment_category_id'=>$id,'status'=>1]);
        $this->assign('equipmentInfo',$equipmentInfo);
        $this->assign('equipmentOperationInfo',$equipmentOperationInfo);
        $this->assign('countEquipmentOperationInfo',count($equipmentOperationInfo));
        $this->assign('id',$id);
        $this->display('editEquipmentCategory');
    }
    //修改数据
    public function saveEquipment(){
        $id = $_POST['id'];
        $name = $_POST['name'];
        $title=$id?L('_EDIT_'):L('_ADD_');
        if($id){
            $a = [];
            $b = [];
            //修改
            $equipmentOperationInfo = $this->WechatEquipmentCategoryOperationModel->getListInfo(['equipment_category_id'=>$id,'status'=>1],'id');
            //二维数组变一维数组
            array_walk_recursive($equipmentOperationInfo, function($value) use (&$a) {
                array_push($a, $value);
            });
            array_walk_recursive($_POST['ids'], function($value) use (&$b) {
                array_push($b, $value);
            });
            //获取两个数组不同的值
            $result=array_diff($a,$b);
            foreach($result as $k=>$v){
              $res = $this->WechatEquipmentCategoryOperationModel->editData(['id'=>$v,'status'=>-1]);
            }
            $data['id'] = $id;
            $data['name'] = $name;
            foreach($_POST['xunjian'] as $key=>$val){
                if(empty($val['name'])){
                    $arr['status'] = -1;
                }else{
                    $arr['name'] = $val['name'];
                    $arr['status'] = 1;
                }
                $arr['id'] = $val['operationId'];
                $arr['equipment_category_id'] = $id;
                $res = $this->WechatEquipmentCategoryOperationModel->editData($arr);
            }
            $res1 = $this->WechatEquipmentCategoryModel->editData($data);

        }else{
            $data['name'] = $name;
            $res = $this->WechatEquipmentCategoryModel->editData($data);

            foreach($_POST['xunjian'] as $key=>$val){
                if(!empty($val['name'])){
                    $arr['name'] = $val['name'];
                    $arr['id'] = $val['operationId'];
                    $arr['equipment_category_id'] = $res;
                    $arr['status'] = 1;
                    $res1 = $this->WechatEquipmentCategoryOperationModel->editData($arr);
                }
            }
        }
        if ($res &&  $res1) {
            $this->ajaxReturn(['status'=>1,'info'=>'操作成功']);
            $this->success($title.L('_SUCCESS_'), U('Wechat/equipmentCategoryList'));
        } else {
            $this->ajaxReturn(['status'=>0,'info'=>'操作失败']);

        }
    }
    //删除设备类别数据
    public function delEquipmentCategory(){

        $Ids = I('post.ids');
        if($Ids){
            $this->WechatEquipmentCategoryModel->where(['id'=>['in',$Ids]])->save(['status' => '-1']);
        }

        $this->success('删除成功');
    }

    //启用设备类别数据
    public function startEquipmentCategory(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatEquipmentCategoryModel->where(['id'=>['IN',$Ids]])->save(['status'=>1]);
        }

        $this->success('启用成功');
    }

    //禁用设备类别数据
    public function endEquipmentCategory(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatEquipmentCategoryModel->where(['id'=>['IN',$Ids]])->save(['status'=>2]);
        }

        $this->success('禁用成功');
    }

    /////////////////////////////////////////////////////////////设备类别结束/////////////////////////////////////////////////////////////////////////////


    /////////////////////////////////////////////////////////////门店维修开始/////////////////////////////////////////////////////////////////////////////
    /**
     * 维修订单管理
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function maintenanceOrderList($page=1,$r=12,$is_ka=0,$is_sh=0){

        //拼接搜索商品名称
        $order_no = I("order_no");
        $store_name = I("store_name");
        if($order_no != ""){
            $map['orde.order_code'] = ['like','%'.$order_no.'%'];
        }
        if($store_name != ""){
            $map['orde.store_name'] = ['like','%'.$store_name.'%'];
        }
        $enterprise_name = I("enterprise_name");
        if($enterprise_name != ""){
            $map['orde.enterprise_name'] = ['like','%'.$enterprise_name.'%'];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }
        $clean_type = I("equipment_type");
        if($clean_type != 0){
            $map['orde.equipment_id'] = ['eq',$clean_type];
        }
        $order_statu = I("order_state");
        if($order_statu != 0){
            $order_statu -= 1;
            $map['orde.order_state'] = ['eq',$order_statu];
            //筛选已完成订单包括已评价订单
            if($order_statu == 3){
                $map['orde.order_state'] = ['in',[3,7]];
            }
        }
        $map['orde.order_type'] = ['eq',1];//1门店维修，2门店消杀，3烟道清洗
        $map['orde.is_ka'] = ['eq',$is_ka];//0：普通订单 1：KA订单
        $map['orde.is_sh'] = ['eq',$is_sh];//是否为售后订单 0：否 1：是

        //获取登录用户绑定的微信服务商标识
        $facilitatorId = getUserBindingFacilitator();
        $facilitatorData = (new WechatKaEnterpriseModel())->getData(['id'=>['IN',$facilitatorId]],'id,name');

        if($facilitatorData && $is_ka){
            $map['orde.enterprise_name'] = ['IN',implode(",", array_column($facilitatorData,'name'))];
        }

        $field = "orde.*,item.change_parts_text,item.parts_price,item.equipment_name,item.after_sale_text,item.old_order_code,equipment.name equipment_type,member.uid,member.name,faci.id faci_id,faci.name faci_name,oa.content,item.cancel_text";
        list($list,$totalCount)=$this->WechatOrderModel->getOrderMemberListByPage($map,$page,'orde.update_time desc',$field,$r);

        $equipmentList = $this->WechatEquipmentCategoryModel->where(['status'=>['eq',1]])->field('id,name value')->select();
        foreach($list as &$vcles){

            $vcles['service_price'] = $vcles['service_price'] ? $vcles['service_price'] : 0;
            $vcles['door_price'] = $vcles['door_price'] ? $vcles['door_price'] : 0;
            $vcles['totle_price'] = $vcles['money_total'];

            //拼接订单完成时间
            if($vcles['order_state']==3 || $vcles['order_state']==7){
                $vcles['orderFinishTime']=$vcles['update_time'];
            }else{
                $vcles['orderFinishTime']='空';
            }

            $vcles['order_state'] = $this->getOrderStatusText($vcles['order_state']);
        }

        //导出excel数据列表
        $content = I('');
        $attr['href'] = U('exportMaintenanceOrderExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        if($is_ka && $is_sh){
            $builder->title(L('KA售后维修订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/maintenanceOrderList',array('is_ka'=>1,'is_sh'=>1)))
                ->search("订单号",'order_no','text','')
                ->search("企业名称",'enterprise_name','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('设备类型：','equipment_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$equipmentList))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消1', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已退款',array('data-title'=>'标记已退款','target-form'=>'ids'))
                //->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyText('orderFinishTime','完成时间')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('city','门店编号')
                ->keyHtml('enterprise_name','所属企业','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('province','企业编号')
                ->keyHtml('equipment_type','设备类型','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('equipment_name','设备名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('name','师傅名称')
                ->keyText('service_price','服务费用（元）')
                ->keyText('door_price','上门费用（元）')
                ->keyHtml('change_parts_text','配件名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('parts_price','配件价格（元）')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('after_sale_text','售后原因','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('old_order_code','关联订单号','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
          //      ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }else if($is_ka && !$is_sh){
            $builder->title(L('KA维修订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/maintenanceOrderList',array('is_ka'=>1)))
                ->search("订单号",'order_no','text','')
                ->search("企业名称",'enterprise_name','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('设备类型：','equipment_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$equipmentList))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消2', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->buttonModalPopup(U('Wechat/doRefund'),null,'标记已退款',array('data-title'=>'标记已退款','target-form'=>'ids'))
                //->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyText('orderFinishTime','完成时间')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('city','门店编号')
                ->keyHtml('enterprise_name','所属企业','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('province','企业编号')
                ->keyHtml('equipment_type','设备类型','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('equipment_name','设备名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('service_price','服务费用（元）')
                ->keyText('door_price','上门费用（元）')
                ->keyHtml('change_parts_text','配件名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('parts_price','配件价格（元）')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
       //         ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }else if(!$is_ka && $is_sh){
            $builder->title(L('个人维修售后订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/maintenanceOrderList',array('is_sh'=>1)))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('设备类型：','equipment_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$equipmentList))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消3', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->buttonModalPopup(U('Wechat/doRefund'),null,'标记已退款',array('data-title'=>'标记已退款','target-form'=>'ids'))
                //->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
                ->keyText('order_code','订单号')
                ->keyHtml('create_time','下单时间','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('orderFinishTime','完成时间','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('equipment_type','设备类型','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('equipment_name','设备名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('service_price','服务费用（元）')
                ->keyText('door_price','上门费用（元）')
                ->keyHtml('change_parts_text','配件名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('parts_price','配件价格（元）')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('after_sale_text','售后原因','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('old_order_code','关联订单号','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
       //         ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }else{
            $builder->title(L('维修订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/maintenanceOrderList'))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('设备类型：','equipment_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$equipmentList))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消4', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->buttonModalPopup(U('Wechat/doRefund'),null,'标记已退款',array('data-title'=>'标记已退款','target-form'=>'ids'))
                //->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyText('orderFinishTime','完成时间')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('equipment_type','设备类型','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('equipment_name','设备名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('faci_name','服务商','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('service_price','服务费')
                ->keyText('door_price','上门费')
                ->keyHtml('change_parts_text','配件名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('parts_price','配件价格')
                ->keyText('totle_price','总计费用')
                ->keyText('order_state','订单状态')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
          //      ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }
    }

    /**
     * 标记取消原因设置
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function doAudit()
    {
        if(IS_POST){

            $ids=I('post.ids','','text');
            $ids=explode(',',$ids);
            $reason=I('post.reason','','text');
            $this->WechatOrderItemModel->where(array('order_id'=>array('in',$ids)))->setField(array('cancel_text'=>$reason));
            $res=$this->WechatOrderModel->where(['id'=>['IN',$ids]])->setField(['order_state'=>4]);
            //取消订单时，如果为局气订单则调用领值接口
            $fields = "orde.id,orde.order_code,orde.order_state,orde.door_price,orde.service_price,orde.money_total,item.parts_price,item.mr_id";
            $orderData = $this->WechatOrderModel->getOrderAndItemData(['orde.id'=>['IN',$ids]],$fields);

            //TODO 如果是领值订单，则通知领值系统
            foreach($orderData as $lk => $lv){
                if(!empty($lv['mr_id'])){
                    $this->WechatService->curlCancel($lv['mr_id'],'cancel',$reason);
                }
            }
            if($res){
                $result['status']=1;
                //$result['info']=L('_OPERATE_FAIL_');
                $result['url']=$_SERVER['HTTP_REFERER'];
            }else{
                $result['status']=0;
                $result['info']=L('_OPERATE_FAIL_');
            }
            $this->ajaxReturn($result);
        }else{
            $ids=I('ids');
            $ids=implode(',',$ids);
            $this->assign('ids',$ids);
            $this->display(T('Wechat@Admin/audit'));
        }
    }

    /**
     * 标记退款原因
     */
    public function doRefund()
    {
        if(IS_POST){
            $price=I('post.price','');
            $ids=I('post.ids','','text');
            $ids=explode(',',$ids);
            $reason=I('post.reason','','text');
            $this->WechatOrderItemModel->where(array('order_id'=>array('in',$ids)))->setField(array('refund_reason'=>$reason,'refund_price'=>$price));
            $res=$this->WechatOrderModel->where(['id'=>['IN',$ids]])->setField(['order_state'=>6]);
            if($res){
                $result['status']=1;
                $result['url']=$_SERVER['HTTP_REFERER'];
            }else{
                $result['status']=0;
                $result['info']=L('_OPERATE_FAIL_');
            }
            $this->ajaxReturn($result);
        }else{
            $ids=I('ids');
            $ids=implode(',',$ids);
            $this->assign('ids',$ids);
            $this->display(T('Wechat@Admin/refund'));
        }
    }

    /**
     * 导出维修订单列表
     * @author  pangyongfu
     */
    public function exportMaintenanceOrderExcel($is_ka=0,$is_sh=0){
        $order_no = I("order_no");
        $store_name = I("store_name");
        if($order_no != ""){
            $map['orde.order_code'] = ['like','%'.$order_no.'%'];
        }
        if($store_name != ""){
            $map['orde.store_name'] = ['like','%'.$store_name.'%'];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }
        $clean_type = I("equipment_type");
        if($clean_type != 0){
            $map['orde.equipment_id'] = ['eq',$clean_type];
        }
        $order_statu = I("order_state");
        if($order_statu != 0){
            $order_statu -= 1;
            $map['orde.order_state'] = ['eq',$order_statu];
        }
        $map['orde.order_type'] = ['eq',1];//1门店维修，2门店消杀，3烟道清洗
        $map['orde.is_ka'] = ['eq',$is_ka];//0：普通订单 1：KA订单
        $map['orde.is_sh'] = ['eq',$is_sh];//是否为售后订单 0：否 1：是
        $field = "orde.*,item.change_parts_text,item.parts_price,item.equipment_name,item.after_sale_text,item.old_order_code,equipment.name equipment_type,member.uid,member.name,faci.id faci_id,faci.name faci_name,oa.content,item.cancel_text";
        $list=$this->WechatOrderModel->getOrderMemberListToExcel($map,'orde.update_time desc',$field);

        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
//                '序号',
                '订单号',
                '下单时间',
                '完成时间',
                '用户',
                '联系电话',
                '门店名称',
//                '门店地址',
                '设备类型',
                '设备名称',
//                '服务商ID',
                '服务商',
//                '师傅ID',
                '师傅名称',
                '服务费',
                '上门费',
                '配件名称',
                '配件价格',
                '总计费用',
                '订单状态',
                '订单评价',
                '取消原因'
            ]
        );
        if($is_ka && $is_sh){
            $title['title'][] = "门店编号";
            $title['title'][] = "企业名称";
            $title['title'][] = "企业编号";
            $title['title'][] = "售后原因";
            $title['title'][] = "关联订单号";
        }elseif($is_ka && !$is_sh){
            $title['title'][] = "门店编号";
            $title['title'][] = "企业名称";
            $title['title'][] = "企业编号";
        }elseif(!$is_ka && $is_sh){
            $title['title'][] = "售后原因";
            $title['title'][] = "关联订单号";
        }
        foreach($list as $ck=>$cv){
//            $excelData[$ck]['id'] = $cv['id'];
            $excelData[$ck]['order_code'] = $cv['order_code']." ";
            $excelData[$ck]['create_time'] = $cv['create_time'];
            //拼接订单完成时间
            if($cv['order_state']==3 || $cv['order_state']==7){
                $excelData[$ck]['orderFinishTime']=$cv['update_time'];
            }else{
                $excelData[$ck]['orderFinishTime']='空';
            }
            $excelData[$ck]['link_person'] = filterEmoji($cv['link_person']);
            $excelData[$ck]['link_phone'] = $cv['link_phone'];
            $excelData[$ck]['store_name'] = $cv['store_name'];
//            $excelData[$ck]['detailed_address'] = $cv['detailed_address'];
            $excelData[$ck]['equipment_type'] = $cv['equipment_type'];
            $excelData[$ck]['equipment_name'] = $cv['equipment_name'];
//            $excelData[$ck]['faci_id'] = $cv['faci_id'];
            $excelData[$ck]['faci_name'] = $cv['faci_name'];
//            $excelData[$ck]['uid'] = $cv['uid'];
            $excelData[$ck]['name'] = $cv['name'];
            $excelData[$ck]['service_price'] = $cv['service_price'] ? $cv['service_price'] : 0;
            $excelData[$ck]['door_price'] = $cv['door_price'] ? $cv['door_price'] : 0;
            $excelData[$ck]['change_parts_text'] = $cv['change_parts_text'];
            $excelData[$ck]['parts_price'] = $cv['parts_price'] ? $cv['parts_price'] : 0;
            $excelData[$ck]['totle_price'] = $cv['money_total'];
            $excelData[$ck]['order_state'] = $this->getOrderStatusText($cv['order_state']);
            $excelData[$ck]['content'] = $cv['content'];
            $excelData[$ck]['cancel_text'] = $cv['cancel_text'];
            if($is_ka && $is_sh){
                $excelData[$ck]['city'] = $cv['city'];
                $excelData[$ck]['enterprise_name'] = $cv['enterprise_name'];
                $excelData[$ck]['province'] = $cv['province'];
                $excelData[$ck]['after_sale_text'] = $cv['after_sale_text'];
                $excelData[$ck]['old_order_code'] = $cv['old_order_code']." ";
            }elseif($is_ka && !$is_sh){
                $excelData[$ck]['city'] = $cv['city'];
                $excelData[$ck]['enterprise_name'] = $cv['enterprise_name'];
                $excelData[$ck]['province'] = $cv['province'];
            }elseif(!$is_ka && $is_sh){
                $excelData[$ck]['after_sale_text'] = $cv['after_sale_text'];
                $excelData[$ck]['old_order_code'] = $cv['old_order_code']." ";
            }
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "维修订单数据表",
            "table_name_two" => "维修订单信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '30',
                'B' => '30',
                'C' => '20',
                'D' => '20',
                'E' => '15',
                'F' => '20',
                'G' => '15',
                'H' => '15',
                'I' => '30',
                'J' => '15',
                'K' => '15',
                'L' => '15',
                'M' => '15',
                'N' => '30',
                'O' => '30',
//                'P' => '15',
//                'Q' => '15',
//                'R' => '15',
//                'S' => '15',
            ),
            'fontsize' => array (
                'A1:S' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:S' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }
    /**
     * 维修师傅管理
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function maintenanceMaster($page=1,$r=20){
        //拼接搜索商品名称
        $map = $this->dealMasterCondition(3);
        $filed = "member.*,fac.name company_name";
        list($list,$totalCount)=$this->WechatMemberModel->getListByPage($map,$page,'member.update_time desc',$filed,$r);
        $content['type'] = ['eq',2];//1：门店消杀，2：门店维修，3：烟道清洗
        $content['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
        $company_list = $this->WechatFacilitatorModel->getData($content,'id,name value');
//        var_dump($company);die;
        foreach($list as &$vmas){
            switch($vmas['isadmin']){
                case 0:
                    $vmas['isadmin'] = "维修员";
                    break;
                case 1:
                    $vmas['isadmin'] = "分配主管";
                    break;
                case 3:
                    $vmas['isadmin'] = "维修主管";
                    break;
                case 5:
                    $vmas['isadmin'] = "超管";
                    break;
            }
            $vmas['id'] = $vmas['uid'];
        }
        $builder=new AdminListBuilder();
        $builder->title(L('维修师傅管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/maintenanceMaster'))
            ->buttonNew(U('Wechat/addMaintenanceMaster'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateMaintenanceMaster?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateMaintenanceMaster?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateMaintenanceMaster?status=2'), 'target-form' => 'ids'])
            ->search("师傅ID",'materId','text','')
            ->search("师傅名称",'materName','text','')
            ->select('所属公司：','company','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$company_list))
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','师傅ID')
            ->keyText('name','师傅名称')
            ->keyGxptStatus()
            ->keyText('link_name','联系人')
            ->keyText('phone','联系电话')
            ->keyText('isadmin','职位')
            ->keyText('wx_code','企业微信绑定编号')
            ->keyText('company_name','所属公司')
            ->keyDoActionEdit('Wechat/addMaintenanceMaster?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 添加和修改维修师傅
     * @author  pangyongfu
     */
    public function addMaintenanceMaster($id=0){
        $title=$id?L('_EDIT_'):L('_ADD_');

        if (IS_POST) {
            //参数验证
            $data = I('');
            $error = $this->checkSystem(3,$data['isadmin'],$id);

            if(!empty($error)){

                $this->error($error);
            }
//            if($data['isadmin'] == 0 && $data['f_uid'] == 0){
//                $this->error("请选择上级");
//            }elseif($data['isadmin'] == 1 && $data['f_uid'] != 0){
//                $this->error("主管不需要选择上级");
//            }
            $data['type_user'] = 3;//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗
            $cate = $this->WechatMemberModel->editData($data);
            if ($cate) {
                $this->success($title.L('_SUCCESS_'), U('Wechat/maintenanceMaster'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatMemberModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatMemberModel->find($id);
            }else{
                $max_id = $this->WechatMemberModel->field("max(uid) uid")->find();
                $max_id = $max_id['uid'] ? $max_id['uid']+1 : 1;
            }
            $content['type'] = ['eq',2];//1：门店消杀，2：门店维修，3：烟道清洗
            $content['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
            $company_list = $this->WechatFacilitatorModel->getData($content,'id,name value');

            foreach($company_list as $clv){
                $companyData[$clv['id']] = $clv['value'];
            }
//            $map['type_user'] = 3;//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗)
//            $map['isadmin'] = 1;//0不是, 1分配主管 3维修主管  5超管
//            $director = $this->WechatMemberModel->where($map)->field("uid,name")->select();
//            $directorList = [];
//            foreach($director as $dval){
//                $directorList[$dval['uid']] = $dval['name'];
//            }
//            $directorList[0] = "请选择";
//            ksort($directorList);
            $builder->title($title.L('维修师傅'))
                ->data($data)
                ->keyHidden("id","")->keyDefault('id',$id)
                ->keyReadOnly('service_type','服务类别')->keyDefault('service_type',"门店维修")
                ->keyReadOnly('uid','师傅ID')->keyDefault('uid',$max_id)
                ->keyText('name','师傅名称')->keyDefault('name',$data['name'])
                ->keyText('link_name','联系人')->keyDefault('link_name',$data['link_name'])
                ->keyText('phone','联系电话')->keyDefault('phone',$data['phone'])
                ->keySelect('isadmin','职位','',[0=>'维修员',1=>'分配主管'])->keyDefault('isadmin',$data['isadmin'])
//                ->keySelect('f_uid','上级(当职位为维修员时需选择上级)','',$directorList)->keyDefault('f_uid',$data['f_uid'])
                ->keyText('wx_code','企业微信绑定编号')->keyDefault('wx_code',$data['wx_code'])
                ->keySelect('facilitator_id','所属公司','',$companyData)->keyDefault('facilitator_id',$data['facilitator_id'])
                ->buttonSubmit(U('Wechat/addMaintenanceMaster'))->buttonBack()
                ->display();
        }
    }

    /**
     * 删除启用禁用维修师傅
     * @author  pangyongfu
     */
    public function updateMaintenanceMaster(){
        $Ids = I('post.ids');
        $status = I('get.status');
        if($Ids){

            $this->WechatMemberModel->where(['uid'=>['IN',$Ids]])->save(['status'=>$status]);
        }
        $desc = $status == '1' ? "启用成功"  : ($status == '2' ? "禁用成功" : "删除成功");
        $this->success($desc);
    }

    /**
     * 处理接收参数
     * @param $stauts
     * @return mixed
     * @author pangyongfu
     */
    private function dealCondition($stauts){
        $order_no = I("order_no");
        if($order_no != ""){
            $map['orde.order_code'] = ['like','%'.$order_no.'%'];
        }
        $server_name = I("server_name");
        if($server_name != ""){
            $map['worker.name'] = ['like','%'.$server_name.'%'];
        }
        $server_id = I("server_id");
        if($server_id != ""){
            $map['worker.uid'] = ['eq',$server_id];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }
        $map['orde.order_type'] = ['eq',$stauts]; //1门店维修，2门店消杀，3烟道清洗
        return $map;
    }
    /**
     * 维修评价列表
     * @author  pangyongfu
     */
    public function maintenanceOrderAppraise($page=1,$r=20){
//拼接搜索商品名称
        $map = $this->dealCondition(1);
        $field = "orde.order_code,member.name member_name,worker.uid,worker.name,appraise.delivery_score,appraise.content,appraise.create_time,appraise.id,facilitator.name faci_name";
        list($list,$totalCount)=$this->WechatOrderAppraiseModel->getAppriaseListByPage($map,$page,'orde.update_time desc',$field,$r);

        //导出excel数据列表
        $content = I('');
        $attr['href'] = U('exportMaintenanceOrderAppraiseExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        $builder->title(L('维修评价管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/maintenanceOrderAppraise'))
            ->button('删除评价', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除吗?', 'url' => U('Wechat/delOrderAppraise'), 'target-form' => 'ids'])
            ->search("师傅ID",'server_id','text','')
            ->search("师傅名称",'server_name','text','')
            ->search("订单号",'order_no','text','')
            ->select("评价时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
            ->keyId()
            ->keyText('uid','师傅ID')
            ->keyText('name','师傅名称')
            ->keyText('faci_name','服务商')
            ->keyText('order_code','订单号')
            ->keyText('member_name','评价用户')
            ->keyText('create_time','评价时间')
            ->keyText('delivery_score','评价星级（星）')
            ->keyText('content','评价内容')
            ->button('导出',$attr)
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 导出维修评价列表
     * @author  pangyongfu
     */
    public function exportMaintenanceOrderAppraiseExcel(){

        $map = $this->dealCondition(1);
        $field = "orde.order_code,member.name member_name,worker.uid,worker.name,appraise.*,facilitator.name faci_name";
        $list=$this->WechatOrderAppraiseModel->getAppriaseListToExcel($map,'appraise.update_time desc',$field);
        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
                '师傅ID',
                '师傅名称',
                '服务商',
                '订单号',
                '评价用户',
                '评价时间',
                '评价星级（星）',
                '评价内容',
            ]
        );
        foreach($list as $ck=>$cv){
            $excelData[$ck]['uid'] = $cv['uid'];
            $excelData[$ck]['name'] = $cv['name'];
            $excelData[$ck]['faci_name'] = $cv['faci_name'];
            $excelData[$ck]['order_code'] = $cv['order_code']." ";;
            $excelData[$ck]['member_name'] = $cv['member_name'];
            $excelData[$ck]['create_time'] = $cv['create_time'];
            $excelData[$ck]['delivery_score'] = $cv['delivery_score']."星";
            $excelData[$ck]['content'] = $cv['content'];
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "维修评价数据表",
            "table_name_two" => "维修评价信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '10',
                'B' => '30',
                'C' => '20',
                'D' => '10',
                'E' => '15',
                'F' => '20',
                'G' => '15',
                'I' => '50',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    /////////////////////////////////////////////////////////////门店维修结束/////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////清洗服务商管理开始/////////////////////////////////////////////////////////////////////////////
    private function dealMasterCondition($type){
        $materId = I("materId");
        if($materId != ""){
            $map['member.uid'] = ['like','%'.$materId.'%'];
        }
        $materName = I("materName");
        if($materName != ""){
            $map['member.name'] = ['like',"%".$materName."%"];
        }
        $company = I("company");
        if($company != 0){
            $map['member.facilitator_id'] = ['eq',$company];
        }
        $status = I("status");
        if($status != 0){
            $map['member.status'] = ['eq',$status];
        }else{
            $map['member.status']=['neq',-1];
        }
        $map['member.type_user']=['eq',$type];//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗
        return $map;
    }
    /**
     * 清洗师傅管理
     * @author  pangyongfu
     */
    public function cleaningMasterList($page=1,$r=20){

        //拼接搜索商品名称
        $map = $this->dealMasterCondition(4);
        $filed = "member.*,fac.name company_name";
        list($list,$totalCount)=$this->WechatMemberModel->getListByPage($map,$page,'member.update_time desc',$filed,$r);
        $content['type'] = ['eq',3];//1：门店消杀，2：门店维修，3：烟道清洗
        $content['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
        $company_list = $this->WechatFacilitatorModel->getData($content,'id,name value');
//        var_dump($company);die;
        foreach($list as &$vmas){
            switch($vmas['isadmin']){
                case 0:
                    $vmas['isadmin'] = "清洗员";
                    break;
                case 1:
                    $vmas['isadmin'] = "分配主管";
                    break;
                case 3:
                    $vmas['isadmin'] = "维修主管";
                    break;
                case 5:
                    $vmas['isadmin'] = "超管";
                    break;
            }
            $vmas['id'] = $vmas['uid'];
        }
        $builder=new AdminListBuilder();
        $builder->title(L('清洗师傅管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/cleaningMasterList'))
            ->buttonNew(U('Wechat/addCleaningMaster'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateCleaningMaster?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateCleaningMaster?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateCleaningMaster?status=2'), 'target-form' => 'ids'])
            ->search("师傅ID",'materId','text','')
            ->search("师傅名称",'materName','text','')
            ->select('所属公司：','company','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$company_list))
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','师傅ID')
            ->keyText('name','师傅名称')
            ->keyGxptStatus()
            ->keyText('link_name','联系人')
            ->keyText('phone','联系电话')
            ->keyText('isadmin','职位')
            ->keyText('wx_code','企业微信绑定编号')
            ->keyText('company_name','所属公司')
            ->keyDoActionEdit('Wechat/addCleaningMaster?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * @param int $id
     * @author pangyongfu
     */
    public function addCleaningMaster($id = 0){
        $title=$id?L('_EDIT_'):L('_ADD_');

        if (IS_POST) {
            //参数验证
            $data = I('');
            $error = $this->checkSystem(4,$data['isadmin'],$id);

            if(!empty($error)){

                $this->error($error);
            }
//            if($data['isadmin'] == 0 && $data['f_uid'] == 0){
//                $this->error("请选择上级");
//            }elseif($data['isadmin'] == 1 && $data['f_uid'] != 0){
//                $this->error("主管不需要选择上级");
//            }
            $data['type_user'] = 4;
            $cate = $this->WechatMemberModel->editData($data);
            if ($cate) {
                $this->success($title.L('_SUCCESS_'), U('Wechat/cleaningMasterList'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatMemberModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatMemberModel->find($id);
            }else{
                $max_id = $this->WechatMemberModel->field("max(uid) uid")->find();
                $max_id = $max_id['uid'] ? $max_id['uid']+1 : 1;
            }
            $content['type'] = ['eq',3];//1：门店消杀，2：门店维修，3：烟道清洗
            $content['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
            $company_list = $this->WechatFacilitatorModel->getData($content,'id,name value');
            foreach($company_list as $clv){
                $companyData[$clv['id']] = $clv['value'];
            }

//            $map['type_user'] = 4;//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗)
//            $map['isadmin'] = 1;//0不是, 1分配主管 3维修主管  5超管
//            $director = $this->WechatMemberModel->where($map)->field("uid,name")->select();
//            $directorList = [];
//            foreach($director as $dval){
//                $directorList[$dval['uid']] = $dval['name'];
//            }
//            $directorList[0] = "请选择";
//            ksort($directorList);
            $builder->title($title.L('清洗师傅'))
                ->data($data)
                ->keyHidden("id","")->keyDefault('id',$id)
                ->keyReadOnly('service_type','服务类别')->keyDefault('service_type',"烟道清洗")
                ->keyReadOnly('uid','师傅ID')->keyDefault('uid',$max_id)
                ->keyText('name','师傅名称')->keyDefault('name',$data['name'])
                ->keyText('link_name','联系人')->keyDefault('link_name',$data['link_name'])
                ->keyText('phone','联系电话')->keyDefault('phone',$data['phone'])
                ->keySelect('isadmin','职位','',[0=>'清洗员',1=>'分配主管'])->keyDefault('isadmin',$data['isadmin'])
//                ->keySelect('f_uid','上级(当职位为清洗员时需选择上级)','',$directorList)->keyDefault('f_uid',$data['f_uid'])
                ->keyText('wx_code','企业微信绑定编号')->keyDefault('wx_code',$data['wx_code'])
                ->keySelect('facilitator_id','所属公司','',$companyData)->keyDefault('facilitator_id',$data['facilitator_id'])
                ->buttonSubmit(U('Wechat/addCleaningMaster'))->buttonBack()
                ->display();
        }
    }

    /**
     * 更新清洗服务商状态
     * @author  pangyongfu
     */
    public function updateCleaningMaster(){
        $Ids = I('post.ids');
        $status = I('get.status');
        if($Ids){

            $this->WechatMemberModel->where(['uid'=>['IN',$Ids]])->save(['status'=>$status]);
        }
        $desc = $status == '1' ? "启用成功"  : ($status == '2' ? "禁用成功" : "删除成功");
        $this->success($desc);
    }

    /**
     * 清洗订单管理
     * @author  pangyongfu
     */
    public function cleaningOrderList($page=1,$r=12,$is_ka=0,$is_sh=0){
        //拼接搜索商品名称
        $order_no = I("order_no");
        if($order_no != ""){
            $map['orde.order_code'] = ['like','%'.$order_no.'%'];
        }
        $store_name = I("store_name");
        if($store_name != ""){
            $map['orde.store_name'] = ['like','%'.$store_name.'%'];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }
        $clean_type = I("clean_type");
        if($clean_type != 0){
            $map['item.clean_type'] = ['eq',$clean_type];
        }
        $order_statu = I("order_state");
        if($order_statu != 0){
            $order_statu -= 1;
            $map['orde.order_state'] = ['eq',$order_statu];
            //筛选已完成订单包括已评价订单
            if($order_statu == 3){
                $map['orde.order_state'] = ['in',[3,7]];
            }
        }
        $map['orde.order_type'] = ['eq',3];
        $map['orde.is_ka'] = ['eq',$is_ka];//0：普通订单 1：KA订单
        $map['orde.is_sh'] = ['eq',$is_sh];//是否为售后订单 0：否 1：是

        //获取登录用户绑定的微信服务商标识
        $facilitatorId = getUserBindingFacilitator();
        $facilitatorData = (new WechatKaEnterpriseModel())->getData(['id'=>['IN',$facilitatorId]],'id,name');

        if($facilitatorData && $is_ka){
            $map['orde.enterprise_name'] = ['IN',implode(",", array_column($facilitatorData,'name'))];
        }

        $field = "orde.*,item.store_area,item.store_scene,item.clean_type,item.old_order_code,item.after_sale_text,item.last_clean_time,item.difference_price,member.uid,member.name,faci.id faci_id,faci.name faci_name,item.cancel_text,oa.content";
        list($list,$totalCount)=$this->WechatOrderModel->getOrderMemberListByPage($map,$page,'orde.update_time desc',$field,$r);

        foreach($list as &$vcles){

            //拼接订单完成时间
            if($vcles['order_state']==3 || $vcles['order_state']==7){
                $vcles['orderFinishTime']=$vcles['update_time'];
            }else{
                $vcles['orderFinishTime']='空';
            }

            $vcles['clean_type'] = $vcles['clean_type'] == 1 ? "烟道系统清洗" : "中央空调清洗";
            switch($vcles['store_area']){
                case 1:
                    $vcles['store_area'] = "0-100";
                    break;
                case 2:
                    $vcles['store_area'] = "101-200";
                    break;
                case 3:
                    $vcles['store_area'] = "201-300";
                    break;
                case 4:
                    $vcles['store_area'] = "301-400";
                    break;
                case 5:
                    $vcles['store_area'] = "401-500";
                    break;
                case 6:
                    $vcles['store_area'] = "501-600";
                    break;
                case 7:
                    $vcles['store_area'] = "600以上";
                    break;
            }

            $vcles['order_state'] = $this->getOrderStatusText($vcles['order_state']);

            switch($vcles['store_scene']){
                case 1:
                    $vcles['store_scene'] = "商场";
                    break;
                case 2:
                    $vcles['store_scene'] = "写字楼";
                    break;
                case 3:
                    $vcles['store_scene'] = "美食城";
                    break;
                case 4:
                    $vcles['store_scene'] = "底商";
                    break;
                case 5:
                    $vcles['store_scene'] = "其他";
                    break;
            }
//            0：未清洗过 1：15天以内  2：15天~30天 3：30天以前 4：60天以前 5：90天以前
            switch($vcles['last_clean_time']){
                case -1:
                    $vcles['last_clean_time'] = "无";
                    break;
                case 0:
                    $vcles['last_clean_time'] = "未清洗过";
                    break;
                case 1:
                    $vcles['last_clean_time'] = "15天以内";
                    break;
                case 2:
                    $vcles['last_clean_time'] = "15天~30天";
                    break;
                case 3:
                    $vcles['last_clean_time'] = "30天以前";
                    break;
                case 4:
                    $vcles['last_clean_time'] = "60天以前";
                    break;
                case 5:
                    $vcles['last_clean_time'] = "90天以前";
                    break;
            }
            $vcles['totle_price'] = $vcles['money_total'];
        }

        //导出excel数据列表
        $content = I('');
        $attr['href'] = U('exportCleaningOrderExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        if($is_ka && $is_sh){
            $builder->title(L('KA清洗售后订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/cleaningOrderList',array('is_ka'=>1,'is_sh'=>1)))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('清洗类别：','clean_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "烟道系统清洗"],["id" =>2,"value" => "中央空调清洗"]]))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyHtml('orderFinishTime','完成时间','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('city','门店编号')
                ->keyHtml('enterprise_name','所属企业','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('province','企业编号')
                ->keyText('store_area','门店面积（平米）')
                ->keyText('store_scene','门店场景')
                ->keyText('clean_type','清洗类别')
                ->keyText('last_clean_time','上次清洗时间')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('after_sale_text','售后原因','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('old_order_code','关联订单号','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
          //      ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }elseif($is_ka && !$is_sh){
            $builder->title(L('KA清洗订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/cleaningOrderList',array('is_ka'=>1)))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('清洗类别：','clean_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "烟道系统清洗"],["id" =>2,"value" => "中央空调清洗"]]))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyHtml('orderFinishTime','完成时间','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('city','门店编号')
                ->keyHtml('enterprise_name','所属企业','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('province','企业编号')
                ->keyText('store_area','门店面积（平米）')
                ->keyText('store_scene','门店场景')
                ->keyText('clean_type','清洗类别')
                ->keyText('last_clean_time','上次清洗时间')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
           //     ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }elseif(!$is_ka && $is_sh){
            $builder->title(L('个人清洗售后订单管理',array('is_sh'=>1)))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/cleaningOrderList'))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('清洗类别：','clean_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "烟道系统清洗"],["id" =>2,"value" => "中央空调清洗"]]))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
//                ->button('标记已取消', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyHtml('orderFinishTime','完成时间','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('store_area','门店面积（平米）')
                ->keyText('store_scene','门店场景')
                ->keyText('clean_type','清洗类别')
                ->keyText('last_clean_time','上次清洗时间')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('after_sale_text','售后原因','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('old_order_code','关联订单号','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
         //       ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }else{
            $builder->title(L('清洗订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/cleaningOrderList'))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('清洗类别：','clean_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "烟道系统清洗"],["id" =>2,"value" => "中央空调清洗"]]))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyText('orderFinishTime','完成时间')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('store_area','门店面积/㎡')
                ->keyText('store_scene','门店场景')
                ->keyText('clean_type','清洗类别')
                ->keyText('last_clean_time','上次清洗时间')
                ->keyHtml('faci_name','服务商','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('totle_price','总计费用')
                ->keyText('order_state','订单状态')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
          //      ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }
    }

    /**
     * 更新订单状态
     * @author pangyongfu
     */
    public function updateOrderStatus(){
        $Ids = I('post.ids');
        $status = I('get.status');
        if($Ids){

            $this->WechatOrderModel->where(['id'=>['IN',$Ids]])->save(['order_state'=>$status]);
        }
        $desc = $status == '3' ? "已标记为已完成"  : ($status == '4' ? "已标记为已取消" : "已标记为已退款");
        $this->success($desc);
    }
    /**
     * 导出清洗订单列表
     * @author  pangyongfu
     */
    public function exportCleaningOrderExcel($is_ka=0,$is_sh=0){

        //拼接搜索商品名称
        $order_no = I("order_no");
        if($order_no != ""){
            $map['orde.order_code'] = ['like','%'.$order_no.'%'];
        }
        $store_name = I("store_name");
        if($store_name != ""){
            $map['orde.store_name'] = ['like','%'.$store_name.'%'];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }
        $clean_type = I("clean_type");
        if($clean_type != 0){
            $map['item.clean_type'] = ['eq',$clean_type];
        }
        $order_statu = I("order_state");
        if($order_statu != 0){
            $order_statu -= 1;
            $map['orde.order_state'] = ['eq',$order_statu];
        }
        $map['orde.order_type'] = ['eq',3];
        $map['orde.is_ka'] = ['eq',$is_ka];//0：普通订单 1：KA订单
        $map['orde.is_sh'] = ['eq',$is_sh];//是否为售后订单 0：否 1：是
        $field = "orde.*,item.store_area,item.store_scene,item.clean_type,item.old_order_code,item.last_clean_time,item.after_sale_text,member.uid,member.name,faci.id faci_id,faci.name faci_name,item.cancel_text,oa.content";
        $list=$this->WechatOrderModel->getOrderMemberListToExcel($map,'orde.update_time desc',$field);

        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
//                '序号',
                '订单号',
                '下单时间',
                '完成时间',
                '用户',
                '联系电话',
                '门店名称',
//                '门店地址',
                '门店面积/㎡',
                '门店场景',
                '清洗类别',
                '上次清洗时间',
//                '服务商ID',
                '服务商',
//                '师傅ID',
                '师傅名称',
                '总计费用',
                '订单状态',
                '订单评价',
                '取消原因'
            ]
        );
        if($is_ka && $is_sh){
            $title['title'][] = "门店编号";
            $title['title'][] = "企业名称";
            $title['title'][] = "企业编号";
            $title['title'][] = "售后原因";
            $title['title'][] = "关联订单号";
        }elseif($is_ka && !$is_sh){
            $title['title'][] = "门店编号";
            $title['title'][] = "企业名称";
            $title['title'][] = "企业编号";
        }elseif(!$is_ka && $is_sh){
            $title['title'][] = "售后原因";
            $title['title'][] = "关联订单号";
        }
        foreach($list as $ck=>$cv){
//            $excelData[$ck]['id'] = $cv['id'];
            $excelData[$ck]['order_code'] = $cv['order_code']." ";;
            $excelData[$ck]['create_time'] = $cv['create_time'];
            //拼接订单完成时间
            if($cv['order_state']==3 || $cv['order_state']==7){
                $excelData[$ck]['orderFinishTime']=$cv['update_time'];
            }else{
                $excelData[$ck]['orderFinishTime']='空';
            }
            $excelData[$ck]['link_person'] = filterEmoji($cv['link_person']);
            $excelData[$ck]['link_phone'] = $cv['link_phone'];
            $excelData[$ck]['store_name'] = $cv['store_name'];
//            $excelData[$ck]['detailed_address'] = $cv['detailed_address'];
            switch($cv['store_area']){
                case 1:
                    $excelData[$ck]['store_area'] = "0-100";
                    break;
                case 2:
                    $excelData[$ck]['store_area'] = "101-200";
                    break;
                case 3:
                    $excelData[$ck]['store_area'] = "201-300";
                    break;
                case 4:
                    $excelData[$ck]['store_area'] = "301-400";
                    break;
                case 5:
                    $excelData[$ck]['store_area'] = "401-500";
                    break;
                case 6:
                    $excelData[$ck]['store_area'] = "501-600";
                    break;
                case 7:
                    $excelData[$ck]['store_area'] = "600以上";
                    break;
                default:
                    $excelData[$ck]['store_area'] = "未选择";
                    break;
            }
            switch($cv['store_scene']){
                case 1:
                    $excelData[$ck]['store_scene'] = "商场";
                    break;
                case 2:
                    $excelData[$ck]['store_scene'] = "写字楼";
                    break;
                case 3:
                    $excelData[$ck]['store_scene'] = "美食城";
                    break;
                case 4:
                    $excelData[$ck]['store_scene'] = "底商";
                    break;
                case 5:
                    $excelData[$ck]['store_scene'] = "其他";
                    break;
            }
            $excelData[$ck]['clean_type'] = $cv['clean_type'] == 1 ? "烟道系统清洗" : "中央空调清洗";
            switch($cv['last_clean_time']){
                case -1:
                    $excelData[$ck]['last_clean_time'] = "无";
                    break;
                case 0:
                    $excelData[$ck]['last_clean_time'] = "未清洗过";
                    break;
                case 1:
                    $excelData[$ck]['last_clean_time'] = "15天以内";
                    break;
                case 2:
                    $excelData[$ck]['last_clean_time'] = "15天~30天";
                    break;
                case 3:
                    $excelData[$ck]['last_clean_time'] = "30天以前";
                    break;
                case 4:
                    $excelData[$ck]['last_clean_time'] = "60天以前";
                    break;
                case 5:
                    $excelData[$ck]['last_clean_time'] = "90天以前";
                    break;
            }
//            $excelData[$ck]['faci_id'] = $cv['faci_id'];
            $excelData[$ck]['faci_name'] = $cv['faci_name'];
//            $excelData[$ck]['uid'] = $cv['uid'];
            $excelData[$ck]['name'] = $cv['name'];
            $excelData[$ck]['totle_price'] = $cv['money_total'];

            $excelData[$ck]['order_state'] = $this->getOrderStatusText($cv['order_state']);

            $excelData[$ck]['content'] = $cv['content'];
            $excelData[$ck]['cancel_text'] = $cv['cancel_text'];

            if($is_ka && $is_sh){
                $excelData[$ck]['city'] = $cv['city'];
                $excelData[$ck]['enterprise_name'] = $cv['enterprise_name'];
                $excelData[$ck]['province'] = $cv['province'];
                $excelData[$ck]['after_sale_text'] = $cv['after_sale_text'];
                $excelData[$ck]['old_order_code'] = $cv['old_order_code']." ";
            }elseif($is_ka && !$is_sh){
                $excelData[$ck]['city'] = $cv['city'];
                $excelData[$ck]['enterprise_name'] = $cv['enterprise_name'];
                $excelData[$ck]['province'] = $cv['province'];
            }elseif(!$is_ka && $is_sh){
                $excelData[$ck]['after_sale_text'] = $cv['after_sale_text'];
                $excelData[$ck]['old_order_code'] = $cv['old_order_code']." ";
            }
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "清洗订单数据表",
            "table_name_two" => "清洗订单信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '30',
                'B' => '30',
                'C' => '20',
                'D' => '20',
                'E' => '15',
                'F' => '20',
                'G' => '15',
                'H' => '15',
                'I' => '30',
                'J' => '15',
                'K' => '15',
                'L' => '15',
                'M' => '15',
                'N' => '30',
                'O' => '30',
//                'P' => '15',
//                'Q' => '15',
//                'R' => '15',
//                'S' => '15',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    /**
     * 清洗评价列表
     * @author  pangyongfu
     */
    public function cleaningOrderAppraise($page=1,$r=20){
//拼接搜索商品名称
        $map = $this->dealCondition(3);
        $field = "orde.order_code,member.name member_name,worker.uid,worker.name,appraise.delivery_score,appraise.content,appraise.create_time,appraise.id,facilitator.name faci_name";
        list($list,$totalCount)=$this->WechatOrderAppraiseModel->getAppriaseListByPage($map,$page,'orde.update_time desc',$field,$r);

        //导出excel数据列表
        $content = I('');
        $attr['href'] = U('exportCleaningAppraiseExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        $builder->title(L('清洗评价管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/cleaningOrderAppraise'))
            ->button('删除评价', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除吗?', 'url' => U('Wechat/delOrderAppraise'), 'target-form' => 'ids'])
            ->search("师傅ID",'server_id','text','')
            ->search("师傅名称",'server_name','text','')
            ->search("订单号",'order_no','text','')
            ->select("评价时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
            ->keyId()
            ->keyText('uid','师傅ID')
            ->keyText('name','师傅名称')
            ->keyText('faci_name','服务商')
            ->keyText('order_code','订单号')
            ->keyText('member_name','评价用户')
            ->keyText('create_time','评价时间')
            ->keyText('delivery_score','评价星级（星）')
            ->keyText('content','评价内容')
            ->button('导出',$attr)
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 导出清洗评价列表
     * @author  pangyongfu
     */
    public function exportCleaningAppraiseExcel(){

        $map = $this->dealCondition(3);
        $field = "orde.order_code,member.name member_name,worker.uid,worker.name,appraise.*,facilitator.name faci_name";
        $list=$this->WechatOrderAppraiseModel->getAppriaseListToExcel($map,'appraise.update_time desc',$field);
        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
                '师傅ID',
                '师傅名称',
                '服务商',
                '订单号',
                '评价用户',
                '评价时间',
                '评价星级（星）',
                '评价内容',
            ]
        );
        foreach($list as $ck=>$cv){
            $excelData[$ck]['uid'] = $cv['uid'];
            $excelData[$ck]['name'] = $cv['name'];
            $excelData[$ck]['faci_name'] = $cv['faci_name'];
            $excelData[$ck]['order_code'] = $cv['order_code']." ";;
            $excelData[$ck]['member_name'] = $cv['member_name'];
            $excelData[$ck]['create_time'] = $cv['create_time'];
            $excelData[$ck]['delivery_score'] = $cv['delivery_score']."星";
            $excelData[$ck]['content'] = $cv['content'];
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "清洗评价数据表",
            "table_name_two" => "清洗评价信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '10',
                'B' => '30',
                'C' => '20',
                'D' => '10',
                'E' => '15',
                'F' => '20',
                'G' => '15',
                'I' => '50',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }
    /////////////////////////////////////////////////////////////清洗服务商管理结束/////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////消杀服务商管理结束/////////////////////////////////////////////////////////////////////////////
    /**
     * 消杀服务商列表
     * @author  pangyongfu
     */
    public function cleanKillMasterList($page=1,$r=20){
        //拼接搜索商品名称
        $map = $this->dealMasterCondition(2);
        $filed = "member.*,fac.name company_name";
        list($list,$totalCount)=$this->WechatMemberModel->getListByPage($map,$page,'member.update_time desc',$filed,$r);
        $content['type'] = ['eq',1];//1：门店消杀，2：门店维修，3：烟道清洗
        $content['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
        $company_list = $this->WechatFacilitatorModel->getData($content,'id,name value');
//        var_dump($company);die;
        foreach($list as &$vmas){
            switch($vmas['isadmin']){
                case 0:
                    $vmas['isadmin'] = "消杀员";
                    break;
                case 1:
                    $vmas['isadmin'] = "分配主管";
                    break;
                case 3:
                    $vmas['isadmin'] = "维修主管";
                    break;
                case 5:
                    $vmas['isadmin'] = "超管";
                    break;
            }
            $vmas['id'] = $vmas['uid'];
        }
        $builder=new AdminListBuilder();
        $builder->title(L('消杀师傅管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/cleanKillMasterList'))
            ->buttonNew(U('Wechat/addCleanKillMaster'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateCleanKillMaster?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateCleanKillMaster?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateCleanKillMaster?status=2'), 'target-form' => 'ids'])
            ->search("师傅ID",'materId','text','')
            ->search("师傅名称",'materName','text','')
            ->select('所属公司：','company','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$company_list))
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','师傅ID')
            ->keyText('name','师傅名称')
            ->keyGxptStatus()
            ->keyText('link_name','联系人')
            ->keyText('phone','联系电话')
            ->keyText('isadmin','职位')
            ->keyText('wx_code','企业微信绑定编号')
            ->keyText('company_name','所属公司')
            ->keyDoActionEdit('Wechat/addCleanKillMaster?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 验证添加师傅参数
     * @return string
     * @author pangyongfu
     */
    private function checkSystem($type,$isadmin,$id = 0){
        $error = "";
        $data = I('post.');
        if(empty($data["name"])){

            $error .= "师傅名称不可为空！";
        }

        if(empty($data["link_name"])){

            $error .= "联系人不可为空！";
        }
        if(empty($data["phone"])){

            $error .= "联系电话不可为空！";
        }
        if(empty($data["wx_code"])){

            $error .= "企业微信绑定编号不可为空！";
        }
        if(!preg_match("/^1[345678]{1}\\d{9}$/",$data["phone"])){
            $error .= "联系电话格式不正确！";
        }
        if(!$id){
            //验证主管或师傅是否已添加
            $where['isadmin'] = $isadmin;
            $where['type_user'] = $type;
            $where['wx_code'] = $data["wx_code"];
            $where['status'] = 1;//1--启用
            $where['facilitator_id'] = $data["facilitator_id"];//1--启用
            $masterInfo = $this->WechatMemberModel->where($where)->find();
            if(!empty($masterInfo)){
                $error .= "用户已存在！";
                return $error;
            }
        }

        //验证wxcode
        $userInfo = $this->checkWXCode($data["wx_code"],$type,$isadmin);
        if($userInfo['errcode'] != 0){
            $error .= "企业微信绑定编号在企业通讯录中不存在！";
        }
        return $error;
    }
    /**
     * 添加消杀师傅
     * @author  pangyongfu
     */
    public function addCleanKillMaster($id=0){
        $title=$id?L('_EDIT_'):L('_ADD_');

        if (IS_POST) {
            //参数验证
            $data = I('');
            $error = $this->checkSystem(2,$data['isadmin'],$id);

            if(!empty($error)){

                $this->error($error);
            }
//            if($data['isadmin'] == 0 && $data['f_uid'] == 0){
//                $this->error("请选择上级");
//            }elseif($data['isadmin'] == 1 && $data['f_uid'] != 0){
//                $this->error("主管不需要选择上级");
//            }
            $data['type_user'] = 2;
            $cate = $this->WechatMemberModel->editData($data);
            if ($cate) {
                $this->success($title.L('_SUCCESS_'), U('Wechat/cleanKillMasterList'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatMemberModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatMemberModel->find($id);
            }else{
                $max_id = $this->WechatMemberModel->field("max(uid) uid")->find();
                $max_id = $max_id['uid'] ? $max_id['uid']+1 : 1;
            }
            $content['type'] = ['eq',1];//1：门店消杀，2：门店维修，3：烟道清洗
            $content['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
            $company_list = $this->WechatFacilitatorModel->getData($content,'id,name value');
//            $map['type_user'] = 2;//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗)
//            $map['isadmin'] = 1;//0不是, 1分配主管 3维修主管  5超管
//            $director = $this->WechatMemberModel->where($map)->field("uid,name")->select();
//            $directorList = [];
//            foreach($director as $dval){
//                $directorList[$dval['uid']] = $dval['name'];
//            }
//            $directorList[0] = "请选择";
//            ksort($directorList);
            foreach($company_list as $clv){
                $companyData[$clv['id']] = $clv['value'];
            }

            $builder->title($title.L('消杀师傅'))
                ->data($data)
                ->keyHidden("id","")->keyDefault('id',$id)
                ->keyReadOnly('service_type','服务类别')->keyDefault('service_type',"门店消杀")
                ->keyReadOnly('uid','师傅ID')->keyDefault('uid',$max_id)
                ->keyText('name','师傅名称')->keyDefault('name',$data['name'])
                ->keyText('link_name','联系人')->keyDefault('link_name',$data['link_name'])
                ->keyText('phone','联系电话')->keyDefault('phone',$data['phone'])
                ->keySelect('isadmin','职位','',[0=>'消杀员',1=>'分配主管'])->keyDefault('isadmin',$data['isadmin'])
//                ->keySelect('f_uid','上级(当职位为消杀员时需选择上级)','',$directorList)->keyDefault('f_uid',$data['f_uid'])
                ->keyText('wx_code','企业微信绑定编号')->keyDefault('wx_code',$data['wx_code'])
                ->keySelect('facilitator_id','所属公司','',$companyData)->keyDefault('facilitator_id',$data['facilitator_id'])
                ->buttonSubmit(U('Wechat/addCleanKillMaster'))->buttonBack()
                ->display();
        }
    }

    /**
     * 启用禁用以及删除消杀服务商
     * @author  pangyongfu
     */
    public function updateCleanKillMaster(){
        $Ids = I('post.ids');
        $status = I('get.status');
        if($Ids){

            $this->WechatMemberModel->where(['uid'=>['IN',$Ids]])->save(['status'=>$status]);
        }
        $desc = $status == '1' ? "启用成功"  : ($status == '2' ? "禁用成功" : "删除成功");
        $this->success($desc);
    }
    /**
     * 消杀订单管理
     * @author  pangyongfu
     */
    public function cleanKillOrderList($page=1,$r=12,$is_ka=0,$is_sh=0){
        //拼接搜索商品名称
        $order_no = I("order_no");
        if($order_no != ""){
            $map['orde.order_code'] = ['like','%'.$order_no.'%'];
        }
        $store_name = I("store_name");
        if($store_name != ""){
            $map['orde.store_name'] = ['like','%'.$store_name.'%'];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }

        $order_statu = I("order_state");
        if($order_statu != 0){
            $order_statu -= 1;
            $map['orde.order_state'] = ['eq',$order_statu];
            //筛选已完成订单包括已评价订单
            if($order_statu == 3){
                $map['orde.order_state'] = ['in',[3,7]];
            }
        }
        $map['orde.order_type'] = ['eq',2];//1门店维修，2门店消杀，3烟道清洗
        $map['orde.is_ka'] = ['eq',$is_ka];//0：普通订单 1：KA订单
        $map['orde.is_sh'] = ['eq',$is_sh];//是否为售后订单 0：否 1：是

        $map['orde.is_year'] = ['eq',0];//不是年订单
        $map['orde.is_main'] = ['eq',1];//是主订单
        //获取登录用户绑定的微信服务商标识
        $facilitatorId = getUserBindingFacilitator();
        $facilitatorData = (new WechatKaEnterpriseModel())->getData(['id'=>['IN',$facilitatorId]],'id,name');

        if($facilitatorData && $is_ka){
            $map['orde.enterprise_name'] = ['IN',implode(",", array_column($facilitatorData,'name'))];
        }

        $field = "orde.*,item.store_area,item.store_scene,item.insect_species,item.old_order_code,item.after_sale_text,item.insect_time,item.reservation_type,item.difference_price,member.uid,member.name,faci.id faci_id,faci.name faci_name,item.cancel_text,oa.content";
        list($list,$totalCount)=$this->WechatOrderModel->getOrderMemberListByPage($map,$page,'orde.update_time desc',$field,$r);

        foreach($list as &$vcles){

            //拼接订单完成时间
            if($vcles['order_state']==3 || $vcles['order_state']==7){
                $vcles['orderFinishTime']=Date('Y-m-d H:i',strtotime($vcles['update_time']));
            }else{
                $vcles['orderFinishTime']='空';
            }

            $vcles['create_time'] = Date('Y-m-d H:i',strtotime($vcles['create_time']));

            $vcles['insect_species'] = $vcles['insect_species'] == 1 ? "老鼠" : ($vcles['insect_species'] == 2 ? "蟑螂" : "蚊蝇");
            $vcles['insect_time'] = $vcles['insect_time'] == 1 ? "一周" : ($vcles['insect_time'] == 2 ? "二周" : ($vcles['insect_time'] == 3 ? "三周" : "一个月以上"));
            $vcles['reservation_type'] =  "单次";
            switch($vcles['store_area']){
                case 1:
                    $vcles['store_area'] = "0-100";
                    break;
                case 2:
                    $vcles['store_area'] = "101-200";
                    break;
                case 3:
                    $vcles['store_area'] = "201-300";
                    break;
                case 4:
                    $vcles['store_area'] = "301-400";
                    break;
                case 5:
                    $vcles['store_area'] = "401-500";
                    break;
                case 6:
                    $vcles['store_area'] = "501-600";
                    break;
                case 7:
                    $vcles['store_area'] = "600以上";
                    break;
            }
            $vcles['order_state'] = $this->getOrderStatusText($vcles['order_state']);
            //状态判断
            if($vcles['order_state'] == 0 && !empty($vcles['renew_order_id'])){
                $vcles['order_state'] = '续签中';
            }elseif($vcles['order_state'] == 0 && !empty($vcles['facilitator_id']) && $vcles['is_year'] && !$vcles['is_main']){
                $vcles['order_state'] = "待上门";
            }
            if(($vcles['order_state'] == 10 || $vcles['order_state'] == 1) && !empty($vcles['uid'])){
                $vcles['order_state'] = "待上门";
            }
            if($vcles['order_state'] == 1 && !empty($vcles['equipment_id'])){
                $vcles['order_state'] = '服务中';
            }

            switch($vcles['store_scene']){
                case 1:
                    $vcles['store_scene'] = "商场";
                    break;
                case 2:
                    $vcles['store_scene'] = "写字楼";
                    break;
                case 3:
                    $vcles['store_scene'] = "美食城";
                    break;
                case 4:
                    $vcles['store_scene'] = "底商";
                    break;
                case 5:
                    $vcles['store_scene'] = "其他";
                    break;
            }

            $vcles['difference_price'] = $vcles['difference_price'] ? $vcles['difference_price'] : 0;
            $vcles['totle_price'] = $vcles['money_total'] + $vcles['difference_price'];
        }

        //导出excel数据列表
        $content = I('');
        $attr['href'] = U('exportCleanKillOrderExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        if($is_ka && $is_sh){
            $builder->title(L('KA消杀售后订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/cleanKillOrderList',array('is_ka'=>1,'is_sh'=>1)))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
//                ->keyId()
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyHtml('orderFinishTime','完成时间','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('city','门店编号')
                ->keyHtml('enterprise_name','所属企业','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('province','企业编号')
                ->keyText('store_area','门店面积（平米）')
                ->keyText('store_scene','门店场景')
                ->keyText('insect_species','虫害类别')
                ->keyText('insect_time','发现虫害时间')
                ->keyText('reservation_type','预约服务类型')
//                ->keyText('faci_id','服务商ID')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('uid','师傅ID')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('difference_price','差价费用（元）')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('after_sale_text','售后原因','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('old_order_code','关联订单号','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
           //     ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }elseif($is_ka && !$is_sh){
            $builder->title(L('KA消杀订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/cleanKillOrderList',array('is_ka'=>1)))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
//                ->keyId()
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyHtml('orderFinishTime','完成时间','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('city','门店编号')
                ->keyHtml('enterprise_name','所属企业','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('province','企业编号')
                ->keyText('store_area','门店面积（平米）')
                ->keyText('store_scene','门店场景')
                ->keyText('insect_species','虫害类别')
                ->keyText('insect_time','发现虫害时间')
                ->keyText('reservation_type','预约服务类型')
//                ->keyText('faci_id','服务商ID')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('uid','师傅ID')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('difference_price','差价费用（元）')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
          //      ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }elseif(!$is_ka && $is_sh){
            $builder->title(L('个人消杀售后订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/cleanKillOrderList',array('is_sh'=>1)))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
//                ->button('标记已取消', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
//                ->keyId()
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyHtml('orderFinishTime','完成时间','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('store_area','门店面积（平米）')
                ->keyText('store_scene','门店场景')
                ->keyText('insect_species','虫害类别')
                ->keyText('insect_time','发现虫害时间')
                ->keyText('reservation_type','预约服务类型')
//                ->keyText('faci_id','服务商ID')
                ->keyHtml('faci_name','服务商名称','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('uid','师傅ID')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('difference_price','差价费用（元）')
                ->keyText('totle_price','总计费用（元）')
                ->keyText('order_state','订单状态')
                ->keyHtml('after_sale_text','售后原因','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('old_order_code','关联订单号','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
         //       ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }else{
            $builder->title(L('消杀订单管理'))
                ->data($list)
                ->setSelectPostUrl(U('Wechat/cleanKillOrderList'))
                ->search("订单号",'order_no','text','')
                ->search("门店名称",'store_name','text','')
                ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
                ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
                //->button('标记已取消', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已取消吗?', 'url' => U('Wechat/updateOrderStatus?status=4'), 'target-form' => 'ids'])
                ->buttonModalPopup(U('Wechat/doAudit'),null,'标记已取消',array('data-title'=>'标记已取消','target-form'=>'ids'))
                ->button('标记已退款', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已退款吗?', 'url' => U('Wechat/updateOrderStatus?status=6'), 'target-form' => 'ids'])
                ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateOrderStatus?status=3'), 'target-form' => 'ids'])
//                ->keyId()
                ->keyText('order_code','订单号')
                ->keyText('create_time','下单时间')
                ->keyText('orderFinishTime','完成时间')
                ->keyHtml('link_person','用户','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('link_phone','联系电话')
                ->keyHtml('store_name','门店名称','90px;word-wrap: break-word;white-space: normal;')
                ->keyText('store_area','门店面积/㎡')
                ->keyText('store_scene','门店场景')
                ->keyText('insect_species','虫害类别')
//                ->keyText('insect_time','发现虫害时间')
//                ->keyText('reservation_type','预约服务类型')
//                ->keyText('faci_id','服务商ID')
                ->keyHtml('faci_name','服务商','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('uid','师傅ID')
                ->keyHtml('name','师傅名称','90px;word-wrap: break-word;white-space: normal;')
//                ->keyText('difference_price','差价费用（元）')
                ->keyText('totle_price','总计费用')
                ->keyText('order_state','订单状态')
                ->keyHtml('content','订单评价','90px;word-wrap: break-word;white-space: normal;')
                ->keyHtml('cancel_text','取消原因','90px;word-wrap: break-word;white-space: normal;')
                ->button('导出',$attr)
            //    ->buttonModalPopup(U('Wechat/viewEvaluation'),null,'查看评价',array('data-title'=>'查看评价','target-form'=>'ids'))
                ->pagination($totalCount,$r)
                ->display();
        }
    }

    /**
     * 根据状态获取状态值文本信息
     * @param $status
     * @return string
     * @author pangyongfu
     */
    private function getOrderStatusText($status){
        switch($status){
            case 0:
                $text = "派单中";
                break;
            case 1:
                $text = "已接单";
                break;
            case 2:
                $text = "待支付";
                break;
            case 3:
                $text = "已完成";
                break;
            case 4:
                $text = "已取消";
                break;
            case 5:
                $text = "无需上门";
                break;
            case 6:
                $text = "已退款";
                break;
            case 7:
                $text = "已评价";
                break;
            case 8:
                $text = "已支付";
                break;
            case 9:
                $text = "待验收";
                break;
            case 10:
                $text = "未接单";
                break;
            case 11:
                $text = "服务中";
                break;
            case 12:
                $text = "已过期";
                break;
            case 13:
                $text = "待确认";
                break;
            default:
                $text = "已完成";
                break;
        }
        return $text;
    }
    /**
     * 导出消杀订单
     * @author  pangyongfu
     */
    public function exportCleanKillOrderExcel($is_ka=0,$is_sh=0){

        //拼接搜索商品名称
        $order_no = I("order_no");
        if($order_no != ""){
            $map['orde.order_code'] = ['like','%'.$order_no.'%'];
        }
        $store_name = I("store_name");
        if($store_name != ""){
            $map['orde.store_name'] = ['like','%'.$store_name.'%'];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }

        $order_statu = I("order_state");
        if($order_statu != 0){
            $order_statu -= 1;
            $map['orde.order_state'] = ['eq',$order_statu];
        }
        $map['orde.order_type'] = ['eq',2];//1门店维修，2门店消杀，3烟道清洗
        $map['orde.is_ka'] = ['eq',$is_ka];//0：普通订单 1：KA订单
        $map['orde.is_sh'] = ['eq',$is_sh];//是否为售后订单 0：否 1：是

        $map['orde.is_year'] = ['eq',0];//不是年订单
        $map['orde.is_main'] = ['eq',1];//是主订单
        $field = "orde.*,item.store_area,item.store_scene,item.insect_species,item.old_order_code,item.insect_time,item.after_sale_text,item.reservation_type,item.difference_price,member.uid,member.name,faci.id faci_id,faci.name faci_name,item.cancel_text,oa.content";
        $list=$this->WechatOrderModel->getOrderMemberListToExcel($map,'orde.update_time desc',$field);

        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
//                '序号',
                '订单号',
                '下单时间',
                '完成时间',
                '用户',
                '联系电话',
                '门店名称',
//                '门店地址',
                '门店面积/㎡',
                '门店场景',
                '虫害类别',
//                '发现虫害时间',
//                '预约服务类型',
//                '服务商ID',
                '服务商',
//                '师傅ID',
                '师傅名称',
//                '差价费用（元）',
                '总计费用',
                '订单状态',
                '订单评价',
                '取消原因'
            ]
        );
        if($is_ka && $is_sh){
            $title['title'][] = "门店编号";
            $title['title'][] = "企业名称";
            $title['title'][] = "企业编号";
            $title['title'][] = "售后原因";
            $title['title'][] = "关联订单号";
        }elseif($is_ka && !$is_sh){
            $title['title'][] = "门店编号";
            $title['title'][] = "企业名称";
            $title['title'][] = "企业编号";
        }elseif(!$is_ka && $is_sh){
            $title['title'][] = "售后原因";
            $title['title'][] = "关联订单号";
        }
        foreach($list as $ck=>$cv){
//            $excelData[$ck]['id'] = $cv['id'];
            $excelData[$ck]['order_code'] = $cv['order_code']." ";
            $excelData[$ck]['create_time'] = $cv['create_time'];
            //拼接订单完成时间
            if($cv['order_state']==3 || $cv['order_state']==7){
                $excelData[$ck]['orderFinishTime']=$cv['update_time'];
            }else{
                $excelData[$ck]['orderFinishTime']='空';
            }
            $excelData[$ck]['link_person'] = filterEmoji($cv['link_person']);
            $excelData[$ck]['link_phone'] = $cv['link_phone'];
            $excelData[$ck]['store_name'] = $cv['store_name'];
//            $excelData[$ck]['detailed_address'] = $cv['detailed_address'];
            switch($cv['store_area']){
                case 1:
                    $excelData[$ck]['store_area'] = "0-100";
                    break;
                case 2:
                    $excelData[$ck]['store_area'] = "101-200";
                    break;
                case 3:
                    $excelData[$ck]['store_area'] = "201-300";
                    break;
                case 4:
                    $excelData[$ck]['store_area'] = "301-400";
                    break;
                case 5:
                    $excelData[$ck]['store_area'] = "401-500";
                    break;
                case 6:
                    $excelData[$ck]['store_area'] = "501-600";
                    break;
                case 7:
                    $excelData[$ck]['store_area'] = "600以上";
                    break;
            }
            switch($cv['store_scene']){
                case 1:
                    $excelData[$ck]['store_scene'] = "商场";
                    break;
                case 2:
                    $excelData[$ck]['store_scene'] = "写字楼";
                    break;
                case 3:
                    $excelData[$ck]['store_scene'] = "美食城";
                    break;
                case 4:
                    $excelData[$ck]['store_scene'] = "底商";
                    break;
                case 5:
                    $excelData[$ck]['store_scene'] = "其他";
                    break;
            }
            $excelData[$ck]['insect_species'] = $cv['insect_species'] == 1 ? "老鼠" : ($cv['insect_species'] == 2 ? "蟑螂" : "蚊蝇");
//            $excelData[$ck]['insect_time'] = $cv['insect_time'] == 1 ? "一周" : ($cv['insect_time'] == 2 ? "二周" : ($cv['insect_time'] == 3 ? "三周" : "一个月以上"));
//            $excelData[$ck]['reservation_type'] = "单次";
//            $excelData[$ck]['faci_id'] = $cv['faci_id'];
            $excelData[$ck]['faci_name'] = $cv['faci_name'];
//            $excelData[$ck]['uid'] = $cv['uid'];
            $excelData[$ck]['name'] = $cv['name'];
//            $excelData[$ck]['difference_price'] = $cv['difference_price'] ? $cv['difference_price'] : 0;
            $excelData[$ck]['totle_price'] = $cv['money_total'] + $cv['difference_price'];
            $excelData[$ck]['order_state'] = $this->getOrderStatusText($cv['order_state']);
            $excelData[$ck]['content'] = $cv['content'];
            $excelData[$ck]['cancel_text'] = $cv['cancel_text'];
            if($is_ka && $is_sh){
                $excelData[$ck]['city'] = $cv['city'];
                $excelData[$ck]['enterprise_name'] = $cv['enterprise_name'];
                $excelData[$ck]['province'] = $cv['province'];
                $excelData[$ck]['after_sale_text'] = $cv['after_sale_text'];
                $excelData[$ck]['old_order_code'] = $cv['old_order_code']." ";
            }elseif($is_ka && !$is_sh){
                $excelData[$ck]['city'] = $cv['city'];
                $excelData[$ck]['enterprise_name'] = $cv['enterprise_name'];
                $excelData[$ck]['province'] = $cv['province'];
            }elseif(!$is_ka && $is_sh){
                $excelData[$ck]['after_sale_text'] = $cv['after_sale_text'];
                $excelData[$ck]['old_order_code'] = $cv['old_order_code']." ";
            }

        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "消杀订单数据表",
            "table_name_two" => "消杀订单信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '30',
                'B' => '30',
                'C' => '20',
                'D' => '20',
                'E' => '20',
                'F' => '20',
                'G' => '20',
                'I' => '15',
                'H' => '15',
                'J' => '15',
                'K' => '30',
                'L' => '15',
                'M' => '15',
                'N' => '30',
                'O' => '30',
//                'P' => '15',
//                'Q' => '15',
//                'R' => '15',
//                'S' => '15',
//                'T' => '15',
//                'U' => '15',
//                'V' => '15',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    /**
     * 消杀评价列表
     * @author  pangyongfu
     */
    public function cleanKillOrderAppraise($page=1,$r=20){

        $map = $this->dealCondition(2);
        $field = "orde.order_code,member.name member_name,worker.uid,worker.name,appraise.delivery_score,appraise.content,appraise.create_time,appraise.id,facilitator.name faci_name";
        list($list,$totalCount)=$this->WechatOrderAppraiseModel->getAppriaseListByPage($map,$page,'orde.update_time desc',$field,$r);

        //导出excel数据列表
        $content = I('');
        $attr['href'] = U('exportCleanKillOrderAppraiseExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        $builder->title(L('消杀评价管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/cleanKillOrderAppraise'))
            ->button('删除评价', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除吗?', 'url' => U('Wechat/delOrderAppraise'), 'target-form' => 'ids'])
            ->search("师傅ID",'server_id','text','')
            ->search("师傅名称",'server_name','text','')
            ->search("订单号",'order_no','text','')
            ->select("评价时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
            ->keyId()
            ->keyText('uid','师傅ID')
            ->keyText('name','师傅名称')
            ->keyText('faci_name','服务商')
            ->keyText('order_code','订单号')
            ->keyText('member_name','评价用户')
            ->keyText('create_time','评价时间')
            ->keyText('delivery_score','评价星级（星）')
            ->keyText('content','评价内容')
            ->button('导出',$attr)
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 导出消杀评价列表
     * @author  pangyongfu
     */
    public function exportCleanKillOrderAppraiseExcel(){

        $map = $this->dealCondition(2);
        $field = "orde.order_code,member.name member_name,worker.uid,worker.name,appraise.*,facilitator.name faci_name";
        $list=$this->WechatOrderAppraiseModel->getAppriaseListToExcel($map,'appraise.update_time desc',$field);
        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
                '师傅ID',
                '师傅名称',
                '服务商',
                '订单号',
                '评价用户',
                '评价时间',
                '评价星级（星）',
                '评价内容',
            ]
        );
        foreach($list as $ck=>$cv){
            $excelData[$ck]['uid'] = $cv['uid'];
            $excelData[$ck]['name'] = $cv['name'];
            $excelData[$ck]['faci_name'] = $cv['faci_name'];
            $excelData[$ck]['order_code'] = $cv['order_code']." ";;
            $excelData[$ck]['member_name'] = $cv['member_name'];
            $excelData[$ck]['create_time'] = $cv['create_time'];
            $excelData[$ck]['delivery_score'] = $cv['delivery_score']."星";
            $excelData[$ck]['content'] = $cv['content'];
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "消杀评价数据表",
            "table_name_two" => "消杀评价信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '10',
                'B' => '30',
                'C' => '20',
                'D' => '10',
                'E' => '15',
                'F' => '20',
                'G' => '15',
                'I' => '50',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    /**
     * 删除评价
     * @author pangyongfu
     */
    public function delOrderAppraise(){
        $Ids = I('post.ids');
        if($Ids){
            $this->WechatOrderAppraiseModel->where(['id'=>['IN',$Ids]])->delete();
        }
        $desc = "删除成功";
        $this->success($desc);
    }
////////////////////////////////////////////////////会员管理 开始///////////////////////////////////////////////////////////////////
    /**
     * 会员列表
     * @param int $page
     * @param int $r
     * @author pangyongfu
     */
    public function memberList($page=1,$r=20){
        //拼接搜索商品名称
        $uid = I("user_id");
        if($uid != ""){
            $map['member.uid'] = ['eq',$uid];
        }
        $phone = I("phone");
        if($phone != ""){
            $map['member.phone'] = ['like','%'.$phone.'%'];
        }
        $user_name = I("user_name");
        if($user_name != ""){
            $map['member.name'] = ['like','%'.$user_name.'%'];
        }
        $store_name = I("store_name");
        if($store_name != ""){
            $map['member.store_name'] = ['like','%'.$store_name.'%'];
        }
        $map['member.type_user'] = ['eq',1]; //1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗
        $map['member.status'] = ['neq',-1]; //-1--删除；1--启用；2--禁用；
        $map['member.open_id'] = ['neq',''];
        list($list,$totalCount)=$this->WechatMemberModel->getListByPage($map,$page,'member.create_time desc','member.*',$r);

        //导出excel数据列表
        $content = I('');
        $attr['href'] = U('exportMemberListExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        $builder->title(L('会员管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/memberList'))
            ->search("用户ID",'user_id','text','')
            ->search("用户名",'user_name','text','')
            ->search("联系电话",'phone','text','')
            ->search("门店名称",'store_name','text','')
            ->keyText('uid','用户ID')
            ->keyText('name','用户名称')
            ->keyText('phone','联系电话')
            ->keyText('store_name','门店名称')
            ->keyText('address_name','门店地址')
            ->keyText('channel_code','渠道来源')
            ->keyText('create_time','创建时间')
            ->button('导出',$attr)
            ->keyDoActionEdit('Wechat/enterpriseBinding?id=~~~','绑定企业')
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 企业绑定
     */
    public function enterpriseBinding(){
        $uid = empty($_REQUEST['id']) ? '' : $_REQUEST['id'];
        $userInfo = (new WechatMemberModel())->getOnceInfo(['uid'=>$uid], '');
        $enterpriseInfo = (new WechatKaEnterpriseModel())->getData(['status'=>1], 'id,name');
        $memberStore = (new WechatMemberStoreModel())->getMemberStoreInfo(['u_id'=>$uid]);
        $memberStoreNew = [];
        foreach($memberStore as $value){
            array_push($memberStoreNew,$value['store_id']);
        }
        $this->assign('memberStore',json_encode($memberStoreNew));
        $this->assign('enterprise',$enterpriseInfo);
        $this->assign('user',$userInfo);
        $this->display("enterpriseBinding");
    }

    /**
     * 联动获取门店
     */
    public function getStore(){
        $id = empty($_REQUEST['id']) ? '' : $_REQUEST['id'];
        if(empty($id)){
            $this->ajaxReturn(["status" => 0,"msg" => '参数不全']);
        }
        $storeInfo = (new WechatKaStoresModel())->getInfo(['enterprise_id'=>$id,'status'=>1]);
        $this->ajaxReturn(["status" => 1, "data" => empty($storeInfo) ? [] : $storeInfo]);
    }

    /**
     * 绑定企业
     */
    public function addMemberStore(){
        $uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
        $enterprise = empty($_REQUEST['enterprise']) ? '' : $_REQUEST['enterprise'];
        $store = empty($_REQUEST['store']) ? '' : $_REQUEST['store'];
        $memberInfo = (new WechatMemberModel())->editMemberData(['uid'=>$uid,'enterprise_id'=>$enterprise,'isadmin'=>7]);
        $storeName = '';
        if($memberInfo){
            //删除原有门店
            (new WechatMemberStoreModel())->delInfoByUid(['u_id'=>$uid]);
            foreach($store as $key=>$val){
                //获取当前门店是否绑定
                $isStoreInfo = (new WechatMemberStoreModel())->getOneMemberStoreInfo(['store_id'=>$val],'','');
                if($isStoreInfo){
                    $storeInfo = (new WechatKaStoresModel())->getOnceInfo(['id'=>$val],'name');
                    $storeName .= $storeInfo['name'].',';
                }
            }
            $storeName = substr($storeName,0,strlen($storeName)-1);
            if(!empty($storeName)){
                $this->error($storeName."   不可重复绑定");
            }

            foreach($store as $key=>$val){
                //添加新的门店信息
                (new WechatMemberStoreModel())->addInfo(['u_id'=>$uid,'store_id'=>$val]);
            }
        $this->success("绑定成功");
        }
        $this->error("查无此用户");
    }

    /**
     * 导出会员管理数据
     * @author pangyongfu
     */
    public function exportMemberListExcel(){
        $uid = I("user_id");
        if($uid != ""){
            $map['member.uid'] = ['eq',$uid];
        }
        $phone = I("phone");
        if($phone != ""){
            $map['member.phone'] = ['like','%'.$phone.'%'];
        }
        $user_name = I("user_name");
        if($user_name != ""){
            $map['member.name'] = ['like','%'.$user_name.'%'];
        }
        $store_name = I("store_name");
        if($store_name != ""){
            $map['member.store_name'] = ['like','%'.$store_name.'%'];
        }
        $map['member.type_user'] = ['eq',1]; //1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗
        $map['member.status'] = ['neq',-1]; //-1--删除；1--启用；2--禁用；$uid = I("user_id");
        $map['member.open_id'] = ['neq',''];
        $list=$this->WechatMemberModel->getListToExcel($map,'member.update_time desc','member.*');
        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
                '用户ID',
                '用户名称',
                '联系电话',
                '门店名称',
                '门店地址',
                '创建时间',
                '渠道来源',
            ]
        );

        $channelCode = '';
        foreach($list as $ck=>$cv){
            $excelData[$ck]['uid'] = $cv['uid'];
            $excelData[$ck]['name'] = filterEmoji($cv['name']);
            $excelData[$ck]['phone'] = $cv['phone'];
            $excelData[$ck]['store_name'] = $cv['store_name'];
            $excelData[$ck]['address_name'] = $cv['address_name'];
            $excelData[$ck]['create_time'] = $cv['create_time'];

            if(isset($cv['channel_code'])){
                switch ($cv['channel_code']){
                    case 0 :
                        $channelCode = '线上';
                        break;
                    case 1 :
                        $channelCode = '工单';
                        break;
                    case 2 :
                        $channelCode = 'DM';
                        break;
                    case 3 :
                        $channelCode = '笔筒';
                        break;
                    case 4 :
                        $channelCode = '台贴';
                        break;
                    default :
                        $channelCode = $cv['channel_code'];
                        break;
                }
            }

            $excelData[$ck]['channel_code'] = $channelCode;
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "会员数据表",
            "table_name_two" => "会员信息表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '10',
                'B' => '15',
                'C' => '15',
                'D' => '20',
                'E' => '35',
                'F' => '20',
                'G' => '10',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    /**
     * 服务商管理
     * @param int $page
     * @param int $r
     * @author pangyongfu
     */
    public function facilitatorManage($page=1,$r=20){
        //拼接搜索条件
        $materId = I("materId");
        if($materId != ""){
            $map['id'] = ['like','%'.$materId.'%'];
        }
        $materName = I("materName");
        if($materName != ""){
            $map['name'] = ['like',"%".$materName."%"];
        }
        $service_type = I("service_type");
        if($service_type != 0){
            $map['type'] = ['eq',$service_type];
        }
        $status = I("status");
        if($status != 0){
            $map['status'] = ['eq',$status];
        }else{
            $map['status']=['neq',-1];
        }
        list($list,$totalCount)=$this->WechatFacilitatorModel->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$vmas){
            $vmas['type'] = $vmas['type'] == 1 ? "门店消杀" : ($vmas['type'] == 2 ? "门店维修" : "烟道清洗");
        }
        $builder=new AdminListBuilder();
        $builder->title(L('服务商管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/facilitatorManage'))
            ->buttonNew(U('Wechat/addFacilitatorManage'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateFacilitatorManage?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateFacilitatorManage?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateFacilitatorManage?status=2'), 'target-form' => 'ids'])
            ->search("服务商ID",'materId','text','')
            ->search("服务商名称",'materName','text','')
            ->select('服务类别：','service_type','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "门店消杀"],["id" =>2,"value" => "门店维修"],["id" =>3,"value" => "烟道清洗"]]))//1：门店消杀，2：门店维修，3：烟道清洗
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','服务商ID')
            ->keyText('name','服务商名称')
            ->keyGxptStatus()
            ->keyText('link_person','联系人')
            ->keyText('phone','联系电话')
            ->keyText('type','服务类别')
            ->keyDoActionEdit('Wechat/addFacilitatorManage?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 添加编辑服务商
     * @param int $id
     * @author pangyongfu
     */
    public function addFacilitatorManage($id=0){
        $title=$id?L('_EDIT_'):L('_ADD_');

        if (IS_POST) {
            //参数验证
            $error = "";
            $data = I('post.');
            if(empty($data["name"])){

                $error .= "服务商名称不可为空！";
            }

            if(empty($data["link_person"])){

                $error .= "联系人不可为空！";
            }
            if(empty($data["phone"])){

                $error .= "联系电话不可为空！";
            }
            if(!preg_match("/^1[345678]{1}\\d{9}$/",$data["phone"])){
                $error .= "联系电话格式不正确！";
            }
            if(!$data["service_type"]){

                $error .= "请选择服务类型！";
            }else{
                $data["type"] = $data["service_type"];
                unset( $data["service_type"]);
            }

            if(!empty($error)){

                $this->error($error);
            }
            $cate = $this->WechatFacilitatorModel->editData($data);

            //取出添加服务商资质图片信息
            $qualificationImg = explode(',',I("qualificationImg"));
            if(I("qualificationImg")!='0'){
                if($qualificationImg[0] == 0){
                    unset($qualificationImg[0]);
                }
//                    $map['id'] = ['IN',array_values($qualificationImg)];
//                    $picData = $this->pictureModel->where($map)->select();

                //todo 添加或者维护产品图片关系表 jpgk_something_pic
                $rowId = $id ? $id : $cate;
                $app = 'WechatFacilitatorQualification';

                $somethingPicModel = D('Goods/SomethingPic');

                //将关联的图片数据删除
                $somethingPicModel->delData(['row_id'=>$rowId,'app'=>$app]);

                //重新添加图片数据
                foreach($qualificationImg as $val){

                    if($val){
                        $somethingPicModel->editData([
                            'pic_id'=>$val,
                            'app'=>$app,
                            'row_id'=>$rowId,
                        ]);
                    }
                }
            }

            if ($cate) {

                $this->success($title.L('_SUCCESS_'), U('Wechat/facilitatorManage'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatMemberModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            $data["qualificationImg"] = [];
            if ($id != 0) {
                $data = $this->WechatFacilitatorModel->find($id);
                $max_id = $id;

                //拼接图片数据
                $somethingPicModel = D('Goods/SomethingPic');
                $app = 'WechatFacilitatorQualification';

                //将关联的产品图片数据查出
                $picIdData = $somethingPicModel->field('pic_id')->where(['row_id'=>$id,'app' => $app])->select();

                foreach($picIdData as $val){
                    if(!$data["qualificationImg"]){
                        $data["qualificationImg"] = $val['pic_id'];
                    }else{
                        $data["qualificationImg"] .= ','.$val['pic_id'];
                    }
                }
            }else{
                $max_id = $this->WechatFacilitatorModel->field("max(id) id")->find();
                $max_id = $max_id['uid'] ? $max_id['uid']+1 : 1;
            }
            $builder->title($title.L('服务商'))
                ->data($data)
                ->keyHidden("id","")->keyDefault('id',$id)
                ->keyReadOnly('server_id','服务商ID')->keyDefault('server_id',$max_id)
                ->keyText('name','服务商名称')->keyDefault('name',$data['name'])
                ->keyText('link_person','联系人')->keyDefault('link_person',$data['link_person'])
                ->keyText('phone','联系电话')->keyDefault('phone',$data['phone'])
                ->keySelect('service_type','服务类别','',['0' => '请选择','1' => '门店消杀','2' => '门店维修','3' => '烟道清洗'])->keyDefault('service_type',$data['type'])
                ->keyMultiImage("qualificationImg","服务商资质")
                ->buttonSubmit(U('Wechat/addFacilitatorManage'))->buttonBack()
                ->display();
        }
    }

    /**
     * 更新服务商
     * @author pangyongfu
     */
    public function updateFacilitatorManage(){
        $Ids = I('post.ids');
        $status = I('get.status');
        if($Ids){

            $this->WechatFacilitatorModel->where(['id'=>['IN',$Ids]])->save(['status'=>$status]);
        }
        $desc = $status == '1' ? "启用成功"  : ($status == '2' ? "禁用成功" : "删除成功");
        $this->success($desc);
    }

    /**
     * 行业雷达
     * @param int $page
     * @param int $r
     * @author pangyongfu
     */
    public function industryRadar($page=1,$r=20){
        $map = [];
        $map['status']=['neq',-1];

        list($list,$totalCount)=$this->WechatVocationRadarModel->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){

            switch($val['type']){
                case 1:
                    $val['typeText'] = '供需平台';
                    break;
                case 2:
                    $val['typeText'] = '餐饮圈';
                    break;
            }
        }

        $builder=new AdminListBuilder();
        $builder->title('行业雷达列表')
            ->data($list)
            ->buttonNew(U('Wechat/editIndustryRadar'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/delIndustryRadar'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/startIndustryRadar'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/endIndustryRadar'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('typeText','行业雷达对应入口')
            ->keyGxptStatus()
            ->keyText('create_time','创建时间')
            ->keyText('update_time','更新时间')
            ->keyDoActionEdit('Wechat/editIndustryRadar?id=###');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 行业雷达添加
     * @param int $id
     */
    public function editIndustryRadar($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            $data = I('post.');
            if($_POST["guidelinesType"] == 0){

                $error .= "不可为空！";
            }else{
                $data['type'] = $data['guidelinesType'];
                unset($data['guidelinesType']);
            }

            if(empty($_POST["content"])){

                $error .= "内容不可为空！";
            }

            //如果为添加则验证是否已有该条记录
            if(empty($id)){
                $haveData = $this->WechatVocationRadarModel->where(['type'=>['eq',$data['type']],'status'=>['neq',-1]])->select();
                if(!empty($haveData)){
                    $error .= $data['type']==1 ? "供需平台记录已存在":"餐饮圈记录已存在";
                }
            }

            if(!empty($error)){

                $this->error($error);
            }

            if ($cate = $this->WechatVocationRadarModel->editData($data)) {

                $this->success($title.L('_SUCCESS_'), U('Wechat/industryRadar'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatVocationRadarModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatVocationRadarModel->find($id);
            }

            $builder->title($title.L('行业雷达'))
                ->data($data)
                ->keyId()
                ->keySelect('guidelinesType','服务须知对应的入口','',['0' => '请选择','1' => '供需平台','2' => '餐饮圈'])->keyDefault('guidelinesType',$data['type'])
                ->keyEditor('content',L('_CONTENT_'),'','all',['width' => '700px', 'height' => '400px'])
                ->buttonSubmit(U('Wechat/editIndustryRadar'))->buttonBack()
                ->display();
        }
    }

    //删除行业雷达数据
    public function delIndustryRadar(){

        $Ids = I('post.ids');
        if($Ids){
            $this->WechatVocationRadarModel->where(['id'=>['in',$Ids]])->save(['status' => '-1']);
        }

        $this->success('删除成功');
    }

    //启用行业雷达数据
    public function startIndustryRadar(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatVocationRadarModel->where(['id'=>['IN',$Ids]])->save(['status'=>1]);
        }

        $this->success('启用成功');
    }

    //禁用行业雷达数据
    public function endIndustryRadar(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatVocationRadarModel->where(['id'=>['IN',$Ids]])->save(['status'=>2]);
        }

        $this->success('禁用成功');
    }

    //标记已开具
    public function updateTicket(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatTicketModel->where(['id'=>['IN',$Ids]])->save(['status'=>2]);
        }

        $this->success('标记成功');
    }

    /**
     * 发票开具
     * @param int $page
     * @param int $r
     * @author pangyongfu
     */
    public function ticketOpening($page=1,$r=20){
        $order_no = I("order_no");
        if($order_no != ""){
            $map['item.order_code'] = ['like','%'.$order_no.'%'];
        }
        $user_name = I("user_name");
        if($user_name != ""){
            $map['member.name'] = ['like','%'.$user_name.'%'];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }
        $status = I("status");
        if($status != 0){
            $map['ticket.status'] = ['eq',$status];
        }
        $field = "ticket.*,GROUP_CONCAT(item.order_code) order_code,member.name";
        list($list,$totalCount)=$this->WechatTicketModel->getListByPage($map,$page,'ticket.update_time desc',$field,$r);

        foreach($list as &$val){

            switch($val['head_type']){
                case 1:
                    $val['head_type_text'] = '企业';
                    break;
                case 2:
                    $val['head_type_text'] = '个人';
                    break;
            }
            switch($val['tax_ticket_type']){
                case 1:
                    $val['tax_ticket_type_text'] = '普通发票';
                    break;
                case 2:
                    $val['tax_ticket_type_text'] = '专用发票';
                    break;
            }
            switch($val['status']){
                case 1:
                    $val['status_text'] = '未开具';
                    break;
                case 2:
                    $val['status_text'] = '已开具';
                    break;
            }
            switch($val['ticket_type']){
                case 1:
                    $val['ticket_type_text'] = '食品';
                    break;
                case 2:
                    $val['ticket_type_text'] = '服务费';
                    break;
                case 3:
                    $val['ticket_type_text'] = '办公用品';
                    break;
                case 4:
                    $val['ticket_type_text'] = '门店维修';
                    break;
            }
        }

        //导出excel数据列表
        $content = I('');
        $attr['href'] = U('exportTicketListExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        $builder->title(L('发票开具'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/ticketOpening'))
            ->search("订单号",'order_no','text','')
            ->search("用户名",'user_name','text','')
            ->select("申请时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "未开具"],["id" =>2,"value" => "已开具"]]))
            ->button('标记为已开具', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记为已开具吗?', 'url' => U('Wechat/updateTicket'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('order_code','订单号')
            ->keyText('create_time','申请日期')
            ->keyText('name','申请用户')
            ->keyText('head_type_text','抬头类型')
            ->keyText('tax_ticket_type_text','增值税类型')
            ->keyText('ticket_type_text','发票内容')
            ->keyText('head_ticket','发票抬头')
            ->keyText('tax_number','纳税人识别号')
            ->keyText('ticket_address','地址(专用发票)')
            ->keyText('ticket_phone','电话(专用发票)')
            ->keyText('open_bank','开户行(专用发票)')
            ->keyText('account_number','账号(专用发票)')
            ->keyText('money','发票金额')
            ->keyText('receive_person','收件人')
            ->keyText('receive_phone','收件人电话')
            ->keyText('receive_address','收件人地址')
            ->keyText('status_text','状态')
//            ->keyGxptStatus()
            ->button('导出',$attr)
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 导出发票列表
     * @author pangyongfu
     */
    public function exportTicketListExcel(){
        $order_no = I("order_no");
        if($order_no != ""){
            $map['item.order_code'] = ['like','%'.$order_no.'%'];
        }
        $user_name = I("user_name");
        if($user_name != ""){
            $map['member.name'] = ['like','%'.$user_name.'%'];
        }
        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }
        $status = I("status");
        if($status != 0){
            $map['ticket.status'] = ['eq',$status];
        }
        $field = "ticket.*,GROUP_CONCAT(item.order_code) order_code,member.name";
        $list=$this->WechatTicketModel->getListToExcel($map,'ticket.update_time desc',$field);

        foreach($list as &$val){

            switch($val['head_type']){
                case 1:
                    $val['head_type_text'] = '企业';
                    break;
                case 2:
                    $val['head_type_text'] = '个人';
                    break;
            }
            switch($val['tax_ticket_type']){
                case 1:
                    $val['tax_ticket_type_text'] = '普通发票';
                    break;
                case 2:
                    $val['tax_ticket_type_text'] = '专用发票';
                    break;
            }
            switch($val['status']){
                case 1:
                    $val['status_text'] = '未开具';
                    break;
                case 2:
                    $val['status_text'] = '已开具';
                    break;
            }
            switch($val['ticket_type']){
                case 1:
                    $val['ticket_type_text'] = '食品';
                    break;
                case 2:
                    $val['ticket_type_text'] = '服务费';
                    break;
                case 3:
                    $val['ticket_type_text'] = '办公用品';
                    break;
                case 4:
                    $val['ticket_type_text'] = '门店维修';
                    break;
            }
        }
        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
                'ID',
                '订单号',
                '申请日期',
                '申请用户',
                '抬头类型',
                '增值税类型',
                '发票内容',
                '发票抬头',
                '纳税人识别号',
                '地址',
                '电话',
                '开户行',
                '账号',
                '发票金额',
                '收件人',
                '收件人电话',
                '收件人地址',
                '状态',
            ]
        );
        foreach($list as $ck=>$cv){
            $excelData[$ck]['id'] = $cv['id'];
            $excelData[$ck]['order_code'] = $cv['order_code']." ";;
            $excelData[$ck]['create_time'] = $cv['create_time'];
            $excelData[$ck]['name'] = $cv['name'];
            $excelData[$ck]['head_type_text'] = $cv['head_type_text'];
            $excelData[$ck]['tax_ticket_type_text'] = $cv['tax_ticket_type_text'];
            $excelData[$ck]['ticket_type_text'] = $cv['ticket_type_text'];
            $excelData[$ck]['head_ticket'] = $cv['head_ticket'];
            $excelData[$ck]['tax_number'] = $cv['tax_number'];
            $excelData[$ck]['ticket_address'] = $cv['ticket_address'];
            $excelData[$ck]['ticket_phone'] = $cv['ticket_phone']." ";
            $excelData[$ck]['open_bank'] = $cv['open_bank'];
            $excelData[$ck]['account_number'] = $cv['account_number']." ";
            $excelData[$ck]['money'] = $cv['money'];
            $excelData[$ck]['receive_person'] = $cv['receive_person'];
            $excelData[$ck]['receive_phone'] = $cv['receive_phone']." ";
            $excelData[$ck]['receive_address'] = $cv['receive_address'];
            $excelData[$ck]['status_text'] = $cv['status_text'];
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "发票数据表",
            "table_name_two" => "发票详情信息表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '10',
                'B' => '50',
                'C' => '15',
                'D' => '20',
                'E' => '35',
                'F' => '35',
                'G' => '35',
                'H' => '35',
                'I' => '35',
                'J' => '35',
                'K' => '35',
                'L' => '35',
                'M' => '35',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }
    ////////////////////////////////////////客服管理////////////////////////////////////////////////////////////////////////
    /**
     * 客服管理
     * @author pangyongfu
     */
    public function customerService($page=1,$r=20){
        $materId = I("materId");
        if($materId != ""){
            $map['member.uid'] = ['like','%'.$materId.'%'];
        }
        $materName = I("materName");
        if($materName != ""){
            $map['member.name'] = ['like',"%".$materName."%"];
        }
        $status = I("status");
        if($status != 0){
            $map['member.status'] = ['eq',$status];
        }else{
            $map['member.status']=['neq',-1];
        }
        $map['member.isadmin']=['eq',2];//0不是, 1分配主管 2客服 3维修主管  5超管）

        $filed = "member.*";
        list($list,$totalCount)=$this->WechatMemberModel->getListByPage($map,$page,'member.update_time desc',$filed,$r);
        foreach($list as &$vmas){
            $vmas['id'] = $vmas['uid'];
        }
        $builder=new AdminListBuilder();
        $builder->title(L('客服管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/customerService'))
            ->buttonNew(U('Wechat/addCustomerService'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateCustomerService?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateCustomerService?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateCustomerService?status=2'), 'target-form' => 'ids'])
            ->search("客服ID",'materId','text','')
            ->search("客服名称",'materName','text','')
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','客服ID')
            ->keyText('name','客服名称')
            ->keyText('phone','联系方式')
            ->keyGxptStatus()
            ->keyText('wx_code','企业微信绑定编号')
            ->keyDoActionEdit('Wechat/addCustomerService?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 添加客服
     * @author pangyongfu
     */
    public function addCustomerService($id=0){
        $title=$id?L('_EDIT_'):L('_ADD_');

        if (IS_POST) {
            //参数验证
            $error = "";
            $data = I('');
            if(empty($data["name"])){

                $error .= "客服名称不可为空！";
            }
            if(empty($data["phone"])){

                $error .= "联系方式不可为空！";
            }
            if(!preg_match("/^1[345678]{1}\\d{9}$/",$data["phone"])){
                $error .= "联系电话格式不正确！";
            }
            if(empty($data["wx_code"])){

                $error .= "企业微信绑定编号不可为空！";
            }
            //验证主管或师傅是否已添加
            $where['isadmin'] = 2;
            $where['type_user'] = 5;
            $where['wx_code'] = $data["wx_code"];
            $where['status'] = 1;//1--启用
            $masterInfo = $this->WechatMemberModel->where($where)->find();
            if(!empty($masterInfo)){
                $error .= "客服已存在！";
                $this->error($error);
            }
            //验证wxcode
            $userInfo = $this->checkWXCode($data["wx_code"],5);
            if($userInfo['errcode'] != 0){
                $error .= "企业微信绑定编号在企业通讯录中不存在！";
            }
            if(!empty($error)){

                $this->error($error);
            }
            $data['type_user'] = 5;//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗 5：客服)
            $data['isadmin'] = 2;//0不是, 1分配主管 2客服 3维修主管  5超管
            $cate = $this->WechatMemberModel->editData($data);
            if ($cate) {
                $this->success($title.L('_SUCCESS_'), U('Wechat/customerService'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatMemberModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatMemberModel->find($id);
            } else {
                $max_id = $this->WechatMemberModel->field("max(uid) uid")->find();
                $max_id = $max_id['uid'] ? $max_id['uid'] + 1 : 1;
            }
            $builder->title($title . L('客服'))
                ->data($data)
                ->keyHidden("id", "")->keyDefault('id', $id)
                ->keyReadOnly('uid', '客服ID')->keyDefault('uid', $max_id)
                ->keyText('name', '客服名称')->keyDefault('name', $data['name'])
                ->keyText('phone', '联系方式')->keyDefault('name', $data['phone'])
                ->keyText('wx_code', '企业微信绑定编号')->keyDefault('wx_code', $data['wx_code'])
                ->buttonSubmit(U('Wechat/addCustomerService'))->buttonBack()
                ->display();
        }
    }

    /**
     * 启用禁用客服
     * @author pangyongfu
     */
    public function updateCustomerService(){
        $Ids = I('post.ids');
        $status = I('get.status');
        if($Ids){

            $this->WechatMemberModel->where(['uid'=>['IN',$Ids]])->save(['status'=>$status]);
        }
        $desc = $status == '1' ? "启用成功"  : ($status == '2' ? "禁用成功" : "删除成功");
        $this->success($desc);
    }

    /**
     * @param $code
     * @param $type 1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗 5：客服)
     * @param $isadmin  0不是, 1分配主管 2客服 3维修主管  5超管）
     * @return \Wechat\Service\Ambigous
     * @author pangyongfu
     */
    private function checkWXCode($code,$type,$isadmin=0){
        $corpID = COMPANY_CORPID;
        if($isadmin == 1){
            $appSecret = COMPANY_DISTRIBUTE_APPSECRET;
        }elseif($type == 2){
            $appSecret = COMPANY_CLEANKILL_APPSECRET;
        }elseif($type == 3){
            $appSecret = COMPANY_MAINTAIN_APPSECRET;
        }elseif($type == 4){
            $appSecret = COMPANY_CLEANING_APPSECRET;
        }elseif($type == 5){
            $appSecret = COMPANY_CUSTOMER_APPSECRET;
        }elseif($type == 6){
            $appSecret = COMPANY_ENTERPRISE_APPSECRET;
        }

        $token = COMPANY_TOKEN;
        $encodingAesKey = COMPANY_EMCODINGASEKEY;
        $enterService = new EnterpriseService($encodingAesKey,$token,$corpID,$appSecret);
        $userInfo = $enterService->getUserById($code);
        return $userInfo;
    }
    /////////////////////////////////////////////////体验活动列表 start////////////////////////////////////////////////////////////
    /**
     * 体验活动列表
     * @author pangyongfu
     */
    public function activitiesList($page=1,$r=20){
        $map = [];
        $materId = I("store_name");
        if($materId != ""){
            $map['activity.store_name'] = ['like','%'.$materId.'%'];
        }
        $materName = I("link_person");
        if($materName != ""){
            $map['activity.link_person'] = ['like',"%".$materName."%"];
        }
        $materName = I("link_phone");
        if($materName != ""){
            $map['activity.link_phone'] = ['like',"%".$materName."%"];
        }
        $status = I("status");
        if($status != 0){
            $map['activity.status'] = ['eq',$status];
        }

        $filed = "activity.*,member.name,province.name province,city.name city";
        list($list,$totalCount)=$this->WechatActivityModel->getListByPage($map,$page,'activity.create_time desc',$filed,$r);

        foreach($list as &$vmas){
            $vmas['status_text'] = $vmas['status'] == 1 ? "未联系" : ($vmas['status'] == 2 ? "已联系":"已完成");
        }
        $builder=new AdminListBuilder();
        $builder->title(L('体验活动列表'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/activitiesList'))
            ->button('标记未联系', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记未联系吗?', 'url' => U('Wechat/updateActivities?status=1'), 'target-form' => 'ids'])
            ->button('标记已联系', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已联系吗?', 'url' => U('Wechat/updateActivities?status=2'), 'target-form' => 'ids'])
            ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateActivities?status=3'), 'target-form' => 'ids'])
            ->search("门店名称",'store_name','text','')
            ->search("联系人",'link_person','text','')
            ->search("联系方式",'link_phone','text','')
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "未联系"],["id" =>2,"value" => "已联系"],["id" =>3,"value" => "已完成"]]))
            ->keyId('id','体验订单ID')
            ->keyText('uid','用户ID')
            ->keyText('name','用户名称')
            ->keyText('store_name','门店名称')
            ->keyText('store_address','门店地址')
            ->keyText('link_person','联系人')
            ->keyText('link_phone','联系方式')
            ->keyText('status_text','订单状态')
            ->keyText('create_time','创建时间')
            ->pagination($totalCount,$r)
            ->display();
    }
    /**
     * 修改体验订单状态
     * @author pangyongfu
     */
    public function updateActivities(){
        $Ids = I('post.ids');
        $status = I('get.status');
        if($Ids){

            $this->WechatActivityModel->where(['id'=>['IN',$Ids]])->save(['status'=>$status]);
        }
        $desc = "修改成功";
        $this->success($desc);
    }
    /////////////////////////////////////////////////体验活动列表 end////////////////////////////////////////////////////////////
    /**
     * 申请售后列表
     * @author pangyongfu
     */
    public function applyAfterSale($page=1,$r=20){
        $map = [];
        $materId = I("store_name");
        if($materId != ""){
            $map['orde.store_name'] = ['like','%'.$materId.'%'];
        }
        $materName = I("link_person");
        if($materName != ""){
            $map['orde.link_person'] = ['like',"%".$materName."%"];
        }
        $materName = I("link_person");
        if($materName != ""){
            $map['orde.link_person'] = ['like',"%".$materName."%"];
        }

        $map['orde.order_state'] = ['in',[3,7]];
        $map['item.after_sale_text'] = ['neq',""];
        $filed = "orde.*,item.after_sale_text,item.update_time sale_text_time,faci.name fname,member.name worker";
        list($list,$totalCount)=$this->WechatOrderModel->getOrderListByPage($map,$page,'orde.create_time desc',$filed,$r);

        $builder=new AdminListBuilder();
        $builder->title(L('申请售后列表'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/applyAfterSale'))
            ->button('标记已完成', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要标记已完成吗?', 'url' => U('Wechat/updateApplyAfterSale'), 'target-form' => 'ids'])
            ->search("门店名称",'store_name','text','')
            ->search("联系人",'link_person','text','')
            ->search("联系方式",'link_phone','text','')
            ->keyId('id','订单ID')
            ->keyText('order_code','订单号')
            ->keyText('store_name','门店名称')
            ->keyText('fname','服务商')
            ->keyText('worker','师傅名称')
            ->keyText('detailed_address','详细地址')
            ->keyText('link_person','联系人')
            ->keyText('link_phone','联系方式')
            ->keyText('after_sale_text','申请售后原因')
            ->keyText('sale_text_time','申请售后时间')
            ->pagination($totalCount,$r)
            ->display();
    }
    /**
     * 标记售后已完成
     * @author pangyongfu
     */
    public function updateApplyAfterSale(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatOrderItemModel->where(['order_id'=>['IN',$Ids]])->save(['after_sale_text'=>""]);
        }
        $desc = "标记成功";
        $this->success($desc);
    }


    /*******************企业管理******************/

    //企业列表
    public function enterpriseList($page=1,$r=20){

        //搜索项：企业名称，企业编码，餐饮类别，状态
        $map = [];

        $entName = I('entName');
        if($entName != ''){
            $map['ent.name'] = ['LIKE','%'.$entName.'%'];
        }

        $entCode = I('entCode');
        if($entCode != ''){
            $map['ent.code'] = ['LIKE','%'.$entCode.'%'];
        }

        $mealType = I('mealType');
        if($mealType != 0){
            $map['ent.meal_type'] = $mealType;
        }

        $status = I("status");
        if($status != 0){
            $map['ent.status'] = $status;
        }else{
            $map['ent.status']=['neq',-1];
        }

        $fields = 'ent.*,COUNT(store.id) storeCount';

        list($list,$totalCount) = $this->WechatKaEnterpriseModel->getListForPage($map,$page,'ent.update_time desc',$fields,$r);

        foreach ($list as &$v){
            $v['meal_type_text'] = $this->mealTypeList[$v['meal_type']];
        }
        unset($v);

        //导出excel数据列表
        $attr = [];
        $attr['href'] = U('toExcelEnterprise');
        $attr['class']='btn btn-ajax';

        $mealTypeList = [];
        foreach ($this->mealTypeList as $k=>$v){
            $mealTypeList[] = ['id'=>$k,'value'=>$v];
        }

        $builder=new AdminListBuilder();
        $builder->title(L('连锁企业管理'))
            ->data($list)
            ->buttonNew(U('Wechat/addEnterprise'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateEnterprise?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateEnterprise?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateEnterprise?status=2'), 'target-form' => 'ids'])
            ->button('导出',$attr)
            ->search("企业名称",'entName','text','')
            ->search("企业编号",'entCode','text','')
            ->select('餐饮类别：','mealType','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$mealTypeList))
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','序号')
            ->keyText('name','企业名称')
            ->keyText('short_name','企业简称')
            ->keyText('code','企业编号')
            ->keyText('meal_type_text','餐饮类别')
            ->keyText('storeCount','门店数量')
            ->keyText('link_person','联系人')
            ->keyText('link_phone','联系电话')
            ->keyGxptStatus()
            ->keyDoActionEdit('Wechat/addEnterprise?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    public function addEnterprise($id = 0){

        $title=$id?L('_EDIT_'):L('_ADD_');

        if(IS_POST){

            $data = I('');

            $error = '';
            if($data['name'] == ''){
                $error .= '企业名称不可为空，';
            }
            if($data['short_name'] == ''){
                $error .= '企业简称不可为空，';
            }
            if($data['meal_type'] == ''){
                $error .= '餐饮类别不可为空，';
            }
            if($data['link_person'] == ''){
                $error .= '联系人不可为空，';
            }
            if($data['link_phone'] == ''){
                $error .= '联系电话不可为空，';
            }
            if(!preg_match("/^1[3456789]{1}\\d{9}$/",$data["link_phone"])){
                $error .= "联系电话格式不正确！";
            }

            if(!empty($error)){

                $error = trim($error,'，');
                $this->error($error);
            }

            //唯一6位企业编号
            if($data['code'] == ''){
                $data['code'] = date('y').rand(1000,9999);
            }

            $result = $this->WechatKaEnterpriseModel->editData($data);
            if ($result) {
                $this->success($title.L('_SUCCESS_'), U('Wechat/enterpriseList'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatKaEnterpriseModel->getError());
            }

        }else{
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatKaEnterpriseModel->find($id);
            }

            $builder->title($title.L('连锁企业'))
                ->data($data)
                ->keyHidden("id","")->keyDefault('id',$id)
                ->keyHidden("code","")->keyDefault('code',$data['code'])
                ->keyText('name','企业名称')->keyDefault('name',$data['name'])
                ->keyText('short_name','企业简称')->keyDefault('short_name',$data['short_name'])
                ->keySelect('meal_type','餐饮类别','',$this->mealTypeList)->keyDefault('meal_type',$data['meal_type'])
                ->keyText('link_person','联系人')->keyDefault('link_person',$data['link_person'])
                ->keyText('link_phone','联系电话')->keyDefault('link_phone',$data['link_phone'])
                ->buttonSubmit(U('Wechat/addEnterprise'))->buttonBack()
                ->display();
        }
    }

    public function updateEnterprise(){

        $ids = I('post.ids');
        $status = I('get.status');

        if($ids){
            $this->WechatKaEnterpriseModel->where(['id'=>['IN',$ids]])->save(['status'=>$status]);
        }

        $desc = $status == '1' ? "启用成功"  : ($status == '2' ? "禁用成功" : "删除成功");
        $this->success($desc);
    }

    public function toExcelEnterprise(){

        $fields = 'ent.*,COUNT(store.id) storeCount';
        $map = [];
        $map['ent.status'] = ['neq',-1];
        $data = $this->WechatKaEnterpriseModel->getDataForExcel($map,$fields);

        for($i = 0; $i < count ( $data ); $i ++) {
            array_unshift ( $data [$i], $i );
        }

        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 8,
            "title" => [
                '序号',
                '企业名称',
                '企业编号',
                '餐饮类别',
                '门店数量',
                '联系人',
                '联系电话',
                '状态',
                '创建时间'
            ]
        );

        $num = 1;
        foreach ($data as $k=>$v){
            $excelData[$k]['id'] = $v['id'];
            $excelData[$k]['name'] = $v['name'];
            $excelData[$k]['code'] = $v['code'];
            $excelData[$k]['meal_type_text'] = $this->mealTypeList[$v['meal_type']];
            $excelData[$k]['storeCount'] = $v['storeCount'];
            $excelData[$k]['link_person'] = $v['link_person'];
            $excelData[$k]['link_phone'] = $v['link_phone'];
            $excelData[$k]['status_text'] = $v['status']==1?'启用':'禁用';
            $excelData[$k]['create_time'] = $v['create_time'];
        }

        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "企业信息数据表",
            "table_name_two" => "企业信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '8',
                'B' => '30',
                'C' => '20',
                'D' => '20',
                'E' => '20',
                'F' => '20',
                'G' => '15',
                'I' => '25',
                'J' => '25',
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    /******************企业门店管理*******************/

    //企业门店列表
    public function entStoreList($page=1,$r=20){

        //搜索项：门店名称，门店编码，企业名称，企业编号，状态
        $map = [];

        $storeName = I('storeName');
        if($storeName != ''){
            $map['store.name'] = ['LIKE','%'.$storeName.'%'];
        }

        $storeCode = I('storeCode');
        if($storeCode != ''){
            $map['store.code'] = ['LIKE','%'.$storeCode.'%'];
        }

        $entName = I('entName');
        if($entName != ''){
            $map['ent.name'] = ['LIKE','%'.$entName.'%'];
        }

        $entCode = I('entCode');
        if($entCode != ''){
            $map['ent.code'] = ['LIKE','%'.$entCode.'%'];
        }

        $status = I("status");
        if($status != 0){
            $map['store.status'] = $status;
        }else{
            $map['store.status']=['neq',-1];
        }

        $fields = 'store.*,ent.name entName,ent.code entCode';

        list($list,$totalCount) = $this->WechatKaStoresModel->getListForPage($map,$page,'ent.update_time desc',$fields,$r);

        //导出excel数据列表
        $attr = [];
        $attr['href'] = U('toExcelStore');
        $attr['class']='btn btn-ajax';

        $builder=new AdminListBuilder();
        $builder->title(L('门店管理'))
            ->data($list)
            ->buttonNew(U('Wechat/addEntStore'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateEntStore?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateEntStore?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateEntStore?status=2'), 'target-form' => 'ids'])
            ->button('导出',$attr)
            ->button('导出模板',['class' => 'btn btn-ajax','href'=>'store_moban.xls'])
            ->buttonModalPopup(U('Wechat/addStoreForExcel'),null,'导入',['data-title'=>'导入门店'])
            ->search("门店名称",'storeName','text','')
            ->search("门店编号",'storeCode','text','')
            ->search("企业名称",'entName','text','')
            ->search("企业编号",'entCode','text','')
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','序号')
            ->keyText('name','门店名称')
            ->keyText('code','门店编号')
            ->keyText('stores_address','门店地址')
            ->keyText('link_person','联系人')
            ->keyText('link_phone','联系电话')
            ->keyGxptStatus()
            ->keyText('entName','所属企业')
            ->keyText('entCode','企业编号')
            ->keyLink("查看","设备列表",'Wechat/showDeviceList?id=###')
            ->keyDoActionEdit('Wechat/addEntStore?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    //下载模板
    public function uploadStoreExcel(){
        header("http://www.canxun.info/store_moban.xls");
    }

    public function addEntStore($id=0){

        $title=$id?L('_EDIT_'):L('_ADD_');

        if(IS_POST){

            $data = I('');

            $error = '';
            if($data['name'] == ''){
                $error .= '门店名称不可为空，';
            }
            if($data['link_person'] == ''){
                $error .= '联系人不可为空，';
            }
            if($data['link_phone'] == ''){
                $error .= '联系电话不可为空，';
            }
            if(!preg_match("/^1[3456789]{1}\\d{9}$/",$data["link_phone"])){
                $error .= "联系电话格式不正确，";
            }
            if($data['province'] == ''){
                $error .= "省份不可为空，";
            }
            if($data['city'] == ''){
                $error .= "城市不可为空，";
            }
            if($data['stores_address'] == ''){
                $error .= '详细地址不可为空，';
            }
            if($data['enterprise_id'] == ''){
                $error .= '所属企业不可为空';
            }

            if(!empty($error)){

                $error = trim($error,'，');
                $this->error($error);
            }

            //唯一6位企业编号
            if($data['code'] == ''){
                $maxCode = $this->WechatKaStoresModel->where(['enterprise_id'=>$data['enterprise_id']])->field('max(code) maxCode')->find();
                if($maxCode['maxCode']){
                    $data['code'] = $maxCode['maxCode']+1;
                }else{
                    $entCode = $this->WechatKaEnterpriseModel->where(['id'=>$data['enterprise_id']])->field('code')->find();
                    $entCode = $entCode['code'];
                    $data['code'] = intval($entCode.'001');
                }
            }

            $result = $this->WechatKaStoresModel->editData($data);
            if ($result) {
                $this->success($title.L('_SUCCESS_'), U('Wechat/entStoreList'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatKaStoresModel->getError());
            }

        }else{
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatKaStoresModel->find($id);

                $data["district_id"] = [

                    $data["province"],
                    $data["city"],
                    $data["district"]
                ];
            }

            $enterpriseData = $this->WechatKaEnterpriseModel->where(['status'=>['neq',-1]])->select();
            $enterpriseList = [];
            if($enterpriseData){
                foreach ($enterpriseData as $v){
                    $enterpriseList[$v['id']] = $v['name'];
                }
            }

            $builder->title($title.L('门店'))
                ->data($data)
                ->keyHidden("id","")->keyDefault('id',$id)
                ->keyHidden("code","")->keyDefault('code',$data['code'])
                ->keyText('name','门店名称')->keyDefault('name',$data['name'])
                ->keyCity("district_id","门店区域")
                ->keyText('stores_address','详细地址')->keyDefault('stores_address',$data['stores_address'])
                ->keyText('link_person','联系人')->keyDefault('link_person',$data['link_person'])
                ->keyText('link_phone','联系电话')->keyDefault('link_phone',$data['link_phone'])
                ->keySelect('enterprise_id','所属企业','',$enterpriseList)->keyDefault('enterprise_id',$enterpriseList)
                ->buttonSubmit(U('Wechat/addEntStore'))->buttonBack()
                ->display();
        }
    }

    public function updateEntStore(){

        $ids = I('post.ids');
        $status = I('get.status');

        if(!empty($ids)){
            $this->WechatKaStoresModel->where(['id'=>['IN',$ids]])->save(['status'=>$status]);
        }

        $desc = $status == '1' ? "启用成功"  : ($status == '2' ? "禁用成功" : "删除成功");
        $this->success($desc);
    }

    public function toExcelStore(){

        $fields = 'store.*,ent.name entName,ent.code entCode';
        $map = [];
        $map['store.status'] = ['neq',-1];
        $data = $this->WechatKaStoresModel->getDataForExcel($map,$fields);

        for($i = 0; $i < count ( $data ); $i ++) {
            array_unshift ( $data [$i], $i );
        }

        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 10,
            "title" => [
                '序号',
                '门店名称',
                '门店编号',
                '门店地址',
                '联系人',
                '联系电话',
                '状态',
                '所属企业',
                '企业编号',
                '创建时间'
            ]
        );

        $num = 1;
        foreach ($data as $k=>$v){
            $excelData[$k]['id'] = $v['id'];
            $excelData[$k]['name'] = $v['name'];
            $excelData[$k]['code'] = $v['code'];
            $excelData[$k]['stores_address'] = $v['stores_address'];
            $excelData[$k]['link_person'] = $v['link_person'];
            $excelData[$k]['link_phone'] = $v['link_phone'];
            $excelData[$k]['status_text'] = $v['status']==1?'启用':'禁用';
            $excelData[$k]['entName'] = $v['entName'];
            $excelData[$k]['entCode'] = $v['entCode'];
            $excelData[$k]['create_time'] = $v['create_time'];
        }

        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "门店信息数据表",
            "table_name_two" => "门店信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '8',
                'B' => '30',
                'C' => '20',
                'D' => '20',
                'E' => '20',
                'F' => '20',
                'G' => '15',
                'I' => '25',
                'J' => '25',
                'K' => '25',
                'L' => '25',
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    public function addStoreForExcel(){

        $this->display("addStoreForExcel");
    }

    public function uploadExcel($dir = 'excelUpload') {

        set_time_limit(C('MAX_RUN_TIME'));
        $upload = new \Think\Upload();
        $upload->maxSize  = 93145728 ;
        $upload->saveRule =time;
        $upload->allowExts  = array('xls','xlsx','csv');
        $upload->savePath =  $dir.'/';
        $info = $upload->upload();

        if(!$info)
        {
            $this->error($upload->getError());
        }else{
            $PicAndPath = array(
                'picpath'=>str_ireplace(DIRECTORY_SEPARATOR,'/',$info['file']['savepath']) ,
                'picname'=>$info['file']['savename']
            );

            $this -> ajaxReturn($PicAndPath,'json');
        }
    }

    public function submitStoreExcel(){

        //获取excel数据
        $svr = new AnalyzeExcelService();
        $filePath = "Uploads/".$_REQUEST['exc'];
        $excelData = $svr->getExcelData($filePath);
        $excelData = $excelData['data'];
        unset($excelData[1]);//表头

        //企业数据
        $enterpriseData = $this->WechatKaEnterpriseModel->select();
        $enterpriseData = useFieldAsArrayKey($enterpriseData,'code');

        $districtModel = M('District');

        //各企业门店编号最大值
        $sql = 'SELECT
                    ent.`code` entCode,
                    MAX(store.`code`) maxStoreCode
                FROM
                    jpgk_wechat_ka_enterprise ent
                LEFT JOIN jpgk_wechat_ka_stores store ON store.enterprise_id = ent.id
                GROUP BY
                    ent.id';
        $maxCodeData = M()->query($sql);
        $maxCodeData = useFieldAsArrayKey($maxCodeData,'entCode');

        $data = [];
        $error = '';
        foreach ($excelData as $k=>$v){
            if($error != ''){
                break;
            }
            if($v['A'] == ''){
                $error .= '第'.$k.'行未填写门店名称';
                break;
            }
            if($v['B'] == ''){
                $error .= '第'.$k.'行未填写门店省份';
                break;
            }
            if($v['C'] == ''){
                $error .= '第'.$k.'行未填写门店市区';
                break;
            }
            if($v['D'] == ''){
                $error .= '第'.$k.'行未填写门店地址';
                break;
            }
            if($v['E'] == ''){
                $error .= '第'.$k.'行未填写联系人';
                break;
            }
            if($v['F'] == ''){
                $error .= '第'.$k.'行未填写联系电话';
                break;
            }
            if($v['G'] == ''){
                $error .= '第'.$k.'行未填写企业编号';
                break;
            }

            $cityText = $v['C'];
            $cityData = $districtModel->where(['level'=>2,'name'=>['LIKE','%'.$cityText.'%']])->find();
            if(!$cityData){
                $error .= '第'.$k.'行市区名称填写错误';
                break;
            }
            $city = $cityData['id'];
            $province = $cityData['upid'];

            $entData = $enterpriseData[$v['G']];
            if(!$entData){
                $error .= '第'.$k.'行企业编号填写错误';
                break;
            }
            $enterpriseId = $entData['id'];

            $tmpData = [];
            $tmpData['name'] = $v['A'];
            $tmpData['province'] = $province;
            $tmpData['city'] = $city;
            $tmpData['stores_address'] = $v['D'];
            $tmpData['link_person'] = $v['E'];
            $tmpData['link_phone'] = $v['F'];
            $tmpData['enterprise_id'] = $enterpriseId;
            if($maxCodeData[$v['G']]['maxStoreCode']){
                $code = $maxCodeData[$v['G']]['maxStoreCode']+1;
            }else{
                $code = intval(strval($v['G']).'001');
            }

            $tmpData['code'] = $code;
            $maxCodeData[$v['G']]['maxStoreCode'] = $code;

            $data[] = $tmpData;
        }

        if($error != ''){
            $this->ajaxReturn(['status'=>0,'msg'=>$error]);
        }else{
            if($data){
                $result = $this->WechatKaStoresModel->addAll($data);
                if($result){
                    $this->ajaxReturn(['status'=>1,'msg'=>'suc']);
                }else{
                    $this->ajaxReturn(['status'=>0,'msg'=>'添加失败']);
                }
            }else{
                $this->ajaxReturn(['status'=>0,'msg'=>'数据为空']);
            }
        }
    }


    public function viewEvaluation(){
        $map['appraise.order_id'] = $_REQUEST['ids'][0];
        $field = "orde.order_code,member.name member_name,worker.uid,worker.name,appraise.delivery_score,appraise.content,appraise.create_time,appraise.id,facilitator.name faci_name";
        list($list,$totalCount)=$this->WechatOrderAppraiseModel->getAppriaseListByPage($map,1,'orde.update_time desc',$field,20);
        $this->assign("list",$list);
        $this->display("viewEvaluation");
    }
    /**
     * 年服务订单列表
     * @author  MQ.
     */
    public function yearServiceOrderList($page=1,$r=20){
        //拼接搜索商品名称
        $order_code = I("order_code");
        if($order_code != ""){
            $map['orde.order_code'] = ['like','%'.$order_code.'%'];
        }

        $store_name = I("store_name");
        if($store_name != ""){
            $map['orde.store_name'] = ['like','%'.$store_name.'%'];
        }
        $name = I("name");
        if($name != ""){
            $map['faci.name'] = ['like','%'.$name.'%'];
        }

        $create_time = I("selectDate");
        if($create_time != 0){
            $map['selectDate'] = $create_time;
        }

        $order_statu = I("order_state");
        if($order_statu != 0){
            $order_statu -= 1;
            $map['orde.order_state'] = ['eq',$order_statu];
            //筛选已完成订单包括已评价订单
            if($order_statu == 3){
                $map['orde.order_state'] = ['in',[3,7]];
            }
        }
        $map["is_year"] = 1;
        $map["is_main"] = 1;

        $field = "orde.id,orde.year_service_id, orde.order_code, orde.create_time, orde.store_name, orde.is_ka, orde.link_person, orde.link_phone, faci.name, faci.phone, faci.type, orde.order_state, ys.service_num_total, ys.service_num_remain, ys.start_time, ys.end_time, ys.money_total";
        list($list,$totalCount) = $this->WechatOrderModel->getYearOrderList($map, $page, 'orde.create_time desc', $field, $r);

        foreach($list as &$vmas){
            $vmas['create_time'] = substr($vmas['create_time'],0,16);
            $vmas['type'] = $vmas['type'] == 1 ? "门店消杀" : ($vmas['type'] == 2 ? "门店维修" : "烟道清洗");
            $vmas['is_ka'] = $vmas['is_ka'] == 1 ? '是' : '否';
            $vmas['order_state'] = $this->getOrderStatusText($vmas['order_state']);
            $recordChange = M('wechat_changefac_log')->where(['order_id'=>$vmas['id']])->find();
            $vmas['is_change'] = "无";
            if(!empty($recordChange)){
                $vmas['is_change'] = "<span style='color: red'>有</span>";
            }
        }
		//导出excel数据列表
		$content = I('');
		$year_attr['href'] = U('toExcelYearOrderList',$content);
		$year_attr['class']='btn btn-ajax';

		$child_attr['href'] = U('toExcelChildOrderList',$content);
		$child_attr['class']='btn btn-ajax';

        $builder=new AdminListBuilder();
        $builder->title(L('消杀年服务订单管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/yearServiceOrderList'))
			->button('导出年订单',$year_attr)
			->button('导出子订单',$child_attr)
            ->buttonModalPopup(U('Wechat/viewKillReport'),null,'查看消杀报告',array('data-title'=>'消杀报告','target-form'=>'ids'))
            ->search('订单号：','order_code','search','','','')
            ->search('门店名称：','store_name','search','','','') //
            ->search('服务商：','name','search','','','')
            ->select("下单时间：",'selectDate','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
            ->select('订单状态：','order_state','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "派单中"],["id" =>2,"value" => "已接单"],["id" =>3,"value" => "待支付"],["id" =>4,"value" => "已完成"],["id" =>5,"value" => "已取消"],["id" =>6,"value" => "无需上门"],["id" =>7,"value" => "已退款"],["id" =>8,"value" => "已评价"]]))
            ->keyText('order_code','订单号')
            ->keyText('create_time','下单时间')
            ->keyText('store_name','门店名称')
            ->keyText('is_ka','是否KA用户')
            ->keyText('link_person','联系人')
            ->keyText('link_phone','联系电话')
            ->keyText('name','服务商')
            ->keyText('phone','服务商联系电话')
            ->keyText('is_change','服务商更换')
//            ->keyText('type','服务类别')
            ->keyText('order_state','订单状态')
            ->keyText('service_num_total','服务次数')
            ->keyText('service_num_remain','剩余次数')
            ->keyText('start_time','服务起始时间')
            ->keyText('end_time','服务终止时间')
            ->keyText('money_total','服务费用')
            ->keyDoActionEdit('Wechat/yearServiceTimeOrderList?id=###','服务详情')
            ->keyDoActionEdit('Wechat/showChangeFacLog?id=###','更换服务商记录')
            ->pagination($totalCount,$r)
            ->display();
    }
    public function showChangeFacLog($id){
        $list = M('wechat_changefac_log')->where(['order_id'=>$id])->select();

        $builder=new AdminListBuilder();
        $builder->title(L('更换服务商日志'))
            ->data($list)
            ->keyId("id")
            ->keyText('old_fac_name','原服务商名称')
            ->keyText('new_fac_name','新服务商名称')
            ->keyHtml('reason','更换原因','150px;word-wrap: break-word;white-space: normal')
            ->keyText('change_time','更换时间')
            ->display();
    }
    /**
     * 年服务子订单列表
     * @author  MQ.
     */
    public function yearServiceTimeOrderList($id){
//        echo $id;die;
        $year_id = $this->WechatOrderModel->field("year_service_id")->where(['id'=>$id])->find();
        $map = ['yst.year_service_id'=>$year_id['year_service_id'], 'yst.order_id'=>['neq',0]];
        $field = "yst.`service_num`, orde.`order_code`,orde.`order_state`,orde.`update_time`, yst.`create_time`, mem.`name`, mem.`phone`, woa.`content`, orde.`is_sh`, orde.`year_service_id`";
        $list = $this->WechatOrderModel->getYearBabyOrderList($map, '', $field);
        if($list){
            foreach($list as &$vmas){
                //获取order_item表数据
                $shData = (new WechatOrderItemModel())->where(['old_order_code'=>$vmas['order_code']])->find();
                $vmas['is_sh'] = !empty($shData) ? '是' : '否';
                $vmas['order_state'] = $this->getOrderStatusText($vmas['order_state']);
            }
        }
        $builder=new AdminListBuilder();
        $builder->title(L('服务详情'))
            ->data($list)
            ->keyText('service_num','序号')
            ->keyText('order_code','订单号')
            ->keyText('order_state','订单状态')
            ->keyText('update_time','服务完成时间')
            ->keyText('name','消杀师傅')
            ->keyText('phone','联系电话')
            ->keyText('content','服务评价')
            ->keyText('is_sh','是否售后')
            ->display();
    }
/////////////////////////////////////////////////////////////KA巡检订单列表begin///////////////////////////////////////////////////////////////////////////
    /**
     * KA巡检订单列表
     * @author MQ.
     */
    public function inspectionOrderList($page=1,$r=20){
        $map = [];
        $field = 'i.*, e.name as enterprise_name, e.code as enterprise_code, e.link_person, e.link_phone, f.name as facilitator_name, f.phone';
        list($list,$totalCount) = (new WechatInspectionModel())->getInspectionList($map, '', $field, $page, $r);
        foreach($list as &$vmas){
            if($vmas['inspection_status'] == 1){
                $vmas['inspection_status'] = '新工单';
            }elseif($vmas['inspection_status'] == 2){
                $vmas['inspection_status'] = '服务中';
            }elseif($vmas['inspection_status'] == 3){
                $vmas['inspection_status'] = '已取消';
            }elseif($vmas['inspection_status'] == 4){
                $vmas['inspection_status'] = '已完成';
            }
            if($vmas['payment'] == 1){
                $vmas['payment'] = '季付';
            }elseif($vmas['payment'] == 2){
                $vmas['payment'] = '半年付';
            }elseif($vmas['payment'] == 3){
                $vmas['payment'] = '年付';
            }
        }
        $builder=new AdminListBuilder();
        $builder->title(L('KA巡检订单列表'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/inspectionOrderList'))
            ->search('订单号：','inspection_code','search','','','')
            ->search('门店名称：','store_name','search','','','') //
            ->search('服务商：','name','search','','','')
            ->keyId('inspection_id')
            ->keyText('inspection_code','订单号')
            ->keyText('create_time','下单时间')
            ->keyText('enterprise_name','企业名称')
            ->keyText('enterprise_code','企业编号')
            ->keyText('link_person','联系人')
            ->keyText('link_phone','联系电话')
            ->keyText('facilitator_name','服务商')
            ->keyText('phone','服务商联系电话')
            ->keyText('inspection_status','订单状态')
            ->keyText('service_num_total','门店数量')
            ->keyText('service_start_time','服务起始时间')
            ->keyText('service_end_time','服务终止时间')
            ->keyText('service_price','服务费用')
            ->keyText('payment','支付方式')
            ->keyDoActionEdit('Wechat/getInspectionOrderChildrenList?id=&&&','查看订单详情')
            ->pagination($totalCount,$r)
            ->display();
    }
    /**
     * KA巡检订单详情
     * @author MQ.
     */
    public function getInspectionOrderChildrenList($id){
        $map = ['inspection_id'=>$id,'status'=>1];
        $field = "";
        $list = (new WechatInspectionStoreModel())->getInspectionOrderChildrenList($map, '', $field);
        foreach($list as $key=>$val){
            $storeInfo = (new WechatKaStoresModel())->getOnceInfo(['id'=>$val['store_id']],'');
            $list[$key]['store_name'] = $storeInfo['name'];
            $list[$key]['store_code'] = $storeInfo['code'];
            $list[$key]['service_num_use'] = $val['service_num_total']-$val['service_num_remain'];
        }
        $builder=new AdminListBuilder();
        $builder->title(L('KA巡检订单详情'))
            ->data($list)
            ->keyId("inspection_store_id")
            ->keyText('store_name','门店名称')
            ->keyText('store_code','门店编号')
            ->keyText('','店长')
            ->keyText('','联系电话')
            ->keyText('service_num_total','总次数')
            ->keyText('service_num_use','已巡检次数')
            ->keyText('service_num_remain','剩余巡检次数')
            ->keyDoActionEdit('Wechat/serviceDetailList?id=^^^','查看服务详情')
            ->display();
    }
    /**
     * KA服务详情
     * @author MQ.
     */
    public function serviceDetailList($id){
        $money = 0;
        $map = ['inspection_store_id'=>$id];
        $field = "*";
        $list = (new WechatInspectionStoreChildModel())->serviceDetailList($map, '', $field);
        foreach($list as $key=>$val){
            $memberInfo = (new WechatMemberModel())->getOnceInfo(['uid'=>$val['inspector_id']],'');
            $storeInfo = (new WechatKaStoresModel())->getOnceInfo(['id'=>$val['store_id']],'');
            $facilitatorInfo = (new WechatFacilitatorModel())->getOnceInfo(['id'=>$val['facilitator_id']],'');
            $childDeviceInfo = (new WechatInspectionStoreChildDeviceModel())->getInspectionStoreChildDeviceByInspectionStoreChildId(['inspection_store_child_id'=>$val['inspection_store_child_id']],'');
            if($childDeviceInfo){
                foreach($childDeviceInfo as $k=>$v){
                    $list[$key]['money'] += $v['moeny'];
                }
            }
            $list[$key]['store_name'] = $storeInfo['name'];
            $list[$key]['facilitator_name'] = $facilitatorInfo['name'];
//            $list[$key]['money'] = $money;
            $list[$key]['user_name'] = $memberInfo['name'];
            $list[$key]['user_phone'] = $memberInfo['phone'];
            $list[$key]['id'] = $val['inspection_store_child_id'];
        }
        $builder=new AdminListBuilder();
        $builder->title(L('服务详情'))
            ->buttonKAModalPopup(U('Wechat/deviceDetail'),null,'查询明细',['data-title'=>'费用明细','target-form'=>'ids'])
            ->data($list)
            ->keyText('service_num','服务次数')
            ->keyText('child_order_code','订单号')
            ->keyText('store_name','门店名称')
            ->keyText('finish_time','巡检完成时间')
            ->keyText('facilitator_name','服务商')
            ->keyText('user_name','巡检员')
            ->keyText('user_phone','联系电话')
            ->keyText('money','维修费用')
            ->display();
    }

    /**
     * 查询明细
     */
    public function deviceDetail(){
        $data = [];
        $ids = $_REQUEST['ids'];
        $childDeviceInfo = (new WechatInspectionStoreChildDeviceModel())->getInspectionStoreChildDeviceByInspectionStoreChildId(['inspection_store_child_id'=>$ids[0],'yet_repairs_id'=>array('GT', 0)],'');
        if($childDeviceInfo){
            foreach ($childDeviceInfo as $key => $val) {
                $deviceInfo = (new WechatDeviceModel())->info($val['device_id'],'');
                $orderItemInfo = (new WechatOrderItemModel())->getDataByOrderId($val['yet_repairs_id']);
                $data[$key]['device_name'] = $deviceInfo['device_name'];
                $data[$key]['change_parts_text'] = $orderItemInfo['change_parts_text'];
                $data[$key]['money'] = $val['moeny'];
            }
        }
        $this->assign('list',$data);
        $this->display("deviceDetail");
    }
    /////////////////////////////////////////////////////////////KA巡检订单列表end///////////////////////////////////////////////////////////////////////////

	/**
	 * 导出年订单列表
	 */
	public function toExcelYearOrderList(){
		//拼接搜索商品名称
		$order_code = I("order_code");
		if($order_code != ""){
			$map['orde.order_code'] = ['like','%'.$order_code.'%'];
		}

		$store_name = I("store_name");
		if($store_name != ""){
			$map['orde.store_name'] = ['like','%'.$store_name.'%'];
		}
		$name = I("name");
		if($name != ""){
			$map['faci.name'] = ['like','%'.$name.'%'];;
		}

		$create_time = I("selectDate");
		if($create_time != 0){
			$map['selectDate'] = $create_time;
		}

		$order_statu = I("order_state");
		if($order_statu != 0){
			$order_statu -= 1;
			$map['orde.order_state'] = ['eq',$order_statu];
			//筛选已完成订单包括已评价订单
			if($order_statu == 3){
				$map['orde.order_state'] = ['in',[3,7]];
			}
		}
		$map["orde.is_year"] = 1;
		$map["orde.is_main"] = 1;

		$field = "orde.id,orde.year_service_id, orde.order_code, orde.create_time, orde.store_name, orde.is_ka, orde.link_person, orde.link_phone, faci.name, faci.phone, faci.type, orde.order_state, ys.service_num_total, ys.service_num_remain, ys.start_time, ys.end_time, ys.money_total";
		$list = $this->WechatOrderModel->getYearOrderListForExcel($map, 'orde.create_time desc', $field);
		$excelData = array ();
		$title = array (
			"row" => 1,
			"count" => 15,
			"title" => [
				'序号',
				'订单号',
				'下单时间',
				'门店名称',
				'是否KA用户',
				'联系人',
				'联系电话',
				'服务商',
				'服务商联系电话',
				'服务类别',
				'订单状态',
				'服务次数',
				'剩余次数',
				'服务起始时间',
				'服务终止时间',
				'服务费用',
			]
		);

		foreach($list as $ck=>$cv){
			$excelData[$ck]['id'] = $cv['id'];
			$excelData[$ck]['order_code'] = $cv['order_code']." ";
			$excelData[$ck]['create_time'] = $cv['create_time'];
			$excelData[$ck]['store_name'] = $cv['store_name'];
			$excelData[$ck]['is_ka'] = $cv['is_ka'] == 1 ? '是' : '否';
			$excelData[$ck]['order_state'] = $this->getOrderStatusText($cv['order_state']);
			$excelData[$ck]['link_person'] = filterEmoji($cv['link_person']);
			$excelData[$ck]['link_phone'] = $cv['link_phone'];

			$excelData[$ck]['name'] = $cv['name'];
			$excelData[$ck]['phone'] = $cv['phone'];
			$excelData[$ck]['type'] = $cv['type'] == 1 ? "门店消杀" : ($cv == 2 ? "门店维修" : "烟道清洗");
			$excelData[$ck]['order_state'] = $this->getOrderStatusText($cv['order_state']);
			$excelData[$ck]['service_num_total'] = $cv['service_num_total'];
			$excelData[$ck]['service_num_remain'] = $cv['service_num_remain'];
			$excelData[$ck]['start_time'] = $cv['start_time'];
			$excelData[$ck]['end_time'] = $cv['end_time'];
			$excelData[$ck]['money_total'] = $cv['money_total'];
		}
		$len = count ( $excelData );
		$info = array (
			"table_name_one" =>  "消杀年订单数据表",
			"table_name_two" => "消杀年订单信息报表",
			"lister" => get_username(get_uid()),
			"tabletime" => date ( "Y-m-d", time () )
		);

		$optional = array (
			'colwidth' => array (
				'A' => '10',
				'B' => '30',
				'C' => '20',
				'D' => '20',
				'E' => '20',
				'F' => '20',
				'G' => '20',
				'I' => '20',
				'H' => '15',
				'J' => '15',
				'K' => '30',
				'L' => '15',
				'M' => '15',
				'N' => '15',
				'O' => '15',
				'P' => '15',
				'Q' => '15'
			),
			'fontsize' => array (
				'A1:O' . ($len + 4) => '10'
			),
			'colalign' => array (
				'A1:O' . ($len + 4) => '2'
			)
		);
		outputExcel ( $info, $title, $excelData, $optional );
	}

	/**
	 * 导出年订单的子订单列表
	 */
	public function toExcelChildOrderList(){
		//拼接搜索商品名称
		$order_code = I("order_code");
		if($order_code != ""){
			$map['orde.order_code'] = ['like','%'.$order_code.'%'];
		}

		$store_name = I("store_name");
		if($store_name != ""){
			$map['orde.store_name'] = ['like','%'.$store_name.'%'];
		}
		$name = I("name");
		if($name != ""){
			$map['faci.name'] = ['like','%'.$name.'%'];;
		}

		$create_time = I("selectDate");
		if($create_time != 0){
			$map['selectDate'] = $create_time;
		}

		$order_statu = I("order_state");
		if($order_statu != 0){
			$order_statu -= 1;
			$map['orde.order_state'] = ['eq',$order_statu];
			//筛选已完成订单包括已评价订单
			if($order_statu == 3){
				$map['orde.order_state'] = ['in',[3,7]];
			}
		}

		$map["orde.is_year"] = 1;
		$map["orde.is_main"] = 1;
		//获取年订单数据
		$year_field = "orde.id,orde.order_code,orde.year_service_id";
		$yearData = $this->WechatOrderModel->getYearOrderListForExcel($map, 'orde.create_time desc', $year_field);
		$yearIdData = array_column($yearData,'year_service_id');
		unset($map['orde.order_code']);
		$map["orde.is_year"] = 1;
		$map["orde.is_main"] = 0;
		//增加条件
		$map['yst.year_service_id'] = ['in',$yearIdData];
		$field = "orde.*,item.store_area,item.store_scene,item.insect_species,item.old_order_code,item.insect_time,
		item.after_sale_text,item.reservation_type,item.difference_price,mem.uid,mem.name,faci.id faci_id,
		faci.name faci_name,ys.service_num_total,yst.service_num";

		$list = $this->WechatOrderModel->getYearBabyOrderList($map, 'yst.create_time desc,yst.service_num asc', $field);
		$excelData = array ();
//var_dump($list);die;
		$title = array (
			"row" => 1,
			"count" => 15,
			"title" => [
				'序号',
				'年订单ID',
				'年订单订单号',
				'服务总次数',
				'当前服务次数',
				'订单号',
				'下单时间',
				'完成时间',
				'用户',
				'联系电话',
				'门店名称',
				'门店地址',
				'门店面积（平米）',
				'门店场景',
				'虫害类别',
				'发现虫害时间',
				'预约服务类型',
				'服务商ID',
				'服务商名称',
				'师傅ID',
				'师傅名称',
				'总计费用（元）',
				'订单状态',
			]
		);
		foreach($list as $ck=>$cv){
			$excelData[$ck]['id'] = $cv['id'];
			foreach($yearData as $yeark=>$yearv){
				if($yearv['year_service_id'] == $cv['year_service_id']){
					$excelData[$ck]['year_order_id'] = $yearv['id'];
					$excelData[$ck]['year_order_code'] = $yearv['order_code']." ";
					break;
				}
			}
			$excelData[$ck]['service_num_total'] = $cv['service_num_total'];
			$excelData[$ck]['service_num'] = $cv['service_num'];
			$excelData[$ck]['order_code'] = $cv['order_code']." ";
			$excelData[$ck]['create_time'] = $cv['create_time'];
			//拼接订单完成时间
			if($cv['order_state']==3 || $cv['order_state']==7){
				$excelData[$ck]['orderFinishTime']=$cv['update_time'];
			}else{
				$excelData[$ck]['orderFinishTime']='空';
			}
			$excelData[$ck]['link_person'] = filterEmoji($cv['link_person']);
			$excelData[$ck]['link_phone'] = $cv['link_phone'];
			$excelData[$ck]['store_name'] = $cv['store_name'];
			$excelData[$ck]['detailed_address'] = $cv['detailed_address'];
			switch($cv['store_area']){
				case 1:
					$excelData[$ck]['store_area'] = "0-100";
					break;
				case 2:
					$excelData[$ck]['store_area'] = "101-200";
					break;
				case 3:
					$excelData[$ck]['store_area'] = "201-300";
					break;
				case 4:
					$excelData[$ck]['store_area'] = "301-400";
					break;
				case 5:
					$excelData[$ck]['store_area'] = "401-500";
					break;
				case 6:
					$excelData[$ck]['store_area'] = "501-600";
					break;
				case 7:
					$excelData[$ck]['store_area'] = "600以上";
					break;
			}
			switch($cv['store_scene']){
				case 1:
					$excelData[$ck]['store_scene'] = "商场";
					break;
				case 2:
					$excelData[$ck]['store_scene'] = "写字楼";
					break;
				case 3:
					$excelData[$ck]['store_scene'] = "美食城";
					break;
				case 4:
					$excelData[$ck]['store_scene'] = "底商";
					break;
				case 5:
					$excelData[$ck]['store_scene'] = "其他";
					break;
			}
			$excelData[$ck]['insect_species'] = $cv['insect_species'] == 1 ? "老鼠" : ($cv['insect_species'] == 2 ? "蟑螂" : "蚊蝇");
			$excelData[$ck]['insect_time'] = $cv['insect_time'] == 1 ? "一周" : ($cv['insect_time'] == 2 ? "二周" : ($cv['insect_time'] == 3 ? "三周" : "一个月以上"));
			$excelData[$ck]['reservation_type'] = "单次";
			$excelData[$ck]['faci_id'] = $cv['faci_id'];
			$excelData[$ck]['faci_name'] = $cv['faci_name'];
			$excelData[$ck]['uid'] = $cv['uid'];
			$excelData[$ck]['name'] = $cv['name'];
			$excelData[$ck]['totle_price'] = $cv['money_total'];
			$excelData[$ck]['order_state'] = $this->getOrderStatusText($cv['order_state']);
		}
		$len = count ( $excelData );
		$info = array (
			"table_name_one" =>  "消杀子订单数据表",
			"table_name_two" => "消杀子订单信息报表",
			"lister" => get_username(get_uid()),
			"tabletime" => date ( "Y-m-d", time () )
		);

		$optional = array (
			'colwidth' => array (
				'A' => '10',
				'B' => '30',
				'C' => '20',
				'D' => '20',
				'E' => '20',
				'F' => '20',
				'G' => '20',
				'I' => '20',
				'H' => '15',
				'J' => '15',
				'K' => '30',
				'L' => '15',
				'M' => '15',
				'N' => '15',
				'O' => '15',
				'P' => '15',
				'Q' => '15',
				'R' => '15',
				'S' => '20',
			),
			'fontsize' => array (
				'A1:O' . ($len + 4) => '10'
			),
			'colalign' => array (
				'A1:O' . ($len + 4) => '2'
			)
		);
		outputExcel ( $info, $title, $excelData, $optional );
	}
//*************************************************服务须知列表开始**************************************************

    //服务须知列表start
    public function appGuidelinesList($page=1,$r=20)
    {
        $map = [];
        $map['status']=['neq',-1];
        $map['type']=['in',[4,5,6]];

        list($list,$totalCount)=$this->WechatServiceGuidelinesModel->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){

            switch($val['type']){
                case 4:
                    $val['typeText'] = '餐讯上门';
                    break;
                case 5:
                    $val['typeText'] = '维养系统';
                    break;
                case 6:
                    $val['typeText'] = '微信点餐系统';
                    break;
            }
        }

        $builder=new AdminListBuilder();
        $builder->title('App第三方服务')
            ->data($list)
            ->buttonNew(U('Wechat/editAppGuidelines'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/delAppGuidelines'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/startAppGuidelines'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/endAppGuidelines'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('typeText','服务对应入口')
            ->keyGxptStatus()
            ->keyText('create_time','创建时间')
            ->keyText('update_time','更新时间')
            ->keyDoActionEdit('Wechat/editAppGuidelines?id=###');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 服务须知添加
     * @param int $id
     */
    public function editAppGuidelines($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            $data = I('post.');
            if($_POST["guidelinesType"] == 0){

                $error .= "不可为空！";
            }else{
                $data['type'] = $data['guidelinesType'];
                unset($data['guidelinesType']);
            }

            if(empty($_POST["content"])){

                $error .= "内容不可为空！";
            }
            //如果为添加则验证是否已有该条记录
            if(empty($id)){
                $haveData = $this->WechatServiceGuidelinesModel->where(['type'=>['eq',$data['type']],'status'=>['neq',-1]])->select();
                if(!empty($haveData)){
                    $error .= $data['type']==4 ? "餐讯上门已存在":($data['type']==5 ? "维养系统已存在":"微信点餐系统须知已存在");
                }
            }
            if(!empty($error)){

                $this->error($error);
            }

            if ($cate = $this->WechatServiceGuidelinesModel->editData($data)) {

                $this->success($title.L('_SUCCESS_'), U('Wechat/appGuidelinesList'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatServiceGuidelinesModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatServiceGuidelinesModel->find($id);
            }

            $builder->title($title.L('服务须知'))
                ->data($data)
                ->keyId()
                ->keySelect('guidelinesType','第三方服务入口','',['0' => '请选择','4' => '餐讯上门','5' => '维养系统','6' => '微信点餐系统'])->keyDefault('guidelinesType',$data['type'])
                ->keyEditor('content',L('_CONTENT_'),'','all',['width' => '700px', 'height' => '400px'])
                ->buttonSubmit(U('Wechat/editAppGuidelines'))->buttonBack()
                ->display();
        }
    }

    //删除服务须知数据
    public function delAppGuidelines(){

        $Ids = I('post.ids');
        if($Ids){
            $this->WechatServiceGuidelinesModel->where(['id'=>['in',$Ids]])->save(['status' => '-1']);
        }

        $this->success('删除成功');
    }

    //启用服务须知数据
    public function startAppGuidelines(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatServiceGuidelinesModel->where(['id'=>['IN',$Ids]])->save(['status'=>1]);
        }

        $this->success('启用成功');
    }

    //禁用服务须知数据
    public function endAppGuidelines(){
        $Ids = I('post.ids');
        if($Ids){

            $this->WechatServiceGuidelinesModel->where(['id'=>['IN',$Ids]])->save(['status'=>2]);
        }

        $this->success('禁用成功');
    }


    /////////////////////////////////////////////////////////////服务须知结束/////////////////////////////////////////////////////////////////////////////
    /**
     * 展示设备列表
     */
    public function showDeviceList($id){
        $deviceName = I("deviceName");
        if($deviceName != ""){
            $map['device_name'] = ['like','%'.$deviceName.'%'];
        }
        $device_code = I("deviceCode");
        if($device_code != ""){
            $map['device_code'] = ['like','%'.$device_code.'%'];
        }
        $equipmentType = I("equipmentType");
        if($equipmentType != 0){
            $map['category'] = $equipmentType;
        }
        $is_operate = I("is_operate");
        if($is_operate != 0){
            $map['is_operate'] = ['eq',$is_operate];
        }
        //获取门店信息
        $storeInfo = $this->WechatKaStoresModel->where(['id'=>$id])->find();
        $equipmentData = $this->WechatEquipmentCategoryModel->where(['status'=>['neq',-1]])->select();
        $equipmentList = [];
        if($equipmentData){
            foreach ($equipmentData as $k=>$v){
                $equipmentList[$k]['id'] = $v['id'];
                $equipmentList[$k]['value'] = $v['name'];
            }
        }
        //todo 获取门店设备信息
        $map['device.store_id'] = $id;
        $map['device.status'] = ["not in",[-1,3]];
        $list = $this->WechatDeviceModel->alias("device")
            ->join("left join jpgk_wechat_equipment_category category on device.category = category.id")
            ->where($map)
            ->field("device.*,category.name category")
            ->select();
        foreach($list as &$lval){
            $lval['is_operate'] = $lval['is_operate'] == 1 ? "有" : "无";
        }
        //导出excel数据列表
        $content = I('');
        $content['store_id'] = $id;
        $attr = [];
        $attr['href'] = U('exportDeviceExcel',$content);
        $attr['class']='btn btn-ajax';
        $builder=new AdminListBuilder();
        $builder->title(L($storeInfo['name']))
            ->data($list)
            ->buttonNew(U('Wechat/editDevice?store_id='.$id))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateDevice?status=-1'), 'target-form' => 'ids'])
            ->button('导出设备',$attr)
            ->button('导出模板',['class' => 'btn btn-ajax','href'=>'store_device.xlsx'])
            ->buttonModalPopup(U('Wechat/addDeviceForExcel?store_id='.$id),null,'导入设备',['data-title'=>'导入设备'])
            ->search("设备名称",'deviceName','text','')
            ->search("设备编号",'deviceCode','text','')
            ->select("设备类别",'equipmentType','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$equipmentList))
            ->select('巡检操作','is_operate','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "有"],["id" =>2,"value" => "无"]]))
            ->keyId('id','序号')
            ->keyText('device_name','设备名称')
            ->keyText('device_code','设备编号')
            ->keyText('device_model','设备型号')
            ->keyText('brand','设备品牌')
            ->keyText('category','设备类别')
            ->keyText('is_operate','巡检操作')
            ->keyGxptStatus()
            ->keyDoActionEdit('Wechat/editDevice?store_id='.$id.'&id=###')
            ->display();
    }
    /**
     * 编辑设备
     * @param int $id
     */
    public function editDevice($id=0,$store_id=''){

        $title=$id?L('_EDIT_'):L('_ADD_');

        if(IS_POST){

            $data = I('');
            $error = '';
            if($data['device_name'] == ''){
                $error .= '设备名称不能为空，';
            }
            if($data['brand'] == ''){
                $error .= '设备品牌不能为空，';
            }
            if($data['device_model'] == ''){
                $error .= '设备型号不能为空，';
            }
            if(!empty($error)){

                $error = trim($error,'，');
                $this->error($error);
            }

            //TODO 生成设备编码
            if(!$data['id']){
                $data['device_code'] = getDeviceCode($data['name']);
            }

            $result = $this->WechatDeviceModel->editData($data);
            if ($result) {
                $this->success($title.L('_SUCCESS_'), U('Wechat/showDeviceList?id='.$store_id));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatDeviceModel->getError());
            }

        }else{
            $builder = new AdminConfigBuilder();

            $data = [];

            if ($id != 0) {
                $data = $this->WechatDeviceModel->find($id);
            }
            //获取设备类别
            $equipmentData = $this->WechatEquipmentCategoryModel->where(['status'=>['neq',-1]])->select();
            $equipmentList = [];
            if($equipmentData){
                foreach ($equipmentData as $v){
                    $equipmentList[$v['id']] = $v['name'];
                }
            }

            $builder->title($title.L("设备"))
                ->data($data)
                ->keyHidden("id","")->keyDefault('id',$id)
                ->keyHidden("store_id","")->keyDefault('store_id',$store_id)
                ->keyText('device_name','设备名称')->keyDefault('device_name',$data['device_name'])
                ->keyText('device_model','设备型号')->keyDefault('device_model',$data['device_model'])
                ->keyText('brand','设备品牌')->keyDefault('brand',$data['brand'])
                ->keySelect('category','设备类别','',$equipmentList)->keyDefault('category',$data['category'])
                ->keySelect('is_operate','巡检操作',"",["1"=>"有","2"=>"无"])->keyDefault('is_operate',$data['is_operate'])
                ->buttonSubmit(U('Wechat/editDevice'))->buttonBack()
                ->display();
        }
    }

    /**
     * 设备删除
     * @param $ids
     * @param $status
     */
    public function updateDevice($ids,$status){
        $this->WechatDeviceModel->where(['id'=>['IN',$ids]])->save(['status'=>$status]);
        $this->success("删除成功");
    }
    /**
     * 导入设备弹框
     */
	public function addDeviceForExcel($store_id){

        $this->store_id = $store_id;
        $this->display("addDeviceForExcel");
    }

    /**
     * 导出门店设备列表
     */
    public function exportDeviceExcel(){
        $deviceName = I("deviceName");
        if($deviceName != ""){
            $map['device_name'] = ['like','%'.$deviceName.'%'];
        }
        $device_code = I("deviceCode");
        if($device_code != ""){
            $map['device_code'] = ['like','%'.$device_code.'%'];
        }
        $equipmentType = I("equipmentType");
        if($equipmentType != 0){
            $map['category'] = $equipmentType;
        }
        $is_operate = I("is_operate");
        if($is_operate != 0){
            $map['is_operate'] = ['eq',$is_operate];
        }
        $map['store_id'] = I("store_id");
        $map['status'] = ["neq",'-1'];
        $list=$this->WechatDeviceModel->where($map)->select();
        $storeInfo = $this->WechatKaStoresModel->find($map['store_id']);
        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => [
                'ID',
                '设备名称',
                '设备编码',
                '设备类别',
                '设备型号',
                '设备品牌',
                '门店名称',
                '是否需要操作',
                '设备状态',
            ]
        );
        foreach($list as $ck=>$cv){
            $excelData[$ck]['id'] = $cv['id'];
            $excelData[$ck]['device_name'] = $cv['device_name'];
            $excelData[$ck]['device_code'] = $cv['device_code']." ";
            $excelData[$ck]['category'] = $this->getEquipmentType($cv['category']);
            $excelData[$ck]['device_model'] = $cv['device_model'];
            $excelData[$ck]['brand'] = $cv['brand'];
            $excelData[$ck]['store_name'] = $storeInfo['name'];
            $excelData[$ck]['is_operate'] = $cv['is_operate'] == 1 ? "有" : "无";
            $excelData[$ck]['status'] = $cv['status'] == 1 ? "启用" : "禁用";
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "设备列表",
            "table_name_two" => "设备列表信息表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '10',
                'B' => '10',
                'C' => '15',
                'D' => '20',
                'E' => '10',
                'F' => '30',
                'G' => '10',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    /**
     * 获取设备类别
     * @param $category
     * @return string
     */
    private function getEquipmentType($category){
        $type = "";
        switch($category){
            case 4:
                $type = "空调制冷设备";
                break;
            case 5:
                $type = "保温加热设备（电器类）";
                break;
            case 6:
                $type = "保温加热设备（燃气类）";
                break;
            case 7:
                $type = "保温加热设备（电磁类）";
                break;
            case 8:
                $type = "排风新风设备";
                break;
            case 9:
                $type = "不锈钢制品";
                break;
            case 10:
                $type = "内装修（电路、墙面、防水等问题）";
                break;
            case 11:
                $type = "其他设备";
                break;
        }
        return $type;
    }
    /**
     * 导入设备
     */
	public function submitDeviceExcel()
    {
        $id = $_REQUEST['id'];
        $filePath = "Uploads/" . $_REQUEST['exc'];
        $excelData = getExcelData($filePath);
        $excelData = $excelData['data'];
        unset($excelData[1]);//表头
        $data = [];
        $error = "";
        foreach ($excelData as $k=>$v){
            if($error != ''){
                break;
            }
            if($v['A'] == ''){
                $error .= '第'.$k.'行未填写设备类别';
                break;
            }
            if($v['C'] == ''){
                $error .= '第'.$k.'行未填写设备名称';
                break;
            }
            switch($v['A']){
                case "空调制冷设备":
                    $v['A'] = 4;
                    break;
                case "保温加热设备（电器类）":
                    $v['A'] = 5;
                    break;
                case "保温加热设备（燃气类）":
                    $v['A'] = 6;
                    break;
                case "保温加热设备（电磁类）":
                    $v['A'] = 7;
                    break;
                case "排风新风设备":
                    $v['A'] = 8;
                    break;
                case "不锈钢制品":
                    $v['A'] = 9;
                    break;
                case "内装修（电路、墙面、防水等问题）":
                    $v['A'] = 10;
                    break;
                case "其他设备":
                    $v['A'] = 11;
                    break;
            }
            $v['H'] = $v['H'] == "否" ? 2 : 1;
            $tmpData = [];
            $tmpData['device_code'] = getDeviceCode($v['C']);
            $tmpData['store_id'] = $id;
            $tmpData['device_model'] = $v['B'];
            $tmpData['device_name'] = $v['C'];
            $tmpData['brand'] = $v['D'];
            $tmpData['category'] = $v['A'];
            $tmpData['is_operate'] = $v['H'];
            $tmpData['create_time'] = date("Y-m-d H:i:s");

            $data[] = $tmpData;
            if($v['E'] != 1){
                $num = $v['E'];
                for($i=1;$i<$num;$i++){
                    $tmpData = [];
                    $tmpData['device_code'] = getDeviceCode($v['C']);
                    $tmpData['store_id'] = $id;
                    $tmpData['device_model'] = $v['B'];
                    $tmpData['device_name'] = $v['C'];
                    $tmpData['brand'] = $v['D'];
                    $tmpData['category'] = $v['A'];
                    $tmpData['is_operate'] = $v['H'];
                    $tmpData['create_time'] = date("Y-m-d H:i:s");
                    $data[] = $tmpData;
                }
            }
        }
        if($error != ''){
            $this->ajaxReturn(['status'=>0,'msg'=>$error]);
        }else {
            if ($data) {
//                //检验是否有其他设备
//                $dMap['device_name'] = "其他";
//                $dMap['store_id'] = $id;
//                $otherDevice = $this->WechatDeviceModel->where($dMap)->find();
//                if(empty($otherDevice)){
//                    //拼凑其他设备数据
//                    $otherDeviceData['device_code'] = getDeviceCode($v['C']);
//                    $otherDeviceData['store_id'] = $id;
//                    $otherDeviceData['device_model'] = "无";
//                    $otherDeviceData['device_name'] = "其他";
//                    $otherDeviceData['brand'] = "无";
//                    $otherDeviceData['category'] = 11;
//                    $otherDeviceData['is_operate'] = 1;
//                    $otherDeviceData['create_time'] = date("Y-m-d H:i:s");
//                    $data[] = $otherDeviceData;
//                }
                $result = $this->WechatDeviceModel->addAll($data);
                if ($result) {
                    $this->ajaxReturn(['status' => 1, 'msg' => 'suc']);
                } else {
                    $this->ajaxReturn(['status' => 0, 'msg' => '添加失败']);
                }
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '数据为空']);
            }
        }
    }
    /**
     * 企业主管管理
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function companyMaster($page=1,$r=20){
        $materId = I("materId");
        if($materId != ""){
            $map['member.uid'] = ['like','%'.$materId.'%'];
        }
        $materName = I("materName");
        if($materName != ""){
            $map['member.name'] = ['like',"%".$materName."%"];
        }
        $company = I("company");
        if($company != 0){
            $map['member.facilitator_id'] = ['eq',$company];
        }
        $status = I("status");
        if($status != 0){
            $map['member.status'] = ['eq',$status];
        }else{
            $map['member.status']=['neq',-1];
        }
        $map['member.type_user']=['eq',6];//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗
        $map['member.isadmin']=['eq',6];//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗
        $filed = "member.*,ent.name company_name";
        list($list,$totalCount)=$this->WechatMemberModel->getCompanyMasterList($map,$page,'member.update_time desc',$filed,$r);
        $content['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
        $company_list = $this->WechatKaEnterpriseModel->getData($content,'id,name value');
//        var_dump($company);die;
        foreach($list as &$vmas){
            $vmas['id'] = $vmas['uid'];
        }
        $builder=new AdminListBuilder();
        $builder->title(L('企业主管管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/companyMaster'))
            ->buttonNew(U('Wechat/addCompanyMaster'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateCompanyMaster?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateCompanyMaster?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateCompanyMaster?status=2'), 'target-form' => 'ids'])
            ->search("企业主管ID",'materId','text','')
            ->search("企业主管名称",'materName','text','')
            ->select('所属企业：','company','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),$company_list))
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','企业主管ID')
            ->keyText('name','企业主管名称')
            ->keyText('company_name','企业名称')
            ->keyGxptStatus()
            ->keyText('link_name','联系人')
            ->keyText('phone','联系电话')
            ->keyText('wx_code','企业微信绑定编号')
            ->keyDoActionEdit('Wechat/addCompanyMaster?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 添加和修改企业主管
     * @author  pangyongfu
     */
    public function addCompanyMaster($id=0){
        $title=$id?L('_EDIT_'):L('_ADD_');

        if (IS_POST) {
            //参数验证
            $data = I('');
            $error = "";
            if(empty($data["name"])){

                $error .= "企业主管不可为空！";
            }

            if(empty($data["link_name"])){

                $error .= "联系人不可为空！";
            }
            if(empty($data["phone"])){

                $error .= "联系电话不可为空！";
            }
            if(empty($data["wx_code"])){

                $error .= "企业微信绑定编号不可为空！";
            }
            if(!preg_match("/^1[345678]{1}\\d{9}$/",$data["phone"])){
                $error .= "联系电话格式不正确！";
            }
            if(!$id){
                //验证企业主管是否已添加
                $where['isadmin'] = $data['isadmin'];
                $where['wx_code'] = $data["wx_code"];
                $where['status'] = 1;//1--启用
                $masterInfo = $this->WechatMemberModel->where($where)->find();
                if(!empty($masterInfo)){
                    $error .= "用户已存在！";
                    return $error;
                }
            }

            //验证wxcode
            $userInfo = $this->checkWXCode($data["wx_code"],6,$data['isadmin']);
            if($userInfo['errcode'] != 0){
                $error .= "企业微信绑定编号在企业通讯录中不存在！";
            }
            if(!empty($error)){

                $this->error($error);
            }
            $data['type_user'] = 6;//1：门店用户 2：门店消杀 3： 门店维修 4：烟道清洗 6企业主管
            $data['isadmin'] = 6;// 6企业主管
            $cate = $this->WechatMemberModel->editData($data);
            if ($cate) {
                $this->success($title.L('_SUCCESS_'), U('Wechat/companyMaster'));
            } else {
                $this->error($title.L('_FAIL_').$this->WechatMemberModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = $this->WechatMemberModel->find($id);
            }else{
                $max_id = $this->WechatMemberModel->field("max(uid) uid")->find();
                $max_id = $max_id['uid'] ? $max_id['uid']+1 : 1;
            }
            $content['status'] = ['eq',1];//-1--删除；1--启用；2--禁用；
            $company_list = $this->WechatKaEnterpriseModel->getData($content,'id enterprise_id,name value');

            foreach($company_list as $clv){
                $companyData[$clv['enterprise_id']] = $clv['value'];
            }
            $builder->title($title.L('企业主管'))
                ->data($data)
                ->keyHidden("id","")->keyDefault('id',$id)
                ->keyReadOnly('uid','企业主管ID')->keyDefault('uid',$max_id)
                ->keyText('name','企业主管名称')->keyDefault('name',$data['name'])
                ->keyText('link_name','联系人')->keyDefault('link_name',$data['link_name'])
                ->keyText('phone','联系电话')->keyDefault('phone',$data['phone'])
                ->keyText('wx_code','企业微信绑定编号')->keyDefault('wx_code',$data['wx_code'])
                ->keySelect('enterprise_id','所属企业','',$companyData)->keyDefault('enterprise_id',$data['enterprise_id'])
                ->buttonSubmit(U('Wechat/addCompanyMaster'))->buttonBack()
                ->display();
        }
    }

    /**
     * 删除启用禁用企业主管
     * @author  pangyongfu
     */
    public function updateCompanyMaster(){
        $Ids = I('post.ids');
        $status = I('get.status');
        if($Ids){

            $this->WechatMemberModel->where(['uid'=>['IN',$Ids]])->save(['status'=>$status]);
        }
        $desc = $status == '1' ? "启用成功"  : ($status == '2' ? "禁用成功" : "删除成功");
        $this->success($desc);
    }

    /*********************************************************编写文章可中途截一半*******************************************************************************/
    /**
     * 编写文章可中途截一半 列表
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sharePaper($page=1,$r=20){

        if(I('title')){
            $map['title'] = ['like',"%".I('title')."%"];
        }
        $map['status']=['eq',1];
        $filed = "*";
        list($list,$totalCount)=(new WechatShareArticleModel())->getListByPage($map,$page,'create_time desc',$filed,$r);

        $builder=new AdminListBuilder();
        $builder->title(L('分享文章管理'))
            ->data($list)
            ->setSelectPostUrl(U('Wechat/sharePaper'))
            ->buttonNew(U('Wechat/editSharePaper'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Wechat/updateSharePaper?status=-1'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Wechat/updateSharePaper?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Wechat/updateSharePaper?status=2'), 'target-form' => 'ids'])
            ->search("文章标题",'title','text','')
            ->select('状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->keyId('id','ID')
            ->keyText('title','文章标题')
            ->keyText('abstract','文章摘要')
            ->keyText('PViews','阅读量')
            ->keyText('source','来源')
            ->keyGxptStatus()
            ->keyText('create_time','发布时间')
            ->keyDoActionEdit('Wechat/editSharePaper?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 编写文章可中途截一半 新增
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function editSharePaper($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {
            $data = I('post.');

            //参数验证
            $error = "";
            if(empty($data["title"])){

                $error .= "文章标题不可为空！<br>";
            }

            if(empty($data["abstract"])){

                $error .= "文章摘要不可为空！<br>";
            }

            if(empty($data["source"])){

                $error .= "文章来源不可为空！<br>";
            }

            if(empty($data["cover_img_id"])){

                $error .= "文章封面不可为空！<br>";
            }

            if(!empty($error)){

                $this->error($error);
            }
//            $pictureData = (new PictureModel())->where(['id'=>$data['cover_img_id']])->find();
//            $data['image_url'] = $pictureData['path'];
            $cate = (new WechatShareArticleModel())->editData($data);
             if ($cate!== false) {

                $this->success($title.L('_SUCCESS_'), U('Wechat/sharePaper'));
            } else {
                $this->error($title.L('_FAIL_').(new WechatShareArticleModel())->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = (new WechatShareArticleModel())->find($id);
                $videoDetail = (new WechatShareArticleModel())->where(['id'=>$id])->find();
                $data['content'] = $videoDetail['content'];
            }

            $builder->title($title.L('分享文章'))
                ->data($data)
                ->keyId()
                ->keyText('title','文章标题')
                ->keyText('abstract','文章摘要')
                ->keyText('source','文章来源')
                ->keyMultiImage('cover_img_id','微信分享封面')
                ->keyEditor('content',L('_CONTENT_'),'','all',array('width' => '700px', 'height' => '400px'))
                ->buttonSubmit(U('Wechat/editSharePaper'))->buttonBack()
                ->display();
        }
    }
}
