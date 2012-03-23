	function typeid($field, $value, $fieldinfo)
	{
		return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"10\"> ".form::select_type('dearcms', 'select_'.$field, 'select_'.$field, '请选择', $value, 'onchange="$(\'#'.$field.'\').val(this.value)"');
	}
