<?php
/*
高分网专用模板语法
*/



//模板函数
function tpl($tpl){
    //增加模板语法到模板类 
    add_tpl_parse_func('dc_tpl_parse');
    
    if(!$tpl){
        system_error('模板名不能为空！');
    }
    //带有冒号 是app模板
    if(strexists($tpl,':')){
        $tplarr = explode(':',$tpl);
        $app = trim($tplarr[0]);
        $tpl = trim($tplarr[1]);
    }else{
        $app = 'dearcms';
    }
    $tpl_root = TPL_ROOT.$app;//后面不能带'/'
    return template($tpl.'.html',$tpl_root);
}

//后台模板函数
function atpl($tpl){
    //增加模板语法到模板类 
    add_tpl_parse_func('dc_tpl_parse');
    
    if(!$tpl){
        system_error('模板名不能为空！');
    }
    //带有冒号 是app模板
    if(strexists($tpl,':')){
        $tplarr = explode(':',$tpl);
        $app = trim($tplarr[0]);
        $tpl = trim($tplarr[1]);
    }else{
        $app = 'admin';
    }
    $tpl_root = appath($app).'templates';//后面不能带'/'
    //最后一个参数为自定义模板缓存文件的目录 与default同级
    return template($tpl.'.html',$tpl_root,TPL_CACHEPATH_ADMIN.$app);
}

//添加模板解析函数
function add_tpl_parse_func($func){
    $tpl_parse_func = G('tpl_parse_func');
    //如果没定义过 
    if(!is_array($tpl_parse_func)) return GSET('tpl_parse_func',array($func));
    //如果已经存在
    if(!$func || in_array($func,$tpl_parse_func)) return false;
    //加入到解析函数数组
    $tpl_parse_func[] = $func;
    return GSET('tpl_parse_func',$tpl_parse_func);
}



//应对get模板语法
function get_parse($str)
{
	preg_match_all("/([a-z]+)\=\"([^\"]+)\"/i", stripslashes($str), $matches, PREG_SET_ORDER);
	foreach($matches as $v)
	{
		$r[$v[1]] = $v[2];
	}
	extract($r);
	if(!isset($dbsource)) $dbsource = '';
	if(!isset($dbname)) $dbname = '';
	if(!isset($sql)) $sql = '';
	if(!isset($rows)) $rows = 0;
	if(!isset($urlrule)) $urlrule = '';
	if(!isset($distinctfield)) $distinctfield = '';
	if(!isset($return) || !preg_match("/^\w+$/i", $return)) $return = 'r';
	if(isset($page))
	{
	    $str = "<?php \$ARRAY = get(\"$sql\", $rows, $page, \"$dbname\", \"$dbsource\", \"$urlrule\",\"$distinctfield\");\$DATA=\$ARRAY['data'];\$total=\$ARRAY['total'];\$count=\$ARRAY['count'];\$pages=\$ARRAY['pages'];unset(\$ARRAY);foreach(\$DATA AS \$n=>\${$return}){\$n++;?>";
	}
	else
	{
		$str = substr($str, -1) == '/' ? "<?php \${$return} = get(\"$sql\", -1, 0, \"$dbname\", \"$dbsource\");?>" : "<?php \$DATA = get(\"$sql\", $rows, 0, \"$dbname\", \"$dbsource\");foreach(\$DATA AS \$n => \${$return}) { \$n++;?>";
	}
	return $str;
}


/**
 * 解析标签内的各项属性
 */
