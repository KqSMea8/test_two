<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author MQ
 */

namespace Wechat\Model;

use Think\Model;
use Think\Log;

class WechatInspectionStoreModel extends Model
{
    /**
     * 获取订单详情
     * @param $map
     * @param string $order
     * @param $field
     * @return mixed
     */
    public function getInspectionOrderChildrenList($map, $order = 'create_time desc', $field){
        $list = M('WechatInspectionStore')
            ->where($map)
            ->order($order)
            ->field($field)
            ->select();
        return $list;
    }

    /**
     * 获取巡检主单的关联门店数据
     * @param $map
     * @param string $order
     * @param $field
     * @return mixed
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getInspectionOrderStoreData($map = '',$field = '*', $order = 'service_level asc'){
        $list = $this
            ->where($map)
            ->order($order)
            ->field($field)
            ->group('service_level')
            ->select();

        return $list;
    }

    /**
     * 获取门店最近完成的一次子订单列表
     */
    public function getRecentlyCompletedChildOrder($map,$fields,$order="wisc.service_num DESC"){
        $list = $this->alias("wis")
            ->join("jpgk_wechat_inspection_store_child wisc ON wisc.inspection_store_id = wis.inspection_store_id AND wisc.inspection_id = wis.inspection_id")
            ->where($map)
            ->field($fields)
            ->order($order)
            ->select();
        return $list;
    }

    /**
     * 获取一条记录
     * @param $map
     * @param $field
     * @return mixed
     */
    public function getOnceInfo($map,$field){
        $list = $this
            ->where($map)
            ->field($field)
            ->find();

        return $list;
    }

    /**
     * 修改数据
     * @param $data
     * @return bool
     */
    public function saveInfo($data){
        $data['update_time'] = date('Y-m-d H:i:s');
        $list = $this->save($data);
        return $list;
    }
}