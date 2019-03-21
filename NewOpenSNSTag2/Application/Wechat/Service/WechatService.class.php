<?php
namespace Wechat\Service;
use Admin\Model\PictureModel;
use Enterprise\Service\EnterpriseService;
use think\Cache;
use think\Exception;
use think\Log;
use Wechat\Model\WechatFacilitatorModel;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderItemModel;
use Wechat\Model\WechatOrderModel;
use Wechat\Model\WechatPayModel;
use Wechat\Model\WechatShareArticleModel;
use Wechat\Model\WechatYearServiceTimeModel;

require_once APP_PATH ."/Pay/Library/WxpayAPI/lib/WxPay.Config.php";
require_once APP_PATH ."Pay/Library/WxpayAPI/lib/instance/WxPay.NativePay.php";
require_once APP_PATH ."Pay/Library/WxpayAPI/lib/WxPay.Notify.php";
require_once APP_PATH ."Pay/Library/WxpayAPI/lib/phpqrcode/phpqrcode.php";
//require_once APP_PATH ."/common/Lib/WeiPay/SDK/lib/WxPayTaker.Config.php";
//require_once APP_PATH ."/common/Lib/WeiPay/SDK/lib/WxPayTaker.Config.php";

class WechatService{

    private $_accessToken = null;
    private $_jsapiTicket = null;

    private $appId = "";
    private $appSecrect = "";

    const OAUTH2_URL = "https://open.weixin.qq.com/connect/oauth2/authorize?";
    const GET_ACCESS_TOKEN_URL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&";
    const CREATE_MENU_URL = "https://api.weixin.qq.com/cgi-bin/menu/create?";
    const DELETE_MENU_URL = "https://api.weixin.qq.com/cgi-bin/menu/delete?";
    const CREATE_QRCODE_URL = "https://api.weixin.qq.com/cgi-bin/qrcode/create?";
    const SHOW_QRCODE_URL = "https://mp.weixin.qq.com/cgi-bin/showqrcode?";
    const GET_SNS_USER_INFO_URL = "https://api.weixin.qq.com/sns/userinfo?";
    const SEND_TEMPLATE_URL = "https://api.weixin.qq.com/cgi-bin/message/template/send?";
    const GET_USER_INFO_URL = "https://api.weixin.qq.com/cgi-bin/user/info?";
    const GET_SNS_ACCESS_TOKEN_URL = "https://api.weixin.qq.com/sns/oauth2/access_token?";
    const GET_JS_API_TICKET_URL = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?";
    const GET_CODE = "https://open.weixin.qq.com/connect/oauth2/authorize?";
    const SEND_KEFU_MSG = "https://api.weixin.qq.com/cgi-bin/message/custom/send?";
    const GET_MEDIA_URL = "https://file.api.weixin.qq.com/cgi-bin/media/get?";
    const GROUPS_CREATE_URL = "https://api.weixin.qq.com/cgi-bin/groups/create?";
    const GROUPS_MEMBERS_UPDATE_URL = "https://api.weixin.qq.com/cgi-bin/groups/members/update?";
    const MENU_ADDCONDITIONAL_URL = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?";
    const BATCH_MEMBERS_AND_TAGS = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?";
    const GET_USER_LIST_URL = "https://api.weixin.qq.com/cgi-bin/user/get?";



    public function __construct($appId = WECHAT_APPID,$appSecrect = WECHAT_APPSECRET)
    {
        $this->appId = $appId;
        $this->appSecrect = $appSecrect;
    }

    /**
     * 获取jsapi签名
     *
     * @return multitype:string NULL number unknown
     */
    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = [
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        ];

