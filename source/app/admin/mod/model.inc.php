<?php 
defined('IN_DC') or exit('Access Denied');
$model = dc::loadclass('model','dearcms');


if(!$action) $action = 'manage';
if(!$forward) $forward = "?file=$file&action=manage";

$submenu = array(
	array('添加模型', '?file='.$file.'&action=add'),
	array('管理模型', '?file='.$file.'&action=manage'),
	array('更新模型缓存', '?file='.$file.'&action=cache'),
);
$menu = admin_menu($modelname.'模型管理', $submenu);


switch($action)
{
        case 'add':
		if($dosubmit)
		{
			$modelid = $model->add($info);
			if($modelid)
			{
				cache_model();
				showmessage('操作成功！', '?file=model_field&action=manage&modelid='.$modelid);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			include atpl('model_add');
		}
        break;
        case 'edit':
		if($dosubmit)
		{
			$result = $model->edit($modelid, $info);
			if($result)
			{
				cache_model();
				showmessage('操作成功！', $forward);
			}
			else
			{
				showmessage('操作失败！');
			}
		}
		else
		{
			$info = $model->get($modelid);
			if(!$info) showmessage('指定的模型不存在！');
			extract($info);
			include atpl('model_edit');
		}
		break;
        case 'manage':
            $infos = $model->listinfo('modeltype=0', 'modelid', 1, 100);
            include atpl('model_manage');
        break;
	case 'export':
		$result = $model->export($modelid);
		$filename = $result['arr_model']['tablename'].'.model';
		cache_write($filename, $result, CACHE_MODEL_PATH);
		file_down(CACHE_MODEL_PATH.$filename, $filename);
		break;
        case 'delete':
		$result = $model->delete($modelid);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！', $forward);
		}
        break;
        case 'disable':
		$result = $model->disable($modelid, $disabled);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
        break;
        
        case 'cache':
                $model = new model();
                $model->cache();
                foreach($_G['MODEL'] as $modelid=>$v)
                {
                	if($v['modeltype'] == 0)
                	{
                		$field = new model_field($modelid);
                		$field->cache();
                	}
                }
                showmessage('模型缓存更新成功！');
        break;
}
?>