<?php
class param {

	//路由配置
	private $route_config = '';
	
	public function __construct() {
		return true;
	}

	/**
	 * 应用
	 */
	public function route_app() {
		$app = isset($_GET['app']) && !empty($_GET['app']) ? $_GET['app'] : (isset($_POST['app']) && !empty($_POST['app']) ? $_POST['app'] : '');
		if (empty($app)) {
                    //默认app
			return 'dearcms';
		} else {
			return $app;
		}
	}

	/**
	 * 应用模块
	 */
	public function route_mod() {
		$mod = isset($_GET['mod']) && !empty($_GET['mod']) ? $_GET['mod'] : (isset($_POST['mod']) && !empty($_POST['mod']) ? $_POST['mod'] : '');
		if (empty($mod)) {
			return 'index';
		} else {
			return $mod;
		}
	}

	/**
	 * 事件
	 */
	public function route_act() {
		$act = isset($_GET['act']) && !empty($_GET['act']) ? $_GET['act'] : (isset($_POST['act']) && !empty($_POST['act']) ? $_POST['act'] : '');
		if (empty($act)) {
			return 'index';
		} else {
			return $act;
		}
	}


}
?>