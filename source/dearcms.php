<?php
/**
 *  dearcms.php DearCMS入口文件
 */
define('IN_DC', true);

define('DS',str_replace("\\", '/', DIRECTORY_SEPARATOR));
define('DCR', substr(str_replace("\\", '/', dirname(__FILE__)), 0, -6));
define('DCS',DCR.'source/');
define('DCD',DCR.'data/');
define('DCC',DCD.'cache/');
define('MICROTIME_START', microtime());
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
error_reporting(0);


//资源加载
function import($key, $init = true){ return dc::import($key, $init);}
function lib($key, $info = false){ return dc::lib($key, $info);}

//加载工具
class dc {
    
    
    /**
     * 获取option表数据
     *  key
     */
    public static function option($key){
        self::import('option');
        return 'ok';
    }
    

    //获取app文件夹路径
    function appath($app=''){
        if(!$app) return DCS.'app'.DS;
        return DCS.'app'.DS.$app.DS;
    }

    //返回app的mod文件路径
    function modpath($mod='index'){
        if(strpos($mod, ':')) list($app, $mod) = explode(':', $mod);
        return self::appath($app).'mod'.DS.$mod.'.inc.php';
    }

    /**
     * 加载资源 import('app:type.name');
     *  type可以指定所属app 如 app:type，type可以是class func tool等
     *  name为文件名
     */
    public static function import($key, $initialize = true){
        //'app','type','name','filepath'
        @extract(self::lib($key, true));

        if(in_array($type, array('class','model','tool'))){
            return self::_loadclass($key, $initialize);
        }else{
            //默认加载function
            return self::loadfunc($name);
        }
        return false;
    }
    
    /**
     * 库文件路径 app:type.name
     */
    public static function lib($key, $info = false) {
        if(strpos($key,'.')){
            list($type, $name) = explode('.',$key);
            if(strpos($type,':')){
                list($app, $type) = explode(':',$type);
            }
        }else{
            $name = & $key;
        }
        if(in_array($type, array('class','model','tool'))){
            $path = $type;
            $filepath = $app ? self::appath($app) : DCS;
            //model类 文件命名以model.class.php结尾
            if($type == 'model'){
                $filepath .= $path.DS.$name.'.model.class.php';
            }else{
                $filepath .= $path.DS.$name.'.class.php';
            }
        }else{
            $filepath = $app ? self::appath($app) : DCS;
            $filepath .= 'function'.DS.$name.'.func.php';
        }
        return $info ? compact('app','type','name','filepath') : $filepath;
    }

    
    /**
     * 加载类 path为目录 可以是class、model、tool
        默认为系统类，也可以加载app类（name为app:name）
     */
    public static function &_loadclass($name, $initialize = true, $param = array()) {
        static $classes = array();
        $key = md5($name);
        if (isset($classes[$key])) {
            if (!empty($classes[$key])) {
                return $classes[$key];
            } else {
                return true;
            }
        }
        //'app','type','name','filepath'
        @extract(self::lib($name, true));

        if (include $filepath) {
            if ($initialize) {
                //类名含目录路径时
                if(strpos($name, '/')) $name = array_pop(explode('/', $name));
                //model类 类名为 model_name命名  文件命名以model.class.php结尾
                if($type == 'model') $name = 'model_'.$name;
                $classes[$key] = new $name;
            } else {
                $classes[$key] = true;
            }
            return $classes[$key];
        } else {
            system_error($type.'文件'.$name.'载入出错！');
            //return false;
        }
    }
    
    /**
     * 加载函数库
     * @param string $func 函数库名
     * 此函数可避免多次include的问题
     */
    public static function loadfunc($name) {
        static $funcs = array();
        $key = md5($name);
        if (isset($funcs[$key])) return true;

        //'app','type','name','filepath'
        @extract(self::lib($name, true));

        if (include $filepath) {
            $funcs[$key] = true;
            return true;
        } else {
            system_error('func文件'.$name.'载入出错！');
            //return false;
        }
    }

