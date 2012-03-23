	function textarea($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::textarea('info['.$field.']', $field, $value, $rows, $cols, $css, $formattribute);
	}
