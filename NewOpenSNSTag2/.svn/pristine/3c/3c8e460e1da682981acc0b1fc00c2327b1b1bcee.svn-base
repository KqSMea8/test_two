<?php
require_once('PHPExcel.php');
/**
 * 新Excel导出类文件
 * Enter description here ...
 * @author M
 *
 */
class ExportToChart{
	
	/**
	 * 						《excel导出图表传递参数信息》
	 * ======================================================================================================================
	 * 
	 * $info		表头信息			array类型	
	 * 
	 * 				参数如下：
	 * 				labels			标签段
	 * 				labelcount		标签段数量
	 * 				tick			分段标签
	 * 				tickcount		分段标签数量
	 * 				count			data数量
	 * 				end				结束段
	 * 				例如
	 * 				array(
	 * 				'labels' => 'D,E,F' ,
	 * 				'labelcount' => 3,
	 * 				'tick' => 'B',
	 * 				'tickcount' => 7,
	 * 				'count' => 8,
	 * 				'end' => 'F'
	 * 				)
	 * ======================================================================================================================
	 * 
	 * $title		表格列标题信息		array类型   
	 *				
	 *				title_one		表格标题1
	 *				title_two		表格标题2
	 * 				title_three		制表人信息
	 * 				title_four		制表时间信息
	 * 				title_five   	图表标题
	 * 				title_six   	图表单位
	 *				例
	 * 				array(
	 *					'title_one' => "出品明细报表",
	 *					'title_two' => "{$cabname}{$startdate}至{$enddate}出品明细报表",
	 *					'title_three' => "制表人：京品高科",
	 *					'title_four' => "制表时间：{$nowtime}",
	 *					'title_five' =>"{$cabname}{$startdate}至{$enddate}出品明细报表",
	 * 					'title_six' =>"数量（份）"
	 *				)
	 * ======================================================================================================================
	 *
	 * $data		表格内容信息		array类型	<!--必须传递数据-->
	 * 				表数据以二维索引数组形式传入即可,
	 * 				例如
	 * 				array( 
	 *					array ( '姓名', '性别'  ), 
	 *					array ( 'songbin', '男'), 
	 *					array ( 'sss', '女'), 
	 *					array ( 'yyy', '男')
	 *					 );
	 * ======================================================================================================================
	 * 
	 * $type	可选参数		array类型	
	 * 
	 * 			plottype		图表类型
	 * 			plotgrouping	分组类型	
	 * 			direction		图表方向
	 * 			position		标签位置
	 * 			例如
	 * 			array(
	 * 			'plottype' => 'barchart',
	 * 			'plotgrouping' => 'clustered',
	 * 			'direction' => 'bar',
	 * 			'position' => 'r')
	 * 
	 * ======================================================================================================================
	 * $style   表格宽度                 array()
	 * 			例如
	 * 			array(
	 *				'A' => 15,
	 *				'B' => 25,
	 *				'C' => 25,
	 *				'D' => 10,
	 *				'E' => 10,
	 * 				'F' => 10,
	 *				'G' => 10,
	 *				'H' => 20,
	 *	        )
	 * ======================================================================================================================
	 */
	public static function excel($info,$title,$data,$type,$style){

		/** Error reporting */
		//error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		date_default_timezone_set('Asia/Shanghai');

		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		date_default_timezone_set('Asia/Shanghai');
		
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */

/** PHPExcel */
		
	
		$objPHPExcel = new PHPExcel();
		$objWorksheet = $objPHPExcel->getActiveSheet();
		$objWorksheet->setCellValue('A1',$title['title_one'])->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objWorksheet->mergeCells('A1:'.$info['end'].'1');
		$objWorksheet->setCellValue('A2',$title['title_two'])->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objWorksheet->mergeCells('A2:'.$info['end'].'2');
		$objWorksheet->fromArray($data,NULL,'A3');
		//设置单元格宽度
		foreach ($style as $key => $value) {
			$objWorksheet->getColumnDimension($key)->setWidth($value);
		}
		$info['count'] = $info['count'] + 2;
		
		$objWorksheet->getStyle('A3:'.$info['end'].'3')->getFont()->setBold(true);
		$objWorksheet->setAutoFilter('A3:'.$info['end'].$info['count']);
		$info['count'] = $info['count'] + 2;
		$objWorksheet->mergeCells('A'.$info['count'].':B'.$info['count']);
		$objWorksheet->setCellValue('A'.$info['count'],$title['title_three']);
		$objWorksheet->mergeCells('C'.$info['count'].':D'.$info['count']);
		$objWorksheet->setCellValue('C'.$info['count'],$title['title_four']);
		
		
//	Set the Labels for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
		// $dataseriesLabels = array(	//	2010
			// new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$15', NULL, 1),	//	2011
		 	// new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$C$15', NULL, 1),
		 	// new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$d$15', NULL, 1),);	//	2012
		// );
		
		$label = explode(',', $info['labels']);
		
		$dataseriesLabels = array();
		for($i=0;$i<$info['labelcount'];$i++){
			$dataseriesLabels[$i] = new PHPExcel_Chart_DataSeriesValues('String','Worksheet!$'.$label[$i].'$3', NULL, 1);
		}
//	Set the X-Axis Labels
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
		$info['tickcounts'] = $info['tickcount'] + 2;
		$xAxisTickValues = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$'.$info['tick'].'$4:$'.$info['tick'].'$'.$info['tickcounts'], NULL, $info['tickcount'])	//	Q1 to Q4
		);
		
