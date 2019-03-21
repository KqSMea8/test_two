<?php
namespace app\wechat\service;
use app\cabinet\model\Cab;
use app\cabinet\model\CabDoor;
use app\common\Lib\WinnerLookAPI\WinnerLookService;
use app\common\model\message\Message;
use app\common\service\Redis;
use app\common\service\Sms;
use app\member\model\Order;
use app\member\model\OrderItem;
use app\user\model\UserCab;
use think\Db;
use think\Log;


/**
 * 消息队列回调
 * @package app\wechat\service
 */
class MqsCallback
{
	/**
	 * 取消订单消息回调
	 * @param int $orderId 订单id
	 */
	public function cancelOrder($orderId){

        $order = Order::get($orderId);
        if($order->status_pay == PAY_STATUS_NO_PAY){
            //order表status_pay
            $cabId = $order->cab_id;
            $order->save(['status_pay'=>PAY_STATUS_CANCELED],['order_id'=>$orderId]);

            //redis释放柜门
            $redis = new Redis();
            $orderItem = (new OrderItem())->where(['order_id'=>$orderId])->select();
            $doorIdArr = [];
            foreach ($orderItem as $item){
                $doorIdArr[] = $item->door_id;
                $key = "jpgk:znqcg:reserve:" . $cabId . ":" . $item->door_id;
                $redis->del($key);
            }
        }
	}

    /**
     * 即将超时
     * @param $orderId
     */
	public function sendAlmostOvertimeMsg($orderId){

        $orderData = $this->getOrderData($orderId);
        if(!empty($orderData) && $orderData['status_pay'] == Order::PAY_STATUS_PAY_SUCCESS && $orderData['status'] == Order::ORDER_STATUS_ALREADY_IN){
            //支付成功且还是入柜状态（未取）

            //机柜类型
            $cabId = $orderData['cab_id'];
            $cab = Cab::get($cabId);
            $type = $cab->type;

            //给配送员发送消息
            $senderService = new WechatService(\WxPaySenderConfig::APPID,\WxPaySenderConfig::APPSECRET);
            $sOpenId = $orderData['s_open_id'];
            $sTitle = '包裹即将超时通知';
            if($type == 1){
                $sContent = "您投递在".$orderData['cab_code']."柜的物品即将超时啦，请及时提醒顾客\r\n";
                $sContent .= "收货人手机号：".$orderData['t_phone']."\r\n";
                $sContent .= "寄件地址：".$orderData['address']."\r\n";
                $sContent .= "投放时间：".$orderData['pay_time']."\r\n";
                $sContent .= "超时时间：".date("Y-m-d H:i:s",strtotime("+".Order::OVERTIME_MINUTES." minute",strtotime($orderData['pay_time'])))."\r\n";
                $sContent .= "取餐码：".$orderData['take_code']."\r\n";
                $sContent .= "包裹：柜门".$orderData['doorLabels']."\r\n";
                $sContent .= "数量：".$orderData['count'];
            }else{
                $sContent = "您的物品即将超时啦，请及时提醒顾客\r\n";
                $sContent .= "收货人手机号：".$orderData['t_phone']."\r\n";
                $sContent .= "寄件地址：".$orderData['address']."\r\n";
                $sContent .= "投放时间：".$orderData['pay_time']."\r\n";
                $sContent .= "超时时间：".date("Y-m-d H:i:s",strtotime("+".Order::OVERTIME_MINUTES." minute",strtotime($orderData['pay_time'])))."\r\n";
                $sContent .= "数量：".$orderData['count'];
            }
            $sUrl = 'http://sender.qucanxia.com/wechat/Sender/mySend';
            $senderService->sendCustomServiceNewsText($sOpenId,$sTitle,$sUrl,'',$sContent);

            //添加message信息
            (new Message())->sendMessage("您有一个订单即将超时，请及时处理",Message::MESSAGE_TYPE_ALMOST_OVERTIME_TO_SENDER,$orderData['mem_from'],$orderData['mem_from'],'',$orderId);

            if(!empty($orderData['t_open_id'])){
                //给取货用户发消息
                $takerService = new WechatService(\WxPayTakerConfig::APPID,\WxPayTakerConfig::APPSECRET);
                $tOpenId = $orderData['t_open_id'];
                $tTitle = '包裹即将超时通知';
                if($type == 1){
                    $tContent = "您在".$orderData['cab_code']."柜的物品即将超时啦，请及时取货\r\n";
                    $tContent .= "包裹：柜门".$orderData['doorLabels']."\r\n";
                    $tContent .= "数量：".$orderData['count']."\r\n";
                    $tContent .= "快递员手机号：".$orderData['s_phone']."\r\n";
                    $tContent .= "取货地址：".$orderData['address']."\r\n";
                }else{
                    $tContent = "您的物品即将超时啦，请及时取货\r\n";
                    $tContent .= "数量：".$orderData['count']."\r\n";
                    $tContent .= "快递员手机号：".$orderData['s_phone']."\r\n";
                    $tContent .= "取货地址：".$orderData['address']."\r\n";
                }
                $tUrl = config('host_url').'wechat/Taker/myDoors';
                $takerService->sendCustomServiceNewsText($tOpenId,$tTitle,$tUrl,'',$tContent);
            }

            //添加message信息
            (new Message())->sendMessage("您有一个订单即将超时，请及时处理",Message::MESSAGE_TYPE_ALMOST_OVERTIME_TO_TAKER,$orderData['mem_to'],$orderData['mem_to'],'',$orderId);

            //给区域管理员发消息
//            $cabId = $orderData['cab_id'];
//            $userCab = UserCab::all(['cab_id'=>$cabId]);
//            if(!empty($userCab)){
//                $userCab = collection($userCab)->toArray();
//                foreach ($userCab as $v){
//                    (new Message())->sendMessage("您有一个订单即将超时，请及时处理",Message::MESSAGE_TYPE_TO_MANAGER,$v['user_id'],'','',$orderId);
//                }
//            }else{
                (new Message())->sendMessage("您有一个订单即将超时，请及时处理",Message::MESSAGE_TYPE_TO_MANAGER,'','','',$orderId);
//            }
        }
    }

