	function number($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		return form::text('info['.$field.']', $field, $value, 'text', 10, $css, $formattribute);
	}
