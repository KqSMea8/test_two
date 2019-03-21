<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/7
 * Time: 15:05
 */

namespace Wechat\Model;


use Think\Model;

class WechatMemberModel extends Model
{
    /**
     * 获取分页会员数据
     * @param array $map
     * @param int $page
     * @param string $order
     * @param string $field
     * @param int $r
     * @return array
     */
    public function getListByPage($map = [], $page = 1, $order = 'member.update_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->alias("member")
            ->join("left join jpgk_wechat_facilitator fac on member.facilitator_id = fac.id")
            ->where($map)
            ->count();
        $list = [];
        if ($totalCount) {
            $list =  $this->alias("member")
                ->join("left join jpgk_wechat_facilitator fac on member.facilitator_id = fac.id")
                ->where($map)
                ->page($page, $r)
                ->order($order)
                ->field($field)
                ->select();
        }
        return array($list, $totalCount);
    }
    /**
     * 获取分页会员数据
     * @param array $map
     * @param int $page
     * @param string $order
     * @param string $field
     * @param int $r
     * @return array
     */
    public function getCompanyMasterList($map = [], $page = 1, $order = 'member.update_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->alias("member")
            ->join("left join jpgk_wechat_ka_enterprise ent on member.enterprise_id = ent.id")
            ->where($map)
            ->count();
        $list = [];
        if ($totalCount) {
            $list =  $this->alias("member")
                ->join("left join jpgk_wechat_ka_enterprise ent on member.enterprise_id = ent.id")
                ->where($map)
                ->page($page, $r)
                ->order($order)
                ->field($field)
                ->select();
        }
        return array($list, $totalCount);
    }
    public function editData($data){
        if ($data['id']) {
            unset($data['id']);
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
        } else {
            unset($data['id']);
            $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
        }

        return $res;
    }
    public function editMemberData($data){
        if ($data['uid']) {
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
        } else {
            unset($data['uid']);
            $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
        }
        return $res;
    }

    /**
     * 根据条件获取用户数据（不分页）
     * @param array $map
     * @param string $order
     * @param string $field
     * @return array
     * @author pangyongfu
     */
    public function getListToExcel($map = [], $order = 'member.update_time desc', $field = '*')
    {
        $totalCount = $this->alias("member")
            ->join("left join jpgk_wechat_facilitator fac on member.facilitator_id = fac.id")
            ->where($map)
            ->count();
        $list = [];
        if ($totalCount) {
            $list =  $this->alias("member")
                ->join("left join jpgk_wechat_facilitator fac on member.facilitator_id = fac.id")
                ->where($map)
                ->order($order)
                ->field($field)
                ->select();
        }
        return $list;
    }

    /**
     * 获取师傅列表和任务数
     * @param array $map
     * @param string $order
     * @param string $field
     * @return mixed
     * @author pangyongfu
     */
    public function getMasterData($map){

        $sql = "SELECT
                member.uid,
                member. NAME uname,
                if(orde.num,orde.num,0) num
            FROM
                jpgk_wechat_member member
            LEFT JOIN (SELECT count(*) num,workers_id FROM jpgk_wechat_order
            WHERE order_type = {$map['order_type']}
            AND order_state IN (1, 2, 9,10)
            GROUP BY
                workers_id) orde ON member.uid = orde.workers_id
            WHERE
                member.type_user = {$map['type_user']}
            AND member.isadmin = {$map['isadmin']}
            AND member. STATUS = {$map['status']}
            AND member.facilitator_id = {$map['facilitator_id']}
            ORDER BY
                member.update_time DESC";
        $list = $this->query($sql);
        return $list;
    }
    /**
     * 获取一条信息
     * @param $map
     * @param string $field
     * @return mixed
     */
    public function getOnceInfo($map, $field = '*'){

        $data = $this->where($map)->field($field)->find();
        return $data;
    }

    /**
     * 根据条件获取用户信息
     * @param $map
     * @param string $field
     * @return mixed
     */
    public function getInfoByMap($map, $field = '*'){

        $data = $this->where($map)->field($field)->select();
        return $data;
    }
}