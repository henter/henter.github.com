	function number($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::text('info['.$field.']', $field, $value, 'text', 10, $css, $formattribute, $minlength, $maxlength, $pattern, $errortips);
	}
