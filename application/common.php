<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
/******************************info***********************************
 * NDJ Service Function  [WE CAN DO IT JUST N]                       *
 * *******************************************************************
 * Copyright (c) 2016-2017 http:ndj.com  All rights reserved.        *
 * *******************************************************************
 * Licensed                                                          *
 * *******************************************************************
 * Function common function List                                     *
 * *******************************************************************
 * Author: 流年 <liu21st@gmail.com>                                   *
 * *******************************************************************
 * Date: 2017/08/04  12:03                                           *
 * *******************************************************************
 **/

/**
 * @function 测试函数，获取当前时间戳，到毫秒级
 * @return array 返回当前时间戳(s)，毫秒
 */
function opstart(){
    return [time(),microtime()];
}

/**
 * @function 测试函数，给出从$start到此处的执行时间，
 * @param $start [s,ms]
 * @return mixed|string
 * @more 和opstart配合使用效果会更好
 */
function opend($start){
    list($start_s,$start_ms)=$start;
    $end_s=time();
    $end_ms=microtime();
    if($end_s>$start_s){
        if($end_ms>=$start_ms){
            echo 'timeO:';
            $runTime=($end_s-$start_s).ltrim(($end_ms-$start_ms),'0');
        }else{
            echo 'timeT:';
            //3.1-2.3
            $runTime=($end_s-$start_s-1).ltrim(($start_ms-$end_ms),'0');
        }
    }else{
        echo 'timeTR:';
        $runTime=$end_ms-$start_ms;
    }
    dump($runTime);
    exit;
    return $runTime;
}

/**
 * @function 互换数组键名和某字段值
 * @param $data example 待处理数组 $data=[0=>['name'=>'san','age'=>1],1=>['name'=>'si','age'=>23]]
 * @param null $field 待互换字段名 exmple $field=>'name'
 * @return array 返回互换结果  example ['san'=>['name'=>0,'age'=>1],'si'=>['name'=>0,'age'=>23]]
 * @more 以起义之名，自下而上，交换一次.
 * value uprising success,then set key as value and kill other values;
 */
function ndj_uprising($data,$field=null)
{
    $res=[];
    foreach($data as $k=>$v)
    {
        if(isset($v[$field]))
        {
            $res[$v[$field]]=$v;
            $res[$v[$field]][$field]=$k;//互换
        }
        elseif(!is_array($v))
        {
            $res[$v]=$k;
        }
        else
        {
            foreach($v as $key)
            {
                $res[$key]=$k;
            }
        }
    }
    return $res;

}

/**
 * @function 匹配字符串是否符合某类型数据
 * @param $value 待判断字符串 example '192.16
 * @param $type 要比对的类型 'ip' | 'mac' example 'ip'
 * @return bool|int 返回比对结果 example true
 * @more 用来模糊匹配字符是否可能是ip或者mac地址，可以扩充，比如邮箱什么的，在搜索的时候将关键字分类时用了一次
 */
function str_preg_like($value,$type)
{
    $type=$type?strtolower($type):null;
    $value=$value?strtolower($value):'';
    $typelist=['ip','mac'];
    if(!in_array($type,$typelist))
    {
        return false;
    }
    switch($type)
    {
        case 'ip':
            $res=preg_match('/([1,2]{0,1}[0-9]{1,2}\.){1,4}/',$value);
        break;
        case 'mac':
            $res=preg_match('/([0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f]{2}[:, ,-,\|,\*,\%]{1}){1,5}/',$value);
        break;
        default:
            $res=false;
        break;
    }
    return $res;

}

/***
 * @function 判断字符串是否是某类型数据
 * @param $value 待判断字符  example '192.158.1.
 * @param $type 待比对类型 ip|mac  example ip
 * @return bool|int 匹配结果   false
 * @more 这个是严格比配，所输ip或mac必须完整。否则返回false
 */
function str_preg($value,$type){
    $type=$type?strtolower($type):null;
    if(is_null($type)){return false;}
    if($type=='ip'){
        $pat='/^(25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|[0-9])\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|[0-9])\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|[0-9])\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|[0-9])$/';
    }
    elseif($type=='mac')
    {
        $pat='/^([0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f,A,B,C,D,E,F]{2}[:, ,-,\|,\*,\%]{1}){5}[0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f,A,B,C,D,E,F]{2}$/';
    }
    $res=preg_match($pat,$value);
    return $res;
}

function ndj_filterResByIds($res,$ids=[],$primaryKey='hostid',$valueIsKey=true)
{
    if(!$valueIsKey)
    {
        $ids=setValueAsKey($ids);
    }
    if(empty($ids))return [];
    return array_filter($res,function($v) use($ids,$primaryKey){
        return isset($ids[$v[$primaryKey]]);
    });
}
/***
 * @function 设置某字段值为数组键名
** @param array $data 初始数组
** @param string|array   设为键名的键名,
 * @param $delimeter 指定连接各字段的连接符
 * @return  array
 * @more 可以设置多个键值为组合键名以保持唯一性。也可以设置单个，可以指定连接符
***/
function setValueAsKey($data,$field=null,$delimeter='_')
{
    $res=[];
    $data=ndj_to_array($data);
    if(!$data){return $res;}
    if(is_array($field))
    {
        foreach($data as $v)
        {
            $key='';
            foreach($field as $f)
            {
                $key.=$delimeter.ndj_get($v[$f],'');
            }
            $key=ltrim($key,$delimeter);
            $res[$key]=$v;
        }
    }
    elseif(is_string($field)||is_numeric($field))
    {
        foreach($data as $k=>$v)
        {
            if(is_array($v[$field])){
                foreach($v[$field] as $vf){
                    $res[$vf]=$v;
                }
            }elseif($field||is_numeric($field)){
                $res[$v[$field]]=$v;
            }
            elseif(is_null($field)&&is_array($v))
            {
                foreach($v as $vc)
                {
                    $res[$vc]=$k;
                }
            }
        }
    }else{
        foreach($data as $v)
        {
            $res[$v]=$v;
        }
    }

    return $res;
}

/**
 * @function 将数组处理成普通索引数组
 * @param null $data 待处理数组
 * @return array 键名为自然数排列的数组。
 * @more 移除键名，将键名处理成普通的0,1,2,3,4这样
 */
function ndj_rmvKey($data=null){
    if(is_null($data)){return [];}
    if(is_array($data)){return array_values($data);}
    return [$data];
}

/**
 * @function 获取数组某字段的值组成新数组
 * @param $value 待处理数组
 * @param null $field 待获取字段
 * @param bool $unique 是否去重
 * @return array 处理好的一维数组
 * @more 一般用来获取二维数组里的某字段值，$value=[['id'=>1],['id'=>3]],获取id为一维数组时可以这样
 * ndj_objectValues($value,'id',true),返回[1,3]
 */
function ndj_objectValues($value,$field=null,$unique=true)
{
    if (is_null($value)) {
        return $value;
    }

    if (!is_array($value)) {
        $result = [$value];
    }
    elseif (isset($value[$field])) {
        $result = [$value[$field]];
    }
    else {
        $result = [];

        foreach ($value as $val) {
            if (!is_array($val)) {
                $result[] = $val;
            }
            elseif (isset($val[$field])) {
                $result[] = $val[$field];
            }
        }
    }
    if($unique){$result=array_unique($result);}
    return $result;
}

/**
 * @type function 组装where条件专用，支持like = > <
 * @param array $go 唯一参数，三维数组
 * @return string 返回组好的where条件
 */
function getWhere($go=[['field'=>'field','type'=>'like||=','value'=>['value1','value2']]])
{
    $str = '';
    foreach ($go as $v) {
        if (!isset($v['value'])) {
            continue;
        }
        foreach ($go as $v) {
            $v['type'] = isset($v['type']) ? $v['type'] : '=';//type默认值
            if ($v['value'] == [] || !isset($v['value'])) {
                $v['value']=null;
            }//过滤掉无效的where条件
            $str .= ' (';

            if (!is_array($v['field'])) {
                $v['field'] = [$v['field']];
            }
//        if(!is_array($v['value'])){$v['field']=[$v['field']];}
            foreach ($v['field'] as $vf) {
                $str .= getWhereBy($vf, $v['type'], $v['value']) . ' or ';
            }
            $str = rtrim($str, 'or ');
            $str .= ') and ';
        }
        $str = rtrim($str, 'and ');
//    dump($str);

        $str = '(' . $str . ')';

        return $str;
    }
}

