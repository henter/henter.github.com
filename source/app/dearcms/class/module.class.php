<?php 
class module
{
	var $db;
	var $table;
	var $MODULE;

    function __construct()
    {
		global $db, $MODULE;
		$this->db = &$db;
		$this->table = DB_PRE.'module';
		$this->MODULE = $MODULE;
    }

	function module()
	{
		$this->__construct();
	}

	function get($module, $fields = '*')
	{
		return $this->db->get_one("SELECT $fields FROM `$this->table` WHERE `module`='$module'");
	}

	function add($info)
	{
		if(!is_array($info) || !$this->check($info['module'])) return false;
		$info['publishdate'] = $info['installdate'] = $info['updatedate'] = date('Y-m-d');
		$this->db->insert($this->table, $info);
		$this->cache();
		return true;
	}

	function edit($module, $info)
	{
		unset($info['module'], $info['iscore']);
		return $this->db->update($this->table, $info, "`module`='$module'");
	}

    function install()
    {
    }

    function uninstall()
    {
    }

	function listinfo()
	{
		return $this->db->select("SELECT * FROM `$this->table` WHERE 1 ORDER BY `installdate`");
	}

	function check($module)
	{
		if(isset($this->MODULE[$module]))
		{
			$this->error = '模块已经存在';
			return false;
		}
		if(!preg_match("/^[a-z0-9]{2,15}$/", $module))
		{
			$this->error = '模块错误';
			return false;
		}
		return true;
	}

	function disable($module, $disabled)
	{
		$disabled = $disabled == 1 ? 1 : 0;
		$this->db->query("UPDATE `$this->table` SET `disabled`=$disabled WHERE `module`='$module'");
		$this->cache();
		return true;
	}

	function cache()
	{
		//cache_common();
		//cache_module();
	}

	function msg()
	{
		return $this->error;
	}
}
?>