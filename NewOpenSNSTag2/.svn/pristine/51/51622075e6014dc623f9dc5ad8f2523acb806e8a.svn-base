<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 下午1:22
 */

namespace Home\Model;

use Think\Model;

class DistrictModel extends Model
{
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