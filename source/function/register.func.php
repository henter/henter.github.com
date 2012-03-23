<?php
/*
    用于app注册功能
*/


//获取某函数被哪个文件调用
function get_app_by_register_func(){
    $debug_backtrace = debug_backtrace();
    //krsort($debug_backtrace);
    return str_replace('controller_','',$debug_backtrace[2]['class']);
}

//注册URI基础分段
function register_uribase($uribase){
    $app = get_app_by_register_func();
    //echo $app.'<hr />';
}

//注册模板标签语法
function register_tpltag($tpltag){
    $app = get_app_by_register_func();
    //echo $app.'<hr />';
}
