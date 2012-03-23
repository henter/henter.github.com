<?php
defined('IN_DC') or exit('Access Denied');

require_once 'admin/content.class.php';
require_once 'attachment.class.php';
$c = new content();

if(is_numeric($contentid) && $contentid>0)
{
	$data = $c->get($contentid);
	$catcode = $data['catcode'];
	$shopid = $data['shopid'];
}

$modelid = $CAT[$catcode]['modelid'];
$modelid = $modelid ? $modelid : 1;

if($type == 2)
{
	if($action == 'manage') $action = 'link';
}
elseif($type == 1)
{
	if($action == 'manage') $action = 'block';
}
else
{


	$attachment = new attachment('dc', $catcode);

	$submenu = array();

		$submenu[] = array('<font color="red">发布信息</font>', '?file=content&action=catselect');
		//$submenu[] = array('我发布的信息', '?file='.$file.'&action=my');
		//$submenu[] = array('管理', '?file='.$file.'&action=manage');
		//$submenu[] = array('回收站', '?file='.$file.'&action=recycle');
		//$submenu[] = array('广告', '?file='.$file.'&action=block');

	//$submenu[] = array('搜索', '?file='.$file.'&action=search');
		$submenu[] = array('所有内容管理', "?file=content_all");
	$menu = admin_menu($CAT[$catcode]['catname'].' 信息管理', $submenu);

}

