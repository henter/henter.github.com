<?php

/*
模板调用 三个参数
$template   模板名称
$mod           模块名 
$type           模板类型 如templates/shop/defalut中的shop（共三个 main主站  shop商铺  space个人空间）
$path           模板路径 如templates/shop/defalut中的default（商铺多模板基于此参数）
*/
function template($template,$mod='dc',$type='main',$path='')
{
        global $indextemplate;
        $path = $path ? $path : $indextemplate;
       $modurl = ($mod == 'dc') ? '' : $mod.'_';
       $tplmod = $modurl.$template;
       $templatemod = ($mod ? $mod : 'dc').'_'.$path.'_'.$template;
	$tplfile = TPL_ROOT.$type.'/'.$path.'/'."$tplmod.html";
	$compiledtplfile = TPL_CACHEPATH.$type.'/'.$templatemod.'.tpl.php';

        if(!file_exists($tplfile)) exit("在 template/$type/$path 中找不到模板文件 $tplmod.html ！");

	if(TPL_REFRESH && (!file_exists($compiledtplfile) || @filemtime($tplfile) > @filemtime($compiledtplfile)))
	{
		template_compile($tplfile,$compiledtplfile);
	}
	return $compiledtplfile;
}



function template_compile($tplfile,$compiledtplfile)
{
       $content = @file_get_contents($tplfile);
       //if($content === false) exit("$tplfile is not exists!");
	$content = template_parse($content);
	$strlen = file_put_contents($compiledtplfile, $content);
	@chmod($compiledtplfile, 0777);
	return $strlen;
}


function template_compile_admin($template,$mod='dc')
{
        $modurl = ($mod=='dc') ? ADMIN_PATH.'/' : $mod.'/admin/';
        $templatemod = ($mod ? $mod : 'dc').'_'.$template;
        $tplfile = DC_ROOT.$modurl."templates/$template.html";
        $compiledtplfile = TPL_CACHEPATH_ADMIN.$templatemod.'.tpl.php';

        $content = @file_get_contents($tplfile);
	 if($content === false) showmessage("$template is not exists!");
	$content = template_parse($content);
	$strlen = file_put_contents($compiledtplfile, $content);
	@chmod($compiledtplfile, 0777);
	return $strlen;
}

function template_compile_cp($template)
{
       global $cproot;
       $templatecp = 'cp_'.$template;
	$tplfile = $cproot."templates/$template.html";
	$compiledtplfile = TPL_CACHEPATH_ADMIN.$templatecp.'.tpl.php';

        $content = @file_get_contents($tplfile);
	 if($content === false) showmessage("$template is not exists!");
	$content = template_parse($content);
	$strlen = file_put_contents($compiledtplfile, $content);
	@chmod($compiledtplfile, 0777);
	return $strlen;
}

function template_compile_m($template)
{
       $template_m = 'm_'.$template;
	$tplfile = M_ROOT."templates/$template.html";
	$compiledtplfile = TPL_CACHEPATH_ADMIN.$template_m.'.tpl.php';

        $content = @file_get_contents($tplfile);
	 if($content === false) showmessage("$template is not exists!");
	$content = template_parse($content);
	$strlen = file_put_contents($compiledtplfile, $content);
	@chmod($compiledtplfile, 0777);
	return $strlen;
}


function template_refresh($tplfile, $compiledtplfile)
{
	$str = file_get_contents($tplfile);
	$str = template_parse($str);
	$strlen = file_put_contents($compiledtplfile, $str);
	@chmod($compiledtplfile, 0777);
	return $strlen;
}

function template_block($blockid)
{
	$tplfile = TPL_ROOT.'block/'.$blockid.'.html';
	$compiledtplfile = TPL_CACHEPATH.'block_'.$blockid.'.tpl.php';
	if(TPL_REFRESH && (!file_exists($compiledtplfile) || @filemtime($tplfile) > @filemtime($compiledtplfile)))
	{
		template_refresh($tplfile, $compiledtplfile);
	}
	return $compiledtplfile;
}