    /**
     * 加载配置文件
     * @param string $file 配置文件
     * @param string $key  要获取的配置荐
     * @param string $default  默认配置。当获取配置项目失败时该值发生作用。
     * @param boolean $reload 强制重新加载。
     */
    public static function loadconfig($file, $key = '', $default = '', $reload = false) {
        static $configs = array();
        if (!$reload && isset($configs[$file])) {
            if (empty($key)) {
                return $configs[$file];
            } elseif (isset($configs[$file][$key])) {
                return $configs[$file][$key];
            } else {
                return $default;
            }
        }
        $path = DCR.'config'.DS.$file.'.config.php';

        if (file_exists($path)) {
            $configs[$file] = include $path;
        }else{
            system_error('config文件'.$file.'载入出错！');
        }
        if (empty($key)) {
            return $configs[$file];
        } elseif (isset($configs[$file][$key])) {
            return $configs[$file][$key];
        } else {
            return $default;
        }
    }
}



/*
    核心初始类
*/
class dearcms{
    var $db = null;
    var $session = null;
    var $var = array();

    var $superglobal = array(
        'GLOBALS' => 1,
        '_GET' => 1,
        '_POST' => 1,
        '_REQUEST' => 1,
        '_COOKIE' => 1,
        '_SERVER' => 1,
        '_ENV' => 1,
        '_FILES' => 1,
    );

    function &instance() {
        static $object;
        if(empty($object)) {
            $classname = __CLASS__;
            $object = new $classname();
        }
        return $object;
    }
    
    function __construct(){
        $this->_init_env();
        $this->_init_config();
        $this->_init_input();
        $this->_init_output();
    }
    
    function init(){
        $this->_init_db();
        $this->_init_dc();
    }
    
