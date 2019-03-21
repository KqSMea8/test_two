<?php
namespace Pay\Controller;
/**
 * @author  tianxh.
 * email: tianxh@jpgk.com.cn
 * QQ:2018997757 * Date: 2016/11/9
 * Time: 13:46
 */
use Pay\Conf\Conf;
use Pay\Library\WxpayAPI\WxNativePay;
use Think\Controller;

/**
 * Class TestController
 *
 *
 */
class WxPayController extends Controller
{

    public function payHtml(){

        $this->assign("payUrl",WxNativePay::getPayUrl().$_GET["payUrl"]);

        $this->display("payHtml");
    }

    public function payUrl(){

        $wxNativePay = new WxNativePay();

        $url = urldecode($_GET["payUrl"]);
        $qrCode = new \QRcode();
        $img = $qrCode::png($url);
    }
}