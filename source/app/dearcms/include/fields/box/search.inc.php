	function box($field, $value)
	{
		return $value === '' ? '' : " `$field`='$value' ";
	}
