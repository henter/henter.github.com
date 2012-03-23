	function downfile($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::downfile('info['.$field.']', $field, $value, $size, $mode, $css, $formattribute);
	}
