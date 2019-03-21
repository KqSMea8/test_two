<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 上午10:47
 * @author HQ
 */

namespace OnlineVote\Model;

use Think\Model;

class EventUserVoteModel extends Model
{
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

    public function getListByPage($map = [], $page = 0, $order = 'update_time desc', $field = '*', $r = 20)
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
            ->join('LEFT JOIN jpgk_ucenter_member a on b.uid=a.id')
            ->limit($page,$r)
            ->where(['b.event_id' => $map['id']])
            ->select();

        $count = $this->alias('b')
            ->join('LEFT JOIN jpgk_ucenter_member a on b.uid=a.id')
            ->where (['b.event_id' => $map['id']])
            ->count();

        return [$data, $count];
    }

    public function getList($map = [], $order = 'update_time desc', $field = '*')
    {
        $list = $this->where($map)->order($order)->field($field)->select();
        return $list;
    }

}