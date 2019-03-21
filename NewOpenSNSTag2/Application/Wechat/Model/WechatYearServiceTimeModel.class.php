<?php
/**
 * Created by PhpStorm.
 * User: pangyongfu
 * Date: 2017/12/12
 * Time: 10:50
 */

namespace Wechat\Model;


use Think\Model;

class WechatYearServiceTimeModel extends Model
{
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

    public function getData($map, $field = '*')
    {
        $totalCount = $this->where($map)->count();
        $data = [];
        if($totalCount){
            $data = $this->where($map)->field($field)->select();
        }
        return $data;
    }

    public function getListByPage($map = [], $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->where($map)->count();
        if ($totalCount) {
            $list = $this->where($map)->page($page, $r)->order($order)->field($field)->select();
        }
        return array($list, $totalCount);
    }

}