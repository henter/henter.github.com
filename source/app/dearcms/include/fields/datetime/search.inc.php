	function datetime($field, $value)
	{
		$sql = '';
		if(is_numeric($value['start'])) $sql .= "AND `$field`>='$value[start]' ";
		if(is_numeric($value['end'])) $sql .= "AND `$field`<='$value[end]' ";
		if($sql) $sql = substr($sql, 3);
		return $sql;
	}
