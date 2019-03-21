<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author MQ
 */

namespace Wechat\Model;

use Think\Model;
use Think\Log;

class WechatInspectionModel extends Model
{

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
        $res = [];
        if ($data['inspection_id']) {
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
        }
        return $res ? $res : false;
    }

    /**
     * 获取主订单列表
     * @param $map
     * @param string $order
     * @param $field
     * @return mixed
     */

    public function getInspectionList($map, $order = 'i.create_time desc', $field, $page, $r){

        $totalCount = M('WechatInspection')->alias("i")
            ->join("jpgk_wechat_ka_enterprise as e on i.enterprise_id = e.id")
            ->join("jpgk_wechat_facilitator as f on i.facilitator_id = f.id")
            ->where($map)
            ->order($order)
            ->field($field)
            ->count();
        if ($totalCount) {
            $list = M('WechatInspection')->alias("i")
                ->join("jpgk_wechat_ka_enterprise as e on i.enterprise_id = e.id")
                ->join("jpgk_wechat_facilitator as f on i.facilitator_id = f.id")
                ->where($map)
                ->page($page, $r)
                ->order($order)
                ->field($field)
                ->select();
        }
        return array($list, $totalCount);
    }


    /**
     * 获取巡检主订单信息
     * @param $map
     * @param $field
     * @return array
     */
    public function getInspectionOrderInfo($map = "",$field = "*"){
        $data = M('WechatInspection')->alias("wi")
            ->join("jpgk_wechat_ka_enterprise ent on wi.enterprise_id = ent.id")
            ->where($map)
            ->field($field)
            ->find();
        return $data;
    }

    /**
     * 获取巡检主订单信息
     * @param $map
     * @param $field
     * @return array
     */
    public function getInspectionOrderData($map = "",$field = "*"){

        $data = $this->alias("wi")
//            ->join("jpgk_wechat_ka_enterprise wke on wi.enterprise_id = wke.id")
            ->join("left join jpgk_wechat_inspection_store wis on wi.inspection_id = wis.inspection_id")
            ->where($map)
            ->field($field)
            ->order('wi.create_time desc')
            ->group('wi.inspection_id')
            ->select();

        return empty($data) ? [] : $data;
    }

    /**
     * 获取巡检主订单相关详情页面
     * @param string $map
     * @param string $field
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getMainInspectionOrderDetail($map = "",$field = "*"){
        $data = $this->alias("wi")
            ->join("jpgk_wechat_ka_enterprise wke on wi.enterprise_id = wke.id")
            ->join("jpgk_wechat_facilitator wf on wi.facilitator_id = wf.id")
            ->where($map)
            ->field($field)
            ->find();

        return empty($data) ? [] : $data;
    }

    /**
     * 获取巡检主订单中的门店数据
     * @param string $map
     * @param string $field
     * @return array
     * @author  HQ.
     * email: huangqing@jpgk.com.cn
     * QQ:2322523770
     */
    public function getInspectionStoreOrderData($map = "",$field = "*"){

        $data = M('WechatInspectionStore')->alias("wis")
            ->join("left join jpgk_wechat_inspection_store wis on wi.inspection_id = wis.inspection_id")
            ->where($map)
            ->field($field)
            ->order('wi.create_time desc')
            ->group('wi.inspection_id')
            ->select();

        return empty($data) ? [] : $data;
    }
    /**
     * 获取巡检主订单信息
     * @param $map
     * @param $field
     * @return array
     */
    public function getInspectionStoreData($map = "",$field = "*"){

        $data = $this->alias("wi")
            ->join("left join jpgk_wechat_inspection_store wis on wi.inspection_id = wis.inspection_id")
            ->where($map)
            ->field($field)
            ->order('wi.create_time desc')
            ->select();
        return empty($data) ? [] : $data;
    }

    /**
     * 修改剩余次数及主订单状态
     * @param $inspection_store_id
     */
    public function changeOrderInfo($inspection_store_id){
        //修改剩余次数
        $storeChild = (new WechatInspectionStoreModel())->getOnceInfo(['inspection_store_id'=>$inspection_store_id],'');

        $number = $storeChild['service_num_remain'] - 1;

        $res = (new WechatInspectionStoreModel())->saveInfo(['inspection_store_id'=>$inspection_store_id,'service_num_remain'=>$number]);

        //获取门店下所有子订单 查看剩余次数是否都是0 是则修改主订单
        $allOrderChild = (new WechatInspectionStoreModel())->getOnceInfo(['inspection_id'=>$storeChild['inspection_id'],'service_num_remain'=>array('NEQ',0)],'');
        $res1 = true;
        if(empty($allOrderChild)){
            //修改主订单
            $res1 = (new WechatInspectionModel())->editData(['inspection_id'=>$storeChild['inspection_id'],'inspection_status'=>4,'finish_time'=>date('Y-m-d H:i:s')]);
        }
        if($res && $res1){
            return true;
        }else{
            return false;
        }
    }

}