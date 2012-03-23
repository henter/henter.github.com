<?php
/*
    核心初始类
*/
class app
{
    function __construct()
    {
        $param = new param();
        define('ROUTE_APP', $param->route_app());
        define('ROUTE_MOD', $param->route_mod());
        define('ROUTE_ACT', $param->route_act());
        $this->init();
    }
    
    private function init(){
        dc::loadclass('app_abstract','',false);
        $app_controller = $this->load_app();
        if (preg_match('/^[_]/i', ROUTE_MOD)) {
            system_error(ROUTE_MOD.'为内部方法，禁止访问。');
        } else {
            //如果存在模块方法或魔术方法
            if (method_exists($app_controller, ROUTE_MOD) || method_exists($app_controller, '__call')) {
                call_user_func(array($app_controller, ROUTE_MOD));
            }else{
                system_error(ROUTE_MOD.'方法不存在。');
            }
        }
    }
    
    /**
    * 加载应用
    */
    private function load_app($app = '',$classname='') {
        if (!$app) $app = ROUTE_APP;
        $filepath = DCS.'app/'.$app.'/controller.class.php';
        if (file_exists($filepath)) {
            //自动加载app的类和函数库
            dc::autofunc(ROUTE_APP);
            dc::autoclass(ROUTE_APP);
            
            //app主控制器
            $classname = $classname ? $classname : 'controller_'.$app;
            include $filepath;
            $app_controller = new $classname;
            //检查是否继承了app_anstract抽象类
            if(!is_subclass_of($app_controller,'app_abstract')){
                system_error($app.'控制器类必须继承app_abstract抽象类！');
            }
            //app注册功能
            if (method_exists($app_controller, 'register')) {
                $app_controller->register();
            }
            return $app_controller;
        } else {
            system_error($app.'应用不存在');
        }
    }

}


/*
    app抽象类 所有app的controller.class.php必须继承此类
*/
abstract class app_abstract
{
    public function __construct()
    {
    }
    
    //自动加载app的模块
    function __call($mod,$arg_array){
        global $_G,$db;
        $modpath = modpath($mod);
        if(file_exists($modpath)){
            include $modpath;
        }else{
            system_error($mod.'模块文件不存在。');
        }
    }
    
    //自动获取对象属性
    public function __get($key)
    {
        return $this->get($key);
    }
    
    public function __set($key, $value)
    {
        return null === $value ? $this->delete($key) : $this->set($key, $value);
    }
    
    public function __unset($key)
    {
        return $this->delete($key);
    }
    
}
