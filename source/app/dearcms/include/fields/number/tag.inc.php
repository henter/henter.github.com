	function number($field, $value)
	{
		return $value === '' ? '' : " `$field`='$value' ";
	}
