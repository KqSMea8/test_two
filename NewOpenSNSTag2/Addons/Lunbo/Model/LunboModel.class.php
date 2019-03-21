<?php
namespace Addons\Lunbo\Model;

use Think\Model;

class LunboModel extends Model
{
    protected $tableName = 'imageslider';
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', 1, self::MODEL_BOTH),
    );
    public function test()
    {
      exit('fsdfdsfdsf');
    }
    public function addData($data = array())
    {
        $data = $this->create($data);
        return $this->add($data);
    }


}