		//	Set the Data values for each data series we want to plot
		//		Datatype
		//		Cell reference for data
		//		Format Code
		//		Number of datapoints in series
		//		Data values
		//		Data Marker
		// $dataSeriesValues = array(
		// 	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$D$2:$D$8', NULL, 7),
		// 	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$E$2:$E$8', NULL, 7),
		// 	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$F$2:$F$8', NULL, 7),
		// );
		$dataSeriesValues = array();
		for($j=0;$j<$info['labelcount'];$j++){
			$dataSeriesValues[$j] = new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$'.$label[$j].'$4:$'.$label[$j].'$'.$info['tickcounts'], NULL, $info['tickcount']);
			
		}
		
		//	Build the dataseries
		$series = new PHPExcel_Chart_DataSeries(
			$type['plottype'],		// plotType
			$type['plotgrouping'],	// plotGrouping
			range(0, count($dataSeriesValues)-1),			// plotOrder
			$dataseriesLabels,								// plotLabel
			$xAxisTickValues,								// plotCategory
			$dataSeriesValues								// plotValues
		);
		//	Set additional dataseries parameters
		//		Make it a horizontal bar rather than a vertical column graph
		if(isset($type['direction'])){
			$series->setPlotDirection($type['direction']);
		}
		$layout = new PHPExcel_Chart_Layout();
		$layout->setShowVal(TRUE);
		$layout->setShowPercent(TRUE);
		//	Set the series in the plot area
		$plotarea = new PHPExcel_Chart_PlotArea($layout, array($series));
		//	Set the chart legend
		$legend = new PHPExcel_Chart_Legend($type['position'], NULL, false);
		
		$titles = new PHPExcel_Chart_Title($title['title_five']);
		if(isset($title['title_six'])){
			$yAxisLabel = new PHPExcel_Chart_Title($title['title_six']);		
		}else{
			$yAxisLabel = NULL;
		}
	
		
		//	Create the chart
		$chart = new PHPExcel_Chart(
			'chart1',		// name
			$titles,			// title
			$legend,		// legend
			$plotarea,		// plotArea
			true,			// plotVisibleOnly
			0,				// displayBlanksAs
			NULL,			// xAxisLabel
			$yAxisLabel		// yAxisLabel
		);
		
		//	Set the position where the chart should appear in the worksheet
		$info['count'] = $info['count'] + 2;
		$chart->setTopLeftPosition('A'.$info['count']);
		$info['count'] = $info['count'] + 20;
		$chart->setBottomRightPosition('N'.$info['count']);
		//	Add the chart to the worksheet
		if(!isset($info['nochart'])){			
			$objWorksheet->addChart($chart);
		}
		
		
		// Save Excel 2007 file
		//echo date('H:i:s') , " Write to Excel2007 format" , EOL;
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="01simple.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->setIncludeCharts(true);
		$objWriter->save('php://output');
	
	} 
			
}
?>