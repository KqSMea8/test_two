<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/27
 * Time: 9:12
 */

namespace Wechat\Model;

use Think\Model;

class WechatKaEnterpriseModel extends Model {

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );

    //列表
    public function getListForPage($map = [], $page = 1, $order = 'update_time desc', $field = '*', $r = 20){

        $totalCount = $this->alias('ent')->where($map)->count();
        if ($totalCount) {
            $list = $this->alias('ent')
                ->join('left join jpgk_wechat_ka_stores store on store.enterprise_id = ent.id')
                ->where($map)
                ->page($page, $r)
                ->order($order)
                ->field($field)
                ->group('ent.id')
                ->select();
        }
        return array($list, $totalCount);
    }

    public function getDataForExcel($map = [],$field = '*'){

        $data = $this->alias('ent')
            ->join('left join jpgk_wechat_ka_stores store on store.enterprise_id = ent.id')
            ->where($map)
            ->field($field)
            ->group('ent.id')
            ->select();

        return $data;
    }

    //
    public function getData($map, $field = '*')
    {
        $totalCount = $this->where($map)->count();
        $data = [];
        if($totalCount){
            $data = $this->where($map)->field($field)->select();
        }
        return $data;
    }


    public function getOnceData($map, $field = '*')
    {
        $totalCount = $this->where($map)->count();
        $data = [];
        if($totalCount){
            $data = $this->where($map)->field($field)->find();
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
}