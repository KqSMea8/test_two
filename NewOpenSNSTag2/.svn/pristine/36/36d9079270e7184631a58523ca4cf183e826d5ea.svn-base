<?php

namespace Addons\Lunbo;
use Common\Controller\Addon;
/**
 * 轮播图插件
 * @author 梦在想我<open@yangtzeu.edu.cn>
 */

    class LunboAddon extends Addon{

        public $info = array(
            'name'=>'Lunbo',
            'title'=>'轮播图',
            'description'=>'轮播图插件',
            'status'=>1,
            'author'=>'梦在想我',
            'version'=>'0.1',
			'has_adminlist'=>'1',
        );

        public $admin_list = array(
							'model'=>'imageslider',		//要查的表
                            'fields'=>'paixu,url,link,status',			//要查的字段
                            'map'=>'status=1',				//查询条件, 如果需要可以再插件类的构造方法里动态重置这个属性
                            'order'=>'paixu desc',		//排序,
                            'listKey'=>array( 		//这里定义的是除了id序号外的表格里字段显示的表头名
                            'paixu'=>'排序',
							'link'=>'链接',
							'url'=>'路径',
							'link'=>'链接',
							'status'=>'启用状态'
                            ),
        );

        public function install(){
		$prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS `{$prefix}imageslider`");
        $model->execute("
CREATE TABLE IF NOT EXISTS `{$prefix}imageslider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paixu` int(11) NOT NULL DEFAULT 0,
  `url` varchar(500) NOT NULL,
  `link` varchar(500) NOT NULL,
  `status` int(2) NOT NULL DEFAULT 1,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");
        $model->execute("
INSERT INTO `{$prefix}imageslider`  (paixu,url,link,status,create_time,update_time) VALUES
              (1,'Uploads/Lunbo/show-1.jpg','http://wk.meijialz.com','1',1444444444,1444444444),
              (2,'Uploads/Lunbo/show-2.jpg','http://wk.meijialz.com','1',1444444444,1444444444),
              (3,'Uploads/Lunbo/show-3.jpg','http://wk.meijialz.com','1',1444444444,1444444444),
              (4,'Uploads/Lunbo/show-4.jpg','http://wk.meijialz.com','1',1444444444,1444444444),
              (5,'Uploads/Lunbo/show-5.jpg','http://wk.meijialz.com','1',1444444444,1444444444),
              (6,'Uploads/Lunbo/show-6.jpg','http://wk.meijialz.com','1',1444444444,1444444444),
              (1,'Uploads/Lunbo/show-7.jpg','http://wk.meijialz.com','1',1444444444,1444444444)
");
            return true;
        }

        public function uninstall(){
			$prefix = C("DB_PREFIX");
			D()->execute("DROP TABLE IF EXISTS `{$prefix}imageslider`");
            return true;
        }

        //实现的imageSlider钩子方法
        public function imageSlider($param){
            $map = array(
                'status'=>1
            );
            $imageSilider = M('imageslider')->where($map)->field('url,link')->limit(8)->order('paixu desc')->select();
            $this->assign('param', $param);
            $this->assign('imageLinks', $imageSilider);
			$this->display (T('Addons://Lunbo@Lunbo/lunbo'));     //  页面上的report
        }

    }