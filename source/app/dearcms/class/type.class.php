<?php
class type
{
	var $db;
	var $table;
	var $pages;
	var $number;
       var $module;
       var $modelid;

    function __construct($module = 'dearcms')
    {
		global $db;
		$this->db = &$db;
		$this->table = DB_PRE.'type';
		$this->module = $module;
    }

	function type($module = 'dearcms')
	{
		$this->__construct($module);
	}

	function get($typeid, $fields = '*')
	{
		$typeid = intval($typeid);
		return $this->db->get_one("SELECT $fields FROM $this->table WHERE typeid=$typeid");
	}

	function add($info)
	{
		$this->modelid = $info['modelid'];
              if($this->check($info['name'])) return false;
		if(!is_array($info) || empty($info['name'])) return false;
              $info['module'] = $this->module;
		$this->db->insert($this->table, $info);
		$typeid = $this->db->insert_id();
		$this->cache();
		return $typeid;
	}

	function edit($typeid, $info)
	{
		if(!$typeid || !is_array($info) || empty($info['name'])) return false;
		$typeid = intval($typeid);

		$result = $this->db->update($this->table, $info, "typeid=$typeid");
		$this->cache();
		return $result;
	}

	function delete($typeid)
	{
		$typeid = intval($typeid);
		$this->db->query("DELETE FROM $this->table WHERE `typeid`=$typeid");
		$this->cache();
		return true;
	}

	function listinfo($page = 1, $pagesize = 20)
	{
		$page = max(intval($page), 1);
              $offset = $pagesize*($page-1);
		$r = $this->db->get_one("SELECT count(*) AS number FROM $this->table WHERE `module`='$this->module'");
              $number = $r['number'];
              $this->pages = pages($number, $page, $pagesize);
		$array = array();
		$result = $this->db->query("SELECT * FROM $this->table WHERE `module`='$this->module' ORDER BY `listorder`,`typeid` LIMIT $offset, $pagesize");
		while($r = $this->db->fetch_array($result))
		{
			$array[] = $r;
		}
		$this->number = $this->db->num_rows($result);
              $this->db->free_result($result);
		return $array;
	}

	function listorder($info)
	{
		if(!is_array($info)) return false;
		foreach($info as $id=>$listorder)
		{
			$id = intval($id);
			$listorder = intval($listorder);
			$this->db->query("UPDATE $this->table SET listorder=$listorder WHERE `typeid`=$id");
		}
		return true;
	}
    //相同mod 下的 typename 相同
    function check($typename)
    {
        $row = $this->db->get_one("SELECT `name` FROM `$this->table` WHERE `name` = '$typename' AND `module` = '$this->module' AND `modelid`='$this->modelid'");
        if(empty($row))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    function checkdir($dir, $typeid = 0)
    {
    	if($typeid)
    	{
    		$result = $this->db->get_one("SELECT `typeid` FROM `$this->table` WHERE `typedir`='$dir' AND `typeid`!='$typeid'");
    		if($result) return false;
    	}
    	else 
    	{
    		$result = $this->db->get_one("SELECT `typeid` FROM `$this->table` WHERE `typedir`='$dir'");
    		if($result) return false; 
    	}
    	return true;
    }
    
	function cache()
	{
        cache_type();
        cache_common();
	}
}
?>