        return $signPackage;
    }

    /**
     * 获取jsapi_ticket
     * jsapi_ticket是公众号用于调用微信JS接口的临时票据
     * 开发者必须在自己的服务全局缓存jsapi_ticket
     */
    public function getJsApiTicket()
    {
        $accessToken = $this->getAccessToken();
        if (is_null($this->_jsapiTicket)) {
//            $this->_jsapiTicket = S('wx_jsapiTicket');
            if (!$this->_jsapiTicket) {
                $result = $this->httpGet(self::GET_JS_API_TICKET_URL . 'access_token=' . $accessToken . '&type=jsapi');
                $json = $this->decodeJson($result);
                if (array_key_exists('ticket', $json)) {
                    $this->_jsapiTicket = $json ['ticket'];
                    S('wx_jsapiTicket', $json ['ticket'], 3600);
                }
            }
        }
        return $this->_jsapiTicket;
    }
    /**
     * 获取16位随机字符串
     *
     * @param number $length
     * @return string
     */
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取网页授权URL
     * SNS为社会性网络服务，为区别网页授权与公众号，网页授权方法中都会带上SNS
     * state参数只能是字母和数字，为方便统一解析，所有参数都使用小写，用A来连接参数，用B来连接键与值
     *
     * @example state为storeidB7AcaidB8,解析后就是storeid=7&cabid=8
     * @param string $url
     *            授权重定向url
     * @param string $scope
     *            应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @param string $state
     *            重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
     * @return string
     */
    public function getSNSOauthUrl($url, $scope = 'snsapi_base', $state = '123')
    {
        return self::OAUTH2_URL . "appid=" . $this->appId . "&redirect_uri=" . urlencode($url) . "&response_type=code&scope=" . $scope . "&state=" . $state . "#wechat_redirect";
    }

    /**
     * 根据code获取网页授权TODO
     */
    public function getSNSAccessToken($code)
    {
        @file_put_contents("pangyongfu.log",self::GET_SNS_ACCESS_TOKEN_URL . 'appid=' . $this->appId . '&secret=' . $this->appSecrect . '&code=' . $code . '&grant_type=authorization_code'."\r\n",FILE_APPEND);

        return $this->httpGet(self::GET_SNS_ACCESS_TOKEN_URL . 'appid=' . $this->appId . '&secret=' . $this->appSecrect . '&code=' . $code . '&grant_type=authorization_code');
    }

    /**
     * 微信接入验证
     * @param $echoStr
     * @param $signature
     * @param $timestamp
     * @param $nonce
     * @param $token
     *
     */
    public function verify($echoStr, $signature, $timestamp, $nonce,$token){
        // 生成签名
        $tmpArr = [
            $token,
            $timestamp,
            $nonce
        ];

        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            echo $echoStr;
            exit ();
        }
    }

    /**
     * 解析POST过来的XML信息
     *
     * @return NULL
     */
    public function parseMsg()
    {
        $postStr = file_get_contents("php://input");
        if (!empty ($postStr)) {
            libxml_disable_entity_loader(true);
            $this->_receive = ( array )simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            return $this->_receive;
        }
        return null;
    }

    /**
     * 获取解析后的XML数据中信息
     *
     * @param string $name
     *            为all时返回整个数组
     * @return NULL
     */
    public function getRev($name)
    {
        if ($name == 'all') {
            return $this->_receive;
        }
        if (is_array($this->_receive) && array_key_exists($name, $this->_receive)) {
            return $this->_receive [$name];
        }
        return null;
    }

    public function eliminateDuplication(){

        $redis = Lz517Helper::getRedisNoAOF();

        if($this->getRev('MsgType') == 'event'){
            //如果是事件，使用FromUserName + CreateTime排重
            $key = 'wechat:eliminate:' . $this->getRev('FromUserName').$this->getRev('CreateTime');
        }else{
            //如果是消息，使用MsgId进行排重
            $key = 'wechat:eliminate:' . $this->getRev('MsgId');
        }

        if($redis->exists($key)){
            echo '';
            die;
        }else{
            $redis->set($key,1);
        }
    }

    /**
     * 获取access_token
     */
    public function getAccessToken()
    {
        if (is_null($this->_accessToken)) {
//            $this->_accessToken = S('wx_accessToken');
            @file_put_contents("pangyongfu.log","从缓存中获取的token：".$this->_accessToken."\r\n",FILE_APPEND);
//            if (!$this->_accessToken) {

                $appId = $this->appId;
                $appSecret = $this->appSecrect;

                $result = $this->httpGet(self::GET_ACCESS_TOKEN_URL . 'appid=' .$appId  . '&secret=' . $appSecret);
                @file_put_contents("pangyongfu.log","22222".self::GET_ACCESS_TOKEN_URL . 'appid=' .$appId  . '&secret=' . $appSecret."\r\n",FILE_APPEND);
                @file_put_contents("pangyongfu.log","从微信端请求AccessToken的结果是：".$result."\r\n",FILE_APPEND);

                $json = $this->decodeJson($result);

                if (array_key_exists('access_token', $json)) {
                    $this->_accessToken = $json ['access_token'];
//                    S('wx_accessToken', $json ['access_token'], 3600);
                }
//            }
        }
        return $this->_accessToken;
    }

    /**
     * GET 请求
     * @param string $url
     * @return bool| cml
     */
    private function httpGet($url)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 300);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        @file_put_contents("pangyongfu.log","GET 请求错误信息2：".curl_errno($oCurl)."\r\n",FILE_APPEND);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        @file_put_contents("pangyongfu.log","GET 请求返回数据：".json_encode($aStatus)."\r\n",FILE_APPEND);

        if (intval($aStatus ["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * 解析微信返回的json数据
     * @param unknown $result
     * @return Ambigous <NULL, mixed>
     */
    private function decodeJson($result)
    {
        $arr = null;
        if ($result) {
            $arr = json_decode($result, true);
            if ($arr && array_key_exists('errcode', $arr)) {
                $this->errCode = $arr ['errcode'];
                $this->errMsg = $arr ['errmsg'];
            }
        }
        return $arr;
    }
    /**
     * 获取用户基本信息
     *
     * @param string $openid
     * @return array
     */
    public function getUserInfo($openid)
    {
        $accessToken = $this->getAccessToken();
        @file_put_contents("pangyongfu.log","有获取的token值：".$accessToken."\r\n",FILE_APPEND);
        @file_put_contents("pangyongfu.log",self::GET_USER_INFO_URL . 'access_token=' . $accessToken . '&openid=' . $openid . "&lang=zh_CN"."\r\n",FILE_APPEND);

        $result = $this->httpGet(self::GET_USER_INFO_URL . 'access_token=' . $accessToken . '&openid=' . $openid . "&lang=zh_CN");
        return json_decode($result, true);
    }

    /**
     * 发送模版消息接口
     * @param $param
     * @return string
     * @author  sunyn
     * email: sunyn@jpgk.com.cn
     * QQ:743669853
     */
    public function sendTemplate($param)
    {
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::SEND_TEMPLATE_URL . 'access_token=' . $accessToken, $param);
        return $result;
    }

    /**
     * 获取微信用户列表
     * @param $param
     * @return string
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getWXUserList()
    {

        $appId = WECHAT_APPID;
        $appSecret = WECHAT_APPSECRET;


        $result = $this->httpGet(self::GET_ACCESS_TOKEN_URL . 'appid=' .$appId  . '&secret=' . $appSecret);

        Log::write('获取用户accessToken | '.$result);

        $json = $this->decodeJson($result);

        $return = $this->httpPost(self::GET_USER_LIST_URL . 'access_token=' . $json ['access_token'],'');
        return $return;
    }

    /**
     * 获取微信用户列表 服务号
     * @param $param
     * @return string
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getWXUserListService()
    {

        $result = $this->getAccessToken();

        Log::write('获取用户accessToken | '.$result);

        $json = $this->decodeJson($result);

        $return = $this->httpPost(self::GET_USER_LIST_URL . 'access_token=' . $json ['access_token'],'');
        return $return;
    }

    //发送客服图文消息(订阅号专属)
    public function sendDYCustomServiceNewsText($openId, $title, $url, $picUrl,$content=''){

        if(!empty($url)){
            $url .= '&t='.time();
        }
        $param = '{
                        "touser":"'.$openId.'",
                        "msgtype":"news",
                        "news":{
                            "articles":[
                                {
                                    "title":"'.$title.'",
                                     "description":"'.$content.'",
                                     "url":"'.$url.'",
                                     "picurl":"'.$picUrl.'"
                                }
                            ]
                        }
                    }';

//        $appId = 'wxfd6a8e36ad215dda';
//        $appSecret = '0f461ba0f10dea715868830ae364dc27';

        $appId = WECHAT_APPID;
        $appSecret = WECHAT_APPSECRET;

        $result = $this->httpGet(self::GET_ACCESS_TOKEN_URL . 'appid=' .$appId  . '&secret=' . $appSecret);

        Log::write('获取用户accessToken | '.$result);

        $json = $this->decodeJson($result);

        $accessToken = $json['access_token'];
        $result = $this->httpPost(self::SEND_KEFU_MSG . 'access_token=' . $accessToken,$param);
        $result = $this->decodeJson($result);

        return $result;

    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    private function httpPost($url, $param)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = [];
            foreach ($param as $key => $val) {
                $aPOST [] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 600);//todo
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus ["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }


    /**
     * 创建公众号菜单（客户端）
     *
     * @return unknown
     */
    public function createClientMenu()
    {
        $accessToken = $this->getAccessToken();

        $menu = '{
            "button":[
                       {
                           "name":"预约服务",
                           "sub_button":[
                            {
                               "type":"view",
                               "name":"门店消杀",
                               "url":"'.host_url.'/Wechat/Index/selectUserType?door_type=2"
                            },
                            {
                               "type":"view",
                               "name":"烟道清洗",
                               "url":"'.host_url.'/Wechat/Index/selectUserType?door_type=3"
                            },
                            {
                               "type":"view",
                               "name":"门店维修",
                               "url":"'.host_url.'/Wechat/Index/selectUserType?door_type=1"
                            }]
                        },
                       {
                           "name":"更多服务",
                           "sub_button":[
                           {
                               "type":"view",
                               "name":"油烟净化器检测安装",
                               "url":"'.host_url.'/Wechat/Index/customerServices/type/1"
                           },
                           {
                               "type":"view",
                               "name":"微信会员营销系统",
                               "url":"https://e.eqxiu.com/s/PUvfORrM"
                            },
                            {
                               "type":"view",
                               "name":"《餐饮业大气污染物排放标准》",
                               "url":"'.host_url.'/Public/餐饮业大气污染物排放标准.pdf"
                            },
                            {
                               "type":"view",
                               "name":"餐饮青年联盟",
                               "url":"http://cyqnlmbj.mikecrm.com/HFwYPrP?from=singlemessage"
                            },
                            {
                               "type":"view",
                               "name":"恶意讹诈举报",
                               "url":"http://cn.mikecrm.com/dsaV7yb"
                            }]
                        },
                       {
                           "name":"我的",
                           "sub_button":[
                           {
                               "type":"view",
                               "name":"巡检列表",
                               "url":"'.host_url.'/Wechat/Index/showChildList"
                            },{
                               "type":"view",
                               "name":"我的订单",
                               "url":"'.host_url.'/Wechat/Index/myOrder"
                            },{
                               "type":"view",
                               "name":"门店管理",
                               "url":"'.host_url.'/Wechat/Index/addressList"
                            },{
                               "type":"view",
                               "name":"客服",
                               "url":"'.host_url.'/Wechat/Index/customerServices"
                            },{
                               "type":"view",
                               "name":"索取发票",
                               "url":"'.host_url.'/Wechat/Index/myInvoice"
                            }]
                        }
            ]
        }';

        $result = $this->httpPost(self::CREATE_MENU_URL . 'access_token=' . $accessToken, $menu);
        return $result;
    }

    public function createMenuWithParam($menu){
        $accessToken = $this->getAccessToken();

        $result = $this->httpPost(self::CREATE_MENU_URL . 'access_token=' . $accessToken, $menu);
        return $result;
    }

    /**
     * 删除菜单
     * @return string
     * @author  tianxh.
     * email: tianxh@jpgk.com.cn
     * QQ:2018997757
     */
    public function deleteMenu(){
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::DELETE_MENU_URL . 'access_token=' . $accessToken,[]);
        return $result;
    }

    /**
     * 获取带参数的永久二维码
     *
     * @param string $str
     *            1到64位
     * newToken 是否需要新生成token
     * @abstract $str为字符串，为方便统一解析，各参数之间以&链接，键与值之间用=链接
     * @example cabid=4&storeid=11&doorcode=A01
     *          PS.如果携带柜门号，也许以后可以实现使用微信扫码开门
     */
    public function getQRcode($str,$newToken=false)
    {
        G('a');
        /*if(APP_STATUS == 'config_production'){
            if($newToken){

                $url = 'http://jiahe.1668.com/orderapi/load_token1206.php';

                $curl = new MyCurl($url);
                $curl->createCurl();
                $jsonData = $curl->__tostring();
                $result = json_decode($jsonData, true);

                if($result['error'] == 0){
                    $accessToken = $result['data']['data'];
                }else{
                    return false;
                }
            }else{
                $accessToken = $this->getAccessToken();
            }
        }else{
            $accessToken = $this->getAccessToken();
        }*/
        $accessToken = $this->getAccessToken();
        G('b');
//        file_put_contents('/data1/aaa.php',G('a','b'),FILE_APPEND);
        $param = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "' . $str . '"}}}';
        G('c');
        $result = $this->httpPost(self::CREATE_QRCODE_URL . 'access_token=' . $accessToken, $param);
        G('d');