/**
 * @function 组装where语句使用
 * @param $field array||string 字段名
 * @param $type string 字段与value的匹配类型 like = > <  etc
 * @param $value array||string||int  与字段按type匹配的值
 * @return string 返回单字段组装完成的sql语句
 */
function getWhereBy($field,$type,$value)
{
//        $str='name like "%abloomy%"';
    $value=is_array($value)?$value:[$value];
    $query='';
    switch($type){
        case 'like':
            $type=$type;
            break;
        case '=':
            $type=$type;
            break;
        default:
            $type=$type;
    }
    foreach($value as $v){
        if($type=='like'){
            $query.=$field.' '.$type.' "%'.$v.'%" or ';
        }elseif($type=='='){
            if(is_numeric($v)){
                $query.=$field.' '.$type.''.$v.' or ';
            }else{
                $query.=$field.' '.$type.' "'.$v.'"  or ';
            }

        }else{
            if(is_numeric($v)){
                $query.=$field.' '.$type.''.$v.' or ';
            }else{
                $query.=$field.' '.$type.' "'.$v.'"  or ';
            }
        }

    }
    return rtrim($query,'or ');
}

/**
 * @function 格式化一个时间段
 * @param int $timeStart 起始时间
 * @param int $timeEnd 结束时间
 * @return string 格式化好的字符串 比如 2小时3分钟18秒
 * @more
 */
function ndj_lasttime($timeStart=0,$timeEnd=0){
    $last=$timeEnd-$timeStart;
    if($last<=0){return '0秒';};
    $D=floor(($last)/(86400));//天
    $H=floor(($last-$D*86400)/3600);//时
    $f=floor(($last-$D*86400-$H*3600)/60);//分
    $s=floor(($last-$D*86400-$H*3600-$f*60));//秒
    $res='';
    if($D)$res.=$D.'天 ';
    if($H)$res.=$H.'时 ';
    if($f)$res.=$f.'分 ';
    if($s)$res.=$s.'秒 ';
    return $res;
}

/**
 * @function 递归获取节点path
 * @param $data
 * @param int $pid
 * @param string $id
 * @param array $res
 * @return array
 */
function path_old($data,$pid=0,$id='id',$res=[])
{
    foreach($data as $k=>$v)
    {
        if($v['pid']==$pid)
        {
            unset($data[$k]);
            $ppath=isset($res[$v['pid']]['path'])?$res[$v['pid']]['path'].'_':'';
            $v['path']=$ppath.$v[$id];
            $res[$v[$id]]=$v;
            $res=path($data,$v[$id],$id,$res);
        }
    }
    return $res;
}

/***
 *
 * @param $box
 * @param int $pid
 * @param string $id
 * @param string $path
 * @param array $res
 * @return array
 */
function path($box,$pid=0,$id='id',$res=[],$path='0')
{
    $load=[];
    foreach($box as $v)
    {
        $load[$v['pid']][]=$v;
    }
    return path_get($load,$pid,$id,$res,$path);
}

/***
 * 获取path
 * @param $box
 * @param int $pid
 * @param string $id
 * @param array $res
 * @param string $path
 * @return array
 */
function path_get($box,$pid=0,$id='id',$res=[],$path='')
{
    if(!isset($box[$pid])){
        return $res;
    }
    foreach($box[$pid] as $k=>$v)
    {
        $v['path']=$path?$path.'_'.$v[$id]:$v[$id];
        $res[$v[$id]]=$v;
        if(isset($box[$v[$id]])){
            $res=path_get($box,$v[$id],$id,$res,$v['path']);
        }
    }
    return $res;
}
/**
 * @function 递归获取树结构(多维数组)
 * @param $data 待处理数组。
 * @param int $pid 初始节点的pid
 * @param int $get 是否获取一维列表
 * @param string $id 主键名称
 * @param array $datalib 递归存储盒子
 * @return array
 * @demo $node_base, 'n'.$node_base[$nodeId]['pid'], $get = 0,  'uid','upid'
 * @more
 */
    function find_level($data,$pid=0,$get=0,$id='nodeid',$pidn='pid',$datalib=[],$path='0')
    {
        $zend=[];
        foreach($data as $v)
        {
            $zend[$v[$pidn]][]=$v;
        }
        unset($data);
        return find_level_get($zend,$pid,$get,$id,$datalib,$path);
    }
    function find_level_get($data,$pid=0,$get=0,$id='nodeid',$datalib=[],$path='')
    {
        if(isset($data[$pid]))
        {
            $arr=$data[$pid];
            foreach($arr as $k=>$v)
            {
                $arr[$k]['path']=$path?$path.'_'.$v[$id]:$pid.'_'.$v[$id];
                if($data[$v[$id]])
                {
                    $arr[$k]['child']=find_level_get($data,$v[$id],$get,$id,$datalib,$arr[$k]['path']);
                    unset($data[$id]);
                }

            }
            return $arr;
        }
        return false;
    }
function ndj_find_level($data,$pid=0,$get=0,$id='nodeid',$pidn='pid',$datalib=[],$path='0')
{
    $zend=[];
    foreach($data as $v)
    {
        $zend[$v[$pidn]][]=$v;
    }
    unset($data);
    return find_level_get($zend,$pid,$get,$id,$pidn,$datalib,$path);
}
function ndj_find_level_get($data,$pid=0,$get=0,$id='nodeid',$pidn='pid',$datalib=[],$path='')
{
    if(isset($data[$pid]))
    {
        $arr=$data[$pid];
        foreach($arr as $k=>$v)
        {
            $arr[$k]['path']=$path?$path.'_'.$v[$id]:$pid.'_'.$v[$id];
            if($data[$v[$id]])
            {
                $arr[$k]['child']=ndj_find_level_get($data,$v[$id],$get,$id,$datalib,$arr[$k]['path']);
                unset($data[$id]);
            }

        }
        return $arr;
    }
    return false;
}
function find_level_old($data,$pid=0,$get=0,$id='nodeid',$datalib=[])
{           $arr=array();
            if(!$datalib){
                $datalib=[];
            };
        $idbox=[];
//        $idbox[]=$pid;
    foreach($data as $k=>$v)
    {
        if($v['pid']==$pid)
        {
            unset($data[$k]);
            $idbox[]=$pid;
            //此处处理path
            $ppath=isset($datalib[$v['pid']]['path'])?$datalib[$v['pid']]['path'].'_':'';
            $v['path']=$ppath.$v[$id];
            $datalib[$v[$id]]=$v;
            //path处理结束
            $arr[$k]=$v;
            if(find_level($data,$v[$id],$get,$id,$datalib)){
                $arr[$k]['child']=find_level($data,$v[$id],$get,$id,$datalib);
            }
//            unset($data[$k]);

        }
    }
    if($get){
        $datalib=zbx_objectValues($datalib,'nodeid');
        $idsTotal=zbx_objectValues($data,'nodeid');
        if(in_array($pid,$idsTotal)){$datalib[]=$pid;}
        $datalib=array_unique(array_merge($datalib,$idbox));
        return $datalib;
    }

    return $arr;
}

/**
 * @function 递归某项求和，此节点之下的所有节点的该项统计
** @param 进来的数据应该是什么样子的呢，其他层就那德性了，第一层怎么说，我感觉这个参数应该是本层加child,而不是遍历才能拿到
 * @return 递归累加之后的结构化数据。
**
**/
function find_level_total(&$data){
    // $child_total=0;$top_flag=false;
    $data['this_total']=isset($data['this_total'])?$data['this_total']:$data['total'];
    foreach($data['child'] as $k=>$v){
       
        if(isset($v['child'])){
            $data['total']+=find_level_total($data['child'][$k])['total'];
        }else{
            $data['child'][$k]['this_total']=isset($data['child'][$k]['this_total'])?$data['child'][$k]['this_total']:$v['total'];
            $data['total']+=$v['total'];
        }
    }
    return $data;
    
}

/**
 * @function 将对象转换成数组
 * @param $source 对象
 * @param $field 字段
 * @param string $sourceType
 * @param bool $setValueAsKey
 * @return array
 * @more 不好使
 */
