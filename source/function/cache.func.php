<?php 
function cache_all()
{
	@set_time_limit(600);
	cache_common();
	cache_module();
	cache_block();
	cache_model();
       cache_menu();
	cache_area();
	cache_marea();
	cache_cat();
	cache_type();
	cache_pos();
	cache_keyword();
	cache_member_group();
	cache_areaconfig();
	return TRUE;
}

function cache_common()
{
	global $db;
	$data = array();
	$result = $db->query("SELECT `module`,`name`,`path`,`url`,`iscore`,`version` FROM `".DB_PRE."module` WHERE `disabled`=0");
	while($r = $db->fetch_array($result))
	{
		if(!$r['path']) $r['path'] = $r['module'] == 'dearcms' ? '' : $r['module'].'/';
		if(!$r['url']) $r['url'] = $r['module'] == 'dearcms' ? '' : $r['module'].'/';
		$data[$r['module']] = $r;
	}
	$db->free_result($result);
	$CACHE['MODULE'] = $data;


	$data = array();
	$result = $db->query("SELECT * FROM `".DB_PRE."model` WHERE `disabled`=0");
	while($r = $db->fetch_array($result))
	{
		$data[$r['modelid']] = $r;
	}
	$db->free_result($result);
	$CACHE['MODEL'] = $data;


	$data = array();
	$result = $db->query("SELECT `typeid`,`modelid`,`module`,`name`,`style`,`typedir`,`url` FROM `".DB_PRE."type` WHERE 1 ORDER BY `listorder`,`typeid`");
	while($r = $db->fetch_array($result))
	{
		$data[$r['typeid']] = $r;
	}
	$db->free_result($result);
	$CACHE['TYPE'] = $data;


	$data = array();
	$result = $db->query("SELECT * FROM `".DB_PRE."admin_role` WHERE 1 ORDER BY `id`");
	while($r = $db->fetch_array($result))
	{
		$data[$r['id']] = $r;
	}
	$db->free_result($result);
	$CACHE['ROLE'] = $data;

        $data = array();
        $r = $db->get_one("SELECT `setting` FROM `".DB_PRE."module` WHERE `module`='dearcms'");
        $setting = $r['setting'];
        eval("\$DC = $setting;");
        if($DC['siteurl'] =='') $DC['siteurl'] = SITE_URL;
        $CACHE['DC'] = $DC;
        cache_write('common.php', $CACHE);
    return $CACHE;
}


function cache_block()
{
	global $db;
	$data = array();
	$result = $db->query("SELECT `blockid`,`pageid`,`name`,`disabled` FROM `".DB_PRE."block` WHERE `disabled`=0");
	while($r = $db->fetch_array($result))
	{
		$data[$r['pageid']] = $r;
	}
	$db->free_result($result);
    cache_write('block.php', $data);
}


function cache_module()
{
	global $db;
	$data = array();
	$result = $db->query("SELECT `module`,`name`,`path`,`url`,`iscore`,`version`,`publishdate`,`installdate`,`updatedate`,`setting` FROM `".DB_PRE."module` WHERE `disabled`=0");
	while($r = $db->fetch_array($result))
	{
		if(!$r['path']) $r['path'] = $r['module'] == 'dc' ? '' : $r['module'].'/';
		if(!$r['url'])
		{
			$r['url'] = $r['module'] == 'dc' ? '' : $r['module'].'/';
			$db->query("UPDATE `".DB_PRE."module` SET `url`='$r[url]' WHERE module='$r[module]' LIMIT 1");
		}

		if($r['setting'])
		{
			$setting = $r['setting'];
			eval("\$setting = $setting;"); 
			unset($r['setting']);
			if(is_array($setting)) $r = array_merge($r, $setting);
        }
		cache_write('module_'.$r['module'].'.php', $r);
	}
	$db->free_result($result);
}

function cache_model()
{
	cache_table(DB_PRE.'model', '*', '', '', 'modelid', 1);
}
function cache_keyword()
{
	cache_table(DB_PRE.'tags', '*', 'tag', '', 'listorder,usetimes', 0, 100);
}

function cache_type()
{
	cache_table(DB_PRE.'type', '*', '', '', 'listorder,typeid', 1);
}

function cache_pos($where = '', $order = 'listorder,posid')
{
	cache_table(DB_PRE.'pos', '*', 'name', '', 'listorder,posid', 0);
}

function cache_member_group()
{
	cache_table(DB_PRE.'member_group', '*', '', '', 'groupid', 1);
	cache_table(DB_PRE.'member_group', '*', 'name', '', 'groupid', 0);
}



function cache_table($table, $fields = '*', $valfield = '', $where = '', $order = '', $iscacheline = 0, $number = 0)
{
	global $db;
	$keyfield = $db->get_primary($table);
	$data = array();
	if($where) $where = " WHERE $where";
	if(!$order) $order = $keyfield;
	$limit = $number ? "LIMIT 0,$number" : '';
	$result = $db->query("SELECT $fields FROM `$table` $where ORDER BY $order $limit");
	$table = preg_replace("/^".DB_PRE."(.*)$/", "\\1", $table);
	while($r = $db->fetch_array($result))
	{
		if(isset($r['setting']) && !empty($r['setting']))
		{
			$setting = $r['setting'];
			eval("\$setting = $setting;"); 
			unset($r['setting']);
			if(is_array($setting)) $r = array_merge($r, $setting);
                }
		$key = $r[$keyfield];
		$value = $valfield ? $r[$valfield] : $r;
		$data[$key] = $value;
		if($iscacheline) cache_write($table.'_'.$key.'.php', $value);
	}
	$db->free_result($result);
	cache_write($table.'.php', $data);
}

function cache_menu()
{
	cache_table(DB_PRE.'menu', '*', '', '', 'listorder,menuid', 0, 100);
}

function cache_marea()
{
	cache_table(DB_PRE.'marketarea', '*', '', '', 'listorder,id', 0);
}


function cache_area()
{
    global $db;
    $areas = $db->select("SELECT * FROM `".DB_PRE."area` ORDER BY listorder,aid");

    foreach($areas AS $k=>$v){
        $array[$v[areacode]] = $v;
        //if($v[status]) $array[$v[areacode]] = $v; //只缓存已开通的地区
    }

    cache_write('area.php', $array);
}


function cache_cat()
{
    global $db;
    $cats = $db->select("SELECT * FROM `".DB_PRE."cat` ORDER BY listorder,cid");

    foreach($cats AS $k=>$v){
        $array[$v[catcode]] = $v;
    }

    cache_write('cat.php', $array);
}

function cache_areaconfig(){
    global $DC;
    $siteurl = $DC[siteurl];

    $data .= "var dc_path = '".DC_PATH."';\n";
    $data .= "var cookie_pre = '".COOKIE_PRE."';\n";
    $data .= "var cookie_domain = '".COOKIE_DOMAIN."';\n";
    $data .= "var cookie_path = '".COOKIE_PATH."';\n";
    $data .= "var siteurl = '$siteurl';\n";

    cache_write_str('config.js', $data , DC_ROOT."data/");
}


?>