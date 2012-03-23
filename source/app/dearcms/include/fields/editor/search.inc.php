	function editor($field, $value)
	{
		return $value ? " `$field` LIKE '%$value%' " : '';
    }