function getSome($source,$field,$sourceType='obj',$setValueAsKey=false){
    if(!is_array($field)){$field=[$field];}
    $res=[];
    switch ($sourceType){
        case 'obj':
            foreach ($source as $k=>$v){
                if($setValueAsKey){
                    foreach($field as $vf){
                        $res[$v->$setValueAsKey][$vf]=$v->$vf;
                    }

                }else{
                    foreach($field as $vf){
                        $res[$k][$vf]=$v->$vf;
                    }
                }
            }
            break;
        case 'arr':
            $res=[];
            foreach($source as $v){
                if(!is_array($field)){
                    $field=[$field];
                }
                foreach($field as $vf){
                    if(is_array($v[$vf])){
                        $res=array_merge($res,$v[$vf]);
                    }else{
                        $res[]=$v[$vf];
                    }
                }
            }
            $res=array_unique($res);
            break;
        default;
    }
    return $res;
}

/**
 * @function 设置sql mode
 * @return void
 * @more 暂时没用，关于sql_mode问题应该在数据库部署时候就设置好
 */
function setSqlMode(){
    Db::query("SET sql_mode ='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");

}

/**
 * @function  将字符串分割成数组，并去重
 * @param null $data
 * @param string $parameter
 * @return array|bool|null
 * @more
 */
function ndj_explode($data=null,$parameter=' '){
    if(is_null($data)){return false;}
    $data=explode($parameter,$data);
    $data=array_filter($data);
    return $data;
}


/**@function 生成图片base编码格式
 * @param $image_file 图片路径
 * @return string base64加密后字符串
 * @more  刘辉 2017/1/17 13:30:53
 */
function base64EncodeImage ($image_file) {
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));

        return $base64_image;
    }

/**
 * @function 获取REQUEST，不存在时用提供的默认值代替
 * @param $name 要获取值的键名
 * @param null $def
 * @return null|string
 * @more 获取Request参数的数据.
 */
function ndj_getRequest($name, $def = null) {

    return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $def;
}

/**
 * @function 根据fieldlist获取$_REQUEST值
 * @param $fieldList ['id']
 * @return array
 */
function ndj_getRequestWithField($fieldList,$before=null)
{
    $data=is_array($before)?$before:[];
    foreach($fieldList as $v)
    {
        if(isset($_REQUEST[$v]))
        {
            $data[$v]=$_REQUEST[$v];
        }
    }
    return $data;
}

/**
 * @function 检查Request中是否有某键值对
 * @param null $fieldName
 * @return bool
 * @more
 */
function ndj_can_get($fieldName=null)
{
    if(!is_numeric($fieldName)&&(!is_string($fieldName)))
    {
        return false;
    }
    return isset($_REQUEST[$fieldName]);
}

/**
 * @function 数组内元素求和
 * @param $data 待求和数组
 * @param null $field $data为二维数组时$field表示当内层数组的键值对键名，
 * @return float|int
 * @more  一维数组直接求和其内元素[1,3,0,3]，二维数组求和其内某字段。[['id'=>1,'value'=>1],['id=>3,'value'=>5]]
 */
function ndj_getSumByField($data,$field=null)
{
    $count=0;
    if(is_null($field))
    {
        foreach($data as $v)
        {
            $count+=floatval($v);
        }
    }
    else
    {
        foreach($data as $v)
        {
            if(!isset($v[$field])){continue;}
            $count+=$v[$field];
        }
    }
    return $count;
}

/**
 * @function 根据某字段去重
 * @param null $data
 * @param null $field
 * @return array|string
 * @more 没用过
 */
function ndj_getValuesUnique($data=null,$field=null){
    if(is_null($data)||is_null($field)){return 'key param need';}
    if(!is_array($data)){return 'data must be an array';}
    $zend=[];
    if(!is_string($field)){
        foreach($data as $v){
            if(isset($v[$field])){
                $zend[]=$v[$field];
            }
        }
    }elseif(is_array($field)){
        foreach($data as $v){
            foreach($field as $vf){
                if(isset($v[$vf])){
                    $zend[$vf][]=$v[$vf];
                }
            }
        }
    }
    return $zend;

}

/**
 * @function 生成一定格式的返回值
 * @param string $str 消息
 * @param null $code 返回值代码
 * @param null $data 携带数据
 * @return array
 * @more 在ajax处理数据时多次使用这个，msg+code+data的形式
 */
function ndj_error($str='',$code=null,$data=null){
    $str=is_string($str)?$str:'';
    $dsg=ndj_toString($data);
    return ['code'=>$code?$code:400,'msg'=>$str,'data'=>$data?$data:[],'dsg'=>$dsg];
}

/**
 * @function 生成操作成功时的返回值
 * @param null $data
 * @param string $str
 * @return array
 * @more 参照ndj_error()
 */
function ndj_res($data=null,$str='')
{
    $data=$data?$data:[];
    $str=is_string($str)?$str:'';
    return ['code'=>200,'msg'=>$str,'data'=>$data];
}

/***分组专用，函数并未完成，类似于数据库中的group by
** @param $data array 待分组数据
** @param $field string 分组用的字段，按某字段值分组
** @param $unique boolean 是否要求唯一性
***/
function ndj_group($data,$field,$unique=null)
{
    if(!is_array($field))
        {
            $field=[$field];
        }
    $zend=[];
    foreach($data as $v)
        {
            $fh='';
            foreach($field as $vf)
            {
                $fh.='_'.isset($v[$vf])?$v[$vf]:'';
            }
            if(!isset($zend[$fh]))
                {
                    $zend[$fh]=[];
                }
            if($unique&&!(in_array($v,$zend[$fh])))
                {
                     $zend[$fh][]=$v;
                }
                elseif(!$unique)
                {
                     $zend[$fh][]=$v;
                }
        }
    return $zend;
}

/**
 * @function 将数组提高一维，按每组多少个元素来分
 * @param $data 待分组数组
 * @param $listRows 每组多少条
 * @return array
 * @more 比如一维数组$data里有100个元素,$listROws=20,则会生成一个有五个元素(每个里面有20个元素)的二维数组
 */
function ndj_divide($data,$listRows)
{
    if(!$data){return [];}
    $total=count($data);
    $page=ceil($total/$listRows);
    $res=[];
    $i=$page;
    for($i=0;$i<$page;$i++){
        $res[]=array_values(array_slice($data,$i*$listRows,$listRows));
    }
    return $res;

}

/**
 * @function 遍历数组，修改数组元素
 * @param $data 待处理数组
 * @param $function 回调函数
 * @return array
 * @more 函数用来重新设置数组元素 $function 是个函数，一般用的是匿名~
 */
function ndj_array_reset($data,$function){
    $res=[];
    foreach($data as $k=>$v){
        $v = $function($v);
        if(!is_null($v))
        {
            $res[$k]=$v;
        }
    }
    return $res;
}

/***
 * @function 一维数组转二维数组并设置键名
** @param $data array 一维数组，[1,2,3]
** @param $fieldname string 要设置的键名,默认为索引数组
** @return $res array  返回设置好的二维数组  [['id'=>1],['id'=>2],['id'=>3]]
****/
function ndj_dot2line($data=null,$fieldname=null,$setDot2Key=true){
    if(is_null($data)){return [];}
    $res=[];
    if(!is_string($fieldname)){
        foreach($data as $k=>$v){
            $res[$k][]=$v;
        }
    }elseif($setDot2Key){
        foreach($data as $v)
        {
            $res[$v]=[$fieldname=>$v];
        }
    }else{
        foreach($data as $k=>$v){
            $res[$k]=[$fieldname=>$v];
        }
    }
    return $res;
}

/***
 * @function 根据特定字段合并数组
** @param $relationfield string 链接各个数组的主见，
** @param $data array 待合并的数组
** @param $default  各键值的默认值。
** @return array 返回合并好的数组。
****/
function ndj_array_merge($relationfield='hostid',$data=null,$default=NA){
    if(!$data){
        return [];
    }
    $datalength=count($data);
    if($datalength==1){return $data;}
    $first=array_shift($data);
    $check=array_pop($first);
    if(!isset($check[$relationfield])){return $first;}
    $first[]=$check;
    $keys=[];
    foreach($data as $k=>$v){
        if(!$v)
        {
            continue;
        }
        $check=array_pop($v);
        $keys=array_merge($keys,array_keys($check));//全部键名
        $data[$k][]=$check;
    }
    $keys=array_unique($keys);
    $firstbt=[];
    foreach($first as $k=>$v){
        foreach($keys as $vk){
            if(!isset($v[$vk])){
                $v[$vk]=$default;
            }
        }
        $firstbt[$v[$relationfield]]=$v;
    }//待合并数组初始化
    foreach($data as $vt){
        foreach($vt as $v){
        if((!isset($v[$relationfield]))||!isset($firstbt[$v[$relationfield]])){
            continue;
        }else{
            $firstbt[$v[$relationfield]]=array_merge($firstbt[$v[$relationfield]],$v);
        }
        }
    }
    return $firstbt;
}

