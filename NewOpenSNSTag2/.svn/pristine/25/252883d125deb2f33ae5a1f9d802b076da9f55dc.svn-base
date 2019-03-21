<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author HQ
 */

namespace Wechat\Model;

use Think\Model;

class WechatDeviceModel extends Model
{

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );

    /**
     * 获取数据详细信息
     * @param $id
     * @param bool $field
     * @return mixed
     */
    public function info($id, $field = true)
    {
        /* 获取单个课程信息 */
        $map = array();
        if (is_numeric($id)) { //通过ID查询
            $map['id'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    public function editData($data)
    {
        if ($data['id']) {
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
            $detail['id'] = $data['id'];
        } else {
            $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
            $detail['id'] = $res;
        }

        return $res;
    }

    /**
     * 修改设备方法 新方法
     * @param $data
     * @return mixed
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function editDataNew($data)
    {
        if ($data['id']) {
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
            $detail = $data['id'];
        } else {
            $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
            $detail = $res;
        }

        return $detail;
    }

    public function getData($id)
    {
        if ($id > 0) {
            $map['id'] = $id;
            $data = $this->where($map)->find();
            return $data;
        }
        return null;
    }

    public function getListByPage($map = [], $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->where($map)->count();
        if ($totalCount) {
            $list = $this->where($map)->page($page, $r)->order($order)->field($field)->select();
        }
        return array($list, $totalCount);
    }

    public function getList($map = [], $order = 'update_time desc', $field = '*')
    {
        $totalCount = $this->where($map)->count();
        if ($totalCount) {
            $list = $this->where($map)->order($order)->field($field)->select();
        }
        return $list;
    }

    /**
     * 获取设备关联订单相关数据
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getDeviceInspectionMemData($map = [],$field = '*'){

        //设备表
        //订单表
        //门店表
        //用户表
        $data = $this->alias("d")
            ->join("jpgk_wechat_inspection_store_child wisc on wisc.inspection_store_child_id = d.inspection_store_child_id")
            ->join("jpgk_wechat_ka_stores wks on wks.id = wisc.store_id")
            ->join("jpgk_wechat_member wm on wm.uid = d.customer_service_id")
            ->where($map)
            ->field($field)
            ->group('d.inspection_store_child_id')
            ->select();
        return $data;
    }

    /**
     * 获取设备关联订单相关数据
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getDeviceInspectionMemInfo($map = [],$field = '*'){

        //设备表
        //订单表
        //门店表
        //用户表
        $data = $this->alias("d")
            ->join("jpgk_wechat_inspection_store_child wisc on wisc.inspection_store_child_id = d.inspection_store_child_id")
            ->join("jpgk_wechat_ka_stores wks on wks.id = wisc.store_id")
            ->join("jpgk_wechat_member wm on wm.uid = d.customer_service_id")
            ->where($map)
            ->field($field)
            ->group('d.inspection_store_child_id')
            ->find();

        return $data;
    }

    /**
     * 获取设备列表和类别详情
     * @param array $map
     * @param string $field
     * @param string $order
     * @return mixed
     */
    public function getDeviceInfo($map = [],$field = '*',$order = "d.create_time desc"){
        $data = $this->alias("d")
            ->join("jpgk_wechat_equipment_category ec on ec.id = d.category")
            ->where($map)
            ->field($field)
            ->order($order)
            ->select();
        return $data;
    }

    /**
     * 获取设备分类操作
     * @param array $map
     * @param string $field
     * @param string $order
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getDeviceOperation($map = [],$field = '*',$order = "weco.create_time desc"){
        $data = $this
            ->alias('wd')
//            ->field('weco.*')
            ->field($field)
            ->join('jpgk_wechat_equipment_category wec on wec.id = wd.category')
            ->join('jpgk_wechat_equipment_category_operation weco on weco.equipment_category_id = wec.id')
//            ->where(['wd.id'=>$deviceId])
            ->where($map)
            ->order($order)
            ->select();

        return $data;
    }
    /**
     * 获取巡检工单其他设备巡检详情
     * @param $where
     * @return mixed
     */
    public function getInspectionDeviceDetail($where){
        $data = $this->alias("dev")
            ->join("left join jpgk_wechat_inspection_store_child_device ins on ins.device_id = dev.id")
            ->join("left join jpgk_wechat_equipment_category cat on dev.category = cat.id")
            ->join("left join jpgk_wechat_inspection_store_child isc on isc.inspection_store_child_id = ins.inspection_store_child_id")
            ->where($where)
            ->field("ins.repairs_id,ins.yet_repairs_id,ins.inspection_operate,ins.remark,ins.moeny,ins.source,
            dev.id device_id,dev.device_name,dev.brand device_brand,dev.category device_category_id,
            cat.name category_name,isc.enterprise_id,isc.store_id")
            ->select();
        return $data;
    }

} 