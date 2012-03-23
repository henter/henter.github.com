	function text($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$type = $ispassword ? 'password' : 'text';
		return form::text('info['.$field.']', $field, $value, $type, $size, $css, $formattribute, $minlength, $maxlength, $pattern, $errortips);
	}
