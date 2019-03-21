<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 上午10:47
 * @author HQ
 */

namespace Event\Model;

use Think\Model;

class EventAttendModel extends Model
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
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    public function editData($data)
    {
        $data['update_time'] = time();
        if ($data['id']) {
            $res = $this->save($data);
        } else {
            $data['uid'] = is_login();
            $data['create_time'] = time();
            $res = $this->add($data);
        }

        return $res;
    }

    public function getListByPage($map = [], $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {
        //publishDate为 1时查今天的 2时查昨天 3时查一周内 4时查一月内 0查全部
        if(!empty($map["selectDate"])){

            $havenTime = time() - strtotime(date("Y-m-d"));
            switch($map["selectDate"]){

                case -1:
                    $map["create_time"] = ["egt",time()-$havenTime];
                    break;
                case 1:
                    $map["create_time"] = ["between",[strtotime("-1 day") - $havenTime,time()-$havenTime]];
                    break;
                case 2:
                    $map["create_time"] = ["between",[strtotime("-7 day") - $havenTime,time()]];
                    break;
                case 3:
                    $map["create_time"] = ["between",[strtotime("-30 day") - $havenTime,time()]];
                    break;
            }
        }

        $data = $this->field('a.mobile,
			  a.username,
			  a.email,
			  b.create_time,
			  b.brand,
			  b.department,
			  b.position,
			  b.job,
			  b.id,
			  a.id as cuid,
			  b.event_id,
			  b.status')
            ->alias('b')
            ->join('jpgk_ucenter_member a on b.uid=a.id')
            ->page($page,$r)
            ->order($order)
            ->where(['b.event_id' => $map['id']])
            ->select();

        $count = $this->alias('b')
            ->join('jpgk_ucenter_member a on b.uid=a.id')
            ->where (['b.event_id' => $map['id']])
            ->count();

        return [$data, $count];
    }

    public function getListAttendByPage($map = [], $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {
        $where = [];
        //publishDate为 1时查今天的 2时查昨天 3时查一周内 4时查一月内 0查全部
        if(!empty($map["selectDate"])){

            $havenTime = time() - strtotime(date("Y-m-d"));
            switch($map["selectDate"]){

                case 1:
                    $where["b.create_time"] = ["egt",time()-$havenTime];
                    break;
                case 2:
                    $where["b.create_time"] = ["between",[strtotime("-1 day") - $havenTime,time()-$havenTime]];
                    break;
                case 3:
                    $where["b.create_time"] = ["between",[strtotime("-7 day") - $havenTime,time()]];
                    break;
                case 4:
                    $where["b.create_time"] = ["between",[strtotime("-30 day") - $havenTime,time()]];
                    break;
            }
        }

        //拼接搜索
        if(!empty($map['enrolStatus'])){
            switch($map["enrolStatus"]){

                //报名中
                case 1:
                    $where["b.status"] = 0;
                    break;
                //报名成功
                case 2:
                    $where["b.status"] = 1;
                    break;
                //取消报名
                case 3:
                    $where["b.status"] = -1;
                    break;
            }
        }

        $data = $this->field('a.mobile,
			  a.username,
			  a.email,
			  b.create_time,
			  b.brand,
			  b.department,
			  b.position,
			  b.job,
			  b.id,
			  a.id as cuid,
			  b.event_id,
			  b.status,
			  c.title,
			  c.price,
			  c.eTime
			  ')
            ->alias('b')
            ->join('jpgk_ucenter_member a on b.uid=a.id')
            ->join('jpgk_event c on b.event_id=c.id')
            ->page($page,$r)
            ->order($order)
            ->where($where)
            ->select();

        $count = $this->alias('b')
            ->join('jpgk_ucenter_member a on b.uid=a.id')
            ->join('jpgk_event c on b.event_id=c.id')
            ->where($where)
            ->count();

        return [$data, $count];
    }

    public function getList($map = [], $order = 'update_time desc', $field = '*')
    {
        $list = $this->where($map)->order($order)->field($field)->select();
        return $list;
    }

    //获取活动报名导出数据
    public function getAttendList(){

        $data = $this->field('a.mobile,
			  a.username,
			  a.email,
			  b.create_time,
			  b.brand,
			  b.department,
			  b.position,
			  b.job,
			  b.id,
			  a.id as cuid,
			  b.event_id,
			  b.status,
			  c.title,
			  c.price,
			  c.eTime
			  ')
            ->alias('b')
            ->join('jpgk_ucenter_member a on b.uid=a.id')
            ->join('jpgk_event c on b.event_id=c.id')
            ->select();

        return $data;
    }
}