<?php 
defined('IN_DC') or exit('Access Denied');

require_once 'admin/position.class.php';
$pos = new position();
$module = $module ? $module : 'shop';

if(!$action) $action = 'manage';
if(!$forward) $forward = '?file='.$file.'&action=manage&module=shop';

$submenu = array(
	array('添加推荐位', "?file=$file&action=add&module=$module"),
	array('管理推荐位', "?file=$file&action=manage&module=$module"),
);
$menu = admin_menu($modelname.'模型管理', $submenu);


switch($action)
{
    case 'add':
		if($dosubmit)
		{
                     $info[module] = $module;
			$posid = $pos->add($info);
			if($posid)
			{
				//$priv_role->update('posid', $posid, $roleids);
				showmessage('操作成功！', $forward);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			include atpl('position_add');
		}
		break;
    case 'edit':
		if($dosubmit)
		{
			$result = $pos->edit($posid, $info);
			if($result)
			{
				//$priv_role->update('posid', $posid, $roleids);
				showmessage('操作成功！', $forward);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			$info = $pos->get($posid);
			if(!$info) showmessage('指定的推荐位不存在！');
			extract($info);
			//$roleids = implode(',', $priv_role->get_roleid('posid', $posid));
			include atpl('position_edit');
		}
		break;
    case 'manage':
		$page = max(intval($page), 1);
		$pagesize = max(intval($pagesize), 20);
              $where = " module='$module'";
              $infos = $pos->listinfo($where, 'listorder, posid', $page, $pagesize);
		include atpl('position_manage');
		break;
    case 'delete':
              if($posid < 4) showmessage('系统推荐位，不能删除！');
		$result = $pos->delete($posid);
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
		$result = $pos->listorder($info);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;

    case 'content_add':
        if($dosubmit)
        {
            $posid = intval($posid);
            if(!$posid) showmessage('指定的专题不存在！');
            if(empty($contentid)) showmessage('请选择要插入的信息！');
            $pos->content_add($posid, $contentid);

            showmessage('操作成功!', $forward);
        }
        else
        {
            $posid = intval($posid);
            if(!$posid) showmessage('指定的推荐位不存在！');

			$where = '';
            $status = isset($status) ? intval($status) : 99;
            $where .= " `status`=$status";
            if($typeid) $where .= " AND `typeid`=0 ";
            if($areaid) $where .= " AND `areaid`='$areaid' ";
            if($inputdate_start) $where .= " AND `inputtime`>='".strtotime($inputdate_start.' 00:00:00')."'"; else $inputdate_start = date('Y-m-01');
            if($inputdate_end) $where .= " AND `inputtime`<='".strtotime($inputdate_end.' 23:59:59')."'"; else $inputdate_end = date('Y-m-d');
            if($q != '')
            {
                if($catid) $where .= " AND catid='$catid'";
                if($field == 'title')
                {
                    $where .= " AND `title` LIKE '%$q%'";
                }
                elseif($field == 'userid')
                {
					$userid = userid($q);
                    if($userid) $where .= " AND `userid`=$userid";
                }
                elseif($field == 'contentid')
                {
                    $where .= " AND `contentid`='$q'";
                }
            }

			$infos = $pos->content_select($where, $page, 20);
            $pagetitle = '添加推荐位信息';

            include atpl('position_content_add');
        }
        break;

        case 'content_manage':
            if($dosubmit)
            {
                if(!$posid) showmessage('参数非法！');
                if(empty($contentid)) showmessage('请选择要删除的信息！');
				$pos->content_delete($posid, $contentid);
                showmessage('操作成功！', $forward);
            }
            else
            {
                $posid = intval($posid);
				$r = $pos->get($posid);
				if(!$r) showmessage('指定的推荐位不存在！');
				extract($r);
                $infos = $pos->content_list($posid, $page, 20);
                include atpl('position_content_manage');
            }
            break;

    default :
		$page = max(intval($page), 1);
		$pagesize = max(intval($pagesize), 20);
        $infos = $pos->listinfo('', 'listorder, posid', $page, $pagesize);
		include atpl('position_list');
}
?>