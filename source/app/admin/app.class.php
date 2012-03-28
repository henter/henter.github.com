<?php
/*
App Name: DearCMS Admin
App URI: http://dearcms.com/?app=admin
Description: dearcms中的admin应用
Author: Henter
Version: 1.0
Author URI: http://henter.me/
*/

class app_admin extends app
{
    function __construct(){
        define('IN_ADMIN',true);
    }
    

    
    function register(){
    
    }
    
    function index(){
        echo 'admin index';
    }
    
    function _fuck(){
        echo 'fuck';
    }
}