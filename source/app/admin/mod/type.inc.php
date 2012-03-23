<?php 
defined('IN_DC') or exit('Access Denied');

require_once 'admin/type.class.php';
if(!isset($module)) $module = 'dearcms';
$type = new type($module);

if(!$action) $action = 'manage';
if(!$forward) $forward = "?file=$file&action=mange";

$submenu = array(
	array('添加类别', '?file='.$file.'&action=add'),
	array('管理类别', '?file='.$file.'&action=manage'),
);
$menu = admin_menu($modelname.'类别管理', $submenu);

switch($action)
{
    case 'add':
		if($dosubmit)
		{
                    $info[name] or showmessage("请填写类别名称");
			$result = $type->add($info);
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
			$models = '<select name="info[modelid]" id="modelid">';
			foreach($MODEL as $mid => $m)
			{
				$models .= '<option value="'.$mid.'">'.$m['name'].'</option>';
			}
			$models .= '</select>';
			include atpl('type_add');
		}
		break;
    case 'edit':
		if($dosubmit)
		{
                    $info[name] or showmessage("请填写类别名称");
			$result = $type->edit($typeid, $info);
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
			$info = $type->get($typeid);
			if(!$info) showmessage('指定的类别不存在！');
			extract($info);
			$models = '<select name="info[modelid]" id="modelid">';
			foreach($MODEL as $mid => $m)
			{
				if($mid==$modelid)
				{
					$t = 'selected';
				}
				else
				{
					$t = '';
				}
				$models .= '<option value="'.$mid.'" '.$t.'>'.$m['name'].'</option>';
			}
			$models .= '</select>';
			include atpl('type_edit');
		}
		break;
    case 'manage':
		$page = max(intval($page), 1);
		$pagesize = max(intval($pagesize), 20);
              $infos = $type->listinfo($page, $pagesize);
		include atpl('type_manage');
		break;
    case 'delete':
		$result = $type->delete($typeid);
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
		$result = $type->listorder($info);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;
}
?>