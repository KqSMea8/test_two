<?php
namespace Common\Lib;
/**
 * 极光推送 todo 是否要有日志记录。
 * 2015年4月16日16:02:10
 * XiaohuiTian
 */

require_once "jpush/autoload.php";
use JPush\Model as M;
use JPush\JPushClient;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

use JPush\Client as JPush;

class Lz517JPush {

	//真实APPkey
	private $appKey = '8304b071e728eac747d5b2c8';

	//真实masterSecret
	private $masterSecret = '15cb29da406c396b5eee3461';

    //测试APPkey
    private $testAppKey = '348c21d68daa5d8620508368';

    //测试masterSecret
    private $testMasterSecret = '58788e45320ac4f674fa577f';

	public function pushMessageForApp($message, Array $tag, $extra = [], $isAnd = true, $isIos = true){

		vendor('autoload');

        $client = new JPush( $this->appKey , $this->masterSecret );

//        $pusher = $client -> push();
//        $pusher -> setPlatform('all');
//        $pusher -> addAllAudience();
//        $pusher -> setNotificationAlert('Hello, JPush');
		try {

//            $pusher -> send();

			$client->push()
				->setPlatform(array('ios', 'android'))
				->addTag($tag)
				->setNotificationAlert($message)
				->iosNotification('您有新消息', array(
					'sound' => 'hello jpush',
					'badge' => 2,
					'content-available' => true,
					'category' => 'jiguang',
					'extras' => $extra,
				))
				->androidNotification('您有新消息', array(
					'title' => 'hello jpush',
					'build_id' => 2,
					'extras' => $extra,
				))
				->message('message content', array(
					'title' => $message,
					'content_type' => 'text',
					'extras' => $extra,
				))
				->options(array(
					'sendno' => 300,
					'time_to_live' => 3600,
					'apns_production' => false,
					'big_push_duration' => 100
				))
				->send();

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

    //推送定时消息 餐讯使用
    public function pushTimingMessageForApp($data){

        if(isset($data["message"])){
            if(ENVIRONMENTSTATUS!='Reality'){
                //初始化
                $clientIos = new JPush( $this->appKey , $this->masterSecret );

                //简单的推送样例
                $payload = $clientIos
                    ->push()
                    ->setPlatform('all')
                    ->addAllAudience()
                    ->iosNotification(
                        $data["message"],
                        [
                            'extras'=>$data['needData']
                        ]
                    )
                    ->options([
                        "apns_production" => false  //true表示发送到 产环境(默认值)，false为开发环境
                    ])
                    ->build();

                // 创建在指定时间点触发的定时任务
                $responseIos = $clientIos
                    ->schedule()
                    ->createSingleSchedule("指定时间点的定时任务",$payload,["time" => $data['time']]);

                //And初始化
                $clientAnd = new JPush( $this->testAppKey , $this->testMasterSecret );

                //简单的推送样例
                $payload = $clientAnd
                    ->push()
                    ->setPlatform('all')
                    ->addAllAudience()
                    ->androidNotification(
                        $data["message"],
                        [
                            'extras'=>$data['needData']
                        ]
                    )
                    ->build();

                // 创建在指定时间点触发的定时任务
                $responseAnd = $clientAnd
                    ->schedule()
                    ->createSingleSchedule("指定时间点的定时任务",$payload,["time" => $data['time']]);

                return $responseAnd;
            }else{
                //初始化
                $client = new JPush( $this->appKey , $this->masterSecret );

                //简单的推送样例
                $payload = $client
                    ->push()
                    ->setPlatform('all')
                    ->addAllAudience()
                    ->iosNotification(
                        $data["message"],
                        [
                            'extras'=>$data['needData']
                        ]
                    )
                    ->androidNotification(
                        $data["message"],
                        [
                            'extras'=>$data['needData']
                        ]
                    )
                    ->options([
                        "apns_production" => true  //true表示发送到 产环境(默认值)，false为开发环境
                    ])
                    ->build();

                // 创建在指定时间点触发的定时任务
                $response = $client
                    ->schedule()
                    ->createSingleSchedule("指定时间点的定时任务",$payload,["time" => $data['time']]);
                return $response;
            }
        }
    }
}
