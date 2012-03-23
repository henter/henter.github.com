<?php

class tree extends object
{
	protected $db, $table, $field_id, $field_parentid, $field_name;

	function __construct($table, $id = 'id', $parentid = 'parentid', $name = 'name')
	{
		$this->table = $table;
		$this->field_id = $id;
		$this->field_parentid = $parentid;
		$this->field_name = $name;
		$this->db = & factory::db();
	}

	function set($data)
	{
		if (isset($data[$this->field_parentid]) && intval($data[$this->field_parentid]) < 1) $data[$this->field_parentid] = null;
		unset($data['parentids'], $data['childids']);
		if (isset($data[$this->field_id]) && $data[$this->field_id])
		{
			$id = intval($data[$this->field_id]);
			unset($data[$this->field_id]);
			$r = $this->get($id);
			if (!$r) return false;
			$is_update = 0;
			if (array_key_exists($this->field_parentid,$data) && $r[$this->field_parentid] !== $data[$this->field_parentid])
			{
				if (isset($data[$this->field_parentid])
					 && in_array($data[$this->field_parentid], explode(',', $r['childids'])))
				{
					$this->error = '逻辑错误';
					return false;
				}
				else
				{
					$is_update = 1;
				}
			}
			$this->db->update("UPDATE `$this->table` SET `".implode('`=?,`', array_keys($data))."`=? WHERE `$this->field_id`=$id", array_values($data));
			if ($is_update)
			{
				// !is_null($data[$this->field_parentid])
				if (array_key_exists($this->field_parentid,$data))
				{
					$this->set_parentids($id);
					$d = $this->get($id, 'parentids');
					array_map(array($this, 'set_childids'), explode(',', $d['parentids']));
				}
				if (!is_null($r['parentids']))
				{
					array_map(array($this, 'set_childids'), explode(',', $r['parentids']));
				}
				if (!is_null($r['childids']))
				{
					array_map(array($this, 'set_parentids'), explode(',', $r['childids']));
				}
			}
			return true;
		}
		else
		{
			$id = $this->db->insert("INSERT INTO `$this->table`(`".implode('`,`', array_keys($data))."`) VALUES(".implode(',', array_fill(0, count($data), '?')).")", array_values($data));
			if (isset($data[$this->field_parentid]) && $data[$this->field_parentid] > 0)
			{
				$id = $this->db->lastInsertId();
				$this->set_parentids($id);
				$r = $this->get($id, 'parentids');
				array_map(array($this, 'set_childids'), explode(',', $r['parentids']));
			}
			return $id;
		}
	}

	function get($id, $fields = '*')
	{
		return $this->db->get("SELECT $fields FROM `$this->table` WHERE `$this->field_id`=$id");
	}

	function rm($id)
	{
		$id = intval($id);
		$r = $this->get($id);
		if (!$r) return false;
		$this->db->exec("DELETE FROM `$this->table` WHERE `$this->field_id`=$id");
		if (!is_null($r[$this->field_parentid]))
		{
			$parentids = explode(',', $r['parentids']);
			array_map(array($this, 'set_childids'), $parentids);
		}
		return true;
	}

	function exists($id)
	{
		return $this->get($id, $this->field_id) ? true : false;
	}

	function ls($parentid = null)
	{
		return $this->get_child($parentid);
	}

	function pos($id, $fields = '*')
	{
		$r = $this->get($id, $fields);
		if (!$r) return false;
		$data = array();
		if (!is_null($r['parentids']))
		{
			$data = $this->db->select("SELECT $fields FROM `$this->table` WHERE `$this->field_id` IN(?)", array($r['parentids']));
		}
		$data[] = $r;
		return $data;
	}

	function search($name, $fields = '*')
	{
		$name = trim($name);
		if (empty($name)) return false;
		return $this->db->select("SELECT $fields FROM `$this->table` WHERE `$this->field_name` LIKE '%?%' ORDER BY `sort`, `$this->field_id`", array($name));
	}

	function set_parentids($id)
	{
		$data = $this->get_parent($id);
		if ($data)
		{
			foreach ($data as $r)
			{
				$parentids[] = $r[$this->field_id];
			}
			$parentids = "'".implode(',', $parentids)."'";
		}
		else
		{
			$parentids = 'null';
		}
		$id = intval($id);
		return $this->db->exec("UPDATE `$this->table` SET `parentids`=$parentids WHERE `$this->field_id`=$id");
	}

	function set_childids($id)
	{
		$data = $this->get_child($id, '*', 1);
		if ($data)
		{
			$childids = array();
			foreach ($data as $r)
			{
				$childids[] = $r[$this->field_id];
			}
			$childids = "'".implode(',', $childids)."'";
		}
		else
		{
			$childids = 'null';
		}
		$id = intval($id);
		return $this->db->exec("UPDATE `$this->table` SET `childids`=$childids WHERE `$this->field_id`=$id");
	}

	function get_child($id = null, $fields = '*', $deep = 0, $where = 1)
	{
		if (is_null($id))
		{
			$data = $this->db->select("SELECT $fields FROM `$this->table` WHERE `$this->field_parentid` is null AND $where ORDER BY `sort`");
		}
		else
		{
			$data = $this->db->select("SELECT $fields FROM `$this->table` WHERE `$this->field_parentid`=? AND $where ORDER BY `sort`", array($id));
		}
		if ($deep && $data)
		{
			foreach ($data as $k=>$r)
			{
				$data = array_merge($data, $this->get_child($r[$this->field_id], $fields, $deep));
			}
		}
		return $data;
	}

	function get_parent($id, $fields = '*', $is_start = 1)
	{
		$r = $this->get($id, $fields);
		if (!$r) return false;
		if ($is_start)
		{
			$data = array();
		}
		else
		{
			$data[] = $r;
		}
		if (!is_null($r[$this->field_parentid]))
		{
			$data = array_merge($data, $this->get_parent($r[$this->field_parentid], $fields, 0));
		}
		if ($is_start) krsort($data);
		return $data;
	}

	function sort($data = array())
	{
		foreach ($data as $id=>$sort)
		{
			$id = intval($id);
			$sort = min(intval($sort), 255);
			$this->db->exec("UPDATE `$this->table` SET `sort`=$sort WHERE `$this->field_id`=$id");
		}
		return true;
	}
}