    function _init_env(){
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
	if(phpversion() < '5.3.0') {
		set_magic_quotes_runtime(0);
	}
       //加载核心函数库
       import('global');

	if(function_exists('ini_get')) {
		$memorylimit = @ini_get('memory_limit');
		if($memorylimit && return_bytes($memorylimit) < 33554432 && function_exists('ini_set')) {
			ini_set('memory_limit', '128m');
		}
	}
    
	foreach ($GLOBALS as $key => $value) {
		if (!isset($this->superglobal[$key])) {
			$GLOBALS[$key] = null; unset($GLOBALS[$key]);
		}
	}
        
	global $_G;
	$_G = array(
		'uid' => 0,
		'username' => '',
		'referer' => '',
		'charset' => '',
		'config' => array(),
		'setting' => array(),
		'cookie' => array(),
		'cache' => array(),
		'session' => array(),
		'cat' => array(),
	);
	$_G['PHP_SELF'] = htmlspecialchars($_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']);
	$_G['basefilename'] = basename($_G['PHP_SELF']);
	$_G['siteroot'] = substr($_G['PHP_SELF'], 0, -strlen($_G['basefilename']));
	$this->var = & $_G;
    }
    
    function _init_config() {
       $_config = dc::loadconfig('dearcms');
    	if(empty($_config)) {
    		if(!file_exists(DCD.'./install.lock')) {
    			header('location: install');
    			exit;
    		} else {
    			system_error('缺少配置文件！');
    		}
    	}

    	if(empty($_config['debug']) || !file_exists(lib('debug'))) {
    		define('DEBUG', false);
    	} elseif($_config['debug'] === 1 || $_config['debug'] === 2 || !empty($_REQUEST['debug']) && $_REQUEST['debug'] === $_config['debug']) {
    		define('DEBUG', true);
    		if($_config['debug'] == 2) {
    			error_reporting(E_ALL);
    		}
    	} else {
    		define('DEBUG', false);
    	}

        define('IP', ip());
        define('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
        define('SCRIPT_NAME', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : preg_replace("/(.*)\.php(.*)/i", "\\1.php", $_SERVER['PHP_SELF']));
        define('QUERY_STRING', $_SERVER['QUERY_STRING']);
        define('PATH_INFO', isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');
        define('DOMAIN', isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : preg_replace("/([^:]*)[:0-9]*/i", "\\1", $_SERVER['HTTP_HOST']));
        define('SCHEME', $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://');
        define('SITE_URL', SCHEME.$_SERVER['HTTP_HOST'].DC_PATH);
        define('RELATE_URL', str_replace(array('(', '$', ')', '{', '}', '<', '>'), '', isset($_SERVER['REQUEST_URI']) ? (MAGIC_QUOTES_GPC ? dstripslashes($_SERVER['REQUEST_URI']) : daddslashes($_SERVER['REQUEST_URI'])) : SCRIPT_NAME.(QUERY_STRING ? '?'.QUERY_STRING : PATH_INFO)));
        define('URL', SCHEME.$_SERVER['HTTP_HOST'].RELATE_URL);
        define('RELATE_REFERER',urlencode(RELATE_URL));
        define('TIME', time());
        
        //下面的设置 可能会在_init_dc里重写覆盖
    	$this->var['staticurl'] = SITE_URL.'content/static/';
    	$this->var['siteurl'] = SITE_URL;
        
    	$this->var['config'] = & $_config;
       
    	if(substr($_config['cookie']['path'], 0, 1) != '/') {
    		$this->var['config']['cookie']['path'] = '/'.$this->var['config']['cookie']['path'];
    	}
    	$this->var['config']['cookie']['pre'] = $this->var['config']['cookie']['pre'].substr(md5($this->var['config']['cookie']['path'].'|'.$this->var['config']['cookie']['domain']), 0, 4).'_';

        $this->var['authkey'] = md5($_config['authkey'].$this->var['config']['cookie']['pre']);
        
        //模板
        define('TPL_ROOT', DCR.'content'.DS.'template'.DS.$_config['template'].DS); //模板保存物理路径 后面要带‘/’
        define('TPL_CACHEPATH', DCD.'cache_template'.DS.$_config['template'].DS); //模板缓存物理路径
        define('TPL_CACHEPATH_ADMIN', DCD.'cache_template'.DS.'admin'.DS); //模板缓存物理路径
        define('TPL_TAG_CACHEPATH', DCC.'tpltag'.DS); //模板标签缓存
            
        //路由
        $router = import('class.router');
        define('ROUTE_APP', $router->route_app());
        define('ROUTE_MOD', $router->route_mod());
        define('ROUTE_ACT', $router->route_act());

    }
    
    function _init_input(){
		if (isset($_GET['GLOBALS']) ||isset($_POST['GLOBALS']) ||  isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
			system_error('request_tainting');
		}
		if(!MAGIC_QUOTES_GPC) {
			$_GET = daddslashes($_GET);
			$_POST = daddslashes($_POST);
			$_COOKIE = daddslashes($_COOKIE);
			$_FILES = daddslashes($_FILES);
		}
    }
    
    function _init_output(){
        header("Content-type:text/html;charset=".$this->var['config']['charset']); 
    }
    
    //数据库
    function _init_db(){
        if(!$this->dbclass) $this->dbclass = 'db_mysql';
        import('class.db/'.$this->dbclass, true);
        $db = & DB::object($this->dbclass);
        $db->set_config($this->var['config']['db'][$this->var['config']['dbid']]);
        DB::$pre = & $db->pre;
        $db->connect();
    }
    
    //设置项
    function _init_dc(){
        //$tpldir = dc::option('tpldir');
        
        $this->var['staticurl'] = $this->var['staticurl'] ? $_config['staticurl'] : SITE_URL.'content/static/';
        
        import('class.app');
    }

}


/* 数据库类 来自DiscuzX2 */
class DB{
       public static $pre = '';
	function &object($dbclass = 'db_mysql') {
            static $db;
            if(empty($db)) $db = new $dbclass();
            return $db;
	}
    
	function table($table) {
		return DB::_execute('table', $table);
	}

	function delete($table, $condition, $limit = 0, $unbuffered = true) {
		if(empty($condition)) {
			$where = '1';
		} elseif(is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$sql = "DELETE FROM ".DB::table($table)." WHERE $where ".($limit ? "LIMIT $limit" : '');
		return DB::query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}

	function insert($table, $data, $return_insert_id = false, $replace = false, $silent = false) {

		$sql = DB::implode_field_value($data);

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

		$table = DB::table($table);
		$silent = $silent ? 'SILENT' : '';

		$return = DB::query("$cmd $table SET $sql", $silent);

		return $return_insert_id ? DB::insert_id() : $return;

	}

	function update($table, $data, $condition, $unbuffered = false, $low_priority = false) {
		$sql = DB::implode_field_value($data);
		$cmd = "UPDATE ".($low_priority ? 'LOW_PRIORITY' : '');
		$table = DB::table($table);
		$where = '';
		if(empty($condition)) {
			$where = '1';
		} elseif(is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$res = DB::query("$cmd $table SET $sql WHERE $where", $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}

	function implode_field_value($array, $glue = ',') {
		$sql = $comma = '';
		foreach ($array as $k => $v) {
			$sql .= $comma."`$k`='$v'";
			$comma = $glue;
		}
		return $sql;
	}

	function insert_id() {
		return DB::_execute('insert_id');
	}

	function get($sql) {
		DB::checkquery($sql);
		return DB::_execute('get', $sql);
	}
    
	function get_row($sql) {
		DB::checkquery($sql);
		return DB::_execute('get_row', $sql);
	}
    
	function get_var($sql) {
		DB::checkquery($sql);
		return DB::_execute('get_var', $sql);
	}

	function get_col($sql) {
		DB::checkquery($sql);
		return DB::_execute('get_col', $sql);
	}

	function query($sql, $type = '') {
		DB::checkquery($sql);
		return DB::_execute('query', $sql, $type);
	}
    
	function result($resourceid, $row = 0) {
		return DB::_execute('result', $resourceid, $row);
	}

	function num_rows($resourceid) {
		return DB::_execute('num_rows', $resourceid);
	}

	function affected_rows() {
		return DB::_execute('affected_rows');
	}

	function free_result($query) {
		return DB::_execute('free_result', $query);
	}

	function error() {
		return DB::_execute('error');
	}

	function errno() {
		return DB::_execute('errno');
	}

	function _execute($cmd , $arg1 = '', $arg2 = '') {
		static $db;
		if(empty($db)) $db = & DB::object();
		$res = $db->$cmd($arg1, $arg2);
		return $res;
	}

	function checkquery($sql) {
		static $status = null, $checkcmd = array('SELECT', 'UPDATE', 'INSERT', 'REPLACE', 'DELETE');
		if($status === null) $status = G('config.querysafe.status');
		if($status) {
			$cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
			if(in_array($cmd, $checkcmd)) {
				$test = DB::_do_query_safe($sql);
				if($test < 1) DB::_execute('halt', 'security_error', $sql);
			}
		}
		return true;
	}

	function _do_query_safe($sql) {
		static $_CONFIG = null;
		if($_CONFIG === null) {
			$_CONFIG = G('config.querysafe');
		}
        
		$sql = str_replace(array('\\\\', '\\\'', '\\"', '\'\''), '', $sql);
		$mark = $clean = '';
		if(strpos($sql, '/') === false && strpos($sql, '#') === false && strpos($sql, '-- ') === false) {
			$clean = preg_replace("/'(.+?)'/s", '', $sql);
		} else {
			$len = strlen($sql);
			$mark = $clean = '';
			for ($i = 0; $i <$len; $i++) {
				$str = $sql[$i];
				switch ($str) {
					case '\'':
						if(!$mark) {
							$mark = '\'';
							$clean .= $str;
						} elseif ($mark == '\'') {
							$mark = '';
						}
						break;
					case '/':
						if(empty($mark) && $sql[$i+1] == '*') {
							$mark = '/*';
							$clean .= $mark;
							$i++;
						} elseif($mark == '/*' && $sql[$i -1] == '*') {
							$mark = '';
							$clean .= '*';
						}
						break;
					case '#':
						if(empty($mark)) {
							$mark = $str;
							$clean .= $str;
						}
						break;
					case "\n":
						if($mark == '#' || $mark == '--') {
							$mark = '';
						}
						break;
					case '-':
						if(empty($mark)&& substr($sql, $i, 3) == '-- ') {
							$mark = '-- ';
							$clean .= $mark;
						}
						break;

					default:

						break;
				}
				$clean .= $mark ? '' : $str;
			}
		}

		$clean = preg_replace("/[^a-z0-9_\-\(\)#\*\/\"]+/is", "", strtolower($clean));

		if($_CONFIG['afullnote']) {
			$clean = str_replace('/**/','',$clean);
		}

		if(is_array($_CONFIG['dfunction'])) {
			foreach($_CONFIG['dfunction'] as $fun) {
				if(strpos($clean, $fun.'(') !== false) return '-1';
			}
		}

		if(is_array($_CONFIG['daction'])) {
			foreach($_CONFIG['daction'] as $action) {
				if(strpos($clean,$action) !== false) return '-3';
			}
		}

		if($_CONFIG['dlikehex'] && strpos($clean, 'like0x')) {
			return '-2';
		}

		if(is_array($_CONFIG['dnote'])) {
			foreach($_CONFIG['dnote'] as $note) {
				if(strpos($clean,$note) !== false) return '-4';
			}
		}

		return 1;

	}

}
