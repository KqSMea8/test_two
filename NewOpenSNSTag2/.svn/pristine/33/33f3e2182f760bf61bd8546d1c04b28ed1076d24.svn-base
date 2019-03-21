<?php
/**
 * Created by PhpStorm.
 * User: pangyongfu
 * Date: 2017/12/12
 * Time: 15:52
 */

namespace Wechat\Model;


use Think\Model;

class WechatPayModel extends Model
{
    public function editData($data)
    {
        if ($data['id']) {
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
        } else {
            $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
        }

        return $res;
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
}