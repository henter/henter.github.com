<?php
class app_dearcms extends app_abstract
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
        
    }
    
    function _fuck(){
        echo 'fuck';
    }
}