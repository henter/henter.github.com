<?php
defined('IN_DC') or exit('Access Denied');
require_once 'admin/model.class.php';
require_once 'admin/model_field.class.php';

require_once 'admin/content.class.php';
require_once 'attachment.class.php';

$c = new content();

$STATUS=array(99=>'正常',0=>'待审核');

$submenu  = array();
$submenu[] = array('<font color="red">发布信息</font>', '?file=cat&add=content');
$submenu[] = array('所有通过信息', '?mod='.$mod.'&file='.$file.'&action=manage');
$r = $db->get_one("SELECT `contentid` FROM `".DB_PRE."content` WHERE status!=99 AND status!=0 LIMIT 1");

//if($r) $submenu[] = array('<font color="#ff0000">审核信息</font>', '?mod='.$mod.'&file='.$file.'&action=manage&status=1000');

$submenu[] = array('回收站', '?mod='.$mod.'&file='.$file.'&action=recycle&status=0');
//$submenu[] = array('我发布的信息', '?mod='.$mod.'&file='.$file.'&action=my');
//$submenu[] = array('搜索', '?mod='.$mod.'&file='.$file.'&action=search');

$menu = admin_menu('管理信息', $submenu);
$attachment = new attachment($mod, 0);

switch($action)
{
	case 'search':
		if($dosubmit)
		{
			require CACHE_MODEL_PATH.'content_search.class.php';
			$content_search = new content_search();
			$infos = $content_search->data($page, 20);
			include atpl('content_search_list');
		}
		else
		{
			require CACHE_MODEL_PATH.'content_search_form.class.php';
			$content_search_form = new content_search_form();
			$forminfos = $content_search_form->get_where();
			$orderfields = $content_search_form->get_order();

                    $pagetitle = $CATEGORY[$catid]['catname'].'-搜索';
			include atpl('content_search');
		}
		break;

	case 'my':
		$userid = $userid ? intval($userid) : $_userid;
		$where = " `userid` = '$userid'";
	    $status = isset($status) ? intval($status) : -1;
	    if($status != -1) $where .= " AND `status`='$status'";
            $page = max(intval($page), 1);
		$infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
		$pagetitle = '我的信息-管理';
		include atpl('content_my');
		break;

	 case 'pass':
		if($contentid=='') showmessage('请选择要批准的内容');
		$c->status($contentid, 99, 1);
		showmessage('操作成功！', $forward);
		break;

	case 'listorder':
		$result = $c->listorder($listorders);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;

	case 'move':
		if($dosubmit)
		{
			if($targetcatid=='') showmessage('目标栏目不能为空',$forward);
			if(!$fromtype)
			{
				if(empty($contentid)) showmessage('指定的ID不能为空');
				if(!preg_match("/^[0-9]+(,[0-9]+)*$/",$contentid)) showmessage($LANG['illegal_parameters']);
				$c->move($contentid,$targetcatid,$fromtype);
			}
			else
			{
				if(in_array($targetcatid,$batchcatid)) showmessage('源栏目和目标栏目不能相同', $forward);
				if($CATEGORY[$targetcatid]['child']) showmessage('目标栏目含有子栏目，不允许添加信息', $forward);
				$c->move($batchcatid,$targetcatid,$fromtype);
			}
			showmessage($LANG['operation_success'], $forward);
		}
		else
		{
			$modelid = $CATEGORY[$catid]['modelid'];
			foreach($CATEGORY as $id=>$cat)
			{
				if($cat['modelid'] == $modelid) $subcat[$id] = $cat;
			}
			$CATEGORY = $subcat;
			$contentid = is_array($contentid) ? implode(',',$contentid) : $contentid;
			$category_select = form::select_category($mod, 0,'catid','catid','',$catid);
			$category_select = str_replace(array("<select name='catid' id='catid' >","<option value='0'></option>"),'',$category_select);
			include atpl('content_move');
		}
	break;

	case 'cancel':
		$c->status($contentid, 0);
		showmessage('操作成功！', $forward);
		break;

	case 'delete':
		$c->delete($contentid);
		showmessage('操作成功！', $forward);
		break;

	case 'inspect':
		if($dosubmit)
		{
			if($contentid=='') showmessage('请选择要批准的内容');
			if($pass)
			{
				$passname = 'passstatus';
			}
			else
			{
				$passname = 'rejectstatus';
			}
			$c->inspect($contentid, $passname);
		}
		$STATU = $c->get_pro_status();
		$t = $catids = '';
		$submenu = array();
		foreach($STATU as $s)
		{
			$submenu[] = array($s['name'], '?mod='.$mod.'&file='.$file.'&action=inspect&status='.$s['status']);
		}
		$menu = admin_menu('信息审核', $submenu);
		foreach($CATEGORY AS $catid => $CATE)
		{
			if($priv_role->check('catid', $catid, 'check'))
			{
				$catids .= $t.$catid;
				$t = ',';
			}
		}
		if(!$catids) showmessage('您的账号没有管理栏目的权限');
		$status = $status ? intval($status) : 3;
		$where = " `catid` IN ($catids) AND `status`=".$status;
		$infos = $c->listinfo($where, '`listorder` DESC,`contentid` DESC', $page, 20);
		$pagetitle = '全部信息-审核';
		include atpl('content_inspect');
		break;

	default:
		$status = isset($status) ? intval($status) : 99;
	    if($status == 1000)
		{
			$where .= " `status`!=99 AND `status`!=0";
		}
		else
		{
			$where .= " `status`=$status";
		}
	    if($typeid) $where .= " AND `typeid`=0 ";
	    if($areacode) $where .= " AND  `areacode` LIKE '$areacode%'";
	    if($catcode) $where .= " AND  `catcode` LIKE '$catcode%'";
	    if($inputdate_start) $where .= " AND `inputtime`>='".strtotime($inputdate_start.' 00:00:00')."'"; else $inputdate_start = date('Y-m-01');
	    if($inputdate_end) $where .= " AND `inputtime`<='".strtotime($inputdate_end.' 23:59:59')."'"; else $inputdate_end = date('Y-m-d');
		if($q != '')
	    {
			if($field == 'title')
			{
				$where .= " AND `title` LIKE '%$q%'";
			}
			elseif($field == 'userid')
			{
				$userid = intval($q);
				if($userid)	$where .= " AND `userid`=$userid";
			}
			elseif($field == 'username')
			{
				$userid = userid($q);
				if($userid)	$where .= " AND `userid`=$userid";
			}
			elseif($field == 'contentid')
			{
				$contentid = intval($q);
				if($contentid) $where .= " AND `contentid`=$contentid";
			}
		}
        $infos = $c->listinfo($where, '`listorder` DESC,`contentid` DESC', $page, 20);

        $pagetitle = '全部信息-管理';
		include atpl('content_all');
}
?>