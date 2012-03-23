<?php
class controller_dearcms extends app_abstract
{
    function __construct()
    {
        
    }
    

    function index(){
        //dc::import('test');
        echo 'hello dearcms !';

        //include tpl('index');
    }
    
    //注册app所需功能
    function register(){
        register_uribase('testuri');
        register_tpltag('testuri');
    }
    
    function _fuck(){
        echo 'fuck';
    }
}