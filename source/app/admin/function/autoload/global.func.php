<?php
function gettestnum($catid){
    global $db;
    $num = $db->get_one("SELECT COUNT(*) AS num FROM `".DB_PRE."test` WHERE `catid`=$catid");
    return $num[num];
}
    
function set_config($config)
{
	if(!is_array($config)) return FALSE;
	$configfile = DC_ROOT.'/inc/config.inc.php';
	if(!is_writable($configfile)) showmessage('Please chmod ./inc/config.inc.php to 0777 !');
	$pattern = $replacement = array();
	foreach($config as $k=>$v)
	{
            $pattern[$k] = "/define\(\s*['\"]".strtoupper($k)."['\"]\s*,\s*([']?)[^']*([']?)\s*\)/is";
            $replacement[$k] = "define('".$k."', \${1}".$v."\${2})";
	}
	$str = file_get_contents($configfile);
	$str = preg_replace($pattern, $replacement, $str);
	return file_put_contents($configfile, $str);
}

function module_setting($module, $setting)
{
	global $db,$MODULE;
	if(!is_array($setting) || !array_key_exists($module, $MODULE)) return FALSE;
	if(isset($setting['url']))
	{
		$url = $setting['url'];
		if($setting['url'] && substr($url, -1) != '/')
		{
			$url .= '/';
		}
            $db->query("UPDATE ".DB_PRE."module SET url='$url' WHERE module='$module'");
            unset($setting['url']);
	}
	$setting = array2string($setting);
       $db->query("UPDATE ".DB_PRE."module SET setting='$setting' WHERE module='$module'");
	cache_module();
	cache_common();
	return TRUE;
}

function filter_write($filter_word)
{
	$filter_word = array_map('trim', explode("\n", str_replace('*', '.*', $filter_word)));
    return cache_write('filter_word.php', $filter_word);
}

function file_select($textid, $catcode = 0, $isimage = 0)
{
	return "<input type='button' value='浏览...' style='cursor:pointer;' onclick=\"file_select('$textid', $catcode, $isimage)\">";
}

function ip_access($ip, $accesslist)
{
	$regx = str_replace(array("\r\n", "\n", ' '), array('|', '|', ''), preg_quote($accesslist, '/'));
	return preg_match("/^".$regx."/", $ip) ? false : true;
}


function admin_menu($menuname, $submenu = array())
{
    global $mod,$file,$action;
    $menu = $s = '';echo $flag;
    foreach($submenu as $m)
	{
		$B1 = $B2 = '';	
		if($m[3] && $m[4] && $m[3]==$m[4]) 
		{
			$B1 = '<b>';
			$B2 = '</b>';
		}
		$title = isset($m[2]) ? "title='".$m[2]."'" : "";
		$menu .= $s."<a href='".$m[1]."' ".$title.">".$B1.$m[0].$B2."</a>";
        $s = ' | ';
	}
    //ob_start();
    //include atpl('admin_menu');
    //$data = ob_get_contents();
    //ob_clean();
    $data = "
    <div class='space'>
    <div class='subtitle'>$menuname</div>
    <table class='maintable' border='0' cellspacing='0' cellpadding='0'>
      <tr class='altbg1'>
        <td>$menu</td>
      </tr>
    </table>
</div>
    ";
    return $data;
}
?>