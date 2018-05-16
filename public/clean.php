<?php
$path=str_replace('\\','/',__DIR__);
require $path."/app/define.php";//加载常量
$databaseConf=require str_replace('\\','/',__DIR__)."/app/database.php";
$redisConf=@require str_replace('\\','/',__DIR__)."/app/config.php";
define("CHECK_AFTER",14400);
//连接本地的 Redis 服务
$redis = new Redis();
$redis->connect($redisConf['cache']['host'], $redisConf['cache']['port']);
//echo "Connection to server sucessfully\a\n";
//查看服务是否运行
//echo "Server is running: " . $redis->ping()."\n\a\n";
//echo $redis->dbSize();
$mysql=new mysqli($databaseConf["hostname"],$databaseConf['username'],$databaseConf['password'],$databaseConf['database'],'3306');
//$res=$mysql->query("select * from wifi_authlist where mac like '%3B:7A'");
//$cc=$res->fetch_array(2);
//var_dump($cc);
$time=time();
$zend=$redis->keys("r.*");
$routes=[];
//清理不活跃网关。
foreach($zend as $key)
{
    $z=$redis->get($key);
    $z=json_decode($z);
    if(($cha=$time-$z->last_heart_time)>NDJ_ROUTE_DELAY_MULTI*$z->delay)
    {
        //ping超时，清理缓存，更新数据
        $sql="update wifi_route_his set last_heart_time=? where routeid=? and login_time=?";
        $stmt=$mysql->prepare($sql);
        $stmt->bind_param("iii",$z->last_heart_time,$z->routeid,$z->login_time);
        $stmt->execute();
        $stmt->fetch();
        $redis->del($key);
        $redis->del(NDJ_ROUTE_ONLINEUSERS_CACHE_KEY.ltrim($key,'r.'));
        continue;
    }
    $routes[$z->routeid]=$z;
}
$keys=$redis->keys("l.*");

//清理不活跃用户
foreach($keys as $v){
    $clear=false;
    $data=$redis->get($v);
    $data=json_decode($data);
    if(is_null($data)||(!isset($data->user_token))||(!isset($data->gw_id))||(!$data->gw_id)||(!isset($data->user))){
        $redis->del($v);
        continue;
    }
    //当最后一次上报数据时间不存在，或者以及超过一定时限未上报信息，认为用户已下线.
    if($time-$data->update_time>CHECK_AFTER){
        $logoutby=NDJ_USER_LOGOUT_BY_OVERTIME;
        $clear=true;
    }
//    if(!isset($routes[$data->route_id]))
//    {
//        $logoutby=NDJ_USER_LOGOUT_BY_ROUTE_OFFLINE;
//        $clear=true;
//    }
    if($clear)
    {
        //结果它,正经的坏数据
        //更新历史记录
        $sql="update wifi_user_his set last_heart=?,incoming=?,outgoing=?,ip=?,logout_time=?,logout_by=? where user_token=?";
        $stmt=$mysql->prepare($sql);
//        $stmt->bind_param('i',$data->last_heart);
        $stmt->bind_param("iiisiis",$data->last_heart,$data->incoming,$data->outgoing,$data->ip,$time,$logoutby,$data->user_token);
        $stmt->execute();
        $ss=$stmt->fetch();
        //更新用户状态
        $sql="update wifi_authlist set status=3,last_time=? where id=?";
        $stmt=$mysql->prepare($sql);
        $stmt->bind_param("ii",$time,$data->authlist_id);
        $stmt->execute();
        $stmt->fetch();
        //清空缓存
        $redis->del($v);
        $rListKey=NDJ_ROUTE_ONLINEUSERS_CACHE_KEY.$data->gw_id;
        if($rUList=$redis->get($rListKey))
        {
            $rUList=json_decode($rUList);
            $tk=$data->user_token;
            unset($rUList->$tk);
            $rUList=json_encode($rUList);
            $redis->set($rListKey,$rUList);
        }
    }

}
$mysql->close();
$redis->close();


