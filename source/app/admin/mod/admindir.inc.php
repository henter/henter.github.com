<?php
defined('IN_DC') or exit('Access Denied');


if($dosubmit){
    $admindir = trim($admindir);
    $admindir or showmessage("请输入目录名称！");
    if($admindir == ADMIN_PATH) showmessage("改一样的？你无聊？");
    if (file_exists(DC_ROOT.$admindir) || !file_exists(DC_ROOT.ADMIN_PATH)) showmessage("新目录已存在或旧目录不存在！");



    if(rename(DC_ROOT.$adminfile, DC_ROOT.$admindir.'.php') && rename(DC_ROOT.ADMIN_PATH, DC_ROOT.$admindir)){
        //将后台目录写入配置文件
        $uconfig = array(
            'ADMIN_PATH'=>$admindir,
        );
        set_config($uconfig);
        cache_common();
        showmessage("后台目录更改成功！<script language='javascript'>parent.location.href='$admindir.php?file=logout&action=relogin';</script>");
    }else{
        showmessage("修改失败，请手动修改配置文件及目录名称！");
    }

}



include atpl('admindir');

?>