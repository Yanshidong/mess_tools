<?php
/**
 * Created by PhpStorm.
 * User: tutu
 * Date: 2017/6/16
 * Time: 15:57
 */

namespace app\common\excel;
use app\common;
require_once  dirname(__FILE__) . '/Classes/PHPExcel.php';
class excel {
    public static function get()
    {
        return new PHPExcel();
    }
}