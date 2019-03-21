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

class ProductModel extends Model
{

    protected $tableName = "supply_marketing_product";

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );

    /**
     * 获取分类详细信息
     * @param $id
     * @param bool $field
     * @return mixed
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function info($id, $field = true)
    {
        /* 获取分类信息 */
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
//        $data = $this->create();
        if ($data['id']) {
            $res = $this->save($data);
        } else {
            $res = $this->add($data);
        }
        return $res;
    }

    public function getListByPage($map, $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {

        //生成具体的查询条件 拼接搜索条件
        if(!empty($map["companyId"])) {
            $trueMap = [
                "company_id" => $map["companyId"]
            ];
        }
        if(!empty($map["productName"])){

            $trueMap["title"] = ["like","%{$map['productName']}%"];
        }

        if(!empty($map["publishDate"])){

            $havenTime = time() - strtotime(date("Y-m-d"));
            switch($map["publishDate"]){

                case -1:
                    $trueMap["create_time"] = ["egt",time()-$havenTime];
                    break;
                case 1:
                    $trueMap["create_time"] = ["between",[strtotime("-1 day") - $havenTime,time()-$havenTime]];
                    break;
                case 2:
                    $trueMap["create_time"] = ["between",[strtotime("-7 day") - $havenTime,time()]];
                    break;
                case 3:
                    $trueMap["create_time"] = ["between",[strtotime("-30 day") - $havenTime,time()]];
                    break;
            }
        }

        if(!empty($map["shenHeStatus"])){

            $trueMap["check_status"] = $map["shenHeStatus"];
        }

        if(!empty($map["category"])){

            $trueMap["two_level_category"] = $map["category"];
        }

        if(!empty($map['check_status'])){
            $trueMap["check_status"] = $map["check_status"];
        }

        if(!empty($map['productStatus'])){

            switch($map["productStatus"]){
                case 1:
                    $trueMap["status"] = 1;
                    break;
                case 2:
                    $trueMap["status"] = 0;
                    break;
            }
        }else{
            $trueMap["status"] = ["egt",0];
        }

        $totalCount = $this->where($trueMap)->count();

        if ($totalCount) {

            $list = $this->where($trueMap)->page($page, $r)->order($order)->field($field)->select();

            $productIdArr = array_column($list,"id");
            if(!empty($productIdArr)){

                $idstr = implode(",",$productIdArr);
                $curTime = time();
                //通过sql，查询统计信息
                $sql = "SELECT
                        p.*,
                        COUNT(DISTINCT(yuyue.id)) as 'yuyueshu',
                        count(DISTINCT(coment.id)) as 'zixunshu',
                        ser.id as isserver,
                        company.name as 'companyName'
                    FROM
                        jpgk_supply_marketing_product AS p
                    LEFT JOIN jpgk_supply_marketing_appointment AS yuyue ON p.id = yuyue.product_id
                    LEFT JOIN jpgk_local_comment as coment on (coment.app = 'SupplyMarket' AND coment.row_id = p.id AND coment.pid= 0)
                    LEFT JOIN jpgk_pay_server as ser on (ser.app = 'SupplyMarket' AND ser.row_id = p.id AND ser.end_time > {$curTime} AND ser.`status` = 1)
                    LEFT JOIN jpgk_supply_marketing_company as company ON company.id = p.company_id
                    where 1=1
                    AND  p.id in({$idstr})
                    GROUP BY p.id
                    ORDER BY p.update_time DESC";
                $list = $this->query($sql);
            }

        }

        foreach($list as &$tmp){

            if(!empty($tmp["isserver"])){

                $tmp["isserver"] = "置顶";
            }else{
                $tmp["isserver"] = "否";
            }

            $tmp["create_time"] = date("Y-m-d H:i:s",$tmp["create_time"]);

            switch($tmp["check_status"]){

                case 1:
                    $tmp["checkstatustext"] = "审核中";
                    break;
                case 2:
                    $tmp["checkstatustext"] = "审核通过";
                    break;
            }
        }
        return array($list, $totalCount);
    }

    //导出Excels数据
    public function getListData($map, $order = 'update_time desc', $field = '*')
    {

        //生成具体的查询条件 拼接搜索条件
        if(!empty($map["companyId"])) {
            $trueMap = [
                "company_id" => $map["companyId"]
            ];
        }
        if(!empty($map["productName"])){

            $trueMap["title"] = ["like","%{$map['productName']}%"];
        }

        if(!empty($map["publishDate"])){

            $havenTime = time() - strtotime(date("Y-m-d"));
            switch($map["publishDate"]){

                case -1:
                    $trueMap["create_time"] = ["egt",time()-$havenTime];
                    break;
                case 1:
                    $trueMap["create_time"] = ["between",[strtotime("-1 day") - $havenTime,time()-$havenTime]];
                    break;
                case 2:
                    $trueMap["create_time"] = ["between",[strtotime("-7 day") - $havenTime,time()]];
                    break;
                case 3:
                    $trueMap["create_time"] = ["between",[strtotime("-30 day") - $havenTime,time()]];
                    break;
            }
        }

        if(!empty($map["shenHeStatus"])){

            $trueMap["check_status"] = $map["shenHeStatus"];
        }

        if(!empty($map["category"])){

            $trueMap["two_level_category"] = $map["category"];
        }

        if(!empty($map['check_status'])){
            $trueMap["check_status"] = $map["check_status"];
        }

        if(!empty($map['productStatus'])){

            switch($map["productStatus"]){
                case 1:
                    $trueMap["status"] = 1;
                    break;
                case 2:
                    $trueMap["status"] = 0;
                    break;
            }
        }else{
            $trueMap["status"] = ["egt",0];
        }

        $totalCount = $this->where($trueMap)->count();

        if ($totalCount) {

            $list = $this->where($trueMap)->order($order)->field($field)->select();

            $productIdArr = array_column($list,"id");
            if(!empty($productIdArr)){

                $idstr = implode(",",$productIdArr);
                $curTime = time();
                //通过sql，查询统计信息
                $sql = "SELECT
                        p.*,
                        COUNT(DISTINCT(yuyue.id)) as 'yuyueshu',
                        count(DISTINCT(coment.id)) as 'zixunshu',
                        ser.id as isserver,
                        company.name as 'companyName'
                    FROM
                        jpgk_supply_marketing_product AS p
                    LEFT JOIN jpgk_supply_marketing_appointment AS yuyue ON p.id = yuyue.product_id
                    LEFT JOIN jpgk_local_comment as coment on (coment.app = 'SupplyMarket' AND coment.row_id = p.id AND coment.pid= 0)
                    LEFT JOIN jpgk_pay_server as ser on (ser.app = 'SupplyMarket' AND ser.row_id = p.id AND ser.end_time > {$curTime} AND ser.`status` = 1)
                    LEFT JOIN jpgk_supply_marketing_company as company ON company.id = p.company_id
                    where 1=1
                    AND  p.id in({$idstr})
                    GROUP BY p.id
                    ORDER BY p.update_time DESC";
                $list = $this->query($sql);
            }

        }

        foreach($list as &$tmp){

            if(!empty($tmp["isserver"])){

                $tmp["isserver"] = "置顶";
            }else{
                $tmp["isserver"] = "否";
            }

            $tmp["create_time"] = date("Y-m-d H:i:s",$tmp["create_time"]);

            switch($tmp["check_status"]){

                case 1:
                    $tmp["checkstatustext"] = "审核中";
                    break;
                case 2:
                    $tmp["checkstatustext"] = "审核通过";
                    break;
            }
        }
        return $list;
    }
} 