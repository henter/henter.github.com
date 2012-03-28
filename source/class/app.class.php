<?php
/*
    核心初始类
*/
class app{
    var $app = ROUTE_APP;
    var $mod = ROUTE_MOD;
    var $act = ROUTE_ACT;
    function __construct(){
        //当前应用属性 $app $mod可能会自定义 这里将其赋给常量
        define('APP', $this->app);
        define('MOD', $this->mod);
        define('ACT', $this->act);
        
        $this->init();
    }

    private function init(){
        $app_obj = $this->load_app($this->app);
        if (preg_match('/^[_]/i', $this->mod)) {
            system_error($this->mod.'为内部方法，禁止访问。');
        } else {
            //如果存在模块方法或魔术方法
            if (method_exists($app_obj, $this->mod) || method_exists($app_obj, '__call')) {
                call_user_func(array($app_obj, $this->mod));
            }else{
                system_error($this->mod.'方法不存在。');
            }
        }
    }
    
    /**
    * 加载应用
    */
    private function load_app($app = '',$classname='') {
        $filepath = dc::appath($app).'app.class.php';
        if (file_exists($filepath)) {
            //app主控制器
            $classname = $classname ? $classname : 'app_'.$app;
            include $filepath;
            $app_obj = new $classname;
            //检查是否继承了app_anstract抽象类
            if(!is_subclass_of($app_obj,__CLASS__)){
                system_error($app.'控制器类必须继承'.__CLASS__.'抽象类！');
            }
            //app注册功能
            if (method_exists($app_obj, 'register')) {
                $app_obj->register();
            }
            return $app_obj;
        } else {
            system_error($app.'应用不存在');
        }
    }

    //自动加载app的模块
    function __call($mod,$arg_array){
        global $_G;
        
        if(!@include dc::modpath($this->app.':'.$mod)){
            system_error($mod.'模块文件不存在。');
        }
    }
    
    //自动获取对象属性
    public function __get($key){
        return $this->get($key);
    }
    
    public function __set($key, $value){
        return NULL === $value ? $this->delete($key) : $this->set($key, $value);
    }
    
    public function __unset($key){
        return $this->delete($key);
    }
    
    
}

