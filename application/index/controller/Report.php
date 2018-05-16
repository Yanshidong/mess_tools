<?php
/**
 * Created by PhpStorm.
 * User: tutu
 * Date: 18-4-12
 * Time: 上午10:04
 */

namespace app\index\controller;


use think\Controller;
use think\File;

class Report extends Controller
{
    private $deviceListCharge=array();//all device charge total
    private $deviceChargeMax=array();//more then 100%
    private $smallgateDevice=array();//small gateway ap
    private $fileLoaded=array();//load file list

    private $hostInfo=array();//all host info
    public function index()
    {
        return $this->fetch();
    }
    public function limitDeviceCharge1()
    {
        set_time_limit(0);
        $eacheDeviceCharge=50000;
        //gate small gateway ap list
        $smallgatelistxls='./smallgate.xlsx';
        $gatewaylist=$this->getSheet($smallgatelistxls);
        $zend=[];
        foreach($gatewaylist as $v)
        {
            $v[0]=preg_replace("/ |:|-/",'',$v[0]);
            $zend[$v[0]]=$v;
        }
        $gatewaylist=$zend;

        $dir="./report/";
        $filelist=scandir($dir);
        $filelist=array_filter($filelist,function ($v){
           return $v!='.'&&$v!='..';
        });

        $fileFirst=array_shift($filelist);
        foreach($filelist as $fileFirst){
        $fileFirst=$dir.$fileFirst;

        $sheet1Arr=$this->getSheet($fileFirst,1);
        $title=array_shift($sheet1Arr);
        $sheet0Arr=$this->getSheet($fileFirst,0);//############
        $this->setIndexAsKey($sheet0Arr);
        foreach($sheet1Arr as $host)
        {
            $mac=preg_replace("/ |:|-/",'',$host[0]);
            //push smallgateDevice list
            if(isset($gatewaylist[$mac])){
                $this->smallgateDevice[$mac]=$gatewaylist[$mac];
                continue;
            }
            if(isset($this->deviceChargeMax[$mac]))
            {
                continue;
            }
            //
            if(!isset($this->deviceListCharge[$mac])){
                if(isset($this->hostInfo[$mac])){
                    $this->deviceListCharge[$mac]=[$mac,0,$this->hostInfo[$mac][0],$this->hostInfo[$mac][1],$this->hostInfo[$mac][3]];
                }else{
                    $this->deviceListCharge[$mac]=[$mac,0];
                }

            }
            $this->deviceListCharge[$mac][1]+=$host[6]*10+$host[8]*5;
            if($this->deviceListCharge[$mac][1]>=1000){
                $this->deviceChargeMax[$mac]=[$this->hostInfo[$mac][0],$this->hostInfo[$mac][1],$this->hostInfo[$mac][2],$this->hostInfo[$mac][3]];
                unset($this->deviceListCharge[$mac]);
            }

        }
        }
        dump($this->deviceListCharge);
        dump($this->deviceChargeMax);
        dump($this->smallgateDevice);
        $Res=[
          '计费结果'=>$this->deviceListCharge,
          '计费达到100%'=>$this->deviceChargeMax,
          '小网关下设备'=>$this->smallgateDevice
        ];
        $options=[
            'fileName'=>uniqid("ndj_").'.xls',
            'outputDir'=>'./'
        ];
        $this->writeExcel($Res,$options);


    }
    private function writeExcel($data=null,$options=null,$writeOption=true)
    {
        $downLoadKey='php://output';
        $dir=ndj_get($options['outputDir']);

        $dir=$writeOption?($dir.$options['fileName']):$downLoadKey;
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
        foreach($box as $title=>$v)
        {
            if($first)$objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex($first)->fromArray($v);
            $objPHPExcel->getActiveSheet()->setTitle($title);
            $objPHPExcel->getActiveSheet()->getColumnDimension(\PHPExcel_Cell::stringFromColumnIndex($first))->setAutoSize(true);
            $first++;
        }//写数据。
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
//        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//        $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
        $fileName=ndj_get($options['fileName']);
        if(!$fileName){
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
        if($dir==$downLoadKey){
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
        }
        /*================================================
        开始下载相关
        =================================================*/

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $res=$objWriter->save($dir);//
        if($res==$downLoadKey)exit;
    }
    private function getSheet($filePath,$sheet=0)
    {
        if(isset($this->fileLoaded[$filePath])){
            $phpexcel=$this->fileLoaded[$filePath];
        }else{
            $phpexcel=\PHPExcel_IOFactory::load($filePath);
        }
        $this->fileLoaded[$filePath]=$phpexcel;
        $sheetindexNow=$phpexcel->getActiveSheetIndex();
        if($sheetindexNow==$sheet)
        {
            return $phpexcel->getActiveSheet()->toArray();
        }
        $sheet1=$phpexcel->setActiveSheetIndex($sheet);
        $sheet1Arr=$sheet1->toArray();
        return $sheet1Arr;
    }

    private function getSheetName($filePath,$sheet=0)
    {

        if(isset($this->fileLoaded[$filePath])){
            $phpexcel=$this->fileLoaded[$filePath];
        }else{
            $phpexcel=\PHPExcel_IOFactory::load($filePath);
        }
        $this->fileLoaded[$filePath]=$phpexcel;
        $sheetindexNow=$phpexcel->getActiveSheetIndex();
        if($sheetindexNow==$sheet)
        {
            return $phpexcel->getActiveSheet()->getTitle();
        }
        $sheet1=$phpexcel->setActiveSheetIndex($sheet);
        $sheet1Arr=$sheet1->getTitle();
        return $sheet1Arr;
    }
    private function setIndexAsKey($arr,$index=2)
    {
        $this->hostInfo;
        foreach($arr as $v)
        {
            $mac=preg_replace("/ |:|-/",'',$v[$index]);
            $this->hostInfo[$mac]=$v;
        }
    }

    /**
     * @param $dir 以 /结尾
     * @return array $dir目录下的所有文件的绝对路径。
     */
    private function scandir($dir)
    {
        $filelist=scandir($dir);
        $filelist=array_filter($filelist,function ($v){
            return $v!='.'&&$v!='..';
        });
        $filelist=ndj_array_reset($filelist,function($v)use($dir){
           $file=new File($dir.$v);
           return $file->getRealPath();
        });
        return $filelist;
    }
    public function resetExcel($filepath='./resetExcel/')
    {
        $files=$this->scandir($filepath);
        $options=[
            'fileName'=>'',
            'outputDir'=>''
        ];
        foreach($files as $filename)
        {
            $sheet1=$this->getSheet($filename);
            $sheet1Name=$this->getSheetName($filename);
            $sheet1=setValueAsKey($sheet1,2);
            $sheet2=$this->getSheet($filename,1);
            $sheet2Name=$this->getSheetName($filename,1);
            foreach ($sheet2 as $sheet2k=>$sheet2v)
            {
                if(!$sheet2k)continue;
                $sheet2[$sheet2k][0]=$sheet1[$sheet2v[1]][0];
            }
            $options['fileName']=$filename;
            $Box=[
                $sheet1Name=>$sheet1,
                $sheet2Name=>$sheet2
            ];
            $this->writeExcel($Box,$options);
        }
    }
}