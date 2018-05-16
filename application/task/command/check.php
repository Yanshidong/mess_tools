<?php
/**
 * Created by PhpStorm.
 * User: tutu
 * Date: 18-4-17
 * Time: 上午8:53
 */

namespace app\task\command;
use Curl\Curl;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\NullLogger;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\Input\Argument;
use think\console\Input\Option;
use think\Exception;
use think\Log;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;

class check extends Command
{
    private $Myoutput=null;
    private $MyloggerHandler=[];
    private $checkAuthCenterUrlBox=[
        ["http://101.200.104.2/public/index.php/api/Ping?gw_id=test-for-authcenter-status","/Pong/",'ping'],
        ["http://101.200.104.2/public/index.php/api/Login?gw_id=test-for-authcenter-status&ip=192.168.1.84&mac=18:81:18:81:18:81&name=xiaoazhu","/北京市海淀区经济和信息化办公室/",'login'],
        ["http://101.200.104.2/public/index.php/api/roam?gw_id=test-for-authcenter-status&mac=7F:22:3E:E4:55:AF","/\"roam\":\"yes\"/",'roam'],
        ["http://101.200.104.2/public/index.php/api/Portal","/您已完成认证/",'Portal'],
        ["http://101.200.104.2/public/index.php/api/auth?stage=counters&gw_id=test-for-authcenter-status&mac=7F:22:3E:E4:55:AF&ip=192.168.1.84","/Auth\: 1/",'counter']
    ];
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->checkAuthCenterUrlBox[4][0].='&incoming='.((time()-1524018107)/10+mt_rand(0,5))."&outgoing=".((time()-1524018107)/1000);
    }

    private function getOutput()
    {
        if(!$this->Myoutput){
            $this->Myoutput=new Output();
        }
        return $this->Myoutput;
    }
    protected function configure()
    {
        $this->setName('check')
            ->addArgument('serverName', Argument::REQUIRED, "your name")
            ->addOption('writeto', null, Option::VALUE_REQUIRED, 'log file write to which file,full path')
            ->setDescription('check Server Status');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('serverName'));
        $name = $name ?: 'AuthCenter';
        switch ($name)
        {
            case "AuthCenter":
                $this->checkAuthCenter();
                break;
            default:
                $this->checkAuthCenter();
        }

        if ($input->hasOption('writeto')) {
            $writeto = $input->getOption('writeto');

        }
//        $this->sendMail("hahah");
    }
    private function checkAuthCenter()
    {

        foreach($this->checkAuthCenterUrlBox as $k=>$v)
        {
            if($k==4)continue;
            $this->curlCheck($v[0],$v[1],$v[2]);
        }
        $roam=$this->checkAuthCenterUrlBox[4];
        $this->curlCheck($roam[0],$roam[1],$roam[2]);

    }
    private function curlCheck(String $url,String $preg='',String $name):bool
    {
        $res=false;
        $start=microtime(true);
        try{
        $curl=new Curl();
        $curl->get($url);
        $response=$curl->response;
        //roam fanhui token saved
        if(preg_match("/roam/",$response))
        {
            $data=json_decode($response);
            $this->checkAuthCenterUrlBox[4][0]=$this->checkAuthCenterUrlBox[4][0]."&token=".$data->client->token."&first_login=".$data->client->first_login;
        }
        if(!$curl->error){
            $res=preg_match($preg,$response);
        }
    }catch (\Exception $e){
        $e->getMessage();
        $res=false;
    }
        $this->writeLog("$name|".($res?"success":"false")."|".round(microtime(true)-$start,2)."s");
        return  $res;
    }
    private function Mylogger($msg,$name="AuthCenter.log"){
        // create a log channel
        if(!isset($this->MyloggerHandler[$name])){
        $log = new Logger('check');
        $log->pushHandler(new StreamHandler("./public/".$name, Logger::INFO));
        $this->MyloggerHandler[$name]=$log;
        }
        $log=$this->MyloggerHandler[$name];
// add records to the log
        $log->info($msg);
    }
    private function writeLog($msg){
        $this->Mylogger($msg.PHP_EOL);
    }
    private function sendMail($msg)
    {
        $obj=new Message();
        $obj->addFrom('649439330@qq.com', 'Matthew Somelli');
        $obj->addTo('wangde007@outlook.com');
        $obj->setSubject('Sending an email from Zend\Mail!');
        $obj->setBody('This is the message body.');
//        $obj->addCc('ralph@example.org');
//        $obj->addBcc('enrico@example.org');
        $obj->addReplyTo('649439330@qq.com', 'Matthew');
        $obj->setEncoding("UTF-8");
        $trans=new Sendmail();
        $trans->send($obj);
    }

}