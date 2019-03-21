<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 下午1:22
 */

namespace Goods\Model;

use Think\Model;

class SomethingPicModel extends Model
{

    public function editData($data)
    {
        if ($data['id']) {
            $data['update_time'] = Date('Y-m-d H:i:s');
            $res = $this->save($data);
        } else {
            $data['create_time'] = time();
            $data['update_time'] = Date('Y-m-d H:i:s');
            $res = $this->add($data);
            action_log('addGoodsPic', 'GoodsPic', $res, is_login());
        }

        return $res;
    }

    public function getOneData($map)
    {
        $lists = $this->where($map)->find();
        return $lists;
    }

    public function delData($map)
    {
        $lists = $this->where($map)->delete();
        return $lists;
    }

    public function getList($map)
    {
        $list = $this->where($map)->select();
        return $list;
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
    public function getImgData($map = [],$field = "*"){
        $list = $this->alias("sm")
            ->join("jpgk_picture as pic on sm.pic_id = pic.id")
            ->where($map)
            ->field($field)
            ->select();
        return $list;
    }
} 