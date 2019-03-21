<?php

namespace Enterprise\Service;

//use Wechat\Model\PrinterFaultRecordModel;
//use Wechat\Model\StoreManagerBindStoreModel;
//use Wechat\Model\MessageReminderModel;
use Think\Log;
use Wechat\Model\WechatInspectionModel;
use Wechat\Model\WechatInspectionStoreChildModel;
use Wechat\Model\WechatKaStoresModel;
use Wechat\Model\WechatMemberModel;
use Wechat\Model\WechatOrderModel;

/**
 * 微信企业号
 *
 * @author songbin
 *
 */
include_once "php/WXBizMsgCrypt.php";

class EnterpriseService
{
    private $encodingAesKey;
    private $token;
    private $corpId;
    private $corpsecret;
    private $_accessToken = null;
    private $_jsapiTicket = null;
    const GET_ACCESS_TOKEN_URL = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?";
    const GET_USER_INFO_URL = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?";
    const GET_USER_URL = "https://qyapi.weixin.qq.com/cgi-bin/user/get?";
    const GET_MEDIA_URL = "https://qyapi.weixin.qq.com/cgi-bin/media/get?";
    const GET_DEPARTMENT_LIST = "https://qyapi.weixin.qq.com/cgi-bin/department/list?";
    const GET_DEPARTMENT_USER_SIMPLE_LIST = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?";
    const GET_DEPARTMENT_USER_LIST = "https://qyapi.weixin.qq.com/cgi-bin/user/list?";
    const MEDIA_UPLOAD = "https://qyapi.weixin.qq.com/cgi-bin/media/upload?";
    const SEND_MESSAGE = "https://qyapi.weixin.qq.com/cgi-bin/message/send?";
    const GET_JS_API_TICKET_URL = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?";
    const GET_CODE = "https://open.weixin.qq.com/connect/oauth2/authorize?";
    const USER_CREATE_URL = "https://qyapi.weixin.qq.com/cgi-bin/user/create?";
    const USER_UPDATE_URL = "https://qyapi.weixin.qq.com/cgi-bin/user/update?";
    const USER_DELETE_URL = "https://qyapi.weixin.qq.com/cgi-bin/user/delete?";
    const USER_BATCHDELETE_URL = "https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?";
    const INVITE_SEND_URL = "https://qyapi.weixin.qq.com/cgi-bin/invite/send?";

    const CUSTOMER_SERVICE = COMPANY_CUSTOMER_SERVICE;
    const REPAIRE_MASTER = COMPANY_REPAIRE_MASTER;
    const CLEANING_MASTER = COMPANY_CLEANING_MASTER;
    const CLEANKILL_MASTER = COMPANY_CLEANKILL_MASTER;
    const DISTRIBUTE_MANAGER = COMPANY_DISTRIBUTE_MANAGER;
    const ENTERPRISE_MASTER = COMPANY_ENTERPRISE_MASTER;

    //1：门店用户 2：门店消杀 3： 设备维修 4：烟道清洗 5：客服 6：企业主管
    const TYPE_USER_CUSTOMER = 1;
    const TYPE_USER_CLEANKILL = 2;
    const TYPE_USER_REPAIRE = 3;
    const TYPE_USER_CLEANING = 4;
    const TYPE_USER_SERVICE = 5;
    const TYPE_USER_ENTERPRISE = 6;
    //0不是, 1分配主管 2客服 6企业主管
    const ADMIN_NOMAL = 0;
    const ADMIN_DISTRIBUTE = 1;
    const ADMIN_SERVICE = 2;
    const ADMIN_ENTERPRISE = 6;

    private $dtuInterfaceUrl = "http://p.lz517.net:8080/Printer/getPrinterState";
    private $dtuInterfaceUrlTest = "http://p.lz517.net:8080/PrinterTask/test";

