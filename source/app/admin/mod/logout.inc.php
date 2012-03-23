<?php
defined('IN_ADMIN') or exit('Access Denied');

unset($_SESSION);
set_cookie('admin','');
$tourl = SITE_URL.'index.php';

if($action=='relogin') $tourl = $adminfile.'?file=login';

showmessage('退出成功！',$tourl);
?>