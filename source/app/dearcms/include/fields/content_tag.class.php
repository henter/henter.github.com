<?php
class content_tag
{
	var $modelid;
	var $fields;

    function __construct($modelid)
    {
		global $db;
		$this->db = &$db;
		$this->modelid = $modelid;
		$this->fields = $this->modelid ? cache_read($this->modelid.'_fields.inc.php', CACHE_MODEL_PATH) : cache_read('common_fields.inc.php', 'fields/');
    }

	function content_tag($modelid)
	{
		$this->__construct($modelid);
	}

	function get($fields, $where, $orderby)
	{
		global $MODEL;
		$content_table_fields = $this->db->get_fields(DB_PRE.'content');
		$array_diff = array_diff($fields, $content_table_fields);
		$this->_diff = $array_diff;
		$where_array_posid = $where_array_keyword = '';
		foreach($this->fields as $field=>$v)
		{
			if($field == 'catcode') continue;
			$func = $v['formtype'];
			if(!$v['iswhere'] || !method_exists($this, $func)) continue;
			$value = isset($where[$field]) ? $where[$field] : '';
			$wheresql[$field] = $this->$func($field, $value);
			if(is_array($wheresql[$field]))
			{
				if($wheresql[$field][0] == DB_PRE.'content_position')
				{
					$where_array_posid = $wheresql[$field];
				}
				elseif($wheresql[$field][0] == DB_PRE.'tags')
				{
					$where_array_keyword = $wheresql[$field];
				}
				unset($wheresql[$field]);
			}
			if($array_diff && $wheresql[$field])
			{
				$wheresql[$field]= in_array($field, $array_diff) ? 'b.'.ltrim($wheresql[$field]) : 'a.'.ltrim($wheresql[$field]);
			}
		}
		if($array_diff)
		{
			foreach($fields as $k=>$field)
			{
				$fields[$k] = in_array($field, $array_diff) ? 'b.'.$field : 'a.'.$field;
			}
			$tablename = "`".DB_PRE."content` a, `".DB_PRE."c_".$MODEL[$this->modelid]['tablename']."` b";
			$whereunion = 'a.contentid=b.contentid AND a.status=99 ';
			$array_orderby = explode(' ',$orderby);
			if(in_array($array_orderby[0],$content_table_fields))
			{
				$orderby = 'a.'.$orderby;
			}
			else
			{
				$orderby = 'b.'.$orderby;
			}
			if(is_array($where_array_posid))
			{
				$tablename .= ", `$where_array_posid[0]` p";
				$whereunion .= ' AND a.contentid=p.'.$where_array_posid[1].' AND '.$where_array_posid[2];
			}
			if(is_array($where_array_keyword))
			{
				$tablename .= ", `$where_array_keyword[0]` k";
				$whereunion .= ' AND a.contentid=k.'.$where_array_keyword[1].' AND '.$where_array_keyword[2];
			}
		}
		else
		{	
			if(is_array($where_array_posid) || is_array($where_array_keyword))
			{
				foreach($fields as $k=>$field)
				{
					$fields[$k] = 'a.'.$field;
				}
				if(is_array($where_array_posid))
				{
					$tablename = "`".DB_PRE."content` a, `$where_array_posid[0]` p";
					$whereunion = 'a.contentid=p.'.$where_array_posid[1].' AND '.$where_array_posid[2].' AND a.status=99 ';
				}
				if(is_array($where_array_keyword))
				{
					if(empty($tablename))
					{
						$tablename .= "`".DB_PRE."content` a, `$where_array_keyword[0]` k";
						$whereunion = 'a.contentid=k.'.$where_array_keyword[1].' AND '.$where_array_keyword[2].' AND a.status=99 ';
					}
					else
					{
						$tablename .= ", `$where_array_keyword[0]` k";
						$whereunion .= ' AND a.contentid=k.'.$where_array_keyword[1].' AND '.$where_array_keyword[2].' ';
					}
				}
				$array_orderby = explode(' ',$orderby);
				if(in_array($array_orderby[0],$content_table_fields))
				{
					$orderby = 'a.'.$orderby;
				}
				else
				{
					$orderby = 'b.'.$orderby;
				}
			}
			else
			{
				$tablename = "`".DB_PRE."content`";
				$whereunion = ' status=99 ';
			}
		}
		$fields = implode(',', $fields);
		$wheresql = implode(' AND ', array_filter($wheresql));
		$wheresql = !empty($wheresql) ? ' AND '.$wheresql : '';
		if(isset($where['catcode'])) $wheresql .= $this->catcode('catcode', $where['catcode']);
		$sql = "SELECT $fields FROM $tablename WHERE $whereunion $wheresql ORDER BY $orderby";
		return $sql;
	}

}?>