//        file_put_contents('/data1/aaa.php',G('c','d'),FILE_APPEND);
        $result = $this->decodeJson($result);
        // 获取返回的ticket,用于获取图片url
        if (array_key_exists('ticket', $result)) {
            $ticket = $result ['ticket'];
            return self::SHOW_QRCODE_URL . 'ticket=' . urlencode($ticket);
        } else {
            return false;
        }
    }


    //发送用户关注服务号 推送的内容
    public function senWechatAttentionMsg($openId){
        if(!$openId){
            return false;
        }

        $msg = "您与30000+智慧的餐饮老板共同关注了餐讯网！

餐讯网是专注于餐饮门店后端安全服务的B2B开放平台，为您提供省心可靠的上门服务，快来试试吧：

<a href='".host_url."/Wechat/Index/selectUserType?door_type=2'>门店消杀--四害无踪，更省心！</a>

<a href='".host_url."/Wechat/Index/selectUserType?door_type=1'>门店维修--极速上门，更可靠！</a>

<a href='".host_url."/Wechat/Index/selectUserType?door_type=3'>烟道清洗--专业服务，更安全！</a>

<a href='".host_url."/Wechat/Index/customerServices'>门店巡检--以检降修，更划算！（联系客服，或直接留言，免费上门勘查，定制专属服务）</a>

客服电话：18810250371
讯哥期待为您服务！";
        $this->sendCustomServiceMsgText($openId,$msg);
    }

    /**
     * 发送微信分享文章消息 TODO 目前只有关注微信时触发
     * @param $id
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendWechatShareArticleMsg($openId,$id){
     //   file_put_contents("huangqing.log",'进入2:'.$openId.' | '.$id.''."\r\n",FILE_APPEND);

        if(!$id || !$openId){
            return false;
        }
      //  file_put_contents("huangqing.log",'进入3:'."\r\n",FILE_APPEND);

        //查询文章数据
        $info = (new WechatShareArticleModel())->where(['id'=>$id])->find();

        //查询封面数据
        $picInfo = (new PictureModel())->where(['id'=>$info['cover_img_id']])->find();
        $picUrl = host_url.$picInfo['path'];

        $jumpUrl = host_url.'/index.php?s=/wechat/index/sharePaper/shareArticleId/'.$id;

        $this->sendCustomServiceNewsText($openId,$info['title'],$jumpUrl,$picUrl,$info['abstract']);
    }

    //发送客服文本消息
    public function sendCustomServiceMsgText($openId,$text){
        $param = '{
                    "touser":"'.$openId.'",
                    "msgtype":"text",
                    "text":
                    {
                         "content":"'.$text.'"
                    }
                }';
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::SEND_KEFU_MSG . 'access_token=' . $accessToken,$param);
        $result = $this->decodeJson($result);
        return $result;
    }
    //发送客服图文消息
    public function sendCustomServiceNewsText($openId, $title, $url, $picUrl,$content=''){

        $title = str_replace("\"","“",$title);
        if(!empty($url)){
            $url .= '&t='.time();
        }
        $param = '{
                    "touser":"'.$openId.'",
                    "msgtype":"news",
                    "news":{
                        "articles":[
                            {
                                "title":"'.$title.'",
                                 "description":"'.$content.'",
                                 "url":"'.$url.'",
                                 "picurl":"'.$picUrl.'"
                            }
                        ]
                    }
                }';
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::SEND_KEFU_MSG . 'access_token=' . $accessToken,$param);
        $result = $this->decodeJson($result);

        @file_put_contents("pangyongfu.log",'图文消息发送返回值:'.json_encode($result)."\r\n",FILE_APPEND);

        return $result;
    }

    //支付成功后退款
    public function refundOrder($orderCode, $money)
    {
        $orderData = (new OrderService()) -> getOrderByOrderCode($orderCode);
        if(config('app_status') != 'config_production')
        {
            $orderData['money_pay'] = 0.01;
        }
        //引入微信支付类库
        require_once APP_PATH . "Common/Lib/WeiPay/SDK/lib/WxPay.Api.php";
        require_once APP_PATH . "Common/Lib/WeiPay/SDK/WxPay.JsApiPay.php";

        $input = new \WxPayRefund();
        $input->SetOut_trade_no($orderCode);
        $input->SetOut_refund_no($orderCode);
        $input->SetTotal_fee($orderData['money_pay'] * 100);
        $input->SetRefund_fee($money* 100);
        $input->SetOp_user_id(\WxPayConfig::MCHID);
        return \WxPayApi::refund($input,300);
    }

    /**
     * 下载多媒体文件
     *
     * @param string $dirname
     *            图片保存地址，默认保存到导购
     * @param unknown $corpsecret
     * @param unknown $mediaId
     * @return string
     */
    public function saveMedia($mediaId, $dirname = "./Uploads/wx_shoppingGuide/")
    {
        set_time_limit(0);
        $accessToken = $this->getAccessToken();
        $url = self::GET_MEDIA_URL . 'access_token=' . $accessToken . '&media_id=' . $mediaId;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0); // 对body进行输出。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $package = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);

        $media = array_merge([
            'mediaBody' => $package
        ], $httpinfo);
        // 求出文件格式
        preg_match('/\w\/(\w+)/i', $media ["content_type"], $extmatches);
        $fileExt = $extmatches [1];
        $filename = time() . rand(100, 999) . ".{$fileExt}";
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }
        file_put_contents($dirname . $filename,  $media ['mediaBody']);

        //上传阿里
