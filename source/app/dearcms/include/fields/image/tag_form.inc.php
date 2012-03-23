	function image($field, $value, $fieldinfo)
	{
		$checked = $value ? 'checked': '';
		return "<input type=\"checkbox\" name=\"info[$field]\" id=\"$field\" value=\"1\" $checked> 是";
	}
