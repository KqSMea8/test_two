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

class supplyMarketingCompanyQualificationModel extends Model
{
//
//    protected $tableName = "supplyMarketingCompany";
//
//    protected $_auto = array(
//        array('status', '1', self::MODEL_INSERT),
//    );

    /**
     * 获取详细信息
     * @param $id
     * @param bool $field
     * @return mixed
     */
    public function info($id, $field = true)
    {
        $map = array();
        if (is_numeric($id)) { //通过ID查询
            $map['id'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }



    public function editData($data)
    {
//        $data = $this->create();
        if ($data['id']) {
            $res = $this->save($data);
        } else {
            $res = $this->add($data);
        }
        return $res;
    }

    public function delData($map)
    {
        $lists = $this->where($map)->delete();
        return $lists;
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