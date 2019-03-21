<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/28
 * Time: 15:07
 */

namespace Wechat\Service;

class AnalyzeExcelService {

    public function getExcelData($dir, $sheet = 0){
        //引入PHPExcel
        vendor("PHPExcel.PHPExcel");
        //实例化读取对象
        $objReader = \PHPExcel_IOFactory::createReader("Excel5");
        try {
            //加载Excel文件
            $objPHPExcel = $objReader->load($dir, $encode = 'utf-8');
        } catch (\PHPExcel_Reader_Exception $e) {
            return ['data' => '', 'info' => $e->getMessage(), 'status' => 0];
        }

        //获取工作簿
        $sheet = $objPHPExcel->getSheet($sheet);
        //获取总行数
        $highestRow = $sheet->getHighestRow();
        //获取总列数
        $highestColumn = $sheet->getHighestColumn();

        //判断文件是否为空
        if (!($highestRow > 0 && $highestColumn >= 'A')) {
            return ['data' => '', 'info' => '该文件为空', 'status' => 0];
        }

        $excelData = [];
        //循环获取数据
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($column = 'A'; $column <= $highestColumn; $column++) {
                $value = $sheet->getCellByColumnAndRow(ord($column) - 65, $row)->getValue();

                // 下面 11 行代码，解决复杂格式下不能正确解析的问题，by liuchao
                if (is_object($value)) {
                    $t       = '';
                    $cellStr = str_replace(' ', '', print_r($value, 1));
                    $cellStr = preg_replace('/\[_text:\w*:\w*\]=>/', '~~', $cellStr);
                    if (preg_match_all('/(~~)[^\n]*/', $cellStr, $res)) {
                        foreach ($res[0] as $v) {
                            $t .= $v;
                        }
                    }
                    $value = str_replace('~~', '', $t);
                }

                $excelData[$row][$column] = $value;
            }
        }

        return ['data' => $excelData, 'info' => '', 'status' => 1];
    }
}