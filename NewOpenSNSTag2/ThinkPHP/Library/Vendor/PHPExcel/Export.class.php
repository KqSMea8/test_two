<?php
require_once('PHPExcel.php');
/**
 * 新Excel导出类文件
 * Enter description here ...
 * @author M
 *
 */
class Export{

	/**
	 * 						《excel导出传递参数信息》
	 * ======================================================================================================================
	 * 
	 * $info		表头信息			array类型	<!--四个键值必须都有-->
	 * 				参数如下：
	 * 				table_name_one	表格第一行大标题
	 * 				table_name_two	表格第二行大标题
	 * 				lister			制表人
	 * 				tabletime		制表时间
	 * ======================================================================================================================
	 * 
	 * $title		表格列标题信息	array类型  <!--必须传递数据,导出数据标题最大支持二级标题-->
	 * 				分两种情况
	 *		-----------------------------------------------------------------------------------------------------------------
	 *				《第一种是使用一级栏目》格式如下：
	 *				row				一级栏目则写1
	 *				title			列标题栏目，使用一维数组信息
	 *								例：array('一级父类1','一级父类2','一级父类3'……)依此类推
	 * 		-----------------------------------------------------------------------------------------------------------------
	 * 				《第二种是使用二级栏目》格式如下：
	 * 				row				二级栏目则填2	
	 * 				count			总共多少列
	 * 				title			列标题栏目
	 * 								标题栏目用数组形式显示每一列数据，若是二级的则是使用二级数组形式(键值要数字索引)
	 * 								例：array('一级父类',array('二级子类1','二级子类2','二级子类3'……))依此类推
	 * 				array ( 
	 *						'row' => 2, 		
	 *						'count' => 40, 		
	 *						'title' => array( 0 => array ( 0 => '日期', ),
	 *										  1 => array ( 0 => '消费券', 
	 *													   1 => array ( 0 => '新增发放', 1 => '今日消费', 2 => '消费单数')
	 *									)
	 *					)
	 *				);
	 * ======================================================================================================================
	 *
	 * $data		表格内容信息		array类型	<!--必须传递数据-->
	 * 				表数据以二维数组形式传入即可,数组的键名任意即可
	 * 				例如
	 * 				array( 
	 *					0 => array ( 'm01' => '0.00', 'm02' => '0.00'  ), 
	 *					1 => array ('m01' => '10.00', 'm02' => '0.00'), 
	 *					2 => array ( 'm01' => '30.00', 'm02' => '0.00'), 
	 *					 3 => array ('m01' => '10.00', 'm02' => '0.00')
	 *					 );
	 * ======================================================================================================================
	 * 
	 * $optional	可选参数		array类型	<!--本参数只是修饰excel表格显示样式，可以不传递数据-->
	 * 
	 * 			键值如下：
	 * 		-----------------------------------------------------------------------------------------------------------------
	 * 			colwidth		指定列宽度	
	 * 							例如：指定“A”列的宽度为“50”,这在传值时写成array("A"=>'50');
	 * 							即array('列标'=>'列宽')若有多个依此类推
	 * 			fontsize		指定文字大小	
	 * 							例如：指定“A1,B2”文字大小为“50”,这在传值时写成array("A1"=>'50',"B2"=>'50');
	 * 							例如：指定“A1-G1”文字大小为“50”,这在传值时写成array("A1:G1"=>'50');
	 * 							例如：指定“A1-A11”文字大小为“50”,这在传值时写成array("A1:A11"=>'50');
	 * 							即array('列标'=>'文字大小')若有多个依此类推
	 * 			lineheight		指定行高	
	 * 							例如：指定“A1,B2”行高为“50”,这在传值时写成array("A1"=>'50',"B2"=>'50');
	 * 							例如：指定“A1-G1”行高为“50”,这在传值时写成array("A1:G1"=>'50');
	 * 							例如：指定“A1-A11”行高为“50”,这在传值时写成array("A1:A11"=>'50');
	 * 							即array('列标'=>'行高')若有多个依此类推
	 * 		-----------------------------------------------------------------------------------------------------------------
	 * 			title_align_one	设置第一行大标题对齐方式，传递格式直接写数字代表对齐方式，数字含义与colalign值相同
	 * 		-----------------------------------------------------------------------------------------------------------------
	 *			title_align_two	设置第二行大标题对齐方式，传递格式直接写数字代表对齐方式，数字含义与colalign值相同
	 *		-----------------------------------------------------------------------------------------------------------------
	 * 			colalign		指定列对齐方式
	 * 							右对齐		传值“0”
	 * 							左对齐		传值“1”
	 * 							居中对齐		传值“2”
	 * 							例如：指定“A”列为居中对齐,这在传值时写成array("A"=>'2');
	 * 							即array('列标'=>'对齐值')若有多个依此类推
	 * 		-----------------------------------------------------------------------------------------------------------------
	 * 			numberformat	指定某一列数字显示格式，（默认：按照千分位分割方式显示数字，保留两位小数）
	 *							例如：指定“A”列为默认格式,这在传值时写成array("A"=>'');使用默认格式则“数组值为空”，
	 *							否则指定数据格式;即array('列标'=>'显示格式'),若有多个依此类推
	 *		-----------------------------------------------------------------------------------------------------------------
	 *			colSum			指定列求和
	 *							例如：指定“A”的数据求和，则array('A')，即array('列标'),若有多个依此类推
	 *		-----------------------------------------------------------------------------------------------------------------
	 *			colAverage		指定列求平均值
	 *							例如：指定“A”的数据求和，则array('A')，即array('列标'),若有多个依此类推
	 *		-----------------------------------------------------------------------------------------------------------------
	 *			colOrder		按照指定列对数据进行排序
	 *							例如：指定“A”列的数据是下标为mid的则array('mid'),即array('字段键名'),若有多个依此类推
	 * 		-----------------------------------------------------------------------------------------------------------------
	 * 
	 * ======================================================================================================================
	 */
	
