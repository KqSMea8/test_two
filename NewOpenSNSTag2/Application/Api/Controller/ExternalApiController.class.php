<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/4
 * Time: 10:01
 */

namespace Api\Controller;


use Admin\Model\PictureModel;
use Enterprise\Service\EnterpriseService;
use Goods\Model\SomethingPicModel;
use Think\Controller;
use Think\Log;
use Wechat\Model\WechatKaEnterpriseModel;
use Wechat\Model\WechatKaStoresModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderAppraiseModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Service\WechatPublicOrderService;
use Wechat\Service\WechatService;

class ExternalApiController extends Controller
{
	private $WechatOrderModel;
	private $WechatOrderItemModel;
	private $WechatPublicOrderService;
	private $WechatService;
	private $WechatMemberModel;
	private $WechatOrderAppraiseModel;
	private $PictureModel;
	private $SomethingPicModel;
	private $WechatKaEnterpriseModel;
	private $WechatKaStoresModel;
	private $member_id;//用户ID
	private $enterprise_code;//企业编号

	function _initialize()
	{

		$this->WechatOrderModel = new WechatOrderModel();
		$this->WechatOrderItemModel = new WechatOrderItemModel();
		$this->WechatPublicOrderService = new WechatPublicOrderService();
		$this->WechatService = new WechatService();
		$this->WechatMemberModel = new WechatMemberModel();
		$this->WechatOrderAppraiseModel = new WechatOrderAppraiseModel();
		$this->PictureModel = new PictureModel();
		$this->SomethingPicModel = new SomethingPicModel();
		$this->WechatKaEnterpriseModel = new WechatKaEnterpriseModel();
		$this->WechatKaStoresModel = new WechatKaStoresModel();
		if(ENVIRONMENTSTATUS == 'Ceshi') {
			$this->member_id = 27;
			$this->enterprise_code = 184334;
		}else{
			$this->member_id = 276;
			$this->enterprise_code = 181828;
		}
	}
	//生成订单号
	private function getOrderCode(){

		$orderCode = date('ymdHis').rand(1000,9999);
		return $orderCode;
	}

