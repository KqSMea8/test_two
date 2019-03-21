<?php
namespace Pay\Library\WxpayAPI;
/**
 * @author  tianxh.
 * email: tianxh@jpgk.com.cn
 * QQ:2018997757 * Date: 2016/11/16
 * Time: 16:26
 */

use Pay\Service\PayForService;

require_once __DIR__ . "/lib/instance/WxPay.NativePay.php";
require_once __DIR__ . "/lib/WxPay.Notify.php";
require_once __DIR__."/lib/phpqrcode/phpqrcode.php";

/**
 * Class WxNativePay
 *
 *
 */
class WxNativePay extends \WxPayNotify
{

    private $data = null;
    private $outTradeNo = "";

    public static function buildPayRequestUrl($tradeNo,$subject,$totalFee,$productId,$notifyUrl,&$requestData = null){

        $input = new \WxPayUnifiedOrder();
        $input->SetBody($subject);
        $input->SetAttach("service:".$subject);
        $input->SetOut_trade_no($tradeNo);
        $input->SetTotal_fee($totalFee);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", strtotime("+2 minutes")));
        //$input->SetGoods_tag("test");
        $input->SetNotify_url($notifyUrl);
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($productId);

        $notify = new \NativePay();
        $result = $notify->GetPayUrl($input);

        $payUrl = $result["code_url"];

        $requestData = $input->GetValues();

        return $payUrl;
    }

    public function notify(PayForService $payForService){

        parent::Handle(true);
        if(parent::GetReturn_code() == "SUCCESS"){

            if(!empty($this->outTradeNo)){

                $payForService->notify($this->outTradeNo,$this->data);
            }
        }
    }

    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($input);
        if(array_key_exists("return_code", $result)
           && array_key_exists("result_code", $result)
           && $result["return_code"] == "SUCCESS"
           && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }
        $this->data = $data;
        //系统业务逻辑处理
        $this->outTradeNo = $data["out_trade_no"];

        return true;
    }

    public static function getPayUrl(){

        return "http://".$_SERVER["HTTP_HOST"].U("Pay/WxPay/payUrl")."&payUrl=";
    }
}