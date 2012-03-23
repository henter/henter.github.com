<?php
defined('IN_ADMIN') or exit('Access Denied');

$parentid = gpc('parentid');
$action = gpc('action');
$m = dc::loadclass('menu');
$MENU = cache_read('menu.php');

if(!$action) $action = 'manage';
if(!$forward) $forward = "?mod=$mod&file=$file&action=manage";


switch ($action){
    case 'add':
		if($dosubmit)
		{
			$menuid = $m->add($info);
       cache_menu();
			if($menuid)
			{
				showmessage('操作成功！', $forward);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			if(!isset($parentid)) $parentid = 0;
			if(!isset($target)) $target = 'right';
			$tpl = 'menu_add';
		}
		break;

    case 'edit':
		if($dosubmit)
		{
		    $info['roleids'] = implode(',', $roleids);
			$result = $m->edit($menuid, $info);
       cache_menu();
			if($result)
			{
				showmessage('操作成功！', $forward);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			$info = $m->get($menuid);
			if(!$info) showmessage('指定的菜单不存在！');
			extract($info);
			if($parentid)
			{
				$parent = $m->get($parentid);
				$parentname = $parent['name'];
			}
			else
			{
				$parentname = '无';
			}
			$tpl = 'menu_edit';
		}
		break;

    case 'manage':
            if(!isset($parentid)) $parentid = 0;
            if($parentid)
            {
			$r = $m->get($parentid);
			$parentname = $r['name'];
            }
            $forward = URL;
            $where = "`parentid` = '$parentid'";
            $infos = $m->listinfo($where, 'listorder, menuid', $page, 20);
            $tpl = 'menu_manage';
            break;

    case 'delete':
		$result = $m->delete($menuid);
       cache_menu();
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;

    case 'listorder':
		$result = $m->listorder($listorder);
       cache_menu();
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;

    case 'disable':
       cache_menu();
		$result = $m->disable($menuid, $disabled);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;

	case 'getchild':
		$array = array();
        $infos = $m->listinfo("parentid='$parentid'", 'listorder, menuid', $page, 20);
	    foreach($infos as $k=>$v)
	    {
			$array[$v['menuid']] = $v['name'];
	    }
		if(!$parentid || $array)
	    {
			$array[0] = $parentid ? '请选择' : '无';
			ksort($array);
			echo form::select($array, 'setparentid', 'setparentid', $parentid, 1, '', 'onchange="if(this.value>0){getchild(this.value);myform.parentid.value=this.value;this.disabled=true;}"');
		}
        exit;
		break;

      case 'add_mymenu':
            if(!isset($target) || empty($target)) $target = 'right';
            if(CHARSET != 'utf-8') $name = iconv('utf-8', DB_CHARSET, $name);
            $info = array('parentid'=>'99', 'name'=>$name, 'url'=>urldecode($url), 'target'=>$target);
            echo $m->add($info) ? 1 : 0;
            exit;
		break;

	case 'get_menu_list':
		$data = $m->get_child($menuid);
		$data = str_charset(CHARSET, 'utf-8', $data);
		$max = array_slice($data, -1);
		$data['max'] = $max[0]['menuid'];
		$data = json_encode($data);
		if(PHP_OS < 5.0) header('Content-type: text/html; charset=utf-8');
		echo $data;
              exit;
	break;

	case 'left' :
            $parentid = $parentid ? $parentid : 1;
            $tpl='left';
	break;

	case 'goto' :
            global $_rolepowers;
            //if(!check_in($menuid, $_rolepowers)) showmessage('对不起，你没有权限进行此操作！');

            $info = $m->get($menuid);
            $url = $info[url];
            if(!$url) $url = '?file=home';
            //if(substr($url,0,9) != 'admin.php') $url = 'admin.php'.$url;
            Header("Location: $url");
	break;
}

include atpl($tpl);
?>