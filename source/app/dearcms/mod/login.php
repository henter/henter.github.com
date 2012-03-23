<?php
defined('IN_DC') or exit('Access Denied');

//if(get_cookie("login")) showmessage('你已经登陆了', '?file=welcome');

if ($submit){
    if(!$user->checklogin($usercode,$idcard)) showmessage('考号或身份证号不对应，请重新登陆！');
    
    $kstime = KS_TIME*60;
    $db->query("UPDATE `".DB_PRE."user` SET `times` = $kstime WHERE `usercode` = $usercode");

    set_cookie("tiaoguo",'');//清除跳过题目
    set_cookie("login",1);
    set_cookie("usercode",$usercode);
    showmessage('登陆成功！', 'index.php',1000);
}else{
    include template($file);
}
?>
