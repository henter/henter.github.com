<?php
class app_dearcms extends app{
    function __construct(){
        
    }
    
    //mod index
    function index(){
        //dc::import('test');
        //echo 'hello dearcms !';
        add_action('init', array($this, 'testaction'));
        add_filter('testfilter', array($this, 'testfilter'));
        //include tpl('index');
        global $wp_filter;
            //print_R($wp_filter);
            $data = get_plugin_files('dcplugin/gaofen.php');
            print_R($data);
        exit;
    }
    
    function testaction(){
        echo 'testaction';
    }
    
    function testfilter($a){
        return $a.'555';
        
    }
    //注册app所需功能
    function register(){
    }
    
    function _fuck(){
        echo 'fuck';
    }
}