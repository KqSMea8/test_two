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

class EventVoteModel extends Model
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
        if (!empty($data['id'])) {
            $res = $this->save($data);
        } else {
            $data['create_time'] = time();
            $res = $this->add($data);
        }

        return $res;
    }

    public function getListByPage($map = [], $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {

        $data = $this->field('c.id,c.name,COUNT(distinct b.user_id) as userNum,SUM(b.vote_num) as voteNum')
            ->alias('a')
            ->join('jpgk_event_vote_item c on a.id=c.event_vote_id')
            ->join('LEFT JOIN jpgk_event_user_vote b on c.id=b.vote_item_id')
            ->page($page, $r)
            ->where(['a.id' => $map['vote_id'],['c.status'=>1]])
            ->group('c. id')
            ->select();

        $sql = "SELECT
            COUNT(*) AS tp_count
            FROM
                jpgk_event_vote_item
            WHERE
            event_vote_id = (SELECT id FROM jpgk_event_vote WHERE id = ".$map['vote_id'].") AND status = 1 LIMIT 1";
        $count = $this->query($sql);

        return [$data, $count[0]['tp_count']];
    }

    public function getList($map = [], $order = 'update_time desc', $field = '*')
    {
        $data = $this->field('c.id,c.name,COUNT(distinct b.user_id) as userNum,SUM(b.vote_num) as voteNum')
            ->alias('a')
            ->join('jpgk_event_vote_item c on a.id=c.event_vote_id')
            ->join('LEFT JOIN jpgk_event_user_vote b on c.id=b.vote_item_id')
            ->where(['a.id' => $map['vote_id'],['c.status'=>1]])
            ->group('c. id')
            ->select();

        return $data;
    }

}