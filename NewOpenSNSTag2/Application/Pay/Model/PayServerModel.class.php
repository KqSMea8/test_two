<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author HQ
 * */

namespace Pay\Model;

use Pay\Conf\Conf;
use Think\Model;

class PayServerModel extends Model
{

    public function addData($data)
    {
        if(!$this->create($data)){
            return false;
        }

        $reutrn = $this->add($data);
        return $reutrn;
    }

    //获取指定模块购买服务的数据
    public function getPayServerListOfRunning($app,$rowIdArr = null){

        $whereMap = [];
        $whereMap["app"] = $app;
        $whereMap["start_time"] = ["lt",time()];
        $whereMap["end_time"] = ["egt",time()];
        $whereMap["status"] = Conf::PAY_SERVICE_STATUS_YES;

        if(!empty($rowIdArr)){

            $whereMap["row_id"] = ["in",$rowIdArr];
        }

        $servers = $this->where($whereMap)->select();

        return $servers;
    }

    //查询指定的数据是否购买了服务
    public function getDataPayServerOfRunning($app,$rowId){

        $whereMap = [];
        $whereMap["app"] = $app;
        $whereMap["start_time"] = ["lt",time()];
        $whereMap["end_time"] = ["egt",time()];
        $whereMap["status"] = Conf::PAY_SERVICE_STATUS_YES;
        $whereMap["row_id"] = ["eq",$rowId];


        $server = $this->where($whereMap)->find();

        return $server;
    }
}