function dc_param_parse($str,$type)
{
	//preg_match_all("/([a-z]+)\=\"([^\"]+)\"/i", stripslashes($str), $matches1, PREG_SET_ORDER);//属性双引号
	//preg_match_all("/([a-z]+)\=\'([^\']+)\'/i", stripslashes($str), $matches2, PREG_SET_ORDER);//属性单引号
	preg_match_all("/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)\=\"([^\"]+)\"/i", stripslashes($str), $matches1, PREG_SET_ORDER);//属性双引号
	preg_match_all("/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)\=\'([^\']+)\'/i", stripslashes($str), $matches2, PREG_SET_ORDER);//属性单引号
	foreach($matches1 as $v)
	{
		$r[$v[1]] = $v[2];
	}
	foreach($matches2 as $v)
	{
		$r[$v[1]] = $v[2];
	}
	@extract($r);//释放所有属性

	if(!isset($return) || !preg_match("/^\w+$/i", $return)) $return = 'r';
    
       //下面这些都是可以在模板语法中用到的属性 同时允许mouthnum和month
       $query['arc'] = compact('ids','noids','cat','cat_in','cat_and','cat_not','parent','catname','tag','tag_and','tag_in','meta_key','meta_value','post_type','num','order','orderby','year','monthnum','month','week','day','hour','flag','img','imgwidth','imgheight','loadcontent','loadcat','tiku_edu','tiku_course','tiku_type','shorttitle');
       $query['cat'] = $query['tag'] = $query['linkcat'] = compact('num','hide','fields','slug','counts','childof','parent','orderby','order','search','postid','include','tagkey');
       $query['dzxthread'] = compact('num','tids','bannedids','fids','uids','typeids','sortids','special','stick','digest','orderby','recommend','keyword','lastpost','picrequired','stamp','titlelen','summarylen','summarylength','prettylink');
       $query['dzxgroup'] = compact('num','fids','gtids','orderby','titlelen','prettylink');
       
       $query['post'] = compact('num','uid','typeid','offset','order','orderby','elite');
       $query['review'] = compact('num','uid','postid','fid','tid');
       $query['wall'] = compact('num','uid','order','orderby');
       
       $funcname = array('post'=>'postlist','review'=>'reviewlist','wall'=>'walllist','arc'=>'arclist','dzxthread'=>'dzxthread','dzxgroup'=>'dzxgroup','area'=>'arealist');
       
       //$paramstr = http_build_query($query[$type]);//此方法会encode所有参数，会导致$传值失效，无法循环嵌套
       $paramstr = '';
       if(isset($cachetime)){
            $query[$type]['cachetime'] = $cachetime;//加入缓存时间自定义
       }
       foreach($query[$type] AS $_k=>$_v){
            $paramstr .= $_k.'='.$_v.'&';
       }
       //去掉"[]"，避免直接传入数组值而导致出错
       $paramstr = strtr($paramstr,array('['=>'',']'=>''));
       
	$str = substr($str, -1) == '/' ? "<?php \${$return} = {$funcname[$type]}(\"$paramstr\");?>" : "<?php \$DATA = {$funcname[$type]}(\"$paramstr\");\$n=0;if(is_array(\$DATA)) foreach(\$DATA AS \$k => \${$return}) { \$n++;?>";
	return $str;

}

//自定义模版标签组 类似 {ad:list num='2'}
function dc_tpltag_cumstom($group='dearcms',$a='',$b='',$c=''){
    $paramstr = '<pre><g '.$group.' g> <a '.$a.' a> - <b '.$b.' b> - <c '.$c.' c></pre>';
    $return = 'r';
    return "<?php \$DATA = arclist(\"$paramstr\");\$n=0;if(is_array(\$DATA)) foreach(\$DATA AS \$k => \${$return}) { \$n++;?>";
}


//模板标签数据缓存  cc参数等于2时，更新缓存
function _load_tpltag_cache($type,$paramstr,$cachetime=''){
    if(!$paramstr || $_GET['cc']) return '';
    $key = md5($paramstr);
    if(_check_tpltag_cache($type,$key,$cachetime)){
        $keyfile = $type.'_'.$key.'.php';
        $keyfilepath = TPL_TAG_CACHEPATH.$keyfile;
        $data = cache_read($keyfile, TPL_TAG_CACHEPATH);
        return $data;
    }
    return false;
}

//模板标签数据缓存
function _write_tpltag_cache($type,$paramstr,$data,$cachetime=''){
    if(!$paramstr || !$data) return '';
    $keyfile = $type.'_'.md5($paramstr).'.php';
    //缓存过期 或者加了cc参数 重新写入
    if(!_check_tpltag_cache($type,md5($paramstr),$cachetime) || $_GET['cc']==2){
        cache_write($keyfile, $data, TPL_TAG_CACHEPATH);
    }
    return $data;//返回原始数据
}

//检查缓存是否过期
function _check_tpltag_cache($type,$key,$cachetime=''){
    if(!$key || $_GET['cc']) return '';
    $keyfile = $type.'_'.$key.'.php';
    $keyfilepath = TPL_TAG_CACHEPATH.$keyfile;

    //缓存刷新时间 后台设置的时间单位是分钟
    $_tpltag_refresh_time = TPLTAG_REFRESH_TIME ? TPLTAG_REFRESH_TIME : 60*60;
    //如果有自定义的缓存时间 cachetime
    $_tpltag_refresh_time = intval($cachetime) ? intval($cachetime) : $_tpltag_refresh_time;

    //如果缓存文件不存在 或者过期 则返回false
    if ((! file_exists ( $keyfilepath ) || (time () - @filemtime ( $keyfilepath ) > $_tpltag_refresh_time))) {
        return false;
    }
    return true;
}
