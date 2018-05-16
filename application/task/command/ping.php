<?php
/**
 * Created by PhpStorm.
 * User: tutu
 * Date: 18-4-18
 * Time: 上午9:29
 */

namespace app\task\command;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use JJG\Ping as PingTool;
class ping extends Command
{

    private $MyloggerHandler=[];
    public function execute(Input $input, Output $output)
    {
        $host=$input->getArgument("srvAddr");
        foreach ($host as $hostAddr)
        {
            $this->ping($hostAddr);

        }
    }
    public function configure()
    {
        $this->setName('ping')
            ->addArgument('srvAddr', Argument::IS_ARRAY, "server ip or domain")
            ->addOption('writeto', null, Option::VALUE_OPTIONAL, 'log file write to which file,full path')
            ->setDescription('check Server network isReachable');
    }
    public function ping(String $host)
    {
        $ping = new PingTool($host);
        $ping->setTtl(128);
        $ping->setTimeout(5);
        $latency=$ping->ping();
        if ($latency !== false) {
            $this->Mylogger("$host:$latency ms");
        }
        else {
            $this->Mylogger("$host:unreachable");
        }
    }
    private function Mylogger($msg,$name="Ping.log"){
        // create a log channel
        if(!isset($this->MyloggerHandler[$name])){
            $log = new Logger('ping');
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
}