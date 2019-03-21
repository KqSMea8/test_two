<?php

namespace News\Model;

use Think\Model;

class NewsGoodsImageModel extends Model
{

    public function editData($data)
    {
        if ($data['id']) {
            $res = $this->save($data);
        } else {
            $res = $this->add($data);
            action_log('add_newsGoodsImage', 'News', $res, is_login());
        }

        return $res;
    }

    public function delData($map)
    {
        $res = $this->where($map)->delete();
        return $res;
    }

    public function getListByPage($map, $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->where($map)->count();
        if ($totalCount) {
            $list = $this->where($map)->page($page, $r)->order($order)->field($field)->select();
        }
        return array($list, $totalCount);
    }

    public function getList($map, $order = 'view desc', $limit = 5, $field = '*')
    {
        $lists = $this->where($map)->order($order)->limit($limit)->select();
        return $lists;
    }

    public function setDead($ids)
    {
        !is_array($ids) && $ids = explode(',', $ids);
        $map['id'] = array('in', $ids);
        $res = $this->where($map)->setField('dead_line', time());
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

} 