	public static function excel($info,$title,$data,$optional=array(),$dir = null){

		set_time_limit(0);
		ini_set('memory_limit','1024M');
		if(!array_key_exists('table_name_two', $info)) die('表格第二行大标题不存在');
		
		//过滤文件名中的特殊符合
		$delstr = array('*', ':', '/', '\\', '?', '[', ']');
		//第二行标题名称，同时也是工作区名称
		$table_name_two = htmlspecialchars_decode(str_replace($delstr, ' ', $info['table_name_two']));
		
		//开始使用PHPExcel类进行数据整理导出
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");
		//定义表头信息
		$objPHPExcel->setActiveSheetIndex(0);
		
		//sheet标题
		$sheettile = mb_substr($table_name_two,0,31);
		$sheet = self::Excel_Sheet($objPHPExcel);
		$sheet->setTitle("$sheettile");
		
		//表格第一行大标题
		if(!array_key_exists('table_name_one', $info)) throw new AwException('表格第一行大标题不存在');
		$table_name_one = htmlspecialchars_decode($info['table_name_one']);
		
		$title_row = $title['row'];
		if($title_row != 1 and $title_row != 2) die('报表栏目行数不能超过两行');
		
		//统计有多少列数据信息
		if($title_row == 2){
			$rowNum = $title['count'];
			$sheet->FreezePane('A5');
			//是否在头部标题加空格
			$top_num_one = 100;
			$top_num_two = 100;
		}else{
			$rowNum = count($title['title']);
			$sheet->FreezePane('A4');
			$top_num_one = 0;
			$top_num_two = 0;
		}
		
		//调用Excel列字母
		$letter = self::cycleLetter($rowNum);
		//获取最后一个字母
		$finalLetter = $letter[$rowNum-1];
		$top_one_row = "A1:{$finalLetter}1";
		$top_tow_row = "A2:{$finalLetter}2";
		
		//设置顶部第一行头信息对齐
		if($top_num_one == 0 || array_key_exists('title_align_one', $optional)){
			if(array_key_exists('title_align_one', $optional)){
				$align_top = $optional['title_align_one'];
			}else{
				$align_top = 2;
			}
			$top_num_one == 0;
			self::Excel_horizontal($sheet,  $top_one_row,$align_top);
		}
		//设置顶部第二行头信息对齐
		if($top_num_two == 0 || array_key_exists('title_align_two', $optional)){
			if(array_key_exists('title_align_two', $optional)){
				$align_top = $optional['title_align_two'];
			}else{
				$align_top = 2;
			}
			$top_num_two == 0;
			self::Excel_horizontal($sheet,  $top_tow_row,$align_top);
		}
		
		self::Exce_topValue($sheet, "A1", $table_name_one, $top_one_row,$top_num_one);
		self::Exce_topValue($sheet, "A2", $table_name_two, $top_tow_row,$top_num_two);
		$sheet->getStyle('A1:A2')->getFont()->setBold(true);
		
		//计算内容有多少条记录
		$contentRow = count($data);
		
		//打印边框
		self::Excel_border($sheet, "A3:{$finalLetter}3", 2);
		
		//设置excel表头
		if($title_row == 2){
			$menuBold = 'A3:'.$finalLetter.'4';
			self::Excel_border($sheet, "A4:{$finalLetter}4", 2);
			$cornum = $contentRow+4;
			//求和、求平均值使用
			$calculate = 4;
			//列标题
			$titl_font_format = 'A3:'.$finalLetter.'4';
			//从第几行开始
			$startRows = 5;
			$k=0;
			$end = $title['row']+2;
			$twoRow = 0;
			foreach ($title['title'] as $n ){
				$toptitle = $letter[$k].'3';
				$endtitle = $letter[$k]."$end";
				//多行
				if(count($n)>1){
					foreach ($n as $z){
						if(is_array($z)){
							//第二行
							$twostart = $letter[$k].'3';
							$k+=count($z)-1;
							$twoend = $letter[$k].'3';
							$sheet->mergeCells("{$twostart}:{$twoend}");
							foreach ($z as $content){
								self::Excel_setValue($sheet, $letter[$twoRow].'4', $content);
								$twoRow++;
							}
						}else{
							//第一行
							self::Excel_setValue($sheet, $toptitle, $z);
						}
					}
				}else{
					//单行
					self::Excel_setValue($sheet, $toptitle, $n[0]);
					 $sheet->mergeCells("{$toptitle}:{$endtitle}");
					$twoRow++;
				}
				$k++;
			}
		}else{
			//导出单行表头信息时使用
			$menuBold = 'A3:'.$finalLetter.'3';
			$cornum = $contentRow+3;
			$calculate = 3;
			$startRows = 4;
			$titl_font_format = 'A3:'.$finalLetter.'3';
			foreach ($title['title'] as $key=>$titleCons){
				self::Excel_setValue($sheet, $letter[$key].'3', $titleCons);
			}
		}
		
		$sheet->getStyle("{$menuBold}")->getFont()->setBold(true);

		//设置列标题文字信息
		self::Excel_horizontal($sheet, $titl_font_format,2);
		self::Excel_vCenter($sheet, $titl_font_format);
				
		//记录数
		$rowNumCon = 0;
		//内容
		$rows = $startRows;
		//判断是否需要排序
		if(array_key_exists('colOrder', $optional)){
			foreach ($data as $dataArr){
				//记录数
				$rowNumCon +=$rowNum;
				$letterKey = 0;
				foreach ($optional['colOrder'] as $str){
					if(array_key_exists($letterKey, $letter)){
						if(array_key_exists("$str",$dataArr)){
							$dataStr = $dataArr["$str"];
						}else{
							$dataStr = '';
						}
						
						$titleLetters = $letter[$letterKey].$rows;
						$titleCons = str_replace ('&nbsp;',' ',$dataStr);
						self::Excel_setValue($sheet, $titleLetters, $titleCons);
						$letterKey++;
					}
				}			
				self::Excel_border($sheet, 'A'.$rows.':'.$finalLetter.$rows, 3);
				$rows++;
			}
		}else{
			foreach ($data as $dataArr){
				//记录数
				$rowNumCon +=$rowNum;
				$letterKey = 0;
				foreach ($dataArr as $str){
					if(array_key_exists($letterKey, $letter)){
						$titleLetters = $letter[$letterKey].$rows;
						$titleCons = str_replace ('&nbsp;',' ',$str);
						self::Excel_setValue($sheet, $titleLetters, $titleCons);
						$letterKey++;
					}
				}			
				self::Excel_border($sheet, 'A'.$rows.':'.$finalLetter.$rows, 3);
				$rows++;
			}
		}
		//列宽
		if(array_key_exists('colwidth',$optional)){
			foreach ($optional['colwidth'] as $key=>$width){
				$sheet->getColumnDimension("{$key}")->setWidth("{$width}");
			}
		}
		
		//设置字号
		if(array_key_exists('fontsize',$optional)){
			foreach ($optional['fontsize'] as $key=>$size){
				$sheet->getStyle($key)->getFont()->setSize($size);//设置字体
			}
		}
		// //设置行高
		// if(array_key_exists('lineheight',$optional)){
			// foreach ($optional['lineheight'] as $key=>$height){
				// $sheet->getColumnDimension("{$key}")->setHeight("{$height}");
			// }
		// }






		//文字对齐
		if(array_key_exists('colalign', $optional)){
			foreach ($optional['colalign'] as $key=>$align){
				$field = $key.$startRows.':'.$key.$cornum;
				self::Excel_horizontal($sheet, $field,$align);
			}
		}
		//数字格式
		if(array_key_exists('numberformat',$optional)){
			foreach ($optional['numberformat'] as $key=>$format) {
				$field = $key.$startRows.':'.$key.($cornum+3);
				if(empty($format)){
					self::Excel_numberFormat($sheet, $field);
				}else{
					self::Excel_numberFormat($sheet, $field,$format);
				}				
			}
		}
		
		//边框线
		$endcol = $cornum;
		if(array_key_exists('colSum',$optional)  || array_key_exists('colAverage',$optional)){
		 	$endseat = $cornum+1;
			$endcol = $endseat;
			$sheet->setCellValue("A{$endseat}", "合计："); 
			self::Excel_border($sheet, 'A'.$endseat.':'.$finalLetter.$endseat, 3);
		}
		
		//边框线
		foreach ($letter as $v){
			self::Excel_border($sheet, "{$v}1:{$v}{$endcol}", 0);	
		}
		
		//求和
		if(array_key_exists('colSum',$optional)){
			foreach ($optional['colSum'] as $sum){
				if($cornum > $calculate){
					$field = $sum.$startRows.':'.$sum.$cornum;
					$sheet->setCellValue("{$sum}{$endseat}", "=SUM({$field})"); 
				}else{
					$sheet->setCellValue("{$sum}{$endseat}", "0"); 
				}
			}
		}
		//平均值
		if(array_key_exists('colAverage',$optional)){
			foreach ($optional['colAverage'] as $ave){
				if($cornum > $calculate){
					$field = $ave.$startRows.':'.$ave.$cornum;
					$sheet->setCellValue("{$ave}{$endseat}", "=AVERAGE({$field})"); 
				}else{
					$sheet->setCellValue("{$ave}{$endseat}", "0"); 
				}
			}
		}
		//最后数据的导出人，导出时间
		$endRow = $cornum+4;
		if(!array_key_exists('lister', $info)) throw new AwException('制表人不存在');
		if(!array_key_exists('tabletime', $info)) throw new AwException('制表时间不存在');
		$exportRname = htmlspecialchars_decode($info['lister']);
		$endLister = '制表人:' . $exportRname;
		$watchTime = '制表时间:' .$info['tabletime'];
		$sheet->setCellValue("A{$endRow}", "{$endLister}");
		$sheet->setCellValue("C{$endRow}", "{$watchTime}");
		$sheet->mergeCells("A{$endRow}:B{$endRow}");
		$sheet->mergeCells("C{$endRow}:E{$endRow}");


		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		if(empty($dir)){
			//指定导出表数据的名称，以及导出数据信息
			$orderdatas = $table_name_two. '.xls';
			header('Content-Type: application/vnd.ms-excel;charset=gbk');
			header('Content-Disposition: attachment;filename="'.$orderdatas.'"');
			header('Cache-Control: max-age=0');
			$objWriter->save('php://output');
		}else{
			$objWriter->save($dir);

		}
	} 
	
