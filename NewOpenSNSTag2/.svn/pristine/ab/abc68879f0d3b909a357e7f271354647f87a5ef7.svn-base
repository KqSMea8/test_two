<?php

namespace News\Model;

use Think\Model;

class NewsOrderModel extends Model
{

    public function editData($data)
    {
        if (!mb_strlen($data['description'], 'utf-8')) {
            $data['description'] = msubstr(op_t($data['content']), 0, 200);
        }
        $detail['content'] = $data['content'];
        $detail['template'] = $data['template'];
        $data['reason'] = '';
        if ($data['id']) {
            $data['update_time'] = time();
            $res = $this->save($data);
            $detail['news_id'] = $data['id'];
        } else {
            $data['create_time'] = $data['update_time'] = time();
            $res = $this->add($data);
            action_log('add_news', 'News', $res, is_login());
            $detail['news_id'] = $res;
        }
        if ($res) {
            D('News/NewsDetail')->editData($detail);
        }
        return $res;
    }

    public function getPageList($map = [],$page = 1,$sort = "newsOrder.create_time desc",$field = "*",$r = 20){

        $trueMap = [
//            "order.app" => ["eq","SupplyMarket"],
            "newsOrder.status" => 1
        ];

        //拼接搜索商品名字
        if(!empty($map["goodsName"])){

            $trueMap["goods.name"] = ["like","%{$map["goodsName"]}%"];
        }

        //拼接搜索商品分类
        if(!empty($map["category"])){

            $trueMap["goods.category"] = $map["category"];
        }

        if(!empty($map["phone"])){

            $trueMap["newsOrder.phone"] = ["like","%{$map["phone"]}%"];
        }

        $count = $this->alias("newsOrder")
            ->join("left join jpgk_news_goods as goods on goods.id = newsOrder.goods_id")
            ->join("left join jpgk_news_category as category on category.id = goods.category")
            ->where($trueMap)
            ->count();

        $list = [];
        if($count){

            $list = $this->alias("newsOrder")
                ->field($field)
                ->join("left join jpgk_news_goods as goods on goods.id = newsOrder.goods_id")
                ->join("left join jpgk_news_category as category on category.id = goods.category")
                ->where($trueMap)
                ->order($sort)
                ->page($page,$r)
                ->select();
        }

        return [$list,$count];
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