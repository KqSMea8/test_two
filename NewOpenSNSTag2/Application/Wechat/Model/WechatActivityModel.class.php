<?php
/**
 * Created by PhpStorm.
 * User: pangyongfu
 * Date: 2018/1/23
 * Time: 14:58
 */

namespace Wechat\Model;


use Think\Model;

class WechatActivityModel extends Model
{
    /**
     * 获取分页体验活动数据
     * @param array $map
     * @param int $page
     * @param string $order
     * @param string $field
     * @param int $r
     * @return array
     */
    public function getListByPage($map = [], $page = 1, $order = 'activity.create_time desc', $field = '*', $r = 20)
    {
        $totalCount = $this->alias("activity")
            ->join("left join jpgk_wechat_member member on activity.uid = member.uid")
            ->join("left join jpgk_district province on activity.province_id = province.id")
            ->join("left join jpgk_district city on activity.city_id = city.id")
            ->where($map)
            ->count();
        $list = [];
        if ($totalCount) {
            $list =  $this->alias("activity")
                ->join("left join jpgk_wechat_member member on activity.uid = member.uid")
                ->join("left join jpgk_district province on activity.province_id = province.id")
                ->join("left join jpgk_district city on activity.city_id = city.id")
                ->where($map)
                ->page($page, $r)
                ->order($order)
                ->field($field)
                ->select();
        }
//        var_dump($this->getLastSql());die;
        return array($list, $totalCount);
    }
}