	function image($field, $value)
	{
		return $value === '' ? '' : " `$field`!='' ";
	}
