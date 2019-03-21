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

class CommentModel extends Model
{

    protected $tableName = "local_comment";

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );

    public function getPageList($map = [],$page = 1,$sort = "coment.create_time desc",$field = "*",$r = 20){

        $trueMap = [
            "coment.app" => ["eq","SupplyMarket"],
            "coment.pid" => 0
        ];
        if(!empty($map["productName"])){

            $trueMap["pro.title"] = ["like","%{$map["productName"]}%"];
        }

        if(!empty($map["isReply"])){

            if($map["isReply"] == 1){

                $trueMap["_string"] = "!isnull(coment1.id)";

            }else{
                $trueMap["_string"] = "isnull(coment1.id)";
            }
        }

        $trueMap["pro.company_id"] = ["eq",$map["company_id"]];

        $count = $this->alias("coment")
            ->join("jpgk_supply_marketing_product as pro on coment.row_id = pro.id")
            ->join("left join jpgk_local_comment coment1 on (coment.id = coment1.pid  and coment1.`status` = 1)")
            ->where($trueMap)
            ->count();

        $list = [];
        if($count){

            $list = $this->alias("coment")
                         ->join("jpgk_supply_marketing_product as pro on coment.row_id = pro.id")
                         ->join("left join jpgk_local_comment coment1 on (coment.id = coment1.pid and coment1.`status` = 1)")
                         ->join("left join jpgk_ucenter_member as u on coment.uid = u.id")
                         ->where($trueMap)
                         ->page($page,$r)
                         ->order($sort)
                         ->field("coment.*,pro.title,coment1.content as replaycontent,coment1.create_time as replaytime,u.username,pro.id as pro_id")
                         ->select();
            //echo $this->getLastSql();die();
            foreach($list as &$tmp){

                $tmp["isReply"] = empty($tmp["replaycontent"])?0:1;
                $tmp["replaytime"] = $tmp["replaytime"]?date("Y-m-d H:i:s",$tmp["replaytime"]):"";
                $tmp["create_time"] = date("Y-m-d H:i:s",$tmp["create_time"]);
            }
        }

        return [$list,$count];
    }

    public function getNewsPageList($map = [],$page = 1,$sort = "coment.create_time desc",$field = "*",$r = 20){

        $trueMap = [
            "coment.app" => ["eq","News"],
            "coment.status" => 1,
            "coment.row_id" => $map['newsId']
        ];

//        $trueMap["pro.company_id"] = ["eq",$map["company_id"]];

        $count = $this->alias("coment")
            ->join("jpgk_news as news on coment.row_id = news.id")
            ->join("LEFT JOIN jpgk_member as member on coment.uid = member.uid")
            ->where($trueMap)
            ->count();

        $list = [];
        if($count){

            $list = $this->alias("coment")
                ->join("jpgk_news as news on coment.row_id = news.id")
                ->join("LEFT JOIN jpgk_member as member on coment.uid = member.uid")
                ->where($trueMap)
                ->page($page,$r)
                ->order($sort)
                ->field("coment.*,news.title as title,member.nickname")
                ->select();
//            foreach($list as &$tmp){
//
//                $tmp["replaytime"] = $tmp["replaytime"]?date("Y-m-d H:i:s",$tmp["replaytime"]):"";
//                $tmp["create_time"] = date("Y-m-d H:i:s",$tmp["create_time"]);
//            }
        }

        return [$list,$count];
    }
} 