/***合并数组，并检测唯一性
** @param $data1,$data2
** @return $data
***/
function ndj_unique_merge($data1=[],$data2=[])
{
    $data=array_merge((array)$data1,(array)$data2);
    $data=ndj_unique($data);
    return $data;
}

/**
 * @function 数组去重，除了一层key 元素进行完全比较。
 * @param array $data1
 * @return array
 * @more 不传$flag,就是普通的unique,否则就是针对二维以上数组的unique,
 */
function ndj_unique($data1=[])
{
    $keys=[];
    $res=[];
    $demo=current($data1);
    if(is_array($demo))
    {
        foreach($data1 as $k=>$v)
        {
            $flagstr=json_encode($v);

            if(!isset($keys[$flagstr]))
            {
                $keys[$flagstr]=$flagstr;
                $res[$k]=$v;
            }

        }
    }else{
        foreach($data1 as $k=>$v)
        {
            $flagstr=$v;

            if(!isset($keys[$flagstr]))
            {
                $keys[$flagstr]=$flagstr;
                $res[$k]=$v;
            }

        }
    }

    return $res;
}

/***
 * @function 数组内元素的替换
** @param $data array 待替换数组，或者字符串
** @param $para array 规则，[],[],
** @return $data array/string 替换完成的结果,目前仅支持一维数组替换2017/3/13
 * @more 又一项事业未竟,
 * 那个想法需要多少东西去支撑，好麻烦的样子
***/
function ndj_array_replace($data,$para1=null,$para2)
{
    if(is_null($para1))
    {
        return $data;
    }
    // $para1=ndj_to_array($para1);
    // $para2=ndj_to_array($para2);
    if(is_string($data)&&!is_array($para2))
    {
        if(in_array($data,$para1))
        {
            $res=$para2;
        }
    }
    elseif(is_array($data))
    {
        if(!is_array($para1))
        {
            
        }
    }


}

/**
 * @function 多个消息分行显示
 * @param null $msgbox 消息盒子
 * @return string 加入换行符之后的字符串。
 * @more 用过一次,感觉还可以吧
 */
function makeMsgFromArray($msgbox=null){
    $msg='';
    if(!$msgbox){return $msg;}
    foreach($msgbox as $v){
        $msg.="\n".$v['type'].':'."\n".$v['message'];
    }
    return $msg;
}

/***
 * @function 检查并强制一个参数为数组，并返回这个数组
** @param $data boolean/string/array/int 参数$data
** @return $data array 返回数组，简单处理
***/
function ndj_to_array($data=[])
{
  if(is_array($data))
  {

  }
  else
  {
      $data=[$data];
  }
  return $data;
}

/**
 * @function 获取指定项，如果没有返回默认值，
 * @param $data 指定数据
 * @param null $default 默认值
 * @param bool $getTrue 是否检查结果的boolean属性
 * @return null
 * @more
 */
function ndj_get($data,$default=null,$getTrue=false)
{
    if(isset($data))
    {
        if($getTrue)
        {
            if($data)
            {
                return $data;
            }
            else
            {
                return $default;
            }
        }
        return $data;
    }
    else
    {

        return $default;
    }
}

/**@function 将参数转为字符串以便输出
 * @param $data 要转为字符串的参数
 * @return mixed 返回字符串
 * @more
 */
function ndj_toString($data)
{   $string='';
    if(is_array($data))
    {
        foreach($data as $k=>$v)
        {
            $string.=$k.':'.json_encode($v);
        }
    }
    elseif(is_string($data)||is_numeric($data))
    {
        $string=$data;
    }
   return $string;
}

/**
 * @function 检查某数组中一些字段是否存在，是否为真，支持给出字段跳出检查的值
 * @param $data
 * @param $fieldListC 待检查字段，允许给出某些字段免检值
 * @return array
 * @more 比如这样 $data=['id'=>1,'value'=>0,'age'=>0],fieldList=['id','value'=>[0],'age','class']
 * 此时value的0可以跳过false检查，age的0就不可以,返回值['f'=>['age'],'s'=>['class'],'t'=>['id','value'],表示字段值为false的:age,字段值缺失的:class,字段值检查通过的:id,value
 */
function ndj_checkField($data,$fieldListC)
{
    $fieldList=['f'=>[],'t'=>[],'s'=>[]];
    foreach($fieldListC as $k=>$v)
    {
        if(!is_array($v)&&isset($data[$v]))
        {
            if($data[$v]||in_array($data[$v],$fieldList[$v]))
            {
                $fieldList['t'][]=$v;
            }
            else
            {
                $fieldList['f'][]=$v;
            }
        }
        elseif(is_array($v)&&isset($data[$k]))
        {
            if($data[$k]||in_array($data[$k],$v,true))
            {
                $fieldList['t'][]=$k;
            }
            else
            {
                $fieldList['f'][]=$k;
            }
        }
        else
        {
            $fieldList['s'][]=!is_array($v)?$v:$k;
        }
    }
    return $fieldList;
    return $fieldList;
}

/**
 * @function 输出检查字段结果 和ndj_checkField配合
 * @param $checkRes ndj_checkField()的检查结果
 * @return string 字符串
 * @more
 */
function ndj_getCheckRes($checkRes)
{
    $str='';
    if($checkRes['f'])
    {
        $str.='参数错误列表:['.implode(',',$checkRes['f']).']';
    }
    if($checkRes['s'])
    {
        $str.='参数缺失列表:['.implode(',',$checkRes['s']).']';
    }

    return $str;
}

/**
 * @function 生成指定长度的随机字符串
 * @param $length
 * @return null|string
 * @more 字符库可以扩充
 */
function getRandChar($length){
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }
    return $str;
}

/***
 * @function 获取客户端IP地址
 * @param int $type
 * @return mixed
 */
function getIP() {
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    }
    elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    }
    elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');

    }
    elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    }
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/****
 * @function 获取客户端IP,
 * @param int $type
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = ip2long($ip);
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/***
 * 过滤掉ids里不为0且不与$cid相等的元素。
 * @param array $ids
 * @param $cid
 * @return array
 */
function find_parentid($ids=[],$cid)
{
    return array_filter($ids,function($v) use($cid){return $v;});
}
/*****
 * @function获取数组中所有指定节点的子。path,$id
 * @param $data
 * @param $id
 * @return array
 */
function  find_child($data,$id,$MassThis=false)
{
        $data=array_filter($data,function($v) use($id,$MassThis){
            $arr=ndj_explode($v['path'],'_');
            return in_array($id,$arr)&&(($arr[count($arr)-1]!=$id)||$MassThis);//查找
        });

    return $data;
}

/**
 * @function 冒泡排序
 * @param null $data
 * @param null $field
 * @param bool $desc
 * @return array|null
 */
function sortDataByBubble(&$data=null,$field=null,$desc=false){
    $total=count($data);
    //升序
    
    for($i=0;$i<$total-1;$i++){
        for($j=0;$j<$total-$i-1;$j++){
            if(intval($data[$j][$field])<intval($data[$j+1][$field])){
                $dot=$data[$j];
                $data[$j]=$data[$j+1];
                $data[$j+1]=$dot;
            }
        }
    }
    if($desc)
    {
        $count=count($data)-1;
        $res=[];
        for($i=$count;$i>=0;$i--)
        {
            $res[]=$data[$i];
        }
        $data=$res;

    }
    return $data;
}

/**
 * @function 获取多维数组某个键名子数组的merge
 * @param array $data
 * @param null $field
 * @return array
 * @more 只用了一次
 */
function ndj_getFieldMerge($data=[],$field=null)
{
    $res=[];
    if(!$data||!$field)
    {
        return $res;
    }
    foreach($data as $v)
    {
        if(!isset($v[$field]))
        {
            return $res;
        }
        $v[$field]=(array)$v[$field];
        $res=array_merge($res,$v[$field]);
    }
    $res=ndj_unique($res);
    return $res;

}

/**
 * @function 取一组历史数据中整点前的最近一条数据，
 * @param $historyData []
 * @param $timeC [['name'=>1,'clock'=>100]],both of configs all sort with desc,
 */
