	function datetime($field, $value)
	{
		return $value === '' ? '' : " `$field`='$value' ";
	}
