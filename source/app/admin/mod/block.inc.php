<?php
defined('IN_DC') or exit('Access Denied');
require_once 'block.class.php';
$block = new block();

if(!$forward) $forward = "?file=$file";

if($_roleid==4){
    $submenu = array(array('添加碎片', '?file='.$file.'&action=add'),array('管理碎片', '?file='.$file),array('刷新所有碎片', '?file='.$file.'&action=refresh'),);
}else{
    $submenu = array(array('管理碎片', '?file='.$file),array('刷新所有碎片', '?file='.$file.'&action=refresh'),);
}
$menu = admin_menu($modelname.'碎片管理', $submenu);


switch($action)
{
    case 'add':
              if($_roleid!=4) showmessage("禁止越权操作！");
		if($dosubmit)
		{
                    if($block->checkpageid($info[pageid]) == false) showmessage('该网页标识已经存在！');
			$blockid = $block->add($info);
			if($blockid)
			{
	                    cache_block();
				header('location:?file='.$file.'&action=adddata&func=add_block&blockid='.$blockid.'&forward='.urlencode($forward));
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			$tpl = 'block_add';
			include atpl($tpl);
		}
		break;
        
        
    case 'edit':
              if($_roleid!=4) showmessage("禁止越权操作！");
		if($dosubmit)
		{
			$result = $block->edit($blockid, $info);
			if($result)
			{
	                    cache_block();
				//header('location:?file='.$file.'&action=datalist&func=add_block&blockid='.$blockid.'&forward='.urlencode($forward));
                            showmessage('修改成功！',"?file=$file");
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			$info = $block->get($blockid);
			if(!$info) showmessage('指定的碎片不存在！');
			@extract($info);
			include atpl('block_edit');
		}
		break;



    case 'adddata':
		$r = $block->get($blockid);
		if(!$r) showmessage('指定的碎片不存在！');

		if($dosubmit)
		{
                            $info['data'] = $data;
                            $areacode = $type ? 0 : $areacode;
                            $checkareacode = $block->checkareacode($blockid);
                            if($checkareacode && !$areacode) showmessage("请选择地区！");
                            if(!$type && !isset($areacode)) showmessage('必须选择地区！');
				//if(is_array($data)) $block->set_template($blockid, $template);
				$result = $block->adddata($blockid, $info, $areacode);
				if($result){
	                            cache_block();
					showmessage('操作成功！', $forward);
				}else{
					showmessage('操作失败！');
				}
		}
		else
		{
		      extract($r);
		      $rdata = $block->get_data($blockid,$areacode);
                    extract($rdata);
    	            $checkareacode = $block->checkareacode($blockid);//检查全国显示的信息是否已经存在
                    
			$template = $block->get_template($blockid);
			$actions = array('add'=>'添加', 'edit'=>'修改', 'update'=>'更新','delete'=>'删除','post'=>'更新');
                    if(!str_exists($forward, '?')) $forward = URL;
			$tpl = 'block_adddata';
			include atpl($tpl);
		}
		break;


    case 'editdata':
		$r = $block->get($blockid);
		if(!$r) showmessage('指定的碎片不存在！');

		if($dosubmit)
		{
                            $info['data'] = $data;
                            $areacode = $type ? 0 : $areacode;
                            if(!$type && !isset($areacode)) showmessage('必须选择地区！');
				$result = $block->editdata($blockid, $info, $areacode);
				if($result){
	                            cache_block();
					showmessage('操作成功！', $forward);
				}else{
					showmessage('操作失败！');
				}
		}
		else
		{
		      extract($r);
		      $rdata = $block->get_data($blockid,$areacode);
                    extract($rdata);
    	            $checkareacode = $block->checkareacode($blockid);//检查全国显示的信息是否已经存在
                    
			$template = $block->get_template($blockid);
			$actions = array('add'=>'添加', 'edit'=>'修改', 'update'=>'更新','delete'=>'删除','post'=>'更新');
                    if(!str_exists($forward, '?')) $forward = URL;
			$tpl = 'block_editdata';
			include atpl($tpl);
		}
		break;


    case 'delete':
              if($_roleid!=4) showmessage("禁止越权操作！");
		$result = $block->delete($blockid);
		if($result)
		{
	            cache_block();
			showmessage('操作成功！');
		}
		else
		{
			showmessage('操作失败！');
		}
		break;
        
    case 'deletedata':
		$result = $block->deletedata($blockid,$areacode);
		if($result)
		{
	            cache_block();
			showmessage('操作成功！');
		}
		else
		{
			showmessage('操作失败！');
		}
		break;


    case 'disable':
		$result = $block->disable($blockid, $disabled);
		if($result)
		{
	            cache_block();
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;

	case 'refresh':
            @set_time_limit(600);
              @dir_delete(DC_ROOT.'data/block/');
	       $block->refresh();
	       cache_block();
		showmessage('操作成功！', $forward);
		break;



	case 'check':
		if($block->checkpageid($value))
	    {
		    exit('success');
		}
		else
	    {
			exit('该网页标识已经存在！');
		}
		break;

	case 'datalist':
            $blockid = intval($blockid);
    	     $r = $block->get($blockid);
	     if(!$r) showmessage('指定的碎片不存在！');
            extract($r);
            $datalist = $block->datalist(" `blockid` = $blockid ", $page, 20);
		include atpl('block_datalist');
		break;
        
    default :
            $data = $block->listinfo('', $page, 20);
		include atpl('block_manage');
}
?>