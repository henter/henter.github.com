	function number($field, $value)
	{
	    if(is_numeric($value['start']) && is_numeric($value['end']) && $value['start'] == $value['end'])
		{
		    $sql = " `$field`='$value[start]' ";
		}
		else
		{
			$sql = '';
			if(is_numeric($value['start'])) $sql .= "AND `$field`>='$value[start]' ";
			if(is_numeric($value['end'])) $sql .= "AND `$field`<='$value[end]' ";
			if($sql) $sql = substr($sql, 3);
		}
		return $sql;
	}
