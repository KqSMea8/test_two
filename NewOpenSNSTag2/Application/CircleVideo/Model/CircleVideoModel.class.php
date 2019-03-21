<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author HQ
 */

namespace CircleVideo\Model;

use Think\Model;
use CircleVideo\Model\CircleVideoDetailModel;

class CircleVideoModel extends Model
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
        } else { //通过标识查询
            $map['title'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    public function editData($data)
    {
        if (!mb_strlen($data['description'], 'utf-8')) {
            $data['description'] = msubstr(trimall(op_t($data['content'])), 0, 200);
        }
        $detail['content'] = $data['content'];
        $detail['template'] = $data['template'];
        if ($data['id']) {
            $data['update_time'] = time();
            $res = $this->save($data);
            $detail['video_id'] = $data['id'];
        } else {
            $data['create_time'] = $data['update_time'] = time();
            $res = $this->add($data);
            $detail['video_id'] = $res;
        }

        if ($res) {
            (new CircleVideoDetailModel())->editData($detail);
        }
        return $res;
    }

    public function getData($id)
    {
        if ($id > 0) {
            $map['id'] = $id;
            $data = $this->where($map)->find();
            if ($data) {
                $data['detail'] = (new CircleVideoDetailModel())->getData($id);
            }
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