	/**
	 * 获取门店编号
	 * @param $store
	 * @return mixed
	 */
	private function getStoreCode($store){

		if(ENVIRONMENTSTATUS == 'Ceshi') {
			$storeCodeData = [
				"JQ-XD" => "",//局气（西单店）
				"JQ-FZ" => "",//局气(方庄店)
				"JQ-QM" => "184334004",//局气（前门店）
				"JQ-JD" => "184334003",//局气（金地店）
				"JQ-WDK" => "184334002",//局气（五道口）
				"JQ-GH" => "",//局气（光华路伯豪）
				"JQ-YT" => "184334005",//局气（悠唐店）
				"JQ-HT" => "",//局气（恒泰店）
				"JQ-AQH" => "",//局气（爱琴海店）
				"JQ-DYC" => "",//局气（大悦城店）
				"JQ-WJ" => "",//局气（望京店）
				"JQ-SA" => "",//局气（双安店）
				"JQ-APM" => "",//局气（王府井APM店）
				"JQ-JY" => "",//局气（金源店）
				"SSTT-XBM" => "",//四世同堂（西便门店）
				"SSTT-CGZ" => "",//四世同堂（车公庄店）
				"SSTT-SJ" => "",//四世同堂（双井店）
				"SSTT-WGC" => "",//四世同堂（魏公村店）
				"SSTT-ZZ" => "",//四世同堂（周庄店）
			];
		}else{
			$storeCodeData = [
				"JQ-XD" => "",//局气（西单店）
				"JQ-FZ" => "",//局气(方庄店)
				"JQ-QM" => "181828003",//局气（前门店）
				"JQ-JD" => "181828002",//局气（金地店）
				"JQ-WDK" => "181828001",//局气（五道口）
				"JQ-GH" => "",//局气（光华路伯豪）
				"JQ-YT" => "181828004",//局气（悠唐店）
				"JQ-HT" => "",//局气（恒泰店）
				"JQ-AQH" => "",//局气（爱琴海店）
				"JQ-DYC" => "",//局气（大悦城店）
				"JQ-WJ" => "",//局气（望京店）
				"JQ-SA" => "",//局气（双安店）
				"JQ-APM" => "",//局气（王府井APM店）
				"JQ-JY" => "",//局气（金源店）
				"SSTT-XBM" => "",//四世同堂（西便门店）
				"SSTT-CGZ" => "",//四世同堂（车公庄店）
				"SSTT-SJ" => "",//四世同堂（双井店）
				"SSTT-WGC" => "",//四世同堂（魏公村店）
				"SSTT-ZZ" => "",//四世同堂（周庄店）
			];
		}
		return $storeCodeData[$store];
	}
	/**
	 * 创建订单
	 */
	public function createOrder(){
		//接收参数
		$store_code = I('store_code');
		$mr_id = I('wo_id');
		$link_person = I('link_person');
		$link_phone = I('link_phone');
		$equipment_name = I('equipment_name');
		$brands_text = I('brands_text');
		$malfunction_text = I('malfunction_text');
		$image_data = I('image_data');
		$urgent_level = I('urgent_level');

		$request = I('');
		@file_put_contents("canxun_yimeike.log", "易每刻下单请求数据：".json_encode($request)."\t\n",FILE_APPEND);

		//验证参数
		if(empty($store_code) || empty($mr_id) || empty($link_person) || empty($link_phone) || empty($equipment_name)
			|| empty($malfunction_text) || empty($urgent_level)){
			echo json_encode(['status'=>0,'msg'=>"parameter error",'code'=>'1001']);
			exit;
		}
		if(empty($brands_text)){
			$brands_text = "未填写";
		}
		//获取门店编号
		$store_number = $this->getStoreCode($store_code);
		if(empty($store_number)){
			echo json_encode(['status'=>0,'msg'=>"the store does not exist",'code'=>'1002']);
			exit;
		}
		//根据企业编号获取企业信息
		$companyData = $this->WechatKaEnterpriseModel->getData(['code'=>$this->enterprise_code]);
		//根据门店编号获取门店信息
		$fileds = "store.name storename,store.code storecode,store.province,store.city,store.stores_address,ent.code companycode,ent.name companyname";
		$juqiData = $this->WechatKaStoresModel->getData(['store.code'=>$store_number,'store.enterprise_id'=>$companyData[0]['id']],$fileds);

		//创建订单
		$orderCode = $this->getOrderCode();
		$orderData = [
			"order_code" => $orderCode,
			"order_state" => 0,//0:派单中;1:已接单;2:待支付;3:已支付;4:已取消;5:已电话解决;6:已退款
			"order_type" => 1,//1设备维修，2门店消杀，3设备清洗
			"member_id" => $this->member_id,//用户ID
			"province" => $juqiData[0]['companycode'],
			"city" => $juqiData[0]['storecode'],
			"store_name" => $juqiData[0]['storename'],
			"detailed_address" => $juqiData[0]['stores_address'],
			"link_person" => $link_person,
			"link_phone" => $link_phone,
			"door_price" => 0,//上门费默认20
			"service_price" => 0,//服务费
			"money_total" => 0,
			"create_time" => date("Y-m-d H:i:s"),
			"update_time" => date("Y-m-d H:i:s"),
			"enterprise_name" => $juqiData[0]['companyname'],
			"is_ka" => 1
		];
		$orderData['equipment_id'] = 2;

		$orderId = $this->WechatOrderModel->add($orderData);

		//添加图片数据
		if(!empty($image_data)){
			foreach ($image_data as $k => $v) {
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
			"equipment_name" => $equipment_name,
			"brands_text" => $brands_text,
			"malfunction_text" => $malfunction_text,
			"mr_id" => $mr_id,
			"urgent_level" => $urgent_level,
			"create_time" => date("Y-m-d H:i:s"),
			"update_time" => date("Y-m-d H:i:s"),
		];
		$res = $this->WechatOrderItemModel->add($orderItemData);


		if($orderId && $res){
			//给客服端发消息提醒
			$enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
			$param = $enterpriseService->getCustomerSendMsg($orderId,1);//新工单提醒
			$enterpriseService->sendMessage($param);//新工单提醒

			echo json_encode(["status" => 1,"data" => ["order_code" => $orderCode],'code'=>'200']);
			exit;
		}else{
			echo json_encode(['status'=>0,'msg'=>"Creating an order failure",'code'=>'1003']);
			exit;
		}
	}

	/**
	 * 验收订单接口
	 */
	public function confirmeOrder(){
		//拼凑更新数据
		$order_code = I('order_code');
		$content = I('confirm_text');

		$request = I('');
		@file_put_contents("canxun_yimeike.log", "易每刻确认订单请求数据：".json_encode($request)."\t\n",FILE_APPEND);
		if(empty($order_code) || empty($content)){
			echo json_encode(['status'=>0,'msg'=>"parameter error",'code'=>'1001']);
			exit;
		}

		$order = $this->WechatOrderModel->where(['order_code'=>$order_code])->find();
		if(empty($order)){
			echo json_encode(['status'=>0,'msg'=>"order does not exist",'code'=>'1002']);
			exit;
		}
		$data['order_state'] = 3;
		$data['update_time'] = date("Y-m-d H:i:s");

		//更新订单
		$this->WechatOrderModel->where(['order_code'=>$order_code])->save($data);

		//评价数据
		$appData['member_id'] = $this->member_id;
		$appData['workers_id'] = $order['workers_id'];
		$appData['order_id'] = $order['id'];
		$appData['delivery_score'] = 5;
		$appData['content'] = $content;

		$this->WechatOrderAppraiseModel->editData($appData);
		//TODO 如果是领值订单，则通知领值系统
		$orderData = $this->WechatPublicOrderService->getOrderData($order['id']);
		if(!empty($orderData['mr_id'])){
			$this->WechatService->curlCancel($orderData['mr_id'],'archive');
		}
		echo json_encode(["status" => 1,"data" => ["order_code" => $order_code],'code'=>'200']);
		exit;
	}
}