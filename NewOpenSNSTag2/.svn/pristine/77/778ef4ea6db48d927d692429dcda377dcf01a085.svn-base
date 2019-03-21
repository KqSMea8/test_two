<?php
namespace Pay\Service;
/**
 * @author  tianxh.
 * email: tianxh@jpgk.com.cn
 * QQ:2018997757 * Date: 2016/11/8
 * Time: 14:32
 */
use Pay\Conf\Conf;
use Pay\Library\alipay\Alipay;
use Pay\Library\WxpayAPI\WxNativePay;
use Pay\Model\PayRecordModel;
use Pay\Model\PayServerModel;

/**
 * Class PayForService
 *
 *
 */
class PayForService
{
    //购买服务
    public function buyService($userId,$app,$dataId,$startTime,$endTime,$payType,$money,$subject = "购买餐讯网服务",$returnUrl = ""){
        //创建购买服务记录数据
        $payServerModel = new PayServerModel();
        $serviceId = $payServerModel->add(["uid" => $userId,"app" => $app,"row_id" => $dataId,"start_time" => strtotime($startTime),"status" => Conf::PAY_SERVICE_STATUS_NO,"end_time" => strtotime($endTime),"create_time" => time()]);

        //生成交易号
        $tradeCode = "X".$payType.date("YmdHis").rand(1000,9999);

        //定义请求支付的数据的变量
        $payRequestData = null;

        //定义请求的url变量
        $payRequestUrl = "";
        $notifyUrl = "";
        if($payType == Conf::PAY_TYPE_WEIXIN){

            $notifyUrl = "http://".$_SERVER["HTTP_HOST"]."/index.php/Pay/Notify/wxpayNotify";
            //echo json_encode([$tradeCode,$subject,$money,$serviceId,$notifyUrl,$payRequestData]);die();
            $payRequestUrl = WxNativePay::buildPayRequestUrl($tradeCode,$subject,$money*100,$serviceId,$notifyUrl,$payRequestData);

        }else if($payType == Conf::PAY_TYPE_ALIPAY){
            $notifyUrl = "http://".$_SERVER["HTTP_HOST"]."/index.php/Pay/Notify/alipayNotify";
            $payRequestUrl = Alipay::buildPayRequestHtml($tradeCode,$subject,$money,$subject,$payRequestData,$notifyUrl,$returnUrl);
        }

        //创建支付记录数据
        $payRecordModel = new PayRecordModel();
        $payRecordModel->add(["status_pay" => Conf::PAY_RECORD_STATUS_START,"service_id" => $serviceId,"pay_type" => $payType,"money" => $money,"trade_code" => $tradeCode,"data_request" => json_encode($payRequestData),"time_out" => time() + 20*60,"time_create" => time()]);

        return $payRequestUrl;
    }

    public function notify($tradeNo,$recordData = null){

        //根据tradeNo查询交易记录
        $payRecordModel = new PayRecordModel();
        $payRecord = $payRecordModel->where(["trade_code" => $tradeNo])->find();

        if(empty($payRecord)){

            return true;
        }

        //记录回调信息
        $payRecord["time_notify"] = time();
        $payRecord["data_notify"] = empty($recordData)?json_encode($_POST):json_encode($recordData);
        $payRecord["status_pay"] = Conf::PAY_RECORD_STATUS_SUCCESS;
        $payRecordModel->save($payRecord);

        //修改购买服务记录的状态
        $payServerModel = new PayServerModel();
        $payServerData = [
            "id" => $payRecord["service_id"],
            "status" => Conf::PAY_SERVICE_STATUS_YES
        ];
        $payServerModel->save($payServerData);
    }
}