<?php
/**
 * Created by PhpStorm.
 * User: pangyongfu
 * Date: 2017/12/8
 * Time: 16:32
 */

namespace Wechat\Model;


use Think\Model;

class WechatOrderAppraiseModel extends Model
{
    /**
     * 获取订单详情列表
     * @author  pangyongfu
     */
    public function getAppriaseListByPage($map = [], $page = 1, $order = 'appraise.update_time desc', $field = '*', $r = 20)
    {
        if($map["selectDate"]){

            switch($map["selectDate"]){

                case 1:
                    $map["appraise.create_time"] = ['like','%'.date("Y-m-d").'%'];
                    break;
                case 2:
                    $map["appraise.create_time"] = ['like','%'.date("Y-m-d",strtotime("-1 day")).'%'];
                    break;
                case 3:
                    $map["appraise.create_time"] = ["between",[date("Y-m-d",strtotime("-7 day"))." 00:00:00",date("Y-m-d")." 23:59:59"]];
                    break;
                case 4:
                    $map["appraise.create_time"] = ["between",[date("Y-m-d",strtotime("-30 day"))." 00:00:00",date("Y-m-d")." 23:59:59"]];
                    break;
            }
            unset($map['selectDate']);
        }

        $totalCount = $this->alias("appraise")
            ->join("jpgk_wechat_order as orde on orde.id = appraise.order_id")
            ->join("jpgk_wechat_member as member on appraise.member_id = member.uid")
            ->join("jpgk_wechat_member as worker on appraise.workers_id = worker.uid")
            ->join("jpgk_wechat_facilitator as facilitator on orde.facilitator_id = facilitator.id")
            ->where($map)
            ->count();

        if ($totalCount) {
            $list = $this->alias("appraise")
                ->join("jpgk_wechat_order as orde on orde.id = appraise.order_id")
                ->join("jpgk_wechat_member as member on appraise.member_id = member.uid")
                ->join("jpgk_wechat_member as worker on appraise.workers_id = worker.uid")
                ->join("jpgk_wechat_facilitator as facilitator on orde.facilitator_id = facilitator.id")
                ->where($map)
                ->page($page, $r)
                ->order($order)
                ->field($field)
                ->select();
        }
        return array($list, $totalCount);
    }
    /**
     * 获取订单详情列表
     * @author  pangyongfu
     */
    public function getAppriaseListToExcel($map = [], $order = 'appraise.update_time desc', $field = '*')
    {
        $list = [];
        $totalCount = $this->alias("appraise")
            ->join("jpgk_wechat_order as orde on orde.id = appraise.order_id")
            ->join("jpgk_wechat_member as member on appraise.member_id = member.uid")
            ->join("jpgk_wechat_member as worker on appraise.workers_id = worker.uid")
            ->join("jpgk_wechat_facilitator as facilitator on orde.facilitator_id = facilitator.id")
            ->where($map)
            ->count();

        if ($totalCount) {
            $list = $this->alias("appraise")
                ->join("jpgk_wechat_order as orde on orde.id = appraise.order_id")
                ->join("jpgk_wechat_member as member on appraise.member_id = member.uid")
                ->join("jpgk_wechat_member as worker on appraise.workers_id = worker.uid")
                ->join("jpgk_wechat_facilitator as facilitator on orde.facilitator_id = facilitator.id")
                ->where($map)
                ->order($order)
                ->field($field)
                ->select();
        }
//        var_dump($this->getLastSql());die;
        return $list;
    }

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
}