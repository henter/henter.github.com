<?php
defined('IN_ADMIN') or exit('Access Denied');
require 'admin/admin.class.php';
$admin = new admin();
//set_cookie('admin',1) ;

$forward = $forward ? $forward : $adminfile.'?file=index';


if ($dosubmit){
    if($result = $admin->cklogin($admin_username,$admin_password)){
        if($result[areacode] && $result[roleid]<4) showmessage('您请到代理商地址登录！',$DC[cpurl]);
        set_cookie('admin',1) ;
        set_cookie('a_username',$result[username]) ;
        set_cookie('a_adminid',$result[id]);
        set_cookie('a_roleid',$result[roleid]);
        set_cookie('a_key',$result[key]);
        set_cookie('a_admin_powers',$result[admin_powers]) ;
        
        set_cookie('auth','');set_cookie('userid','');set_cookie('username','');//退出前台用户 否则会与后台冲突
        showmessage('登陆成功！', '?file=index');
    }

}else{
    
    //if(get_cookie('admin')) showmessage('你已经登陆了', '?file=index');
    include atpl($file);
}
?>
