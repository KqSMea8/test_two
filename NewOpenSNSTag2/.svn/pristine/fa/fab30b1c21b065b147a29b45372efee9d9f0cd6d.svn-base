<?php
namespace app\wechat\service;
use app\cabinet\model\Cab;
use app\cabinet\model\CabDoor;
use app\cabinet\model\CabLineLog;
use app\cabinet\model\Command;
use app\common\service\Redis;
use think\Log;


/**
 * 机柜状态回调处理
 * @package app\wechat\service
 */
class CabCallback
{
	/**
	 * 设备上下线通知
	 * @author  tianxh.
	 * email: tianxh@jpgk.com.cn
	 * QQ:2018997757
	 */
	public function deviceOnLineOfLine($param){

		Log::write("上下线回调的参数是：".json_encode($param));

		//参数验证
		if(empty($param["deviceId"]) || empty($param["status"])){

			throw new \Exception("设备上下线回调参数异常！");
		}

		//更新设备上下线状态
		$redis = new Redis();
		$cab = Cab::get(["cab_code" => $param["deviceId"]]);
		$redisKey = "cab:cabId:".$param["deviceId"];

		if($redis->exists($redisKey)){
			return true;
		}else{
			if($param["status"] == "online"){
				$redis->set($redisKey,1,180);
			}
		}

		if($param["status"] == "online"){
			$cab->is_online = 1;
		}else{
			$cab->is_online = 0;
		}
		$cab->save();

		//设备上下线记录
		$cabLineLog = new CabLineLog();
		$cabLineLog->cab_id = $cab->cab_id;
		$cabLineLog->status = $cab->is_online;
		$cabLineLog->time_create = date("Y-m-d H:i:s");
		$cabLineLog->save();

		return true;
	}

	/**
	 * 设备硬件状态回调
	 * @author  tianxh.
	 * email: tianxh@jpgk.com.cn
	 * QQ:2018997757
	 */
	public function deviceHardStatus($param){

		Log::write("设备硬件状态回调：".input("param"));

		//数据验证
		if(empty($param["deviceId"]) || empty($param["data"])){
			throw new \Exception("设备上下线回调参数异常！");
		}

		$dataArr = json_decode($param["data"],true);

		//检查机柜是否在线，如果不在线，就设置为在线
		$cab = Cab::get(["cab_code" => $param["deviceId"]]);
		if($cab->is_online != 1){
			$cab->is_online = 1;
			$cab->save();
		}

		//查询柜门
		$doorModel = new CabDoor();
		$doors = $doorModel->alias("door")
			->field("door.*")
			->join("cab_group group","door.group_id = group.group_id")
			->join("cab","door.cab_id = cab.cab_id")
			->where("cab.cab_code","eq",$param["deviceId"])
			->where("group.group_no","eq",$dataArr["boardCode"])
			->select();

		//更新柜门状态
		$doorStatusArr = explode(",",$dataArr["passageState"]);

		$changeDoorStatusArr = [];
		if(!empty($doors)){

			//状态赋值
			foreach($doors as &$door){

				if(isset($doorStatusArr[$door->door_no-1])){

					$changeDoorStatusArr[] = [
						"door_id" => $door->door_id,
						"open_status" => $doorStatusArr[$door->door_no-1]
					];
				}
			}

			//更新
			$doorModel->saveAll($changeDoorStatusArr);
		}

		return true;
	}

	/**
	 * 柜门打开回调
	 * @author  tianxh.
	 * email: tianxh@jpgk.com.cn
	 * QQ:2018997757
	 */
	public function doorOpenCallback($param){

		Log::write("开门回调的参数是：".input("param"));

		//数据验证
		if(!empty($param["deviceId"]) && !empty($param["orderId"])){

			//数据更新
			$command = Command::get(["cmd_code" => $param["orderId"]]);
			if(!empty($command)){

				$command->status = ($param["orderState"] == 1?5:10);
				$command->reply_time = date("Y-m-d H:i:s");
				$command->reply = json_encode($param);
				$command->save();
			}
		}
		return true;
	}
}