    /**
     * 超时
     * @param $orderId
     */
    public function sendOvertimeMsg($orderId){

        $orderData = $this->getOrderData($orderId);
        if(!empty($orderData) && $orderData['status_pay'] == Order::PAY_STATUS_PAY_SUCCESS && $orderData['status'] == Order::ORDER_STATUS_ALREADY_IN){
            //支付成功且还是入柜状态（未取）

            //机柜类型
            $cabId = $orderData['cab_id'];
            $cab = Cab::get($cabId);
            $type = $cab->type;

            //给配送员发送消息
            $senderService = new WechatService(\WxPaySenderConfig::APPID,\WxPaySenderConfig::APPSECRET);
            $sOpenId = $orderData['s_open_id'];
            $sTitle = '包裹超时通知';
            if($type == 1){
                $sContent = "您投递在".$orderData['cab_code']."柜的物品已经超时，请及时提醒顾客\r\n";
                $sContent .= "收货人手机号：".$orderData['t_phone']."\r\n";
                $sContent .= "寄件地址：".$orderData['address']."\r\n";
                $sContent .= "投放时间：".$orderData['pay_time']."\r\n";
                $sContent .= "超时时间：".date("Y-m-d H:i:s",strtotime("+".Order::OVERTIME_MINUTES." minute",strtotime($orderData['pay_time'])))."\r\n";
                $sContent .= "取餐码：".$orderData['take_code']."\r\n";
                $sContent .= "包裹：柜门".$orderData['doorLabels']."\r\n";
                $sContent .= "数量：".$orderData['count'];
            }else{
                $sContent = "您的物品已经超时，请及时提醒顾客\r\n";
                $sContent .= "收货人手机号：".$orderData['t_phone']."\r\n";
                $sContent .= "寄件地址：".$orderData['address']."\r\n";
                $sContent .= "投放时间：".$orderData['pay_time']."\r\n";
                $sContent .= "超时时间：".date("Y-m-d H:i:s",strtotime("+".Order::OVERTIME_MINUTES." minute",strtotime($orderData['pay_time'])))."\r\n";
                $sContent .= "数量：".$orderData['count'];
            }
            $sUrl = 'http://sender.qucanxia.com/wechat/Sender/mySend';
            $senderService->sendCustomServiceNewsText($sOpenId,$sTitle,$sUrl,'',$sContent);

            //添加message信息
            (new Message())->sendMessage("您有一个订单已超时，请及时处理",Message::MESSAGE_TYPE_OVERTIME_TO_SENDER,$orderData['mem_from'],$orderData['mem_from'],'',$orderId);

            if(!empty($orderData['t_open_id'])){
                //给取货用户发消息
                $takerService = new WechatService(\WxPayTakerConfig::APPID,\WxPayTakerConfig::APPSECRET);
                $tOpenId = $orderData['t_open_id'];
                $tTitle = '包裹超时通知';
                if($type == 1){
                    $tContent = "您在".$orderData['cab_code']."柜的物品已经超时，请及时取货\r\n";
                    $tContent .= "包裹：柜门".$orderData['doorLabels']."\r\n";
                    $tContent .= "数量：".$orderData['count']."\r\n";
                    $tContent .= "快递员手机号：".$orderData['s_phone']."\r\n";
                    $tContent .= "取货地址：".$orderData['address']."\r\n";
                }else{
                    $tContent = "您的物品已经超时，请及时取货\r\n";
                    $tContent .= "数量：".$orderData['count']."\r\n";
                    $tContent .= "快递员手机号：".$orderData['s_phone']."\r\n";
                    $tContent .= "取货地址：".$orderData['address']."\r\n";
                }
                $tUrl = config('host_url').'wechat/Taker/myDoors';
                $takerService->sendCustomServiceNewsText($tOpenId,$tTitle,$tUrl,'',$tContent);
            }

            //添加message信息
            (new Message())->sendMessage("您有一个订单超时，请及时处理",Message::MESSAGE_TYPE_OVERTIME_TO_TAKER,$orderData['mem_to'],$orderData['mem_to'],'',$orderId);

            //释放柜门
            $doorIds = $orderData['doorIds'];
            (new CabDoor())->save(['use_status'=>DOOR_USE_STATUS_VALID],['door_id'=>['IN',$doorIds]]);
            Log::write("订单".$orderId."超时，释放柜门".$doorIds."，时间：".date("Y-m-d H:i:s"));

            //给区域管理员发消息
//            $cabId = $orderData['cab_id'];
//            $userCab = UserCab::all(['cab_id'=>$cabId]);
//            if(!empty($userCab)){
//                $userCab = collection($userCab)->toArray();
//                foreach ($userCab as $v){
//                    (new Message())->sendMessage("您有一个订单超时，请及时处理",Message::MESSAGE_TYPE_TO_MANAGER,$v['user_id'],'','',$orderId);
//                }
//            }else{
                (new Message())->sendMessage("您有一个订单超时，请及时处理",Message::MESSAGE_TYPE_TO_MANAGER,'','','',$orderId);
//            }
        }
    }

