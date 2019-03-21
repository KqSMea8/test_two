<?php

/**
 * 短信接口
 * @author syn
 */
namespace Common\Api;

use Common\Lib\YiMeiSMS;
use Think\Log;

class SMSService {

	public $sms;

	public function __construct()
    {
		$this -> sms = new YiMeiSMS();
	}

    /**
     * 发短信接口
     *
     * @param string $phone
     *        	要发送的手机号，多个用逗号隔开
     * @param string $content
     *        	发送内容
     * @param string $type
     *        	发送原因，如'注册验证','取餐提示','重置密码'
     * @return array
     * @author  sunyn
     * email: sunyn@jpgk.com.cn
     * QQ:743669853
     */
	public function sendMessage($phone, $content, $type = '') {

		if (empty ( $phone ) || empty ( $content )) {

			return [
					'status' => 0,
					'info' => '参数传递错误！' 
			];
		}

		$re = $this->sms->sendSMS ( $phone, $content );

        //短信盗用预警 记录五分钟内php发送的短信条数
        if($re)
        {
            $redisPhpSmsNum = 'MonitorLogicMonitorEmbezzleSmsOrNotPhpSendSmsNum';
            $redis = getRedisClient();

            //判断redis中是否存在key 存在的话在原值上加1 反之创建key 默认值为1
            if($redis -> exists($redisPhpSmsNum))
            {
                $redis -> INCRBY($redisPhpSmsNum , 1);
            }
            else
            {
                $redis -> set($redisPhpSmsNum , 1);
                $nowMinute = substr(date('i'), -1);
                $nowSecond = date('s');
                //如果当前时间分钟个位小于5 生存时间
                if($nowMinute < 5)
                {
                    $redis -> EXPIRE($redisPhpSmsNum,(4 - $nowMinute) * 60 + (60 - $nowSecond));
                }
                else
                {
                    $redis -> EXPIRE($redisPhpSmsNum,(9 - $nowMinute) * 60 + (60 - $nowSecond));
                }
            }
        }

		// 把短信记录添加到日志中
		$time = date ( 'Y-m-d H:i:s', time () );
		$phoneArr = explode ( ',', $phone );
		foreach ( $phoneArr as $k => $v ) {
			$data [$k] ['phone'] = $v;
			$data [$k] ['content'] = $content;
			$data [$k] ['type'] = $type;
			$data [$k] ['ctime'] = $time;
            $data [$k] ['member_id'] = session('memId') ? session('memId') : 0;
            $data [$k] ['reason'] = $re ['info'];
			$data [$k] ['status'] = $re ['status'];
		}
		if (count ( $phoneArr ) == 1) {
			M('LogSms')->add( $data [0] );
		} else {
            M('LogSms')->add( $data );
		}
		return $re;
	}


	/**
	 * 查询余额
	 */
	public function LingXunTongSelectSelSum() {
		$res = $this->sms->selectSelNum ();
		return $res;
	}


    /**
     * 控制发短信（一个ip一天只发20条短信，一个手机号一天只发5条短信）
     * @param int $phone 手机号
     * @throws \Common\Cls\WrapException
     * @author  ljh
     * email: ljh@jpgk.com.cn
     * QQ:419168324
     */
    public function controlSendMessage($phone)
    {

        $ip = $this->getVisitorIp();
        $ipKey = 'SMS:ServiceControlSendMessage'.$ip;
        $phoneKey = 'SMS:ServiceControlSendMessage'.$phone;
        $redis = getRedisClient();

        //一个手机号一天只发5条短信
        if($redis -> INCRBY($phoneKey,1) == 1)
        {
            $redis->EXPIRE($phoneKey,86400);
        }
        else
        {
            if($redis->get($phoneKey) > 10)
            {
                throw_exception('一个手机号一天只发10条短信');
            }
        }

        //一个ip一天只发20条短信
        if($redis -> INCRBY($ipKey,1) == 1)
        {
            $redis->EXPIRE($ipKey,86400);

        }
        else
        {
            if($redis->get($ipKey) >200)
            {
                throw_exception('一个ip一天只发200条短信');
            }
        }
    }

