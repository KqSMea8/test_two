<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/7
 * Time: 15:05
 */

namespace Wechat\Model;


use Think\Model;

class WechatMemberStoreModel extends Model
{
    /**
     * 修改
     * @param $data
     * @return bool|mixed
     */
    public function editInfo($data){
        if ($data['id']) {
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->save($data);
        }
        return $res;
    }

    /**
     * 添加绑定门店
     * @param $data
     * @return mixed
     */
    public function addInfo($data){
        $data['create_time'] = date('Y-m-d H:i:s');
        $res = $this->add($data);
        return $res;
    }

    /**
     * 根据用户id删除信息
     * @param $data
     * @return mixed
     */
    public function delInfoByUid($data){
        if($data['u_id']){
            $res = $this->where(['u_id'=>$data['u_id']])->delete();
        }
        return $res;
    }

    /**
     * 获取企业绑定门店信息
     * @param array $map
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getMemberStoreInfo($map = [], $order = '', $field = '*')
    {
        $totalCount = $this->alias("member")->where($map)->count();
        $list = [];
        if ($totalCount) {
            $list = $this->alias("member")->where($map)->order($order)->field($field)->select();
        }
        return $list;
    }
    /**
     * 获取企业绑定门店信息
     * @param array $map
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getOneMemberStoreInfo($map = [], $order = '', $field = '*')
    {

        $list = $this->alias("member")->where($map)->order($order)->field($field)->find();
        return $list;
    }

    /**
     * 获取用户绑定门店的单条数据
     */
    public function getMemberStoreInfoByWhere($where = [],$field = '*',$order = ''){
        $list = $this->alias('userStore')
            ->join('jpgk_wechat_member member on member.uid = userStore.u_id')
            ->where($where)
            ->order($order)
            ->field($field)
            ->select();
        return $list;
    }

}