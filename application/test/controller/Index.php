<?php
/**
 * Created by PhpStorm.
 * User: tutu
 * Date: 18-4-28
 * Time: 上午9:47
 */

namespace app\Test\controller;


use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $dir="../../vendor/";
        $res=ndj_deldir($dir);
//        $handler=opendir($dir);

        if(file_exists($dir) && $handle=opendir($dir)){
            while(false!==($item = readdir($handle))){
                if($item!= "." && $item != ".."){
                    if(file_exists($dir.'/'.$item) && is_dir($dir.'/'.$item)){
                        echo "dir:$item<br>";
                    }else{
                        echo "fil:$item<br>";
                    }
                }
            }
            closedir( $handle);
        }
    }
}