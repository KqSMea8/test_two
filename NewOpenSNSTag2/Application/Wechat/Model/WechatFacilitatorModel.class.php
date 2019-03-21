<?php
/**
 * Created by PhpStorm.
 * User: pangyongfu
 * Date: 2017/12/12
 * Time: 10:50
 */

namespace Wechat\Model;


use Think\Model;

class WechatFacilitatorModel extends Model
{
    public function editData($data)
    {
        if ($data['id']) {
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
            $detail['id'] = $data['id'];
        } else {
            $data['create_time'] = $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->add($data);
            $detail['id'] = $res;
        }

        return $res;
    }

    public function getData($map, $field = '*')
    {
        $totalCount = $this->where($map)->count();
        $data = [];
        if($totalCount){
            $data = $this->where($map)->field($field)->select();
        }
        return $data;
    }

    public function getListByPage($map = [], $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->where($map)->count();
        if ($totalCount) {
            $list = $this->where($map)->page($page, $r)->order($order)->field($field)->select();
        }
        return array($list, $totalCount);
    }

    /**
     * 获取服务商列表和任务数
     * @param array $map
     * @param string $order
     * @param string $field
     * @return mixed
     * @author pangyongfu
     */
    public function getFacilitatorData($map = []){

        $sql = "SELECT
            facilt.id,
            facilt. NAME uname,
            if(orde.num,orde.num,0) num
        FROM
            jpgk_wechat_facilitator facilt
        LEFT JOIN (
            SELECT
                count(*) num,
                facilitator_id id
            FROM
                jpgk_wechat_order
            WHERE
                order_type = {$map['order_type']}
            AND order_state IN (0,1, 2, 9)
            GROUP BY
                facilitator_id
        ) orde ON facilt.id = orde.id
        WHERE
            facilt.type = {$map['type']}
        AND facilt. STATUS = {$map['`status`']}
        ORDER BY
            facilt.update_time DESC ";

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
}