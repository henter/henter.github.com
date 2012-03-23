<?php
defined('IN_DC') or exit('Access Denied');

set_cookie('login', '');
set_cookie('userid', '');
set_cookie('username', '');
set_cookie('usercode', '');
unset($_SESSION);
session_destroy();
showmessage('退出成功！','?file=login');
?>