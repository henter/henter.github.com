<?php
class role{
	var $db;
	var $table;
	
	function __construct($action='') 
	{	
		global $db;
		$this->action = $action;
		$this->db = &$db;
		$this->table = DB_PRE.'admin_role';
	}

	function get($id)
	{
		$id = intval($id);
		if($id < 1) return false;
		$result = $this->db->get_one("SELECT * FROM $this->table WHERE id=$id");
		return $result;
	}

	function getpowers($id)
	{
		$id = intval($id);
		if($id < 1) return false;
		$result = $this->db->get_one("SELECT `powers` FROM $this->table WHERE id=$id");
              $power = string2array($result['powers']);
		return $power;
	}


	//获取列表
	function manage($where="",$order="",$page=1,$pagesize = 20)
	{
		$order = ($order) ? $order : ' id DESC';
		$offset = ($page-1)*$pagesize;
		$offset = intval($offset);
		$pagesize = intval($pagesize);
		$sql = "SELECT * FROM $this->table $where ORDER BY $order LIMIT $offset, $pagesize";
		return $this->db->select($sql);
	}


	function add($info)
	{
		$this->db->insert($this->table, $info);
		$id = $this->db->insert_id();
		return $id;
	}

	function edit($id,$info)
	{
        	$this->db->update($this->table, $info," id=$id");
	}

	function delete($id)
	{
		if(is_array($id))
		{
			array_map(array(&$this, 'delete'), $id);
		}
		else
		{
			$id = intval($id);
			if($id < 1) return false;
			$form = $this->get($id);
			if(!$form) return false;
			$this->db->query("DELETE FROM $this->table WHERE id IN ($id)");
		}
		return true;
	}


	function page()
	{
		global $page,$pagesize,$key;
              $where = ($key) ? " WHERE `key`=$key" : "";
		$r = $this->db->get_one("SELECT COUNT(*) AS num FROM $this->table $where");
		$number=$r['num'];
		return pages($number, $page, $pagesize);
	}


    function checkname($rolename){
        $sql = "SELECT * FROM $this->table WHERE rolename = '$rolename'";
        return $this->db->select($sql);
    }


}
?>