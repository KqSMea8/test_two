<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-30
 * Time: 下午1:28
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace News\Widget;
use Think\Controller;

class TestWidget extends Controller
{
    public function test(){

        $this->display("./Application/News/View/default/News/selectcategory.html");
    }
} 