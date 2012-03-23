<?php
/*
App Name: DearCMS Admin
App URI: http://dearcms.com/?app=admin
Description: dearcms中的admin应用
Author: Henter
Version: 1.0
Author URI: http://henter.me/
*/

class controller_admin extends app_abstract
{
    function __construct()
    {
        define('IN_ADMIN',true);
        dc::autofunc('admin');
        //$a = dc::loadmodel('test');

    }
    

    
    function register(){
    
    }
    
    function _fuck(){
        echo 'fuck';
    }
}