function ndj_getHistoryByTimeC($historyData,$timeC)
{
    $timeC=ndj_rmvKey($timeC);
   $box=[];
   foreach($timeC as $v)
   {
       $box[$v['name']]=[];
   }
   $shi=0;
   foreach($historyData as $v)
   {
       if(intval($v['clock'])<intval($timeC[$shi]['clock']))
       {
           $box[$timeC[$shi]['name']]=$v;
       }
       elseif(isset($timeC[$shi+1]))
       {
           $shi++;
       }
       else
       {
           break;
       }
   }
   return $box;
}

/**
 * @function 数字转换为周几，星期几之类
 * @param null $num
 * @param string $flag
 * @return string
 * 比如 5=>周五，5=>星期五
 */
function num2week($num=null,$flag='周')
{
    switch($num)
    {
        case 6:
            $str=$flag.'六';
            break;
        case 5:
            $str=$flag.'五';
            break;
        case 4:
            $str=$flag.'四';
            break;
        case 3:
            $str=$flag.'三';
            break;
        case 2:
            $str=$flag.'二';
            break;
        case 1:
            $str=$flag.'一';
            break;
        case 0:
            $str=$flag.'日';
            break;
        case '0':
            $str=$flag.'日';
            break;
        default:
            $str='';
    }
    return $str;
}

/**
 * @function 处理一组值的单位,按照千进制来换算
 * @param $str
 * @param null $decimal_input
 * @param int $decimalLength
 * @param bool $floatval
 * @return array|mixed
 */
function ndj_sortUnits($str,$decimal_input=null,$decimalLength=2,$floatval=true)
{
    $k=1000;
    $m=1000000;
    $g=1000000000;
    $t=1000000000000;
    $decimalList=[''=>1, 'k'=>$k, 'K'=>$k, 'm'=>$m, 'M'=>$m, 'g'=>$g, 'G'=>$g, 't'=>$t, 'T'=>$t];//strtolower,then,get value,
    $unitsBase=[
            ];//可以处理一下单位的转换比如 g->kg ->t
    if(is_null($decimal_input))
    {
        $res=preg_replace('/(\d{1,})(\.{0,1})(\d{0,})([t|T|G|g|m|M|k|K]{0,1})(.*)/','$1_$2_$3_$4_$5',$str);//找出了整数位,数量级单位，单位
    }
    else
    {
        $res=preg_replace('/(\d{1,})(\.{0,1})(\d{0,})([t|T|G|g|m|M|k|K]{0,1})('.$decimal_input.')$/','$1_$2_$3_$4_$5',$str);//找出了整数位,数量级单位，单位

    }
    $data=explode('_',$res);
    //0=>int,1=>.,2=>float,3=>k,4=>units
    list($int,$dot,$decimal,$magnitude,$units)=[$data[0],$data[1],$data[2],$data[3],$data[4]];
    $num=$int.$dot.$decimal;
    $num=$num*$decimalList[$magnitude];
    $num_magnitude='';
    if($num>$t*$k)
    {
        //科学计数法
        $num_clean=$num;
    }
    elseif($num>$t)
    {
        //T
        $num_clean=$num/$t;
        $num_magnitude='T';
    }
    elseif($num>$g)
    {
        //g
        $num_clean=$num/$g;
        $num_magnitude='G';
    }
    elseif($num>$m)
    {
        //m
        $num_clean=$num/$m;
        $num_magnitude='M';
    }
    elseif($num>$k)
    {
        //k
        $num_clean=$num/$k;
        $num_magnitude='K';
    }
    else
    {
        //保留几位小数
        $num_clean=$num;
    }
    if($decimalLength!=null)
    {
        $num_clean=sprintf("%.".$decimalLength."f", $num_clean);
    }
    if($floatval)
    {
        $num_clean=floatval($num_clean);
        $num=floatval($num);
    }
    $preg_divide_num_units=preg_replace('/(\d{1,})(\.{0,1})(\d{0,})(.*)/','$1$2$3_$4',$str);
    list($num_original,$num_units_original)=explode('_',$preg_divide_num_units);
    $res=[
        'str_original'=>$str,
        'num_original'=>$num_original,
        'num_units_original'=>$num_units_original,
        'num_units'=>$units,
        'num_long'=>$num,
        'num_clean'=>$num_clean,
        'num_magnitude'=>$num_magnitude,
        'num_all'=>$num_clean.$num_magnitude.$units
    ];
    return $res;

}

/**
 * @function 二维数组，排序字段，升降序,SORT_ASC,SORT_DESC,
 * @param $data array 输入数组
 * @param $col string 排序字段名
 * @param int $type 排序类型 升降序,SORT_ASC,SORT_DESC,
 * @return bool array
 * @more
 */
function ndj_arrsort($data,$col,$type=SORT_ASC){
    if(is_array($data)){
        $i=0;
        foreach($data as $k=>$v){
            if(key_exists($col,$v)){
                $arr[$i] = $v[$col];
                $i++;
            }else{
                continue;
            }
        }
    }else{
        return false;
    }
    array_multisort($arr,$type,$data);
    return $data;
}
//单项,

/**
 * @function 未知
 * @param $data
 * @param $keys
 * @param bool $strict
 * @return bool
 * @more
 */
function getDataByKey($data,$keys,$strict=false)
{
    $res=false;
    $str=ndj_get($data[$keys['field']],'');
    if($str!==''&&isset($keys['value']))
    {
        $keys['value']=(array)$keys['value'];
        foreach($keys['value'] as $v)
        {
            if(!$strict)
            {
                $v=strtolower($v);
                $str=strtolower($str);
            }

            $res=$res||preg_match('/(.*)'.$v.'(.*)/',$str);
            if($res){break;}

        }

    }
    return $res;
}

/***
 * @function 未知
 * @param $data [['name'=>'zhagnsna','role'=>'admin']]
 * @param $keys [['field'=>'name','value'=>['张三','李四']],['field'=>'role','value'=>['Admin','coder']]]
 * @return
 * @more
 */
function getDataByKeys($data,$keys,$strict=false)
{
    if(!$keys)
    {
        return $data;
    }
    $res=array_filter($data,function($v) use($keys,$strict){
        foreach($keys as $k)
        {

            if($res=getDataByKey($v,$k,$strict))
            {
                return $res;
            };
        }
        return false;
    });
    return $res;

}

/**
 * @function 移除文件
 * @param $pathes 二维数组
 * @param $field 字段名
 * @return array
 * @more
 */
function unlinkFiles($pathes,$field)
{
    $res=[
        'not_exist'=>[
            'list'=>[],
            'total'=>0
        ],
        'success'=>[
            'list'=>[],
            'total'=>0
        ],
        'false'=>[
            'list'=>[],
            'total'=>0
        ]
    ];
        foreach($pathes as $v)
        {
            if(is_file(ndj_get($v[$field],'')))
            {
                if(unlink($v[$field]))
                {
                    $res['success']['list'][]=$v;
                    $res['success']['total']++;
                }
            }
            else
            {
                $res['not_exist']['list'][]=$v;
                $res['not_exist']['total']++;
            }
        }

            $msg='';
            if($res['not_exist']['total'])
            {
                $msg.='未找到共:'.$res['not_exist']['total'].'位置如下:'.implode(',',ndj_objectValues($res['not_exist']['list'],'map_path')).' ';
            }
            if($res['false']['total'])
            {
                $msg.='失败共:'.$res['false']['total'].'位置如下:'.implode(',',ndj_objectValues($res['false']['list'],'map_path')).' ';
            }
            if($res['success']['total'])
            {
                $msg.='成功共:'.$res['success']['total'].'位置如下:'.implode(',',ndj_objectValues($res['success']['list'],'map_path')).' '      ;
            }
            $res['msg']=$msg;

            return $res;

}

/***
 * @function 将存储数据的基类对象处理成数组
 * @param $object
 * @return mixed
 * @more 页面JSON.stringify转对象数组成json字符串，后太接收后，会出现stdClass Object,用这个转成普通数组。

 */
function ndj_object2array(&$object)
{
    $object =  json_decode( json_encode( $object),true);
    return  $object;
}

/**
 * @function 秒数转成时间单位
 * @param $time
 * @param string $unit
 * @return array
 */
function ndj_int2timeUnit($time,$unit='s')
{
    $unit=strtolower($unit);
    switch(intval($time))
    {
        case 1:
            $u='s';
            $unit='秒';
            break;
        case 60:
            $u='i';
            $unit='分';
            break;
        case 3600:
            $u='h';
            $unit='时';
            break;
        case $day=3600*24:
            $u='d';
            $unit='日';
            break;
        case $day*30:
            $u='m';
            $unit='月';
            break;
        case $day*90:
            $u='q';
            $unit='季度';
            break;
        case $day*365:
            $u='y';
            $unit='年';
            break;
        default:
            $u=NA;
            $unit=NA;
    }
    return ['u'=>$u,'unit'=>$unit];
}

