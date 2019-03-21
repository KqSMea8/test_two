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
class TestController extends Controller
{
    public function testWxPay(){

        try{

            $payRequestData = null;
            $payUrl = WxNativePay::buildPayRequestUrl("test101614434560434034","购买餐讯网资讯服务",1,"1232143","http://www.baidu.com",$payRequestData);

            \QRcode::png($payUrl);

        }catch(\Exception $e){

            var_dump($e);die();
        }
    }

    //支付宝支付测试测试
    public function testAliPay(){
echo json_encode($_SERVER);die();
        $payRequestData = null;
        $html_text = Alipay::buildPayRequestHtml("test101614434345604034","test商品312343",0.01,"test商品31223",$payRequestData,"http://www.baidu.com","http://www.baidu.com");

        header("Content-type:text/html;charset=utf-8");
        echo "<!--".$html_text."-->";
    }
}