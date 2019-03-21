<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/27
 * Time: 13:29
 */

namespace Wechat\Model;

use Think\Model;

class WechatKaStoresModel extends Model {

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );

    //列表
    public function getListForPage($map = [], $page = 1, $order = 'update_time desc', $field = '*', $r = 20){

        $totalCount = $this->alias('store')->join('left join jpgk_wechat_ka_enterprise ent on ent.id = store.enterprise_id')->where($map)->count();
        if ($totalCount) {
            $list = $this->alias('store')
                ->join('left join jpgk_wechat_ka_enterprise ent on ent.id = store.enterprise_id')
                ->where($map)
                ->page($page, $r)
                ->order($order)
                ->field($field)
                ->select();
        }
        return array($list, $totalCount);
    }

    public function getDataForExcel($map = [],$field = '*'){

        $data = $this->alias('store')
            ->join('left join jpgk_wechat_ka_enterprise ent on ent.id = store.enterprise_id')
            ->where($map)
            ->field($field)
            ->select();

        return $data;
    }

    public function getData($map, $field = '*')
    {
        $totalCount = $this->alias('store')->join('left join jpgk_wechat_ka_enterprise ent on ent.id = store.enterprise_id')->where($map)->count();
        $data = [];
        if($totalCount){
            $data = $this->alias('store')
                ->join('left join jpgk_wechat_ka_enterprise ent on ent.id = store.enterprise_id')
                ->where($map)
                ->field($field)
                ->select();
        }
        return $data;
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
     * 获取一条记录
     * @param $map
     * @param string $field
     * @return mixed
     */
    public function getOnceInfo($map, $field = '*'){

        $data = $this->where($map)->field($field)->find();
        return $data;
    }

    public function getInfo($map, $field = '*'){

        $data = $this->where($map)->field($field)->select();
        return $data;
    }
    /**
     * 获取单个门店
     * @param $where
     * @param $field
     * @return array
     * @throws \think\Exception
     */
    public function getStoreInfo($where,$field){

        $list = $this->alias('store')
            ->join("left join jpgk_wechat_popedom popedom on store.popedom_id=popedom.popedom_id")
            ->join("left join jpgk_wechat_street street on store.street_id=street.street_id")
            ->join("left join jpgk_wechat_tenement tenement on store.tenement_id=tenement.tenement_id")
            ->field($field)
            ->where($where)
            ->find();
        return $list;
    }
}