/**
 * @function 格式化时间
 * @param $time
 * @param string $unit
 * @more  未完成
 */
function ndj_timeFormatByUnit($time,$unit='s')
{
    $res=['value'=>$time];
    $unit=strtolower($unit);
    switch($unit)
    {
        case 's':
            $res['unit']='s';
            $res['res']=$time.'秒';
            break;
        case 'i':
            $res['unit']='i';
            $res['res']=($time%60?floor($time/60).'分'.($time%60).'秒':floor($time/60).'分');
            break;
        case 'h':
            $res['unit']='h';
            $res['res']=date('H:i:s',$time);
            break;
        case 'd':
            $res['unit']='d';
            $res['res']=date('d H:i:s');
            break;
        case 'w':

            break;
        case 'm':
            break;
        case 'q':
            break;
        case 'y':
            break;
        default:
    }


}

/**
 * @function 在零时区将时间字符串格式化成秒数,
 * @example 08:00->28400,一个格式化的时间到秒
 * @param $data
 * @return false|int
 * @more
 */
function ndj_strtotime_withinaday($data)
{

    if(!is_numeric($data))
    {
        $data=(strtotime("1970-01-01 ".$data)+date("H",0)*3600);
    }
    return $data;
}

/**
 * @function 在零时区将秒数格式化成时间字符串
 * @example 28400->08:00，这样的转换，从秒到一天之内的一个格式化时间
 * @param int $data
 * @param bool $removeDiff
 * @return false|int|string
 * @more
 */
function ndj_timetostr_withinaday($data=0,$removeDiff=true)
{
    if(is_numeric($data)&&$data<NDJ_SECEND_DAY)
    {
        if($removeDiff)
        {
            $data=$data-date("H",0)*3600;

        }
        $data=date("H:i",$data);
    }else{
        $data='24:00';
    }
    return $data;
}

/***
 * @function 集合的并集运算，集合用俩元素的一维数组表示，仅支持俩集合的操作
 * @param $a
 * @param $b
 * @return array
 */
function ndj_u($a,$b)
{
    if($a[0]>$b[1]||$a[1]<$b[0])
    {
        $res=[$a,$b];
    }
    else
    {
        $res[]=[$a[0]>$b[0]?$b[0]:$a[0],$a[1]>$b[1]?$a[1]:$b[1]];
    }
    return $res;
}

/**
 * @function 集合的交集运算，集合用俩元素的一维数组表示，仅支持俩集合
 * @param $a
 * @param $b
 * @return array
 */
function ndj_n($a,$b)
{
    $l=$a[0]>$b[0]?$a[0]:$b[0];
    $r=$a[1]>$b[1]?$b[1]:$a[1];
    return $l<=$r?[$l,$r]:[];
}

/**
 * @function 两个操作数的交，多段ns([[0,3],[4,5],[6,7]],[[1,6]])
 * @param $a example=[[0,3],[4,5],[6,7]]
 * @param $b example=[[1,6]]
 * @return array
 */
function ndj_ns($a,$b)
{
    $res=[];
    foreach($a as $v)
    {
        foreach($b as $vb)
        {
            $n=ndj_n($v,$vb);
            if(!empty($n))
            {
                $res[]=$n;
            }
        }
    }
    return $res;
}

/**
 * @function 一对多的并集，
 * @param $a [1,4],一
 * @param $b [[2,3],[5,7]],多
 * @return array 返回二维数组并集结果
 */
function ndj_u_us($a,$b)
{
    $ki=[];
    foreach($b as $k=>$v)
    {
        $z=ndj_u($a,$v);
        if(count($z)<2)
        {//可并
            unset($b[$k]);
            $ki=empty($ki)?$z[0]:ndj_u($ki,$z[0])[0];
        }
    }
    $b[]=!empty($ki)?$ki:$a;
    return $b;
}

/***
 * @function 取并集 批量并，多并多，
 * @param $a [[1,3],[5,6]]
 * @param $b [[2,4],[6,8]]
 * @return array
 */
function ndj_us($a,$b)
{
    foreach($a as $av)
    {
        $b=ndj_u_us($av,$b);
    }
    return $b;
}

/**
 * @function 从session里取个一次性数据并销毁session
 * @return mixed|string
 */
function ndj_getKeyFromUrl()
{
    $id='key_from_url';
    if($value=session($id))
    {
        session($id,null);
        $res=$value;
    }
    else{
        $res='';
    }
    return $res;
}

/**
 * @function 处理单位
 * @param $value
 * @param string $units
 * @param null $name
 * @return array
 */
function ndj_sortUnitForEach($value,$units='',$name=null)
{
    $formulaList=[
        1=>'K',
        2=>'M',
        3=>'G',
        4=>'T',
        5=>'P',
        6=>'E',
        7=>'B'
    ];
    $units=ndj_get($units,'');
    $beilv=in_array($units,['byte','bit/s','bit','bps','B'])?1024:1000;
    switch(strtolower($units))
    {
        case 'uptime':
            $value=is_numeric($value)&&$value>0?ndj_lasttime(0,$value):$value;
            $units='';
            break;
        case 'unixtime':
            $value=is_numeric($value)&&$value>0?date('Y-m-d H:i:s',$value):$value;
            $units='';
            break;
        case 's':
            $value=is_numeric($value)&&$value>0?ndj_convertUnitsS($value):$value;
            $units='';
            break;
        default:

    }
    if(is_numeric($value))
    {
        $rate=log($value,$beilv);
        if(isset($formulaList[floor($rate)]))
        {
            $value=round($value/pow($beilv,floor($rate)),2);
            $units=$formulaList[floor($rate)].$units;
        }
        elseif($rate>4)
        {
            $value=sprintf("%.2e",$value);
        }else
        {
            $value=round($value,2);
        }

    }
//    if($name=='devicestate')
//    {
//        $StateOnBox=['1','3','5','associated'];
//        $value=in_array($value,$StateOnBox)?'在线':'离线';
//    }
    $res=['value'=>$value,'units'=>$units];
    return $res;

}

/**
 * @function 确保数组里至少有一个元素，
 * @param array $array 数组
 * @param int $defaultFillValue 数组为空时填充用的键值
 * @param int $defultFillKey 数组为空时填充用的键值
 * @return array 返回检查后的数组
 * @function 该函数会确保数组内至少有一个元素
 * @example  1. ndj_array_hasAtLeastOne([])=>[0=>0],
 * 2.ndj_array_hasAtLeastOne(['name'=>'zhangxiaopang'])=>['name'=>'zhangxiaopang']
 * @former 之前是为了在 使用sql语句时  ..where id in ()..保证括号里至少有一个值，不会出现异常
 */
function ndj_array_hasAtLeastOne($array=[],$defaultFillValue=0,$defultFillKey=0)
{
    $array=is_array($array)?$array:[$array];
    return empty($array)?[$defaultFillValue=>$defultFillKey]:$array;
}

/***
 * @function 查看数组中的一个元素(第一个)
 * @param array $input
 * @return bool|mixed
 * @more 实际查看的是第一个元素，
 */
function ndj_array_view_one(array $input)
{

    return current($input);

}

/**
 * log1000(10000)=1
 * @param $bytes
 * @return string
 */
function ndj_log1000($bytes)
{
    return floor(log($bytes)/log(1000));
}
function ndj_power1000($exp,$base=1000){
    return pow($base,$exp);
}
function ndj_magnitude2str($magnitude=0){
    $magnitudeList=[
      0=>'',
        1=>'K',
        2=>'M',
        3=>'G',
        4=>'T',
        5=>'P',
        6=>'E',
        7=>'B'
    ];
    $magnitude=floor($magnitude);
    if(is_numeric($magnitude)){
       $res=isset($magnitudeList[$magnitude])?$magnitudeList[$magnitude]:'1000^'.$magnitude;
    }else{
        $res=false;
    }
    return $res;
}
function ndj_flush($data,$time=0)
{
    if(ob_get_contents())
    {
        ob_end_clean();

    }
    if(!is_array($data))
    {
        echo $data;
    }else{
        print_r($data);
    }
    echo '<br/>';

    flush();    //刷新输出缓冲
    $time=intval($time);
    sleep($time);
}

/**
 * xmlobj递归
 * @param $arrObjData
 * @param array $arrSkipIndices
 * @return array
 */
