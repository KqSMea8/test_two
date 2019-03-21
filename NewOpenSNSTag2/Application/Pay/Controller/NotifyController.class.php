<?php
namespace Pay\Controller;
/**
 * @author  tianxh.
 * email: tianxh@jpgk.com.cn
 * QQ:2018997757 * Date: 2016/11/9
 * Time: 13:46
 */
use Pay\Library\alipay\Alipay;
use Pay\Library\WxpayAPI\WxNativePay;
use Pay\Service\PayForService;
use Think\Controller;

/**
 * Class TestController
 *
 *
 */
class NotifyController extends Controller
{
    public function alipayNotify(){

        try{
            writeFileLog("opensns_alipay_notify",$_POST);
            $payService = new PayForService();

            Alipay::notify($payService);

        }catch(\Exception $e){

            throw $e;
        }
    }

    //支付宝支付测试测试
    public function wxpayNotify(){

        try{
            writeFileLog("opensns_wx_notify",$_REQUEST);
            $wxNotify = new WxNativePay();
            $wxNotify->notify(new PayForService());
        }catch(\Exception $e){

            throw $e;
        }
    }
}