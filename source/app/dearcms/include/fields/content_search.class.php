<?php
class content_search
{
	var $db;
	var $table;
	var $fields;
	var $common_fields;
	var $modelid;
	var $sql;

    function __construct()
    {
		global $db;
		$this->db = &$db;
		$this->table = '`'.DB_PRE.'content` a';
        $this->fields = $this->common_fields = cache_read('common_fields.inc.php', 'fields/');
		$catcode = isset($_GET['catcode']) ? intval($_GET['catcode']) : 0;
		if($catcode > 0) $this->set_catcode($catcode);
        $this->set();
    }

	function content_search()
	{
		$this->__construct();
	}

	function set()
	{
		$where = array();
		foreach($this->fields as $field=>$v)
		{
			$func = $v['formtype'];
			if($v['issearch'] && isset($_GET[$field]) && method_exists($this, $func))
			{
				 $where[$field] = $this->$func($field, $_GET[$field]) ;
			}
			if($v['isorder'])
			{
				$pre = isset($this->common_fields[$field]) ? 'a.' : 'b.';
				$this->order[] = $pre.$field.' ASC';
				$this->order[] = $pre.$field.' DESC';
			}
		}
		$where = array_filter($where);
		foreach($where as $field=>$w)
		{
			$pre = isset($this->common_fields[$field]) ? 'a.' : 'b.';
			$where[$field] = $pre.$w;
		}
		$where = implode(' AND ', $where);
		$orderby = in_array($_GET['orderby'], $this->order) ? $_GET['orderby'] : 'a.contentid DESC';
		if($this->modelid)
		{
			if($where) $where = "AND $where";
			$sql = "SELECT * FROM $this->table WHERE a.contentid=b.contentid AND a.status=99 $where ORDER BY $orderby";
		}
		else
		{
			if($where) $where = " AND $where";
			$sql = "SELECT * FROM $this->table WHERE status=99 $where ORDER BY $orderby";
		}
		$this->sql = $sql;
		return true;
	}

	function set_catcode($catcode)
	{
		global $MODEL,$CAT;
		if(!isset($CAT[$catcode])) return false;
		$this->modelid = $CAT[$catcode]['modelid'];
		if(!isset($MODEL[$this->modelid])) return false;
		$this->table = '`'.DB_PRE.'content` a, `'.DB_PRE.'c_'.$MODEL[$this->modelid]['tablename'].'` b';
		$this->fields = cache_read($this->modelid.'_fields.inc.php', CACHE_MODEL_PATH);
		return true;
	}

	function data($page = 1, $pagesize = 20)
	{
		if(!$this->sql) return false;
		$page = max(intval($page), 1);
		$offset = $pagesize*($page-1);
		$sql_count = preg_replace("/^SELECT([^(]+)FROM(.+)(ORDER BY.+)$/i", "SELECT COUNT(*) AS `count` FROM\\2", $this->sql);
		$totaltmp = $this->db->get_one($sql_count);
		$this->total = $totaltmp[count];
		if($this->total == 0) return array();
		$this->pages = pages($this->total, $page, $pagesize);
		$data = array();
		$result = $this->db->query("$this->sql LIMIT $offset, $pagesize");
		while($r = $this->db->fetch_array($result))
		{
			$data[] = $r;
		}
		$this->db->free_result($result);
		return $data;
	}

}?>