function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();

    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }

    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}
//数组到xml字符串
function ndj_arrayToXml($arr,$rootkey='ndj_export',$tab_num=''){ 
	$xml = "$tab_num<".$rootkey.">\r\n"; 
	foreach ($arr as $key=>$val){ 
		$key=is_numeric($key)?rtrim($rootkey,'s'):$key;
		if(is_array($val)){ 
			$xml.=ndj_arrayToXml($val,is_numeric($key)?rtrim($key,'s'):$key,$tab_num.'	'); 
		}else{
			$xml.="$tab_num	<".$key.">".$val."</".$key.">\r\n"; 
		} 
	} 
	$xml.="$tab_num</".$rootkey.">\r\n"; 
	return $xml; 
}
/***
 * @function 将xml字符串转为结构化数组。
 * @param $xmlstring
 * @return array
 */
function ndj_xmlString2array($xmlstring)
{
    $xmlObj = simplexml_load_string($xmlstring);
    $arrXml = objectsIntoArray($xmlObj);
    return $arrXml;
}
 /***
 **获取当前设备ipv4地址
 **
 ***/
 function ndj_getServerIP()
 {
    $res=shell_exec("ifconfig");
    $res=$res?$res:shell_exec("ipconfig");
    $res=iconv("gb2312","utf-8",$res);
    $res=str_replace("\n",'',$res);
    $res=str_replace(" ",'',$res);
    if(preg_match("/子网/",$res)){
    $pat='/(.*)(v4)(.*)(:)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(子网)(.*)/';}else{
        $pat='/(.*)(i)(n)(et)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(netmask255\.255)(.*)/';
    }
//    $data=preg_match($pat,$res,$out);
    $res=preg_replace($pat.'is','$5',$res);
    return $res;
 }

/**
 * 交换数组里两个元素
 * @param $arr
 * @param $i
 * @param $j
 * @return null
 */
 function ndj_swap(&$arr,$i,$j)
 {
     $temp=$arr[$i];
     $arr[$i]=$j;
     $arr[$j]=$temp;
 }
function SortTopN($box,$field='value',$topN=3)
{
    $base=[];
    for($i=0;$i<$topN;$i++)
    {
        $pour=array_shift($box);
        if(!is_null($pour))
        {
            $base[]=$pour;
        }else{
            break;
        }
    }
    if(count($base)<$topN)
    {
        return ndj_arrsort($base,$field,SORT_DESC);
    }
    $base=ndj_arrsort($base,$field,SORT_ASC);
    foreach($box as $new)
    {
        if($new[$field]>$base[0][$field])
        {
            $base[0]=$new;
            SortHeap($base,0,$field);
        }
    }
    $base=ndj_arrsort($base,$field,SORT_DESC);
    return $base;
}

//交换
function swap(&$arr,$i,$j)
{
    $temp=$arr[$i];
    $arr[$i]=$arr[$j];
    $arr[$j]=$temp;
}
//插入调整。
function SortHeap(&$arr,$now=0,$field='value')
{
    //左子
    if(isset($arr[2*$now+1])&&$arr[$now][$field]>$arr[2*$now+1][$field])
    {
        swap($arr,$now,2*$now+1);
        SortHeap($arr,2*$now+1,$field);
    }
    //右子
    if(isset($arr[2*$now+2])&&$arr[$now][$field]>$arr[2*$now+2][$field])
    {
        swap($arr,$now,2*$now+2);
        SortHeap($arr,2*$now+2,$field);
    }

}
/***
 * 设置缓存
 * @param $class
 * @param $method
 * @param $data
 * @return mixed|string
 * @more 设置缓存时，加前缀,authid
 */
 function ndj_cache_set($key,$data,$key_type='node',$timeLimit=NDJ_AJAX_DATA_CACHE_TIME)
 {
     $key=ndj_to_array($key);
     $key=session('authid').implode('',$key);
     $key=hash(NDJ_AJAX_DATA_CACHE_KEY_HASH,$key);
     if(!ndj_cache_push($key)) return false;//添加到keyList
     $key=cache($key,$data,$timeLimit);
     return $key;
 }

/***
 * 获取缓存中指定key的值
 * @param $class
 * @param $method
 * @return mixed
 * @more 前缀以 authid
 */
 function ndj_cache_get($key,$default)
 {
     $key=ndj_to_array($key);
     $key=session('authid').implode('',$key);
     $key=hash(NDJ_AJAX_DATA_CACHE_KEY_HASH,$key);
     return cache($key);
 }
 function ndj_cache_clear($key,$default)
 {
     $key=ndj_to_array($key);
     $key=session('authid').implode('',$key);
     $key=hash(NDJ_AJAX_DATA_CACHE_KEY_HASH,$key);
     ndj_cache_keyListDelete($key);
     return cache($key,null);
 }
 function ndj_cache_push($key){
     $keyName=hash(NDJ_AJAX_DATA_CACHE_KEY_HASH,NDJ_AJAX_DATA_CACHE_KEYLIST);
     $before=cache($keyName);
     $before=is_array($before)&&$before?$before:[];
     $before[]=$key;
     return cache($keyName,$before);
 }
 function ndj_cache_keyListDelete($key)
 {
     $key=ndj_to_array($key);
     $keyListAll=ndj_cache_getKeyListAll();
     $keyListAll=array_filter($keyListAll,function($v)use($key){
        return !in_array($v,$key);
     });
     $keyName=hash(NDJ_AJAX_DATA_CACHE_KEY_HASH,NDJ_AJAX_DATA_CACHE_KEYLIST);
     return cache($keyName,$keyListAll);

 }
function ndj_cache_getKeyListAll()
{
    $keyName=hash(NDJ_AJAX_DATA_CACHE_KEY_HASH,NDJ_AJAX_DATA_CACHE_KEYLIST);
    return cache($keyName);
}
/***
 *
 */
 function ndj_cache_clearAll()
 {
     $keyName=hash(NDJ_AJAX_DATA_CACHE_KEY_HASH,NDJ_AJAX_DATA_CACHE_KEYLIST);
     $keyList=ndj_cache_getKeyListAll();
     $res=true;
     if(is_array($keyList))
     {
         foreach($keyList as $key)
         {
             $res=$res&&cache($key,null);
         }
         if($res)
         {
             $res=$res&&cache($keyName,[]);
         }
     }
     return $res;
 }
 function ndj_start($index){
     $_REQUEST['debug']['start'][$index]=microtime(true);
 }
 function ndj_end($index,$continue=true)
 {
     if($continue)
     {
         $_REQUEST['debug']['start'][$index+1]=microtime(true);
     }
     return is_numeric($_REQUEST['debug']['start'][$index])?microtime(true)-$_REQUEST['debug']['start'][$index]:false;

 }
 function session($key,$value='ndj_just_get_session',$prefix=null){
     $session=config('session');
    $prefix=isset($session['prefix'])?$session['prefix']:null;
    if($value==='ndj_just_get_session'){
        session_start();
        $res=is_null($prefix)?$_SESSION[$key]:$_SESSION[$prefix][$key];
        session_write_close();
        return $res;
    }else{
        session_start();
        if($prefix){
            $_SESSION[$prefix][$key]=$value;
        }else{
            $_SESSION[$key]=$value;
        }
        session_write_close();
    }


 }

/***
 * 设置ini文件的配置项
 * @param null $options exp:
 * $options=[
'fieldValue'=>[
'HomepageRelateTree'=>'DD无线'
],
 'content'=>'',
'file_path'=>'dynamicStatic.ini'
];
 * @return bool|int
 */
