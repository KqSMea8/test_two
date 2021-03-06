<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Wechat\Controller;
use Enterprise\Service\EnterpriseService;
use Pay\Service\PayForService;
use Think\Controller;
use Wechat\Model\WechatOrderModel;
use Pay\Library\WxpayAPI\WxNativePay;
use Think\Log;
use Wechat\Model\WechatPayModel;
use Wechat\Service\WechatService;
use Wechat\Model\WechatYearServiceTimeModel;

class NotifyController extends Controller
{

    private $appId;
    private $appSecret;
    private $token;
    private $WechatOrderModel;

    function _initialize()
    {
        $this->appId = WECHAT_APPID;
        $this->appSecret = WECHAT_APPSECRET;
        $this->token = WECHAT_TOKEN;

        $this->WechatOrderModel = new WechatOrderModel();
    }

    /**
     * 餐讯网微信公众号
     */
    public function wxPayNotify()
    {
        //获取微信回调的参数
        $xml = file_get_contents("php://input");
        $data = xmlStringToArray($xml);
        $data = json_decode(json_encode($data),true);
//        $orderCodeOld = $data['xml']['out_trade_no']['@cdata'];
//        $transactionId = $data['xml']['transaction_id']['@cdata'];
//        $returnCode = $data['xml']['return_code']['@cdata'];
//        $resultCode = $data['xml']['result_code']['@cdata'];
        $orderCodeOld = $data['out_trade_no'];
        $transactionId = $data['transaction_id'];
        $returnCode = $data['return_code'];
        $resultCode = $data['result_code'];

        Log::write("支付回调信息json：\r\n".json_encode($data));

        $orderCode = $orderCodeOld;
        //切割补差价的订单code
        $orderCodeCj = explode('CJ',$orderCodeOld);
        if($orderCodeCj[1]){
            $orderCode = $orderCodeCj[1];
        };

        //切割扫码支付的订单
        $orderCodeSm = explode('SM',$orderCodeOld);
        if($orderCodeSm[1]){
            $orderCode = $orderCodeSm[1];
        };

        //切割修改价格的订单 此处要修改的话 要注意 订单码 有可能 扫码支付与师傅修改价格支付的订单 会重合 订单既是扫码单也是修改价格的订单
        $orderCodeGG = explode('GG',$orderCodeOld);
        if($orderCodeGG[1]){
            $orderCode = $orderCodeGG[1];
        }

        //业务逻辑处理
        try{

            if($resultCode == 'SUCCESS' && $returnCode == 'SUCCESS'){

                //查询订单号对应的订单信息
                $orderModel = new WechatOrderModel();
                $orderData = $orderModel->where(['order_code'=>$orderCode])->find();
                if(empty($orderData)){
                    Log::write("回调订单出错 信息为空：".json_encode($orderData,true));
                    throw new \Exception('订单信息为空');
                }

                //添加支付记录
                $payModel = new WechatPayModel();
                $pay = $payModel->where(['trade_no'=>$orderCodeOld])->find();
                if(!empty($pay)){
                    $payData = [
                        'trade_no'=>$orderCodeOld,
                        'status_pay'=>1,//已支付
                        'third_trade_no'=>$transactionId,//第三方订单号
                        'notify_time'=>time()//第三方订单号
                    ];

                    $payModel->where(['trade_no'=>$orderCodeOld])->save($payData);
                }

                //设置订单的支付状态为支付成功 todo 目前是扫码支付的订单修改状态 服务号的支付放在wxPaySuccess方法 可全局搜索
                if($orderData['order_state'] == PAY_STATUS_NO_PAY && $orderCodeSm[1]){
                    $orderSaveStatus = '';
                    //判断订单分类 TODO order_type 1：设备维修，2：门店消杀，3：设备清洗
                    if($orderData['order_type'] == 1){
                        $codescrect = COMPANY_MAINTAIN_APPSECRET;
                        $orderSaveStatus = PAY_STATUS_PAY_COMPLETION;
                    }elseif($orderData['order_type'] == 2){

//                    //判断如果支付为差价的话 订单状态变更为已完成 反之派单中 TODO 此处放在了服务号支付完成处
//                    if($orderCodeCj[1]){
//                        $orderSaveStatus = PAY_STATUS_PAY_COMPLETION;
//                    }else{
//                        $orderSaveStatus = PAY_STATUS_SENDORDERING;
//                    }
                        $codescrect = COMPANY_CLEANKILL_APPSECRET;
                        //1.消杀判断是否为年服务订单
                        if($orderData['is_year'] == 1){
                            $orderSaveStatus = PAY_STATUS_PAY_INSERVICE;

                            //判断如果是续签订单 则将续签前的单次订单变更为年订单的第一个子订单
                            if($orderData['renew_order_id'] && $orderData['year_service_id']){
                                (new WechatYearServiceTimeModel())->where(['year_service_id'=>$orderData['year_service_id'],'service_num'=>1])->save(['order_id'=>$orderData['renew_order_id']]);
                                (new WechatOrderModel())->where(['id'=>$orderData['renew_order_id']])->save(['year_service_id'=>$orderData['year_service_id'],'is_main'=>0,'is_year'=>1]);
                            }
                        }else{
                            $orderSaveStatus = PAY_STATUS_PAY_SUCCESS;
                        }
                    }elseif($orderData['order_type'] == 3){
                        $codescrect = COMPANY_CLEANING_APPSECRET;
                        $orderSaveStatus = PAY_STATUS_PAY_COMPLETION;
                    }
                    $orderInfoData['id'] = $orderData['id'];
                    $orderInfoData['order_state'] = $orderSaveStatus;
                    $oResult = $orderModel->editData($orderInfoData);
                    if($oResult === false){
                        Log::write("回调修改订单状态出错：".json_encode($orderInfoData,true));
                        throw new \Exception('回调修改订单状态失败');
                    }

                    //发送微信模板消息
                    $wechatService = new WechatService();
                    //支付完成给师傅发消息提醒
                    if($orderData['order_type'] == 1 || $orderData['order_type'] == 3){
                        $masterService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,$codescrect);
                        $param = $masterService->getMasterSendMsg($orderData['id'],7);//新工单提醒
                        $masterService->sendMessage($param);//新工单提醒
                    }
                    //消杀订单支付完成给客服和主管发消息提醒
                    if($orderData['order_type'] == 2){

                        //给客服发送取消订单提醒
                        $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_CUSTOMER_APPSECRET);
                        $param = $enterpriseService->getCustomerSendMsg($orderData['id'],11);//订单支付提醒
                        $enterpriseService->sendMessage($param);//订单支付提醒
                        //给分配主管发取消订单消息提醒
                        $distributeService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
                        $param = $distributeService->getDistributeSendMsg($orderData['id'],6);//订单支付提醒
                        $distributeService->sendMessage($param);//订单支付提醒
                    }

                    //支付完成后给用户发送消息提醒
                    //1：设备维修，2：门店消杀，3：设备清洗
                    if($orderData['order_type'] == 1 || $orderData['order_type'] == 2 || $orderData['order_type'] == 3){

                        //判断消杀的订单支付前后顺序 $orderCodeCj不为空 则表示第二次支付
                        if($orderData['order_type'] == 2 && !empty($orderCodeCj[1])){
                           $wechatService->sendOrderPaySuccessMsg($orderData['id'],'storeCleanKillCj');
                        }elseif($orderData['order_type'] == 2 && empty($orderCodeCj[1])){
                            $wechatService->sendOrderPaySuccessMsg($orderData['id'],'storeCleanKill');
                        }

                        //设备维修
                        if($orderData['order_type'] == 1){
                            $wechatService->sendOrderPaySuccessMsg($orderData['id'],'storeMaintain');
                        }

                        //设备清洗
                        if($orderData['order_type'] == 3){
                            $wechatService->sendOrderPaySuccessMsg($orderData['id'],'storeCleaning');
                        }
                    }


                }
            }else{
                Log::write("回调失败信息：\r\n".$xml);
                Log::write("回调resultCode||returnCode：".$resultCode.'||'.$returnCode);
            }

//            //正确返回微信数据;
//            $notify->SetReturn_code("SUCCESS");
//            $notify->SetReturn_msg('OK');
//            $notify-> SetSign();
//            \WxpayApi::replyNotify($notify->ToXml());
        }catch (\Exception $e){
//            Log::write("错误信息：".$e->getMessage()."\r\n".$xml);
//            $notify->SetReturn_code("FAIL");
//            $notify->SetReturn_msg('OK');
//            $notify-> SetSign();
//            \WxpayApi::replyNotify($notify->ToXml());
            return;
        }
    }
}