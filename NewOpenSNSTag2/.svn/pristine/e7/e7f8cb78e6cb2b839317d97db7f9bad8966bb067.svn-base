<?php
/**
 * Created by PhpStorm.
 * User: pangyongfu
 * Date: 2017/12/19
 * Time: 15:56
 */

namespace Wechat\Model;


use Think\Model;

class WechatTicketModel extends Model
{

    public function createTicket($data){

    }

    public function getListByPage($map = [], $page = 1, $order = 'ticket.update_time desc', $field = '*', $r = 20){
        if($map["selectDate"]){

            switch($map["selectDate"]){

                case 1:
                    $map["ticket.create_time"] = ['like','%'.date("Y-m-d").'%'];
                    break;
                case 2:
                    $map["ticket.create_time"] = ['like','%'.date("Y-m-d",strtotime("-1 day")).'%'];
                    break;
                case 3:
                    $map["ticket.create_time"] = ["between",[date("Y-m-d",strtotime("-7 day"))." 00:00:00",date("Y-m-d")." 23:59:59"]];
                    break;
                case 4:
                    $map["ticket.create_time"] = ["between",[date("Y-m-d",strtotime("-30 day"))." 00:00:00",date("Y-m-d")." 23:59:59"]];
                    break;
            }
            unset($map['selectDate']);
        }

        $total = $this->alias("ticket")
            ->join("jpgk_wechat_ticket_order torder ON ticket.id = torder.ticket_id")
            ->join("jpgk_wechat_order as item on torder.order_id = item.id")
            ->join("jpgk_wechat_member as member on ticket.member_id = member.uid")
            ->where($map)
            ->field($field)
            ->group("ticket.id")
            ->select();
        $totalCount = count($total);
        if ($totalCount) {
            $list = $this->alias("ticket")
                ->join("jpgk_wechat_ticket_order torder ON ticket.id = torder.ticket_id")
                ->join("jpgk_wechat_order as item on torder.order_id = item.id")
                ->join("jpgk_wechat_member as member on ticket.member_id = member.uid")
                ->where($map)
                ->page($page, $r)
                ->order($order)
                ->field($field)
                ->group("ticket.id")
                ->select();
        }
//        var_dump($this->getLastSql());die;
        return array($list, $totalCount);
    }
    public function getListToExcel($map = [],$order = 'ticket.update_time desc', $field = '*'){
        if($map["selectDate"]){

            switch($map["selectDate"]){

                case 1:
                    $map["ticket.create_time"] = ['like','%'.date("Y-m-d").'%'];
                    break;
                case 2:
                    $map["ticket.create_time"] = ['like','%'.date("Y-m-d",strtotime("-1 day")).'%'];
                    break;
                case 3:
                    $map["ticket.create_time"] = ["between",[date("Y-m-d",strtotime("-7 day"))." 00:00:00",date("Y-m-d")." 23:59:59"]];
                    break;
                case 4:
                    $map["ticket.create_time"] = ["between",[date("Y-m-d",strtotime("-30 day"))." 00:00:00",date("Y-m-d")." 23:59:59"]];
                    break;
            }
            unset($map['selectDate']);
        }
        $list = [];
        $totalCount = $this->alias("ticket")
            ->join("jpgk_wechat_ticket_order torder ON ticket.id = torder.ticket_id")
            ->join("jpgk_wechat_order as item on torder.order_id = item.id")
            ->join("jpgk_wechat_member as member on ticket.member_id = member.uid")
            ->where($map)
            ->order($order)
            ->field($field)
            ->group("ticket.id")
            ->count();

        if ($totalCount) {
            $list = $this->alias("ticket")
                ->join("jpgk_wechat_ticket_order torder ON ticket.id = torder.ticket_id")
                ->join("jpgk_wechat_order as item on torder.order_id = item.id")
                ->join("jpgk_wechat_member as member on ticket.member_id = member.uid")
                ->where($map)
                ->order($order)
                ->field($field)
                ->group("ticket.id")
                ->select();
        }
//        var_dump($this->getLastSql());die;
        return $list;
    }
    public function getInvoiceDetail($map,$field = '*'){
        $data = $this->alias("ticket")
            ->join("jpgk_wechat_ticket_order torder ON ticket.id = torder.ticket_id")
            ->where($map)
            ->field($field)
            ->group("ticket.id")
            ->find();
//        var_dump($this->getLastSql());die;
        return $data;
    }
}