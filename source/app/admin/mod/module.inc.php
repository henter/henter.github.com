<?php
defined('IN_DC') or exit('Access Denied');
require 'admin/module.class.php';
require 'sql.func.php';

$m = new module();

switch($action)
{
	case 'install':
		if($dosubmit)
		{
			$installdir or showmessage("请输入模块路径！");
			require_once DC_ROOT.$installdir."/install/config.inc.php";
			
			$r = $db->get_one("SELECT module From ".DB_PRE."module WHERE module='$installdir'");
			if($r) showmessage('系统内已存在该模块，请先卸载后再执行该安装程序！');
			
			if(file_exists(DC_ROOT.$installdir."/install/mysql.sql"))
			{
				$sql = file_get_contents(DC_ROOT.$installdir."/install/mysql.sql");
				sql_execute($sql);
			}
			if(file_exists(DC_ROOT.$installdir."/install/extention.inc.php"))
			{
				@include (DC_ROOT.$installdir."/install/extention.inc.php");
			}

			if(file_exists(DC_ROOT.$installdir."/install/templates/"))
			{
				//dir_copy(DC_ROOT.$installdir."/install/templates/", DC_ROOT.'templates/'.TPL_NAME.'/'.$module.'/');
			}
			cache_all();
			showmessage('模块安装成功！', "?file=$file");
		}
		else
		{
			if($confirm)
			{
				if(!is_dir(DC_ROOT.$installdir."/install/"))
				{
					showmessage('模块安装目录不存在！');
				}
				
			       require_once DC_ROOT.$installdir."/install/config.inc.php";
				if(array_key_exists($module, $MODULE)) showmessage('系统内已存在该模块，请先卸载后再执行该安装程序！');
			    include atpl('module_install_confirm');
			}
			else
			{
			    include atpl('module_install');
			}
		}
		break;
		
	case 'uninstall':
		if(!isset($module)) showmessage('操作失败！');
		if(in_array($module, $chas))
		{
			if(file_exists(DC_ROOT.'module/'.$module."/uninstall/extention.php"))
			{
				@include (DC_ROOT.'module/'.$module."/uninstall/extention.php");
			}
		}
		else 
		{
			if(file_exists(DC_ROOT.$module."/uninstall/extention.inc.php"))
			{
				@include (DC_ROOT.$module."/uninstall/extention.inc.php");
			}
			if(file_exists(DC_ROOT.$module."/uninstall/mysql.sql"))
			{
				$sql = file_get_contents(DC_ROOT.$module."/uninstall/mysql.sql");
				sql_execute($sql);
			}	
                    if(file_exists(DC_ROOT.$module."/uninstall/delete.txt"))
			{
				$delete = file_get_contents(DC_ROOT.$module."/uninstall/delete.txt");				
				$deletearr = explode("\n",str_replace("\r","",$delete));
	    		       $deletearr = array_filter($deletearr);
        	    		foreach($deletearr as $del)
        	    		{
        				$del = DC_ROOT.$del;
        	    		 	if(is_dir($del)) dir_delete($del);
        	    		 	else if(file_exists($del)) @unlink($del);
        	    		}
			}
		}
		//dir_delete(DC_ROOT.'templates/'.TPL_NAME.'/'.$module.'/');
		require_once 'menu.class.php';
		$menu = new menu();
		$menuid = $menu->menuid($module);
		$menu->delete($menuid);
		$db->query("DELETE FROM `".DB_PRE."module` WHERE `module`='$module';");
		$db->query("DELETE FROM `".DB_PRE."menu` WHERE `keyid`='$module';");
		cache_all();
		showmessage('模块卸载成功！',"?mod=".$mod."&file=module");
		break;


	case 'add':
	      if($dosubmit)
            {
                //$m->add($info);
                if(!$m->add($info)) showmessage($m->msg());
                showmessage('操作成功！', $forward);
            }
		else
            {
			include atpl('module_add');
            }
	     break;

	case 'view':
		$r = $m->get($module);
		@extract($r);
		include atpl('module_view');
		break;

	case 'faq':
		$r = $m->get($module);
		@extract($r);
 		include atpl('module_faq');
		break;

	case 'disable':
		if($m->disable($module, $value))
		{
			showmessage('操作成功！', '?mod='.$mod.'&file='.$file);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;

        default:
            $data = $m->listinfo();
            include atpl('module');
}
?>