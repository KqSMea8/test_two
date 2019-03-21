<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * @author HQ
 */

namespace CircleVideo\Model;

use Think\Model;
use Common\Model\ContentHandlerModel;

class CircleVideoDetailModel extends Model
{

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );

    /**
     * 获取数据详细信息
     * @param $id
     * @param bool $field
     * @return mixed
     */
    public function info($id, $field = true)
    {
        /* 获取单个课程信息 */
        $map = array();
        if (is_numeric($id)) { //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['title'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    public function editData($data)
    {
        if ($data['video_id']) {
            $detailData = (new CircleVideoDetailModel())->where(['video_id'=>$data['video_id']])->find();
            if(!$detailData){
                $res = (new CircleVideoDetailModel())->add($data);
            }else{
                $res = (new CircleVideoDetailModel())->save($data);
            }
            return $res;
        }
    }

    public function getData($id)
    {
        $contentHandler = new ContentHandlerModel();
        $res = $this->where(array('video_id' => $id))->find();
        $res['content'] = attr_imgjs($res['content']);
        return $res;
    }


} 