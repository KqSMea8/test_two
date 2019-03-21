<?php
/**
 * 微信订阅号使用
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author HQ
 */

namespace Wechat\Controller;
use Think\Controller;
use Think\Log;
use Wechat\Service\WechatService;

class SubscribeController extends Controller{

    private $appId;
    private $appSecret;
    private $token;
    private $EncodingAESKey;

    function __construct()
    {
        $this->appId = 'wxcd9fa757a96a6e96';
        $this->appSecret = '0c8eed09a6f15350fb013608dceadfc2';
        $this->token = 'weixin';
        $this->EncodingAESKey = 'v7CxeiwVKOPqYjnfJIN7ePtNgttvkfHkmhDsZBZk901';
    }

    //获取微信验证信息
    public function verifyWx(){

        Log::write('用户已进入1 | ');

        if(empty($_GET["echostr"])){
            $wechatService = new WechatService();

            //解析
            $wechatService->parseMsg();

            //处理微信事件(关注)
            if($wechatService->getRev('MsgType') == 'event'){
                $openId = $wechatService -> getRev('FromUserName');
                Log::write('用户openid为 | '.$openId);
            }

            Log::write('用户已进入2 | ');

        }else {

            $signature = I("signature");
            $timestamp = I("timestamp");
            $nonce = I("nonce");
            $echostr = I("echostr");

            $temp = [
                $this->token,
                $timestamp,
                $nonce
            ];

            sort($temp, SORT_STRING);

            $tempNew = implode($temp);
            $tempNew = sha1($tempNew);
            if ($tempNew == $signature) {
                echo $echostr;
            }
        }
    }

    //给用户发送群发消息
    public function setUserMsg(){

        set_time_limit(0);
        $wechatService = new WechatService();

        $wXUserData = $wechatService->getWXUserList();
//        oEfCAwKBCg0f2vaiET8ftB6bInLs
//        $openIdData = json_decode($wXUserData,true)['data']['openid'];

//        foreach($openIdData as $val){
//            $wechatService->sendDYCustomServiceNewsText($val,'华为公司人力资源管理纲要2.0总纲——企业经营者必读！','http://opensns.lz517.cn/index.php?s=/Wechat/Index/sharePaper','http://opensns.lz517.cn/Public/images/share2.png','随着我国餐饮业的发展壮大和竞争加剧，人才竞争已成为餐饮企业生存的重要保证，而我国餐饮业中的人力资源管理存在的缺失和不足...');
//        }

        $returnData = $wechatService->sendDYCustomServiceNewsText('oEfCAwKBCg0f2vaiET8ftB6bInLs','华为公司人力资源管理纲要2.0总纲——企业经营者必读！','http://opensns.lz517.cn/index.php?s=/Wechat/Index/sharePaper','http://opensns.lz517.cn/Public/images/share2.png','随着我国餐饮业的发展壮大和竞争加剧，人才竞争已成为餐饮企业生存的重要保证，而我国餐饮业中的人力资源管理存在的缺失和不足...');
        print_r($returnData);die;

    }

}