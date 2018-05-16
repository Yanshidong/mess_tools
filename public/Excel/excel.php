<?php
$path=__DIR__;
$path=str_replace('\\','/',$path).'/excel/Classes/PHPExcel.php';
require_once $path;
/**
 * @param null $data
 * @param string $title
 * @param null $options
 * @more 立即下载.xls文件,数组里内容data,
 */
function excelDownloadNow($data=null,$title='ndj',$options=null)
{
    $box=$data;
    $objPHPExcel=new \PHPExcel();
    $objPHPExcel->getProperties()->setCreator("NDJ")
        ->setLastModifiedBy("Tutu")
        ->setTitle("report")
        ->setSubject("ChargeReport")
        ->setDescription("chargeReport for some,may for only one.")
        ->setKeywords("report Charge NDJ")
        ->setCategory("NDJReport");
    $first=0;
//    foreach($box as $title=>$v)
//    {
    if($first)$objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex($first)->fromArray($box);
    $objPHPExcel->getActiveSheet()->setTitle($title);
    $first++;
//    }//写数据。
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
//        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//        $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
//    $fileName=ndj_get($options['fileName']);
    if(!isset($fileName)){
        $fileName= md5(time());
    }else{

    }
    $fileName.='.xls';
    //win编码格式。

    //保存
    if(ob_get_contents()){
        ob_end_clean();
    }
    header("content-type:text/html;charset=utf8");
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$fileName.'"');
    header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0

    /*================================================
    开始下载相关
    =================================================*/

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $res=$objWriter->save('php://output');//
    exit;
}

/**
 * @param $filename
 * @return array
 * @more 读取指定文件.xls的内容
 */
function excelread($filename)
{
    $objReader = \PHPExcel_IOFactory::createReader('Excel5');
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($filename);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $highestRow = $objWorksheet->getHighestRow();
    $highestColumn = $objWorksheet->getHighestColumn();
    $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
    $excelData = array();
    for ($row = 1; $row <= $highestRow; $row++) {
        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
    }
    return $excelData;
}