    public function inviteSend($userId){
        $param = ['userid' => $userId];
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::INVITE_SEND_URL . 'access_token=' . $accessToken,json_encode($param) );
        return $result;
    }

    /**
     * @param $userIdList
     * @return string
     */
    public function userBatchDelete($userIdList){
        $param = ['useridlist' => $userIdList];
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::USER_BATCHDELETE_URL . 'access_token=' . $accessToken,json_encode($param) );
        return $result;
    }

    /**
     * @param $userId
     * @return Ambigous
     */
    public function userDelete($userId){
        $accessToken = $this->getAccessToken();
        $result = $this->httpGet(self::USER_DELETE_URL . 'access_token=' . $accessToken . "&userid=" . $userId);
        return $result;
    }

    /**
     * @param $param
     * {
        "userid": "zhangsan",
        "name": "李四",
        "department": [1],
        "position": "后台工程师",
        "mobile": "15913215421",
        "gender": "1",
        "email": "zhangsan@gzdev.com",
        "weixinid": "lisifordev",
        "enable": 1,
        "avatar_mediaid": "2-G6nrLmr5EC3MNb_-zL1dDdzkd0p7cNliYu9V5w7o8K0",
        "extattr": {"attrs":[{"name":"爱好","value":"旅游"},{"name":"卡号","value":"1234567234"}]}
        }
     * @return Ambigous
     */
    public function userUpdate($param){
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::USER_UPDATE_URL . 'access_token=' . $accessToken,json_encode($param) );
        return $result;
    }

    /**
     * 添加用户
     * @param $param
     * {
        "userid": "zhangsan",
        "name": "张三",
        "department": [1, 2],
        "position": "产品经理",
        "mobile": "15913215421",
        "gender": "1",
        "email": "zhangsan@gzdev.com",
        "weixinid": "zhangsan4dev",
        "avatar_mediaid": "2-G6nrLmr5EC3MNb_-zL1dDdzkd0p7cNliYu9V5w7o8K0",
        "extattr": {"attrs":[{"name":"爱好","value":"旅游"},{"name":"卡号","value":"1234567234"}]}
        }
     * @return Ambigous
     */
    public function userCreate($param){
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::USER_CREATE_URL . 'access_token=' . $accessToken,json_encode($param) );
        return $result;
    }

    // 构造函数
    public function __construct($encodingAesKey, $token, $corpId = COMPANY_CORPID, $corpsecret)
    {
        $this->encodingAesKey = $encodingAesKey;
        $this->token = $token;
        $this->corpId = $corpId;
        $this->corpsecret = $corpsecret;
    }

    /**
     * 验证回调URL
     */
    public function verifyURL()
    {

        /*
         * ------------使用示例一：验证回调URL---------------
         * 企业开启回调模式时，企业号会向验证url发送一个get请求
         * 假设点击验证时，企业收到类似请求：
         * GET /cgi-bin/wxpush?msg_signature=5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3&timestamp=1409659589&nonce=263014780&echostr=P9nAzCzyDtyTWESHep1vC5X9xho%2FqYX3Zpb4yKa9SKld1DsH3Iyt3tP3zNdtp%2B4RPcs8TgAE7OaBO%2BFZXvnaqQ%3D%3D
         * HTTP/1.1 Host: qy.weixin.qq.com
         *
         * 接收到该请求时，企业应
         * 1.解析出Get请求的参数，包括消息体签名(msg_signature)，时间戳(timestamp)，随机数字串(nonce)以及公众平台推送过来的随机加密字符串(echostr),
         * 这一步注意作URL解码。
         * 2.验证消息体签名的正确性
         * 3. 解密出echostr原文，将原文当作Get请求的response，返回给公众平台
         * 第2，3步可以用公众平台提供的库函数VerifyURL来实现。
         *
         */

        // $sVerifyMsgSig = HttpUtils.ParseUrl("msg_signature");
        $sVerifyMsgSig = $_GET ['msg_signature'];
        // $sVerifyTimeStamp = HttpUtils.ParseUrl("timestamp");
        $sVerifyTimeStamp = $_GET ['timestamp'];
        // $sVerifyNonce = HttpUtils.ParseUrl("nonce");
        $sVerifyNonce = $_GET ['nonce'];
        // $sVerifyEchoStr = HttpUtils.ParseUrl("echostr");
        $sVerifyEchoStr = $_GET ['echostr'];

        log::write('服务端接收微信验证数据 : '.$sVerifyMsgSig." | ".$sVerifyTimeStamp." | ".$sVerifyNonce." | ".$sVerifyEchoStr);
        log::write('服务端配置的微信数据: '.$this->token." | ".$this->encodingAesKey." | ".$this->corpId);
        // 需要返回的明文
        $EchoStr = "";

        $wxcpt = new \WXBizMsgCrypt ($this->token, $this->encodingAesKey, $this->corpId);
        $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

        log::write('微信验证结果为 | '.$errCode);
        if ($errCode == 0) {
            //
            // 验证URL成功，将sEchoStr返回
            echo $sEchoStr;
            // HttpUtils.SetResponce($sEchoStr);
        } else {
            print ("ERR: " . $errCode . "\n\n");
        }
    }

    /**
     * 消息解密
     */
    public function decrypt()
    {
        /*
         * ------------使用示例二：对用户回复的消息解密---------------
         * 用户回复消息或者点击事件响应时，企业会收到回调消息，此消息是经过公众平台加密之后的密文以post形式发送给企业，密文格式请参考官方文档
         * 假设企业收到公众平台的回调消息如下：
         * POST /cgi-bin/wxpush? msg_signature=477715d11cdb4164915debcba66cb864d751f3e6&timestamp=1409659813&nonce=1372623149 HTTP/1.1
         * Host: qy.weixin.qq.com
         * Content-Length: 613
         * <xml>
         * <ToUserName><![CDATA[wx5823bf96d3bd56c7]]></ToUserName><Encrypt><![CDATA[RypEvHKD8QQKFhvQ6QleEB4J58tiPdvo+rtK1I9qca6aM/wvqnLSV5zEPeusUiX5L5X/0lWfrf0QADHHhGd3QczcdCUpj911L3vg3W/sYYvuJTs3TUUkSUXxaccAS0qhxchrRYt66wiSpGLYL42aM6A8dTT+6k4aSknmPj48kzJs8qLjvd4Xgpue06DOdnLxAUHzM6+kDZ+HMZfJYuR+LtwGc2hgf5gsijff0ekUNXZiqATP7PF5mZxZ3Izoun1s4zG4LUMnvw2r+KqCKIw+3IQH03v+BCA9nMELNqbSf6tiWSrXJB3LAVGUcallcrw8V2t9EL4EhzJWrQUax5wLVMNS0+rUPA3k22Ncx4XXZS9o0MBH27Bo6BpNelZpS+/uh9KsNlY6bHCmJU9p8g7m3fVKn28H3KDYA5Pl/T8Z1ptDAVe0lXdQ2YoyyH2uyPIGHBZZIs2pDBS8R07+qN+E7Q==]]></Encrypt>
         * <AgentID><![CDATA[218]]></AgentID>
         * </xml>
         *
         * 企业收到post请求之后应该
         * 1.解析出url上的参数，包括消息体签名(msg_signature)，时间戳(timestamp)以及随机数字串(nonce)
         * 2.验证消息体签名的正确性。
         * 3.将post请求的数据进行xml解析，并将<Encrypt>标签的内容进行解密，解密出来的明文即是用户回复消息的明文，明文格式请参考官方文档
         * 第2，3步可以用公众平台提供的库函数DecryptMsg来实现。
         */

        // $sReqMsgSig = HttpUtils.ParseUrl("msg_signature");
        $sReqMsgSig = $_GET ['msg_signature'];
        // $sReqTimeStamp = HttpUtils.ParseUrl("timestamp");
        $sReqTimeStamp = $_GET ['timestamp'];
        // $sReqNonce = HttpUtils.ParseUrl("nonce");
        $sReqNonce = $_GET ['nonce'];
        // post请求的密文数据
        // $sReqData = HttpUtils.PostData();
        $sReqData = $GLOBALS['HTTP_RAW_POST_DATA'];

        $sMsg = ""; // 解析之后的明文
        $wxcpt = new \WXBizMsgCrypt ($this->token, $this->encodingAesKey, $this->corpId);
        $errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);

        if ($errCode == 0) {
            // 解密成功，sMsg即为xml格式的明文
            // TODO: 对明文的处理
            // For example:
            if (!empty ($sMsg)) {
                libxml_disable_entity_loader(true);
                $this->_receive = ( array )simplexml_load_string($sMsg, 'SimpleXMLElement', LIBXML_NOCDATA);
                return $this->_receive;
            }
            return null;
        } else {
        }
    }

    /**
     * 加密消息并发送
     */
    public function encrypt($sRespData)
    {
        /*
         * ------------使用示例三：企业回复用户消息的加密---------------
         * 企业被动回复用户的消息也需要进行加密，并且拼接成密文格式的xml串。
         * 假设企业需要回复用户的明文如下：
         * <xml>
         * <ToUserName><![CDATA[mycreate]]></ToUserName>
         * <FromUserName><![CDATA[wx5823bf96d3bd56c7]]></FromUserName>
         * <CreateTime>1348831860</CreateTime>
         * <MsgType><![CDATA[text]]></MsgType>
         * <Content><![CDATA[this is a test]]></Content>
         * <MsgId>1234567890123456</MsgId>
         * <AgentID>128</AgentID>
         * </xml>
         *
         * 为了将此段明文回复给用户，企业应：
         * 1.自己生成时间时间戳(timestamp),随机数字串(nonce)以便生成消息体签名，也可以直接用从公众平台的post url上解析出的对应值。
         * 2.将明文加密得到密文。
         * 3.用密文，步骤1生成的timestamp,nonce和企业在公众平台设定的token生成消息体签名。
         * 4.将密文，消息体签名，时间戳，随机数字串拼接成xml格式的字符串，发送给企业号。
         * 以上2，3，4步可以用公众平台提供的库函数EncryptMsg来实现。
         */
        $sReqTimeStamp = time();
        $sReqNonce = $this->createNonceStr();
        $sEncryptMsg = ""; // xml格式的密文
        $wxcpt = new \WXBizMsgCrypt ($this->token, $this->encodingAesKey, $this->corpId);
        $errCode = $wxcpt->EncryptMsg($sRespData, $sReqTimeStamp, $sReqNonce, $sEncryptMsg);
        if ($errCode == 0) {
            echo $sEncryptMsg;
        } else {
        }
    }

    /**
     * 回复文本消息（被动）
     */
    public function responsePassiveText($content)
    {
        $xmlTpl = "<xml>
                        <ToUserName><![CDATA[" . $this->getRev('FromUserName') . "]]></ToUserName>
                        <FromUserName><![CDATA[" . $this->getRev('ToUserName') . "]]></FromUserName>
                        <CreateTime>" . time() . "</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[" . $content . "]]></Content>
                    </xml>";
        $this->encrypt($xmlTpl);
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
     * GET 请求
     *
     * @param string $url
     */
    private function httpGet($url)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
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
     * POST 请求
     *
     * @param string $url
     * @param array $param
     * @return string content
     */
    private function httpPost($url, $param, $isArray = false)
    {
        $oCurl = curl_init();
        curl_setopt ( $oCurl, CURLOPT_SAFE_UPLOAD, false);
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (!$isArray) {
            if (is_string($param)) {
                $strPOST = $param;
            } else {
                $aPOST = array();
                foreach ($param as $key => $val) {
                    $aPOST [] = $key . "=" . urlencode($val);
                }
                $strPOST = join("&", $aPOST);
            }
        } else {
            $strPOST = $param;
        }
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
     * 解析微信返回的json数据
     *
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
     * 解析POST过来的XML信息
     *
     * @return NULL
     */
    public function parseMsg()
    {
        // 解密
        $this->decrypt();
    }

    /**
     * 获取解析后的XML数据中信息
     *
     * @param unknown $name
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

    /**
     * 获取access_token
     *
     * @return \Wechat\Service\Ambigous
     */
    public function getAccessToken()
    {
        if (is_null($this->_accessToken)) {
            $this->_accessToken = S($this->corpsecret);
            if (!$this->_accessToken) {
                $result = $this->httpGet(self::GET_ACCESS_TOKEN_URL . 'corpid=' . $this->corpId . '&corpsecret=' . $this->corpsecret);
                Log::write("获取access——token".$result);
                $json = $this->decodeJson($result);
                if (array_key_exists('access_token', $json)) {
                    $this->_accessToken = $json ['access_token'];
                    S($this->corpsecret, $json ['access_token'], 3600);
                }
            }
        }
        return $this->_accessToken;
    }

    /**
     * 获取员工
     *
     * @param unknown $corpsecret
     * @param unknown $code
     * @param unknown $agentid
     */
    Public function getUserInfo($code, $agentid)
    {
        $accessToken = $this->getAccessToken();
        $result = $this->httpGet(self::GET_USER_INFO_URL . 'access_token=' . $accessToken . '&code=' . $code . '&agentid=' . $agentid);
        return $this->decodeJson($result);
    }

    /**
     * 根据获取员工信息
     *
     * @param unknown $userId
     * @param unknown $corpsecret
     * @return \Wechat\Service\Ambigous
     */
    public function getUserById($userId)
    {
        $accessToken = $this->getAccessToken();
        $result = $this->httpGet(self::GET_USER_URL . 'access_token=' . $accessToken . '&userid=' . $userId);
        $result = $this->decodeJson($result);
        return $result;
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
        set_time_limit(C('MAX_RUN_TIME'));
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

        $media = array_merge(array(
            'mediaBody' => $package
        ), $httpinfo);

        // 求出文件格式
        preg_match('/\w\/(\w+)/i', $media ["content_type"], $extmatches);
        $fileExt = $extmatches [1];
        $filename = time() . rand(100, 999) . ".{$fileExt}";
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }
        file_put_contents($dirname . $filename, $media ['mediaBody']);
        return $dirname . $filename;
    }

    /**
     * 获取部门列表
     * @param $corpsecret
     * @param int $departmentId 部门id，默认为1（全企业）
     * @return bool|mixed|Ambigous
     */
    public function getDepartmentList($departmentId = 1)
    {
        $accessToken = $this->getAccessToken();
        $result = $this->httpGet(self::GET_DEPARTMENT_LIST . 'access_token=' . $accessToken . '&id=' . $departmentId);
        $result = $this->decodeJson($result);
        return $result;
    }

    /**
     * 获取部门成员简单列表
     * @param $corpsecret
     * @param $departmentId 部门id
     * @param int $fetch_child 是否递归获取子成员 1是2否
     * @param int $status 0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
     * @return bool|mixed|Ambigous
     */
    public function getDepartmentUserSimpleList($departmentId = 1, $fetch_child = 1, $status = 0)
    {
        $accessToken = $this->getAccessToken();
        $result = $this->httpGet(self::GET_DEPARTMENT_USER_SIMPLE_LIST . 'access_token=' . $accessToken . '&department_id=' . $departmentId . '&fetch_child=' . $fetch_child . '&status=' . $status);
        $result = $this->decodeJson($result);
        return $result;
    }

    /**
     * 获取部门成员详细列表
     * @param $corpsecret
     * @param $departmentId 部门id
     * @param int $fetch_child 是否递归获取子成员 1是2否
     * @param int $status 0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
     * @return bool|mixed|Ambigous
     */
    public function getDepartmentUserList($departmentId = 1, $fetch_child = 1, $status = 0)
    {
        $accessToken = $this->getAccessToken();
        $result = $this->httpGet(self::GET_DEPARTMENT_USER_LIST . 'access_token=' . $accessToken . '&department_id=' . $departmentId . '&fetch_child=' . $fetch_child . '&status=' . $status);
        $result = $this->decodeJson($result);
        return $result;
    }

    /**
     * 上传媒体文件
     * @param $type 类型
     * @param $media array('media'=>"@media.jpg")
     * @return string|Ambigous
     */
    public function mediaUpload($type, $media)
    {
        $accessToken = $this->getAccessToken();
        $result = $this->httpPost(self::MEDIA_UPLOAD . 'access_token=' . $accessToken . '&type=' . $type, $media, true);
        $result = $this->decodeJson($result);
        return $result;
    }

    public function sendMessage($param){
        $accessToken = $this->getAccessToken();
        @file_put_contents("pangyongfu.log","发消息的accessToken：".$accessToken."\r\n",FILE_APPEND);
        $result = $this->httpPost(self::SEND_MESSAGE . 'access_token=' . $accessToken,$param);
        @file_put_contents("pangyongfu.log","发消息返回数据：".$result."\r\n",FILE_APPEND);
        $result = $this->decodeJson($result);
        return $result;
    }
    
    /**
     * 获取code
     */
    public function getUserCode($url){
        $appId = $this->corpId;
        return self::GET_CODE . 'appid='.$appId.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_base&state=getCode#wechat_redirect';
    }
    
    /**
     * 根据code获取userid
     */
    public function getUserId($code){
        $accessToken = $this->getAccessToken();
        $result = $this->httpGet(self::GET_USER_INFO_URL . 'access_token='.$accessToken.'&code=' . $code);
        $result = $this->decodeJson($result);
        return $result;
    }
    
    /**
     * 获取jsapi_ticket
     * jsapi_ticket是企业号用于调用微信JS接口的临时票据
     * 开发者必须在自己的服务全局缓存jsapi_ticket
     */
    public function getJsApiTicket()
    {
        $accessToken = $this->getAccessToken();
        if (is_null($this->_jsapiTicket)) {
            $this->_jsapiTicket = S('qy_jsapiTicket');
            if (!$this->_jsapiTicket) {
                $result = $this->httpGet(self::GET_JS_API_TICKET_URL . 'access_token=' . $accessToken);
                $json = $this->decodeJson($result);
                if (array_key_exists('ticket', $json)) {
                    $this->_jsapiTicket = $json ['ticket'];
                    S('qy_jsapiTicket', $json ['ticket'], 3600);
                }
            }
        }
        return $this->_jsapiTicket;
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
    
        $signPackage = array(
                "appId" => $this->corpId,
                "nonceStr" => $nonceStr,
                "timestamp" => $timestamp,
                "url" => $url,
                "signature" => $signature,
                "rawString" => $string
        );
        return $signPackage;
    }
    
    /**
     * 根据时间/日期，返回星期
     * @author wuhao
     * @param string $dateTime 指定日期
     * @return string $return 例：'周三'
     */
    public function getWeek($dateTime) {
        $w = date ( "w", strtotime ( $dateTime ) );
        switch ($w) {
            case '0' :
                return "周日";
                break;
            case '1' :
                return "周一";
                break;
            case '2' :
                return "周二";
                break;
            case '3' :
                return "周三";
                break;
            case '4' :
                return "周四";
                break;
            case '5' :
                return "周五";
                break;
            case '6' :
                return "周六";
                break;
            default :
                return "";
                break;
        }
    }
    
    /**
     * 根据userId获取dtucode(企业号：商家后台)
     * @author wuhao
     * @param string $userId 企业号用户手机号
     * @return string $dtucode 与该用户绑定的打印机code
     */
    public function getDtucodeByUserId($userId){
        $model = new StoreManagerBindStoreModel();
        $dtucode = $model->where ( "store_manager_userid = '{$userId}'" )->getField ( 'dtucode' );
        return $dtucode;
    }
    
    /**
     * 根据dtucode获取打印机故障记录
     * @auhtor wuhao
     * @param string $dtucode 打印机标识
     * @return array $printerFaultRecordData打印机故障记录 例：array(0=>array('id'=>1,'dtucode'=>打印机code,'store_id'=>门店id,'reason'=>故障原因,'resolvement'=>解决方案,'ctime'=>创建时间),1=>array(...),...)
     */
    public function getPrinterFaultDataByDtucode($dtucode){
        $printerFaultModel = new PrinterFaultRecordModel();
        $printerFaultRecordData = $printerFaultModel->where ( "dtu_code = " . $dtucode )->order('ctime desc')->select ();
        return $printerFaultRecordData;
    }
    
    /**
     * 根据dtucode获取与门店绑定的数据
     * @author wuhao
     * @param string $dtucode 打印机标识
     * @return array $userIdData打印机绑定信息 例：array(0=>array('store_manager_userid'=>门店店长userId（手机号）,'dtucode'=>打印机code),1=>array(...),...)
     */
    public function getBindDataByDtucode($dtucode){
        $model = new StoreManagerBindStoreModel ();
        $userIdData = $model->where ( "dtucode = " . $dtucode )->select();
        return $userIdData;
    }
    
    /**
     * 添加打印机故障记录
     * @author wuhao
     * @param array $data 例：array('dtucode'=>打印机code,'store_id'=>门店id,'reason'=>故障原因,'resolvement'=>解决方案,'ctime'=>创建时间)
     * @return $result 如果数据非法或者查询错误则返回false，如果是自增主键 则返回主键值，否则返回1
     */
    public function addPrinterFaultRecord($data){
        $printerFaultRecordModel = new PrinterFaultRecordModel ();
        $result = $printerFaultRecordModel->add ( $data );
        return $result;
    }

    /**
     * 添加消息提醒记录
     * @author wuhao
     * @param array $data 例：array('user_id'=>使用者id,'user_name'=>使用者姓名,'content'=>消息提醒内容,'type'=>消息类型（text）,'create_time'=>创建时间,'update_time'=>修改时间,'from_user'=>使用者身份,'remote_addr'=>使用者ip)
     * @return 如果数据非法或者查询错误则返回false，如果是自增主键 则返回主键值，否则返回1
     */
    public function addMessageReminder($data){
        $model = new MessageReminderModel();
        $result = $model->add($data);
        return $result;
    }


    /**
     * 调用欧阳接口，测试dtuCode
     */
    public function sendDtuCodeForTest($dtuCode){

        $result = $this->httpPost($this->dtuInterfaceUrl,['dtuCode'=>$dtuCode]);

        return json_decode($result,true);
    }

    public function sendDtuCodeAgain($dtuCode){
        $result = $this->httpPost($this->dtuInterfaceUrlTest,['dtuCode'=>$dtuCode]);

        return json_decode($result,true);
    }

    public function getOrderStatusText($status){
        $text = "";
        //0:派单中;1:已接单;2:待支付;3:已完成;4:已取消;5:已电话解决;6:已退款;7:已评价8:已支付;9:待验收;10:未接单)
        switch($status){
            case 0:$text = "派单中";break;
            case 1:$text = "已接单";break;
            case 2:$text = "待支付";break;
            case 3:$text = "已完成";break;
            case 4:$text = "已取消";break;
            case 5:$text = "无需上门";break;
            case 6:$text = "已退款";break;
            case 7:$text = "已评价";break;
            case 8:$text = "已支付";break;
            case 9:$text = "待验收";break;
            case 10:$text = "未接单";break;
            case 11:$text = "服务中";break;
            case 12:$text = "已过期";break;
            case 13:$text = "待确认";break;
        }
        return $text;
    }
    public function getInsMainOrderStatusText($status){
        $text = "";
        //1：新工单(服务商未接单)；2：服务中(服务商已接单)；3：已取消；4：已完成）
        switch($status){
            case 1:$text = "未接单";break;
            case 2:$text = "已接单";break;
            case 3:$text = "已取消";break;
            case 4:$text = "已完成";break;
        }
        return $text;
    }
    /**
     * 获取客服发消息内容
     * @param $orderId
     * @param $type
     * @return array|string
     * @author pangyongfu
     */
    public function getCustomerSendMsg($orderId,$type){

        $toUser = null;
        $description = '';
        $agentid = 0;
        $title = '';
        $url = '';
        $wechatOrderModel = new WechatOrderModel();
        $map['orde.id'] = $orderId;
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,item.difference_status,item.difference_status,item.urgent_level,
        orde.order_type,orde.money_total,member.name worker,member.uid,faci.id fid,faci.name fname";
        $repairData = $wechatOrderModel->getDataByOrderId($map,$field);
        $repairData = $repairData[0];
        $repairData['order_type_text'] = $repairData['order_type']==1?"门店维修":($repairData['order_type']==2?"门店消杀":"门店清洗");

        $repairData['order_state_text'] = $this->getOrderStatusText($repairData['order_state']);
        $description .= "工单编号：".$repairData['order_code']."\r\n";
        $description .= "报修门店：".$repairData['store_name']."\r\n";
        $description .= "报修时间：".$repairData['create_time']."\r\n";
        $description .= "当前状态：".$repairData['order_state_text']."\r\n";
        if($repairData['order_type']==2 && $repairData['order_type']==2){
            $repairData['money_total'] += $repairData['difference_price'];
        }
        //获取所有客服人员
        $wechatMemberModel = new WechatMemberModel();
        $serviceArr = $wechatMemberModel->where(['isadmin'=>self::ADMIN_SERVICE,'status'=>1,'type_user'=>self::TYPE_USER_SERVICE])->select();
        @file_put_contents("pangyongfu.log","客服人员：".json_encode($serviceArr)."\r\n",FILE_APPEND);
        $serviceArrWxCodeArr = [];
        if(!empty($serviceArr)){
            foreach ($serviceArr as $v) {
                $serviceArrWxCodeArr[] = $v['wx_code'];
            }
        }
        $serviceWxCode = implode("|",$serviceArrWxCodeArr);
        $toUser = $serviceWxCode;
        @file_put_contents("pangyongfu.log","客服人员WXcode：".json_encode($serviceArr)."\r\n",FILE_APPEND);

        //发送消息类型 1客户下单后 2客户取消订单 3师傅接单后 4客户支付后 5客户验收完毕（消杀）
        switch($type){
            case 1:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "新订单提醒！";
                if($repairData['order_type'] == 1){
                    $description = "您好，您有新订单，请查看并及时操作订单分配";
                }elseif($repairData['order_type'] == 2){
                    $description = "您好，".$repairData['store_name']."店申报了（门店消杀）订单，请操作派单";
                }else{
                    $description = "您好，".$repairData['store_name']."店申报了（烟道清洗）订单，请操作派单";
                }
                if($repairData['urgent_level'] == 2){
                    $description = "您有新的紧急订单，师傅必须在2小时内上门，如超过2小时到达，只能按照普通订单标准收费，请查看并尽快分配订单";
                }
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 2://维修消杀清洗主管未处理订单
                $agentid = self::CUSTOMER_SERVICE;
                $title = "主管超时提醒！";
                $description = $repairData['store_name']."店的订单，主管超时未操作，请关注。";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 3://消杀主管和清洗主管未处理订单
            $agentid = self::CUSTOMER_SERVICE;
                $title = "师傅超时提醒！";
                $description = "您好，订单号为：".$repairData['order_code']."的【".$repairData['order_type_text']."】订单将超过2小时对方未操作，请知悉【点击查看详情】";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 4:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "售后订单提醒！";
                $description = $repairData['store_name']."店的订单已申请售后服务。";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 5:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "烟道清洗提醒！";
                $description = $repairData['store_name']."店的订单，距离烟道清洗周期还有5天，请提醒用户及时下新订单";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 6:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "烟道清洗提醒！";
                $description = $repairData['store_name']."店的订单，距离烟道清洗周期还有2天，请提醒用户及时下新订单";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 7:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "四害消杀提醒！";
                $description = $repairData['store_name']."店的订单，距离消杀周期还有5天，请提醒用户及时下新订单";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 8:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "四害消杀提醒！";
                $description = $repairData['store_name']."店的订单，距离消杀周期还有2天，请提醒用户及时下新订单";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 9:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "用户取消订单提醒！";
                $description = $repairData['store_name']."店的订单，客户已取消订单，请关注。";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 10:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "主管取消订单提醒！";
                $description = $repairData['store_name']."店的订单，主管已取消订单，请关注。";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
            case 11:
                $agentid = self::CUSTOMER_SERVICE;
                $title = "用户支付完成提醒！";
                $description = $repairData['store_name']."店的订单已支付。";
                $url = host_url . "/Enterprise/CustomerServices/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId;
                break;
        }

        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$toUser,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>''
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }

    /**
     * 给客服发送巡检消息
     * @param $orderId string 订单id
     * @param $msgType string 消息类别
     * @param $isMain string 是否是主订单
     * @return array|string
     */
    public function getCustomerInsSendMsg($orderId,$msgType,$isMain = true){

        $toUser = null;
        $description = '';
        $title = '';
        $url = '';
        if($isMain){
            //todo 获取主订单数据
            $mainModel = new WechatInspectionModel();
            $mainMap['wi.inspection_id'] = $orderId;
            $mainField = "wi.inspection_id,wi.inspection_code,wi.inspection_status,wi.create_time,wke.id enterprise_id,wke.name enterprise_name,wf.id fac_id,wf.name fac_name";
            $mainData = $mainModel->getMainInspectionOrderDetail($mainMap,$mainField);
            $mainData['inspection_status_text'] = $this->getInsMainOrderStatusText($mainData['inspection_status']);
            $description .= "工单编号：".$mainData['inspection_code']."\r\n";
            $description .= "巡检企业：".$mainData['enterprise_name']."\r\n";
            $description .= "发起时间：".$mainData['create_time']."\r\n";
            $description .= "当前状态：".$mainData['inspection_status_text']."\r\n";
        }else{
            //todo 获取子订单数据
            $childModel = new WechatInspectionStoreChildModel();
            $childMap['wisc.inspection_store_child_id'] = $orderId;
            $childField = "wisc.inspection_store_child_id,wisc.service_num,wisc.child_order_code,wisc.status,wks.name store_name";
            $childData = $childModel->getChildOrderDetail($childMap,$childField);
            $childData['order_state_text'] = $this->getOrderStatusText($childData['order_state']);
            $description .= "工单编号：".$childData['order_code']."\r\n";
            $description .= "巡检门店：".$childData['store_name']."\r\n";
            $description .= "创建时间：".$childData['create_time']."\r\n";
            $description .= "当前状态：".$childData['order_state_text']."\r\n";

            $service_num = $childData['service_num'];
        }

        //获取所有客服人员
        $wechatMemberModel = new WechatMemberModel();
        $serviceArr = $wechatMemberModel->where(['isadmin'=>self::ADMIN_SERVICE,'status'=>1,'type_user'=>self::TYPE_USER_SERVICE])->select();

        $serviceArrWxCodeArr = [];
        if(!empty($serviceArr)){
            foreach ($serviceArr as $v) {
                $serviceArrWxCodeArr[] = $v['wx_code'];
            }
        }
        $serviceWxCode = implode("|",$serviceArrWxCodeArr);
        $toUser = $serviceWxCode;
        //发送消息类型 1客户下单后 2客户取消订单 3师傅接单后 4客户支付后 5客户验收完毕（消杀）
        switch($msgType){
            case 1://客服发起巡检通知
                $title = "订单生成通知！";
                $description = "您好，【".$mainData['enterprise_name']."】的巡检工单已生成,订单号：".$mainData['inspection_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/CustomerServices/inspectionMainOrderDetail?inspection_id=".$mainData['inspection_id'];
                break;
            case 2://服务商超时未接单提醒
                $title = "订单超时提醒！";
                $description = "您好，【".$mainData['enterprise_name']."】的巡检工单，".$mainData['fac_name']."已超1小时未接单，请尽快处理，订单号：".$mainData['inspection_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/CustomerServices/inspectionMainOrderDetail?inspection_id=".$mainData['inspection_id'];
                break;
            case 3://巡检主订单被服务商接单提醒
                $title = "订单状态更新！";
                $description = "您好，【".$mainData['enterprise_name']."】的巡检工单已被".$mainData['fac_name']."接单，订单号：".$mainData['inspection_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/CustomerServices/inspectionMainOrderDetail?inspection_id=".$mainData['inspection_id'];
                break;
            case 4://子订单生成后距离服务时间只剩2天的通知
                $title = "新订单提醒！";
                $description = "您好，【".$childData['store_name']."】的第".$service_num."次巡检距约定服务时间还剩2天,请尽快提醒服务商与客户确认巡检时间，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/CustomerServices/showChildOrderDetail?inspection_store_child_id=".$childData['inspection_store_child_id'];
                break;
            case 5://巡检员完成工单时，有设备添加时所需发送的消息提醒
                $title = "设备添加提醒！";
                $description = "您好，【".$childData['store_name']."】的第".$service_num."次巡检已完成，巡检师傅提交了设备添加申请，请核实并处理，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/CustomerServices/showChildOrderDetail?inspection_store_child_id=".$childData['inspection_store_child_id'];
                break;
        }

        $param = [
            'msgtype'=> 'news',
            'agentid'=>self::CUSTOMER_SERVICE,
            'touser'=>$toUser,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>''
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }
    /**
     * 给企业主管发送巡检消息
     * @param $orderId string 订单id
     * @param $msgType string 消息类别
     * @return array|string
     */
    public function getEnterpriseInsSendMsg($orderId,$msgType){

        $toUser = null;
        $description = '';
        $title = '';
        $url = '';
        //todo 获取主订单数据
        $mainModel = new WechatInspectionModel();
        $mainMap['wi.inspection_id'] = $orderId;
        $mainField = "wi.inspection_id,wi.inspection_code,wi.inspection_status,wi.create_time,wke.id enterprise_id,wke.name enterprise_name,wf.id fac_id,wf.name fac_name";
        $mainData = $mainModel->getMainInspectionOrderDetail($mainMap,$mainField);
        $mainData['inspection_status_text'] = $this->getInsMainOrderStatusText($mainData['inspection_status']);
        $description .= "工单编号：".$mainData['inspection_code']."\r\n";
        $description .= "巡检企业：".$mainData['enterprise_name']."\r\n";
        $description .= "发起时间：".$mainData['create_time']."\r\n";
        $description .= "当前状态：".$mainData['inspection_status_text']."\r\n";


        //获取所有企业主管
        $wechatMemberModel = new WechatMemberModel();
        $serviceArr = $wechatMemberModel->where(['isadmin'=>self::ADMIN_ENTERPRISE,'status'=>1,'type_user'=>self::TYPE_USER_ENTERPRISE,'enterprise_id'=>$mainData['enterprise_id']])->select();
        $serviceArrWxCodeArr = [];
        if(!empty($serviceArr)){
            foreach ($serviceArr as $v) {
                $serviceArrWxCodeArr[] = $v['wx_code'];
            }
        }
        $serviceWxCode = implode("|",$serviceArrWxCodeArr);
        $toUser = $serviceWxCode;
        //发送消息类型 1客户下单后 2客户取消订单 3师傅接单后 4客户支付后 5客户验收完毕（消杀）
        switch($msgType){
            case 1://客服发起巡检通知
                $title = "巡检订单已接单！";
                $description = "您好，【".$mainData['enterprise_name']."】的巡检服务已生效,我们将依据巡检计划提供上门巡检服务，服务前我们将提前联系门店预约时间，订单号：".$mainData['inspection_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/BusinessExecutive/showMainOrderDetail?inspection_id=".$mainData['inspection_id'];
                break;
        }

        $param = [
            'msgtype'=> 'news',
            'agentid'=>self::ENTERPRISE_MASTER,
            'touser'=>$toUser,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>''
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }
    /**
     * 获取分配主管发消息内容
     * @param $orderId
     * @param $type
     * @return array|string
     * @author pangyongfu
     */
    public function getDistributeSendMsg($orderId,$type){

        $toUser = null;
        $description = '';
        $agentid = 0;
        $title = '';
        $url = '';
        $wechatOrderModel = new WechatOrderModel();
        $map['orde.id'] = $orderId;
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,item.difference_status,item.difference_status,item.urgent_level,
        orde.order_type,orde.money_total,member.name worker,member.uid,faci.id fid,faci.name fname";
        $repairData = $wechatOrderModel->getDataByOrderId($map,$field);
        $repairData = $repairData[0];
        $repairData['order_type_text'] = $repairData['order_type']==1?"门店维修":($repairData['order_type']==2?"门店消杀":"门店清洗");

        $repairData['order_state_text'] = $this->getOrderStatusText($repairData['order_state']);
        $description .= "工单编号：".$repairData['order_code']."\r\n";
        $description .= "报修门店：".$repairData['store_name']."\r\n";
        $description .= "报修时间：".$repairData['create_time']."\r\n";
        $description .= "当前状态：".$repairData['order_state_text']."\r\n";
        if($repairData['order_type']==2 && $repairData['order_type']==2){
            $repairData['money_total'] += $repairData['difference_price'];
        }
        //获取所有分配主管人员
        $wechatMemberModel = new WechatMemberModel();
        $serviceArr = $wechatMemberModel->where(['isadmin'=>self::ADMIN_DISTRIBUTE,'status'=>1,'facilitator_id'=>$repairData['fid']])->select();

        $serviceArrWxCodeArr = [];
        if(!empty($serviceArr)){
            foreach ($serviceArr as $v) {
                $serviceArrWxCodeArr[] = $v['wx_code'];
            }
        }
        $serviceWxCode = implode("|",$serviceArrWxCodeArr);
        $toUser = $serviceWxCode;
        //发送消息类型 1客户下单后 2客户取消订单 3师傅接单后 4客户支付后 5客户验收完毕（消杀）
        switch($type){
            case 1:
                $title = "新订单提醒！";
                $description = "您好，您有新订单，请查看并及时操作订单分配";
                //紧急订单
                if($repairData['urgent_level'] == 2){
                    $description = "您有新的紧急订单，师傅必须在2小时内上门，如超过2小时到达，只能按照普通订单标准收费，请查看并尽快分配订单";
                }
                $url = host_url . "/Enterprise/DistributionSupervisor/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId."&fid=".$repairData['fid'];
                if($repairData['order_state'] == 11){
                    $url = host_url . "/Enterprise/DistributionSupervisor/showCleanKillYearOrder?order_id=".$orderId;
                }
                break;
            case 2:
                $title = "用户取消订单提醒！";
                $description = $repairData['store_name'] . "店已取消订单，请向餐讯网客服反馈取消原因。";
                $url = host_url . "/Enterprise/DistributionSupervisor/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId."&fid=".$repairData['fid'];;
                break;
            case 3://订单被客服派单给主管
                $title = "订单状态超时提醒！";
                $description = $repairData['store_name'] . "店的订单未操作派单，请您尽快操作，特殊原因请联系客服重新分配，以免您企业综合评分受损；";
                $url = host_url . "/Enterprise/DistributionSupervisor/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId."&fid=".$repairData['fid'];;
                break;
            case 4://订单被主管派单给师傅
                $title = "订单状态超时提醒！";
                $description = "您分配的".$repairData['store_name']."店订单，师傅未操作接单，请尽快提醒师傅操作接单或改派其他师傅，以免您企业综合评分受损。";
                $url = host_url . "/Enterprise/DistributionSupervisor/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId."&fid=".$repairData['fid'];;
                break;
            case 5:
                $title = "售后订单提醒！";
                if($repairData['order_type']==1){
                    $description = $repairData['store_name']."店的订单，用户已申请售后服务，请联系维修师傅并及时处理订单。";
                }elseif($repairData['order_type']==2){
                    $description = $repairData['store_name']."店的订单已申请售后服务，请联系消杀师傅并及时处理。";
                }else{
                    $description = $repairData['store_name']."店的订单已申请售后服务，请关注。";
                }
                $url = host_url . "/Enterprise/DistributionSupervisor/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId."&fid=".$repairData['fid'];
                break;
            case 6://清洗和消杀订单被主管派单给师傅1小时30分钟后
                $title = "用户支付完成提醒！";
                $description = $repairData['store_name']."店的订单客户已完成支付。";
                $url = host_url . "/Enterprise/DistributionSupervisor/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId."&fid=".$repairData['fid'];;
                break;
//            case 7://消杀主管接单后
//                $title = "订单状态提醒！";
//                $url = host_url . "/Enterprise/DistributionSupervisor/orderDetail?type=".$repairData['order_type']."&order_id=".$orderId."&fid=".$repairData['fid'];
//                $description = "您好，订单号：".$repairData['order_code']."已操作接单，请您尽快进行处理，以免引起客诉";
//                break;
            case 8://维修和清洗主管接单后
                $title = "订单状态提醒！";
                if($repairData['order_type'] == 1){
                    $text = "维修师傅端";
                    $url = host_url . "/Enterprise/DistributionSupervisor/maintainOrderDetail?order_id=".$orderId;
                }else{
                    $text = "清洗师傅端";
                    $url = host_url . "/Enterprise/DistributionSupervisor/cleaningOrderDetail?order_id=".$orderId;
                }
                $description = "您好，订单号：".$repairData['order_code']."已操作接单，请到".$text."查看详情并进行其他操作";
                break;
        }

        $agentid = self::DISTRIBUTE_MANAGER;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$toUser,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>''
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }

    /**
     * 给客服发送巡检消息
     * @param $orderId string 订单id
     * @param $msgType string 消息类别
     * @param $isMain string 是否是主订单
     * @return array|string
     */
    public function getDistributeInsSendMsg($orderId,$msgType,$isMain = true){

        $toUser = null;
        $description = '';
        $title = '';
        $url = '';
        if($isMain){
            //todo 获取主订单数据
            $mainModel = new WechatInspectionModel();
            $mainMap['wi.inspection_id'] = $orderId;
            $mainField = "wi.inspection_id,wi.inspection_code,wi.inspection_status,wi.create_time,wke.id enterprise_id,wke.name enterprise_name,wf.id fac_id,wf.name fac_name";
            $mainData = $mainModel->getMainInspectionOrderDetail($mainMap,$mainField);
            $mainData['inspection_status_text'] = $this->getInsMainOrderStatusText($mainData['inspection_status']);
            $description .= "工单编号：".$mainData['inspection_code']."\r\n";
            $description .= "巡检企业：".$mainData['enterprise_name']."\r\n";
            $description .= "发起时间：".$mainData['create_time']."\r\n";
            $description .= "当前状态：".$mainData['inspection_status_text']."\r\n";
            $facilitator_id = $mainData['fac_id'];
        }else{
            //todo 获取子订单数据
            $childModel = new WechatInspectionStoreChildModel();
            $childMap['wisc.inspection_store_child_id'] = $orderId;
            $childField = "wisc.inspection_store_child_id,wisc.service_num,wisc.child_order_code,wisc.facilitator_id,wisc.status,wks.name store_name";
            $childData = $childModel->getChildOrderDetail($childMap,$childField);
            $childData['order_state_text'] = $this->getOrderStatusText($childData['order_state']);
            $description .= "工单编号：".$childData['order_code']."\r\n";
            $description .= "巡检门店：".$childData['store_name']."\r\n";
            $description .= "创建时间：".$childData['create_time']."\r\n";
            $description .= "当前状态：".$childData['order_state_text']."\r\n";
            $facilitator_id = $childData['facilitator_id'];

            $service_num = $childData['service_num'];
        }
        //获取所有分配主管人员
        $wechatMemberModel = new WechatMemberModel();
        $serviceArr = $wechatMemberModel->where(['isadmin'=>self::ADMIN_DISTRIBUTE,'status'=>1,'facilitator_id'=>$facilitator_id])->select();

        $serviceArrWxCodeArr = [];
        if(!empty($serviceArr)){
            foreach ($serviceArr as $v) {
                $serviceArrWxCodeArr[] = $v['wx_code'];
            }
        }
        $serviceWxCode = implode("|",$serviceArrWxCodeArr);
        $toUser = $serviceWxCode;
        //发送消息类型 1客户下单后 2客户取消订单 3师傅接单后 4客户支付后 5客户验收完毕（消杀）
        switch($msgType){
            case 1://客服发起巡检通知服务商
                $title = "新订单提醒！";
                $description = "您好，您有【".$mainData['enterprise_name']."】巡检新工单,订单号：".$mainData['inspection_code']."，请查看并及时操作订单分配【点击查看详情】";
                $url = host_url . "/Enterprise/DistributionSupervisor/inspectionMainOrderDetail?inspection_id=".$mainData['inspection_id'];
                break;
            case 2://服务商超时未接单提醒
                $title = "订单超时提醒！";
                $description = "您好，您有【".$mainData['enterprise_name']."】巡检新工单，已超1小时未接单，请尽快处理，订单号：".$mainData['inspection_code']."，请查看并及时操作订单分配【点击查看详情】";
                $url = host_url . "/Enterprise/DistributionSupervisor/inspectionMainOrderDetail?inspection_id=".$mainData['inspection_id'];
                break;
            case 3://巡检子订单生成提醒(首次子订单生成)
                $title = "子订单生成通知！";
                $description = "您好，【".$childData['store_name']."】的第".$service_num."次巡检工单已生成，请尽快分配巡检师傅，并与门店约定巡检时间，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/DistributionSupervisor/inspectionChildOrderDetail?inspection_store_child_id=".$childData['inspection_store_child_id'];
                break;
            case 4://子订单1小时未接单提醒(首次子订单生成)
                $title = "订单超时提醒！";
                $description = "您好，【".$childData['store_name']."】的第".$service_num."次巡检工单分配已超时，请尽快分配巡检师傅，并与门店约定巡检时间，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/DistributionSupervisor/inspectionChildOrderDetail?inspection_store_child_id=".$childData['inspection_store_child_id'];
                break;
            case 5://新订单提醒(后续子订单生成)
                $title = "新订单提醒！";
                $description = "您好，【".$childData['store_name']."】的第".$service_num."次巡检工单已生成，如需更换师傅，请点击操作，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/DistributionSupervisor/inspectionChildOrderDetail?inspection_store_child_id=".$childData['inspection_store_child_id'];
                break;
            case 6://门店确认订单后
                $title = "订单完成通知！";
                $description = "您好，【".$childData['store_name']."】的第".$service_num."次巡检工单已完成，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/DistributionSupervisor/inspectionChildOrderDetail?inspection_store_child_id=".$childData['inspection_store_child_id'];
                break;
        }

        $agentid = self::DISTRIBUTE_MANAGER;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$toUser,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>''
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }
    /**
     * 获取师傅发消息内容
     * @param $repairId
     * @param $type
     * @return array|string
     * @author pangyongfu
     */
    public function getMasterSendMsg($orderId,$type){

        $toUser = null;
        $description = '';
        $agentid = 0;
        $title = '';
        $url = '';
        $wechatOrderModel = new WechatOrderModel();
        $map['orde.id'] = $orderId;
        $field = "orde.id,orde.order_code,orde.order_state,orde.create_time,orde.store_name,orde.is_year,item.difference_status,item.difference_status,item.urgent_level,
        orde.order_type,orde.money_total,member.name worker,member.uid,faci.id fid,faci.name fname";
        $repairData = $wechatOrderModel->getDataByOrderId($map,$field);
        $repairData = $repairData[0];
        log::write("订单数据：".json_encode($repairData));
        $repairData['order_type_text'] = $repairData['order_type']==1?"门店维修":($repairData['order_type']==2?"门店消杀":"门店清洗");

        $repairData['order_state_text'] = $this->getOrderStatusText($repairData['order_state']);
        $description .= "工单编号：".$repairData['order_code']."\r\n";
        $description .= "报修门店：".$repairData['store_name']."\r\n";
        $description .= "报修时间：".$repairData['create_time']."\r\n";
        $description .= "当前状态：".$repairData['order_state_text']."\r\n";
        if($repairData['order_type']==2 && $repairData['order_type']==2){
            $repairData['money_total'] += $repairData['difference_price'];
        }
        //获取所有客服人员
        $wechatMemberModel = new WechatMemberModel();
        $serviceArr = $wechatMemberModel->where(['isadmin'=>self::ADMIN_NOMAL,'status'=>1,'uid'=>$repairData['uid']])->select();

        $serviceArrWxCodeArr = [];
        if(!empty($serviceArr)){
            foreach ($serviceArr as $v) {
                $serviceArrWxCodeArr[] = $v['wx_code'];
            }
        }
        $serviceWxCode = implode("|",$serviceArrWxCodeArr);
        $toUser = $serviceWxCode;
        //发送消息类型 1客户下单后 2客户取消订单 3师傅接单后 4客户支付后 5客户验收完毕（消杀）
        switch($type){
            case 1:
                $title = "新工单提醒！";
                $description = "您好，您有新订单，请尽快与用户电话联系，并操作接单";
                if($repairData['is_year'] == 1){
                    $description = $repairData['store_name']."店的订单还有1天时间上门服务，请与客户确认上门时间。";
                }
                if($repairData['urgent_level'] == 2){
                    $description = "您有新的🔥紧急订单，您必须在2小时内上门，如超过2小时到达，只能按照普通订单标准收费，请尽快与用户电话联系，并接单。";
                }
                break;
            case 2://订单超过一定时间师傅未接单
                $title = "订单状态超时提醒！";
                $description = $repairData['store_name']."店的订单未操作接单，请您尽快操作，特殊原因请联系您的订单分配主管，以免您企业综合评分受损；";
                break;
            case 3://消杀师傅接单后
                $title = "订单状态提醒！";
                $description = "您好，订单号：".$repairData['order_code']."已操作接单，请在1小时内与用户取得联系，以免引起客诉【点击查看详情】";
                break;
            case 4://消杀和清洗订单超过1小时20分钟师傅未接单
                $title = "订单状态提醒！";
                $description = "您好，订单号：".$repairData['order_code']."已操作接单，请点击查看详情并进行其他操作【点击查看详情】";
                break;
            case 5://用户支付超时
                $title = "用户支付超时提醒！";
                $description = "您服务的".$repairData['store_name']."店的订单，用户未完成支付，请您与用户电话联系，请用户尽快支付。";
                break;
            case 6://消杀完成差价支付
                $title = "订单状态提醒！";
                $description = "您好，您服务的订单号：".$repairData['order_code']."，用户已完成差价支付【点击查看详情】";
                break;
            case 7://清洗和维修完成支付
                $title = "订单状态提醒！";
                $description = "您好，您服务的".$repairData['store_name']."店订单，用户已完成支付。";
                break;
            case 8:
                $title = "售后工单提醒！";
                $description = "您服务的".$repairData['store_name']."店，用户已申请售后服务，请尽快与用户电话联系。";
                break;
        }
        //1门店维修，2门店消杀，3烟道清洗
        if($repairData['order_type'] == 1){
            $agentid = self::REPAIRE_MASTER;
            $url = host_url . "/Enterprise/StoreMaintain/orderDetail?order_id=".$orderId;
        }elseif($repairData['order_type'] == 2){
            $agentid = self::CLEANKILL_MASTER;
            $url = host_url . "/Enterprise/StoreCleanKill/orderDetail?order_id=".$orderId;
        }else{
            $agentid = self::CLEANING_MASTER;
            $url = host_url . "/Enterprise/StoreCleaning/orderDetail?order_id=".$orderId;
        }
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$toUser,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>''
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }
    /**
     * 获取师傅发消息内容
     * @param $orderId
     * @param $type
     * @param $day
     * @return array|string
     * @author pangyongfu
     */
    public function getMasterInsSendMsg($orderId,$type,$day = 0){

        $toUser = null;
        $description = '';
        $title = '';
        $url = '';
        //todo 获取子订单数据
        $childModel = new WechatInspectionStoreChildModel();
        $childMap['wisc.inspection_store_child_id'] = $orderId;
        $childField = "wisc.inspection_store_child_id,wisc.service_num,wisc.child_order_code,wisc.facilitator_id,wisc.inspector_id,wisc.status,wks.name store_name";
        $childData = $childModel->getChildOrderDetail($childMap,$childField);
        $childData['order_state_text'] = $this->getOrderStatusText($childData['order_state']);
        $description .= "工单编号：".$childData['order_code']."\r\n";
        $description .= "巡检门店：".$childData['store_name']."\r\n";
        $description .= "创建时间：".$childData['create_time']."\r\n";
        $description .= "当前状态：".$childData['order_state_text']."\r\n";

        //获取所有巡检人员
        $wechatMemberModel = new WechatMemberModel();
        $serviceArr = $wechatMemberModel->where(['isadmin'=>self::ADMIN_NOMAL,'status'=>1,'uid'=>$childData['inspector_id']])->select();

        $serviceArrWxCodeArr = [];
        if(!empty($serviceArr)){
            foreach ($serviceArr as $v) {
                $serviceArrWxCodeArr[] = $v['wx_code'];
            }
        }
        $serviceWxCode = implode("|",$serviceArrWxCodeArr);
        $toUser = $serviceWxCode;
        $service_num = $childData['service_num'];
        //发送消息类型 1客户下单后 2客户取消订单 3师傅接单后 4客户支付后 5客户验收完毕（消杀）
        switch($type){
            case 1://服务商主管派单后
                $title = "新订单提醒！";
                $description = "您好，您有【".$childData['store_name']."】巡检新工单,请尽快与用户电话联系确认巡检时间，并操作接单，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/StoreMaintain/inspectionOrderDetail?inspectionStoreChildId=".$childData['inspection_store_child_id'];
                break;
            case 2://服务商主管派单后超时未接单提醒
                $title = "订单超时提醒！";
                $description = "您好，您有【".$childData['store_name']."】巡检新工单，超时未接单，请尽快与用户电话联系确认巡检时间，并操作接单，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/StoreMaintain/inspectionOrderDetail?inspectionStoreChildId=".$childData['inspection_store_child_id'];
                break;
            case 3://巡检子订单生成提醒(后续子订单生成)
                $title = "新订单提醒！";
                $description = "您好，【".$childData['store_name']."】的第".$service_num."次巡检工单已生成，请尽快与用户电话联系确认巡检时间，订单号：".$childData['child_order_code']."【点击查看详情】";
                $url = host_url . "/Enterprise/StoreMaintain/inspectionOrderDetail?inspectionStoreChildId=".$childData['inspection_store_child_id'];
                break;
            case 4://还剩3天（2天、1天）(后续子订单生成)
                $title = "订单服务提醒！";
                $description = "您好，【".$childData['store_name']."】的第".$service_num."次巡检工单距约定服务时间还剩".$day."天，请尽快与用户电话联系确认巡检时间，订单号：".$childData['child_order_code']."【点击查看详情】";
//				$url = host_url . "/Enterprise/StoreMaintain/inspectionOrderDetail?inspection_store_child_id=".$childData['inspection_store_child_id'];
				$url = host_url . "/Enterprise/StoreMaintain/inspectionOrderDetail?inspectionStoreChildId=".$childData['inspection_store_child_id'];
                break;
        }
        $agentid = self::REPAIRE_MASTER;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$toUser,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>''
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }
    //给主管发送图文消息
    public function sendPicTextMsg($wxCode,$order_type,$title,$description,$url,$pic = ''){

        //1门店维修，2门店消杀，3烟道清洗
        $agentid = self::DISTRIBUTE_MANAGER;

        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>$pic
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);
        $this->sendMessage($param);
    }
    //给客服发送图文消息
    public function sendCustomerPicTextMsg($wxCode,$order_type,$title,$description,$url,$pic = ''){

        //1门店维修，2门店消杀，3烟道清洗
        $agentid = self::CUSTOMER_SERVICE;

        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>$pic
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);
        $this->sendMessage($param);
    }

    /**
     * 紧急订单发送客服
     * @param $wxCode
     * @param $orderId
     * @param $orderCode
     * @param $facilitatorName
     */
    public function emergencyOrderSendMsg($wxCode,$orderId,$orderCode,$facilitatorName){
        //1门店维修，2门店消杀，3烟道清洗
        $agentid = self::CUSTOMER_SERVICE;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>'订单超时提醒',
                        'description'=>'【紧急订单】"'.$facilitatorName.'"服务商已超过10分钟未接单，请催促服务商接单，并尽快安抚门店，订单号："'.$orderCode.'"',
                        'url'=>host_url . "/Enterprise/CustomerServices/orderDetail?type=1&order_id=".$orderId,
                    ]
                ]
            ]
        ];
        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }

    /**
     * 紧急订单发送客服
     * @param $wxCode
     * @param $orderId
     * @param $orderCode
     * @param $facilitatorName
     */
    public function emergencyOrderWorkerSendMsg($wxCode,$orderId,$orderCode,$facilitatorName){
        //1门店维修，2门店消杀，3烟道清洗
        $agentid = self::CUSTOMER_SERVICE;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>'订单超时提醒',
                        'description'=>'【紧急订单】"'.$facilitatorName.'"服务商的师傅已超过10分钟未接单，请催促服务商接单，并尽快安抚门店，订单号："'.$orderCode.'"',
                        'url'=>host_url . "/Enterprise/CustomerServices/orderDetail?type=1&order_id=".$orderId,
                    ]
                ]
            ]
        ];
        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }

    /**
     * 紧急订单-推送服务商
     * @param $wxCode
     * @param $orderId
     * @param $orderCode
     * @return array|string
     */
    public function sendFacilitatorMsg($wxCode,$orderId,$orderCode,$fid){
        //1门店维修，2门店消杀，3烟道清洗
        $agentid = self::DISTRIBUTE_MANAGER;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>'订单超时提醒',
                        'description'=>'【紧急订单】您已超过10分钟未接单，请尽快接单并分配维修师傅，订单号："'.$orderCode.'"',
                        'url'=> host_url . "/Enterprise/DistributionSupervisor/orderDetail/order_id/".$orderId."/type/1/fid/".$fid,
                    ]
                ]
            ]
        ];
        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }


    /**
     * 紧急订单-推送服务商
     * @param $wxCode
     * @param $orderId
     * @param $orderCode
     * @return array|string
     */
    public function sendFacilitatorWorkerMsg($wxCode,$orderId,$orderCode,$fid){
        //1门店维修，2门店消杀，3烟道清洗
        $agentid = self::DISTRIBUTE_MANAGER;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>'订单超时提醒',
                        'description'=>'【紧急订单】您的师傅已超过10分钟未接单，请尽快与师傅联系，订单号："'.$orderCode.'"',
                        'url'=> host_url . "/Enterprise/DistributionSupervisor/orderDetail/order_id/".$orderId."/type/1/fid/".$fid,
                    ]
                ]
            ]
        ];
        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }


    public function sendFacilitatorMsgOver($wxCode,$id,$orderCode,$store_name,$service_num){
        //1门店维修，2门店消杀，3烟道清洗
        $agentid = self::DISTRIBUTE_MANAGER;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>'订单完成通知',
                        'description'=>'"'.$store_name.'"第"'.$service_num.'"次巡检已完成，订单号："'.$orderCode.'"，点击查看详情',
                        'url'=> host_url . "/Enterprise/Distribution_supervisor/inspectionChildOrderDetail/inspection_store_child_id/".$id,
                    ]
                ]
            ]
        ];
        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }

    /**
     * 师傅端 十分钟未接单
     */
    public function workerTenMinutesNoOrder($wxCode,$orderId,$orderCode){
        $agentid = self::REPAIRE_MASTER;
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>'订单超时提醒',
                        'description'=>'【紧急订单】您的师傅已超过10分钟未接单，请尽快与师傅联系，订单号："'.$orderCode.'"',
                        'url'=> host_url . "/Enterprise/StoreMaintain/orderDetail?order_id=".$orderId
                    ]
                ]
            ]
        ];
        $param = json_encode($param,JSON_UNESCAPED_UNICODE);

        return $param;
    }

    /**
     * 给巡检主管发送延迟一小时未派单的提醒
     * @param $data
    *         [inspection_store_child_id] => 1
              [service_num] => 1
              [child_order_code] => 11223
              [update_time] => 2018-08-30 19:03:45
              [storeName] => 局气（五道口）
              [wx_code] => huangqing
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionSupervisorDelayOneHourMsg($data){

        $url = host_url.'/index.php?s=/Enterprise/DistributionSupervisor/inspectionChildOrderDetail/inspection_store_child_id/'.$data['inspection_store_child_id'];
        $remark = ''.$data['storeName'].'第'.$data['service_num'].'次巡检订单分配已超时，请尽快分配巡检师傅，并与门店约定巡检时间，订单号：'.$data['child_order_code'].'，点击查看详情。';
        $title = '订单超时提醒';
        $this->sendPicTextMsg($data['wx_code'],'',$title,$remark,$url);
    }

    /**
     * 给巡检主管发送接单（子订单）成功提醒
     * @param $data
    *         [inspection_store_child_id] => 1
              [service_num] => 1
              [child_order_code] => 11223
              [update_time] => 2018-08-30 19:03:45
              [storeName] => 局气（五道口）
              [wx_code] => huangqing
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionSupervisorTakeOrdersMsg($data){

        $url = host_url."/index.php?s=/Enterprise/StoreMaintain/inspectionOrderDetail/inspectionStoreChildId/".$data['inspection_store_child_id'];

        $remark = ''.$data['storeName'].'第'.$data['service_num'].'次巡检订单已接单，订单号：'.$data['child_order_code'].'，点击查看详情';
        $title = '接单成功提醒';
        $this->sendPicTextMsg($data['wx_code'],'',$title,$remark,$url);
    }


    /**
     * 给巡检员发送延迟一小时未接单的提醒
     * @param $data
     *         [inspection_store_child_id] => 1
    [service_num] => 1
    [child_order_code] => 11223
    [update_time] => 2018-08-30 19:03:45
    [storeName] => 局气（五道口）
    [wx_code] => huangqing
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionWorkerDelayOneHourMsg($data){

        $url = host_url.'/index.php?s=/Enterprise/StoreMaintain/inspectionOrderDetail/inspectionStoreChildId/'.$data['inspection_store_child_id'];
        $remark = '您有'.$data['storeName'].'店【门店巡检】新订单，超时未接单，请尽快与用户电话联系确认巡检时间，并操作接单，订单号：'.$data['child_order_code'].'，点击查看详情';
        $title = '订单超时提醒';
        $this->sendPicTextMsg($data['wx_code'],'',$title,$remark,$url);
    }

    /**
     * 给客服发送 巡检员添加设备提醒
     * @param $data
     *         [inspection_store_child_id] => 1
               [wx_code] => PangYongFu
               [storeName] => 局气（五道口）
               [service_num] => 1
               [child_order_code] => 11223
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionNewDeviceMsg($data)
    {
        $url = host_url.'/index.php?s=/Enterprise/customerServices/confirmDeviceList/inspection_store_child_id/'.$data['inspection_store_child_id'];
        $remark = "".$data['storeName']."第".$data['service_num']."次巡检已完成，订单号：".$data['child_order_code']."，点击查看详情";
        $title = '门店新设备提醒';
        $this->sendCustomerPicTextMsg($data['wx_code'],'',$title,$remark,$url);
    }


    /**
     * 发送巡检备注消息
     * @param $inspectionStoreChildId 子订单标识
     * @param $uid 用户标识
     * @param $type 1：给巡检主管发 2：给巡检员发
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionRequirementsMsg($inspectionStoreChildId,$uid,$type,$storeId){

        $url = '';
        switch ($type)
        {
            //巡检主管
            case 1:
                $url = host_url.'/index.php?s=/Enterprise/DistributionSupervisor/inspectionChildOrderDetail/inspection_store_child_id/'.$inspectionStoreChildId;
                break;
            //巡检员
            case 2:
                $url = host_url.'/index.php?s=/Enterprise/StoreMaintain/inspectionOrderDetail/inspectionStoreChildId/'.$inspectionStoreChildId;
                break;
        }

        //查询用户信息数据
        $memInfo = (new WechatMemberModel())->where(['uid'=>$uid])->field('wx_code')->find();

        //查询门店信息
        $storeInfo = (new WechatKaStoresModel())->where(['id'=>$storeId])->field('name')->find();

        $remark = "".$storeInfo['name']."门店为本次巡检添加了巡检备注，请点击前往订单详情查看。";
        $title = '巡检备注提醒';
        $this->sendInspectionRequirementsMsgText($memInfo['wx_code'],$type,$title,$remark,$url);
    }

    /**
     * 发送巡检备注消息
     * @param $wxCode
     * @param $type
     * @param $title
     * @param $description
     * @param $url
     * @param string $pic
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendInspectionRequirementsMsgText($wxCode,$type,$title,$description,$url,$pic = ''){

        $agentid = '';
        //1：巡检主管 2：巡检员
        switch($type){
            case 1:
                $agentid = self::DISTRIBUTE_MANAGER;
                break;
            case 2:
                $agentid = self::REPAIRE_MASTER;
                break;
        }
        $param = [
            'msgtype'=> 'news',
            'agentid'=>$agentid,
            'touser'=>$wxCode,
            'news'=> [
                'articles'=>[
                    [
                        'title'=>$title,
                        'description'=>$description,
                        'url'=>$url,
                        'picurl'=>$pic
                    ]
                ]
            ]
        ];

        $param = json_encode($param,JSON_UNESCAPED_UNICODE);
        $this->sendMessage($param);
    }

}

?>