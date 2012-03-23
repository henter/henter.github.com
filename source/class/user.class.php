<?php
class User{
	var $db;
	var $table;
	
	function __construct($action='') 
	{	
		global $db;
		$this->action = $action;
		$this->db = &$db;
		$this->table = DB_PRE.'user';
	}

	function get($id)
	{
		$id = intval($id);
		if($id < 1) return false;
		$result = $this->db->get_one("SELECT * FROM $this->table WHERE id=$id");
		return $result;
	}
    
	function getbycode($usercode)
	{
		if(!$usercode) return false;
		return $this->db->get_one("SELECT * FROM $this->table WHERE `usercode` = $usercode");
	}
    
    //检查登陆
	function checklogin($usercode,$idcard)
	{
		if(!$usercode || !$idcard) return false;
		return $this->db->get_one("SELECT * FROM $this->table WHERE `usercode` = $usercode AND `idcard`='$idcard'");
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

	function submitanswer($id,$answer)
	{
            $info[answer] = $answer;
            $this->db->update($this->table, $info," id=$id");
	}

	function getanswer($id)
	{
		if(!$id) return false;
		$return = $this->db->get_one("SELECT `answer` FROM $this->table WHERE `id` = $id");
             return $return ? string2array($return[answer]) : '';
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
    
	function delall()
	{
		$this->db->query("DELETE FROM $this->table");
		return true;
	}

    

	function page()
	{
		global $page,$pagesize;
		$r = $this->db->get_one("SELECT COUNT(*) AS num FROM $this->table $where");
		$number=$r['num'];
		return showpage($number, $page, $pagesize);
	}




}
?>