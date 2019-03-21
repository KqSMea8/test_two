<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 下午1:22
 */

namespace Goods\Model;

use Think\Model;

class UsedMarketGoodsModel extends Model
{

    public function editData($data)
    {
        if ($data['goods_id']) {
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
        } else {
            $data['create_time'] = time();
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
            action_log('add_goods', 'Goods', $res, is_login());
        }

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
            $map['goods_id'] = $id;
            $data = $this->where($map)->find();
            return $data;
        }
        return null;
    }

    /**
     * 获取推荐位数据列表
     * @param $pos 推荐位 1-系统首页，2-推荐阅读，4-本类推荐
     * @param null $category
     * @param $limit
     * @param bool $field
     * @return mixed
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function position($pos, $category = null, $limit = 5, $field = true, $order = 'sort desc,view desc')
    {
        $map = $this->listMap($category, 1, $pos);
        $res = $this->field($field)->where($map)->order($order)->limit($limit)->select();
        /* 读取数据 */
        return $res;
    }

    /**
     * 设置where查询条件
     * @param  number $category 分类ID
     * @param  number $pos 推荐位
     * @param  integer $status 状态
     * @return array             查询条件
     */
    private function listMap($category, $status = 1, $pos = null)
    {
        /* 设置状态 */
        $map = array('status' => $status);

        /* 设置分类 */
        if (!is_null($category)) {
            $cates = D('News/NewsCategory')->getCategoryList(array('pid' => $category, 'status' => 1));
            $cates = array_column($cates, 'id');
            $map['category'] = array('in', array_merge(array($category), $cates));
        }
        $map['dead_line'] = array('gt', time());

        /* 设置推荐位 */
        if (is_numeric($pos)) {
            $map[] = "position & {$pos} = {$pos}";
        }

        return $map;
    }

} 