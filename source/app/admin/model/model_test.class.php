<?php
class test_model {

	//路由配置
	private $route_config = '';
	
	public function __construct() {
		return true;
	}

	/**
	 * 应用
	 */
	public function route_app() {
		return isset($_GET['app']) && $_GET['app'] ? $_GET['app'] : 'dearcms';
	}

	/**
	 * 应用模块
	 */
	public function route_mod() {
		return isset($_GET['mod']) && $_GET['mod'] ? $_GET['mod'] : 'index';
	}

	/**
	 * 事件
	 */
	public function route_act() {
		return isset($_GET['act']) && $_GET['act'] ? $_GET['act'] : 'index';
	}
    
}