function template_parse($str, $istag = 0)
{
	$str = preg_replace("/([\n\r]+)\t+/s","\\1",$str);
	$str = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}",$str);
	$str = preg_replace("/\{atpl\s+(.+)\}/","<?php include atpl(\\1); ?>",$str);
	$str = preg_replace("/\{cptpl\s+(.+)\}/","<?php include cptpl(\\1); ?>",$str);
	$str = preg_replace("/\{mtpl\s+(.+)\}/","<?php include mtpl(\\1); ?>",$str);
	$str = preg_replace("/\{template\s+(.+)\}/","<?php include template(\\1); ?>",$str);
	$str = preg_replace("/\{include\s+(.+)\}/","<?php include \\1; ?>",$str);
       $str = preg_replace("/[\n\r\t]*\{dc:([0-9]+)\}[\n\r\t]*/ies", "dc('\\1')", $str);
       $str = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<?php \\1 ?>','')", $str);
       $str = preg_replace("/[\n\r\t]*\{php\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<?php \\1 ?>','')", $str);
	$str = preg_replace("/\{if\s+(.+?)\}/","<?php if(\\1) { ?>",$str);
	$str = preg_replace("/\{else\}/","<?php } else { ?>",$str);
	$str = preg_replace("/\{elseif\s+(.+?)\}/","<?php } elseif (\\1) { ?>",$str);
	$str = preg_replace("/\{\/if\}/","<?php } ?>",$str);
	$str = preg_replace("/\{loop\s+(\S+)\s+(\S+)\}/","<?php if(is_array(\\1)) foreach(\\1 AS \\2) { ?>",$str);
	$str = preg_replace("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/","<?php if(is_array(\\1)) foreach(\\1 AS \\2 => \\3) { ?>",$str);
	$str = preg_replace("/\{\/loop\}/","<?php } ?>",$str);
	$str = preg_replace("/\{\/get\}/","<?php } unset(\$DATA); ?>",$str);
	$str = preg_replace("/\{tag_([^}]+)\}/e", "get_tag('\\1')", $str);
	$str = preg_replace("/\{get\s+([^}]+)\}/e", "get_parse('\\1')", $str);
    
    
    $str = preg_replace ( "/\{arclist\s+([^}]+)\}/e", "param_parse('\\1','arc')", $str );
    $str = preg_replace ( "/\{\/arclist\}/", "<?php } unset(\$DATA); ?>", $str );
    $str = preg_replace ( "/\{shoplist\s+([^}]+)\}/e", "param_parse('\\1','shop')", $str );
    $str = preg_replace ( "/\{\/shoplist\}/", "<?php } unset(\$DATA); ?>", $str );
    
    
	$str = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/","<?php echo \\1;?>",$str);
	$str = preg_replace("/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/","<?php echo \\1;?>",$str);
	$str = preg_replace("/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/","<?php echo \\1;?>",$str);
	$str = preg_replace("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/es", "addquote('<?php echo \\1;?>')",$str);
	$str = preg_replace("/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>",$str);
	if(!$istag) $str = "<?php defined('IN_DC') or exit('Access Denied'); ?>".$str;
	return $str;
}



function addquote($var)
{
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

function stripvtags($expr, $statement) {
	$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
	$statement = str_replace("\\\"", "\"", $statement);
	return $expr.$statement;
}

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



/******************************************************************************************************/

function param_parse($str,$type)
{
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
       $query['arc'] = compact('typeid','shopid','areacode','cat','title','userid','username','num');
       $query['shop'] = compact('num','tids','bannedids','fids','uids','typeids','sortids','special','stick','digest','orderby','recommend','keyword','lastpost','picrequired','stamp','titlelen');
       $funcname = array('arc'=>'arclist','shop'=>'shoplist');
       
       //$paramstr = http_build_query($query[$type]);//此方法会encode所有参数，会导致$传值失效，无法循环嵌套
       $paramstr = '';
       foreach($query[$type] AS $_k=>$_v){
            $paramstr .= $_k.'='.$_v.'&';
       }
       //去掉"[]"，避免直接传入数组值而导致出错
       $paramstr = strtr($paramstr,array('['=>'',']'=>''));
       
	$str = substr($str, -1) == '/' ? "<?php \${$return} = {$funcname[$type]}(\"$paramstr\");?>" : "<?php \$DATA = {$funcname[$type]}(\"$paramstr\");if(is_array(\$DATA)) foreach(\$DATA AS \$n => \${$return}) { \$n++;?>";
	return $str;

}

//shoplist可用属性参数 citycode cats num param[elite,hot]
function shoplist($paramstr = ''){
    global $db,$curpos,$citycode;
    parse_str($paramstr);
    $_shoplistpos = $curpos;

    $num = $num ? intval($num) : 10;
    
    if($cats){
	$cats = trim($cats);
       $_cats = explode(",", $cats);
       $catsql = " (s.catcode LIKE '".implode("%' OR s.catcode LIKE '", $_cats)."%') ";
       $catsql = " AND ".$catsql;
    }
    if($param == 'elite') $where .= "AND s.elite=1 ";
    if($param == 'hot'){
    	$_shoplistpos .= ",5";
       $hotsql = " AND p.posid in(5) ";
    }
    $sql = "SELECT s.id,s.shopname,s.subname,s.thumb,s.areacode,s.status,s.content,s.url,s.discount FROM `dc_shop` s,`dc_position` p WHERE  s.id=p.shopid  AND p.posid in($_shoplistpos) $hotsql AND s.isimg = 0 AND s.status=1 $catsql AND s.areacode LIKE '$citycode%' GROUP by s.id ORDER BY  s.listorder ASC, s.id DESC LIMIT $num";
    $return = $db->select($sql);
    return $return ? $return : array();
}

//arclist可用属性参数 typeid shopid areacode cat title userid username num
function arclist($paramstr = ''){
    parse_str($paramstr);
    $c = load('content.class.php','dearcms','inc/admin');
    $num = $num ? intval($num) : 10;
        $where = "  `status`=99 ";
        if($typeid) $where .= " AND `typeid`='$typeid' ";
        if($shopid) $where .= " AND `shopid`='$shopid' ";
        if($areacode)  $where .= " AND `areacode` LIKE '%$areacode%'";
        if($cat) $where .= " AND `catcode` LIKE '%$cat%'";
        if($title) $where .= " AND `title` LIKE '%$title%'";
        
        if($userid){
            $userid = intval($userid);
            $where .= " AND `userid`=$userid";
        }
        if($username){
            $userid = userid($q);
            $where .= " AND `userid`=$userid";
        }

        $infos = $c->listinfo($where, '`listorder` DESC,`contentid` DESC', $num);

    return $infos;
}
?>