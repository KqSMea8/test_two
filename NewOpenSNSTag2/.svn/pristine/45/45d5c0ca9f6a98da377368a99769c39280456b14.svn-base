<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 10:05
 */

namespace Wechat\Model;


use Think\Model;

class WechatAddressModel extends Model
{
	/**
	 * 获取用户地址列表
	 * @param $condition
	 * @param $order
	 * @return mixed
	 */
	public function getAddressList($condition,$fields,$order = "is_default desc,update_time desc"){
		$list = $this->where($condition)->field($fields)->order($order)->select();
		return $list;
	}
	/**
	 * 获取一条用户地址
	 * @param $condition
	 * @param $order
	 * @return mixed
	 */
	public function getAddressInfo($condition){
		$info = $this->where($condition)->find();
		return $info;
	}
	/**
	 * 获取一条用户地址(省市区)
	 * @param $condition
	 * @param $order
	 * @return mixed
	 */
	public function getAddressAreaInfo($condition,$field = "addr.*,province.name province_name,city.name city_name"){
		$info = $this->alias("addr")
			->join("left join jpgk_district province on addr.province_id = province.id")
			->join("left join jpgk_district city on addr.city_id = city.id")
			->where($condition)
			->field($field)
			->find();
		if(!empty($info)){
			$info['detail_address'] = $info['province_name'].$info['city_name']." ".$info['detail_address'];
		}
		return $info;
	}
}