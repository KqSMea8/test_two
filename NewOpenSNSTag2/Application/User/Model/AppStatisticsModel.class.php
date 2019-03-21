<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 上午10:47
 * @author HQ
 */

namespace User\Model;

use Think\Model;

class AppStatisticsModel extends Model
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
        }

        return $this->field($field)->where($map)->find();
    }

    public function editData($data)
    {
        $data['update_time'] = time();
        if ($data['id']) {
            $res = $this->save($data);
        } else {
            $data['create_time'] = time();
            $res = $this->add($data);
        }

        return $res;
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