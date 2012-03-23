<?php 
defined('IN_DC') or exit('Access Denied');
@set_time_limit(600);
$fieldpath = appath('dearcms').'include/fields/';
require_once $fieldpath.'fields.inc.php';

$modelid = gpc('modelid');
$fieldid = gpc('fieldid');
$action = gpc('action');

dc::loadclass('model_field','dearcms',false);
$field = new model_field($modelid);
$model = dc::loadclass('model','dearcms');

$modelinfo = $model->get($modelid);
$modelname = $modelinfo['name'];
$tablename = $field->tablename;

$submenu = array(
	array('添加字段', '?file='.$file.'&action=add&modelid='.$modelid),
	array('管理字段', '?file='.$file.'&action=manage&modelid='.$modelid),
	array('预览模型', '?file='.$file.'&action=preview&modelid='.$modelid),
);
$menu = admin_menu($modelname.'模型字段管理', $submenu);

if(!$action) $action = 'manage';



switch($action)
{
    case 'add':
		if($dosubmit)
		{
                    $info['modelid'] = $modelid;
                    //$info['unsetgroupids'] = isset($unsetgroupids) ? implodeids($unsetgroupids) : '';
                    //$info['unsetroleids'] = isset($unsetroleids) ? implodeids($unsetroleids) : '';
                    $result = $field->add($info, $setting);
			if($result)
			{
				extract($setting);
				extract($info);
				require_once 'fields/'.$formtype.'/field_add.inc.php';
				showmessage('操作成功！', $forward);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			//if(!is_ie()) showmessage('本功能只支持IE浏览器，请用IE浏览器打开。');
			//$unsetgroups = form::checkbox($GROUP, 'unsetgroupids', 'unsetgroupids', '', 4);
			//$unsetroles = form::checkbox($ROLE, 'unsetroleids', 'unsetroleids', '', 4);
                    require_once $fieldpath.'patterns.inc.php';
			include atpl('model_field_add');
		}
		break;
    case 'edit':
		if($dosubmit)
		{
            //$info['unsetgroupids'] = isset($unsetgroupids) ? implodeids($unsetgroupids) : '';
            //$info['unsetroleids'] = isset($unsetroleids) ? implodeids($unsetroleids) : '';

			$result = $field->edit($fieldid, $info, $setting);
			if($result)
			{
				extract($setting);
				extract($info);
				if($issystem) $tablename = DB_PRE.'content';
				require_once $fieldpath.$formtype.'/field_edit.inc.php';
				showmessage('操作成功！', $forward);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			//if(!is_ie()) showmessage('本功能只支持IE浏览器，请用IE浏览器打开。');
			$info = $field->get($fieldid);
			if(!$info) showmessage('指定的字段不存在！');
			extract(new_htmlspecialchars($info));
			//$unsetgroups = form::checkbox($GROUP, 'unsetgroupids', 'unsetgroupids', $unsetgroupids, 4);
			//$unsetroles = form::checkbox($ROLE, 'unsetroleids', 'unsetroleids', $unsetroleids, 4);
                    require_once $fieldpath.'patterns.inc.php';
			include atpl('model_field_edit');
		}
		break;
	case 'copy':
		if($dosubmit)
		{
      		$info['modelid'] = $modelid;
			$info['formtype'] = $formtype;
                    //$info['unsetgroupids'] = isset($unsetgroupids) ? implodeids($unsetgroupids) : '';
                    //$info['unsetroleids'] = isset($unsetroleids) ? implodeids($unsetroleids) : '';
			$result = $field->add($info, $setting);
			if($result)
			{
				extract($setting);
				extract($info);
				require_once $fieldpath.$formtype.'/field_add.inc.php';
				showmessage('操作成功！', $forward);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			$info = $field->get($fieldid);
			if(!$info) showmessage('指定的字段不存在！');
			extract(new_htmlspecialchars($info));
			//$unsetgroups = form::checkbox($GROUP, 'unsetgroupids', 'unsetgroupids', $unsetgroupids, 5);
			//$unsetroles = form::checkbox($ROLE, 'unsetroleids', 'unsetroleids', $unsetroleids, 5);
                    require_once $fieldpath.'patterns.inc.php';
			include atpl('model_field_copy');
		}
		break;
    case 'manage':
            $infos = $field->listinfo("modelid=$modelid", 'listorder,fieldid', 1, 100);
            include atpl('model_field_manage');
            break;
    case 'delete':
		$info = $field->get($fieldid);
		$result = $field->delete($fieldid);
		if($result)
		{
			extract($info);
			@extract(unserialize($setting));
			require_once $fieldpath.$formtype.'/field_delete.inc.php';
			showmessage('操作成功！', '?file=model_field&action=manage&modelid='.$modelid);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;
    case 'listorder':
		$result = $field->listorder($info);
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
		$result = $field->disable($fieldid, $disabled);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;
    case 'setting_add':
		require_once $fieldpath.'patterns.inc.php';
              require_once $fieldpath.$formtype.'/field_add_form.inc.php';
		break;
    case 'setting_edit':
		$info = $field->get($fieldid);
		if(!$info) showmessage('指定的字段不存在！');
		eval("\$setting = $info[setting];");
		@extract($setting);
		require_once $fieldpath.'patterns.inc.php';
              require_once $fieldpath.$formtype.'/field_edit_form.inc.php';
		break;
    case 'preview':
		if($dosubmit)
		{
			showmessage(' 仅为预览，无法发布！');
		}
		else
		{
			require CACHE_MODEL_PATH.'content_form.class.php';
			$content_form = new content_form($modelid);
			$forminfos = $content_form->get();
			include atpl('content_add');
		}
		break;
	case 'checkfield':
		if(!$field->check($value))
		{
			exit('只能由英文字母、数字和下划线组成，必须以字母开头');
		}
		elseif($field->exists($value))
		{
			exit('字段名已存在');
		}
		else
		{
			exit('success');
		}
	break;
}
?>