    /**
     * 获取访问者的IP
     * @author  QingHuang.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getVisitorIp()
    {
        if (isset($_SERVER))
        {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            }
            else if (isset($_SERVER["HTTP_CLIENT_IP"]))
            {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            }
            else
            {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        }
        else
        {
            if (getenv("HTTP_X_FORWARDED_FOR"))
            {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            }
            else if (getenv("HTTP_CLIENT_IP"))
            {
                $realip = getenv("HTTP_CLIENT_IP");
            }
            else
            {
                $realip = getenv("REMOTE_ADDR");
            }
        }

        return $realip;
    }

    /**
     * 获取短信的剩余条数
     * @author  HuangQing
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getBalanceOfSmsChannel()
    {
        $return = $this->sms->selectSelNum();
        $temp = $return['data'] * 10;
        return $temp;
    }

    public function sendBindSms($phone)
    {
        if(APP_STATUS == 'config_production' || APP_STATUS == 'config_test')
        {
            //发短信控制
            $this -> controlSendMessage($phone);
        }

        $redis = getRedisClient();

        $phoneKey = 'SMS:getRegisterVerifyCode'.$phone;

        //一分钟只给用户发一次短信
        if($redis ->INCRBY($phoneKey, 1) == 1)
        {
            $redis->EXPIRE($phoneKey,60);
        }
        else
        {
           throwErrMsg('一分钟只能获取一次验证码');
        }

        //控制时间key
        $identityRegisterVerifyCodeKey = 'SMS:identityRegisterVerifyCodeKey'.$phone;

        //存验证码key
        $registerVerifyCodeKey = 'SMS:registerVerifyCodeKey'.$phone;

        //30分钟内发相同验证码
        if($redis ->INCRBY( $identityRegisterVerifyCodeKey, 1 ) == 1){

            $redis->EXPIRE($identityRegisterVerifyCodeKey, 1800);
            $registerVerifyCode= mt_rand ( 0, 9 ) . mt_rand ( 0, 9 ) . mt_rand ( 0, 9 ) . mt_rand ( 0, 9 );
            $redis ->set( $registerVerifyCodeKey, $registerVerifyCode);
            $redis->EXPIRE($registerVerifyCodeKey, 1800);
        }else{

            $registerVerifyCode = $redis ->get( $registerVerifyCodeKey) ;
        }
        session('registerVerifyCode',$registerVerifyCode);
        $verifyCodeData = $this -> sendMessage($phone, "【嘉和一品】您正在绑定嘉和卡，验证码是：" .$registerVerifyCode.'。','bindCard' );
        return $verifyCodeData;
    }

    /**
     * 绑定手机发短信
     * @param $phone
     * @return array
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendBindPhoneSms($phone)
    {
        if(APP_STATUS == 'config_production' || APP_STATUS == 'config_test')
        {
            //发短信控制
//            $this -> controlSendMessage($phone);
        }

        $redis = getRedisClient();

        $phoneKey = 'SMS:bindPhone_'.$phone;

        //一分钟只给用户发一次短信
        if($redis ->INCRBY($phoneKey, 1) == 1)
        {
            $redis->EXPIRE($phoneKey,60);
        }
        else
        {
            throw_exception('一分钟只能获取一次验证码');
        }

        //控制时间key
        $identityRegisterVerifyCodeKey = 'SMS:identityPhoneVerifyCodeKey'.$phone;

        //存验证码key
        $registerVerifyCodeKey = 'SMS:bindPhoneVerifyCodeKey'.$phone;

        //30分钟内发相同验证码
        if($redis ->INCRBY( $identityRegisterVerifyCodeKey, 1 ) == 1){

            $redis->EXPIRE($identityRegisterVerifyCodeKey, 1800);
            $registerVerifyCode= mt_rand ( 0, 9 ) . mt_rand ( 0, 9 ) . mt_rand ( 0, 9 ) . mt_rand ( 0, 9 );
            $redis ->set( $registerVerifyCodeKey, $registerVerifyCode);
            $redis->EXPIRE($registerVerifyCodeKey, 1800);
        }else{

            $registerVerifyCode = $redis ->get( $registerVerifyCodeKey) ;
        }

        session('verifyCode',$registerVerifyCode);

        log::write('验证码session设置',session('verifyCode'));

        $verifyCodeData = $this -> sendMessage($phone, '【餐讯网】'.$registerVerifyCode.'（动态验证码）。餐讯网工作人员不会向您索要此验证码。将验证码泄露给他人，可能造成账号被盗。','bindPhone' );
        return $verifyCodeData;
    }

    /**
     * 餐讯网微信端使用
     * @param $phone
     * @param $content
     * @return array
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function sendSmsForCx($phone,$content){

        if (empty ( $phone ) || empty ( $content )) {

            return [
                'status' => 0,
                'info' => '参数传递错误！'
            ];
        }

        $re = $this->sms->sendSMS ( $phone, $content );


        // 把短信记录添加到日志中
//        $time = date ( 'Y-m-d H:i:s', time () );
//        $phoneArr = explode ( ',', $phone );
//        foreach ( $phoneArr as $k => $v ) {
//            $data [$k] ['phone'] = $v;
//            $data [$k] ['content'] = $content;
//            $data [$k] ['type'] = $type;
//            $data [$k] ['ctime'] = $time;
//            $data [$k] ['member_id'] = session('userInfo.custId') ? session('userInfo.custId') : 0;
//            $data [$k] ['reason'] = $re ['info'];
//            $data [$k] ['status'] = $re ['status'];
//        }
//        if (count ( $phoneArr ) == 1) {
//            M('LogSms')->add( $data [0] );
//        } else {
//            M('LogSms')->add( $data );
//        }

        return $re;

    }
}