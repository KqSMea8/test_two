<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author HQ
 */

namespace Wechat\Model;

use Think\Model;
use Think\Log;

class WechatOrderCrontabModel extends Model
{



    /**
     * 获取数据详细信息
     * @param $id
     * @param bool $field
     * @return mixed
     */
    public function getDataInfo($map, $field = true)
    {
        $res = $this->where($map)->field($field)->find();
        return $res;
    }

    /**
     * 添加
     * @param $data
     */
    public function addInfo($data){

        $res = $this->add($data);
        return $res;
    }


} 