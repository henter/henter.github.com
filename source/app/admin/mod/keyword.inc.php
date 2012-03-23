<?php
defined('IN_DC') or exit('Access Denied');

$keyword = dc::loadmodel('keyword');



if(!$action) $action = 'manage';
if(!$forward) $forward = '?mod='.$mod.'&file='.$file.'&action=manage';
$submenu = array(
	array('添加标签', '?file='.$file.'&action=add'),
	array('管理标签', '?file='.$file.'&action=manage'),
);
$menu = admin_menu($modelname.'模型管理', $submenu);



switch($action)
{
    case 'add':
		if($dosubmit)
		{
			$result = $keyword->add($info);
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
			include atpl('keyword_add');
		}
		break;

    case 'edit':
		if($dosubmit)
		{
			$result = $keyword->edit($tagid, $info);
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
			$info = $keyword->get($tag);
			if(!$info) showmessage('指定的关键词不存在！');
			extract($info);
			include atpl('keyword_edit');
		}
		break;

    case 'manage':
            $infos = $keyword->listinfo('', '', $page, 20);
print_r($infos);exit;
		include atpl('keyword_manage');
		break;

    case 'listorder':
		$keyword->listorder($listorder);
		showmessage("操作成功！", $forward);
        break;

    case 'delete':
		$result = $keyword->delete($tagid);
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
		$result = $keyword->disable($tagid, $disabled);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;

    case 'select':
            $infos = $keyword->listinfo('', 'tagid', $page, 50);
		include atpl('keyword_select');
		break;
}
?>