//        if(APP_STATUS == 'config_production'){
//        $file = substr($dirname . $filename,1);
//        $imgUrl = realpath(dirname (__FILE__ ). str_repeat( DIRECTORY_SEPARATOR . '..',3).DIRECTORY_SEPARATOR.'Public'.DIRECTORY_SEPARATOR);
//        writeSomeLog('pr',[$imgUrl.trim($file,'.')]);
//        writeSomeLog('pr',['image/jiahe'.substr($dirname . $filename,1)]);
//        $LibOSSHelper=   new \Common\Lib\OSSHelper();
//        $LibOSSHelper-> uploadFormImage($imgUrl.trim($file,'.'),'image/jiahe'.substr($dirname . $filename,1));
//        }
        return $dirname . $filename;
    }

    /**
     * 绑定用户标签
     * @param $openIdArr
     * @param $tagId
     * @return string|Ambigous
     * @author  sunyn
     * email: sunyn@jpgk.com.cn
     * QQ:743669853
     */
    public function batchTag($openIdArr, $tagId)
    {
        $param = json_encode(['openid_list' => $openIdArr, 'tagid' => $tagId]);
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::BATCH_MEMBERS_AND_TAGS . 'access_token=' . $accessToken,$param);
        $result = $this->decodeJson($result);

        return $result;
    }

    //调用微信统一下单api
    public function unifiedOrder($orderCode,$money,$openId,$notifyUrl,$type=''){

        if(empty($orderCode)){
            throw new \Exception("下单时，订单号有误！");
        }
        if($money <= 0){
            throw new \Exception("下单时，订单金额信息有误！".$money);
        }
        $requestUrl = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        //生成随机字符串
        $randStr = md5(rand(100000,999999));

        //商品描述
        $bodyDesc = "餐讯网上门服务";

        //判断如果订单分类为消杀 支付失效时间为30分钟、维修和清洗失效时间为1年
        //1：维修 2：清洗 3：消杀
        if($type = 3){
            $expireTime = date("YmdHis",strtotime("+30 minutes"));
        }else{
            $expireTime = date("YmdHis",strtotime("+1 year"));
        }

        $requestData = [
            "appid" => $this->appId,
            "mch_id" => \WxPayConfig::MCHID,
            "nonce_str" => $randStr,
            "body" => $bodyDesc,
            "out_trade_no" => $orderCode,
            "total_fee" => intval($money*100),//单位为分
            "spbill_create_ip" => $_SERVER['REMOTE_ADDR'],
            "notify_url" => $notifyUrl,
            "trade_type" => "JSAPI",
            "time_start" => date("YmdHis"),
            "time_expire" => $expireTime,
            "openid" => $openId
        ];
        Log::write("统一下单参数：".json_encode($requestData));

        $sign = $this->getSign($requestData);

        $requestData["sign"] = $sign;
        $requestXml = $this->arrayToXml($requestData);
        $xmlResponse = $this->postXmlCurl($requestXml,$requestUrl);
        $responseData = $this->xmlToArray($xmlResponse);

        Log::write("统一下单结果：".json_encode($responseData));
        if($responseData["return_code"] == "SUCCESS" && $responseData["result_code"] == "SUCCESS"){

            return $responseData;
        }else{

            if(isset($responseData["return_msg"])){
                Log::write("调用统一下单接口错误：".$requestData["return_msg"]);
                throw new \Exception($responseData["return_msg"]);
            }else{
                Log::write("下单失败");
                throw new \Exception('下单失败');
            }
        }
    }

    /**
     * 生成扫码支付 链接返回
     * @param $orderData
     * @param $notifyUrl
     * @param $productId
     * @param $money
     * @return mixed
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function scanCodePay($orderCode,$notifyUrl,$productId,$money){

        if(empty($orderCode) || $money <= 0){
            throw new \Exception("生成扫码下单时，订单号和订单金额信息有误！");
        }

        //扫码支付
        $input = new \WxPayUnifiedOrder();
        $input->SetBody('购买餐讯网订单');
        $input->SetAttach("service:".'购买餐讯网订单');
        $input->SetOut_trade_no($orderCode);
        $input->SetTotal_fee(intval($money*100));
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", strtotime("+1 year")));
        //$input->SetGoods_tag("test");
        $input->SetNotify_url($notifyUrl);
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($productId);

        $notify = new \NativePay();
        $result = $notify->GetPayUrl($input);
        $payUrl = $result["code_url"];

        Log::write("扫码下单生成结果：".json_encode($result));

        return $payUrl;
    }

    //查询微信订单支付状态
    public function orderQuery($orderCode){

        $url = "https://api.mch.weixin.qq.com/pay/orderquery";

        //生成随机字符串
        $randStr = md5(rand(100000,999999));

        $data = [
            "appid" => $this->appId,
            "mch_id" => \WxPayConfig::MCHID,
            "out_trade_no" => $orderCode,
            "nonce_str" => $randStr,
        ];

        $sign = $this->getSign($data);

        $data['sign'] = $sign;

        $requestXml = $this->arrayToXml($data);

        $xmlResponse = $this->postXmlCurl($requestXml,$url);

        $responseData = $this->xmlToArray($xmlResponse);

        if($responseData['return_code'] == 'SUCCESS'){
            return $responseData;
        }else{
            throw new Exception($responseData['return_msg']);
        }
    }

    /**
     * 添加支付日志
     * @param $orderCode
     * @param string $money
     * @param string $type
     * @param string $statusPay
     * @param string $thirdTradeNo
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function addPayLog($orderCode,$money = '',$type = '',$statusPay = '',$thirdTradeNo = ''){
        //pay数据
        $payModel = new WechatPayModel();
        $pay = $payModel->where(['trade_no'=>$orderCode])->find();
        $dateTime = Date('Y-m-d H:i:s');
        if(empty($pay)){
            $payData = [
                'trade_no'=>$orderCode,
                'type'=>$type,//todo 1:微信 ; 2:支付宝
                'money_pay'=>$money,
                'status'=>1,
                'status_pay'=>$statusPay,// 0:待支付; 1:支付成功; 2:支付失败; 3:超时未支付; 4:退款中; 5:已成功退款
                'create_time'=> $dateTime,
                'update_time'=> $dateTime
            ];
            $payModel->data($payData)->add();
        }else{
            $payData = [
                'type'=>$type,//todo 1:微信 ; 2:支付宝
                'third_trade_no'=>$thirdTradeNo,
                'status_pay'=>$statusPay,// 0:待支付; 1:支付成功; 2:支付失败; 3:超时未支付; 4:退款中; 5:已成功退款
                'update_time'=> $dateTime
            ];
            $payModel->where(['trade_no'=>$orderCode])->save($payData);
        }

        //返回
        if($pay['create_time']){
            return $pay['create_time'];
        }else{
            return $dateTime;
        }
    }

    /**
     * 通过订单号查询订单支付数据
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getPayDataForOrderCode($orderCode){

        $map = [
            'trade_no'=>['like','%'.$orderCode.'%'],
        ];
        //pay数据
        $payModel = new WechatPayModel();
        $payData = $payModel->field('id,trade_no')->order('id desc')->where($map)->select();
        return $payData;
    }

    /**
     * 	作用：生成签名
     */
    private function getSign($Obj)
    {
        $apiMiShi = \WxPayConfig::KEY; //商户密匙

        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$apiMiShi;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     * 	作用：以post方式提交xml到对应的接口url
     */
    private function postXmlCurl($xml,$url,$second=30)
    {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
//        curl_close($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        }else{
            $error = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curl出错，错误码:$error"."<br>");
        }
    }
    /**
     * 	作用：将xml转为array
     */
    private function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = "";
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    private function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 给用户发送客服派单模板消息
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770     */
    public function sendPaiOrderMsg($openId,$first,$keyword1,$keyword2,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_DELIVERY_ORDER;//模板id
        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        log::write('客服派单给用户发送消息日志 | '.json_encode($requestData));
        $this->sendTemplate(json_encode($requestData));
    }

    /**
     * 师傅接单后给用户发送信息
     * @param $orderId
     * @param $workersId
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendMasterOrderMsgNew($orderId,$workersId){

        $orderData = (new WechatOrderModel())->where(['id'=>$orderId])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderData['member_id']])->find();
        $workersData = (new WechatMemberModel())->where(['uid'=>$workersId])->find();
        if($userData && $orderData){
            switch ($orderData['order_type']){
                case 1 :
                    $orderTypeText = '门店维修';
                    $urlNew = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$orderData['id'];
                    $remark = "您在餐讯网预约的".$orderTypeText."服务订单已接单，请等待服务商与您联系。餐讯网客服电话：18810250371。";
                    break;
                case 2 :
                    $orderTypeText = '门店消杀';
                    $urlNew = host_url.'/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.$orderData['id'];
                    $remark = "您在餐讯网预约的".$orderTypeText."服务订单已接单，请等待服务商与您联系。餐讯网客服电话：18810250371。";
                    break;
                case 3 :
                    $orderTypeText = '烟道清洗';
                    $urlNew = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$orderData['id'];
                    $remark = "您在餐讯网预约的".$orderTypeText."服务订单已接单，请等待服务商与您联系。餐讯网客服电话：18810250371。";
                    break;
                default : $orderTypeText = '上门服务'; break;
            }


            $this -> sendMasterOrderMsg($userData['open_id'],'师傅已接单',$orderData['order_code'],$orderTypeText,$orderData['update_time'],$workersData['phone'],$remark,$urlNew);
        }

    }

    /**
     * 师傅接单后给用户发送信息 统一方法
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $keyword3
     * @param $keyword4
     * @param $remark
     * @param $urlNew
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendMasterOrderMsg($openId,$first,$keyword1,$keyword2,$keyword3,$keyword4,$remark,$urlNew){
        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_RECEICE_ORDER;//模板id
        $requestData['url'] = $urlNew;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['keyword3'] = [
            'value' => $keyword3,
        ];
        $requestData['data']['keyword4'] = [
            'value' => $keyword4,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        Log::write("发送师傅接单模板消息的结果是：".$result);
    }

    /**
     * 师傅服务完成并提交费用后给用户发送微信模板信息
     * @param $id
     * @param $type
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendMasterOrederFinishPayMoneyNew($id,$type,$is_change){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();

        $url = '';
        if($type == 'storeMaintain'){
            $remark = '您在餐讯网购买的维修服务已完成，请您核对后完成支付，如有售后问题请联系餐讯网客服：18810250371。';
            $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
        }elseif($type == 'storeCleaning'){
            $remark = '您在餐讯网购买的烟道清洗服务已完成，请您核对后完成支付，如有售后问题请联系餐讯网客服：18810250371。';
            $url = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$id;
        }elseif($type == 'storeCleanKill'){
            $remark = '尊敬的用户您好，请您核对后完成支付，谢谢！';
            $url = host_url.'/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.$id;
        }
        $title = "订单支付通知";
        if($is_change){
            $title = "价格更改通知";
        }
        $this->sendMasterOrederFinishPayMoney($userData['open_id'],$title,$orderNewData['order_code'],$orderNewData['money_total'],$remark,$url);
    }

    /**
     * 支付成功发送消息
     * @param $id
     * @param $type
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendOrderPaySuccessMsg($id,$type){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();
        $price = $orderNewData['money_total'];

        Log::write('订单ID标识 | '.$id.'订单数据为 | '.json_encode($orderNewData));

        $url = '';
        $remark = '';
        if($type == 'storeMaintain'){
            $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
            $remark = '您在餐讯网购买的维修服务已完成，您还可通过平台享受四害消杀、烟道清洗服务。详情可咨询餐讯网客服：18810250371。';
        }elseif($type == 'storeCleaning'){
            $url = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$id;
            $remark = '您在餐讯网购买的烟道清洗服务已完成，您还可通过平台享受四害消杀、门店维修和门店巡检。详情可咨询餐讯网客服：18810250371。';
        }elseif($type == 'storeCleanKill'){
            $url = host_url.'/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.$id;
            $remark = '尊敬用户您好，您的消杀服务已经完成支付，您可以通过平台享受门店维修、门店巡检和烟道清洗等服务，详情可咨询餐讯网客服：18810250371。';
        }elseif($type == 'storeCleanKillCj'){
            $itemData = (new WechatOrderItemModel())->where(['order_id'=>$id])->find();
            $price = $itemData['difference_price'];
            $url = host_url.'/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.$id;
            $remark = '您在餐讯网购买的门店消杀服务差价已完成支付，您可以通过平台享受门店维修、门店巡检和烟道清洗等服务，详情可咨询餐讯网客服：18810250371。';
        }

        $this->sendMasterOrederFinishPayMoney($userData['open_id'],'订单支付提醒',$orderNewData['order_code'],$price,$remark,$url);
    }

    /**
     * 师傅服务完成并提交费用后给用户发送微信模板信息
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendMasterOrederFinishPayMoney($openId,$first,$keyword1,$keyword2,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_ORDER_PAY;//模板id

        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
         $requestData['data']['keyword1'] = [
             'value' => $keyword1,
         ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        Log::write("发送模板消息的结果是：".$result);

    }

    /**
     * 师傅服务完成后（消杀）发送用户微信模板
     * @param $id
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendMasterCleanKillFinishNew($id){

        //发送用户微信模板消息
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();

//        $remark = '尊敬的用户您好，接单师傅已完成服务，请您验收，24小时未验收，系统将默认为验收成功并完成订单，谢谢。';
        $remark = '您的消杀服务订单服务已完成，请您验收，您还可通过平台享受门店维修、门店巡检和烟道清洗等服务，详情可咨询餐讯网客服：18810250371。';
        if($orderNewData['is_year'] == 1 && $orderNewData['is_main'] == 0){
            //获取是第几次服务
            $serviceNum = (new WechatYearServiceTimeModel())->where(['order_id'=>$id])->find()['service_num'];
            $remark = '您的消杀服务订单第'.$serviceNum.'次服务已完成，请您验收，您还可通过平台享受门店维修、门店巡检和烟道清洗等服务，详情可咨询餐讯网客服：18810250371。';
        }
        $url = host_url.'/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.I('id');
        $this->sendMasterCleanKillFinish($userData['open_id'],'验收订单',$orderNewData['order_code'],'作业已完成，请验收',$orderNewData['update_time']."完成消杀",$remark,$url);
    }
    /**
     * 主管设置完订单后（消杀）发送用户待确认微信模板
     * @param $id
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendDistributeCleanKillOrderSetMsg($id,$is_year = 0){

        //发送用户微信模板消息
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();
        $facData = (new WechatFacilitatorModel())->where(['id'=>$orderNewData['facilitator_id']])->find();
        $remark = '您预订的消杀服务订单已设置完成，点此可查看详细信息，如无问题请您确认。如有问题请联系服务商，服务商电话:'.$facData['phone'].'。';
        if($is_year){
            $remark = '您预订的消杀年服务订单已设置完成，点此可查看详细信息，如无问题请您确认。如有问题请联系服务商，服务商电话:'.$facData['phone'].'。';
        }
        $url = host_url.'/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.I('id');
        $this->sendMasterCleanKillFinish($userData['open_id'],'确认订单',$orderNewData['order_code'],'订单已设置，待确认',$orderNewData['update_time'],$remark,$url);
    }
    /**
     * 师傅服务完成后（消杀）发送用户微信模板 统一方法
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $keyword3
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendMasterCleanKillFinish($openId,$first,$keyword1,$keyword2,$keyword3,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_ORDER_YS;//模板id

        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
         $requestData['data']['keyword1'] = [
             'value' => $keyword1,
         ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['keyword3'] = [
            'value' => $keyword3,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        Log::write("发送模板消息的结果是：".$result);

    }
    /**
     * 注册成功发消息
     * @param $id
     * @param $type
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendRegistMsg($uid){
        $userData = (new WechatMemberModel())->where(['uid'=>$uid])->find();
        $first = "恭喜您注册成功！";
        $remark = '尊敬的用户您好，您已注册成功，点击下方预约服务立即下单';
//        $url = 'http://opensns.lz517.com/index.php?s=/wechat/index/activitiesPust/';
        $url = '';

        $this->sendRegistSuccessMsg($userData['open_id'],$first,$userData['phone'],$userData['create_time'],$remark,$url);
    }
    /**
     * 顾客再次下单提醒
     * @param $id
     * @param $type
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendOrderAgainMsg($uid,$id){
        $userData = (new WechatMemberModel())->where(['uid'=>$uid])->find();
        $first = "再次下单提醒！";
        $remark = '尊敬的用户您好，请您再次下单！';
        $url = host_url.'/index.php?s=/wechat/index/selectEqipmentType/order_id/'.$id;
        $date = date("Y-m-d");
        $this->sendOrderAgaineMsg($userData['open_id'],$first,$date,"门店维修",$remark,$url);
    }
    /**
     * 提醒用户再次下单发消息模板
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendOrderAgaineMsg($openId,$first,$keyword1,$keyword2,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_AGAIN_ORDER;//模板id

        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        Log::write("发送模板消息的结果是：".$result);

    }
    /**
     * 注册完成后发消息模板
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendRegistSuccessMsg($openId,$first,$keyword1,$keyword2,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_REGIST_SUCCESS;//模板id

        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        Log::write("发送模板消息的结果是：".$result);

    }

    /**
     * 给用户发送创建订单提醒
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $keyword3
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendUserCreateOrderMsg($openId,$first,$keyword1,$keyword2,$keyword3,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_ORDER_CREATE;//模板id

        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['keyword3'] = [
            'value' => $keyword3,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        @file_put_contents("pangyongfu.log",'图文消息发送用户openId:'.$openId."\r\n",FILE_APPEND);
        @file_put_contents("pangyongfu.log",'图文消息发送返回值:'.json_encode($result)."\r\n",FILE_APPEND);
    }

    /**
     * 给用户发送创建订单提醒
     * @param $id
     * @param $type  1门店维修，2门店消杀，3烟道清洗
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendCreateOrderMsg($id,$type,$shRemark=""){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();

        //1门店维修，2门店消杀，3烟道清洗
        if($type == 1){
            $orderItemData = (new WechatOrderItemModel())->where(['order_id'=>$id])->field("urgent_level")->find();
            $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
            $remark = '您维修订单已生成，服务商将在平台规定时间内与您联系，请保持电话畅通。';
            if($orderItemData['urgent_level'] == 2){
                $remark = '您的🔥紧急订单已生成，师傅将在2小时内上门，如超过2小时到达，只能按照普通订单标准收费，请您保持电话畅通以便师傅与您联系。';
            }
            $title = '门店维修';
        }elseif($type == 2){
            $url = host_url.'/index.php?s=/wechat/index/showCleankillOrder/order_id/'.$id;
            $remark = '您在餐讯网预定的门店消杀服务订单已生成，服务商将在平台规定时间内与您联系，请保持电话畅通。';
            if(!empty($orderNewData['renew_order_id'])){
                $remark = '您的续签订单已生成，，服务商将在平台规定时间内与您联系，请保持电话畅通。';
            }
            $title = '门店消杀';
        }else{
            $url = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$id;
            $remark = '您在餐讯网预定的烟道清洗服务订单已生成，服务商将在平台规定时间内与您联系，请保持电话畅通。';
            $title = '烟道清洗';
        }
        if(!empty($shRemark)){
            $remark = $shRemark;
        }
        $this->sendUserCreateOrderMsg($userData['open_id'],'订单状态提醒',$title,$orderNewData['order_code'],$orderNewData['create_time'],$remark,$url);
    }

    /**
     * 给用户发送订单超时提醒
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendUserOrderOutMsg($openId,$first,$keyword1,$keyword2,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_ORDER_OUT;//模板id

        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        Log::write("发送模板消息的结果是：".$result);
    }

    /**
     * 给用户发送创建订单提醒
     * @param $id
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendUserOrderOutMsgNew($id,$statusText='',$type){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();

        //1门店维修，2门店消杀，3烟道清洗
        if($type == 1){

            $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
            $remark = '您在餐讯网购买的维修服务未支付完成，为不影响您的下次使用请尽快支付，如有售后问题请联系餐讯网客服：18810250371。';
        }else{

            $url = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$id;
            $remark = '您的烟道清洗服务未完成支付，为不影响您的下次使用请尽快支付，如有售后问题请联系餐讯网客服：18810250371。';
        }
        $this->sendUserOrderOutMsg($userData['open_id'],'订单超时提醒',$orderNewData['order_code'],$statusText,$remark,$url);
    }

    /**
     * 给用户发送订单完成提醒
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendUserOrderOverMsg($openId,$first,$keyword1,$keyword2,$remark,$url){

        $requestData = [];
        Log::write("发送模板消息给openId：".$openId);
        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_ORDER_OVER;//模板id

        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        Log::write("发送模板消息的结果是：".$result);
    }

    /**
     * 给用户发送订单完成提醒 清洗
     * @param $id
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendUserCleaningOrderOverMsgNew($id){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();

        $url = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$id;
        $remark = '您在餐讯网购买的门店清洗服务已完成，订单号：'.$orderNewData['order_code'].'，售后问题请联系餐讯网客服：18810250371，期待您下次光临。';

        $this->sendUserOrderOverMsg($userData['open_id'],'订单完成提醒',$orderNewData['order_code'],$orderNewData['update_time'],$remark,$url);
    }

    /**
     * 给用户发送订单完成提醒
     * @param $id
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendUserOrderOverMsgNew($id){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();

        if($orderNewData['order_type'] == 1){
            $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
            $serviceType = "门店维修";
        }elseif($orderNewData['order_type'] == 2){
            $url = host_url.'/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.$id;
            $serviceType = "门店消杀";
        }else{
            $url = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$id;
            $serviceType = "烟道清洗";
        }
        $remark = '您在餐讯网购买的'.$serviceType.'服务已完成，订单号：'.$orderNewData['order_code'].'，售后问题请联系餐讯网客服：18810250371，期待您下次光临。';

        $this->sendUserOrderOverMsg($userData['open_id'],'订单完成提醒',$orderNewData['order_code'],$orderNewData['update_time'],$remark,$url);
    }
    /**
     * 给用户发送订单完成确认提醒
     * @param $id
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendUserMakeSureOrderOverMsg($id,$is_change = 0){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();

        if($orderNewData['order_type'] == 1){
            $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
            $serviceType = "门店维修";
        }elseif($orderNewData['order_type'] == 2){
            $url = host_url.'/index.php?s=/wechat/index/showCleanKillOrder/order_id/'.$id;
            $serviceType = "门店消杀";
        }else{
            $url = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$id;
            $serviceType = "烟道清洗";
        }

        $remark = '您在餐讯网购买的'.$serviceType.'服务已完成，请您确认价格后点击完成，订单号：'.$orderNewData['order_code'].'，售后问题请联系餐讯网客服：18810250371，期待您下次光临。';
        $title = "订单完成确认提醒";
        if($is_change){
            $title = "价格更改通知";
        }
        $this->sendUserOrderOverMsg($userData['open_id'],$title,$orderNewData['order_code'],$orderNewData['update_time'],$remark,$url);
    }
    /**
     * 服务到期提醒（清洗和消杀）
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $keyword3
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendMsgServiceExpiration($openId,$first,$keyword1,$keyword2,$keyword3,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_SERVICE_EXPIRATION;//模板id

        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['keyword3'] = [
            'value' => $keyword3,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $result = $this->sendTemplate(json_encode($requestData));
        Log::write("发送模板消息的结果是：".$result);
    }
    /**
     * 给用户发送服务到期提醒
     * @param $id
     * @param $type  1门店维修，2门店消杀，3烟道清洗
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendServiceExpiration($id,$type,$day,$expiration){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();

        //1门店维修，2门店消杀，3烟道清洗
        if($type == 2){
            $url = host_url.'/index.php?s=/wechat/index/showCleankillOrder/order_id/'.$id;
            $remark = '距离您上次消杀已过'.$day.'天，北京市政府规定，餐企应定期进行除虫灭害工作，防止害虫孳生，建议每月定期对门店虫害进行消杀，您可点击下方预约服务-门店消杀下单，及时进行虫害防治。';
            $title = '门店消杀';
            $first = '四害消杀提醒';
        }else{
            $url = host_url.'/index.php?s=/wechat/index/showCleaningOrder/order_id/'.$id;
            $remark = '距离您上次烟道清洗已过'.$day.'天，北京市政府规定，后厨排油烟管道应当每60日至少清理1次，为确保消防安全，您可点击下方预约服务-烟道清洗下单，及时清理烟道。';
            $title = '烟道清洗';
            $first = '烟道清洗提醒';
        }
        if(!empty($shRemark)){
            $remark = $shRemark;
        }
        $this->sendMsgServiceExpiration($userData['open_id'],$first,$orderNewData['order_code'],$title,$expiration,$remark,$url);
    }


    /**
     * 生成订单微信支付数据/扫码支付
     * @param $orderData
     * @throws \Exception
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function checkOrderNoPay($orderData){

        //订单如果是待支付 拼接微信支付代码
        if($orderData['order_state'] == '2'){

            $orderCode = $orderData['order_code'];

            //获取订单数据 需支付的金额
            $money = $orderData['money_total'];

            //获取微信js签名信息
//            $this->getWechatJsSignPackage();

            //获取用户的open_id
//            $memId = session("memId");
            $member = (new WechatMemberModel())->where(['uid'=>$orderData['member_id']])->find();
            $openId = $member['open_id'];

            //支付回调地址
            $notifyUrl = host_url.'/index.php/Wechat/Notify/wxPayNotify';
            require_once APP_PATH . "Pay/Library/WxpayAPI/lib/instance/WxPay.JsApiPay.php";

            //1.判断该订单师傅是否已经更改过价格 如果价格相等 取pay表最新的数据
            $orderNewData = $this->getPayDataForOrderCode($orderCode);

            //循环取得第一个就是最新生成的订单号
            $orderGGCode = '';
            $orderNum = '';
            $smOrderCode = '';
            foreach($orderNewData as $val){
                if(strstr($val['trade_no'],"GG") && !strstr($val['trade_no'],"SM")){
                    $orderCode = $val['trade_no'];

                    //获取可编辑的更改价格的订单号
                    $orderGGCode = $val['trade_no'];

                    //获取订单号中前缀自增的数字
                    $orderNum = explode('GG',$val['trade_no'])[0];
                    break;
                }elseif(strstr($val['trade_no'],"SM") && empty($smOrderCode)){
                    $smOrderCode = $val['trade_no'];
                }
            }

            //2.如果money_total 与 money_total_old 字段不一致的话,则价格就是已经变更
            $orderNumNew = '';
            if($orderData['money_total'] != $orderData['money_total_old']){

                //2.1 首先去查pay表是否已经有生成的变更价格的订单了,如果有取出在原有的基础上 将订单号头一位自增加一 ，避免重复
                if(!empty($orderGGCode)){
                    $orderNumNew = $orderNum+1;
                    $orderCode = $orderNumNew.'GG'.$orderData['order_code'];
                }else{
                    $orderCode = '1GG'.$orderData['order_code'];
                }

                //3.统一修改价格
                (new WechatOrderModel())->where(['id'=>$orderData['id']])->save(['money_total_old'=>$orderData['money_total']]);
            }

            log::write('生成的全新订单号:'.$orderCode);
            log::write('生成的全新价格:'.$money);

            //调用统一下单，获取支付相关数据
            $UnifiedOrderResult = $this->unifiedOrder($orderCode,$money,$openId,$notifyUrl,'1');

            $jsApiParameters = (new \JsApiPay())->GetJsApiParameters($UnifiedOrderResult);
            $jsApiParameters = json_decode ( $jsApiParameters, true );

            //增加payLog数据
            $this->addPayLog($orderCode,$money,1,0,$orderCode);

            //增加扫码payLog数据
            $smPayLog = $this->addPayLog('SM'.$orderCode,$money,1,0,'SM'.$orderCode);

            //判断如果订单创建时间超过2小时则重新生成一个全新的扫码订单
            if((strtotime(Date('Y-m-d H:i:s')) - strtotime($smPayLog)>=7200)){

                //取最新的扫码订单号
                $orderCodeNew = '';
                if($smOrderCode){
                    $codeNumNew = explode('SM',$smOrderCode)[0];
                    $codeNew = explode('SM',$smOrderCode)[1];
                    $orderCodeNew = ($codeNumNew + 1).'SM'.$codeNew;
                }
                //扫码支付
                $payUrl = $this->scanCodePay($orderCodeNew,$notifyUrl,$orderData['id'],$money);
                $this->addPayLog($orderCodeNew,$money,1,0,$orderCodeNew);
            }else{
                //扫码支付
                $payUrl = $this->scanCodePay('SM'.$orderCode,$notifyUrl,$orderData['id'],$money);
            }
            $returnNewData = [
                'p_timeStamp'=>$jsApiParameters['timeStamp'],
                'p_nonceStr'=>$jsApiParameters['nonceStr'],
                'p_package'=>$jsApiParameters['package'],
                'p_paySign'=>$jsApiParameters['paySign'],
                'p_signType'=>$jsApiParameters['signType'],
                'payUrl'=>$payUrl,
                'orderId'=>$orderData['id'],
                'orderCode'=>$orderCode,
                'moneyPay'=>$money,
            ];

            return $returnNewData;
        }
    }

    /**
     * 给用户发送时间修改提醒
     * @param $id
     */
    public function sendMsgChangeOrderTime($id,$time,$num){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();
        $facData = (new WechatFacilitatorModel())->where(['id'=>$orderNewData['facilitator_id']])->find();
        $url = host_url.'/index.php?s=/wechat/index/showcleankillyearorder/order_id/'.$id;
        $remark = '您购买的消杀年服务，第'.$num.'次服务时间已经更改至'.$time.'，点击此条信息可查看年服务订单详情，如因故不能按期进行消杀，请及时告知服务商！服务商电话:'.$facData['phone'];
        $title = '订单已修改';
        $first = '服务时间修改通知';
        $keyword2 = "下次服务时间(".$time.")";
        $this->sendOrderChangeMsg($userData['open_id'],$first,$title,$remark,$url,$keyword2);
    }
    /**
     * 给用户发送更换服务商通知
     * @param $id
     */
    public function sendMsgChangeOrderFac($id){
        $orderNewData = (new WechatOrderModel())->where(['id'=>$id])->find();
        $userData = (new WechatMemberModel())->where(['uid'=>$orderNewData['member_id']])->find();
        $facData = (new WechatFacilitatorModel())->where(['id'=>$orderNewData['facilitator_id']])->find();
        $url = host_url.'/index.php?s=/wechat/index/showcleankillyearorder/order_id/'.$id;
        $remark = '您好，您在餐讯网预订的门店消杀年服务服务商已更换为'.$facData['name'].'，新服务商将完成后续的消杀服务，点击此条信息可查看年服务订单详情';
        $title = '服务商已更换';
        $first = '服务商更换通知';
        $keyword2 = date("Y-m-d H:i:s");
        $this->sendOrderChangeMsg($userData['open_id'],$first,$title,$remark,$url,$keyword2);
    }
    /**
     * 给供应商主管发送年服务提醒消息
     * @param $orderId
     * @param $supervisorId
     * @param $serviceTime
     * @param $orderType
     * @param $storeName
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendYearServiceMsgForFacilitator($orderId,$supervisorId,$serviceTime,$orderType,$storeName){

        $userInfo = (new WechatMemberModel())->where(['uid'=>$supervisorId])->find();
        //消杀年服务
        if($orderType == 2){
            $url = host_url.'/index.php?s=/Enterprise/DistributionSupervisor/showCleanKillYearOrder/order_id/'.$orderId;
//            $remark = ''.$storeName.'门店的消杀年服务,计划将于'.$serviceTime.'，上门，请提前与用户确认上门时间。';
            $remark = ''.$storeName.'店的订单还有5天时间上门服务，请与客户确认上门时间，如有变化请修改上门时间。';
            $title = '年服务提醒';
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
            $enterpriseService->sendPicTextMsg($userInfo['wx_code'],$orderType,$title,$remark,$url);
        }
    }

//    /**
//     * 给用户发送年服务提醒消息
//     * @param $orderId
//     * @param $openId
//     * @param $number
//     * @param $serviceTime
//     * @param $orderType
//     * @author  HQ.
//     * email: huangqing@jpgk.com.cn
//     * QQ:2322523770
//     */
//    public function sendYearServiceMsgForUser($orderId,$openId,$number,$serviceTime,$orderType){
//
//        //消杀年服务
//        if($orderType == 2){
//            $url = host_url.'/index.php?s=/wechat/index/showcleankillyearorder/order_id/'.$orderId;
//            $remark = '您购买的消杀年服务，计划将于'.$serviceTime.'进行第'.$number.'次上门消杀，服务订单将会于上门前一天自动生成，请您提前安排好门店工作，做好消杀准备。服务商会提前与您联系，如因故不能按期进行消杀，请及时告知服务商。';
//            $title = '年服务提醒';
//            $this->sendCustomServiceNewsText($openId,$title,$url,'',$remark);
//        }
//        //维修年服务
//        //清洗年服务
//    }
    /**
     * 给用户发送年服务模板提醒消息
     * @param $orderId
     * @param $openId
     * @param $number
     * @param $serviceTime
     * @param $orderType
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendYearServiceMsgForUser($orderId,$openId,$number,$serviceTime,$orderType){
        //获取主订单的服务商
        $orderData = (new WechatOrderModel())->where(['id'=>$orderId])->field("facilitator_id")->find()['facilitator_id'];
        $facilitator = (new WechatFacilitatorModel())->where(['id'=>$orderData])->find()['name'];
        //消杀年服务
        if($orderType == 2){
            $url = host_url.'/index.php?s=/wechat/index/showcleankillyearorder/order_id/'.$orderId;
            $remark = '您购买的消杀年服务，计划将于'.$serviceTime.'进行第'.$number.'次上门消杀，服务订单将会于上门前一天自动生成，请您提前安排好门店工作，做好消杀准备。服务商会提前与您联系，如因故不能按期进行消杀，请及时告知服务商。';
            $first = '年服务提醒';
            $this->sendDoorServiceMsg($openId,$first,$serviceTime,$remark,$url,$facilitator);
        }
        //维修年服务
        //清洗年服务
    }

    /**
     * 给供应商发送年服务订单创建消息
     * @param $orderId
     * @param $supervisorId
     * @param $serviceTime
     * @param $orderType
     * @param $storeName
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendYearServiceCreatMsgForFacilitator($orderId,$supervisorId,$serviceNum,$serviceTime,$orderType,$storeName){

        $userInfo = (new WechatMemberModel())->where(['uid'=>$supervisorId])->find();
        //消杀年服务
        if($orderType == 2){
            $url = host_url.'/index.php?s=/Enterprise/DistributionSupervisor/showCleanKillYearOrder/order_id/'.$orderId;
//            XXXX门店的消杀年服务，第二次服务订单已经生成，计划需要在明日（2018年6月20日）上门，请提前与用户确认上门时间。
            $remark = ''.$storeName.'门店的消杀年服务，第'.$serviceNum.'次服务订单已经生成，计划需要在明日（'.$serviceTime.'）上门，请提前与用户确认上门时间。';
            $title = '年服务提醒';
            $enterpriseService = new EnterpriseService(COMPANY_EMCODINGASEKEY,COMPANY_TOKEN,COMPANY_CORPID,COMPANY_DISTRIBUTE_APPSECRET);
            $enterpriseService->sendPicTextMsg($userInfo['wx_code'],$orderType,$title,$remark,$url);
        }
    }

    /**
     * 给用户发送年服务订单创建消息
     * @param $orderId
     * @param $openId
     * @param $number
     * @param $serviceTime
     * @param $orderType
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendYearServiceCreatMsgForUser($orderId,$openId,$number,$serviceTime,$orderType,$fid){

        //获取主订单的服务商
        $orderData = (new WechatOrderModel())->where(['id'=>$orderId])->field("facilitator_id")->find()['facilitator_id'];
        $facilitator = (new WechatFacilitatorModel())->where(['id'=>$orderData])->find()['name'];
        //消杀年服务
        if($orderType == 2){
            $url = host_url.'/index.php?s=/wechat/index/showcleankillyearorder/order_id/'.$orderId;
            $remark = '您购买的消杀年服务，第'.$number.'次服务订单已经生成，服务商预计将于明日（'.$serviceTime.'）上门服务。';
            $first = '年服务提醒';
            $this->sendDoorServiceMsg($openId,$first,$serviceTime,$remark,$url,$facilitator);
        }
        //维修年服务
        //清洗年服务
    }
    /**
     * 给用户发送上门服务模板（消息之前是图文消息）
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770     */
    public function sendDoorServiceMsg($openId,$first,$keyword1,$remark,$url,$keyword2){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_DOOR_SERVICE;//模板id
        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        log::write('主管修改服务日期后给用户发送消息日志 | '.json_encode($requestData));
        $this->sendTemplate(json_encode($requestData));
    }
    /**
     * 给用户发送客服派单模板消息
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770     */
    public function sendOrderChangeMsg($openId,$first,$keyword1,$remark,$url,$keyword2){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_ORDER_CHANGE;//模板id
        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => $keyword2,
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        log::write('主管修改服务日期后给用户发送消息日志 | '.json_encode($requestData));
        $this->sendTemplate(json_encode($requestData));
    }
    /**
     * patch请求---领值接口
     */
    public function curlPatch($wo_id,$wo_status,$wo_external_cost,$wo_ff_2,$wo_ff_3,$wo_ff_4){

        $url = "https://valueapex.com/eamic/ws/rest/v1/woList/".$wo_id;//领值接口api
        if(empty($wo_external_cost)){
            $wo_external_cost = 0;
        }
        if(empty($wo_ff_2)){
            $wo_ff_2 = 0;
        }
        if(empty($wo_ff_3)){
            $wo_ff_3 = 0;
        }
        if(empty($wo_ff_4)){
            $wo_ff_4 = 0;
        }
        $data = [
            'wo_status'=>$wo_status,//工单状态
            'wo_external_cost'=>$wo_external_cost,//总金额
            'wo_ff_2'=>$wo_ff_2,//上门检测费
            'wo_ff_3'=>$wo_ff_3,//维修费
            'wo_ff_4'=>$wo_ff_4,//配件费
        ];
        //当订单状态为6时增加完成时间
        if($wo_status == 6){
            $data['wo_finish_time'] = date("Y-m-d H:i:s");
        }

        $ch = curl_init();
        curl_setopt ($ch,CURLOPT_URL,$url);
        curl_setopt ($ch, CURLOPT_USERPWD, '00051:ZcAoQ8kAlWM946Vb');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);

        @file_put_contents("canxun_yimeike.log", "请求数据：".json_encode($data)."---wo_id：".$wo_id."\t\n"."易每刻返回数据：".json_encode($output)."\t\n",FILE_APPEND);
        return $output;
    }
    /**
     *
     */
    public function curlCancel($wo_id,$name,$reason = ''){
        $data = array(
            'action' => $name,  // 'archive' for status 8, or 'cancel' for status 9
            'description'=>$reason  // Mandatory if action 'cancel'
        );

        $url = 'https://valueapex.com/eamic/ws/rest/v1/woList/'.$wo_id;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, '00051:ZcAoQ8kAlWM946Vb');
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $response = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($response,true);

        @file_put_contents("canxun_yimeike.log", "请求数据：".json_encode($data)."---wo_id：".$wo_id."\t\n"."易每刻返回数据：".json_encode($output)."\t\n",FILE_APPEND);
        return $output;
    }
    /**
     * 获取巡检子订单店长详情
     * @param $child_id
     * @return array
     */
    private function getChildOrderAndShopOwnnerInfo($child_id){

        $childModel = new WechatInspectionStoreChildModel();
        $map['wisc.inspection_store_child_id'] = $child_id;
        $field = "wisc.inspection_store_child_id,wisc.create_time,wisc.update_time,wisc.child_order_code,wisc.status,wisc.service_num,
        wm.open_id,wks.name store_name,wmis.name inspector_name,wmis.phone inspector_phone";
        $childData = $childModel->getChildOrderAndShopOwnnerInfo($map,$field);
        $childData['status_text'] = C('INSPECTION_CHILDSTATUS')[$childData['status']];
        return $childData;
    }
    /**
     * 给用户发送巡检订单创建成功提醒
     * @param $inspection_store_child_id
     * @author  pyf.
     * email: pyf@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInsCreateOrderMsg($inspection_store_child_id){
        //获取子订单信息
        $childData = $this->getChildOrderAndShopOwnnerInfo($inspection_store_child_id);
        $url = host_url.'/index.php?s=/wechat/index/inspectionChildOrderDetail/inspection_store_child_id/'.$inspection_store_child_id;
        $remark = $childData['store_name'].'的第'.$childData['service_num'].'次工单已生成，巡检师傅将提前与您联系，请保持电话畅通，订单号：'.$childData['child_order_code'].'，点击查看详情。有问题可联系餐讯网客服电话：18810250371（工作时间：9:00-22:00）';
        $title = '订单生成通知';

        $this->sendUserCreateOrderMsg($childData['open_id'],'订单状态提醒',$title,$childData['child_order_code'],$childData['create_time'],$remark,$url);
    }
    /**
     * 给用户发送巡检订单创建成功提醒
     * @param $inspection_store_child_id
     * @author  pyf.
     * email: pyf@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInsAcceptOrderMsg($inspection_store_child_id){
        //获取子订单信息
        $childData = $this->getChildOrderAndShopOwnnerInfo($inspection_store_child_id);
        $url = host_url.'/index.php?s=/wechat/index/inspectionChildOrderDetail/inspection_store_child_id/'.$inspection_store_child_id;
        $remark = $childData['store_name'].'的第'.$childData['service_num'].'次工单已接单，巡检师傅将在约定时间上门巡检，请保持电话畅通，订单号：'.$childData['child_order_code'].'，点击查看详情。有问题可联系餐讯网客服电话：18810250371（工作时间：9:00-22:00）';

        $this -> sendMasterOrderMsg($childData['open_id'],'巡检师傅已接单',$childData['child_order_code'],$childData['status_text'],$childData['update_time'],$childData['inspector_phone'],$remark,$url);
    }
    /**
     * 给用户发送巡检订单创建成功提醒
     * @param $inspection_store_child_id
     * @author  pyf.
     * email: pyf@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInsOrderStatusChangeMsg($inspection_store_child_id){
        //获取子订单信息
        $childData = $this->getChildOrderAndShopOwnnerInfo($inspection_store_child_id);
        $url = host_url.'/index.php?s=/wechat/index/inspectionChildOrderDetail/inspection_store_child_id/'.$inspection_store_child_id;
        $remark = $childData['store_name'].'的第'.$childData['service_num'].'次工单已完成，请验收，超过24小时后系统将自动验收订单，订单号：'.$childData['child_order_code'].'，点击查看详情。有问题可联系餐讯网客服电话：18810250371（工作时间：9:00-22:00）';

        $this->sendMasterCleanKillFinish($childData['open_id'],'验收订单',$childData['child_order_code'],'订单已完成，待验收',$childData['update_time'],$remark,$url);
    }


    /**
 * 维修主管十分钟未接单发送用户
 * @param $openId
 * @param $id
 */
    public function sendUserEmergencyOrderMsg($openId,$id){

        //发送用户微信模板消息
        $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
        $remark = '【紧急订单】紧急订单已收到，我们会于10分钟内联系您上门事宜，餐讯网客服电话：18810250371（工作时间：9:00-22:00）';
        $title = '订单进度通知';
        $this->sendCustomServiceNewsText($openId,$title,$url,'',$remark);
    }

    /**
     * 门店未确认推送消息
     * @param $openId
     * @param $id
     * @param $store_name
     * @param $service_num
     * @param $order_code
     */
    public function acceptanceOrder($openId,$id,$store_name,$service_num,$order_code){

        //发送用户微信模板消息
        $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
        $remark = '"'.$store_name.'"【门店巡检】第"'.$service_num.'"次订单已完成，请验收，超过24小时后系统将自动验收订单。订单号："'.$order_code.'"，点击查看详情。餐讯网客服电话：18810250371（工作时间：9:00-22:00）';
        $title = '待验收通知';
        $this->sendCustomServiceNewsText($openId,$title,$url,'',$remark);
    }

    /**
     * 24小时门店已确认推送消息
     * @param $openId
     * @param $id
     * @param $store_name
     * @param $service_num
     * @param $order_code
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function confirmedOrder($openId,$id,$store_name,$service_num,$order_code){
        //发送用户微信模板消息
        $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
        $remark = '"'.$store_name.'"【门店巡检】第"'.$service_num.'"次订单系统已自动验收。订单号："'.$order_code.'"，点击查看详情。餐讯网客服电话：18810250371（工作时间：9:00-22:00）';
        $title = '订单验收通知';
        $this->sendCustomServiceNewsText($openId,$title,$url,'',$remark);
    }

    /**
     * 维修员十分钟未接单发送用户
     * @param $openId
     * @param $id
     */
    public function sendUserEmergencyOrderWorkerMsg($openId,$id){

        //发送用户微信模板消息
        $url = host_url.'/index.php?s=/wechat/index/showStoreRepaireOrder/order_id/'.$id;
        $remark = '【紧急订单】紧急订单已接单，师傅会于10分钟内联系您上门事宜，餐讯网客服电话：18810250371（工作时间：9:00-22:00）';
        $title = '订单进度通知';
        $this->sendCustomServiceNewsText($openId,$title,$url,'',$remark);
    }
    /**
     * 给用户发送门店绑定消息
     * @param $openId
     * @param $first
     * @param $keyword1
     * @param $keyword2
     * @param $remark
     * @param $url
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770     */
    public function sendBindStoreMsg($openId,$first,$keyword1,$remark,$url){

        $requestData = [];

        $requestData['touser'] = $openId;
        $requestData['template_id'] = WECHAT_MSG_BIND_STORE;//模板id
        $requestData['url'] = $url;
        $requestData['data']['first'] = [
            'value' => $first,
            'color' => '#173177',
        ];
        $requestData['data']['keyword1'] = [
            'value' => $keyword1,
        ];
        $requestData['data']['keyword2'] = [
            'value' => date("Y-m-d H:i:s"),
        ];
        $requestData['data']['remark'] = [
            'value' => $remark,
            'color' => '#173177',
        ];

        $this->sendTemplate(json_encode($requestData));
    }
}