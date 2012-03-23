	function textarea($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		if($checkcharacter && $maxlength)
		{
			$formattribute .= ' onkeyup="checkLength(this, \''.$field.'\', \''.$maxlength.'\');"';
		}
		$html = '';
		if($value && $checkcharacter && $maxlength)
		{
			$html = '<script type="text/javascript">checkLength(document.getElementById(\''.$field.'\'), \''.$field.'\', \''.$maxlength.'\');</script>';
		}
		return form::textarea('info['.$field.']', $field, $value, $rows, $cols, $css, $formattribute, $checkcharacter, $maxlength).$html;
	}