function ndj_ini_set($options=null){
    if(is_null($options['fieldValue'])){
        return false;
    }
//    $content=isset($options['content'])?$options['content']:file_get_contents($options['file_path']);
//    if(!is_string($content)){
//        return false;
//    }
//    foreach($options['fieldValue'] as $field=>$value){
//
//        $content=str_replace('.','\.',$content);
////        $content=str_replace('*','\*',$content);
//        $content=preg_replace("/(.*)(".$field." *= *)(.*)(;.*)/","$1$2".$value."$4",$content);
//        $content=str_replace('\.','.',$content);
////        $content=str_replace('\\*','*',$content);
//    }
    $content='';
    foreach ($options['fieldValue'] as $key=>$value)
    {
        $content.=$key.' = '.$value.';'.PHP_EOL;
    }
    return file_put_contents($options['file_path'],$content);
}
function ndj_convertUnitsS($value, $ignore_millisec = false) {
    $secs = round($value * 1000, ZBX_UNITS_ROUNDOFF_UPPER_LIMIT) / 1000;
    if ($secs < 0) {
        $secs = -$secs;
        $str = '-';
    }
    else {
        $str = '';
    }

    $values = ['y' => null, 'm' => null, 'd' => null, 'h' => null, 'mm' => null, 's' => null, 'ms' => null];

    /*
     * $n_unit == 4,	(#y #m #d)
     * $n_unit == 3,	(#m #d #h)
     * $n_unit == 2,	(#d #h #mm)
     * $n_unit == 1,	(#h #mm #s)
     * $n_unit == 0,	(#mm #s) or (#mm #s #ms)
     */
    $n_unit = 0;

    $n = floor($secs / SEC_PER_YEAR);
    if ($n != 0) {
        $secs -= $n * SEC_PER_YEAR;
        $n_unit = 4;

        $values['y'] = $n;
    }

    $n = floor($secs / SEC_PER_MONTH);
    $secs -= $n * SEC_PER_MONTH;

    if ($n == 12) {
        $values['y']++;
    }
    else {
        if ($n != 0) {
            $values['m'] = $n;
            if ($n_unit == 0) {
                $n_unit = 3;
            }
        }

        $n = floor($secs / SEC_PER_DAY);
        if ($n != 0) {
            $secs -= $n * SEC_PER_DAY;
            $values['d'] = $n;
            if ($n_unit == 0) {
                $n_unit = 2;
            }
        }

        $n = floor($secs / SEC_PER_HOUR);
        if ($n_unit < 4 && $n != 0) {
            $secs -= $n * SEC_PER_HOUR;
            $values['h'] = $n;
            if ($n_unit == 0) {
                $n_unit = 1;
            }
        }

        $n = floor($secs / SEC_PER_MIN);
        if ($n_unit < 3 && $n != 0) {
            $secs -= $n * SEC_PER_MIN;
            $values['mm'] = $n;
        }

        $n = floor($secs);
        if ($n_unit < 2 && $n != 0) {
            $secs -= $n;
            $values['s'] = $n;
        }

        if ($ignore_millisec) {
            $n = round($secs, ZBX_UNITS_ROUNDOFF_UPPER_LIMIT);
            if ($n_unit < 1 && $n != 0) {
                $values['s'] += $n;
            }
        }
        else {
            $n = round($secs * 1000, ZBX_UNITS_ROUNDOFF_UPPER_LIMIT);
            if ($n_unit < 1 && $n != 0) {
                $values['ms'] = $n;
            }
        }
    }

    $str .= isset($values['y']) ? $values['y']._x('y', 'year short').' ' : '';
    $str .= isset($values['m']) ? $values['m']._x('m', 'month short').' ' : '';
    $str .= isset($values['d']) ? $values['d']._x('d', 'day short').' ' : '';
    $str .= isset($values['h']) ? $values['h']._x('h', 'hour short').' ' : '';
    $str .= isset($values['mm']) ? $values['mm']._x('m', 'minute short').' ' : '';
    $str .= isset($values['s']) ? $values['s']._x('s', 'second short').' ' : '';
    $str .= isset($values['ms']) ? $values['ms']._x('ms', 'millisecond short') : '';

    return $str ? rtrim($str) : '0';
}

function ndj_getPidByName($name='')
{
    $res = shell_exec("ps -ef|grep '" . $name . "'|grep -v grep|awk '{print $2}'");
    return $res;
}
function ndj_getItemCacheKey($itemid)
{
 return 'item.'.$itemid;
}

function ndj_dump($str)
{
//    global $start;
//    $end=microtime(true);
//    dump($str."|耗时:".($end-$start)."|".$end);
//    $start=$end;
}


/***
 * 将子分组,获取所有子
 * @param $data :eg.[['id'=>1,'pid'=>0],['id'=>2,'pid'=>1]]
 * @return array.[0=>[1=>1,2=>2]]
 */
function ndj_child_group($data,$includeSelf=false,$idName='id',$pidName='pid')
{
    global $box,$dataStart;
    $dataStart=[];
    foreach($data as $v)
    {
        $dataStart[$v[$pidName]][$v[$idName]]=$v[$idName];
    }
    $box=$dataStart;

    if($includeSelf){
        foreach($dataStart as $k=>$v)
        {
            $box[$k][$k]=$k;
            ndj_insert_into_tree($k,$k);
        }
    }else{
        foreach($dataStart as $k=>$v)
        {
            ndj_insert_into_tree($k,$k);
        }
    }

    $Res=$box;
    unset($box);
    return $Res;
}

function ndj_insert_into_tree($nowPid=0,$insert_into=0)
{
    global $box,$dataStart;
    if(isset($dataStart[$nowPid]))
    {
        foreach($dataStart[$nowPid] as $v)
        {
            $box[$insert_into][$v]=$v;
            if(isset($dataStart[$v]))
                ndj_insert_into_tree($v,$insert_into);
        }
    }
}
/**
 * [将Base64图片转换为本地图片并保存]
 * @E-mial wuliqiang_aa@163.com
 * @TIME   2017-04-07
 * @WEB    http://blog.iinu.com.cn
 * @param  [Base64] $base64_image_content [要保存的Base64]
 * @param  [目录] $path [要保存的路径]
 */
function base64_image_content($base64_image_content,$path){
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
        $type = $result[2];
        $new_file = $path;
//        if(!file_exists($new_file)){
//            //检查是否有该文件夹，如果没有就创建，并给予最高权限
//            mkdir($new_file, 0700);
//        }
        $new_file = $new_file.".{$type}";
        $img = str_replace('data:image/png;base64,', '', $base64_image_content);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);

        $imgPath=$new_file;
        if(@file_exists($imgPath)){
            @unlink($imgPath);
        }@clearstatcache();
        $fp=fopen($imgPath,'w');
        fwrite($fp,$data);
        fclose($fp);
        if (file_exists($imgPath)){
            return $new_file;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

function ndj_num2utf8(int $num=0)
{
    $charlist=['零','一','二','三','四','五','六','七','八','九','十'];
    $res='';
    if($num<11)
    {
        $res=$charlist[$num];
    }elseif($ys=$num%10){
        //不是整十
        $res=$charlist[($num-$ys)/10].$charlist[$ys];
    }else{
        //整十
        $num=$num/10;
        $res=$charlist[$num];
    }
    return $res;
}
function addFileToZip($path,$zip){
    $handler=opendir($path); //打开当前文件夹由$path指定。
    while(($filename=readdir($handler))!==false){
        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
            if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                addFileToZip($path."/".$filename, $zip);
            }else{ //将文件加入zip对象
                $zip->addFile($path."/".$filename);
            }
        }
    }
    @closedir($path);
}
function ndj_set_html_msg($msg='',$id='ndj_progress_msg')
{
    ndj_flush("<script>document.getElementById('".$id."').innerHTML='".$msg."';</script>");
}
function ndj_progress_ctl($str='0%',$echoBox=false,$options=['html'=>'<span id="ndj_progress_ctl">0%</span>   <span id="ndj_progress_msg"></span>','id'=>'ndj_progress_ctl']){
    if($echoBox)
    {
        ndj_flush($options['html']);
    }
    ndj_flush("<script>document.getElementById('".$options['id']."').innerHTML='".$str."';</script>");
}

/**
 * @param $dirname 要清空内容的文件夹
 * @param bool $self 是否删除该文件夹
 * @return bool 清除结果
 * @more 删除指定文件夹内容，
 */
function do_rmdir($dirname, $self = true) {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    if ($dir) {
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            do_rmdir($dirname . '/' . $entry);
        }
    }
    $dir->close();
    $self && rmdir($dirname);
}

/**
 * 删除某个目录下的所有文件
 * 例如 /var/www/html/runtime/
 * @param $dirName 末尾必须带 /
 * @return bool
 *
 */
function ndj_deldir($path){
    //如果是目录则继续
    if(is_dir($path)){
        //扫描一个文件夹内的所有文件夹和文件并返回数组
        $p = scandir($path);
        foreach($p as $val){
            //排除目录中的.和..
            if($val !="." && $val !=".."){
                //如果是目录则递归子目录，继续操作
                if(is_dir($path.$val)){
                    //子目录中操作删除文件夹和文件
                    ndj_deldir($path.$val.'/');
                    //目录清空后删除空文件夹
                    @rmdir($path.$val.'/');
                }else{
                    //如果是文件直接删除
                    unlink($path.$val);
                }
            }
        }
    }
}