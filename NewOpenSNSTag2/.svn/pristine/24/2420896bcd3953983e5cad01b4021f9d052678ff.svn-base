<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 下午1:22
 */

namespace Goods\Model;

use Think\Model;

class UsedMarketOrderModel extends Model
{
    public function getListByPage($map, $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->where($map)->count();
        if ($totalCount) {
            $list = $this->where($map)->page($page, $r)->order($order)->field($field)->select();
        }
        return array($list, $totalCount);
    }

    public function getOrderListByPage($map, $page = 1, $order = 'usedMarketOrder.update_time desc', $field = '*', $r = 20){

        //newsDate为 1时查今天的 2时查昨天 3时查一周内 4时查一月内 0查全部
        if(!empty($map["newsDate"])){

            $havenTime = time() - strtotime(date("Y-m-d"));
            switch($map["newsDate"]){

                case 1:
                    $map["usedMarketOrder.create_time"] = ['like','%'.date("Y-m-d").'%'];
                    break;
                case 2:
                    $map["usedMarketOrder.create_time"] = ['like','%'.date("Y-m-d",strtotime("-1 day")).'%'];
                    break;
                case 3:
                    $map["usedMarketOrder.create_time"] = ["between",[date("Y-m-d",strtotime("-7 day"))." 00:00:00",date("Y-m-d")." 23:59:59"]];
                    break;
                case 4:
                    $map["usedMarketOrder.create_time"] = ["between",[date("Y-m-d",strtotime("-30 day"))." 00:00:00",date("Y-m-d")." 23:59:59"]];
                    break;
            }
            unset($map['newsDate']);
        }

        $totalCount = $this->alias("usedMarketOrder")
            ->join("jpgk_used_market_order_goods as goods on goods.order_id = usedMarketOrder.id")
            ->join("jpgk_pay as pay on pay.id = usedMarketOrder.pay_id")
            ->where($map)
            ->field('sum(CASE WHEN goods.order_id = usedMarketOrder.id THEN 1 ELSE 0 END ) as goodsNum')
            ->count();

        $list = [];
        if ($totalCount) {

            $list = $this->alias("usedMarketOrder")
                ->join("jpgk_used_market_order_goods as goods on goods.order_id = usedMarketOrder.id")
                ->join("jpgk_pay as pay on pay.id = usedMarketOrder.pay_id")
                ->where($map)
                ->page($page, $r)
                ->field($field)
                ->group('goods.order_id')
                ->order($order)
                ->select();
        }
        return [$list, $totalCount];
    }

    public function getList($map, $order = 'update_time desc', $limit = 5, $field = '*')
    {
        $lists = $this->where($map)->order($order)->limit($limit)->select();
        return $lists;
    }

    public function getData($id)
    {
        if ($id > 0) {
            $map['id'] = $id;
            $data = $this->where($map)->find();
            return $data;
        }
        return null;
    }
}