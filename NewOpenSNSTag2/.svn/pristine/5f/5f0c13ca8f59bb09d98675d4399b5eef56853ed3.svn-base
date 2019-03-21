<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author MQ
 */

namespace Wechat\Model;

use Think\Model;
use Think\Log;

class WechatInspectionStoreChildDeviceModel extends Model
{

    /**
     * 获取一条信息
     * @param $map
     * @param string $field
     * @return mixed
     */
    public function getInspectionStoreChildDeviceByInspectionStoreChildId($map, $field = '*'){

        $data = $this->where($map)->field($field)->select();
        return $data;
    }

    /**
     * 根据条件获取一条信息
     * @param $map
     * @param string $field
     * @return mixed
     */
    public function getInfoByMap($map, $field = '*'){
        $data = $this->where($map)->field($field)->find();
        return $data;
    }

    /**
     * 修改数据
     * @param $data
     * @return bool|mixed
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function editData($data)
    {
        if (!empty($data['inspection_child_device_id']) && $data['inspection_child_device_id'] != 'null') {
            $data['update_time'] = date('Y-m-d H:i:s');
            $this->save($data);
            $res = $data['inspection_child_device_id'];
        } else {
            unset($data['inspection_child_device_id']);
            $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
        }
        return $res;
    }

    /**
     * 获取巡检工单其他设备巡检详情
     * @param $where
     * @return mixed
     */
    public function getInspectionDeviceDetail($where){
        $data = $this->alias("ins")
            ->join("left join jpgk_wechat_device dev on ins.device_id = dev.id")
            ->join("left join jpgk_wechat_equipment_category cat on dev.category = cat.id")
            ->join("left join jpgk_wechat_inspection_store_child isc on isc.inspection_store_child_id = ins.inspection_store_child_id")
            ->where($where)
            ->field("ins.*,dev.device_name,dev.brand device_brand,dev.category device_category_id,cat.name category_name,isc.enterprise_id,isc.store_id")
            ->select();
        return $data;
    }


}