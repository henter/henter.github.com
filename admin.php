<?php
define('IN_ADMIN', TRUE);
require dirname(__FILE__).'/inc/common.inc.php';
require 'form.class.php';
require 'cache.func.php';
require DC_ROOT.$adminpath.'global.func.php';

if(!isset($file)) $file = 'index';
preg_match("/^[0-9A-Za-z_-]+$/", $file) or showmessage('Invalid Request.');
if(!isset($forward) && str_exists(HTTP_REFERER, '?')) $forward = HTTP_REFERER;


if(get_cookie('admin'))
{
        $_username = get_cookie('a_username');
        $_roleid = get_cookie('a_roleid');
        $_areacode = get_cookie('a_areacode');
        $_key = get_cookie('a_key');
        $_adminid = get_cookie('a_adminid');
        
        $POS = cache_read('pos.php');
}
elseif($file != 'login')
{
    Header("Location: ?file=login&forward=".urlencode(URL));
}

$_rolepowers = $_key ? '' : $ROLE[$_roleid]['powers'];

if($_rolepowers) $menusql = " AND menuid in($_rolepowers) ";


if($mod && $mod!='dc' && $mod!='dearcms'){
    @include DC_ROOT.$mod.'/'.'admin/admin.inc.php';
    if(!@include DC_ROOT.($mod ? $mod.'/' : '').'admin/'.$file.'.inc.php') include atpl($file);
}else{
    if(!@include DC_ROOT.($mod ? $mod.'/' : '').$adminpath.$file.'.inc.php') include atpl($file);
    //if(!@include DC_ROOT.($mod ? $mod.'/' : '').$adminpath.$file.'.inc.php') exit("$mod.$file文件不存在！");
}




?>