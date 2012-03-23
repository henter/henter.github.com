<?php 
class position
{
	var $db;
	var $pages;
	var $number;
	var $table;

    function __construct()
    {
		global $db;
		$this->db = &$db;
		$this->table = DB_PRE.'pos';
              $this->table_content = DB_PRE.'content';
              $this->table_shop = DB_PRE.'shop';
		$this->table_position = DB_PRE.'position';
		register_shutdown_function(array(&$this, 'cache'));
    }

	function position()
	{
		$this->__construct();
	}

	function get($posid)
	{
		$posid = intval($posid);
		if($posid < 1) return false;
		return $this->db->get_one("SELECT * FROM `$this->table` WHERE `posid`=$posid");
	}

	function add($pos)
	{
		if(!is_array($pos) || empty($pos['name'])) return false;
		$this->db->insert($this->table, $pos);
		return $this->db->insert_id();
	}

	function edit($posid, $pos)
	{
		if(!$posid || !is_array($pos) || empty($pos['name'])) return false;
		return $this->db->update($this->table, $pos, "`posid`=$posid");
	}

	function delete($posid)
	{
		$posid = intval($posid);
		if($posid < 1) return false;
		return $this->db->query("DELETE FROM `$this->table` WHERE `posid`=$posid");
	}

	function listinfo($where = '', $order = '', $page = 1, $pagesize = 50)
	{
		if($where) $where = " WHERE $where";
		if($order) $order = " ORDER BY $order";
		$page = max(intval($page), 1);
            $offset = $pagesize*($page-1);
            $limit = " LIMIT $offset, $pagesize";
		$r = $this->db->get_one("SELECT count(*) as `number` FROM `$this->table` $where");
            $number = $r['number'];
            $this->pages = pages($number, $page, $pagesize);
		$array = array();
		$result = $this->db->query("SELECT * FROM `$this->table` $where $order $limit");
		while($r = $this->db->fetch_array($result))
		{
			$array[] = $r;
		}
		$this->number = $this->db->num_rows($result);
            $this->db->free_result($result);
		return $array;
	}

	function count($posid)
	{
		$posid = intval($posid);
              $r = $this->db->get_one("SELECT COUNT(*) AS `count` FROM `$this->table_position` WHERE `posid`=$posid");
              
		return $r ? $r['count'] : '';
	}

	function cache($where = '', $order = 'listorder,posid')
	{
		cache_table(DB_PRE.'pos', '*', 'name', '', 'listorder,posid', 0);
	}

	function listorder($info)
	{
		if(!is_array($info)) return false;
		foreach($info as $id=>$listorder)
		{
			$id = intval($id);
			$listorder = intval($listorder);
			$this->db->query("UPDATE `$this->table` SET `listorder`=$listorder WHERE `posid`=$id");
		}
		return true;
	}

    function content_add($posid, $contentid)
    {
        $posid = intval($posid);
        if(is_array($contentid))
		{
			$contentid = array_map('intval', $contentid);
			foreach($contentid AS $id)
			{
				$this->db->query("INSERT INTO `$this->table_position` (`posid`, `contentid`) VALUES($posid, $id)");
				$this->db->query("UPDATE `$this->table_content` SET `posids`=1 where `contentid`=$id");
			}
		}
		else
		{
			$this->db->query("INSERT INTO `$this->table_position` (`posid`, `contentid`) VALUES($posid, $contentid)");
			$this->db->query("UPDATE `$this->table_content` SET `posids`=1 where `contentid`=$contentid");
		}
        return true;
    }

    function content_select($where, $page = 1, $pagesize = 50)
	{
		if($where) $where = "WHERE $where";
		$page = max(intval($page), 1);
        $offset = $pagesize*($page-1);
        $limit = " LIMIT $offset, $pagesize";
		$r = $this->db->get_one("SELECT count(*) as `number` FROM `$this->table_content` $where");
        $number = $r['number'];
        $this->pages = pages($number, $page, $pagesize);
		return $this->db->select("SELECT * FROM `$this->table_content` $where ORDER BY `contentid` $limit");
	}

    function content_list($posid, $page = 1, $pagesize = 50)
	{
		$page = max(intval($page), 1);
        $offset = $pagesize*($page-1);
        $limit = " LIMIT $offset, $pagesize";
		$r = $this->db->get_one("SELECT count(*) as `number` FROM `$this->table_position` AS a ,`$this->table_content` AS b WHERE a.contentid = b.contentid AND posid = $posid");
        $number = $r['number'];
        $this->pages = pages($number, $page, $pagesize);
		return $this->db->select("SELECT * FROM `$this->table_position` AS a ,`$this->table_content` AS b WHERE a.contentid=b.contentid AND a.posid=$posid ORDER BY a.contentid $limit");
	}

    function content_delete($posid, $contentid)
	{
		$posid = intval($posid);
            if(is_array($contentid))
		{
			$contentid = array_map('intval', $contentid);
            $contentids = implodeids($contentid);
			$this->db->query("DELETE FROM `$this->table_position` WHERE posid=$posid AND `contentid` IN($contentids)");
			foreach($contentid as $id)
			{
				$this->content_pos($id);
			}
		}
		else
		{
            $contentids = $contentid;
			$this->db->query("DELETE FROM `$this->table_position` WHERE posid=$posid AND `contentid` IN($contentids)");
			$this->content_pos($contentid);
		}
		return true;
	}

	function content_exists($posid, $contentid)
	{
		$posid = intval($posid);
		$contentid = intval($contentid);
		return $this->db->get_one("SELECT `posid` FROM `$this->table_position` WHERE `posid`=$posid AND `contentid`=$contentid");
	}

	function content_pos($contentid)
	{
		$contentid = intval($contentid);
		$posids = $this->db->get_one("SELECT `posid` FROM `$this->table_position` WHERE `contentid`=$contentid") ? 1 : 0;
		return $this->db->query("UPDATE `$this->table_content` SET `posids`=$posids where `contentid`=$contentid");
	}
}
?>