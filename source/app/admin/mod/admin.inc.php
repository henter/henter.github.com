<?php
defined('IN_ADMIN') or exit('Access Denied');
require_once 'admin/admin.class.php';

$admin = new admin();

if(!$action) $action = 'manage';
if(!$forward) $forward = "?file=$file&action=manage";



switch ($action){
	case 'manage':
		$admins = $admin->manage();
		$tpl='admin_manage';
	break;
    
	case 'add' :
		if ($dosubmit){

                    ($data[username] && $data[password]) or showmessage('登陆名称和密码必须填写！');
                    $data[areacode] = $areacode;
                    $data[password] = md5($data[password]);
			if ($admin->checkname($data[username])){
				showmessage('管理员已经存在！');
			}

			$admin->add($data);
			showmessage('添加成功！',"?file=$file");
		}
		$tpl='admin_add';
	break;
       
	case 'edit' :
		if ($dosubmit){
                     $data[areacode] = $areacode;
                     if($data[password]){
                        $data[password] = md5($data[password]);
                     }else{
                        unset($data[password]);
                     }
                     
			$admin->edit($id, $data);
			showmessage('修改成功！',"?file=$file");
		}else{
			$result = $admin->get($id);
			@extract($result);
                     @extract($admin_power);
                     if($id!=$_adminid && $_roleid!=4) showmessage("禁止越权操作！");

			$tpl='admin_add';
		}
	break;

	case 'delete':
		if(empty($id)) showmessage('请选择要删除的信息');
		$admin->delete($id);
		showmessage('删除成功',"?file=$file");
	break;


	case 'show' :
		$show = $admin->show($uid);
	break;

	case 'check':
		if($admin->check($value))
	    {
		    exit('success');
		}
		else
	    {
			exit($admin->errormsg());
		}
		break;
}

include atpl($tpl);
?>