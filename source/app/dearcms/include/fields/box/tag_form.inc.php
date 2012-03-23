	function box($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if($boxtype == 'radio')
		{
			return form::radio($options, 'info['.$field.']', $field, $value, $cols, $css, $formattribute, $width);
		}
		elseif($boxtype == 'checkbox')
		{
			return form::checkbox($options, 'info['.$field.']', $field, $value, $cols, $css, $formattribute, $width);
		}
		elseif($boxtype == 'select')
		{
			return form::select($options, 'info['.$field.']', $field, $value, $size, $css, $formattribute);
		}
		elseif($boxtype == 'multiple')
		{
			return form::multiple($options, 'info['.$field.']', $field, $value, $size, $css, $formattribute);
		}
	}
