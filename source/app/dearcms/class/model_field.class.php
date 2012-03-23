<?php 
class model_field
{
	var $db;
	var $pages;
	var $number;
	var $table;
	var $modelid;
	var $modelname;
	var $tablename;
	var $fields;

    function __construct($modelid)
    {
		global $db;
		$this->db = &$db;
		$this->table = DB_PRE.'model_field';
		$model = $this->db->get_one("SELECT * FROM ".DB_PRE."model WHERE modelid=$modelid");
		$this->modelid = $model['modelid'];
		$this->modelname = $model['name'];
		$this->fields =  cache_read($modelid.'_fields.inc.php', CACHE_MODEL_PATH);
		$this->tablename = DB_PRE.'c_'.$model['tablename'];
    }

	function model_field($modelid)
	{
		$this->__construct($modelid);
	}

	function get($fieldid)
	{
		$fieldid = intval($fieldid);
		if($fieldid < 1) return false;
		$r = $this->db->get_one("SELECT * FROM $this->table WHERE fieldid=$fieldid");
		eval("\$setting = $r[setting];");
		return $setting ? array_merge($r, $setting) : $r;
	}

	function add($info, $setting = array())
	{
		if(!is_array($info) || empty($info['field']) || empty($info['name']) || !$this->check($info['field']) || $this->exists($info['field'])) return false;
		$this->db->insert($this->table, $info);
		$fieldid = $this->db->insert_id();
              setting_set($this->table, "fieldid=$fieldid", $setting);
		$this->cache();
        return true;
	}

	function edit($fieldid, $info, $setting = array())
	{
		if(!$fieldid || !is_array($info) || empty($info['name'])) return false;
		$this->db->update($this->table, $info, "fieldid=$fieldid");
		$t = setting_set($this->table, "fieldid=$fieldid", $setting);
		$this->cache();
		return true;
	}

	function delete($fieldid)
	{
		$fieldid = intval($fieldid);
		if($fieldid < 1) return false;
		$this->db->query("DELETE FROM $this->table WHERE fieldid=$fieldid");
		$this->cache();
		return $this->db->affected_rows();
	}

	function listinfo($where = '', $order = '', $page = 1, $pagesize = 100)
	{
		if($where) $where = " WHERE $where";
		if($order) $order = " ORDER BY $order";
		$page = max(intval($page), 1);
            $offset = $pagesize*($page-1);
            $limit = " LIMIT $offset, $pagesize";
		$array = array();
		$result = $this->db->query("SELECT * FROM $this->table $where $order $limit");
		while($r = $this->db->fetch_array($result))
		{
			if($r['setting'])
			{
				eval("\$setting = $r[setting];");
				$r = array_merge($r, $setting);
				unset($r['setting'], $setting);
			}
			$array[] = $r;
		}
            $this->db->free_result($result);
		return $array;
	}

	function cache()
	{
            $fields = $datasource = array();
            $infos = $this->listinfo("modelid=$this->modelid AND disabled=0", 'listorder,fieldid', 1, 100);
		foreach($infos as $id=>$info)
		{
			$fields[$info['field']] = $info;
			if(!$info['issystem']) $datasource[$info['field']] = $info['name'];
		}
		cache_write($this->modelid.'_fields.inc.php', $fields, CACHE_MODEL_PATH);
		if(strtolower(CHARSET) != 'utf-8') $datasource = str_charset(CHARSET, 'utf-8', $datasource);
		cache_write('dc_'.str_replace(DB_PRE, 'dc_', $this->tablename).'.php', $datasource, DC_ROOT.'data/datasource/');
		return true;
	}

	function listorder($info)
	{
		if(!is_array($info)) return false;
		foreach($info as $id=>$listorder)
		{
			$id = intval($id);
			$listorder = intval($listorder);
			$this->db->query("UPDATE $this->table SET listorder=$listorder WHERE fieldid=$id");
		}
		$this->cache();
		return true;
	}

	function disable($fieldid, $disabled)
	{
		$fieldid = intval($fieldid);
		if($fieldid < 1) return false;
		$this->db->query("UPDATE $this->table SET disabled=$disabled WHERE fieldid=$fieldid");
		$this->cache();
        return true;
	}

	function check($field)
	{
		return preg_match("/^[a-z][0-9a-z_]*[0-9a-z]?$/i", $field);
	}

	function exists($field)
	{
		return array_key_exists($field, $this->fields);
	}
}
?>