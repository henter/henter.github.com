	function text($field, $value)
	{
		return $value === '' ? '' : " `$field`='$value' ";
	}
