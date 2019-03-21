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

class AppointmentModel extends Model
{

    protected $tableName = "supply_marketing_appointment";

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );

    public function getPageList($map = [],$page = 1,$sort = "coment.create_time desc",$field = "*",$r = 20){

        $trueMap = [];
        if(!empty($map["company_id"])){
            $trueMap["pro.company_id"] = ["eq",$map["company_id"]];
        }

        if(!empty($map["productName"])){

            $trueMap["pro.title"] = ["like","%{$map["productName"]}%"];
        }

        if(!empty($map["status"])){

            $map["appointment.status"] = $map["status"];
        }



        $count = $this->alias("appointment")
            ->join("jpgk_supply_marketing_product as pro on appointment.product_id = pro.id")
            ->where($trueMap)
            ->count();
//echo $this->getLastSql();die();
        $list = [];
        if($count){

            $list = $this->alias("appointment")
                         ->join("jpgk_supply_marketing_product as pro on appointment.product_id = pro.id")
                         ->join("left join jpgk_ucenter_member as u on appointment.uid = u.id")
                         ->where($trueMap)
                         ->page($page,$r)
                         ->order($sort)
                         ->field("appointment.*,pro.title,u.username,u.mobile")
                         ->select();
            //echo $this->getLastSql();die();
            foreach($list as &$tmp){

                $tmp["appointmentTimeText"] = date("Y-m-d H:i:s",$tmp["appointment_time"]);
                switch($tmp["status"]){

                    case 1:
                        $tmp["statusText"] = "预约中";
                        break;
                    case 2:
                        $tmp["statusText"] = "预约成功";
                        break;
                    case 3:
                        $tmp["statusText"] = "用户取消";
                        break;
                    case 4:
                        $tmp["statusText"] = "完成交易";
                        break;
                    case 5:
                        $tmp["statusText"] = "发布方取消";
                        break;
                }
            }
        }

        return [$list,$count];
    }
} 