<?php

class treeview
{
	public $data = array(), $html = '';
	       
	function __construct($data = array())
	{
		$this->data = $data;
	}
	
	function set($data)
	{
		if (!is_array($data)) return false;
		$this->data = $data;
	}

	function get($id = null, $treeid = 'tree', $eval = '', $deep = 1)
	{
		$childs = $this->get_child($id);
		if (!$childs) return ;
		if ($deep == 1) $eval = addslashes($eval);
		$space = str_repeat("\t", $deep-1);
		$html = $treeid ? "<ul id=\"$treeid\">\n" : "\n".$space."<ul>\n";
		foreach ($childs as $k=>$r)
		{
			$child = $this->haschild($k) ? $this->get($k, '', $eval, $deep+1) : '';
			extract($r);
			eval("\$html .= \"$space\t$eval\n\";");
		}
		$html .= $space."</ul>\n".$space;
		return $html;
	}
	
	function select($id = null, $selectedid = null, $eval = '', $deep = 1)
	{
		$childs = $this->get_child($id);
		if (!$childs) return ;
		if ($deep == 1)
		{
			$eval = addslashes($eval);
			$this->html = '';
		}
		$space = $deep > 1 ? str_repeat('&nbsp;&nbsp; ', $deep-1) : '';
		foreach ($childs as $k=>$r)
		{
			extract($r);
			$selected = $selectedid == $k ? 'selected' : '';
			eval("\$this->html .= \"$eval\n\";");
			if ($this->haschild($k)) $this->select($k, $selectedid, $eval, $deep+1);
		}
		return $this->html;
	}
	
	function pos($id, $eval = '')
	{
		if(!is_array($this->data) || !isset($this->data[$id])) return false;
		$parents = $this->get_parent($id);
		$parents[] = $this->data[$id];
		$eval = addslashes($eval);
		$html = '';
		foreach ($parents as $r)
		{
			extract($r);
			eval("\$html .= \"$eval\n\";");
		}
		return $html;
	}

	function get_parent($id)
	{
		if(!is_array($this->data) || !isset($this->data[$id])) return false;
		static $parents = array();
		$parentid = $this->data[$id]['parentid'];
        if (is_null($parentid))
        {
        	krsort($parents);
        }
        else 
        {
        	$parents[] = $this->data[$parentid];
        	$this->get_parent($parentid);
        }
        return $parents;
	}

	function get_child($id)
	{
		if(!is_array($this->data) || (!is_null($id) && !isset($this->data[$id]))) return false;
		if (is_numeric($id)) $id = intval($id);
		$childs = array();
		foreach($this->data as $k=>$r)
		{
			if (is_numeric($r['parentid'])) $r['parentid'] = intval($r['parentid']);
			if($r['parentid'] === $id) $childs[$k] = $r;
		}
		return $childs;
	}
	
	function haschild($id)
	{
		if(!is_array($this->data) || !isset($this->data[$id])) return false;
		if (is_numeric($id)) $id = intval($id);
		foreach($this->data as $k=>$r)
		{
			if (is_numeric($r['parentid'])) $r['parentid'] = intval($r['parentid']);
			if($r['parentid'] === $id) return true;
		}
		return false;
	}
}
?>