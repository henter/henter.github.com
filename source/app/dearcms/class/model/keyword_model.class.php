<?php
defined('IN_DC') or exit('Access Denied');
dc::loadclass('model');

class keyword_model extends model {
	public $table_name = '';
	public function __construct() {
		$this->table_name = 'position';
		parent::__construct();
	}
}
?>