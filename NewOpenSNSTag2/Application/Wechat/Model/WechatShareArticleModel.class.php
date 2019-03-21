<?php
/**
 * Created by PhpStorm.
 * User: pangyongfu
 * Date: 2017/12/12
 * Time: 10:50
 */

namespace Wechat\Model;


use Think\Model;

class WechatShareArticleModel extends Model
{
    public function editData($data)
    {
        if ($data['id']) {
            $res = $this->save($data);
            $detail['id'] = $data['id'];
        } else {
            $data['create_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
            $detail['id'] = $res;
        }

        return $res;
    }

    public function getListByPage($map = [], $page = 1, $order = 'create_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->where($map)->count();
        if ($totalCount) {
            $list = $this->where($map)->page($page, $r)->order($order)->field($field)->select();
        }
        return array($list, $totalCount);
    }

    /**
     * 获取一条信息
     * @param $map
     * @param string $field
     * @return mixed
     */
    public function getOnceInfo($map, $field = '*'){

        $data = $this->where($map)->field($field)->find();
        return $data;
    }
}