<?php
namespace app\wechat\service;
use app\common\service\Redis;


/**
 *通用的业务逻辑
 */
class LogicCommon
{
	/**
	 * 过滤注册并发
	 * @param string $openId 用户的openId
	 * @author  tianxh.
	 * email: tianxh@jpgk.com.cn
	 * QQ:2018997757
	 */
	public static function filterRegistConcurrent($openId){

		if(config("app_status") != "config_development"){

			$key = "znqcg:regist:openId:".$openId;

			$redis = new Redis();
			if($redis->incrBy($key,1) > 1){

				return false;
			}else{
				$redis->expire($key,5*60);
				return true;
			}
		}else{
			return true;
		}
	}
}