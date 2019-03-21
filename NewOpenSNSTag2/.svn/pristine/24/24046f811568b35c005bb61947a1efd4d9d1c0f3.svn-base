<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 上午10:47
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace GXPT\Model;


use Think\Model;

class supplyMarketingCompanyModel extends Model
{
//
//    protected $tableName = "supplyMarketingCompany";
//
//    protected $_auto = array(
//        array('status', '1', self::MODEL_INSERT),
//    );

    /**
     * 获取公司详细信息
     * @param $id
     * @param bool $field
     * @return mixed
     */
    public function info($id, $field = true)
    {
        /* 获取分类信息 */
        $map = array();
        if (is_numeric($id)) { //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    public function editData($data)
    {
//        $data = $this->create();
        if ($data['id']) {
            $data['update_time'] = time();
            $res = $this->save($data);
        } else {
            $data['create_time'] = time();
            $data['update_time'] = time();
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

    public function getListData($map = [],$order = 'update_time desc', $field = '*')
    {
        $list = $this->where($map)->order($order)->field($field)->select();
        return $list;
    }
} 