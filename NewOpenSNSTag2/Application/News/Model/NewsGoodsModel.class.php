<?php

namespace News\Model;

use Think\Model;

class NewsGoodsModel extends Model
{

    public function editData($data)
    {
        $newsData['name'] = $data['name'];
        $newsData['category'] = $data['category'];
        $newsData['buy_amount'] = $data['buy_amount'];
        $newsData['unit'] = $data['unit'];
        $newsData['description'] = $data['description'];
        $newsData['id'] = $data['id'];
        $newsData['type'] = $data['type'];
        $newsData['update_time'] = Date('Y-m-d H:i:s');
        if ($data['id']) {
            $res = $this->save($newsData);

            $res = $res ? $data['id'] : $res;
        } else {
            $newsData['create_time'] = time();
            $newsData['id'] = md5(time().rand());

            $res = $this->add($newsData);
            $res = $newsData['id'] ? $newsData['id'] : $res;
            action_log('add_newsGoods', 'News', $res, is_login());
        }

        return $res;
    }

    public function getListByPage($map, $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {

        if(empty($map['category'])){
            unset($map['category']);
        }
        //publishDate为-1时查今天的 1时查昨天 2时查一周内 3时查一月内 4查全部
        if(!empty($map["publishDate"])){

            $havenTime = time() - strtotime(date("Y-m-d"));
            switch($map["publishDate"]){

                case -1:
                    $map["create_time"] = ["egt",time()-$havenTime];
                    break;
                case 1:
                    $map["create_time"] = ["between",[strtotime("-1 day") - $havenTime,time()-$havenTime]];
                    break;
                case 2:
                    $map["create_time"] = ["between",[strtotime("-7 day") - $havenTime,time()]];
                    break;
                case 3:
                    $map["create_time"] = ["between",[strtotime("-30 day") - $havenTime,time()]];
                    break;
            }
        }


        $totalCount = $this->where($map)->count();
        if ($totalCount) {
            $list = $this->where($map)->page($page, $r)->order($order)->field($field)->select();
        }
        return array($list, $totalCount);
    }

    public function getList($map, $order = 'view desc', $limit = 5, $field = '*')
    {
        $lists = $this->where($map)->order($order)->limit($limit)->select();
        return $lists;
    }

    public function setDead($ids)
    {
        !is_array($ids) && $ids = explode(',', $ids);
        $map['id'] = array('in', $ids);
        $res = $this->where($map)->setField('dead_line', time());
        return $res;
    }

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