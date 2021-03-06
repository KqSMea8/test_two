<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 下午1:22
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace News\Model;

use Think\Model;

class NewsModel extends Model
{

    public function editData($data)
    {
        if($data['description']){
            if (!mb_strlen($data['description'], 'utf-8')) {
                $data['description'] = msubstr(trimall(op_t($data['content'])), 0, 200);
            }
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

    public function getListByPage($map, $page = 1, $order = 'update_time desc', $field = '*', $r = 20)
    {
        //newsDate为 1时查今天的 2时查昨天 3时查一周内 4时查一月内 0查全部
        if(!empty($map["newsDate"])){

            $havenTime = time() - strtotime(date("Y-m-d"));
            switch($map["newsDate"]){

                case 1:
                    $map["create_time"] = ["egt",time()-$havenTime];
                    break;
                case 2:
                    $map["create_time"] = ["between",[strtotime("-1 day") - $havenTime,time()-$havenTime]];
                    break;
                case 3:
                    $map["create_time"] = ["between",[strtotime("-7 day") - $havenTime,time()]];
                    break;
                case 4:
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
            if ($data) {
                $data['detail'] = D('News/NewsDetail')->getData($id);
            }
            return $data;
        }
        return null;
    }

    /**
     * 获取推荐位数据列表
     * @param $pos 推荐位 1-系统首页，2-推荐阅读，4-本类推荐
     * @param null $category
     * @param $limit
     * @param bool $field
     * @return mixed
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function position($pos, $category = null, $limit = 5, $field = true, $order = 'sort desc,view desc')
    {
        $map = $this->listMap($category, 1, $pos);
        $res = $this->field($field)->where($map)->order($order)->limit($limit)->select();
        /* 读取数据 */
        return $res;
    }

    /**
     * 设置where查询条件
     * @param  number $category 分类ID
     * @param  number $pos 推荐位
     * @param  integer $status 状态
     * @return array             查询条件
     */
    private function listMap($category, $status = 1, $pos = null)
    {
        /* 设置状态 */
        $map = array('status' => $status);

        /* 设置分类 */
        if (!is_null($category)) {
            $cates = D('News/NewsCategory')->getCategoryList(array('pid' => $category, 'status' => 1));
            $cates = array_column($cates, 'id');
            $map['category'] = array('in', array_merge(array($category), $cates));
        }
        $map['dead_line'] = array('gt', time());

        /* 设置推荐位 */
        if (is_numeric($pos)) {
            $map[] = "position & {$pos} = {$pos}";
        }

        return $map;
    }

} 