    public function sendWarningMsg($orderId){

        $orderData = $this->getOrderData($orderId);
        if(!empty($orderData) && $orderData['status_pay'] == Order::PAY_STATUS_PAY_SUCCESS && $orderData['status'] == Order::ORDER_STATUS_ALREADY_IN) {
            //支付成功且还是入柜状态（未取）

            //机柜类型
            $cabId = $orderData['cab_id'];
            $cab = Cab::get($cabId);
            $type = $cab->type;

            $winnerLook = new WinnerLookService();
            $address = $orderData['address'];
            $cabCode = $orderData['cab_code'];
            $senderPhone = $orderData['s_phone'];
            $takeCode = $orderData['take_code'];
            $doorLabels = $orderData['doorLabels'];
            $takerPhone = $orderData['t_phone'];

            //给客户发短信
            if($type == 1){
                $msg = '您的外卖已放入'.$address.$cabCode.'柜，现已超时15分钟，请及时取餐。送餐员电话'.$senderPhone.'，取餐码为'.$takeCode.'柜门号为'.$doorLabels;
            }else{
                $msg = '您的外卖已放入'.$address.'，现已超时15分钟，请及时取餐。送餐员电话'.$senderPhone.'，取餐码为空柜门号为空';
            }
            $winnerLook->sendSms($takerPhone,$msg,Sms::SMS_TYPE_MEM_TAKE);

            //给客户打电话
            if($type == 1){
                $wResult = $winnerLook->notify($takerPhone,$address,$cabCode,$senderPhone,$takeCode,$doorLabels,400193);
            }else{
                $wResult = $winnerLook->notify($takerPhone,$address,'附近',$senderPhone,'附近','附近',400193);
            }
            Log::write('取货人语音通知结果：'.json_encode($wResult));
            if($wResult['result'] != '000000'){
                Log::write("15分钟提醒，语音通知报错，错误码：".$wResult['result']."，错误信息：".$wResult['message']);
            }
        }
    }

    public function getOrderData($orderId){

        $sql = "SELECT
                    o.*, c.cab_code,
                    c.address,
                    taker.phone t_phone,
                    sender.phone s_phone,
                    taker.open_id t_open_id,
                    sender.open_id s_open_id,
                    GROUP_CONCAT(cd.door_label) doorLabels,
                    GROUP_CONCAT(cd.door_id) doorIds,
                    COUNT(oi.item_id) count
                FROM
                    `order` o
                JOIN order_item oi ON o.order_id = oi.order_id
                JOIN cab c ON c.cab_id = o.cab_id
                JOIN cab_door cd ON cd.door_id = oi.door_id
                JOIN member sender ON sender.mem_id = o.mem_from
                JOIN member taker ON taker.mem_id = o.mem_to
                WHERE o.order_id = {$orderId}
                GROUP BY
                    o.order_id";

        $data = Db::query($sql);

        Log::write("查到的订单数据为：".json_encode(['order_id'=>$orderId,'data'=>$data]));

        $data = empty($data)?[]:$data[0];

        return $data;
    }
}