switch($action)
{
    
    case 'catselect':

			include atpl('content_catselect');
		break;
        
        
    case 'add':
		if($dosubmit)
		{
			$info['status'] = ($status == 2 || $status == 3) ? $status : ($allow_manage ? 99 : 3);
			$info['status'] = 99;
                     $info['shopid'] = $shopid ? $shopid : 0;
			if(isset($info['inputtime'])) $info['updatetime'] = $info['inputtime'];

			$contentid = $c->add($info,$cat_selected);
			if($contentid) showmessage('发布成功！', '?file=content&shopid='.$info[shopid]);
		}
		else
		{
			$data['catcode'] = $catcode;

			require CACHE_MODEL_PATH.'content_form.class.php';
			$content_form = new content_form($modelid);
			$forminfos = $content_form->get($data);

			include atpl('content_add');
		}
		break;

    case 'edit':
		if($dosubmit)
		{
			$info['status'] = ($status == 2 || $status == 3) ? $status : 99;
			$info['status'] = 99;
			$c->edit($contentid, $info);

			showmessage('修改成功！', $forward);
		}
		else
		{
			require CACHE_MODEL_PATH.'content_form.class.php';
			$content_form = new content_form($modelid);
			$forminfos = $content_form->get($data);
			include atpl('content_edit');
		}
		break;

	case 'view':
		//if(!$priv_role->check('catcode', $catcode, 'view') && !$allow_manage) showmessage('无查看权限！');

		require_once CACHE_MODEL_PATH.'content_output.class.php';
		$coutput = new content_output();
		$info = $coutput->get($data);

		include atpl('content_view');
		break;

	case 'log_list':
		$ACTION = array('add'=>'发布', 'edit'=>'修改', 'delete'=>'删除');
	    $content = $c->get($contentid);
		extract($content);
	    $log->set('contentid', $contentid);
		$data = $log->listinfo($where, $page, 20);
		include atpl('content_log');
	    break;

    case 'my':
		if(!$allow_add) showmessage('无发布权限！');
		$c->set_userid($_userid);
	    $status = isset($status) ? intval($status) : -1;
		$where = "`catcode`=$catcode ";
	    if($status != -1) $where .= " AND `status`='$status'";
        $infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
		$pagetitle = '我的信息-管理';
		include atpl('content_my');
		break;

    case 'my_contribute':
		$c->set_userid($_userid);
	    $contentid = $c->contentid($contentid, array(0, 1, 2));
		$c->status($contentid, 3);
		showmessage('操作成功！', $forward);
		break;

    case 'my_cancelcontribute':
		$c->set_userid($_userid);
	    $contentid = $c->contentid($contentid, array(3));
		$c->status($contentid, 2);
		showmessage('操作成功！', $forward);
		break;

    case 'my_edit':
		$c->set_userid($_userid);
	    $contentid = $c->contentid($contentid, array(0, 1, 2, 3));

		if($dosubmit)
		{
			$c->edit($contentid, $info);
			showmessage('修改成功！', $forward);
		}
		else
		{
			require CACHE_MODEL_PATH.'content_form.class.php';
			$content_form = new content_form($modelid);
			$forminfos = $content_form->get($data);

			include atpl('content_edit');
		}
		break;

    case 'my_delete':
		$c->set_userid($_userid);
	    $contentid = $c->contentid($contentid, array(0, 1, 2, 3));
		$c->delete($contentid);
		showmessage('操作成功！', $forward);
		break;

	case 'my_view':
		$c->set_userid($_userid);
	    $contentid = $c->contentid($contentid, array(0, 1, 2, 3));

		require_once CACHE_MODEL_PATH.'content_output.class.php';
		$coutput = new content_output();
		$info = $coutput->get($data);

		include atpl('content_view');
		break;

	case 'check':
		$allow_status = $p->get_process_status($processid);
		if(!isset($status) || !in_array($status, $allow_status)) $status = -1;
		$where = "`catcode`=$catcode ";
		$where .= $status == -1 ? " AND `status` IN(".implode(',', $allow_status).")" : " AND `status`='$status'";
            $infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
		$process = $p->get($processid, 'passname,passstatus,rejectname,rejectstatus');
		extract($process);

            $pagetitle = $CAT[$catcode]['catname'].'-审核';
		include atpl('content_check');
		break;
	
	case 'check_title':
		//if(CHARSET=='gbk') $c_title = iconv('utf-8', 'gbk', $c_title);
		if($c->get_contentid($c_title))
		{	
			echo '此标题已存在！';
		}
		else
		{
			echo '标题不存在！';
		}
		break;

    case 'browse':
		$where = "`catcode`=$catcode AND `status`=99";
        $infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
		include atpl('content_browse');
		break;

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

            $pagetitle = $CAT[$catcode]['catname'].'-搜索';
			include atpl('content_search');
		}
		break;

    case 'recycle':
		//if(!$allow_manage) showmessage('无管理权限！');
        $infos = $c->listinfo("catcode=$catcode AND status=0", 'listorder DESC,contentid DESC', $page, 20);

        $pagetitle = $CAT[$catcode]['catname'].'-回收站';
		include atpl('content_recycle');
		break;

    case 'pass':
		if(!$priv_role->check('catcode', $catcode, 'check') && !$allow_manage) showmessage('无审核权限！');
		$allow_status = $p->get_process_status($processid);
		if($contentid=='') showmessage('请选择要批准的内容');
	    $contentid = $c->contentid($contentid, 0, $allow_status);
		$process = $p->get($processid, 'passstatus');
		$c->status($contentid, $process['passstatus']);
		showmessage('操作成功！', $forward);
		break;

    case 'reject':
		if(!$priv_role->check('catcode', $catcode, 'check') && !$allow_manage) showmessage('无审核权限！');
		$allow_status = $p->get_process_status($processid);
		if($contentid=='') showmessage('请选择要批准的内容');
	    $contentid = $c->contentid($contentid, 0, $allow_status);
		$process = $p->get($processid, 'rejectstatus');
		$c->status($contentid, $process['rejectstatus']);
		showmessage('操作成功！', $forward);
		break;

	case 'cancel':
		//if(!$allow_manage) showmessage('无管理权限！');
		$c->status($contentid, 0);
		showmessage('操作成功！', $forward);
		break;

    case 'delete':
		//if(!$allow_manage) showmessage('无管理权限！');
        echo $contentid;exit;
		$c->delete($contentid);
		showmessage('操作成功！', $forward);
		break;

    case 'clear':
		//if(!$allow_manage) showmessage('无管理权限！');
		$c->clear();
		showmessage('操作成功！', $forward);
		break;

    case 'restore':
		//if(!$allow_manage) showmessage('无管理权限！');
		$c->restore($contentid);
		showmessage('操作成功！', $forward);
		break;

    case 'restoreall':
		//if(!$allow_manage) showmessage('无管理权限！');
		$c->restoreall();
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



	case 'posid':
		if(!$posid) showmessage('不存在此推荐位！');
		if(!$contentid) showmessage('没有被推荐的信息！');
		if(!$priv_role->check('posid', $posid)) showmessage('您没有此推荐位的权限！');
		foreach($contentid as $cid)
		{
			if($c->get_posid($cid, $posid)) continue;
			$c->add_posid($cid, $posid);
		}
		showmessage('批量推荐成功！', '?mod='.$mod.'&file='.$file.'&action=manage&catcode='.$catcode);
		break;

	case 'typeid':
		if(!$typeid) showmessage('不存在此类别！');
		if(!$contentid) showmessage('没有信息被选中！');
		foreach($contentid as $cid)
		{
			$c->add_typeid($cid, $typeid);
		}
		showmessage('批量加入类别到成功！', '?mod='.$mod.'&file='.$file.'&action=manage&catcode='.$catcode);
		break;

	default:
	     require_once 'admin/model_field.class.php';
            $model_field = new model_field($modelid);

	    $where = "  `status`=99 ";

	    if($typeid) $where .= " AND `typeid`='$typeid' ";
	    if($shopid) $where .= " AND `shopid`='$shopid' ";
	    if($areacode) $where .= " AND  `areacode` LIKE '$areacode%' ";
	    if($catcode) $where .= " AND  `catcode` LIKE '$catcode%' ";
	    if($inputdate_start) $where .= " AND `inputtime`>='".strtotime($inputdate_start.' 00:00:00')."'"; else $inputdate_start = date('Y-m-01');
	    if($inputdate_end) $where .= " AND `inputtime`<='".strtotime($inputdate_end.' 23:59:59')."'"; else $inputdate_end = date('Y-m-d');
		if($q)
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

        $pagetitle = $CAT[$catcode]['catname'].'-管理';
/*
		foreach($POS AS $key => $p)
		{
			if($priv_role->check('posid', $key))
			{
				$POSID[$key] = $p;
			}
		}
		$POS = $POSID;
		$POS[0] = '不限推荐位';
*/
		include atpl('content_manage');
}
?>