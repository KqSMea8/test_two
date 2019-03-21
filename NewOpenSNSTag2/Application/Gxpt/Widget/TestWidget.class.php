<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-30
 * Time: 下午1:28
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace GXPT\Widget;


use Think\Controller;

class TestWidget extends Controller
{
    public function test(){

        $this->display("./Application/Gxpt/View/default/Gxpt/selectcategory.html");
    }
} 