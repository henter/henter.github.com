<?php
defined('IN_ADMIN') or exit('Access Denied');
require_once 'admin/role.class.php';
$role = new role();


if(!$action) $action = 'manage';
if(!$forward) $forward = "?file=$file&action=manage";
$submenu = array(
	array('添加用户组', '?file='.$file.'&action=add'),
	array('管理用户组', '?file='.$file.'&action=manage'),
);
$menu = admin_menu($modelname.'后台用户组管理', $submenu);



switch ($action){
	case 'manage':
		$roles = $role->manage();
		$tpl='role_manage';
	break;
    
	case 'add' :
		if ($dosubmit){
                    $data[rolename]  or showmessage('名称必须填写！');
                    $data[powers] = implode(',',$power);
			if ($role->checkname($data[rolename])){
				showmessage('用户组已经存在！');
			}

			$role->add($data);
                     cache_common();
			showmessage('添加成功！',"?file=$file");
		}
		$tpl='role_add';
	break;
       
	case 'edit' :
		if ($dosubmit){
                    $data[powers] = implode(',',$power);
			$role->edit($id, $data);
                    cache_common();
			showmessage('修改成功！',"?file=$file");
		}else{
			$result = $role->get($id);
			@extract($result);

			$tpl='role_add';
		}
	break;

	case 'delete':
              if($_roleid!=4) showmessage('此操作只有超级管理员允许执行！');
		if(empty($id)) showmessage('请选择要删除的用户组');
		$role->delete($id);
                    cache_common();
		showmessage('删除成功',"?file=$file");
	break;

}

function ckpower($menuid){
    global $powers;
    if (check_in($menuid,$powers)) echo ' checked ';
}



include atpl($tpl);
?>