	//合并单元格
//	private static  function Excel_mergeCells($sheet,$stat,$end){
//		return $sheet->mergeCells("{$stat}:{$end}");
//	}


	//顶部标题信息
	private static function Exce_topValue($sheet,$first,$value,$path,$num=100){
		$value = self::Excel_nbsp($num).$value;
		return $sheet->setCellValue("{$first}", "{$value}")->mergeCells("{$path}");
	}
	
	
	//边框
	private static function Excel_border($sheet,$coordinate,$path=0){
		switch ($path){
			case 0:
				$str='getRight';
			break;
			case 1:
				$str='getLeft';
			break;
			case 2:
				$str='getTop';
			break;
			case 3:
				$str='getBottom';
			break;
		}
		return $sheet->getStyle("{$coordinate}")->getBorders()->$str()
					->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	}
	
	
	//给单元格赋值
	private static function Excel_setValue($sheet,$field,$con){
		
		$titleCon = htmlspecialchars_decode($con);
		return $sheet->setCellValue("{$field}", "{$titleCon}");
	}
	
	
	//内容横对齐
	private static function Excel_horizontal($sheet,$field,$con=0){
		switch ($con){
			case 0:
				$horizontal = PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
			break;
			case 1:
				$horizontal = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
			break;
			case 2:
				$horizontal = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
			break;
		}
		return $sheet->getStyle("{$field}")->getAlignment()
					->setHorizontal($horizontal);			
	}
	
	
	//垂直居中
	private static function Excel_vCenter($sheet,$field){
		return $sheet->getStyle("{$field}")->getAlignment()
					->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	}
	
	
	//格式化数据
	private static function Excel_numberFormat($sheet,$field,$format='#,##0.00'){
		return $sheet->getStyle("{$field}")->getNumberFormat()
					->setFormatCode("{$format}");
	}
	
	
	//指定列宽
//	private static function Excel_cloWidth($sheet,$field,$width){
//		return $sheet->getColumnDimension("{$field}")->setWidth("{$width}");
//		
//	}

	
	//空格
	private static function Excel_nbsp($num){
		$nbsp='';
		for($i=0;$i<$num;$i++){
			$nbsp .=' ';
		}
		return $nbsp;
	}
	
	
	private static function Excel_Sheet($objPHPExcel){
		return $objPHPExcel->getActiveSheet();
	}
	
/**
 * 仿照Excel表格列字母排序方式排列字母，根据传入的数据返回对应数量的字母
 * $num传入的字母数量
 */
	private static function cycleLetter($num){
		$letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$str =	'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$ceils = ceil($num/26);
		$sums = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,';
		if($ceils>1){
			for($i = 0; $i<$ceils; $i++){
				$cos = substr($str,$i,1);
				for($ii = 0; $ii<26;$ii++){
					$sums .= $cos.$letter[$ii].',';
				}
			}
		}
		if($num >26){
			$lasts = ($num-26)*3;
			$sums = substr($sums,0,52).substr($sums,52,$lasts);
		}else{
			$lasts = $num*2;
			$sums = substr($sums,0,$lasts);
		}
		return explode(',',rtrim($sums,','));
	}
}
?>