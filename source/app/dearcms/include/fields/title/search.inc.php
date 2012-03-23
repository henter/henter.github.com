	function title($field, $value)
	{
		return $value === '' ? '' : " `$field` LIKE '%$value%' ";
	}